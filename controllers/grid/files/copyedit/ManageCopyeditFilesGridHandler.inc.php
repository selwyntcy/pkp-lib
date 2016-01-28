<?php

/**
 * @file controllers/grid/files/copyedit/ManageCopyeditFilesGridHandler.inc.php
 *
 * Copyright (c) 2014-2016 Simon Fraser University Library
 * Copyright (c) 2003-2016 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class ManageCopyeditFilesGridHandler
 * @ingroup controllers_grid_files_copyedit
 *
 * @brief Handle the copyedited file selection grid
 */

import('lib.pkp.controllers.grid.files.SelectableSubmissionFileListCategoryGridHandler');

class ManageCopyeditFilesGridHandler extends SelectableSubmissionFileListCategoryGridHandler {
	/**
	 * Constructor
	 */
	function ManageCopyeditFilesGridHandler() {
		import('lib.pkp.controllers.grid.files.SubmissionFilesCategoryGridDataProvider');
		parent::SelectableSubmissionFileListCategoryGridHandler(
			new SubmissionFilesCategoryGridDataProvider(SUBMISSION_FILE_COPYEDIT),
			WORKFLOW_STAGE_ID_EDITING,
			FILE_GRID_ADD|FILE_GRID_DELETE|FILE_GRID_VIEW_NOTES|FILE_GRID_EDIT
		);

		$this->addRoleAssignment(
			array(
				ROLE_ID_SUB_EDITOR,
				ROLE_ID_MANAGER,
				ROLE_ID_ASSISTANT
			),
			array(
				'fetchGrid', 'fetchCategory', 'fetchRow',
				'addFile',
				'downloadFile',
				'deleteFile',
				'updateCopyeditFiles'
			)
		);

		// Set the grid title.
		$this->setTitle('submission.copyedited');
	}


	//
	// Public handler methods
	//
	/**
	 * Save 'manage copyedited files' form
	 * @param $args array
	 * @param $request PKPRequest
	 * @return JSONMessage JSON object
	 */
	function updateCopyeditFiles($args, $request) {
		$submission = $this->getSubmission();

		import('lib.pkp.controllers.grid.files.copyedit.form.ManageCopyeditFilesForm');
		$manageCopyeditFilesForm = new ManageCopyeditFilesForm($submission->getId());
		$manageCopyeditFilesForm->readInputData();

		if ($manageCopyeditFilesForm->validate()) {
			$manageCopyeditFilesForm->execute(
				$args, $request,
				$this->getGridCategoryDataElements($request, $this->getStageId())
			);

			// Let the calling grid reload itself
			return DAO::getDataChangedEvent();
		} else {
			return new JSONMessage(false);
		}
	}
}

?>
