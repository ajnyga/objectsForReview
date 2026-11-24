{**
 * plugins/generic/objectsForReview/templates/frontend/pages/forReview.tpl
 *
 * Copyright (c) 2014-2026 Simon Fraser University
 * Copyright (c) 2003-2026 John Willinsky
 * Distributed under the GNU GPL v3. For full terms see the file docs/COPYING.
 *
 * The template used for listing available objects for review
 *}

{include file="frontend/components/header.tpl" pageTitle="plugins.generic.objectsForReview.frontendTitle"}

<script type="text/javascript">
  window.addEventListener('DOMContentLoaded', function(){ldelim}
    new Tablesort(document.getElementById('objects-for-review-table'));
  {rdelim});
</script>

<div class="page objectsForReview">
  {include file="frontend/components/breadcrumbs.tpl" currentTitleKey="plugins.generic.objectsForReview.frontendTitle"}

  <p>{$instructions|strip_unsafe_html}</p>

  {if !$currentUser}
    <p>{translate key='plugins.generic.objectsForReview.logInToReserve'}</p>
  {/if}

		<table id="objects-for-review-table" style="width:100%">
      <tr data-sort-method='none'>
        <th>{translate key="plugins.generic.objectsForReview.itemTitle"}</th>
        <th>{translate key="plugins.generic.objectsForReview.itemAuthors"}</th>
        <th>{translate key="plugins.generic.objectsForReview.itemPublisher"}</th>
        <th>{translate key="plugins.generic.objectsForReview.itemYear"}</th>
        <th></th> 
      </tr>
			{foreach from=$objectsForReview item=objectForReview}
      <tr>
				<td data-sort="{$objectForReview.title|strip_tags|escape}">
            {$objectForReview.title|strip_unsafe_html}<br />
            {$objectForReview.identifierType|escape}: {$objectForReview.identifier|escape}
				</td>
        <td>
            {$objectForReview.authors|escape}
        </td>
        <td>
            {$objectForReview.publisher|escape}
        </td>
        <td>
            {$objectForReview.year|escape}
        </td>
        <td>
          {if $currentUser} <!-- If logged in, show buttons -->
            {if $objectForReview.userId} <!-- If object reserved, show cancel or status -->
              {if $currentUser->getId() == $objectForReview.userId} <!-- If reserved for current user, show cancel button -->
              <form class="pkp_form" id="cancelObjectForm-{$objectForReview.objectId|escape}" method="post" action="{url op="cancelObject"}" onsubmit="return confirm({translate|json_encode|escape key="plugins.generic.objectsForReview.cancel.confirm"});">
                {csrf}
                <input type="hidden" name="objectId" value="{$objectForReview.objectId|escape}" />
                <button type="submit" id="cancelObject-{$objectForReview.objectId|escape}" class="pkp_button pkp_button_offset">{translate key='plugins.generic.objectsForReview.cancel'}</button>
              </form>
              {else} <!-- Else show reserved status -->
                {translate key='plugins.generic.objectsForReview.objectReserved'}
              {/if}
            {else} <!-- Else if not reserved, show reserve button -->
              <form class="pkp_form" id="reserveObjectForm-{$objectForReview.objectId|escape}" method="post" action="{url op="reserveObject"}" onsubmit="return confirm({translate|json_encode|escape key="plugins.generic.objectsForReview.reserve.confirm"});">
                {csrf}
                <input type="hidden" name="objectId" value="{$objectForReview.objectId|escape}" />
                <button type="submit" id="reserveObject-{$objectForReview.objectId|escape}" class="pkp_button">{translate key='plugins.generic.objectsForReview.reserve'}</button>
              </form>
            {/if} <!-- END If object reserved, show cancel or status -->
          {else} <!-- If not logged in, just show the status -->
            {if $objectForReview.userId}
              {translate key='plugins.generic.objectsForReview.objectReserved'}
            {else}
              {translate key='plugins.generic.objectsForReview.objectAvailable'}
            {/if}
          {/if} <!-- END If logged in, show buttons -->
        </td>
        </tr>
			{/foreach}
		</table>
</div>
{include file="frontend/components/footer.tpl"}
