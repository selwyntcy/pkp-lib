<?php
/**
 * @defgroup controllers_confirmationModal_linkAction Confirmation Modal Link Action
 */

/**
 * @file controllers/confirmationModal/linkAction/ViewReviewGuidelinesLinkAction.inc.php
 *
 * Copyright (c) 2014-2016 Simon Fraser University Library
 * Copyright (c) 2003-2016 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class ViewReviewGuidelinesLinkAction
 * @ingroup controllers_confirmationModal_linkAction
 *
 * @brief An action to open the review guidelines confirmation modal.
 */

import('lib.pkp.classes.linkAction.LinkAction');

class ViewReviewGuidelinesLinkAction extends LinkAction {

	/**
	 * Constructor
	 * @param $request Request
	 * @param $stageId int Stage ID of review assignment
	 */
	function ViewReviewGuidelinesLinkAction($request, $stageId) {
		$context = $request->getContext();
		// Instantiate the view review guidelines confirmation modal.
		import('lib.pkp.classes.linkAction.request.ConfirmationModal');
		$viewGuidelinesModal = new ConfirmationModal(
			$context->getLocalizedSetting(
				$stageId==WORKFLOW_STAGE_ID_EXTERNAL_REVIEW?'reviewGuidelines':'internalReviewGuidelines'
			),
			__('reviewer.submission.guidelines'),
			null, null,
			false
		);

		// Configure the link action.
		parent::LinkAction('viewReviewGuidelines', $viewGuidelinesModal, __('reviewer.submission.guidelines'));
	}
}

?>
