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

{url|assign:actionUrl router=$smarty.const.ROUTE_COMPONENT component="plugins.generic.objectsForReview.controllers.grid.ObjectsForReviewGridHandler" op="updateObjectForReview" submissionId=$submissionId escape=false}
<form class="pkp_form" id="objectsForReviewForm" method="post" action="{$actionUrl}">
	{csrf}
	{if $funderId}
		<input type="hidden" name="funderId" value="{$funderId|escape}" />
	{/if}
	{fbvFormArea id="objectsForReviewFormArea" class="border"}
		{fbvFormSection}
			<span id="funderError" class="error" style="display:none">{translate key="plugins.generic.objectsForReview.funderNameIdentificationRequired.registry"}</span>
			{fbvElement type="hidden" class="funderNameIdentification" label="plugins.generic.objectsForReview.funderNameIdentification" id="funderNameIdentification" value=$funderNameIdentification maxlength="255" inline=true size=$fbvStyles.size.LARGE}
			<span>{translate key="plugins.generic.objectsForReview.funderNameIdentification"}</span>
		{/fbvFormSection}
		{fbvFormSection}
			{fbvElement type="hidden" class="funderAwards" label="plugins.generic.objectsForReview.funderGrants" id="funderAwards" value=$funderAwards maxlength="255" inline=true size=$fbvStyles.size.LARGE}
			<span>{translate key="plugins.generic.objectsForReview.funderGrants"}</span>
		{/fbvFormSection}				
	{/fbvFormArea}
	{fbvFormSection class="formButtons"}
		{assign var=buttonId value="submitFormButton"|concat:"-"|uniqid}
		{fbvElement type="submit" class="submitFormButton" id=$buttonId label="common.save"}
	{/fbvFormSection}
</form>