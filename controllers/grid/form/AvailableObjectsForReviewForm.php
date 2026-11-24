<?php

/**
 * @file plugins/generic/objectsForReview/controllers/grid/form/AvailableObjectsForReviewForm.php
 *
 * Copyright (c) 2014-2026 Simon Fraser University
 * Copyright (c) 2003-2026 John Willinsky
 * Distributed under the GNU GPL v3. For full terms see the file docs/COPYING.
 *
 * @class AvailableObjectsForReviewForm
 * @ingroup controllers_grid_objectsForReviewManager
 *
 * Form for adding/editing an available objectForReview
 *
 */
namespace APP\plugins\generic\objectsForReview\controllers\grid\form;

use APP\plugins\generic\objectsForReview\classes\ObjectForReview;
use APP\plugins\generic\objectsForReview\ObjectsForReviewPlugin;
use APP\template\TemplateManager;
use PKP\db\DAORegistry;

class AvailableObjectsForReviewForm extends \PKP\form\Form {
	/** @var int Context ID */
	var $contextId;

    /** @var string objectId */
    public $objectId;

	/** @var ObjectsForReviewPlugin */
	var $plugin;

	/**
	 * Constructor
	 * @param $objectsForReviewPlugin ObjectsForReviewPlugin
	 * @param $contextId int Context ID
	 * @param $objectId int (optional) Review ID
	 */
	function __construct($objectsForReviewPlugin, $contextId, $objectId = null) {
		parent::__construct($objectsForReviewPlugin->getTemplateResource('editAvailableObjectForReviewForm.tpl'));

		$this->contextId = $contextId;
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
		if ($this->objectId) {
			$objectForReviewDao = DAORegistry::getDAO('ObjectForReviewDAO');
			$objectForReview = $objectForReviewDao->getById($this->objectId);
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
		$templateMgr->assign('objectId', $this->objectId);
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
			$objectForReview = $objectForReviewDao->getById($this->objectId);
		} else {
			// Create a new objectForReview
			$objectForReview = $objectForReviewDao->newDataObject();
			$objectForReview->setContextId($this->contextId);
			$objectForReview->setCreator('manager');
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
		} else {
			$objectForReview = $objectForReviewDao->insertObject($objectForReview);
		}

	}
}
