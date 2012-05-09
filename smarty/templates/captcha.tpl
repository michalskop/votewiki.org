{extends file="main_html.tpl"}

{block name=title}{$smarty.const.VW_TITLE} – {$h1}{/block}

{block name=pageId}captcha{/block}

{block name=content}
  <form action='#' method='post'>
    {foreach from=$post key=key item=item}
     <input type="hidden" value="{$item}" name="{$key}" id="{$key}" />
    {/foreach}
    <script type="text/javascript"
     src="http://www.google.com/recaptcha/api/challenge?k={$captcha_public_key}">
  </script>
  <noscript>
     <iframe src="http://www.google.com/recaptcha/api/noscript?k={$captcha_public_key}"
         height="300" width="500" frameborder="0"></iframe><br>
     <textarea name="recaptcha_challenge_field" rows="3" cols="40">
     </textarea>
     <input type="hidden" name="recaptcha_response_field"
         value="manual_challenge">
  </noscript>
  <input type="submit"  value="{t}Save{/t}" data-theme="e" data-icon="bell"/>
  </form>
{/block}
