{extends file="main_html.tpl"}

{block name=title}{$smarty.const.VW_TITLE} – {t}search{/t}{/block}

{block name=head}{/block}

{block name=pageId}{$page_id}{/block}

{block name=formStart}{/block}

{block name=content}
<!-- search box -->
<div id="search-wrapper" class="ui-body ui-body-c ui-corner-all">
  <input type="search" name="search" id="search" value="" data-inline="true" />
  <input type="submit" value="{t}find{/t}" data-icon="search" data-inline="true"/>
</div>


<!-- tags -->
<ul data-role="listview" data-theme="d" data-divider-theme="d">
  <li data-role="list-divider">{t}Tags{/t}<span class="ui-li-count">{$records_tag|@count}</span></li>
  {foreach from=$records_tag item=tag}
	<li><a href="/record/--{$tag.division.parliament_code|replace:'/':'_'}|{$tag.source.value}">
	  <h3>{$tag.record.name}</h3>
	  <p><b>{$tag.division.divided_on|date_format:"%x"} {$tag.parliament.name}</b></p>
	</a></li>
  {/foreach}
  <li data-role="list-divider">{t}Summary{/t}<span class="ui-li-count">{$records_summary|@count}</span></li>
  {foreach from=$records_summary item=summary}
	<li><a href="/record/--{$summary.division.parliament_code|replace:'/':'_'}|{$summary.source.value}">
	  <h3>{$summary.record.name}</h3>
	  <p><b>{$summary.division.divided_on|date_format:"%x"} {$summary.parliament.name}</b></p>
	  <p>{$summary.summary.text}</p>
	</a></li>
  {/foreach}
</ul>

{/block}
