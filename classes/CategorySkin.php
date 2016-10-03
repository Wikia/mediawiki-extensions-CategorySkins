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
	/**
	 * Category name
	 * @var	string
	 */
	private $category;

	/**
	 * Title prefix
	 * @var	string
	 */
	private $prefix;

	/**
	 * Title suffix
	 * @var	string
	 */
	private $suffix;

	/**
	 * Logo title
	 * @var	string
	 */
	private $logo;

	/**
	 * Logo page link
	 * @var	string
	 */
	private	$logoLink;

	/**
	 * Has a stylesheet flag
	 * @var bool
	 */
	private $hasStyle = false;

	/**
	 * Main constructor
	 *
	 * @param array	$row	Core skin attributes
	 */
	private function __construct($row) {
		$this->category = $row['cs_category'];
		$this->prefix = $row['cs_prefix'];
		$this->suffix = $row['cs_suffix'];
		$this->logo = $row['cs_logo'];
		$this->logoLink = $row['cs_logo_link'];
		$this->hasStyle = $row['cs_style'];
	}

	/**
	 * Injects styles into the Resource Loader
	 *
	 * @access	public
	 * @return	void
	 */
	public static function injectModules() {
		global $wgResourceModules;

		if (!defined('MW_PHPUNIT_TEST') && !defined('MW_UPDATER') && !defined('RUN_MAINTENANCE_IF_MAIN') && !defined('DO_MAINTENANCE')) {
			$db = wfGetDB(DB_SLAVE);
			$res = $db->select(
				['category_skins'],
				['cs_category'],
				[],
				__METHOD__
			);

			if (empty($res)) {
				return;
			}

			foreach ($res as $cs) {
				$wgResourceModules['ext.categoryskins.skin.'.self::categoryToModuleName($cs->cs_category)] = [
					'class' => 'CategorySkinModule'
				];
			}
		}
	}

	/**
	 * Enforce module name constraints (No pipes, commas, or exclamation marks, and under 255 chars)
	 *
	 * @param string $name	Module name
	 * @return string	Cleaned up module name
	 */
	public static function categoryToModuleName($name) {
		return substr(str_replace(['|', ',', '!'], '', $name), 0, 200);
	}

	/**
	 * Enforce body class name (No space, convert camel case to hyphens, and remove extra hyphens)
	 *
	 * @param string	Category name
	 * @return string	Cleaned up body class name
	 */
	public static function categoryToBodyClassName($name) {
		// Convert spaces to hyphens.
		$name = str_replace(" ", "-", $name);

		// Get rid of all extra hyphens and lowercase it all;
		$name = 'cs-'.mb_strtolower(preg_replace('#-{2,}#', '-', $name), 'UTF-8');

		return $name;
	}

	/**
	 * Recursive lookup through nested categories to find one for which we have a style
	 *
	 * @param  Title
	 * @return CategorySkin or false
	 */
	public static function newFromTitle(Title $title) {
		$cache = wfGetCache( CACHE_ANYTHING );
		$key = wfMemcKey( 'categoryskins', $title->getPrefixedDBkey(), 'skin' );
		$data = $cache->get($key);
		
		if ($data !== false) {
			wfDebugLog( 'CategorySkins', 'Retrieved category skin data from Memcache');
			return new CategorySkin((array)$data);
		}
		
		wfDebugLog( 'CategorySkins', 'Retrieving category skin data from the DB');
		$categoryDepths = Curse::array_keys_recursive($title->getParentCategoryTree());
		// filter out the "Category:" prefix and flatten
		$db = wfGetDB(DB_SLAVE);
		$cats = [];
		foreach ($categoryDepths as $d => $categories) {
			foreach ($categories as $i => $category) {
				$cats[] = "'" . $db->strencode(substr($category, strpos($category, ':') + 1)) . "'";
			}
		}

		if ($title->getNamespace() == NS_CATEGORY) {
			$cats[] = "'" . $db->strencode($title->getDBkey()) . "'";
		}

		if (!empty($cats)) {
			$cats = implode(',', $cats);
			// SELECT * FROM catstyles WHERE category IN (implode($cats, ',')) ORDER BY FIELD(catstyles.category, implode($cats, ',')) LIMIT 1
			$res = $db->selectRow(
				'category_skins',
				['*'],
				["cs_category IN ($cats)"],
				__METHOD__,
				['ORDER BY' => "FIELD(cs_category, $cats)"]
			);
			if ($res) {
				// Cache the results for 5 minutes
				$cache->set($key, $res, 300);
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
	 * Clears the cached category skin for the given Title
	 * @param Title $title
	 */
	public static function clearCacheForTitle(Title $title) {
		$cache = wfGetCache( CACHE_ANYTHING );
		$key = wfMemcKey( 'categoryskins', $title->getPrefixedDBkey(), 'skin' );
		$cache->delete($key);
	}

	/**
	 * Apply a skin to page's given category
	 *
	 * @param Title $title
	 * @param OutputPage $output
	 * @return void
	 */
	public function apply(Title &$title, OutputPage $output) {
		global $wgUploadPath, $wgLogo;

		// apply logo
		if ($this->logo) {
			$this->logo = str_replace(" ", "_", $this->logo);
			$hash = md5($this->logo);
			$wgLogo = implode('/', [$wgUploadPath, substr($hash, 0, 1), substr($hash, 0, 2), $this->logo]);
		}

		// apply custom stylesheet
		if ($this->hasStyle) {
			$output->addModules('ext.categoryskins.skin.'.self::categoryToModuleName($this->category));
		}
	}

	/**
	 * Apply a custom title from a given category
	 *
	 * @param $template
	 * @return bool | void
	 */
	public function applyTitleChange($template){
		if (!isset($template->data) || !isset($template->data['headelement'])) {
			return true;
		}

		$template->set(
			'headelement',
			str_replace('<title>'.htmlspecialchars($template->data['pagetitle']).'</title>', '<title>'.htmlspecialchars($this->prefix.$template->data['title'].$this->suffix).'</title>', $template->data['headelement'])
		);
	}

	/**
	 * Apply custom class to the body tag.
	 *
	 * @param	array	$bodyAttributes
	 * @return	void
	 */
	public function applyBodyChange(&$bodyAttributes) {
		$bodyAttributes['class'] .= ' '.self::categoryToBodyClassName($this->category);
	}

	/**
	 * Adjust mainpage URL to be the custom category skins one.
	 *
	 * @param	array	$navUrls
	 * @return	mixed|void
	 */
	public function applyLogoLinkChange(&$navUrls) {
		if (!$this->logoLink) {
			return;
		}
		$navUrls['mainpage']['href'] = "/".$this->logoLink;
	}
}
