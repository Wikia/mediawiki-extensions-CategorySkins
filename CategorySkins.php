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

/******************************************/
/* Credits                                */
/******************************************/
$wgExtensionCredits['specialpage'][] = [
	'path'           => __FILE__,
	'name'           => 'Category Skins',
	'author'         => 'Curse Wiki Team',
	'descriptionmsg' => 'categoryskins_description',
	'version'        => '1.0' //Must be a string or Mediawiki will turn it into an integer.
];

// Uncomment if applicable
$wgAvailableRights[] = 'skincategories';

/******************************************/
/* Language Strings, Page Aliases, Hooks  */
/******************************************/
$wgMessagesDirs['CategorySkins'] = __DIR__.'/i18n';
$wgExtensionMessagesFiles['CategorySkins']      = __DIR__."/CategorySkins.i18n.php";

// Classes

$wgAutoloadClasses['CategorySkin']       = __DIR__.'/classes/CategorySkin.php';
$wgAutoloadClasses['CategorySkinModule'] = __DIR__.'/classes/CategorySkinModule.php';
$wgAutoloadClasses['CategorySkinsHooks'] = __DIR__.'/CategorySkins.hooks.php';

// Special Pages

$wgAutoloadClasses['SpecialCategorySkins'] = __DIR__."/specials/SpecialCategorySkins.php";
$wgSpecialPages['CategorySkins']           = 'SpecialCategorySkins';

// Resource modules

$wgResourceModules['ext.categoryskins.special'] = [
	'styles' => ['css/categoryskins.special.less'],
	'scripts' => ['js/categoryskins.special.js'],
	'localBasePath' => __DIR__.'/',
	'remoteExtPath' => 'CategorySkins',
	'dependencies' => ['ext.curse.font-awesome'],
	'position' => 'top',
];

// Hooks (uncomment if applicable)
$wgHooks['BeforeInitialize'][]					= 'CategorySkinsHooks::onBeforeInitialize';
$wgHooks['LoadExtensionSchemaUpdates'][]		= 'CategorySkinsHooks::onLoadExtensionSchemaUpdates';
$wgHooks['SkinTemplateOutputPageBeforeExec'][]	= 'CategorySkinsHooks::onSkinTemplateOutputPageBeforeExec';

// Setup functions
$wgExtensionFunctions[] = 'CategorySkin::injectModules';
