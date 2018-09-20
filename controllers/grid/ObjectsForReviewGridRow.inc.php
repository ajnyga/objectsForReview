<?php

/**
 * @file plugins/generic/objectsForReview/controllers/grid/ObjectForReviewGridRow.inc.php
 *
 * Copyright (c) 2014-2017 Simon Fraser University
 * Copyright (c) 2003-2017 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class ObjectForReviewGridRow
 * @ingroup plugins_generic_funding
 *
 * @brief Handle ObjectForReview grid row requests.
 */

import('lib.pkp.classes.controllers.grid.GridRow');

class ObjectForReviewGridRow extends GridRow {
	/** @var boolean */
	var $_readOnly;

	/**
	 * Constructor
	 */
	function __construct($readOnly = false) {
		$this->_readOnly = $readOnly;
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
		$ObjectForReviewId = $this->getId();
		$submissionId = $request->getUserVar('submissionId');

		if (!empty($ObjectForReviewId) && !$this->isReadOnly()) {
			$router = $request->getRouter();

			// Create the "edit" action
			import('lib.pkp.classes.linkAction.request.AjaxModal');
			$this->addAction(
				new LinkAction(
					'editObjectForReviewItem',
					new AjaxModal(
						$router->url($request, null, null, 'editObjectForReview', null, array('ObjectForReviewId' => $ObjectForReviewId, 'submissionId' => $submissionId)),
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
						$router->url($request, null, null, 'deleteObjectForReview', null, array('ObjectForReviewId' => $ObjectForReviewId, 'submissionId' => $submissionId)), 'modal_delete'
					),
					__('grid.action.delete'),
					'delete'
				)
			);
		}
	}

	/**
	 * Determine if this grid row should be read only.
	 * @return boolean
	 */
	function isReadOnly() {
		return $this->_readOnly;
	}
}

?>
