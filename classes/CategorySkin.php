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
	private $prefix;
	private $suffix;
	private $logo;

	private function __construct($prefix = '', $suffix = '', $logo) {
		$this->prefix = $prefix;
		$this->suffix = $suffix;
		$this->logo = $logo;
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
		// SELECT * FROM catstyles WHERE category IN (implode($cats, ',')) ORDER BY FIELD(catstyles.category, implode($cats, ',')) LIMIT 1
		// $db = wfGetDB(DB_SLAVE);
		// $res = $db->select(null, );
		if (!empty($categoryDepths[0]) && $categoryDepths[0][0] == 'Category:Elephants') {
			return new CategorySkin('Elephant ', ' Page', 'LogoElephant.png');
		}
		MWDebug::log('No category match');
		return false;
	}

	/**
	 * Apply this skin to the given
	 */
	public function apply(&$title, $output) {
		global $wgUploadPath, $wgLogo;
		$hash = md5($this->logo);
		$wgLogo = implode('/', [$wgUploadPath, substr($hash, 0, 1), substr($hash, 0, 2), $this->logo]);
		$title->mPrefixedText = $this->prefix.$title->getPrefixedText().$this->suffix;
		// self::$categoryTitle = $this->title;
		// $output->addModules('ext.categoryskins.skin');
		// :( probably have to rewrite to perform the css content lookup that ResourceLoaderWikiModule does right here and insert inline instead
		// alternatively, modify the modules definition to be dynamic from the databaese right up front
	}
}
