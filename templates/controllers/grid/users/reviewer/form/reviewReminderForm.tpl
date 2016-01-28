{**
 * templates/controllers/grid/users/reviewer/reviewReminderForm.tpl
 *
 * Copyright (c) 2014-2016 Simon Fraser University Library
 * Copyright (c) 2003-2016 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * Display the form to send a review reminder--Contains a user-editable message field (all other fields are static)
 *
 *}
<script type="text/javascript">
	$(function() {ldelim}
		// Attach the form handler.
		$('#sendReminderForm').pkpHandler('$.pkp.controllers.form.AjaxFormHandler');
	{rdelim});
</script>

<form class="pkp_form" id="sendReminderForm" method="post" action="{url op="sendReminder"}" >
	{fbvFormArea id="sendReminder"}
		<input type="hidden" name="submissionId" value="{$submissionId|escape}" />
		<input type="hidden" name="stageId" value="{$stageId|escape}" />
		<input type="hidden" name="reviewAssignmentId" value="{$reviewAssignmentId|escape}" />

		{fbvFormSection title="user.role.reviewer"}
			{fbvElement type="text" id="reviewerName" value=$reviewerName disabled="true"}
		{/fbvFormSection}

		{fbvFormSection title="editor.review.personalMessageToReviewer" for="message"}
			{fbvElement type="textarea" id="message" value=$message rich=true}
		{/fbvFormSection}
		{fbvFormSection title="reviewer.submission.reviewSchedule"}
			{fbvElement type="text" id="dateNotified" label="reviewer.submission.reviewRequestDate" value=$reviewAssignment->getDateNotified()|date_format:$dateFormatShort disabled=true inline=true size=$fbvStyles.size.SMALL class="datepicker"}
			{if $reviewAssignment->getDateConfirmed()}
				{fbvElement type="text" id="dateConfirmed" label="editor.review.dateAccepted" value=$reviewAssignment->getDateConfirmed()|date_format:$dateFormatShort disabled=true inline=true size=$fbvStyles.size.SMALL class="datepicker"}
			{else}
				{fbvElement type="text" id="responseDue" label="reviewer.submission.responseDueDate" value=$reviewAssignment->getDateResponseDue()|date_format:$dateFormatShort disabled=true inline=true size=$fbvStyles.size.SMALL class="datepicker"}
			{/if}
			{fbvElement type="text" id="dateDue" label="reviewer.submission.reviewDueDate" value=$reviewAssignment->getDateDue()|date_format:$dateFormatShort disabled=true inline=true size=$fbvStyles.size.SMALL class="datepicker"}
		{/fbvFormSection}
		{fbvFormButtons submitText="editor.review.sendReminder"}
	{/fbvFormArea}
</form>
