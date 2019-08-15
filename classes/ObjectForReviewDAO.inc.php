<?php

/**
 * @file plugins/generic/objectsForReview/classes/classes/ObjectForReviewDAO.inc.php
 *
 * Copyright (c) 2014-2019 Simon Fraser University
 * Copyright (c) 2003-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class ObjectForReviewDAO
 * @ingroup plugins_generic_objectsForReview
 *
 * Operations for retrieving and modifying ObjectForReview objects.
 */

import('lib.pkp.classes.db.DAO');
import('plugins.generic.objectsForReview.classes.ObjectForReview');

class ObjectForReviewDAO extends DAO {

	/**
	 * Get a object for objectForReview by ID
	 * @param $objectId int ObjectForReview ID
	 * @param $submissionId int (optional) Submission ID
	 */
	function getById($objectId, $submissionId = null) {
		$params = array((int) $objectId);
		if ($submissionId) $params[] = (int) $submissionId;

		$result = $this->retrieve(
			'SELECT * FROM objects_for_review WHERE object_id = ?'
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
	 * Get a object for objectForReview by submission ID
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
	 * Retrieve all objects
	 * @param $contextId int required
	 * @param $withoutReviewOnly true if only objects without a review should be included
	 * @return DAOResultFactory containing matching Contexts
	 */
	function getAll($contextId, $withoutReviewOnly = false) {
		$result = $this->retrieve(
			'SELECT * FROM objects_for_review WHERE context_id = ?'
			. ($withoutReviewOnly?' AND submission_id IS NULL':''),
			$contextId
		);
		return new DAOResultFactory($result, $this, '_fromRow');
	}

	/**
	 * Insert a object for review.
	 * @param $objectForReview ObjectForReview
	 * @return int Inserted objectForReview ID
	 */
	function insertObject($objectForReview) {

		$this->update(
			'INSERT INTO objects_for_review (submission_id, context_id, user_id, identifier, identifier_type, resource_type, description) VALUES (?, ?, ?, ?, ?, ?, ?)',
			array(
				(int) $objectForReview->getSubmissionId(),
				(int) $objectForReview->getContextId(),
				(int) $objectForReview->getUserId(),
				$objectForReview->getIdentifier(),
				$objectForReview->getIdentifierType(),
				$objectForReview->getResourceType(),
				$objectForReview->getDescription()
			)
		);
		
		$objectForReview->setId($this->getInsertId());
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
				user_id = ?,
				identifier = ?,
				identifier_type = ?,
				resource_type = ?,
				description = ?
			WHERE object_id = ?',
			array(
				(int) $objectForReview->getContextId(),
				(int) $objectForReview->getUserId(),
				$objectForReview->getIdentifier(),
				$objectForReview->getIdentifierType(),
				$objectForReview->getResourceType(),
				$objectForReview->getDescription(),
				(int) $objectForReview->getId()
			)
		);
	}

	/**
	 * Delete a objectForReview by ID.
	 * @param $objectForReviewId int
	 */
	function deleteById($objectId) {
		$this->update(
			'DELETE FROM objects_for_review WHERE object_id = ?',
			(int) $objectId
		);

		$this->update(
			'DELETE FROM objects_for_review WHERE object_id = ?',
			(int) $objectId
		);
	}

	/**
	 * Delete a objectForReview object.
	 * @param $objectForReview ObjectForReview
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
	 * @return ObjectForReview
	 */
	function _fromRow($row) {
		$objectForReview = $this->newDataObject();
		$objectForReview->setId($row['object_id']);
		$objectForReview->setIdentifier($row['identifier']);
		$objectForReview->setIdentifierType($row['identifier_type']);
		$objectForReview->setResourceType($row['resource_type']);
		$objectForReview->setDescription($row['description']);
		$objectForReview->setContextId($row['context_id']);
		$objectForReview->setUserId($row['user_id']);
		$objectForReview->setSubmissionId($row['submission_id']);
		return $objectForReview;
	}

	/**
	 * Get the insert ID for the last inserted objectForReview.
	 * @return int
	 */
	function getInsertId() {
		return $this->_getInsertId('objects_for_review', 'object_id');
	}

}

?>
