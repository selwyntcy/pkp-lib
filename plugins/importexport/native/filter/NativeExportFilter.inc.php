<?php

/**
 * @file plugins/importexport/native/filter/NativeExportFilter.inc.php
 *
 * Copyright (c) 2014-2016 Simon Fraser University Library
 * Copyright (c) 2000-2016 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class NativeExportFilter
 * @ingroup plugins_importexport_native
 *
 * @brief Base class that converts a DataObject to a Native XML document
 */

import('lib.pkp.plugins.importexport.native.filter.NativeImportExportFilter');

class NativeExportFilter extends NativeImportExportFilter {
	/**
	 * Constructor
	 * @param $filterGroup FilterGroup
	 */
	function NativeExportFilter($filterGroup) {
		parent::NativeImportExportFilter($filterGroup);
	}


	//
	// Helper functions
	//
	/**
	 * Create a set of child nodes of parentNode containing the
	 * localeKey => value data representing translated content.
	 * @param $doc DOMDocument
	 * @param $parentNode DOMNode
	 * @param $name string Node name
	 * @param $values array Array of locale key => value mappings
	 */
	function createLocalizedNodes($doc, $parentNode, $name, $values) {
		$deployment = $this->getDeployment();
		if (is_array($values)) {
			foreach ($values as $locale => $value) {
				if ($value === '') continue; // Skip empty values
				$parentNode->appendChild($node = $doc->createElementNS($deployment->getNamespace(), $name, $value));
				$node->setAttribute('locale', $locale);
			}
		}
	}

	/**
	 * Create an optional node with a name and value.
	 * @param $doc DOMDocument
	 * @param $parentNode DOMElement
	 * @param $name string
	 * @param $value string|null
	 * @return DOMElement|null
	 */
	function createOptionalNode($doc, $parentNode, $name, $value) {
		if ($value === '' || $value === null) return null;

		$deployment = $this->getDeployment();
		$parentNode->appendChild($node = $doc->createElementNS($deployment->getNamespace(), $name, $value));
		return $node;
	}
}

?>
