{**
 * templates/controllers/grid/user/reviewer/form/defaultReviewerForm.tpl
 *
 * Copyright (c) 2014-2016 Simon Fraser University Library
 * Copyright (c) 2003-2016 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * A default reviewer selection form.
 *
 *}
<script type="text/javascript">
	$(function() {ldelim}
		// Attach the form handler.
		$('#defaultReviewerForm').pkpHandler('$.pkp.controllers.grid.users.reviewer.form.AddReviewerFormHandler',
			{ldelim}
				templateUrl: {url|json_encode router=$smarty.const.ROUTE_COMPONENT component='grid.users.reviewer.ReviewerGridHandler' op='fetchTemplateBody' stageId=$stageId reviewRoundId=$reviewRoundId submissionId=$submissionId escape=false}
			{rdelim}
		);
	{rdelim});
</script>

<form class="pkp_form" id="defaultReviewerForm" method="post" action="{url op="updateReviewer"}" >

	{include file="controllers/grid/users/reviewer/form/reviewerFormFooter.tpl"}

	{fbvFormButtons submitText="editor.submission.addReviewer"}
</form>

