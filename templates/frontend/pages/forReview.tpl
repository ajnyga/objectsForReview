{**
 * plugins/generic/objectsForReview/templates/frontend/pages/forReview.tpl
 *
 * Copyright (c) 2014-2019 Simon Fraser University
 * Copyright (c) 2003-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
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

  {if !$currentUser}
    <p>{translate key='plugins.generic.objectsForReview.logInToReserve'}</p>
  {/if}

		<table id="objects-for-review-table" class="pkpTable" style="width:100%">
      <tr data-sort-method='none'>
        <th>Title</th>
        <th>Authors</th>
        <th>Publisher</th>
        <th>Year</th>
        <th></th> 
      </tr>
			{foreach from=$objectsForReview item=objectForReview}
      <tr>
				<td data-sort='{$objectForReview.title|strip_tags}'>
            {$objectForReview.title|strip_unsafe_html}<br />
            {$objectForReview.identifierType|escape}: {$objectForReview.identifier|escape}
				</td>
        <td>
            {$objectForReview.authors}
        </td>
        <td>
            {$objectForReview.publisher}
        </td>
        <td>
            {$objectForReview.year}
        </td>
        <td>
          {if $currentUser} <!-- If logged in, show buttons -->
            {if $objectForReview.userId} <!-- If object reserved, show cancel or status -->
              {if $currentUser->getId() == $objectForReview.userId} <!-- If reserved for current user, show cancel button -->
              <form class="pkp_form" id="cancelObjectForm-{$objectForReview.objectId|escape}" action="{url op="cancelObject"}" onsubmit="return confirm('{translate key='plugins.generic.objectsForReview.cancel.confirm'}');">
                {csrf}
                <input type="hidden" name="objectId" value="{$objectForReview.objectId|escape}" />
                <button type="submit" id="cancelObject-{$objectForReview.objectId|escape}" class="pkp_button pkp_button_offset">{translate key='plugins.generic.objectsForReview.cancel'}</button>
              </form>
              {else} <!-- Else show reserved status -->
                {translate key='plugins.generic.objectsForReview.objectReserved'}
              {/if}
            {else} <!-- Else if not reserved, show reserve button -->
              <form class="pkp_form" id="reserveObjectForm-{$objectForReview.objectId|escape}" action="{url op="reserveObject"}" onsubmit="return confirm('{translate key='plugins.generic.objectsForReview.reserve.confirm'}');">
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


<!--

          Käyttäjä on kirjautunut
            - Kirja vapaa
            - Kirja varattu itselle
            - Kirja varattu toiselle

          Käyttäjä ei ole kirjautunut
            - Kirja vapaa
            - Kirja ei vapaa
-->


        </td>
        </tr>
			{/foreach}
		</table>
</div>
{include file="frontend/components/footer.tpl"}
