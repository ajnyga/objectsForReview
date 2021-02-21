 <?php

/**
 * @file plugins/generic/objectsForReview/controllers/grid/ObjectsForReviewManagementGridCellProvider.inc.php
 *
 * Copyright (c) 2014-2021 Simon Fraser University
 * Copyright (c) 2003-2021 John Willinsky
 * Distributed under the GNU GPL v3. For full terms see the file docs/COPYING.
 *
 * @class ObjectsForReviewManagementGridCellProvider
 * @ingroup plugins_generic_objectsForReview
 *
 * @brief Class for a cell provider to display information about objectsForReview items
 */

import('lib.pkp.classes.controllers.grid.GridCellProvider');

class ObjectsForReviewManagementGridCellProvider extends GridCellProvider {

	//
	// Template methods from GridCellProvider
	//

	/**
	 * Extracts variables for a given column from a data element
	 * so that they may be assigned to template before rendering.
	 *
	 * @copydoc GridCellProvider::getTemplateVarsFromRowColumn()
	 */
	function getTemplateVarsFromRowColumn($row, $column) {
		$objectsForReviewItem = $row->getData();
		switch ($column->getId()) {
			case 'identifierType':
				return array('label' => $objectsForReviewItem['identifierType']);
			case 'identifier':
				return array('label' => $objectsForReviewItem['identifier']);
			case 'description':
				return array('label' => $objectsForReviewItem['description']);
			case 'user':
				return array('label' => $objectsForReviewItem['user']);	
		}
	}
}

?>
