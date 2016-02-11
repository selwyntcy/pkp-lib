{**
 * templates/controllers/grid/users/reviewer/readReview.tpl
 *
 * Copyright (c) 2014-2016 Simon Fraser University Library
 * Copyright (c) 2003-2016 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * Screen to let user read a review.
 *
 *}

<script type="text/javascript">
	$(function() {ldelim}
		// Attach the form handler.
		$('#readReviewForm').pkpHandler('$.pkp.controllers.grid.users.reviewer.ReadReviewHandler');
	{rdelim});
</script>

<form class="pkp_form" id="readReviewForm" method="post" action="{url op="reviewRead"}">
	<input type="hidden" name="reviewAssignmentId" value="{$reviewAssignment->getId()|escape}" />
	<input type="hidden" name="submissionId" value="{$reviewAssignment->getSubmissionId()|escape}" />
	<input type="hidden" name="stageId" value="{$reviewAssignment->getStageId()|escape}" />
	{if $reviewAssignment->getUnconsidered() == $smarty.const.REVIEW_ASSIGNMENT_UNCONSIDERED}
		<p>{translate key="editor.review.readConfirmation"}</p>
	{elseif !$reviewAssignment->getDateCompleted()}
		<p>{translate key="editor.review.reviewDetails.readConfirmation.description"}</p>
	{/if}
	<div id="reviewAssignment-{$reviewAssignment->getId()|escape}">
		<h2>{$reviewAssignment->getReviewerFullName()|escape}</h2>

		{if $reviewAssignment->getDateCompleted()}
			<span class="pkp_controllers_informationCenter_itemLastEvent">{translate key="common.completed.date" dateCompleted=$reviewAssignment->getDateCompleted()|date_format:$datetimeFormatShort}</span>

			{if $reviewAssignment->getReviewFormId()}
				{include file="reviewer/review/reviewFormResponse.tpl"}
			{elseif $reviewerComment}
				<h3>{translate key="editor.review.reviewerComments"}</h3>
				{include file="controllers/revealMore.tpl" content=$reviewerComment->getComments()|nl2br|strip_unsafe_html}
			{/if}
			{if $reviewAssignment->getRecommendation()}
				<h3>{translate key="submission.recommendation" recommendation=$reviewAssignment->getLocalizedRecommendation()}</h3>
			{/if}
			{if $reviewAssignment->getCompetingInterests()}
				<h3>{translate key="reviewer.submission.competingInterests"}</h3>
				<span id="reviewCompetingInterests">{$reviewAssignment->getCompetingInterests()|nl2br|strip_unsafe_html}</span>
			{/if}

		{else}
			{if $reviewAssignment->getDateCompleted()}
				<span class="pkp_controllers_informationCenter_itemLastEvent">{translate key="common.completed.date" dateCompleted=$reviewAssignment->getDateCompleted()|date_format:$datetimeFormatShort}</span>
			{elseif $reviewAssignment->getDateConfirmed()}
				<span class="pkp_controllers_informationCenter_itemLastEvent">{translate key="common.confirmed.date" dateConfirmed=$reviewAssignment->getDateConfirmed()|date_format:$datetimeFormatShort}</span>
			{elseif $reviewAssignment->getDateReminded()}
				<span class="pkp_controllers_informationCenter_itemLastEvent">{translate key="common.reminded.date" dateReminded=$reviewAssignment->getDateReminded()|date_format:$datetimeFormatShort}</span>
			{elseif $reviewAssignment->getDateNotified()}
				<span class="pkp_controllers_informationCenter_itemLastEvent">{translate key="common.notified.date" dateNotified=$reviewAssignment->getDateNotified()|date_format:$datetimeFormatShort}</span>
			{elseif $reviewAssignment->getDateAssigned()}
				<span class="pkp_controllers_informationCenter_itemLastEvent">{translate key="common.assigned.date" dateAssigned=$reviewAssignment->getDateAssigned()|date_format:$datetimeFormatShort}</span>
			{/if}
		{/if}
	</div>
	<div class="pkp_notification" id="noFilesWarning" style="display: none;">
		{include file="controllers/notification/inPlaceNotificationContent.tpl" notificationId=noFilesWarningContent notificationStyleClass=notifyWarning notificationTitle="editor.review.noReviewFilesUploaded"|translate notificationContents="editor.review.noReviewFilesUploaded.details"|translate}
	</div>
	{fbvFormArea id="readReview"}
		{fbvFormSection}
			{url|assign:reviewAttachmentsGridUrl router=$smarty.const.ROUTE_COMPONENT component="grid.files.attachment.EditorReviewAttachmentsGridHandler" op="fetchGrid" submissionId=$submission->getId() reviewId=$reviewAssignment->getId() stageId=$reviewAssignment->getStageId() escape=false}
			{load_url_in_div id="readReviewAttachmentsGridContainer" url=$reviewAttachmentsGridUrl}
		{/fbvFormSection}
		{fbvFormButtons id="closeButton" hideCancel=false submitText="common.confirm"}
	{/fbvFormArea}
</form>
