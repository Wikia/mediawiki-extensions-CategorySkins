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
	/* Protected Methods */

	/**
	 * Gets list of pages used by this module
	 *
	 * @param $context ResourceLoaderContext
	 *
	 * @return Array: List of pages
	 */
	protected function getPages( \ResourceLoaderContext $context ) {
		return [ // drop first 23 characters (ext.categoryskins.skin.)
			'MediaWiki:'.substr($this->name, 23).'.css' => [ 'type' => 'style' ]
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
