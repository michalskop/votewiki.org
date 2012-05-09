{extends file="main_html.tpl"}

{block name=title}{$smarty.const.VW_TITLE} – {t}search{/t}{/block}

{block name=head}{/block}

{block name=pageId}{$page_id}{/block}

{block name=content}
  {include file="search_box.tpl"}

  {include file="search_result_tag.tpl"}
  
  {if isset($records)}
    <ul data-role="listview" data-theme="d" data-divider-theme="d">
      <li data-role="list-divider">{t}Fultext{/t}<span class="ui-li-count">{$records|@count}</span></li>
      {foreach from=$records item=record}
        <li><a href="/record/--{$record.info.parliament_code|replace:'/':'_'}|{$record.info.value}">
	    <h3>{$record.info.division_name}</h3>
	    <p><b>{$record.info.divided_on|date_format:"%x"} {$record.info.parliament_name}</b></p>
	        {foreach from=$record.text item=s}
	          <p>{$s.text}</p>
	        {/foreach}
        </a></li>
      {/foreach}
    </ul>
  {/if}
  
  {if $nothing_found}
    <p>{t}No records found.{/t}</p>
  {/if}

{/block}

{block name=footer}
 {include file="footer.tpl"}
{/block}
