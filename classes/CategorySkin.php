<?php
/**
 * Curse Inc.
 * Category Skins
 * Apply custom styles to pages according to category membership
 *
 * @author		Noah Manneschmidt
 * @copyright	(c) 2015 Curse Inc.
 * @license		All Rights Reserved
 * @package		CategorySkins
 * @link		http://www.curse.com/
 *
**/

class CategorySkin {
	// core attributes of the skin
	private $category;
	private $prefix;
	private $suffix;
	private $logo;
	private $hasStyle = false;

	private function __construct($row) {
		$this->category = $row['cs_category'];
		$this->prefix = $row['cs_prefix'];
		$this->suffix = $row['cs_suffix'];
		$this->logo = $row['cs_logo'];
		$this->hasStyle = $row['cs_style'];
	}

	public static function injectModules() {
		global $wgResourceModules;
		$db = wfGetDB(DB_SLAVE);
		$res = $db->select(
			['category_skins'],
			['cs_category'],
			[],
			__METHOD__
		);
		foreach ($res as $cs) {
			$wgResourceModules['ext.categoryskins.skin.'.self::categoryToModuleName($cs->cs_category)] = [
				'class' => 'CategorySkinModule'
			];
		}
	}

	/**
	 * enforce module name constraints (no pipes, commas, or exclamation marks, and under 255 chars)
	 */
	public static function categoryToModuleName($name) {
		return substr(str_replace(['|',',','!'], '', $name), 0, 200);
	}

	/**
	 * Recursive lookup through nested categories to find one for which we have a style
	 * @param  Title
	 * @return CategorySkin or false
	 */
	public static function newFromTitle(Title $title) {
		$categoryDepths = Curse::array_keys_recursive($title->getParentCategoryTree());
		// filter out the "Category:" prefix and flatten
		$db = wfGetDB(DB_SLAVE);
		$cats = [];
		foreach ($categoryDepths as $d => $categories) {
			foreach ($categories as $i => $category) {
				$cats[] = "'".$db->strencode(substr($category, strpos($category, ':')+1))."'";
			}
		}
		if (!empty($cats)) {
			$cats = implode(',', $cats);
			// SELECT * FROM catstyles WHERE category IN (implode($cats, ',')) ORDER BY FIELD(catstyles.category, implode($cats, ',')) LIMIT 1
			$res = $db->selectRow('category_skins', ['*'], ["cs_category IN ($cats)"], __METHOD__, ['ORDER BY' => "FIELD(cs_category, $cats)"]);
			if ($res) {
				return new CategorySkin((array)$res);
			}
		}

		// if we don't have a skin yet, check categories on subject page (if this is a talk page)
		if ($title->isTalkPage()) {
			return self::newFromTitle($title->getSubjectPage());
		}
		return false;
	}

	/**
	 * Apply this skin to the given
	 */
	public function apply(&$title, $output) {
		global $wgUploadPath, $wgLogo;

		// apply logo
		if ($this->logo) {
			$hash = md5($this->logo);
			$wgLogo = implode('/', [$wgUploadPath, substr($hash, 0, 1), substr($hash, 0, 2), $this->logo]);
		}

		// apply title manipulation
		$title->mPrefixedText = $this->prefix.$title->getPrefixedText().$this->suffix;

		// apply custom stylesheet
		if ($this->hasStyle) {
			$output->addModules('ext.categoryskins.skin.'.self::categoryToModuleName($this->category));
		}
	}
}
