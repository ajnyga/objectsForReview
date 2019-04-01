{**
 * plugins/generic/objectsForReview/templates/editAvailableObjectsForReviewForm.tpl
 *
 * Copyright (c) 2014-2019 Simon Fraser University
 * Copyright (c) 2003-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * Form for editing an available objectsForReview item
 *}
<script>
	$(function() {ldelim}
		// Attach the form handler.
		$('#availableObjectsForReviewForm').pkpHandler('$.pkp.controllers.form.AjaxFormHandler');
	{rdelim});
</script>

{capture assign="actionUrl"}{url router=$smarty.const.ROUTE_COMPONENT component="plugins.generic.objectsForReview.controllers.grid.ObjectsForReviewManagementGridHandler" op="updateAvailableObjectForReview" escape=false}{/capture}

<form class="pkp_form" id="availableObjectsForReviewForm" method="post" action="{$actionUrl}">
	{csrf}
	{if $reviewId}
		<input type="hidden" name="reviewId" value="{$reviewId|escape}" />
	{/if}
	{fbvFormArea id="availableObjectsForReviewFormArea" class="border"}


		{fbvFormSection for="resourceType" label="plugins.generic.objectsForReview.resourceType"}
			{fbvElement type="select" id="resourceType" from=$resourceTypes selected=$resourceType size=$fbvStyles.size.SMALL}
		{/fbvFormSection}

		{fbvFormSection for="identifierType" label="plugins.generic.objectsForReview.itemIdentifierType"}
			{fbvElement type="select" id="identifierType" from=$identifierTypes selected=$identifierType translate=false size=$fbvStyles.size.SMALL} 
		{/fbvFormSection}

		{fbvFormSection label="plugins.generic.objectsForReview.itemIdentifier" for="identifier"}
			{fbvElement type="text" id="identifier" value=$identifier maxlength="255" inline=true multilingual=false size=$fbvStyles.size.MEDIUM}
		{/fbvFormSection}

		{fbvFormSection label="plugins.generic.objectsForReview.itemDescription" for="description"}
			{fbvElement type="textarea" multilingual=false name="description" id="description" value=$description rich=false height=$fbvStyles.height.TALL variables=$allowedVariables}
		{/fbvFormSection}

	{/fbvFormArea}

	{fbvFormSection class="formButtons"}
		{assign var=buttonId value="submitFormButton"|concat:"-"|uniqid}
		{fbvElement type="submit" class="submitFormButton" id=$buttonId label="common.save"}
	{/fbvFormSection}
</form>
