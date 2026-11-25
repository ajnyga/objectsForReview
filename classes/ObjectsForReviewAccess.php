<?php

/**
 * @file plugins/generic/objectsForReview/classes/ObjectsForReviewAccess.php
 *
 * Copyright (c) 2014-2026 Simon Fraser University
 * Copyright (c) 2003-2026 John Willinsky
 * Distributed under the GNU GPL v3. For full terms see the file docs/COPYING.
 *
 * @class ObjectsForReviewAccess
 * @ingroup plugins_generic_objectsForReview
 *
 * @brief Permission and ownership checks shared by the plugin's API and handlers.
 */

namespace APP\plugins\generic\objectsForReview\classes;

use APP\submission\Submission;
use PKP\db\DAORegistry;
use PKP\security\Role;
use PKP\stageAssignment\StageAssignment;
use PKP\user\User;

class ObjectsForReviewAccess
{
    /**
     * Can the user add, edit or remove objects for review on this submission?
     *
     * @param int[] $userRoles The user's roles in the current context
     */
    public static function canAdminister(Submission $submission, User $user, array $userRoles): bool
    {
        // Submissions still in the submission wizard can be edited (by the author).
        if ($submission->getData('submissionProgress')) {
            return true;
        }

        // Managers always have access.
        if (in_array(Role::ROLE_ID_MANAGER, $userRoles)) {
            return true;
        }

        // Sub editors and assistants need to be assigned to the current stage.
        return StageAssignment::withSubmissionIds([$submission->getId()])
            ->withStageIds([(int) $submission->getData('stageId')])
            ->withUserId($user->getId())
            ->withRoleIds([Role::ROLE_ID_SUB_EDITOR, Role::ROLE_ID_ASSISTANT])
            ->exists();
    }

    /**
     * Get an object for review that belongs to the given context, or null.
     */
    public static function getContextObject(mixed $objectId, int $contextId): ?ObjectForReview
    {
        if (!$objectId) {
            return null;
        }
        /** @var ObjectForReviewDAO $dao */
        $dao = DAORegistry::getDAO('ObjectForReviewDAO');
        $objectForReview = $dao->getById((int) $objectId);
        return ($objectForReview && $objectForReview->getContextId() == $contextId) ? $objectForReview : null;
    }

    /**
     * Get an object for review that is attached to the given submission, or null.
     */
    public static function getSubmissionObject(mixed $objectId, int $submissionId): ?ObjectForReview
    {
        if (!$objectId) {
            return null;
        }
        /** @var ObjectForReviewDAO $dao */
        $dao = DAORegistry::getDAO('ObjectForReviewDAO');
        return $dao->getById((int) $objectId, $submissionId);
    }
}
