<?php
/**
 * Curse Inc.
 * Category Skins
 * Apply custom styles to pages according to category membership
 *
 * @author    Noah Manneschmidt
 * @copyright (c) 2015 Curse Inc.
 * @license   GPL-2.0-or-later
 * @package   CategorySkins
 * @link      https://gitlab.com/hydrawiki
**/

class CategorySkinsHooks {
	/**
	 * Current Page Title
	 *
	 * @var	object
	 */
	static private $title;

	/**
	 * Current CategorySkin
	 *
	 * @var	object
	 */
	static private $categorySkin;

	/**
	 * Set the current page title object only once.
	 *
	 * @param Title $title
	 *
	 * @return void
	 */
	private static function initSkin(Title $title) {
		if ($title === null) {
			return false;
		}
		if (self::$title == null || !$title->equals(self::$title)) {
			self::$categorySkin = CategorySkin::newFromTitle($title);
			self::$title = $title;
		}
		return self::$categorySkin;
	}

	/**
	 * Check to see if a skin needs to be applied to the page.
	 *
	 * @param Title   $title
	 * @param Article $article
	 * @param object  $output
	 * @param User    $user
	 * @param object  $request
	 * @param object  $mediaWiki
	 *
	 * @see    http://www.mediawiki.org/wiki/Manual:Hooks/BeforeInitialize
	 * @return boolean
	 */
	public static function onBeforeInitialize(&$title, &$article, &$output, &$user, $request, $mediaWiki) {
		$skin = self::initSkin($title);
		if ($skin !== false) {
			$skin->apply($title, $output);
		}
		return true;
	}

	/**
	 * Check to see if a title needed to be overriden for the page.
	 *
	 * @param SkinTemplate  $skin
	 * @param QuickTemplate $template
	 *
	 * @see    http://www.mediawiki.org/wiki/Manual:Hooks/SkinTemplateOutputPageBeforeExec
	 * @return boolean
	 */
	public static function onSkinTemplateOutputPageBeforeExec(SkinTemplate &$skin, QuickTemplate &$template) {
		$cs_skin = self::initSkin($skin->getTitle());
		if ($cs_skin) {
			$cs_skin->applyTitleChange($template);
		}
		return true;
	}

	/**
	 * Check to see if a body class needs to be on a page.
	 *
	 * @param OutputPage $out
	 * @param Skin       $sk
	 * @param array      $bodyAttrs
	 *
	 * @see    http://www.mediawiki.org/wiki/Manual:Hooks/OutputPageBodyAttributes
	 * @return boolean
	 */
	public static function onOutputPageBodyAttributes(OutputPage $out, Skin $sk, array &$bodyAttrs) {
		$cs_skin = self::initSkin($sk->getTitle());
		if ($cs_skin !== false) {
			$cs_skin->applyBodyChange($bodyAttrs);
		}
		return true;
	}

	/**
	 * Check to see if the logo needs to have a url replacement done.
	 *
	 * @param SkinTemplate $skin
	 * @param array        $nav_urls
	 * @param integer      $revid
	 * @param integer      $revidDuplicate
	 *
	 * @see    https://www.mediawiki.org/wiki/Manual:Hooks/SkinTemplateBuildNavUrlsNav_urlsAfterPermalink
	 * @return boolean
	 */
	public static function onSkinTemplateBuildNavUrlsNav_urlsAfterPermalink(SkinTemplate &$skin, array &$nav_urls, int &$revid, int &$revidDuplicate) {
		$cs_skin = self::initSkin($skin->getTitle());
		if ($cs_skin !== false) {
			$cs_skin->applyLogoLinkChange($nav_urls);
		}
		return true;
	}

	/**
	 * Setups and Modifies Database Information
	 *
	 * @param DatabaseUpdater $updater Database update object
	 *
	 * @see    http://www.mediawiki.org/wiki/Manual:Hooks/LoadExtensionSchemaUpdates
	 * @return boolean	true
	 */
	public static function onLoadExtensionSchemaUpdates(DatabaseUpdater $updater) {
		$extDir = __DIR__;
		$updater->addExtensionUpdate(
			[
				'addTable',
				'category_skins',
				"{$extDir}/install/sql/create_table_category_skins.sql",
				true
			]
		);

		// 2015-06-16
		$updater->addExtensionUpdate(
			[
				'addField',
				'category_skins',
				'cs_logo_link',
				"{$extDir}/upgrade/sql/categoryskins_upgrade_add_cs_logo_link.sql",
				true
			]
		);

		return true;
	}

	/**
	 * Clear cached categories on page save
	 *
	 * @param WikiPage $article The page that was just saved.
	 *
	 * @return true
	 */
	public static function onPageContentSaveComplete($article) {
		if ($article && $article->getTitle()) {
			CategorySkin::clearCacheForTitle($article->getTitle());
		}
		return true;
	}
}
