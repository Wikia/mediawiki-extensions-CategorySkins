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

	static $form = [
		'cs_id' => [
			'class' => 'HTMLDynamicHiddenField',
			'default' => 0
		],
		'cs_category' => [
			'type' => 'text',
			'label' => 'Category',
			'required' => true
		],
		'cs_prefix' => [
			'type' => 'text',
			'label' => 'Title prefix'
		],
		'cs_suffix' => [
			'type' => 'text',
			'label' => 'Title suffix'
		],
		'cs_logo' => [
			'type' => 'text',
			'label' => 'Replace logo',
			'help' => 'Give the name of an uploaded file to replace the wiki logo for the pages in this category'
		],
		'cs_style' => [
			'type' => 'check',
			'label' => 'Apply CSS page',
			'help' => 'Will apply MediaWiki:CategoryName.css to style pages in the category'
		],
	];

	public function execute( $path ) {
		$this->setHeaders();
		$this->checkPermissions();
		$this->outputHeader();
		$this->getOutput()->addModules('ext.categoryskins.special');
		$db = wfGetDB(DB_SLAVE);

		if ($path) {
			$this->getOutput()->addHtml('<p>'.Html::element('a', ['href'=>$this->getTitle()->getLinkUrl()], 'back to skin list'));
		}

		switch ($path) {
		case 'edit':
			if ($this->getRequest()->getVal('id') && !$this->loadSkinForEdit()) {
				$this->getOutput()->addHtml('<p>Could not load skin for ID: '.$this->getRequest()->getVal('id'));
				return;
			}
			// hard-code the field names to remove the 'wp' prefix (ugh!)
			foreach(self::$form as $k => &$v) $v['name'] = $k;
			//create the form
			$form = new HTMLForm(self::$form, $this->getContext(), 'categoryskin');
			$form->setId('categoryskin')->setSubmitText('Save')->setWrapperLegendMsg('categoryskin');
			$form->setSubmitCallback([$this, 'saveStyle']);
			// check for submission, returns true if validation passes
			if ($form->show()) {
				// submission successful!
				$this->getOutput()->redirect($this->getTitle()->getLinkUrl());
			}
			break;

		case 'delete':
			if (!$this->getRequest()->getVal('id')) {
				$this->getOutput()->addHtml('<p>No id specified</p>');

			}
			if ($this->getRequest()->wasPosted()) {
				if ( $db->delete('category_skins', ['cs_id' => $this->getRequest()->getVal('id')]) ) {
					$this->getOutput()->redirect($this->getTitle()->getLinkUrl());
				} else {
					$this->getOutput()->addHtml('<p>Error while deleting skin</p>');
				}
			} else {
				$name = $db->selectField('category_skins', ['cs_category'], ['cs_id' => $this->getRequest()->getVal('id')]);
				$this->getOutput()->addHtml("<p>Confirm deletion of skin for Category:$name</p>");
				$this->getOutput()->addHtml('<form method="post"><button>Delete</button></form>');
			}
			break;

		default:
			// display list
			$res = $db->select(
				['category_skins'],
				['*'],
				[],
				__METHOD__,
				[ 'cs_category' => 'DESC' ]
			);
			$this->getOutput()->addHtml(Html::element('a', ['href'=>$this->getTitle('edit')->getLinkUrl()], 'New Category Skin'));
			$this->getOutput()->addHtml($this->styleTable($res));
		}
	}

	public function saveStyle($data) {
		$db = wfGetDB(DB_MASTER);
		if ($data['cs_id']) {
			$res = $db->update('category_skins', $data, [ 'cs_id' => $data['cs_id'] ], __METHOD__);
		} else {
			unset($data['cs_id']);
			$res = $db->insert('category_skins', $data, __METHOD__);
		}

		if ($res) {
			return true;
		}

		return 'Failed to save category skin';
	}

	private function loadSkinForEdit() {
		$id = $this->getRequest()->getVal('id');
		$db = wfGetDB(DB_SLAVE);
		$row = $db->selectRow('category_skins', ['*'], ['cs_id' => $id], __METHOD__);
		if ($row) {
			foreach (array_keys(self::$form) as $k) {
				$this->getRequest()->setVal($k, $row->$k);
			}
			return true;
		}
		return false;
	}

	/**
	 * Generate a style table for database results.
	 *
	 * @access	private
	 * @param	mixed	Database Result object or false for no results.
	 * @return	string
	 */
	private function styleTable($styles) {
		if (empty($styles) || !$styles->current()) {
			return '<p>'.wfMessage('cs_no_styles_exist')->escaped().'</p>';
		}
		$html = '
			<table class="wikitable">
				<thead>
					<tr>
						<th>'.wfMessage('cs_category_name')->escaped().'</td>
						<th>'.wfMessage('cs_title_prefix')->escaped().'</td>
						<th>'.wfMessage('cs_title_suffix')->escaped().'</td>
						<th>'.wfMessage('cs_logo')->escaped().'</td>
						<th>'.wfMessage('cs_stylesheet')->escaped().'</td>
						<th>'.wfMessage('cs_edit')->escaped().'</td>
						<th>'.wfMessage('cs_delete')->escaped().'</td>
					</th>
				</thead>
				<tbody>';
		foreach ($styles as $style) {
			$html .= '<tr>';
			$html .= Html::rawElement('td', [], Html::element('a', ['href'=>Title::newFromText('Category:'.$style->cs_category)->getLinkUrl()], $style->cs_category));
			$html .= Html::element('td', [], var_export($style->cs_prefix, true));
			$html .= Html::element('td', [], var_export($style->cs_suffix, true));

			// Logo
			if ($logo = Title::newFromText('File:'.$style->cs_logo)) {
				$html .= Html::rawElement('td', [], Html::element('a', ['href'=>$logo->getLinkUrl()], $style->cs_logo));
			} else {
				$html .= Html::element('td', [], htmlspecialchars($style->cs_logo));
			}

			// Stylesheet Link
			if ($style->cs_style) {
				$stylesheetPath = 'Mediawiki:'.$style->cs_category.'.css';
				$html .= Html::rawElement('td', [], Html::element('a', ['href'=>Title::newFromText($stylesheetPath)->getLinkURL()], $stylesheetPath));
			} else {
				$html .= Html::element('td', ['class' => 'table-center'], 'N/A');
			}

			$html .= Html::rawElement('td', ['class' => 'table-center'], Html::rawElement('a', ['href'=>$this->getTitle('edit')->getLinkUrl().'?id='.$style->cs_id], Curse::awesomeIcon('pencil')));
			$html .= Html::rawElement('td', ['class' => 'table-center'], Html::rawElement('a', ['href'=>$this->getTitle('delete')->getLinkUrl().'?id='.$style->cs_id], Curse::awesomeIcon('trash')));
		}
		$html .= '
				</tbody>
			</table>';

		return $html;
	}
}
