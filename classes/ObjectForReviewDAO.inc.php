<?php

/**
 * @file plugins/generic/funding/classes/classes/FunderDAO.inc.php
 *
 * Copyright (c) 2014-2017 Simon Fraser University
 * Copyright (c) 2003-2017 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class FunderDAO
 * @ingroup plugins_generic_funding
 *
 * Operations for retrieving and modifying ObjectForReview objects.
 */

import('lib.pkp.classes.db.DAO');
import('plugins.generic.objectsForReview.classes.ObjectForReview');

class ObjectForReviewDAO extends DAO {

	/**
	 * Get a object for review by ID
	 * @param $reviewId int ObjectForReview ID
	 * @param $submissionId int (optional) Submission ID
	 */
	function getById($reviewId, $submissionId = null) {
		$params = array((int) $reviewId);
		if ($submissionId) $params[] = (int) $submissionId;

		$result = $this->retrieve(
			'SELECT * FROM objects_for_review WHERE review_id = ?'
			. ($submissionId?' AND submission_id = ?':''),
			$params
		);

		$returner = null;
		if ($result->RecordCount() != 0) {
			$returner = $this->_fromRow($result->GetRowAssoc(false));
		}
		$result->Close();
		return $returner;
	}

	/**
	 * Get a object for review by submission ID
	 * @param $submissionId int Submission ID
	 * @param $contextId int (optional) context ID
	 */
	function getBySubmissionId($submissionId, $contextId = null) {
		$params = array((int) $submissionId);
		if ($contextId) $params[] = (int) $contextId;

		$result = $this->retrieve(
			'SELECT * FROM objects_for_review WHERE submission_id = ?'
			. ($contextId?' AND context_id = ?':''),
			$params
		);

		return new DAOResultFactory($result, $this, '_fromRow');
	}

	/**
	 * Insert a object for review.
	 * @param $funder ObjectForReview
	 * @return int Inserted objectForReview ID
	 */
	function insertObject($objectForReview) {

		$this->update(
			'INSERT INTO objects_for_review (submission_id, context_id, identifier, identifier_type, description) VALUES (?, ?, ?, ?, ?)',
			array(
				(int) $objectForReview->getSubmissionId(),
				(int) $objectForReview->getContextId(),
				$objectForReview->getIdentifier(),
				$objectForReview->getIdentifierType(),
				$objectForReview->getDescription()
			)
		);
		
		$objectForReview->setId($this->getInsertId());
		#$this->updateLocaleFields($objectForReview);
		return $objectForReview->getId();

	}

	/**
	 * Update the database with a objectForReview object
	 * @param $objectForReview ObjectForReview
	 */
	function updateObject($objectForReview) {
		$this->update(
			'UPDATE	objects_for_review
			SET	context_id = ?,
				identifier = ?,
				identifier_type = ?,
				description = ?
			WHERE review_id = ?',
			array(
				(int) $objectForReview->getContextId(),
				$objectForReview->getIdentifier(),
				$objectForReview->getIdentifierType(),
				$objectForReview->getDescription(),
				(int) $objectForReview->getId()
			)
		);
		#$this->updateLocaleFields($objectForReview);
	}

	/**
	 * Delete a objectForReview by ID.
	 * @param $objectForReviewId int
	 */
	function deleteById($reviewId) {

		$this->update(
			'DELETE FROM objects_for_review WHERE review_id = ?',
			(int) $reviewId
		);

		$this->update(
			'DELETE FROM objects_for_review WHERE review_id = ?',
			(int) $reviewId
		);
	}

	/**
	 * Delete a objectForReview object.
	 * @param $objectForReviewId ObjectForReviewId
	 */
	function deleteObject($objectForReview) {
		$this->deleteById($objectForReview->getId());
	}

	/**
	 * Generate a new funder object.
	 * @return ObjectForReview
	 */
	function newDataObject() {
		return new ObjectForReview();
	}

	/**
	 * Return a new funder object from a given row.
	 * @return Funder
	 */
	function _fromRow($row) {
		$objectForReview = $this->newDataObject();


		$objectForReview->setId($row['review_id']);
		$objectForReview->setIdentifier($row['identifier']);
		$objectForReview->setIdentifierType($row['identifier_type']);
		$objectForReview->setDescription($row['description']);
		$objectForReview->setContextId($row['context_id']);
		$objectForReview->setSubmissionId($row['submission_id']);

		#$this->getDataObjectSettings('funder_settings', 'funder_id', $row['funder_id'], $funder);

		return $objectForReview;
	}

	/**
	 * Get the insert ID for the last inserted funder.
	 * @return int
	 */
	function getInsertId() {
		return $this->_getInsertId('objects_for_review', 'review_id');
	}


	/**
	 * Get the additional field names.
	 * @return array
	 */
	#function getAdditionalFieldNames() {
	#	return array('description');
	#}


}

?>
