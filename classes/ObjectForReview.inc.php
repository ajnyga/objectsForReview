<?php

/**
 * @file plugins/generic/objectsForReview/classes/ObjectForReview.inc.php
 *
 * Copyright (c) 2014-2019 Simon Fraser University
 * Copyright (c) 2003-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class ObjectForReview
 * @ingroup plugins_generic_objectForReview
 *
 * Data object representing a ObjectForReview.
 */

class ObjectForReview extends DataObject {

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
	function getId() {
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
	 * Get description.
	 * @return string
	 */
	function getDescription() {
		return $this->getData('description');
	}

	/**
	 * Set description.
	 * @param $description string
	 */
	function setDescription($description) {
		return $this->setData('description', $description);
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

}

?>
