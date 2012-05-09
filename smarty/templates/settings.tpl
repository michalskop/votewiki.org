{extends file="main_html.tpl"}

{block name=title}{$smarty.const.VW_TITLE} – {t}settings{/t}{/block}

{block name=pageId}settings{/block}

{block name=content}
  <form action="/settings" method="GET">
	<div data-role="fieldcontain">
	    <fieldset data-role="controlgroup">
	    	<legend>{t}Change language{/t}:</legend>
	    	  {counter start=0 skip=1 assign="count"}
	    	  {foreach from=$locales key=key item=locale}
	    	    {counter}
	         	<input type="radio" name="locale" id="radio-choice-{$count}" value="{$key}" {if ($locale.lang == $current_locale.lang)}checked="checked" {/if}/>
	         	<label for="radio-choice-{$count}">({$locale.lang}) {$locale.name}</label>
	          {/foreach}
	    </fieldset>
	</div>
	<input type="submit" value="{t}Save{/t}" data-icon="check" />
  </form>
{/block}

{block name=footer}
 {include file="footer.tpl"}
{/block}
