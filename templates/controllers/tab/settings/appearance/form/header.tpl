{**
 * controllers/tab/settings/appearance/form/header.tpl
 *
 * Copyright (c) 2014-2016 Simon Fraser University Library
 * Copyright (c) 2003-2016 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @brief Form fields for configuring the frontend header
 *
 *}
{fbvFormSection label="manager.setup.logo" class=$wizardClass}
	<div id="pageHeaderLogoImage">
		{$imagesViews.pageHeaderLogoImage}
	</div>
	<div id="{$uploadImageLinkActions.pageHeaderLogoImage->getId()}" class="pkp_linkActions">
		{include file="linkAction/linkAction.tpl" action=$uploadImageLinkActions.pageHeaderLogoImage contextId="appearanceForm"}
	</div>
{/fbvFormSection}
