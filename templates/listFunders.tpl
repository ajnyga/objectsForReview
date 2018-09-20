{**
 * plugins/generic/objectsForReview/templates/listFunders.tpl
 *
 * Copyright (c) 2014-2018 Simon Fraser University
 * Copyright (c) 2003-2018 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * The included template that is hooked into Templates::Article::Details.
 *}
<div class="item funders">
	<div class="value">
		<h3>{translate key="plugins.generic.objectsForReview.objectsForReviewData"}</h3>
		<ul>
			{foreach from=$objectsForReviewData item=objectForReview}
				<li>

				</li>
			{/foreach}
		</ul>
	</div>
</div>
