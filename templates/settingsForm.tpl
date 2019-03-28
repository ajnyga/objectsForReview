{**
 * plugins/generic/objectsForReview/templates/settingsForm.tpl
 *
 * Copyright (c) 2014-2019 Simon Fraser University
 * Copyright (c) 2003-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * Objects for Review plugin settings
 *
 *}
<script>
	$(function() {ldelim}
		// Attach the form handler.
		$('#orSettingsForm').pkpHandler('$.pkp.controllers.form.AjaxFormHandler');
	{rdelim});
</script>

<form class="pkp_form" id="orSettingsForm" method="post" action="{url router=$smarty.const.ROUTE_COMPONENT op="manage" category="generic" plugin=$pluginName verb="settings" save=true}">
	{csrf}
	{include file="controllers/notification/inPlaceNotification.tpl" notificationId="orSettingsFormNotification"}

	{fbvFormArea id="objectsForReviewSettingsFormArea"}

			{fbvElement type="select" id="section" from=$sections selected=$section translate=false size=$fbvStyles.size.SMALL}

		{fbvFormSection list=true label="plugins.generic.objectsForReview.settings.displayOptions"}
			{fbvElement type="checkbox" id="displayAsSubtitle" value="1" label="plugins.generic.objectsForReview.settings.displayAsSubtitle" checked=$displayAsSubtitle}
			{fbvElement type="checkbox" id="displayAsList" value="1" label="plugins.generic.objectsForReview.settings.displayAsList" checked=$displayAsList}
		{/fbvFormSection}



		{fbvFormButtons}
	
	{/fbvFormArea}

	<p><span class="formRequired">{translate key="common.requiredField"}</span></p>

</form>
