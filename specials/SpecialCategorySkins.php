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

class SpecialCategorySkins extends SpecialPage {
	public function __construct() {
		parent::__construct(
			'CategorySkins', // name
			'skincategories', // required user right
			true // display on Special:Specialpages
		);
	}

	public function getGroupName() {
		return 'pages';
	}

	public function execute( $path ) {
		$this->setHeaders();
		$this->checkPermissions();
		$this->outputHeader();
		$this->getOutput()->addModules('ext.categoryskins.special');

		if ($path == 'edit') {
			$formContents = [
				'category' => [
					'type' => 'text',
					'label' => 'Category',
					'required' => true
				],
				'titlePrefix' => [
					'type' => 'text',
					'label' => 'Title prefix'
				],
				'titleSuffix' => [
					'type' => 'text',
					'label' => 'Title suffix'
				],
				'logoReplace' => [
					'type' => 'text',
					'label' => 'Replace logo',
					'help' => 'Give the name of an uploaded file to replace the wiki logo for the pages in this category'
				],
				'stylesheet' => [
					'type' => 'check',
					'label' => 'Apply CSS page',
					'help' => 'Will apply MediaWiki:CategoryName.css to style pages in the category'
				],
			];
			$form = new HTMLForm($formContents, $this->getContext(), 'categoryskin');
			$form->setId('categoryskin')->setSubmitText('Save')->setWrapperLegendMsg('categoryskin');
			// $form->setDisplayFormat('vform');
			$form->setSubmitCallback([$this, 'saveStyle']);
			$form->show();
		} else {
			// display list
		}
	}

	public function saveStyle($data) {
		$s = new CategorySkin($data);
		if ($s->save()) {
			return true;
		}

		return 'Failed to save category style';
	}
}
