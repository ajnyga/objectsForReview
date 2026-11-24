<?php

/**
 * @file plugins/generic/objectsForReview/controllers/grid/form/ObjectsForReviewForm.php
 *
 * Copyright (c) 2014-2026 Simon Fraser University
 * Copyright (c) 2003-2026 John Willinsky
 * Distributed under the GNU GPL v3. For full terms see the file docs/COPYING.
 *
 * @class ObjectsForReviewForm
 * @ingroup controllers_grid_objectsForReview
 *
 * Form for adding/editing an objectForReview
 *
 */
namespace APP\plugins\generic\objectsForReview\controllers\grid\form;

use APP\plugins\generic\objectsForReview\classes\ObjectForReview;
use APP\plugins\generic\objectsForReview\ObjectsForReviewPlugin;
use APP\template\TemplateManager;
use PKP\db\DAORegistry;

class ObjectsForReviewForm extends \PKP\form\Form {
	/** @var int Context ID */
	var $contextId;

	/** @var int Submission ID */
	var $submissionId;

    /** @var string objectId */
    public $objectId;

	/** @var ObjectsForReviewPlugin */
	var $plugin;

	/**
	 * Constructor
	 * @param $objectsForReviewPlugin ObjectsForReviewPlugin
	 * @param $contextId int Context ID
	 * @param $submissionId int Submission ID
	 * @param $objectId int (optional) Review ID
	 */
	function __construct($objectsForReviewPlugin, $contextId, $submissionId, $objectId = null) {
		parent::__construct($objectsForReviewPlugin->getTemplateResource('editObjectForReviewForm.tpl'));

		$this->contextId = $contextId;
		$this->submissionId = $submissionId;
		$this->objectId = $objectId;
		$this->plugin = $objectsForReviewPlugin;

		// Add form checks
		$this->addCheck(new \PKP\form\validation\FormValidator($this, 'identifierType', 'required', 'plugins.generic.objectsForReview.identifierTypeRequired'));
		$this->addCheck(new \PKP\form\validation\FormValidator($this, 'resourceType', 'required', 'plugins.generic.objectsForReview.resourceTypeRequired'));
		$this->addCheck(new \PKP\form\validation\FormValidator($this, 'title', 'required', 'plugins.generic.objectsForReview.titleRequired'));
		$this->addCheck(new \PKP\form\validation\FormValidator($this, 'identifier', 'required', 'plugins.generic.objectsForReview.identifierRequired'));
		$this->addCheck(new \PKP\form\validation\FormValidatorPost($this));
		$this->addCheck(new \PKP\form\validation\FormValidatorCSRF($this));

	}

	/**
	 * @copydoc Form::initData()
	 */
	function initData() {
		$this->setData('submissionId', $this->submissionId);
		if ($this->objectId) {
			$objectForReviewDao = DAORegistry::getDAO('ObjectForReviewDAO');
			$objectForReview = $objectForReviewDao->getById($this->objectId, $this->submissionId);
			$this->setData('identifierType', $objectForReview->getIdentifierType());
			$this->setData('resourceType', $objectForReview->getResourceType());
			$this->setData('identifier', $objectForReview->getIdentifier());
			$this->setData('authors', $objectForReview->getAuthors());
			$this->setData('title', $objectForReview->getTitle());
			$this->setData('year', $objectForReview->getYear());
			$this->setData('publisher', $objectForReview->getPublisher());
		}
	}

	/**
	 * @copydoc Form::readInputData()
	 */
	function readInputData() {
		$this->readUserVars(array('identifierType', 'resourceType', 'identifier', 'authors', 'title', 'year', 'publisher'));
	}

	/**
	 * @copydoc Form::fetch
	 */
	function fetch($request, $template = NULL, $display = false) {
		$templateMgr = TemplateManager::getManager();
		$currentUser = $request->getUser();
		$context = $request->getContext();

		// Get reserved objects for review
		$objectForReviewDao = DAORegistry::getDAO('ObjectForReviewDAO');
		$objectsForReview = $objectForReviewDao->getByUserId($currentUser->getId(), $context->getId(), true);

		if ($objectsForReview){
			$reservedObjects = array();
			while ($objectForReview = $objectsForReview->next()) {
				$objectId = $objectForReview->getId();
				$reservedObjects[$objectId] = array(
					'objectId' => $objectId,
					'description' => htmlspecialchars((string) $objectForReview->getAuthors()) . ': ' . $objectForReview->getTitle(),
					'userId' => $objectForReview->getUserId()
				);
			}
			$templateMgr->assign('reservedObjects', $reservedObjects);
		}

		if ($this->plugin->getSetting($context->getId(), 'onlyReserved')){
			$templateMgr->assign('onlyReserved', true);
		}

		$templateMgr->assign('objectId', $this->objectId);
		$templateMgr->assign('submissionId', $this->submissionId);
		$templateMgr->assign('identifierTypes', ObjectForReview::IDENTIFIER_TYPES);
		$templateMgr->assign('resourceTypes', ObjectForReview::RESOURCE_TYPES);

		return parent::fetch($request);
	}

	/**
	 * Save form values into the database
	 */
	function execute(...$functionArgs) {
		$objectId = $this->objectId;
		$objectForReviewDao = DAORegistry::getDAO('ObjectForReviewDAO');

		if ($objectId) {
			// Load and update an existing objectForReview
			$objectForReview = $objectForReviewDao->getById($this->objectId, $this->submissionId);
		} else {
			// Create a new objectForReview
			$objectForReview = $objectForReviewDao->newDataObject();
			$objectForReview->setContextId($this->contextId);
			$objectForReview->setSubmissionId($this->submissionId);
			$objectForReview->setCreator('workflow');
		}

		$objectForReview->setIdentifier($this->getData('identifier'));
		$objectForReview->setIdentifierType($this->getData('identifierType'));
		$objectForReview->setResourceType($this->getData('resourceType'));	
		$objectForReview->setAuthors($this->getData('authors'));
		$title = str_replace(['<p>', '</p>'], '', $this->getData('title'));
		$objectForReview->setTitle($title);
		$objectForReview->setYear($this->getData('year'));
		$objectForReview->setPublisher($this->getData('publisher'));

		if ($objectId) {
			$objectForReviewDao->updateObject($objectForReview);
			return $objectId;
		} else {
			return $objectForReviewDao->insertObject($objectForReview);
		}
	}
}
