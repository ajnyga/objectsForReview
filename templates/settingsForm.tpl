{**
 * plugins/generic/objectsForReview/settingsForm.tpl
 *
 * Copyright (c) 2014-2018 Simon Fraser University
 * Copyright (c) 2003-2018 John Willinsky
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

	{fbvFormArea id="objectsForReviewSettingsFormAreaSections"}
    {fbvElement type="select" id="section" from=$sections selected=$section translate=false size=$fbvStyles.size.SMALL}
  {/fbvFormArea}

	{fbvFormArea id="objectsForReviewSettingsFormAreaDisplay"}
    {fbvElement type="checkbox" id="displayInToc" value="1" checked=$displayInToc label="plugins.generic.objectsForReview.settings.displayInToc.display"}
	{/fbvFormArea}

	{fbvFormButtons}

	<p><span class="formRequired">{translate key="common.requiredField"}</span></p>
</form>
