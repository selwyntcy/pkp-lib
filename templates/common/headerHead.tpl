{**
 * templates/common/headerHead.tpl
 *
 * Copyright (c) 2014-2016 Simon Fraser University Library
 * Copyright (c) 2000-2016 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * Common site backend header <head> tag and contents.
 *}
<head>
	<meta http-equiv="Content-Type" content="text/html; charset={$defaultCharset|escape}" />
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>{$pageTitleTranslated|strip_tags}</title>

	{load_header context="backend" headers=$headers}
	{load_stylesheet context="backend" stylesheets=$stylesheets}
	{load_script context="backend" scripts=$scripts}
</head>
