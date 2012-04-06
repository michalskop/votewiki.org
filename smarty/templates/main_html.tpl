<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
	"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">

<head>
	<title>{block name=title}{/block}</title>
	<meta http-equiv="content-type" content="text/html; charset=UTF-8"/>
	<meta name="viewport" content="width=device-width, initial-scale=1"> 
	<meta name="Description" content="{t}MSGID_HTML_HEAD_DESCRIPTION{/t}" />
	<link rel="shortcut icon" href="/images/favicon.ico" type="image/x-icon">
	<link rel="icon" href="/images/favicon.ico" type="image/x-icon">
	<link rel="stylesheet" href="http://code.jquery.com/mobile/1.1.0-rc.1/jquery.mobile-1.1.0-rc.1.min.css" />
	<link rel="stylesheet" href="http://jeromeetienne.github.com/jquery-mobile-960/css/jquery-mobile-fluid960.min.css" />
	<script src="http://code.jquery.com/jquery-1.7.1.min.js"></script>
	<script src="http://code.jquery.com/mobile/1.1.0-rc.1/jquery.mobile-1.1.0-rc.1.min.js"></script>
	
	<link rel="stylesheet" type="text/css" href="/css/votewiki.css" />
	<link rel="stylesheet" type="text/css" href="/css/jqm-icon-pack-1.1-original.css" />

	{block name=head}{/block}
	<script type="text/javascript">var google_analytics_key = "{$smarty.const.GOOGLE_ANALYTICS_KEY}"</script>
	<script type="text/javascript" src="/js/google_analytics.js"></script>
</head>
<body>
  <div data-role="page" id="{block name=pageId}{/block}" class="pageRecord">
    {block name=formStart}{/block}
	<div data-role="header" {block name=headerDataTheme}{/block}>
		<h1>{$h1}</h1>
		<a href="/" data-role="button" data-icon="home" data-iconpos="notext">{t}Home{/t}</a>
		<a href="/search" data-role="button" data-icon="search" data-iconpos="notext">{t}Search{/t}</a>
	</div><!-- /header -->
	<div data-role="content">
		{block name=content}{/block}
	</div><!-- /content -->
	<div data-role="footer" {block name=footerDataTheme}{/block}>
	  {block name=footer}{/block}
	</div><!-- /footer -->
	{block name=formEnd}{/block}
  </div><!-- /page -->
</body>
</html>
