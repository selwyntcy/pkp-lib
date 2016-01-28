{**
 * controllers/tab/settings/appearance/form/footer.tpl
 *
 * Copyright (c) 2014-2016 Simon Fraser University Library
 * Copyright (c) 2003-2016 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @brief Form fields for adding custom content to the frontend footer
 *
 *}
{fbvFormArea id="pageFooterContainer" title="manager.setup.pageFooter"}
	{fbvFormSection description="manager.setup.pageFooterDescription"}
		{fbvElement type="textarea" multilingual=true name="pageFooter" id="pageFooter" value=$pageFooter rich=true}
	{/fbvFormSection}
{/fbvFormArea}
