<?php

/**
 * @file plugins/generic/objectsForReview/controllers/grid/form/ObjectsForReviewForm.inc.php
 *
 * Copyright (c) 2014-2019 Simon Fraser University
 * Copyright (c) 2003-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class ObjectsForReviewForm
 * @ingroup controllers_grid_objectsForReview
 *
 * Form for adding/editing an objectForReview
 *
 */

import('lib.pkp.classes.form.Form');

class ObjectsForReviewForm extends Form {
	/** @var int Context ID */
	var $contextId;

	/** @var int Submission ID */
	var $submissionId;

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
		$this->addCheck(new FormValidator($this, 'identifierType', 'required', 'plugins.generic.objectsForReview.identifierTypeRequired'));
		$this->addCheck(new FormValidator($this, 'resourceType', 'required', 'plugins.generic.objectsForReview.resourceTypeRequired'));
		$this->addCheck(new FormValidator($this, 'title', 'required', 'plugins.generic.objectsForReview.titleRequired'));
		$this->addCheck(new FormValidator($this, 'identifier', 'required', 'plugins.generic.objectsForReview.identifierRequired'));
		$this->addCheck(new FormValidatorPost($this));
		$this->addCheck(new FormValidatorCSRF($this));

	}

	/**
	 * @copydoc Form::initData()
	 */
	function initData() {
		$this->setData('submissionId', $this->submissionId);
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
		$identifierTypes = $this->_getIdentifierTypes();
		$resourceTypes = $this->_getResourceTypes(null);
		$currentUser = $request->getUser();
		$context = $request->getContext();

		// Get reserved objects for review
		$objectForReviewDao = DAORegistry::getDAO('ObjectForReviewDAO');
		$objectsForReview = $objectForReviewDao->getByUserId($currentUser->getId(), NULL, true);
		if ($objectsForReview){
			$reservedObjects = array();
			while ($objectForReview = $objectsForReview->next()) {
				$objectId = $objectForReview->getId();
				$reservedObjects[$objectId] = array(
					'objectId' => $objectId,
					'description' => $objectForReview->getAuthors() . ": " . $objectForReview->getTitle(),
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
		$templateMgr->assign('identifierTypes', $identifierTypes);
		$templateMgr->assign('resourceTypes', $resourceTypes);

		return parent::fetch($request);
	}

	/**
	 * Save form values into the database
	 */
	function execute() {
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

	/**
	 * List identifierTypes 
	 * See Crossref Schema isReviewOf relation types and Datacite relationType
	 * @return array
	 */
	function _getIdentifierTypes() {
		return array(
				'DOI'=>'doi',
				'ISSN'=>'issn',
				'ISBN'=>'isbn',
				'Link'=>'uri',
				'urn'=>'urn',				
				'pmid'=>'pmid',
				'pmcid'=>'pmcid',
				'purl'=>'purl',
				'arxiv'=>'arxiv',
				'ark'=>'ark',
				'handle'=>'handle',
				'uuid'=>'uuid',
				'ecli'=>'ecli',
				'accession'=>'accession',
				'other'=>'other'
		);
	}

	/**
	 * Get a COAR Resource Type by URI. If $uri is null return all.
	 * @param $uri string
	 * @return mixed
	 */
	function _getResourceTypes($uri = null) {
		$resourceTypes = array(
				'http://purl.org/coar/resource_type/c_2f33' => 'plugins.generic.objectsForReview.COAR.book',
				'http://purl.org/coar/resource_type/c_3248' => 'plugins.generic.objectsForReview.COAR.bookPart',
				'http://purl.org/coar/resource_type/c_6501' => 'plugins.generic.objectsForReview.COAR.journalArticle',
				'http://purl.org/coar/resource_type/c_5794' => 'plugins.generic.objectsForReview.COAR.conferencePaper',
				'http://purl.org/coar/resource_type/c_46ec' => 'plugins.generic.objectsForReview.COAR.thesis',
				'http://purl.org/coar/resource_type/c_816b' => 'plugins.generic.objectsForReview.COAR.preprint',
				'http://purl.org/coar/resource_type/c_7ad9' => 'plugins.generic.objectsForReview.COAR.website',
				'http://purl.org/coar/resource_type/c_ddb1' => 'plugins.generic.objectsForReview.COAR.dataset',
				'http://purl.org/coar/resource_type/c_ddb1' => 'plugins.generic.objectsForReview.COAR.software',
				'http://purl.org/coar/resource_type/c_12cc' => 'plugins.generic.objectsForReview.COAR.cartographicMaterial',
				'http://purl.org/coar/resource_type/c_18cc' => 'plugins.generic.objectsForReview.COAR.sound'
		);
		if ($uri){
			return $resourceTypes[$uri];
		} else {
			return $resourceTypes;
		}
	}
}

?>
