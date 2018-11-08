<?php

/**
 * @file plugins/generic/objectsForReview/controllers/grid/form/ObjectsForReviewForm.inc.php
 *
 * Copyright (c) 2014-2018 Simon Fraser University
 * Copyright (c) 2003-2018 John Willinsky
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
	 * @param $reviewId int (optional) Review ID
	 */
	function __construct($objectsForReviewPlugin, $contextId, $submissionId, $reviewId = null) {
		parent::__construct($objectsForReviewPlugin->getTemplateResource('editObjectForReviewForm.tpl'));

		$this->contextId = $contextId;
		$this->submissionId = $submissionId;
		$this->reviewId = $reviewId;
		$this->plugin = $objectsForReviewPlugin;

		// Add form checks
		$this->addCheck(new FormValidator($this, 'identifierType', 'required', 'plugins.generic.objectsForReview.identifierTypeRequired'));
		$this->addCheck(new FormValidator($this, 'itemType', 'required', 'plugins.generic.objectsForReview.itemTypeRequired'));
		$this->addCheck(new FormValidator($this, 'description', 'required', 'plugins.generic.objectsForReview.descriptionRequired'));
		$this->addCheck(new FormValidator($this, 'identifier', 'required', 'plugins.generic.objectsForReview.identifierRequired'));
		$this->addCheck(new FormValidatorPost($this));
		$this->addCheck(new FormValidatorCSRF($this));

	}

	/**
	 * @copydoc Form::initData()
	 */
	function initData() {
		$this->setData('submissionId', $this->submissionId);
		if ($this->reviewId) {
			$objectForReviewDao = DAORegistry::getDAO('ObjectForReviewDAO');
			$objectForReview = $objectForReviewDao->getById($this->reviewId);
			$this->setData('identifierType', $objectForReview->getIdentifierType());
			$this->setData('itemType', $objectForReview->getItemType());
			$this->setData('description', $objectForReview->getDescription());
			$this->setData('identifier', $objectForReview->getIdentifier());
		}
	}

	/**
	 * @copydoc Form::readInputData()
	 */
	function readInputData() {
		$this->readUserVars(array('identifierType', 'itemType', 'description', 'identifier'));
	}

	/**
	 * @copydoc Form::fetch
	 */
	function fetch($request) {
		$templateMgr = TemplateManager::getManager();
		$identifierTypes = $this->getIdentifierTypes();
		$itemTypes = $this->getItemTypes();

		$templateMgr->assign('reviewId', $this->reviewId);
		$templateMgr->assign('submissionId', $this->submissionId);
		$templateMgr->assign('identifierTypes', $identifierTypes);
		$templateMgr->assign('itemTypes', $itemTypes);

		return parent::fetch($request);
	}

	/**
	 * Save form values into the database
	 */
	function execute() {
		$reviewId = $this->reviewId;
		$objectForReviewDao = DAORegistry::getDAO('ObjectForReviewDAO');

		if ($reviewId) {
			// Load and update an existing objectForReview
			$objectForReview = $objectForReviewDao->getById($this->reviewId, $this->submissionId);
		} else {
			// Create a new objectForReview
			$objectForReview = $objectForReviewDao->newDataObject();
			$objectForReview->setContextId($this->contextId);
			$objectForReview->setSubmissionId($this->submissionId);
		}

		$objectForReview->setIdentifier($this->getData('identifier'));
		$objectForReview->setIdentifierType($this->getData('identifierType'));
		$objectForReview->setItemType($this->getData('itemType'));
		$objectForReview->setDescription($this->getData('description'));

		if ($reviewId) {
			$objectForReviewDao->updateObject($objectForReview);
		} else {
			$objectForReview = $objectForReviewDao->insertObject($objectForReview);
		}
	}

	/**
	 * List identifierTypes 
	 * See Crossref Schema isReviewOf relation types and Datacite relationType
	 */
	function getIdentifierTypes() {
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
	 * List itemTypes 
	 * See https://www.zotero.org/support/kb/item_types_and_fields
	 */
	function getItemTypes() {
		return array(
			__('plugins.generic.objectsForReview.item.book') => 'Book',
			__('plugins.generic.objectsForReview.item.bookSection') => 'Book Section',
			__('plugins.generic.objectsForReview.item.artwork') => 'Artwork',
			__('plugins.generic.objectsForReview.item.audioRecording') => 'Audio Recording',
			__('plugins.generic.objectsForReview.item.bill') => 'Bill',
			__('plugins.generic.objectsForReview.item.blogPost') => 'Blog Post',
			__('plugins.generic.objectsForReview.item.case') => 'Case',
			__('plugins.generic.objectsForReview.item.computerProgram') => 'Computer Program',
			__('plugins.generic.objectsForReview.item.conferencePaper') => 'Conference Paper',
			__('plugins.generic.objectsForReview.item.dictionaryEntry') => 'Dictionary Entry',
			__('plugins.generic.objectsForReview.item.document') => 'Document',
			__('plugins.generic.objectsForReview.item.email') => 'Email',
			__('plugins.generic.objectsForReview.item.encyclopediaArticle') => 'Encyclopedia Article',
			__('plugins.generic.objectsForReview.item.film') => 'Film',
			__('plugins.generic.objectsForReview.item.forumPost') => 'Forum Post',
			__('plugins.generic.objectsForReview.item.hearing') => 'Hearing',
			__('plugins.generic.objectsForReview.item.instantMessage') => 'Instant Message',
			__('plugins.generic.objectsForReview.item.interview') => 'Interview',
			__('plugins.generic.objectsForReview.item.journalArticle') => 'Journal Article',
			__('plugins.generic.objectsForReview.item.letter') => 'Letter',
			__('plugins.generic.objectsForReview.item.magazineArticle') => 'Magazine Article',
			__('plugins.generic.objectsForReview.item.manuscript') => 'Manuscript',
			__('plugins.generic.objectsForReview.item.map') => 'Map',
			__('plugins.generic.objectsForReview.item.newspaperArticle') => 'Newspaper Article',
			__('plugins.generic.objectsForReview.item.patent') => 'Patent',
			__('plugins.generic.objectsForReview.item.podcast') => 'Podcast',
			__('plugins.generic.objectsForReview.item.presentation') => 'Presentation',
			__('plugins.generic.objectsForReview.item.radioBroadcast') => 'Radio Broadcast',
			__('plugins.generic.objectsForReview.item.report') => 'Report',
			__('plugins.generic.objectsForReview.item.statute') => 'Statute',
			__('plugins.generic.objectsForReview.item.thesis') => 'Thesis',
			__('plugins.generic.objectsForReview.item.TvBroadcast') => 'TV Broadcast',
			__('plugins.generic.objectsForReview.item.videoRecording') => 'Video Recording',
			__('plugins.generic.objectsForReview.item.webpage') => 'Webpage',
			__('plugins.generic.objectsForReview.item.attachment') => 'Attachment',
			__('plugins.generic.objectsForReview.item.note') => 'Note'
		);
	}

}

?>
