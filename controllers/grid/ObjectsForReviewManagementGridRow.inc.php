<?php

/**
 * @file plugins/generic/objectsForReview/controllers/grid/ObjectsForReviewManagementGridRow.inc.php
 *
 * Copyright (c) 2014-2021 Simon Fraser University
 * Copyright (c) 2003-2021 John Willinsky
 * Distributed under the GNU GPL v3. For full terms see the file docs/COPYING.
 *
 * @class ObjectsForReviewManagementGridRow
 * @ingroup plugins_generic_objectsForReview
 *
 * @brief Handle ObjectsForReview management grid row requests.
 */

import('lib.pkp.classes.controllers.grid.GridRow');

class ObjectsForReviewManagementGridRow extends GridRow {

	/**
	 * Constructor
	 */
	function __construct($readOnly = false) {
		parent::__construct();
	}

	//
	// Overridden template methods
	//
	/**
	 * @copydoc GridRow::initialize()
	 */
	function initialize($request, $template = null) {
		parent::initialize($request, $template);
		$objectId = $this->getId();

		if (!empty($objectId)) {
			$router = $request->getRouter();

			// Create the "edit" action
			import('lib.pkp.classes.linkAction.request.AjaxModal');
			$this->addAction(
				new LinkAction(
					'edit',
					new AjaxModal(
						$router->url($request, null, null, 'editAvailableObjectForReview', null, array('objectId' => $objectId)),
						__('grid.action.edit'),
						'modal_edit',
						true),
					__('grid.action.edit'),
					'edit'
				)
			);

			// Create the "delete" action
			import('lib.pkp.classes.linkAction.request.RemoteActionConfirmationModal');
			$this->addAction(
				new LinkAction(
					'delete',
					new RemoteActionConfirmationModal(
						$request->getSession(),
						__('common.confirmDelete'),
						__('grid.action.delete'),
						$router->url($request, null, null, 'deleteAvailableObjectForReview', null, array('objectId' => $objectId)), 'modal_delete'
					),
					__('grid.action.delete'),
					'delete'
				)
			);
		}
	}
}

?>
