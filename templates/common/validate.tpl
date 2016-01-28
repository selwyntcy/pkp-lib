{**
 * templates/common/validate.tpl
 *
 * Copyright (c) 2014-2016 Simon Fraser University Library
 * Copyright (c) 2000-2016 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * Header components for the JQuery Validate plugin
 *}
	<script src="{$baseUrl}/lib/pkp/js/lib/jquery/plugins/validate/{if $useMinifiedJavaScript}jquery.validate.js{else}jquery.validate.min.js{/if}"></script>

	{if in_array($currentLocale, array('pt_PT', 'pt_BR'))}
		<script src="{$baseUrl}/lib/pkp/js/lib/jquery/plugins/validate/localization/messages_{$currentLocale|regex_replace:"/(.*)_(.*)/":"\\1\\2"|strtolower}.js"></script>
	{elseif in_array(substr($currentLocale,0,2), array('ar', 'bg', 'cn', 'cs', 'da', 'de', 'es', 'fa', 'fi', 'fr', 'hu', 'it', 'kk', 'nl', 'no', 'pl', 'ro', 'ru', 'se', 'sk', 'tr', 'tw', 'ua'))}
		<script src="{$baseUrl}/lib/pkp/js/lib/jquery/plugins/validate/localization/messages_{$currentLocale|regex_replace:"/(.*)_(.*)/":"\\1"|strtolower}.js"></script>
	{/if}
