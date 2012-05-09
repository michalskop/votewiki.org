{extends file="main_html.tpl"}

{block name=title}{$smarty.const.VW_TITLE} – {t}search{/t}{/block}

{block name=head}{/block}

{block name=pageId}{$page_id}{/block}

{block name=formStart}{/block}

{block name=content}
{include file="search_box.tpl"}

{if isset($tags)}
  {if isset($no_tags_message)}
    <p>{t}No records found for tag{/t} <b>{$parameter}</b></p>
  {/if}
  {foreach from=$tags key=key item=tag}
    <a href="/tag/{$key}" data-role="button" data-theme="d" data-mini="true" title="{$key}" data-inline="true">{$key}</a>
  {/foreach}
  
{else}
  {include file="search_result_tag.tpl"}
{/if}

{/block}

{block name=footer}
 {include file="footer.tpl"}
{/block}
