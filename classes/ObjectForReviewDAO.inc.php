<?php

/**
 * @file plugins/generic/objectsForReview/classes/classes/ObjectForReviewDAO.inc.php
 *
 * Copyright (c) 2014-2017 Simon Fraser University
 * Copyright (c) 2003-2017 John Willinsky
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
	 * Get a ObjectForReview by ID
	 * @param $funderId int Funder ID
	 * @param $submissionId int (optional) Submission ID
	 */
	function getById($funderId, $submissionId = null) {
		$params = array((int) $funderId);
		if ($submissionId) $params[] = (int) $submissionId;

		$result = $this->retrieve(
			'SELECT * FROM objects_for_review WHERE funder_id = ?'
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

}

?>
