<?php

/**
 * @file plugins/generic/objectsForReview/classes/components/forms/ObjectForReviewForm.php
 *
 * Copyright (c) 2014-2026 Simon Fraser University
 * Copyright (c) 2003-2026 John Willinsky
 * Distributed under the GNU GPL v3. For full terms see the file docs/COPYING.
 *
 * @class ObjectForReviewForm
 * @ingroup plugins_generic_objectsForReview
 *
 * @brief Form for adding or editing an object for review. Its action and
 *   method are set by the Vue component that opens it.
 */

namespace APP\plugins\generic\objectsForReview\classes\components\forms;

use APP\plugins\generic\objectsForReview\classes\ObjectForReview;
use PKP\components\forms\FieldRichText;
use PKP\components\forms\FieldSelect;
use PKP\components\forms\FieldText;
use PKP\components\forms\FormComponent;

class ObjectForReviewForm extends FormComponent
{
    public const FORM_OBJECT_FOR_REVIEW = 'objectForReview';
    public $id = self::FORM_OBJECT_FOR_REVIEW;
    public $method = 'POST';

    public function __construct(string $action = 'emit')
    {
        $this->action = $action;
        $this->locales = [];

        $resourceTypes = [];
        foreach (ObjectForReview::RESOURCE_TYPES as $uri => $localeKey) {
            $resourceTypes[] = ['value' => $uri, 'label' => __($localeKey)];
        }

        $identifierTypes = [];
        foreach (ObjectForReview::IDENTIFIER_TYPES as $value => $label) {
            $identifierTypes[] = ['value' => $value, 'label' => $label];
        }

        $this->addField(new FieldSelect('resourceType', [
            'label' => __('plugins.generic.objectsForReview.resourceType'),
            'options' => $resourceTypes,
            'isRequired' => true,
            'value' => array_key_first(ObjectForReview::RESOURCE_TYPES),
        ]))
            ->addField(new FieldSelect('identifierType', [
                'label' => __('plugins.generic.objectsForReview.itemIdentifierType'),
                'options' => $identifierTypes,
                'isRequired' => true,
                'value' => array_key_first(ObjectForReview::IDENTIFIER_TYPES),
            ]))
            ->addField(new FieldText('identifier', [
                'label' => __('plugins.generic.objectsForReview.itemIdentifier'),
                'isRequired' => true,
                'size' => 'large',
                'value' => '',
            ]))
            ->addField(new FieldText('authors', [
                'label' => __('plugins.generic.objectsForReview.itemAuthors'),
                'size' => 'large',
                'value' => '',
            ]))
            ->addField(new FieldRichText('title', [
                'label' => __('plugins.generic.objectsForReview.itemTitle'),
                'isRequired' => true,
                'value' => '',
            ]))
            ->addField(new FieldText('publisher', [
                'label' => __('plugins.generic.objectsForReview.itemPublisher'),
                'size' => 'large',
                'value' => '',
            ]))
            ->addField(new FieldText('year', [
                'label' => __('plugins.generic.objectsForReview.itemYear'),
                'size' => 'small',
                'value' => '',
            ]));
    }
}
