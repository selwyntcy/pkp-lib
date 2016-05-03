{**
 * templates/workflow/submissionHeader.tpl
 *
 * Copyright (c) 2014-2016 Simon Fraser University Library
 * Copyright (c) 2003-2016 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * Include the submission progress bar
 *}
<script type="text/javascript">
	// Initialise JS handler.
	$(function() {ldelim}
		$('#submissionHeader').pkpHandler(
			'$.pkp.pages.workflow.SubmissionHeaderHandler', {ldelim}
				participantToggleSelector: '#participantToggle'
			{rdelim}
		);
	{rdelim});
</script>
<div id="submissionHeader">
	<div class="pkp_page_title">
		<h1 class="pkp_submission_title">
			<span class="pkp_screen_reader">{translate key="submission.submissionTitle"}</span>
			{$submission->getLocalizedTitle()}
		</h1>
		<div class="pkp_submission_author">
			<span class="pkp_screen_reader">{translate key="user.role.author_s"}</span>
			{$submission->getAuthorString()}
		</div>
		<ul class="pkp_submission_actions">
			{if array_intersect(array(ROLE_ID_MANAGER, ROLE_ID_SUB_EDITOR), $userRoles)}
				<li>{include file="linkAction/linkAction.tpl" action=$submissionEntryAction}</li>
			{/if}
			<li>{include file="linkAction/linkAction.tpl" action=$submissionInformationCenterAction}</li>
			<li>{include file="linkAction/linkAction.tpl" action=$submissionLibraryAction}</li>
		</ul>
	</div>

	{* This must be within the SubmissionHeaderHandler element in order for the
	   participants panel to work. *}
	{url|assign:submissionProgressBarUrl op="submissionProgressBar" submissionId=$submission->getId() stageId=$stageId contextId="submission" escape=false}
	{load_url_in_div id="submissionProgressBarDiv" url=$submissionProgressBarUrl}
</div>
