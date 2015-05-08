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
	static private $mockData = [
		[
			'cs_id' => 1,
			'cs_category' => 'Elephants',
			'cs_prefix' => 'Elephant ',
			'cs_suffix' => ' Page',
			'cs_logo' => 'LogoElephant.png',
			'cs_style' => 1,
		]
	];

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
		// TODO replace mock data with DB data
		foreach (self::$mockData as $cs) {
			$wgResourceModules['ext.categoryskins.skin.'.self::categoryToModuleName($cs['cs_category'])] = [
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
		if (empty($categoryDepths)) {
			return false;
		}
		// filter out the "Category:" prefix
		foreach ($categoryDepths as $d => $categories) {
			foreach ($categories as $i => $category) {
				$categoryDepths[$d][$i] = substr($category, strpos($category, ':')+1);
			}
		}
		// SELECT * FROM catstyles WHERE category IN (implode($cats, ',')) ORDER BY FIELD(catstyles.category, implode($cats, ',')) LIMIT 1
		// $db = wfGetDB(DB_SLAVE);
		// $res = $db->select(null, );
		// TODO replace mock data with DB data
		if (!empty($categoryDepths[0]) && $categoryDepths[0][0] == 'Elephants') {
			$res = self::$mockData[0];
		}
		if ($res) {
			return new CategorySkin($res);
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
