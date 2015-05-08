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

class CategorySkinsHooks {
	public static function onBeforeInitialize(&$title, &$article, &$output, &$user, $request, $mediaWiki) {
		$skin = CategorySkin::newFromTitle($title);
		if ($skin) {
			$skin->apply($title, $output);
		}
		return true;
	}
}
