<!-- tags -->
{if isset($records_tag) and ($records_tag|@count > 0)}
<ul data-role="listview" data-theme="d" data-divider-theme="d">
  <li data-role="list-divider">{t}Taged{/t}<span class="ui-li-count">{$records_tag|@count}</span></li>
  {foreach from=$records_tag item=record}
	<li><a href="/record/--{$record.parliament_code|replace:'/':'_'}|{$record.value}">
	  <h3>{$record.division_name}</h3>
	  <p><b>{$record.divided_on|date_format:"%x"} {$record.parliament_name}</b></p>
	</a></li>
  {/foreach}
</ul>
{/if}
