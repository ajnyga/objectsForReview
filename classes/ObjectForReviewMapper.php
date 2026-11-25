<?php

/**
 * @file plugins/generic/objectsForReview/classes/ObjectForReviewMapper.php
 *
 * Copyright (c) 2014-2026 Simon Fraser University
 * Copyright (c) 2003-2026 John Willinsky
 * Distributed under the GNU GPL v3. For full terms see the file docs/COPYING.
 *
 * @class ObjectForReviewMapper
 * @ingroup plugins_generic_objectsForReview
 *
 * @brief Convert objects for review to the array shape used by the API and the Vue components.
 */

namespace APP\plugins\generic\objectsForReview\classes;

use APP\facades\Repo;
use PKP\core\PKPString;

class ObjectForReviewMapper
{
    /**
     * Map one object for review.
     *
     * @param bool $includeReserver Add the name and email of the user who reserved it
     */
    public static function map(ObjectForReview $objectForReview, bool $includeReserver = false): array
    {
        $data = [
            'id' => (int) $objectForReview->getId(),
            'identifier' => $objectForReview->getIdentifier(),
            'identifierType' => $objectForReview->getIdentifierType(),
            'resourceType' => $objectForReview->getResourceType(),
            'authors' => $objectForReview->getAuthors(),
            'title' => $objectForReview->getTitle(),
            'publisher' => $objectForReview->getPublisher(),
            'year' => $objectForReview->getYear(),
            'creator' => $objectForReview->getCreator(),
            'submissionId' => $objectForReview->getSubmissionId() ? (int) $objectForReview->getSubmissionId() : null,
            // Sanitized HTML: plain fields are escaped, the rich-text title is purified
            'description' => PKPString::stripUnsafeHtml($objectForReview->getDescription()),
        ];

        if ($includeReserver) {
            $user = $objectForReview->getUserId() ? Repo::user()->get((int) $objectForReview->getUserId(), true) : null;
            $data['reservedBy'] = $user ? ['fullName' => $user->getFullName(), 'email' => $user->getEmail()] : null;
        }

        return $data;
    }

    /**
     * Map many objects for review.
     *
     * @param iterable<ObjectForReview> $objectsForReview
     */
    public static function mapMany(iterable $objectsForReview, bool $includeReserver = false): array
    {
        $items = [];
        foreach ($objectsForReview as $objectForReview) {
            $items[] = self::map($objectForReview, $includeReserver);
        }
        return $items;
    }
}
