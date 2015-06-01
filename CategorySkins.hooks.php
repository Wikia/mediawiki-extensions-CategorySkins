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
	/**
	 * Check to see if a skin needs to be applied to the page.
	 *
	 * @see		http://www.mediawiki.org/wiki/Manual:Hooks/BeforeInitialize
	 * @access	public
	 * @return	bool	true
	 */
	public static function onBeforeInitialize(&$title, &$article, &$output, &$user, $request, $mediaWiki) {
		$skin = CategorySkin::newFromTitle($title);
		if ($skin) {
			$skin->apply($title, $output);
		}
		return true;
	}

	/**
	 * Check to see if a title needed to be overriden for the page.
	 *
	 * @see		http://www.mediawiki.org/wiki/Manual:Hooks/SkinTemplateOutputPageBeforeExec
	 * @access	public
	 * @return	bool
	 */
	public static function onSkinTemplateOutputPageBeforeExec (&$skin, &$template) {
		$cs_skin = CategorySkin::newFromTitle($skin->getTitle());
		if ($cs_skin) {
			$cs_skin->applyTitleChange($template);
		}
		return true;
	}

	/**
	 * Check to see if a body class needs to be on a page.
	 *
	 * @see		http://www.mediawiki.org/wiki/Manual:Hooks/OutputPageBodyAttributes
	 * @access	public
	 * @return	bool
	 */
	public static function onOutputPageBodyAttributes ($out, $sk, &$bodyAttrs) {
		$cs_skin = CategorySkin::newFromTitle($sk->getTitle());
		if ($cs_skin) {
			$cs_skin->applyBodyChange($bodyAttrs);
		}
		return true;
	}

	/**
	 * Setups and Modifies Database Information
	 *
	 * @see		http://www.mediawiki.org/wiki/Manual:Hooks/LoadExtensionSchemaUpdates
	 * @access	public
	 * @return	boolean	true
	 */
	static public function onLoadExtensionSchemaUpdates(DatabaseUpdater $updater) {
		$extDir = __DIR__;
		$updater->addExtensionUpdate(array('addTable', 'category_skins', "{$extDir}/install/sql/create_table_category_skins.sql", true));

		return true;
	}
}
