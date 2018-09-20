<?php

/**
 * @file plugins/generic/objectsForReview/controllers/grid/ObjectsForReviewGridCellProvider.inc.php
 *
 * Copyright (c) 2014-2018 Simon Fraser University
 * Copyright (c) 2003-2018 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class ObjectsForReviewGridCellProvider
 * @ingroup plugins_generic_objectsForReview
 *
 * @brief Class for a cell provider to display information about objectsForReview items
 */

import('lib.pkp.classes.controllers.grid.GridCellProvider');

class ObjectsForReviewGridCellProvider extends GridCellProvider {

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
			case 'objectsForReviewName':
				return array('label' => $objectsForReviewItem['objectsForReviewName']);
			case 'objectsForReviewIdentification':
				return array('label' => $objectsForReviewItem['objectsForReviewIdentification']);
			case 'objectsForReviewGrants':
				return array('label' => $objectsForReviewItem['objectsForReviewGrants']);
		}
	}
}

?>
