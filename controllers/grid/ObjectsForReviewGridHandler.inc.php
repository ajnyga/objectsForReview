<?php

/**
 * @file plugins/generic/objectsForReview/controllers/grid/ObjectsForReviewGridHandler.inc.php
 *
 * Copyright (c) 2014-2017 Simon Fraser University
 * Copyright (c) 2003-2017 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class ObjectsForReviewGridHandler
 * @ingroup plugins_generic_funding
 *
 * @brief Handle ObjectsForReview grid requests.
 */

import('lib.pkp.classes.controllers.grid.GridHandler');
import('plugins.generic.objectsForReview.controllers.grid.ObjectsForReviewGridRow');
import('plugins.generic.objectsForReview.controllers.grid.ObjectsForReviewGridCellProvider');

class ObjectsForReviewGridHandler extends GridHandler {
	static $plugin;

	/** @var boolean */
	var $_readOnly;

	/**
	 * Constructor
	 */
	function __construct() {
		parent::__construct();
		$this->addRoleAssignment(
			array(ROLE_ID_MANAGER, ROLE_ID_SUB_EDITOR, ROLE_ID_ASSISTANT, ROLE_ID_AUTHOR),
			array('fetchGrid', 'fetchRow', 'addObjectsForReview', 'editObjectsForReview', 'updateObjectsForReview', 'deleteObjectsForReview')
		);
	}

	//
	// Getters/Setters
	//
	/**
	 * Set the ObjectsForReview plugin.
	 * @param $plugin FundingPlugin
	 */
	static function setPlugin($plugin) {
		self::$plugin = $plugin;
	}

	/**
	 * Get the submission associated with this grid.
	 * @return Submission
	 */
	function getSubmission() {
		return $this->getAuthorizedContextObject(ASSOC_TYPE_SUBMISSION);
	}

	/**
	 * Get whether or not this grid should be 'read only'
	 * @return boolean
	 */
	function getReadOnly() {
		return $this->_readOnly;
	}

	/**
	 * Set the boolean for 'read only' status
	 * @param boolean
	 */
	function setReadOnly($readOnly) {
		$this->_readOnly = $readOnly;
	}


	//
	// Overridden template methods
	//

	/**
	 * @copydoc PKPHandler::authorize()
	 */
	function authorize($request, &$args, $roleAssignments) {
		import('lib.pkp.classes.security.authorization.SubmissionAccessPolicy');
		$this->addPolicy(new SubmissionAccessPolicy($request, $args, $roleAssignments));
		return parent::authorize($request, $args, $roleAssignments);
	}

	/**
	 * @copydoc Gridhandler::initialize()
	 */
	function initialize($request, $args = null) {
		parent::initialize($request, $args);

		$submission = $this->getSubmission();
		$submissionId = $submission->getId();

		// Set the grid details.
		$this->setTitle('plugins.generic.funding.fundingData');
		$this->setEmptyRowText('plugins.generic.funding.noneCreated');

		// Get the items and add the data to the grid
		$objectForReviewDao = DAORegistry::getDAO('ObjectForReviewDAO');
		$objectForReviewIterator = $objectForReviewDao->getBySubmissionId($submissionId);

		$gridData = array();

		/*
		while ($objectsForReview = $objectsForReviewIterator->next()) {
			$objectsForReviewId = $objectsForReview->getId();
			$objectsForReviewAwards = $objectsForReviewAwardDao->getObjectsForReviewAwardNumbersByObjectsForReviewId($objectsForReviewId);
			$gridData[$objectsForReviewId] = array(
				'objectsForReviewName' => $objectsForReview->getObjectsForReviewName(),
				'objectsForReviewIdentification' => $objectsForReview->getObjectsForReviewIdentification(),
				'objectsForReviewGrants' => implode(";", $objectsForReviewAwards)
			);
		}
		*/

		$this->setGridDataElements($gridData);

		if ($this->canAdminister($request->getUser())) {
			$this->setReadOnly(false);
			// Add grid-level actions
			$router = $request->getRouter();
			import('lib.pkp.classes.linkAction.request.AjaxModal');
			$this->addAction(
				new LinkAction(
					'addObjectsForReview',
					new AjaxModal(
						$router->url($request, null, null, 'addObjectsForReview', null, array('submissionId' => $submissionId)),
						__('plugins.generic.funding.addObjectsForReview'),
						'modal_add_item'
					),
					__('plugins.generic.funding.addObjectsForReview'),
					'add_item'
				)
			);
		} else {
			$this->setReadOnly(true);
		}

		// Columns
		$cellProvider = new ObjectsForReviewGridCellProvider();
		$this->addColumn(new GridColumn(
			'objectsForReviewName',
			'plugins.generic.funding.objectsForReviewName',
			null,
			'controllers/grid/gridCell.tpl',
			$cellProvider
		));
		$this->addColumn(new GridColumn(
			'objectsForReviewIdentification',
			'plugins.generic.funding.objectsForReviewIdentification',
			null,
			'controllers/grid/gridCell.tpl',
			$cellProvider
		));
		$this->addColumn(new GridColumn(
			'objectsForReviewGrants',
			'plugins.generic.funding.objectsForReviewGrants',
			null,
			'controllers/grid/gridCell.tpl',
			$cellProvider
		));

	}

	//
	// Overridden methods from GridHandler
	//
	/**
	 * @copydoc Gridhandler::getRowInstance()
	 */
	function getRowInstance() {
		return new ObjectsForReviewGridRow($this->getReadOnly());
	}

	/**
	 * @copydoc GridHandler::getJSHandler()
	 */
	#public function getJSHandler() {
	#	return '$.pkp.plugins.generic.funding.ObjectsForReviewGridHandler';
	#}

	//
	// Public Grid Actions
	//
	/**
	 * An action to add a new 
	 ForReview item
	 * @param $args array Arguments to the request
	 * @param $request PKPRequest
	 */
	function addObjectsForReview($args, $request) {
		// Calling editObjectsForReviewitem with an empty ID will add
		// a new ObjectsForReview item.
		return $this->editObjectsForReview($args, $request);
	}

	/**
	 * An action to edit a objectsForReview
	 * @param $args array Arguments to the request
	 * @param $request PKPRequest
	 * @return string Serialized JSON object
	 */
	function editObjectsForReview($args, $request) {
		$objectsForReviewId = $request->getUserVar('objectsForReviewId');
		$context = $request->getContext();
		$submission = $this->getSubmission();
		$submissionId = $submission->getId();

		$this->setupTemplate($request);

		// Create and present the edit form
		import('plugins.generic.funding.controllers.grid.form.ObjectsForReviewForm');
		$objectsForReviewForm = new ObjectsForReviewForm(self::$plugin, $context->getId(), $submissionId, $objectsForReviewId);
		$objectsForReviewForm->initData();
		$json = new JSONMessage(true, $objectsForReviewForm->fetch($request));
		return $json->getString();
	}

	/**
	 * Update a objectsForReview
	 * @param $args array
	 * @param $request PKPRequest
	 * @return string Serialized JSON object
	 */
	function updateObjectsForReview($args, $request) {
		$objectsForReviewId = $request->getUserVar('objectsForReviewId');
		$context = $request->getContext();
		$submission = $this->getSubmission();
		$submissionId = $submission->getId();

		$this->setupTemplate($request);

		// Create and populate the form
		import('plugins.generic.funding.controllers.grid.form.ObjectsForReviewForm');
		$objectsForReviewForm = new ObjectsForReviewForm(self::$plugin, $context->getId(), $submissionId, $objectsForReviewId);
		$objectsForReviewForm->readInputData();
		// Validate
		if ($objectsForReviewForm->validate()) {
			// Save
			$objectsForReview = $objectsForReviewForm->execute();
 			return DAO::getDataChangedEvent($submissionId);
		} else {
			// Present any errors
			$json = new JSONMessage(true, $objectsForReviewForm->fetch($request));
			return $json->getString();
		}
	}

	/**
	 * Delete a objectsForReview
	 * @param $args array
	 * @param $request PKPRequest
	 * @return string Serialized JSON object
	 */
	function deleteObjectsForReview($args, $request) {
		$objectForReviewId = $request->getUserVar('objectForReviewId');
		$submission = $this->getSubmission();
		$submissionId = $submission->getId();

		$objectForReviewDao = DAORegistry::getDAO('ObjectForReviewDAO');
		$objectsForReview = $objectForReviewDao->getById($objectForReviewId, $submissionId);

		$objectForReviewDao->deleteObject($objectsForReview);
		return DAO::getDataChangedEvent($submissionId);
	}

	/**
	 * Determines if there should be add/edit actions on this grid.
	 * @param $user User
	 * @return boolean
	 */
	function canAdminister($user) {
		$submission = $this->getSubmission();

		// Incomplete submissions can be edited. (Presumably author.)
		if ($submission->getDateSubmitted() == null) return true;

		// Managers should always have access.
		$userRoles = $this->getAuthorizedContextObject(ASSOC_TYPE_USER_ROLES);
		if (array_intersect(array(ROLE_ID_MANAGER), $userRoles)) return true;

		// Sub editors and assistants need to be assigned to the current stage.
		$stageAssignmentDao = DAORegistry::getDAO('StageAssignmentDAO');
		$stageAssignments = $stageAssignmentDao->getBySubmissionAndStageId($submission->getId(), $submission->getStageId(), null, $user->getId());
		$userGroupDao = DAORegistry::getDAO('UserGroupDAO');
		while ($stageAssignment = $stageAssignments->next()) {
			$userGroup = $userGroupDao->getById($stageAssignment->getUserGroupId());
			if (in_array($userGroup->getRoleId(), array(ROLE_ID_SUB_EDITOR, ROLE_ID_ASSISTANT))) return true;
		}

		// Default: Read-only.
		return false;
	}

}

?>