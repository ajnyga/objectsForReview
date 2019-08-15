<?php

/**
 * @file plugins/generic/objectsForReview/pages/ObjectsForReviewHandler.inc.php
 *
 * Copyright (c) 2014-2019 Simon Fraser University
 * Copyright (c) 2003-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class ObjectsForReviewPlugin
 * @ingroup plugins_generic_objectsForReview
 * @brief Handle reader-facing router requests for the objects for review plugin
 */

import('classes.handler.Handler');

class ObjectsForReviewHandler extends Handler {

	/**
	 * @copydoc PKPHandler::authorize()
	 */
	function authorize($request, &$args, $roleAssignments) {
		import('lib.pkp.classes.security.authorization.ContextRequiredPolicy');
		$this->addPolicy(new ContextRequiredPolicy($request));
		return parent::authorize($request, $args, $roleAssignments);
	}

	/**
	 * View objects for review
	 *
	 * @param $args array [
	 *		@option string Section ID
	 *		@option string page number
 	 * ]
	 * @param $request PKPRequest
	 * @return null|JSONMessage
	 */
	public function index($args, $request) {
		$context = $request->getContext();
		$contextId = $context ? $context->getId() : CONTEXT_ID_NONE;
		$plugin = PluginRegistry::getPlugin('generic', 'ObjectsForReviewPlugin');
		$templateMgr = TemplateManager::getManager($request);
		$currentUser = $request->getUser();

		// Get the items and add the data to the grid
		$objectForReviewDao = DAORegistry::getDAO('ObjectForReviewDAO');
		$objectsForReview = $objectForReviewDao->getAll($context->getId(), true, true);
		$gridData = array();
		while ($objectForReview = $objectsForReview->next()) {
				$objectId = $objectForReview->getId();
				$gridData[$objectId] = array(
					'objectId' => $objectForReview->getId(),
					'identifierType' => $objectForReview->getIdentifierType(),
					'identifier' => $objectForReview->getIdentifier(),
					'description' => $objectForReview->getDescription(),
					'userId' => $objectForReview->getUserId()
				);
		}

		$templateMgr->assign(array('objectsForReview' => $gridData, 'currentUser' => $currentUser));

		return $templateMgr->display($plugin->getTemplateResource('frontend/pages/forReview.tpl'));
	}

	/**
	 * Reserve an object.
	 * @param $args array
	 * @param $request PKPRequest
	 * @return JSONMessage JSON object
	 */
	function reserveObject($args, $request) {
		if (!$request->checkCSRF()) fatalError('Error!');
		$objectId = (int) $request->getUserVar('objectId');
		$currentUser = $request->getUser();
		$currentContext = $request->getContext();
		$objectForReviewDao = DAORegistry::getDAO('ObjectForReviewDAO');
		$objectForReview = $objectForReviewDao->getById($objectId);
		if ($objectForReview && !$objectForReview->getUserId() && $currentContext->getId() == $objectForReview->getContextId()) {
			$objectForReview->setUserId($currentUser->getId());
			$objectForReviewDao->updateObject($objectForReview);
		}
		$request->redirect(null, 'for-review');
	}

	/**
	 * Reserve an object.
	 * @param $args array
	 * @param $request PKPRequest
	 * @return JSONMessage JSON object
	 */
	function cancelObject($args, $request) {
		if (!$request->checkCSRF()) fatalError('Error!');
		$objectId = (int) $request->getUserVar('objectId');
		$currentUser = $request->getUser();
		$currentContext = $request->getContext();
		$objectForReviewDao = DAORegistry::getDAO('ObjectForReviewDAO');
		$objectForReview = $objectForReviewDao->getById($objectId);
		if ($objectForReview && $objectForReview->getUserId() == $currentUser->getId() && $currentContext->getId() == $objectForReview->getContextId()) {
			$objectForReview->setUserId(null);
			$objectForReviewDao->updateObject($objectForReview);
		}
		$request->redirect(null, 'for-review');
	}
}
