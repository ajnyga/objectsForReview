{**
 * plugins/generic/objectsForReview/templates/listReviews.tpl
 *
 * Copyright (c) 2014-2018 Simon Fraser University
 * Copyright (c) 2003-2018 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * The included template that is hooked into Templates::Article::Main and Templates::Catalog::Book::Main.
 *}
<div class="item funders">
	<div class="value">
    {if $objectsForReview|@count == 1}
  		<h3>{translate key="plugins.generic.objectsForReview.objectsForReviewData.singular"}</h3>
    {else}
      <h3>{translate key="plugins.generic.objectsForReview.objectsForReviewData.plural"}</h3>
    {/if}
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
</div>
