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

class CategorySkinModule extends ResourceLoaderWikiModule {
	private $categoryName;
	public function __construct($categoryName) {
		$this->categoryName = $categoryName;
	}

	/* Protected Methods */

	/**
	 * Gets list of pages used by this module
	 *
	 * @param $context ResourceLoaderContext
	 *
	 * @return Array: List of pages
	 */
	protected function getPages( \ResourceLoaderContext $context ) {
		return [
			'MediaWiki:'.$this->categoryName.'.css' => [ 'type' => 'style' ]
		];
	}

	/* Methods */

	/**
	 * Gets group name
	 *
	 * @return String: Name of group
	 */
	public function getGroup() {
		return 'site';
	}

	public function getPosition() {
		return 'top';
	}
}
