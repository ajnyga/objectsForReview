<?php

/**
 * @file plugins/generic/objectsForReview/mailables/ObjectsForReviewNew.php
 *
 * Copyright (c) 2014-2026 Simon Fraser University
 * Copyright (c) 2000-2026 John Willinsky
 * Distributed under the GNU GPL v3. For full terms see the file docs/COPYING.
 *
 * @class ObjectsForReviewNew
 *
 * @ingroup plugins_generic_objectsForReview
 *
 * @brief Email sent to the editor when a user reserves an object for review
 */

namespace APP\plugins\generic\objectsForReview\mailables;

use APP\journal\Journal;
use PKP\mail\Mailable;
use PKP\mail\traits\Configurable;
use PKP\mail\traits\Sender;
use PKP\security\Role;


class ObjectsForReviewNew extends Mailable
{
    use Configurable;
    use Sender;

    protected static ?string $name = 'emails.ofrNewReservation.name';
    protected static ?string $description = 'emails.ofrNewReservation.description';
    protected static ?string $emailTemplateKey = 'OFR_NEW_RESERVATION';
    protected static array $toRoleIds = [Role::ROLE_ID_MANAGER, Role::ROLE_ID_SUB_EDITOR];

    public function __construct(Journal $context, array $emailParams)
    {
        parent::__construct([$context]);
        $this->addData($emailParams);
    }

}