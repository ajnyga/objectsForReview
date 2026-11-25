<?php

/**
 * @file plugins/generic/objectsForReview/classes/ObjectForReview.php
 *
 * Copyright (c) 2014-2026 Simon Fraser University
 * Copyright (c) 2003-2026 John Willinsky
 * Distributed under the GNU GPL v3. For full terms see the file docs/COPYING.
 *
 * @class ObjectForReview
 * @ingroup plugins_generic_objectForReview
 *
 * Data object representing a ObjectForReview.
 */

namespace APP\plugins\generic\objectsForReview\classes;

class ObjectForReview extends \PKP\core\DataObject {

	/** Identifier types (stored value => label). See Crossref isReviewOf and DataCite relationType. */
	public const IDENTIFIER_TYPES = [
		'DOI' => 'doi',
		'ISSN' => 'issn',
		'ISBN' => 'isbn',
		'Link' => 'uri',
		'urn' => 'urn',
		'pmid' => 'pmid',
		'pmcid' => 'pmcid',
		'purl' => 'purl',
		'arxiv' => 'arxiv',
		'ark' => 'ark',
		'handle' => 'handle',
		'uuid' => 'uuid',
		'ecli' => 'ecli',
		'accession' => 'accession',
		'other' => 'other',
	];

	/** COAR resource types (URI => locale key). */
	public const RESOURCE_TYPES = [
		'http://purl.org/coar/resource_type/c_2f33' => 'plugins.generic.objectsForReview.COAR.book',
		'http://purl.org/coar/resource_type/c_3248' => 'plugins.generic.objectsForReview.COAR.bookPart',
		'http://purl.org/coar/resource_type/c_6501' => 'plugins.generic.objectsForReview.COAR.journalArticle',
		'http://purl.org/coar/resource_type/c_5794' => 'plugins.generic.objectsForReview.COAR.conferencePaper',
		'http://purl.org/coar/resource_type/c_46ec' => 'plugins.generic.objectsForReview.COAR.thesis',
		'http://purl.org/coar/resource_type/c_816b' => 'plugins.generic.objectsForReview.COAR.preprint',
		'http://purl.org/coar/resource_type/c_7ad9' => 'plugins.generic.objectsForReview.COAR.website',
		'http://purl.org/coar/resource_type/c_ddb1' => 'plugins.generic.objectsForReview.COAR.dataset',
		'http://purl.org/coar/resource_type/c_5ce6' => 'plugins.generic.objectsForReview.COAR.software',
		'http://purl.org/coar/resource_type/c_12cc' => 'plugins.generic.objectsForReview.COAR.cartographicMaterial',
		'http://purl.org/coar/resource_type/c_18cc' => 'plugins.generic.objectsForReview.COAR.sound',
	];

	//
	// Get/set methods
	//

	/**
	 * Get context ID.
	 * @return int
	 */
	function getContextId(){
		return $this->getData('contextId');
	}

	/**
	 * Set context ID.
	 * @param $contextId int
	 */
	function setContextId($contextId) {
		return $this->setData('contextId', $contextId);
	}

	/**
	 * Get submission ID.
	 * @return int
	 */
	function getSubmissionId(){
		return $this->getData('submissionId');
	}

	/**
	 * Set submission ID.
	 * @param $submissionId int
	 */
	function setSubmissionId($submissionId) {
		return $this->setData('submissionId', $submissionId);
	}

	/**
	 * Get user ID.
	 * @return int
	 */
	function getUserId(){
		return $this->getData('userId');
	}

	/**
	 * Set user ID.
	 * @param $userId int
	 */
	function setUserId($userId) {
		return $this->setData('userId', $userId);
	}	

	/**
	 * Get objectId.
	 * @return string
	 */
	public function getId(): ?int
	{
		return $this->getData('objectId');
	}

	/**
	 * Set objectId.
	 * @param $objectId string
	 */ 
	function setId($objectId) {
		return $this->setData('objectId', $objectId);
	}

	/**
	 * Get identifier.
	 * @return string
	 */
	function getIdentifier() {
		return $this->getData('identifier');
	}

	/**
	 * Set identifier.
	 * @param $identifier string
	 */
	function setIdentifier($identifier) {
		return $this->setData('identifier', $identifier);
	}

	/**
	 * Get identifierType.
	 * @return string
	 */
	function getIdentifierType() {
		return $this->getData('identifierType');
	}

	/**
	 * Set identifierType.
	 * @param $identifierType string
	 */
	function setIdentifierType($identifierType) {
		return $this->setData('identifierType', $identifierType);
	}

	/**
	 * Get resourceType.
	 * @return string
	 */
	function getResourceType() {
		return $this->getData('resourceType');
	}

	/**
	 * Set resourceType.
	 * @param $resourceType string
	 */
	function setResourceType($resourceType) {
		return $this->setData('resourceType', $resourceType);
	}

	/**
	 * Get description as HTML.
	 *
	 * Plain-text fields (authors, publisher, year) are escaped; the title is
	 * rich text, so the result must still be sanitized on output.
	 *
	 * @return string
	 */
	function getDescription() {
		$description = (string) $this->getData('title');
		if ($this->getData('authors'))
			$description = htmlspecialchars($this->getData('authors')) . ': ' . $description;
		if ($this->getData('publisher'))
			$description = $description . '. ' . htmlspecialchars($this->getData('publisher'));
		if ($this->getData('year'))
			$description = $description . '. ' . htmlspecialchars($this->getData('year')) . '.';
		return $description;
	}

	/**
	 * Get authors.
	 * @return string
	 */
	function getAuthors() {
		return $this->getData('authors');
	}

	/**
	 * Set authors.
	 * @param $authors string
	 */
	function setAuthors($authors) {
		return $this->setData('authors', $authors);
	}

	/**
	 * Set title.
	 * @param $title string
	 */
	function setTitle($title) {
		return $this->setData('title', $title);
	}	

	/**
	 * Get title.
	 * @return string
	 */
	function getTitle() {
		return $this->getData('title');
	}

	/**
	 * Set publisher.
	 * @param $publisher string
	 */
	function setPublisher($publisher) {
		return $this->setData('publisher', $publisher);
	}

	/**
	 * Get publisher.
	 * @return string
	 */
	function getPublisher() {
		return $this->getData('publisher');
	}	

	/**
	 * Set year.
	 * @param $year string
	 */
	function setYear($year) {
		return $this->setData('year', $year);
	}

	/**
	 * Get year.
	 * @return string
	 */
	function getYear() {
		return $this->getData('year');
	}

	/**
	 * Get creator.
	 * @return string
	 */
	function getCreator() {
		return $this->getData('creator');
	}

	/**
	 * Set creator.
	 * @param $creator string
	 */
	function setCreator($creator) {
		return $this->setData('creator', $creator);
	}

	/**
	 * Get dateCreated.
	 * @return string
	 */
	function getDateCreated() {
		return $this->getData('dateCreated');
	}

	/**
	 * Set dateCreated.
	 * @param $dateCreated string
	 */
	function setDateCreated($dateCreated) {
		return $this->setData('dateCreated', $dateCreated);
	}	

}
