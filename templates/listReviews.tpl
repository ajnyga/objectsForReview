{**
 * plugins/generic/objectsForReview/templates/listReviews.tpl
 *
 * Copyright (c) 2014-2026 Simon Fraser University
 * Copyright (c) 2003-2026 John Willinsky
 * Distributed under the GNU GPL v3. For full terms see the file docs/COPYING.
 *
 * The included template that is hooked into Templates::Article::Main and Templates::Catalog::Book::Main.
 *}
<div class="item objectsForReview">
	<div class="value">
    {if $objectsForReview|@count == 1}
  		<h3>{translate key="plugins.generic.objectsForReview.objectsForReviewData.singular"}</h3>
    {else}
      <h3>{translate key="plugins.generic.objectsForReview.objectsForReviewData.plural"}</h3>
    {/if}
		<ul>
			{foreach from=$objectsForReview item=objectForReview}
				<li>
          {if $objectForReview.identifierType|lower == "link" && $objectForReview.identifier|regex_replace:"/^https?:\/\/.*/i":"" == ""}
            <a href="{$objectForReview.identifier|escape}">{$objectForReview.description|strip_unsafe_html}</a>
          {else}
            {$objectForReview.description|strip_unsafe_html} {$objectForReview.identifierType|escape}: {$objectForReview.identifier|escape}
          {/if}
				</li>
			{/foreach}
		</ul>
	</div>
</div>
