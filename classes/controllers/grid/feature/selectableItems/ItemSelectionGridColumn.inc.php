<?php
/**
 * @file classes/controllers/grid/feature/selectableItems/ItemSelectionGridColumn.inc.php
 *
 * Copyright (c) 2014-2016 Simon Fraser University Library
 * Copyright (c) 2000-2016 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class ItemSelectionGridColumn
 * @ingroup classes_controllers_grid_feature_selectableItems
 *
 * @brief Implements a column with checkboxes to select grid items.
 */

import('lib.pkp.classes.controllers.grid.GridColumn');

class ItemSelectionGridColumn extends GridColumn {

	/** @var string */
	var $_selectName;


	/**
	 * Constructor
	 * @param $selectName string The name of the form parameter
	 *  to which the selected files will be posted.
	 */
	function ItemSelectionGridColumn($selectName) {
		assert(is_string($selectName) && !empty($selectName));
		$this->_selectName = $selectName;

		import('lib.pkp.classes.controllers.grid.ColumnBasedGridCellProvider');
		$cellProvider = new ColumnBasedGridCellProvider();
		parent::GridColumn('select', 'common.select', null, 'controllers/grid/gridRowSelectInput.tpl', $cellProvider,
				array('width' => 1));
	}


	//
	// Getters and Setters
	//
	/**
	 * Get the select name.
	 * @return string
	 */
	function getSelectName() {
		return $this->_selectName;
	}


	//
	// Public methods
	//
	/**
	 * Method expected by ColumnBasedGridCellProvider
	 * to render a cell in this column.
	 *
	 * @see ColumnBasedGridCellProvider::getTemplateVarsFromRowColumn()
	 */
	function getTemplateVarsFromRow($row) {
		// Return the data expected by the column's cell template.
		return array(
			'elementId' => $row->getId(),
			'selectName' => $this->getSelectName(),
			'selected' => $row->getFlag('selected'));
	}
}

?>
