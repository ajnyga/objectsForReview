<?php

/**
 * @file plugins/generic/objectsForReview/classes/ObjectForReview.inc.php
 *
 * Copyright (c) 2014-2017 Simon Fraser University
 * Copyright (c) 2003-2017 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class Funder
 * @ingroup plugins_generic_objectsForReview
 *
 * Data object representing a funder.
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
	 * Get identification.
	 * @return string
	 */
	function getFunderIdentification() {
		return $this->getData('funderIdentification');
	}

	/**
	 * Set identification.
	 * @param $funderIdentification string
	 */
	function setFunderIdentification($funderIdentification) {
		return $this->setData('funderIdentification', $funderIdentification);
	}


}

?>
