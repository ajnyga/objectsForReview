{**
 * plugins/generic/funding/templates/listFunders.tpl
 *
 * Copyright (c) 2014-2017 Simon Fraser University
 * Copyright (c) 2003-2017 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * The included template that is hooked into Templates::Article::Details.
 *}
<div class="item funders">
	<div class="value">
		<h3>{translate key="plugins.generic.funding.fundingData"}</h3>
		<ul>
			{foreach from=$funderData item=funder}
				<li>
					{if $funder.funderIdentification}
						{assign var="funderSearch" value=$funder.funderIdentification|explode:"/"}
						<a href="https://search.crossref.org/funding?q={$funderSearch[4]|escape}">{$funder.funderName|escape}</a>
					{else}
						{$funder.funderName|escape}
					{/if}
					<br />
					{if $funder.funderAwards}{translate key="plugins.generic.funding.funderGrants"} {$funder.funderAwards|escape}{/if}
				</li>
			{/foreach}
		</ul>
	</div>
</div>
