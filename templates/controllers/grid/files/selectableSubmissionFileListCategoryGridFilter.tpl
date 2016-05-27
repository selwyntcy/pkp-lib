{**
 * controllers/grid/files/selectableSubmissionFileListCategoryGridFilter.tpl
 *
 * Copyright (c) 2014-2016 Simon Fraser University Library
 * Copyright (c) 2000-2016 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * Filter template for selectable submission file list category grid.
 *}
<script type="text/javascript">
	// Attach the form handler to the form.
	$('#fileListFilterForm').pkpHandler('$.pkp.controllers.form.ToggleFormHandler');
</script>
<form class="pkp_form filter" id="fileListFilterForm" action="{url router=$smarty.const.ROUTE_COMPONENT op="fetchGrid"}" method="post">
	{fbvFormArea}
		{fbvFormSection list="true"}
			{fbvElement type="checkbox" id="allStages" checked=$filterSelectionData.allStages label="editor.submission.fileList.includeAllStages"}
		{/fbvFormSection}
	{/fbvFormArea}
</form>
