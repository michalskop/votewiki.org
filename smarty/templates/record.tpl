{extends file="main_html.tpl"}

{block name=title}{$smarty.const.VW_TITLE} – {$h1}{/block}

{block name=head}
<script type="text/javascript" src="/js/record.js"></script>
{/block}

{block name=pageId}{$page_id}{/block}

{block name=formStart}<form action="/record/{$page_id}/save/captcha" method="post" data-ajax="false" data-rel="dialog">{/block}

{block name=content}

  <!--charts-->
  <div>
    <div class="ui-grid-b">
      <div class="ui-block-a">{t}For{/t}: {$data.sum.vote_meaning_global.for}</div>
      <div class="ui-block-b">{t}Against{/t}: {$data.sum.vote_meaning_global.against}</div>
      <div class="ui-block-c">{t}Neutral{/t}: {$data.sum.vote_meaning_global.neutral}</div>     
      <div class="ui-block-a"><img src="{$charts.vote_meaning.for}" /></div>
      <div class="ui-block-b"><img src="{$charts.vote_meaning.against}" /></div>
      <div class="ui-block-c"><img src="{$charts.vote_meaning.neutral}" /></div>
    </div>
    {$date}
  </div>

  <!--summaries-->
  <div data-role="fieldcontain">
    {foreach from=$meanings item=meaning}
    <div>
      <label for="textarea-summary-{$meaning.code}">
        <span data-role="controlgroup" data-type="horizontal" >
          <span data-role="button" data-icon="{$meaning.icon}" data-theme="{$meaning.swatch}" data-mini="true">{t}{$meaning.name}{/t}</span>
          <span data-role="button" data-icon="edit" data-theme="{$meaning.swatch}" data-mini="true" data-iconpos="right">&nbsp;</span>
        </span>
      </label>
      <textarea name="textarea-summary-{$meaning.code}" id="textarea-summary-{$meaning.code}" class="changable textarea-summary textarea-textareahide" data-mini="true" maxlength="140" style="display:none">{if isset($texts.{$meaning.summary}.text)}{$texts.{$meaning.summary}.text}{/if}</textarea>
      <div id="textarea-summary-{$meaning.code}-text" class="textarea-text">
        {if isset($texts.{$meaning.summary}.text)}
            {$texts.{$meaning.summary}.text}
        {/if}
      </div>
      <div id="textarea-summary-{$meaning.code}-remaining" class="remaining"></div>
    </div>
    {/foreach}
  </div>
  
  <!--tags-->
  <div data-role="fieldcontain" id="tags-wrapper">
    <span id="tags">
		<span data-role="button" data-icon="edit" data-mini="true" data-inline="true">
		  {t}Tags{/t}:
		</span>
		{assign var=loop value=1}
		{foreach from=$tags item=tag}
		  <span data-role="controlgroup" data-type="horizontal" data-mini="true" data-inline="true" id="tag-{$loop}-wrapper">
			<a href="/search/{$tag.tag}" data-role="button" data-theme="d" data-mini="true" title="{$tag.tag}">{$tag.tag}</a>
			<input type="hidden" name="tag-{$loop}-input" id="tag-{$loop}-input" value="{$tag.tag}"/>
			<span data-role="button" data-icon="delete" data-iconpos="right" title="{t}Delete tag{/t}" id="tag-{$loop}" class="tag-delete">&nbsp;</span>
		  </span>
		  {assign var=loop value=$loop+1}
		{/foreach}
	</span>
	<span data-role="button" data-theme="b" data-mini="true" data-icon="plus" data-inline="true" data-iconpos="right" id="tag-add">&nbsp;</span>
  </div>
  
  <!--individual votes-->
  <div data-role="collapsible">
    <h3 data-icon="person">{t}Individual votes{/t}</h3>
    {foreach from=$data.sum.group_global key=kgroup item=group}
      <div data-role="collapsible" data-mini="true" data-theme="d">
        <h4>{$data.group.$kgroup.short_name} <img src="{$charts.group.$kgroup}" style="float:right"></h4>
        {section name=kmeaning start=1 loop=4}
          {if isset($data.mps.$kgroup.{$meanings[kmeaning].code})}
          <div data-role="collapsible" data-mini="true" data-collapsed="false" data-theme="{$meanings[kmeaning].swatch}">
            <h5>{$data.vote_meaning.{$meanings[kmeaning].code}.name}</h5>
            <div class="ui-grid-c">
              {cycle values='' reset="true"}
              {foreach from=$data.mps.$kgroup.{$meanings[kmeaning].code}|@sort_names item=mp}
        	    <div class="ui-block-{cycle values='a,b,c,d'}">{$mp.last_name} {$mp.first_name|truncate:2:"."}</div>
              {/foreach}
            </div>
          </div>
          {/if}
        {/section}
      </div>
    {/foreach}
  </div>
  
  <!--descriptions-->
  <div data-role="fieldcontain">
    {foreach from=$meanings item=meaning}
      <label for="textarea-description-{$meaning.code}">
        <span data-role="controlgroup" data-type="horizontal" >
          <span data-role="button" data-icon="{$meaning.icon}" data-theme="{$meaning.swatch}">{t}{$meaning.name}{/t}</span>
          <span data-role="button" data-icon="edit" data-theme="{$meaning.swatch}" data-iconpos="right">&nbsp;</span>
        </span>
      </label>
    <textarea name="textarea-description-{$meaning.code}" id="textarea-description-{$meaning.code}" class="changable textarea-description textarea-textareahide" data-mini="true" >{if isset($texts.{$meaning.description}.text)}{$texts.{$meaning.description}.text}{/if}</textarea>
      <div>  
        <div id="textarea-description-{$meaning.code}-text" class="textarea-text">
          {if isset($texts.{$meaning.description}.text)}{$texts.{$meaning.description}.text}{/if}
        </div>
      </div>  
    {/foreach}
  </div>
  
{/block}

{block name=footer}
<!--footer -->
<div data-role="navbar" data-tap-toggle="false" data-iconpos="top">
  <ul>
    <li><a href="/new" data-icon="info" data-ajax="false">{t}About{/t}</a></li>
    <li><a href="/record/--cz_psp|26002" data-icon="star" data-ajax="false">{t}New record{/t}</a></li>
    <li><a href="/record/--cz_psp|26003" data-icon="gear" data-ajax="false">{t}Settings{/t}</a></li>
  </ul>
</div>
</div>
<div id="save">
  <div data-role="navbar" data-position="fixed" data-tap-toggle="false" data-iconpos="top">
    <ul>
      <li><input type="submit" value="{t}Save{/t}" data-theme="e" data-icon="bell" /></li>
    </ul>
  </div>
</div>
{/block}

{block name=formEnd}</form>{/block}