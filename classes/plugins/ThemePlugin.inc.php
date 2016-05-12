<?php

/**
 * @file classes/plugins/ThemePlugin.inc.php
 *
 * Copyright (c) 2014-2016 Simon Fraser University Library
 * Copyright (c) 2003-2016 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class ThemePlugin
 * @ingroup plugins
 *
 * @brief Abstract class for theme plugins
 */

import('lib.pkp.classes.plugins.LazyLoadPlugin');

abstract class ThemePlugin extends LazyLoadPlugin {
	/**
	 * Constructor
	 */
	function ThemePlugin() {
		parent::Plugin();
	}

	/**
	 * @copydoc Plugin::register
	 */
	function register($category, $path) {
		if (!parent::register($category, $path)) return false;
		$request = $this->getRequest();
		if ($this->getEnabled() && !defined('SESSION_DISABLE_INIT')) {
			$templateManager = TemplateManager::getManager($request);
			HookRegistry::register('PageHandler::displayCss', array($this, '_displayCssCallback'));

			$context = $request->getContext();
			$site = $request->getSite();
			$contextOrSite = $context?$context:$site;

			// Add the stylesheet.
			if ($contextOrSite->getSetting('themePluginPath') == basename($path)) {
				$dispatcher = $request->getDispatcher();
				$templateManager->addStyleSheet($dispatcher->url($request, ROUTE_COMPONENT, null, 'page.PageHandler', 'css', null, array('name' => $this->getName())), STYLE_SEQUENCE_LATE);

				// If this theme uses templates, ensure they're given priority.
				array_unshift(
					$templateManager->template_dir,
					$path = Core::getBaseDir() . DIRECTORY_SEPARATOR . $this->getPluginPath() . '/templates'
				);
			}
		}
		return true;
	}

	/**
	 * Get the filename to this theme's stylesheet, or null if none.
	 * @return string|null
	 */
	function getLessStylesheet() {
		return null;
	}

	/**
	 * Get the compiled CSS cache filename
	 * @return string|null
	 */
	function getStyleCacheFilename() {
		// Only relevant if Less compilation is used; otherwise return null.
		if ($this->getLessStylesheet() === null) return null;

		return 'compiled-' . $this->getName() . '.css';
	}

	/**
	 * @copydoc PKPPlugin::getTemplatePath()
	 */
	function getTemplatePath($inCore = false) {
		return parent::getTemplatePath($inCore) . 'templates/';
	}

	/**
	 * Called as a callback upon stylesheet compilation.
	 * Used to inject this theme's styles.
	 */
	function _displayCssCallback($hookName, $args) {
		$request = $args[0];
		$stylesheetName = $args[1];
		$result =& $args[2];
		$lastModified =& $args[3];

		// Ensure the callback is for this plugin before intervening
		if ($stylesheetName != $this->getName()) return false;

		if ($this->getLessStylesheet()) {
			$cacheDirectory = CacheManager::getFileCachePath();
			$cacheFilename = $this->getStyleCacheFilename();
			$lessFile = $this->getPluginPath() . '/' . $this->getLessStylesheet();
			$compiledStylesheetFile = $cacheDirectory . '/' . $cacheFilename;

			if ($cacheFilename === null || !file_exists($compiledStylesheetFile)) {
				// Need to recompile, so flag last modified.
				$lastModified = time();

				// Compile this theme's styles
				require_once('lib/pkp/lib/vendor/oyejorge/less.php/lessc.inc.php');
				$less = new Less_Parser(array( 'relativeUrls' => false ));
				$less->parseFile ($lessFile);
				$compiledStyles = str_replace('{$baseUrl}', $request->getBaseUrl(true), $less->getCss());

				// Give other plugins the chance to intervene
				HookRegistry::call('ThemePlugin::compileCss', array($request, $less, &$compiledStylesheetFile, &$compiledStyles));

				if ($cacheFilename === null || file_put_contents($compiledStylesheetFile, $compiledStyles) === false) {
					// If the stylesheet cache can't be written, log the error and
					// output the compiled styles directly without caching.
					error_log("Unable to write \"$compiledStylesheetFile\".");
					$result .= $compiledStyles;
					return false;
				}
			} else {
				// We were able to fall back on a previously compiled file. Set lastModified.
				$cacheLastModified = filemtime($compiledStylesheetFile);
				$lastModified = $lastModified===null?
					$cacheLastModified:
					min($lastModified, $cacheLastModified);
			}

			// Add the compiled styles to the rest
			$result .= "\n" . file_get_contents($compiledStylesheetFile);
		}
		return false;
	}
}

?>
