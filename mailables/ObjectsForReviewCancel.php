<?php

/**
 * @file classes/mail/mailables/ManualPaymentNotify.php
 *
 * Copyright (c) 2014-2022 Simon Fraser University
 * Copyright (c) 2000-2022 John Willinsky
 * Distributed under the GNU GPL v3. For full terms see the file docs/COPYING.
 *
 * @class ManualPaymentNotify
 *
 * @ingroup mail_mailables
 *
 * @brief Email is sent automatically to notify journal manager about new payment that needs to be processed
 */

namespace APP\plugins\generic\objectsForReview\mailables;

use APP\journal\Journal;
use PKP\mail\Mailable;
use PKP\mail\traits\Configurable;
use PKP\mail\traits\Sender;
use PKP\security\Role;
use PKP\user\User;

class ObjectsForReviewCancel extends Mailable
{
    use Configurable;
    use Sender;

    protected static ?string $name = 'emails.ofrCancelReservation.name';
    protected static ?string $description = 'emails.ofrCancelReservation.description';
    protected static ?string $emailTemplateKey = 'OFR_CANCEL_RESERVATION';
    protected static array $toRoleIds = [Role::ROLE_ID_MANAGER, Role::ROLE_ID_SUB_EDITOR];

    public function __construct(Journal $context, array $emailParams)
    {
        parent::__construct([$context]);
        $this->addData($emailParams);
    }
}