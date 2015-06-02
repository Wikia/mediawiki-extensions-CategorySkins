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
	/**
	 * Main constructor
	 */
	public function __construct() {
		parent::__construct(
			'CategorySkins', // name
			'skincategories', // required user right
			true // display on Special:Specialpages
		);
	}

	/**
	 * Get group name
	 * @return string
	 */
	public function getGroupName() {
		return 'pages';
	}

	/**
	 * Form fields
	 * @var array
	 */
	static $form = [
		'cs_id' => [
			'class' => 'HTMLDynamicHiddenField',
			'default' => 0
		],
		'cs_category' => [
			'type' => 'text',
			'label' => 'Category',
			'validation-callback'  => ['CategorySkin', 'validateCategory'],
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

	/**
	 * Main executor
	 *
	 * @param null|string $path
	 * @throws DBUnexpectedError
	 */
	public function execute( $path = null ) {
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
				$this->getOutput()->addHtml('<p>'.wfMessage("cs_error_load_skin_for_id", $this->getRequest()->getVal('id'))->text());
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
				$this->getOutput()->addHtml('<p>'.wfMessage("cs_error_no_id")->text().'</p>');

			}
			if ($this->getRequest()->wasPosted()) {
				if ( $db->delete('category_skins', ['cs_id' => $this->getRequest()->getVal('id')]) ) {
					$this->getOutput()->redirect($this->getTitle()->getLinkUrl());
				} else {
					$this->getOutput()->addHtml('<p>'.wfMessage("cs_error_deleting_skin")->text().'</p>');
				}
			} else {
				$name = $db->selectField('category_skins', ['cs_category'], ['cs_id' => $this->getRequest()->getVal('id')]);
				$this->getOutput()->addHtml("<p>".wfMessage("cs_confirm_skin_delete", $name)->text()."</p>");
				$this->getOutput()->addHtml('<form method="post"><button>'.wfMessage("cs_delete")->text().'</button></form>');
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

	/**
	 * Save skin details
	 *
	 * @param $data
	 * @return bool|string
	 */
	public function saveStyle($data) {
		$db = wfGetDB(DB_MASTER);
		$title = Title::newFromText($data['cs_category']);
		$data['cs_category'] = $title->getPrefixedDBkey();
		if ($data['cs_id']) {
			$res = $db->update('category_skins', $data, [ 'cs_id' => $data['cs_id'] ], __METHOD__);
		} else {
			unset($data['cs_id']);
			$res = $db->insert('category_skins', $data, __METHOD__);
		}

		if ($res) {
			return true;
		}

		return wfMessage("cs_error_failed_skin_save")->text();
	}

	/**
	 * Request skin data and load it
	 * 
	 * @return bool
	 */
	private function loadSkinForEdit() {
		$id = $this->getRequest()->getVal('id');
		$db = wfGetDB(DB_SLAVE);
		$row = $db->selectRow('category_skins', ['*'], ['cs_id' => $id], __METHOD__);
		if ($row) {
			foreach (array_keys(self::$form) as $k) {
				if ($k == 'cs_category') {
					$row->$k = Title::newFromText($row->$k);
				}
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
			$html .= Html::rawElement('td', [], Html::element('a', ['href'=>Title::newFromText('Category:'.$style->cs_category)->getLinkUrl()], Title::newFromText($style->cs_category)->getPrefixedText()));
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
				$html .= Html::rawElement('td', [], Html::element('a', ['href'=>Title::newFromText($stylesheetPath)->getLinkURL()], Title::newFromText($stylesheetPath)->getPrefixedText()));
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
