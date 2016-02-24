{**
 * templates/common/headerHead.tpl
 *
 * Copyright (c) 2014-2016 Simon Fraser University Library
 * Copyright (c) 2000-2016 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * Common site header <head> tag and contents.
 *}
<head>
	<meta http-equiv="Content-Type" content="text/html; charset={$defaultCharset|escape}" />
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>
		{$pageTitleTranslated|strip_tags}
	</title>
	{if $requestedOp == 'index' && $metaSearchDescription != ''}
		<meta name="description" content="{$metaSearchDescription|escape}" />
	{/if}
	<meta name="generator" content="{$applicationName} {$currentVersionString|escape}" />
	{$metaCustomHeaders}
	{if $displayFavicon}<link rel="icon" href="{$faviconDir}/{$displayFavicon.uploadName|escape:"url"}" type="{$displayFavicon.mimeType|escape}" />{/if}

	<!-- Base Jquery -->
	{if $allowCDN}
		<script src="//ajax.googleapis.com/ajax/libs/jquery/{$smarty.const.CDN_JQUERY_VERSION}/{if $useMinifiedJavaScript}jquery.min.js{else}jquery.js{/if}"></script>
		<script src="//ajax.googleapis.com/ajax/libs/jqueryui/{$smarty.const.CDN_JQUERY_UI_VERSION}/{if $useMinifiedJavaScript}jquery-ui.min.js{else}jquery-ui.js{/if}"></script>
	{else}
		<script src="{$baseUrl}/lib/pkp/lib/vendor/components/jquery/{if $useMinifiedJavaScript}jquery.min.js{else}jquery.js{/if}"></script>
		<script src="{$baseUrl}/lib/pkp/lib/vendor/components/jqueryui/{if $useMinifiedJavaScript}jquery-ui.min.js{else}jquery-ui.js{/if}"></script>
	{/if}

	{* 20160125: hack: slick.js *}
	{* 20160128: hack: changed to cdn version of slick.js as upstream has removed it *}
	{* todo: should move to theme *}
	<link rel="stylesheet" type="text/css" href="https://cdnjs.cloudflare.com/ajax/libs/slick-carousel/1.5.9/slick.min.css">
	<link rel="stylesheet" type="text/css" href="https://cdnjs.cloudflare.com/ajax/libs/slick-carousel/1.5.9/slick-theme.min.css">

	{* Load Noto Sans font from Google Font CDN *}
	{* To load extended latin or other character sets, see https://www.google.com/fonts#UsePlace:use/Collection:Noto+Sans *}
	<link href='//fonts.googleapis.com/css?family=Noto+Sans:400,400italic,700,700italic' rel='stylesheet' type='text/css'>

	{load_stylesheet context="frontend" stylesheets=$stylesheets}

	{* Form validator used on search form *}
	{include file="common/validate.tpl"}

	<!-- Constants for JavaScript -->
	{include file="common/jsConstants.tpl"}

	<!-- Default global locale keys for JavaScript -->
	{include file="common/jsLocaleKeys.tpl" }

	<!-- Compiled scripts -->
	{if $useMinifiedJavaScript}
		<script src="{$baseUrl}/js/pkp.min.js"></script>
	{else}
		{include file="common/minifiedScripts.tpl"}
	{/if}

	<!-- Pines Notify build/cache -->
	<script src="{$baseUrl}/lib/pkp/js/lib/pnotify/buildcustom.php?mode=js{if $useMinifiedJavaScript}&amp;min=1{/if}&amp;modules="></script>

	{$additionalHeadData}
</head>
