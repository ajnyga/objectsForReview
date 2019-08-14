{**
 * plugins/generic/objectsForReview/templates/frontend/pages/forReview.tpl
 *
 * Copyright (c) 2014-2019 Simon Fraser University
 * Copyright (c) 2003-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * The template used for listing available objects for review
 *}

{include file="frontend/components/header.tpl" pageTitle="plugins.generic.objectsForReview.frontendTitle.plural"}

<div class="page objectsForReview">
  {include file="frontend/components/breadcrumbs.tpl" currentTitleKey="plugins.generic.objectsForReview.frontendTitle.plural"}
		<ul>
			{foreach from=$objectsForReview item=objectForReview}
				<li>
          {if $objectForReview.identifierType == "link"}
            <a href={$objectForReview.identifier}>{$objectForReview.description}</a>
          {else}
            {$objectForReview.description}. {$objectForReview.identifierType}: {$objectForReview.identifier}
          {/if}
				</li>
			{/foreach}
		</ul>
</div>
{include file="frontend/components/footer.tpl"}
