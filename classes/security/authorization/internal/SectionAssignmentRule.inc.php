<?php
/**
 * @file classes/security/authorization/internal/SectionAssignmentRule.inc.php
 *
 * Copyright (c) 2014-2016 Simon Fraser University Library
 * Copyright (c) 2000-2016 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class SectionAssignmentRule
 * @ingroup security_authorization_internal
 *
 * @brief Class to check if there is an assignment
 * between user and a section/series.
 *
 */

class SectionAssignmentRule {

	//
	// Public static methods.
	//
	/**
	 * Check if a sub-editor user is assigned to a section/series.
	 * @param $contextId
	 * @param $sectionId
	 * @param $userId
	 * @return boolean
	 */
	function effect($contextId, $sectionId, $userId) {
		$subEditorsDao = Application::getSubEditorsDAO();
		if ($subEditorsDao->editorExists($contextId, $sectionId, $userId)) {
			return true;
		} else {
			return false;
		}
	}
}

?>
