{extends file="main_html.tpl"}

{block name=title}{$smarty.const.VW_TITLE} – {t}search{/t}{/block}

{block name=head}{/block}

{block name=pageId}{$page_id}{/block}

{block name=content}
  {include file="search_box.tpl"}
  
	<!-- last records -->
	{if isset($records) and ($records|@count > 0)}
	<ul data-role="listview" data-theme="d" data-divider-theme="d">
	  <li data-role="list-divider">{t}Last updated records{/t}<span class="ui-li-count">{$records|@count}</span></li>
	  {foreach from=$records item=record}
		<li><a href="/record/--{$record.parliament_code|replace:'/':'_'}|{$record.value}">
		  <h3>{$record.division_name}</h3>
		  <p><b>{$record.divided_on|date_format:"%x"} {$record.parliament_name}</b></p>
		</a></li>
	  {/foreach}
	</ul>
	{/if}

{/block}

{block name=footer}
 {include file="footer.tpl"}
{/block}
