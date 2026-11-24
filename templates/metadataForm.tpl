{**
 * plugins/generic/objectsForReview/templates/metadataForm.tpl
 *
 * Copyright (c) 2014-2026 Simon Fraser University
 * Copyright (c) 2003-2026 John Willinsky
 * Distributed under the GNU GPL v3. For full terms see the file docs/COPYING.
 *
 * The included template that is hooked into Templates::Submission::SubmissionMetadataForm::AdditionalMetadata.
 *}

{if array_intersect(array(\PKP\security\Role::ROLE_ID_MANAGER, \PKP\security\Role::ROLE_ID_SUB_EDITOR, \PKP\security\Role::ROLE_ID_ASSISTANT, \PKP\security\Role::ROLE_ID_AUTHOR), (array)$userRoles)}
<div id="objectsForReview">
  {capture assign="objectsForReviewGridUrl"}{url router=PKP\core\PKPApplication::ROUTE_COMPONENT component="plugins.generic.objectsForReview.controllers.grid.ObjectsForReviewGridHandler" op="fetchGrid" submissionId=$submissionId escape=false}{/capture}
  {load_url_in_div id="objectsForReviewGridContainer"|uniqid url=$objectsForReviewGridUrl}
</div>
{/if}
