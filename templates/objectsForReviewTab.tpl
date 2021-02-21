{**
 * templates/objectsForReviewTab.tpl
 *
 * Copyright (c) 2014-2021 Simon Fraser University
 * Copyright (c) 2003-2021 John Willinsky
 * Distributed under the GNU GPL v3. For full terms see the file docs/COPYING.
 *
 * Objects for review plugin -- add a new tab to the settings interface.
 *}
<tab id="objectsForReview" label="{translate key="plugins.generic.objectsForReview.tabTitle"}">
	{capture assign="objectsForReviewGridUrl"}{url router=$smarty.const.ROUTE_COMPONENT component="plugins.generic.objectsForReview.controllers.grid.ObjectsForReviewManagementGridHandler" op="fetchGrid" escape=false}{/capture}
	{load_url_in_div id="objectsForReviewGridContainer" url=$objectsForReviewGridUrl}
</tab>
