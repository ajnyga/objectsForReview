<?php

/**
 * @file plugins/generic/objectsForReview/api/v1/objectsForReview/ObjectsForReviewController.php
 *
 * Copyright (c) 2014-2026 Simon Fraser University
 * Copyright (c) 2003-2026 John Willinsky
 * Distributed under the GNU GPL v3. For full terms see the file docs/COPYING.
 *
 * @class ObjectsForReviewController
 * @ingroup plugins_generic_objectsForReview
 *
 * @brief API endpoints for objects for review.
 *
 * Context routes (managers) handle the objects offered for review.
 * Submission routes handle the objects linked to one submission.
 */

namespace APP\plugins\generic\objectsForReview\api\v1\objectsForReview;

use APP\core\Application;
use APP\plugins\generic\objectsForReview\classes\components\forms\ObjectForReviewForm;
use APP\plugins\generic\objectsForReview\classes\ObjectForReview;
use APP\plugins\generic\objectsForReview\classes\ObjectForReviewDAO;
use APP\plugins\generic\objectsForReview\classes\ObjectForReviewMapper;
use APP\plugins\generic\objectsForReview\classes\ObjectsForReviewAccess;
use APP\plugins\generic\objectsForReview\ObjectsForReviewPlugin;
use APP\submission\Submission;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Route;
use PKP\core\PKPBaseController;
use PKP\core\PKPRequest;
use PKP\db\DAORegistry;
use PKP\security\authorization\ContextAccessPolicy;
use PKP\security\authorization\SubmissionAccessPolicy;
use PKP\security\authorization\UserRolesRequiredPolicy;
use PKP\security\Role;

class ObjectsForReviewController extends PKPBaseController
{
    /** Maximum length of the identifier columns in the database */
    public const MAX_IDENTIFIER_LENGTH = 255;

    /** Actions that work on a single submission */
    public array $requiresSubmissionAccess = [
        'getSubmissionObjects',
        'addSubmissionObject',
        'editSubmissionObject',
        'removeSubmissionObject',
        'attachReservedObject',
    ];

    public function __construct(protected ObjectsForReviewPlugin $plugin)
    {
    }

    /**
     * @copydoc \PKP\core\PKPBaseController::getHandlerPath()
     */
    public function getHandlerPath(): string
    {
        return 'objectsForReview';
    }

    /**
     * @copydoc \PKP\core\PKPBaseController::getRouteGroupMiddleware()
     */
    public function getRouteGroupMiddleware(): array
    {
        return [
            'has.user',
            'has.context',
        ];
    }

    /**
     * @copydoc \PKP\core\PKPBaseController::getGroupRoutes()
     */
    public function getGroupRoutes(): void
    {
        // Objects offered for review by the journal (managers)
        Route::middleware([
            self::roleAuthorizer([
                Role::ROLE_ID_SITE_ADMIN,
                Role::ROLE_ID_MANAGER,
            ]),
        ])->group(function () {
            Route::get('', $this->getAvailableObjects(...))
                ->name('objectsForReview.getAvailable');

            Route::post('', $this->addAvailableObject(...))
                ->name('objectsForReview.addAvailable');

            Route::put('{objectId}', $this->editAvailableObject(...))
                ->name('objectsForReview.editAvailable')
                ->whereNumber('objectId');

            Route::delete('{objectId}', $this->deleteAvailableObject(...))
                ->name('objectsForReview.deleteAvailable')
                ->whereNumber('objectId');
        });

        // Objects linked to a submission (editors and authors)
        Route::middleware([
            self::roleAuthorizer([
                Role::ROLE_ID_SITE_ADMIN,
                Role::ROLE_ID_MANAGER,
                Role::ROLE_ID_SUB_EDITOR,
                Role::ROLE_ID_ASSISTANT,
                Role::ROLE_ID_AUTHOR,
            ]),
        ])->group(function () {
            Route::get('_components/objectForm', $this->getObjectForm(...))
                ->name('objectsForReview.getObjectForm');

            Route::get('submissions/{submissionId}', $this->getSubmissionObjects(...))
                ->name('objectsForReview.getSubmissionObjects')
                ->whereNumber('submissionId');

            Route::post('submissions/{submissionId}', $this->addSubmissionObject(...))
                ->name('objectsForReview.addSubmissionObject')
                ->whereNumber('submissionId');

            Route::put('submissions/{submissionId}/{objectId}', $this->editSubmissionObject(...))
                ->name('objectsForReview.editSubmissionObject')
                ->whereNumber(['submissionId', 'objectId']);

            Route::delete('submissions/{submissionId}/{objectId}', $this->removeSubmissionObject(...))
                ->name('objectsForReview.removeSubmissionObject')
                ->whereNumber(['submissionId', 'objectId']);

            Route::post('submissions/{submissionId}/attach/{objectId}', $this->attachReservedObject(...))
                ->name('objectsForReview.attachReservedObject')
                ->whereNumber(['submissionId', 'objectId']);
        });
    }

    /**
     * @copydoc \PKP\core\PKPBaseController::authorize()
     */
    public function authorize(PKPRequest $request, array &$args, array $roleAssignments): bool
    {
        $illuminateRequest = $args[0]; /** @var \Illuminate\Http\Request $illuminateRequest */
        $actionName = static::getRouteActionName($illuminateRequest);

        $this->addPolicy(new UserRolesRequiredPolicy($request), true);
        $this->addPolicy(new ContextAccessPolicy($request, $roleAssignments));

        if (in_array($actionName, $this->requiresSubmissionAccess)) {
            $this->addPolicy(new SubmissionAccessPolicy($request, $args, $roleAssignments));
        }

        return parent::authorize($request, $args, $roleAssignments);
    }

    //
    // Form
    //

    /**
     * Get the configuration of the add/edit form
     */
    public function getObjectForm(Request $illuminateRequest): JsonResponse
    {
        return response()->json((new ObjectForReviewForm())->getConfig(), Response::HTTP_OK);
    }

    //
    // Objects offered for review (managers)
    //

    /**
     * Get the objects offered for review that are not linked to a submission
     */
    public function getAvailableObjects(Request $illuminateRequest): JsonResponse
    {
        $contextId = $this->getRequest()->getContext()->getId();
        $objectsForReview = $this->getDao()->getAll($contextId, true, true)->toArray();

        return response()->json([
            'items' => ObjectForReviewMapper::mapMany($objectsForReview, true),
        ], Response::HTTP_OK);
    }

    /**
     * Add an object offered for review
     */
    public function addAvailableObject(Request $illuminateRequest): JsonResponse
    {
        $params = $this->getParams($illuminateRequest);
        if ($errors = $this->validateParams($params)) {
            return response()->json($errors, Response::HTTP_BAD_REQUEST);
        }

        $objectForReview = $this->getDao()->newDataObject();
        $objectForReview->setContextId($this->getRequest()->getContext()->getId());
        $objectForReview->setCreator('manager');
        $this->setFields($objectForReview, $params);
        $objectId = $this->getDao()->insertObject($objectForReview);

        return $this->objectResponse($objectId, true);
    }

    /**
     * Edit an object offered for review
     */
    public function editAvailableObject(Request $illuminateRequest): JsonResponse
    {
        $objectForReview = ObjectsForReviewAccess::getContextObject(
            $illuminateRequest->route('objectId'),
            $this->getRequest()->getContext()->getId()
        );
        if (!$objectForReview) {
            return $this->notFound();
        }

        $params = $this->getParams($illuminateRequest);
        if ($errors = $this->validateParams($params)) {
            return response()->json($errors, Response::HTTP_BAD_REQUEST);
        }

        $this->setFields($objectForReview, $params);
        $this->getDao()->updateObject($objectForReview);

        return $this->objectResponse($objectForReview->getId(), true);
    }

    /**
     * Delete an object offered for review
     */
    public function deleteAvailableObject(Request $illuminateRequest): JsonResponse
    {
        $objectForReview = ObjectsForReviewAccess::getContextObject(
            $illuminateRequest->route('objectId'),
            $this->getRequest()->getContext()->getId()
        );
        if (!$objectForReview) {
            return $this->notFound();
        }

        $data = ObjectForReviewMapper::map($objectForReview, true);
        $this->getDao()->deleteObject($objectForReview);

        return response()->json($data, Response::HTTP_OK);
    }

    //
    // Objects linked to a submission
    //

    /**
     * Get the objects linked to a submission, whether the user can edit them,
     * and the user's own reservations that can be attached
     */
    public function getSubmissionObjects(Request $illuminateRequest): JsonResponse
    {
        $submission = $this->getSubmission();
        $context = $this->getRequest()->getContext();
        $canEdit = $this->canAdminister($submission);

        $reserved = [];
        if ($canEdit) {
            $reserved = $this->getDao()
                ->getByUserId($this->getRequest()->getUser()->getId(), $context->getId(), true)
                ->toArray();
        }

        return response()->json([
            'items' => ObjectForReviewMapper::mapMany($this->getDao()->getBySubmissionId($submission->getId())->toArray()),
            'canEdit' => $canEdit,
            'onlyReserved' => (bool) $this->plugin->getSetting($context->getId(), 'onlyReserved'),
            'reserved' => ObjectForReviewMapper::mapMany($reserved),
        ], Response::HTTP_OK);
    }

    /**
     * Add an object entered by hand to a submission
     */
    public function addSubmissionObject(Request $illuminateRequest): JsonResponse
    {
        $submission = $this->getSubmission();
        $contextId = $this->getRequest()->getContext()->getId();

        if (!$this->canAdminister($submission)) {
            return $this->forbidden();
        }

        // Only objects created by the managers may be linked when this setting is on
        if ($this->plugin->getSetting($contextId, 'onlyReserved')) {
            return $this->forbidden();
        }

        $params = $this->getParams($illuminateRequest);
        if ($errors = $this->validateParams($params)) {
            return response()->json($errors, Response::HTTP_BAD_REQUEST);
        }

        $objectForReview = $this->getDao()->newDataObject();
        $objectForReview->setContextId($contextId);
        $objectForReview->setSubmissionId($submission->getId());
        $objectForReview->setCreator('workflow');
        $this->setFields($objectForReview, $params);
        $objectId = $this->getDao()->insertObject($objectForReview);

        return $this->objectResponse($objectId);
    }

    /**
     * Edit an object linked to a submission
     */
    public function editSubmissionObject(Request $illuminateRequest): JsonResponse
    {
        $submission = $this->getSubmission();

        if (!$this->canAdminister($submission)) {
            return $this->forbidden();
        }

        $objectForReview = ObjectsForReviewAccess::getSubmissionObject($illuminateRequest->route('objectId'), $submission->getId());
        if (!$objectForReview) {
            return $this->notFound();
        }

        $params = $this->getParams($illuminateRequest);
        if ($errors = $this->validateParams($params)) {
            return response()->json($errors, Response::HTTP_BAD_REQUEST);
        }

        $this->setFields($objectForReview, $params);
        $this->getDao()->updateObject($objectForReview);

        return $this->objectResponse($objectForReview->getId());
    }

    /**
     * Remove an object from a submission. Objects offered by the managers are
     * detached (and keep their reservation); objects entered by hand are deleted.
     */
    public function removeSubmissionObject(Request $illuminateRequest): JsonResponse
    {
        $submission = $this->getSubmission();

        if (!$this->canAdminister($submission)) {
            return $this->forbidden();
        }

        $objectForReview = ObjectsForReviewAccess::getSubmissionObject($illuminateRequest->route('objectId'), $submission->getId());
        if (!$objectForReview) {
            return $this->notFound();
        }

        $data = ObjectForReviewMapper::map($objectForReview);

        if ($objectForReview->getCreator() === 'manager') {
            $objectForReview->setSubmissionId(null);
            $this->getDao()->updateObject($objectForReview);
        } else {
            $this->getDao()->deleteObject($objectForReview);
        }

        return response()->json($data, Response::HTTP_OK);
    }

    /**
     * Link one of the current user's reservations to a submission
     */
    public function attachReservedObject(Request $illuminateRequest): JsonResponse
    {
        $submission = $this->getSubmission();
        $user = $this->getRequest()->getUser();

        if (!$this->canAdminister($submission)) {
            return $this->forbidden();
        }

        $objectForReview = ObjectsForReviewAccess::getContextObject(
            $illuminateRequest->route('objectId'),
            $this->getRequest()->getContext()->getId()
        );

        if (
            !$objectForReview
            || $objectForReview->getUserId() != $user->getId()
            || $objectForReview->getSubmissionId()
        ) {
            return $this->notFound();
        }

        $objectForReview->setSubmissionId($submission->getId());
        $this->getDao()->updateObject($objectForReview);

        return $this->objectResponse($objectForReview->getId());
    }

    //
    // Helpers
    //

    protected function getDao(): ObjectForReviewDAO
    {
        return DAORegistry::getDAO('ObjectForReviewDAO');
    }

    protected function getSubmission(): Submission
    {
        return $this->getAuthorizedContextObject(Application::ASSOC_TYPE_SUBMISSION);
    }

    protected function canAdminister(Submission $submission): bool
    {
        return ObjectsForReviewAccess::canAdminister(
            $submission,
            $this->getRequest()->getUser(),
            (array) $this->getAuthorizedContextObject(Application::ASSOC_TYPE_USER_ROLES)
        );
    }

    /**
     * Read the object fields from the request
     */
    protected function getParams(Request $illuminateRequest): array
    {
        $params = [];
        foreach (['resourceType', 'identifierType', 'identifier', 'authors', 'title', 'publisher', 'year'] as $field) {
            $value = $illuminateRequest->input($field);
            $params[$field] = is_string($value) ? trim($value) : '';
        }
        // The rich text editor may wrap the title in a paragraph
        $params['title'] = trim(str_replace(['<p>', '</p>'], '', $params['title']));
        return $params;
    }

    /**
     * Validate the object fields
     *
     * @return array Errors keyed by field name, in the format the form expects
     */
    protected function validateParams(array $params): array
    {
        $errors = [];

        foreach (['resourceType', 'identifierType', 'identifier', 'title'] as $field) {
            if ($params[$field] === '' || ($field === 'title' && trim(strip_tags($params['title'])) === '')) {
                $errors[$field] = [__('validator.required')];
            }
        }

        if (!isset($errors['resourceType']) && !array_key_exists($params['resourceType'], ObjectForReview::RESOURCE_TYPES)) {
            $errors['resourceType'] = [__('validator.in', ['attribute' => __('plugins.generic.objectsForReview.resourceType')])];
        }

        if (!isset($errors['identifierType']) && !array_key_exists($params['identifierType'], ObjectForReview::IDENTIFIER_TYPES)) {
            $errors['identifierType'] = [__('validator.in', ['attribute' => __('plugins.generic.objectsForReview.itemIdentifierType')])];
        }

        if (!isset($errors['identifier']) && mb_strlen($params['identifier']) > self::MAX_IDENTIFIER_LENGTH) {
            $errors['identifier'] = [__('plugins.generic.objectsForReview.validation.maxLength', ['length' => self::MAX_IDENTIFIER_LENGTH])];
        }

        return $errors;
    }

    /**
     * Copy validated fields to an object (does not change its creator, context, submission or reservation)
     */
    protected function setFields(ObjectForReview $objectForReview, array $params): void
    {
        $objectForReview->setResourceType($params['resourceType']);
        $objectForReview->setIdentifierType($params['identifierType']);
        $objectForReview->setIdentifier($params['identifier']);
        $objectForReview->setAuthors($params['authors']);
        $objectForReview->setTitle($params['title']);
        $objectForReview->setPublisher($params['publisher']);
        $objectForReview->setYear($params['year']);
    }

    protected function objectResponse(int $objectId, bool $includeReserver = false): JsonResponse
    {
        $objectForReview = $this->getDao()->getById($objectId);
        return response()->json(ObjectForReviewMapper::map($objectForReview, $includeReserver), Response::HTTP_OK);
    }

    protected function notFound(): JsonResponse
    {
        return response()->json(['error' => __('api.404.resourceNotFound')], Response::HTTP_NOT_FOUND);
    }

    protected function forbidden(): JsonResponse
    {
        return response()->json(['error' => __('api.403.unauthorized')], Response::HTTP_FORBIDDEN);
    }
}
