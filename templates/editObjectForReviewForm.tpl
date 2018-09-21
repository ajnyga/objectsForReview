{**
 * plugins/generic/objectsForReview/templates/editFunderForm.tpl
 *
 * Copyright (c) 2014-2018 Simon Fraser University
 * Copyright (c) 2003-2018 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * Form for editing a objectsForReview item
 *}
<script>
	$(function() {ldelim}
		// Attach the form handler.
		$('#objectsForReviewForm').pkpHandler('$.pkp.controllers.form.AjaxFormHandler');
	{rdelim});
</script>

{capture assign="actionUrl"}{url router=$smarty.const.ROUTE_COMPONENT component="plugins.generic.objectsForReview.controllers.grid.ObjectsForReviewGridHandler" op="updateObjectForReview" submissionId=$submissionId escape=false}{/capture}

<form class="pkp_form" id="objectsForReviewForm" method="post" action="{$actionUrl}">
	{csrf}
	{if $reviewId}
		<input type="hidden" name="reviewId" value="{$reviewId|escape}" />
	{/if}
	{fbvFormArea id="objectsForReviewFormArea" class="border"}


		{fbvFormSection for="identifierType" label="plugins.generic.objectsForReview.itemIdentifierType"}
			{fbvElement type="select" id="identifierType" from=$identifierTypes selected=$identifierType translate=false size=$fbvStyles.size.SMALL}
		{/fbvFormSection}

		{fbvFormSection label="plugins.generic.objectsForReview.itemIdentifier" for="identifier"}
			{fbvElement type="text" id="identifier" value=$identifier maxlength="255" inline=true multilingual=false size=$fbvStyles.size.MEDIUM}
		{/fbvFormSection}

		{fbvFormSection label="plugins.generic.objectsForReview.itemDescription" for="description"}
			{fbvElement type="textarea" multilingual=true name="description" id="description" value=$description rich=true height=$fbvStyles.height.TALL variables=$allowedVariables}
		{/fbvFormSection}


	{/fbvFormArea}


	{fbvFormSection class="formButtons"}
		{assign var=buttonId value="submitFormButton"|concat:"-"|uniqid}
		{fbvElement type="submit" class="submitFormButton" id=$buttonId label="common.save"}
	{/fbvFormSection}
</form>