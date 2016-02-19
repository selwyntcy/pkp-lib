<?php

/**
 * @defgroup template Template
 * Implements template management.
 */

/**
 * @file classes/template/PKPTemplateManager.inc.php
 *
 * Copyright (c) 2014-2016 Simon Fraser University Library
 * Copyright (c) 2000-2016 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class TemplateManager
 * @ingroup template
 *
 * @brief Class for accessing the underlying template engine.
 * Currently integrated with Smarty (from http://smarty.php.net/).
 */

/* This definition is required by Smarty */
define('SMARTY_DIR', Core::getBaseDir() . '/lib/pkp/lib/vendor/smarty/smarty/libs/');

require_once('./lib/pkp/lib/vendor/smarty/smarty/libs/Smarty.class.php');
require_once('./lib/pkp/lib/vendor/smarty/smarty/libs/plugins/modifier.escape.php'); // Seems to be needed?

define('CACHEABILITY_NO_CACHE',		'no-cache');
define('CACHEABILITY_NO_STORE',		'no-store');
define('CACHEABILITY_PUBLIC',		'public');
define('CACHEABILITY_MUST_REVALIDATE',	'must-revalidate');
define('CACHEABILITY_PROXY_REVALIDATE',	'proxy-revalidate');

define('STYLE_SEQUENCE_CORE', 0);
define('STYLE_SEQUENCE_NORMAL', 10);
define('STYLE_SEQUENCE_LATE', 15);
define('STYLE_SEQUENCE_LAST', 20);

define('CDN_JQUERY_VERSION', '1.11.0');
define('CDN_JQUERY_UI_VERSION', '1.11.0');

import('lib.pkp.classes.template.PKPTemplateResource');

class PKPTemplateManager extends Smarty {
	/** @var array of URLs to stylesheets */
	private $_styleSheets = array();

	/** @var array of URLs to javascript files */
	private $_javaScripts = array();

	/** @var string Type of cacheability (Cache-Control). */
	private $_cacheability;

	/** @var object The form builder vocabulary class. */
	private $_fbv;

	/** @var PKPRequest */
	private $_request;

	/**
	 * Constructor.
	 * Initialize template engine and assign basic template variables.
	 * @param $request PKPRequest
	 */
	function PKPTemplateManager($request) {
		assert(is_a($request, 'PKPRequest'));
		$this->_request = $request;

		parent::Smarty();

		// Set up Smarty configuration
		$baseDir = Core::getBaseDir();
		$cachePath = CacheManager::getFileCachePath();

		// Set the default template dir (app's template dir)
		$this->app_template_dir = $baseDir . DIRECTORY_SEPARATOR . 'templates';
		// Set fallback template dir (core's template dir)
		$this->core_template_dir = $baseDir . DIRECTORY_SEPARATOR . 'lib' . DIRECTORY_SEPARATOR . 'pkp' . DIRECTORY_SEPARATOR . 'templates';

		$this->template_dir = array($this->app_template_dir, $this->core_template_dir);
		$this->compile_dir = $cachePath . DIRECTORY_SEPARATOR . 't_compile';
		$this->config_dir = $cachePath . DIRECTORY_SEPARATOR . 't_config';
		$this->cache_dir = $cachePath . DIRECTORY_SEPARATOR . 't_cache';

		$this->_cacheability = CACHEABILITY_NO_STORE; // Safe default

		// Are we using implicit authentication?
		$this->assign('implicitAuth', Config::getVar('security', 'implicit_auth'));
	}

	/**
	 * Initialize the template manager.
	 */
	function initialize() {
		$locale = AppLocale::getLocale();
		$application = PKPApplication::getApplication();
		$router = $this->_request->getRouter();
		assert(is_a($router, 'PKPRouter'));

		$this->assign(array(
			'defaultCharset' => Config::getVar('i18n', 'client_charset'),
			'basePath' => $this->_request->getBasePath(),
			'baseUrl' => $this->_request->getBaseUrl(),
			'requiresFormRequest' => $this->_request->isPost(),
			'currentUrl' => $this->_request->getCompleteUrl(),
			'dateFormatTrunc' => Config::getVar('general', 'date_format_trunc'),
			'dateFormatShort' => Config::getVar('general', 'date_format_short'),
			'dateFormatLong' => Config::getVar('general', 'date_format_long'),
			'datetimeFormatShort' => Config::getVar('general', 'datetime_format_short'),
			'datetimeFormatLong' => Config::getVar('general', 'datetime_format_long'),
			'timeFormat' => Config::getVar('general', 'time_format'),
			'allowCDN' => Config::getVar('general', 'enable_cdn'),
			'useMinifiedJavaScript' => Config::getVar('general', 'enable_minified'),
			'currentContext' => $this->_request->getContext(),
			'currentLocale' => $locale,
			'pageTitle' => $application->getNameKey(),
			'applicationName' => __($application->getNameKey()),
			'exposedConstants' => $application->getExposedConstants(),
			'jsLocaleKeys' => $application->getJSLocaleKeys(),
		));

		if (is_a($router, 'PKPPageRouter')) {
			$this->assign(array(
				'requestedPage' => $router->getRequestedPage($this->_request),
				'requestedOp' => $router->getRequestedOp($this->_request),
			));
		}

		if ($dispatcher = $this->_request->getDispatcher()) {
			$this->addStyleSheet($dispatcher->url($this->_request, ROUTE_COMPONENT, null, 'page.PageHandler', 'css'), STYLE_SEQUENCE_CORE, 'backend');
		}

		// If there's a locale-specific stylesheet, add it.
		if (($localeStyleSheet = AppLocale::getLocaleStyleSheet($locale)) != null) $this->addStyleSheet($this->_request->getBaseUrl() . '/' . $localeStyleSheet, 'backend');

		// Register custom functions
		$this->register_modifier('translate', array('AppLocale', 'translate'));
		$this->register_modifier('strip_unsafe_html', array('PKPString', 'stripUnsafeHtml'));
		$this->register_modifier('String_substr', array('PKPString', 'substr'));
		$this->register_modifier('to_array', array($this, 'smartyToArray'));
		$this->register_modifier('compare', array($this, 'smartyCompare'));
		$this->register_modifier('concat', array($this, 'smartyConcat'));
		$this->register_modifier('strtotime', array($this, 'smartyStrtotime'));
		$this->register_modifier('explode', array($this, 'smartyExplode'));
		$this->register_modifier('assign', array($this, 'smartyAssign'));
		$this->register_function('translate', array($this, 'smartyTranslate'));
		$this->register_function('null_link_action', array($this, 'smartyNullLinkAction'));
		$this->register_function('flush', array($this, 'smartyFlush'));
		$this->register_function('call_hook', array($this, 'smartyCallHook'));
		$this->register_function('html_options_translate', array($this, 'smartyHtmlOptionsTranslate'));
		$this->register_block('iterate', array($this, 'smartyIterate'));
		$this->register_function('page_links', array($this, 'smartyPageLinks'));
		$this->register_function('page_info', array($this, 'smartyPageInfo'));
		$this->register_modifier('truncate', array($this, 'smartyTruncate'));

		// Modified vocabulary for creating forms
		$fbv = $this->getFBV();
		$this->register_block('fbvFormSection', array($fbv, 'smartyFBVFormSection'));
		$this->register_block('fbvFormArea', array($fbv, 'smartyFBVFormArea'));
		$this->register_function('fbvFormButtons', array($fbv, 'smartyFBVFormButtons'));
		$this->register_function('fbvElement', array($fbv, 'smartyFBVElement'));
		$this->assign('fbvStyles', $fbv->getStyles());

		$this->register_function('fieldLabel', array($fbv, 'smartyFieldLabel'));

		// register the resource name "core"
		$coreResource = new PKPTemplateResource($this->core_template_dir);
		$this->register_resource('core', array(
			array($coreResource, 'fetch'),
			array($coreResource, 'fetchTimestamp'),
			array($coreResource, 'getSecure'),
			array($coreResource, 'getTrusted')
		));

		$appResource = new PKPTemplateResource($this->app_template_dir);
		$this->register_resource('app', array(
			array($appResource, 'fetch'),
			array($appResource, 'fetchTimestamp'),
			array($appResource, 'getSecure'),
			array($appResource, 'getTrusted')
		));

		$this->register_function('url', array($this, 'smartyUrl'));
		// ajax load into a div or any element
		$this->register_function('load_url_in_el', array($this, 'smartyLoadUrlInEl'));
		$this->register_function('load_url_in_div', array($this, 'smartyLoadUrlInDiv'));

		// load stylesheets from a given context
		$this->register_function('load_stylesheet', array($this, 'smartyLoadStylesheet'));

		/**
		 * Kludge to make sure no code that tries to connect to the
		 * database is executed (e.g., when loading installer pages).
		 */
		if (!defined('SESSION_DISABLE_INIT')) {
			$application = PKPApplication::getApplication();
			$currentVersion = $application->getCurrentVersion();
			$this->assign(array(
				'isUserLoggedIn' => Validation::isLoggedIn(),
				'isUserLoggedInAs' => Validation::isLoggedInAs(),
				'currentVersionString' => $currentVersion->getVersionString(false),
				'itemsPerPage' => Config::getVar('interface', 'items_per_page'),
				'numPageLinks' => Config::getVar('interface', 'page_links'),
			));

			$user = $this->_request->getUser();
			$hasSystemNotifications = false;
			if ($user) {
				$notificationDao = DAORegistry::getDAO('NotificationDAO');
				$notifications = $notificationDao->getByUserId($user->getId(), NOTIFICATION_LEVEL_TRIVIAL);
				if ($notifications->getCount() > 0) {
					$this->assign('hasSystemNotifications', true);
				}

				// Assign the user name to be used in the sitenav
				$this->assign(array(
					'loggedInUsername' => $user->getUserName(),
					'initialHelpState' => (int) $user->getInlineHelp(),
				));
			}
		}

		// Load enabled block plugins and setup active sidebar variables
		PluginRegistry::loadCategory('blocks', true);
		$leftSidebarHooks = HookRegistry::getHooks('Templates::Common::LeftSidebar');
		$this->assign(array(
			'hasLeftSidebar' => !empty($leftSidebarHooks),
		));
	}

	/**
	 * Override the Smarty {include ...} function to allow hooks to be
	 * called.
	 */
	function _smarty_include($params) {
		if (!HookRegistry::call('TemplateManager::include', array($this, &$params))) {
			return parent::_smarty_include($params);
		}
		return false;
	}

	/**
	 * Flag the page as cacheable (or not).
	 * @param $cacheability boolean optional
	 */
	function setCacheability($cacheability = CACHEABILITY_PUBLIC) {
		$this->_cacheability = $cacheability;
	}

	/**
	 * Add a page-specific style sheet.
	 * @param $url string the URL to the style sheet
	 * @param $priority int STYLE_SEQUENCE_...
	 * @param $contexts string|array where stylesheet should be used
	 */
	function addStyleSheet($url, $priority = STYLE_SEQUENCE_NORMAL, $contexts = array('frontend') ) {
		$contexts = (array) $contexts;
		foreach($contexts as $context) {
			$this->_styleSheets[$context][$priority][] = $url;
		}
	}

	/**
	 * Add a page-specific script.
	 * @param $url string the URL to be included
	 */
	function addJavaScript($url) {
		array_push($this->_javaScripts, $url);
	}

	/**
	 * @see Smarty::fetch()
	 */
	function fetch($resource_name, $cache_id = null, $compile_id = null, $display = false) {
		// Add additional java script URLs
		if (!empty($this->_javaScripts)) {
			$baseUrl = $this->get_template_vars('baseUrl');
			$scriptOpen = '	<script type="text/javascript" src="';
			$scriptClose = '"></script>';
			$javaScript = '';
			foreach ($this->_javaScripts as $script) {
				$javaScript .= $scriptOpen . $baseUrl . '/' . $script . $scriptClose . "\n";
			}

			$additionalHeadData = $this->get_template_vars('additionalHeadData');
			$this->assign('additionalHeadData', $additionalHeadData."\n".$javaScript);
		}

		foreach( $this->_styleSheets as &$list ) {
			ksort( $list );
		}
		$this->assign('stylesheets', $this->_styleSheets);

		// If no compile ID was assigned, get one.
		if (!$compile_id) $compile_id = $this->getCompileId($resource_name);

		$result = null;
		if ($display == false && HookRegistry::call('TemplateManager::fetch', array($this, $resource_name, $cache_id, $compile_id, &$result))) return $result;
		return parent::fetch($resource_name, $cache_id, $compile_id, $display);
	}

	/**
	 * Fetch content via AJAX and add it to the DOM, wrapped in a container element.
	 * @param $id string ID to use for the generated container element.
	 * @param $url string URL to fetch the contents from.
	 * @param $element string Element to use for container.
	 * @return JSONMessage The JSON-encoded result.
	 */
	function fetchAjax($id, $url, $element = 'div') {
		return new JSONMessage(true, $this->smartyLoadUrlInEl(
			array(
				'url' => $url,
				'id' => $id,
				'el' => $element,
			),
			$this
		));
	}

	/**
	 * Calculate a compile ID for a resource.
	 * @param $resourceName string Resource name.
	 * @return string
	 */
	function getCompileId($resourceName) {
		return sha1($resourceName);
	}

	/**
	 * Returns the template results as a JSON message.
	 * @param $template string
	 * @param $status boolean
	 * @return JSONMessage JSON object
	 */
	function fetchJson($template, $status = true) {
		import('lib.pkp.classes.core.JSONMessage');
		return new JSONMessage($status, $this->fetch($template));
	}

	/**
	 * Display the template.
	 */
	function display($template, $sendContentType = null, $hookName = null, $display = true) {
		// Set the defaults
		// N.B: This was moved from method signature to allow calls such as: ->display($template, null, null, false)
		if ( is_null($sendContentType) ) {
			$sendContentType = 'text/html';
		}
		if ( is_null($hookName) ) {
			$hookName = 'TemplateManager::display';
		}

		$charset = Config::getVar('i18n', 'client_charset');

		// Give any hooks registered against the TemplateManager
		// the opportunity to modify behavior; otherwise, display
		// the template as usual.

		$output = null;
		if (!HookRegistry::call($hookName, array($this, &$template, &$sendContentType, &$charset, &$output))) {
			// If this is the main display call, send headers.
			if ($hookName == 'TemplateManager::display') {
				// Explicitly set the character encoding
				// Required in case server is using Apache's
				// AddDefaultCharset directive (which can
				// prevent browser auto-detection of the proper
				// character set)
				header('Content-Type: ' . $sendContentType . '; charset=' . $charset);

				// Send caching info
				header('Cache-Control: ' . $this->_cacheability);
			}

			// Actually display the template.
			return $this->fetch($template, null, null, $display);
		} else {
			// Display the results of the plugin.
			echo $output;
		}
	}


	/**
	 * Clear template compile and cache directories.
	 */
	function clearTemplateCache() {
		$this->clear_compiled_tpl();
		$this->clear_all_cache();
	}

	/**
	 * Return an instance of the template manager.
	 * @param $request PKPRequest
	 * @return TemplateManager the template manager object
	 */
	static function &getManager($request = null) {
		if (!isset($request)) {
			$request = Registry::get('request');
			if (Config::getVar('debug', 'deprecation_warnings')) trigger_error('Deprecated call without request object.');
		}
		assert(is_a($request, 'PKPRequest'));

		$instance =& Registry::get('templateManager', true, null); // Reference required

		if ($instance === null) {
			$instance = new TemplateManager($request);
			PluginRegistry::loadCategory('themes', true);
			$instance->initialize();
		}

		return $instance;
	}

	/**
	 * Return an instance of the Form Builder Vocabulary class.
	 * @return TemplateManager the template manager object
	 */
	function getFBV() {
		if(!$this->_fbv) {
			import('lib.pkp.classes.form.FormBuilderVocabulary');
			$this->_fbv = new FormBuilderVocabulary();
		}
		return $this->_fbv;
	}


	//
	// Custom template functions, modifiers, etc.
	//

	/**
	 * Smarty usage: {translate key="localization.key.name" [paramName="paramValue" ...]}
	 *
	 * Custom Smarty function for translating localization keys.
	 * Substitution works by replacing tokens like "{$foo}" with the value of the parameter named "foo" (if supplied).
	 * @param $params array associative array, must contain "key" parameter for string to translate plus zero or more named parameters for substitution.
	 * 	Translation variables can be specified also as an optional
	 * 	associative array named "params".
	 * @param $smarty Smarty
	 * @return string the localized string, including any parameter substitutions
	 */
	function smartyTranslate($params, $smarty) {
		if (isset($params) && !empty($params)) {
			if (!isset($params['key'])) return __('');

			$key = $params['key'];
			unset($params['key']);
			if (isset($params['params']) && is_array($params['params'])) {
				$paramsArray = $params['params'];
				unset($params['params']);
				$params = array_merge($params, $paramsArray);
			}
			return __($key, $params);
		}
	}

	/**
	 * Smarty usage: {null_link_action id="linkId" key="localization.key.name" image="imageClassName"}
	 *
	 * Custom Smarty function for displaying a null link action; these will
	 * typically be attached and handled in Javascript.
	 * @param $smarty Smarty
	 * @return string the HTML for the generated link action
	 */
	function smartyNullLinkAction($params, $smarty) {
		assert(isset($params['id']));

		$id = $params['id'];
		$key = isset($params['key'])?$params['key']:null;
		$hoverTitle = isset($params['hoverTitle'])?true:false;
		$image = isset($params['image'])?$params['image']:null;
		$translate = isset($params['translate'])?false:true;

		import('lib.pkp.classes.linkAction.request.NullAction');
		import('lib.pkp.classes.linkAction.LinkAction');
		$key = $translate ? __($key) : $key;
		$this->assign('action', new LinkAction(
			$id, new NullAction(), $key, $image
		));

		$this->assign('hoverTitle', $hoverTitle);
		return $this->fetch('linkAction/linkAction.tpl');
	}

	/**
	 * Smarty usage: {html_options_translate ...}
	 * For parameter usage, see http://smarty.php.net/manual/en/language.function.html.options.php
	 *
	 * Identical to Smarty's "html_options" function except option values are translated from i18n keys.
	 * @param $params array
	 * @param $smarty Smarty
	 */
	function smartyHtmlOptionsTranslate($params, $smarty) {
		if (isset($params['options'])) {
			if (isset($params['translateValues'])) {
				// Translate values AND output
				$newOptions = array();
				foreach ($params['options'] as $k => $v) {
					$newOptions[__($k)] = __($v);
				}
				$params['options'] = $newOptions;
			} else {
				// Just translate output
				$params['options'] = array_map(array('AppLocale', 'translate'), $params['options']);
			}
		}

		if (isset($params['output'])) {
			$params['output'] = array_map(array('AppLocale', 'translate'), $params['output']);
		}

		if (isset($params['values']) && isset($params['translateValues'])) {
			$params['values'] = array_map(array('AppLocale', 'translate'), $params['values']);
		}

		require_once($this->_get_plugin_filepath('function','html_options'));
		return smarty_function_html_options($params, $smarty);
	}

	/**
	 * Iterator function for looping through objects extending the
	 * ItemIterator class.
	 * Parameters:
	 *  - from: Name of template variable containing iterator
	 *  - item: Name of template variable to receive each item
	 *  - key: (optional) Name of variable to receive index of current item
	 */
	function smartyIterate($params, $content, $smarty, &$repeat) {
		$iterator =& $smarty->get_template_vars($params['from']);

		if (isset($params['key'])) {
			if (empty($content)) $smarty->assign($params['key'], 1);
			else $smarty->assign($params['key'], $smarty->get_template_vars($params['key'])+1);
		}

		// If the iterator is empty, we're finished.
		if (!$iterator || $iterator->eof()) {
			if (!$repeat) return $content;
			$repeat = false;
			return '';
		}

		$repeat = true;

		if (isset($params['key'])) {
			list($key, $value) = $iterator->nextWithKey();
			$smarty->assign_by_ref($params['item'], $value);
			$smarty->assign_by_ref($params['key'], $key);
		} else {
			$smarty->assign_by_ref($params['item'], $iterator->next());
		}
		return $content;
	}

	/**
	 * Display page information for a listing of items that has been
	 * divided onto multiple pages.
	 * Usage:
	 * {page_info from=$myIterator}
	 */
	function smartyPageInfo($params, $smarty) {
		$iterator = $params['iterator'];

		if (isset($params['itemsPerPage'])) {
			$itemsPerPage = $params['itemsPerPage'];
		} else {
			$itemsPerPage = $smarty->get_template_vars('itemsPerPage');
			if (!is_numeric($itemsPerPage)) $itemsPerPage=25;
		}

		$page = $iterator->getPage();
		$pageCount = $iterator->getPageCount();
		$itemTotal = $iterator->getCount();

		if ($pageCount<1) return '';

		$from = (($page - 1) * $itemsPerPage) + 1;
		$to = min($itemTotal, $page * $itemsPerPage);

		return __('navigation.items', array(
			'from' => ($to===0?0:$from),
			'to' => $to,
			'total' => $itemTotal
		));
	}

	/**
	 * Flush the output buffer. This is useful in cases where Smarty templates
	 * are calling functions that take a while to execute so that they can display
	 * a progress indicator or a message stating that the operation may take a while.
	 */
	function smartyFlush($params, $smarty) {
		$smarty->flush();
	}

	function flush() {
		while (ob_get_level()) {
			ob_end_flush();
		}
		flush();
	}

	/**
	 * Call hooks from a template.
	 */
	function smartyCallHook($params, $smarty) {
		$output = null;
		HookRegistry::call($params['name'], array(&$params, &$smarty, &$output));
		return $output;
	}

	/**
	 * Generate a URL into a PKPApp.
	 * @param $params array
	 * @param $smarty object
	 * Available parameters:
	 * - router: which router to use
	 * - context
	 * - page
	 * - component
	 * - op
	 * - path (array)
	 * - anchor
	 * - escape (default to true unless otherwise specified)
	 * - params: parameters to include in the URL if available as an array
	 */
	function smartyUrl($parameters, $smarty) {
		if ( !isset($parameters['context']) ) {
			// Extract the variables named in $paramList, and remove them
			// from the parameters array. Variables remaining in params will be
			// passed along to Request::url as extra parameters.
			$context = array();
			$application = PKPApplication::getApplication();
			$contextList = $application->getContextList();
			foreach ($contextList as $contextName) {
				if (isset($parameters[$contextName])) {
					$context[$contextName] = $parameters[$contextName];
					unset($parameters[$contextName]);
				} else {
					$context[$contextName] = null;
				}
			}
			$parameters['context'] = $context;
		}

		// Extract the reserved variables named in $paramList, and remove them
		// from the parameters array. Variables remaining in parameters will be passed
		// along to Request::url as extra parameters.
		$paramList = array('params', 'router', 'context', 'page', 'component', 'op', 'path', 'anchor', 'escape');
		foreach ($paramList as $parameter) {
			if (isset($parameters[$parameter])) {
				$$parameter = $parameters[$parameter];
				unset($parameters[$parameter]);
			} else {
				$$parameter = null;
			}
		}

		// Merge parameters specified in the {url paramName=paramValue} format with
		// those optionally supplied in {url params=$someAssociativeArray} format
		$parameters = array_merge($parameters, (array) $params);

		// Set the default router
		if (is_null($router)) {
			if (is_a($this->_request->getRouter(), 'PKPComponentRouter')) {
				$router = ROUTE_COMPONENT;
			} else {
				$router = ROUTE_PAGE;
			}
		}

		// Check the router
		$dispatcher = PKPApplication::getDispatcher();
		$routerShortcuts = array_keys($dispatcher->getRouterNames());
		assert(in_array($router, $routerShortcuts));

		// Identify the handler
		switch($router) {
			case ROUTE_PAGE:
				$handler = $page;
				break;

			case ROUTE_COMPONENT:
				$handler = $component;
				break;

			default:
				// Unknown router type
				assert(false);
		}

		// Let the dispatcher create the url
		return $dispatcher->url($this->_request, $router, $context, $handler, $op, $path, $parameters, $anchor, !isset($escape) || $escape);
	}

	/**
	 * Display page links for a listing of items that has been
	 * divided onto multiple pages.
	 * Usage:
	 * {page_links
	 * 	name="nameMustMatchGetRangeInfoCall"
	 * 	iterator=$myIterator
	 *	additional_param=myAdditionalParameterValue
	 * }
	 */
	function smartyPageLinks($params, $smarty) {
		$iterator = $params['iterator'];
		$name = $params['name'];
		if (isset($params['params']) && is_array($params['params'])) {
			$extraParams = $params['params'];
			unset($params['params']);
			$params = array_merge($params, $extraParams);
		}
		if (isset($params['anchor'])) {
			$anchor = $params['anchor'];
			unset($params['anchor']);
		} else {
			$anchor = null;
		}
		if (isset($params['all_extra'])) {
			$allExtra = ' ' . $params['all_extra'];
			unset($params['all_extra']);
		} else {
			$allExtra = '';
		}

		unset($params['iterator']);
		unset($params['name']);

		$numPageLinks = $smarty->get_template_vars('numPageLinks');
		if (!is_numeric($numPageLinks)) $numPageLinks=10;

		$page = $iterator->getPage();
		$pageCount = $iterator->getPageCount();

		$pageBase = max($page - floor($numPageLinks / 2), 1);
		$paramName = $name . 'Page';

		if ($pageCount<=1) return '';

		$value = '';

		$router = $this->_request->getRouter();
		$requestedArgs = null;
		if (is_a($router, 'PageRouter')) {
			$requestedArgs = $router->getRequestedArgs($this->_request);
		}

		if ($page>1) {
			$params[$paramName] = 1;
			$value .= '<a href="' . $this->_request->url(null, null, null, $requestedArgs, $params, $anchor) . '"' . $allExtra . '>&lt;&lt;</a>&nbsp;';
			$params[$paramName] = $page - 1;
			$value .= '<a href="' . $this->_request->url(null, null, null, $requestedArgs, $params, $anchor) . '"' . $allExtra . '>&lt;</a>&nbsp;';
		}

		for ($i=$pageBase; $i<min($pageBase+$numPageLinks, $pageCount+1); $i++) {
			if ($i == $page) {
				$value .= "<strong>$i</strong>&nbsp;";
			} else {
				$params[$paramName] = $i;
				$value .= '<a href="' . $this->_request->url(null, null, null, $requestedArgs, $params, $anchor) . '"' . $allExtra . '>' . $i . '</a>&nbsp;';
			}
		}
		if ($page < $pageCount) {
			$params[$paramName] = $page + 1;
			$value .= '<a href="' . $this->_request->url(null, null, null, $requestedArgs, $params, $anchor) . '"' . $allExtra . '>&gt;</a>&nbsp;';
			$params[$paramName] = $pageCount;
			$value .= '<a href="' . $this->_request->url(null, null, null, $requestedArgs, $params, $anchor) . '"' . $allExtra . '>&gt;&gt;</a>&nbsp;';
		}

		return $value;
	}

	/**
	 * Convert the parameters of a function to an array.
	 */
	function smartyToArray() {
		return func_get_args();
	}

	/**
	 * Concatenate the parameters and return the result.
	 */
	function smartyConcat() {
		$args = func_get_args();
		return implode('', $args);
	}

	/**
	 * Concatenate the parameters and return the result.
	 * @param $a mixed Parameter A
	 * @param $a mixed Parameter B
	 * @param $strict boolean True iff a strict (===) compare should be used
	 * @param $invert booelan True iff the output should be inverted
	 */
	function smartyCompare($a, $b, $strict = false, $invert = false) {
		$result = $strict?$a===$b:$a==$b;
		return $invert?!$result:$result;
	}

	/**
	 * Convert a string to a numeric time.
	 */
	function smartyStrtotime($string) {
		return strtotime($string);
	}

	/**
	 * Override the built-in smarty truncate modifier to support mbstring and HTML tags
	 * text properly, if possible.
	 */
	function smartyTruncate($string, $length = 80, $etc = '...', $break_words = false, $middle = false, $skip_tags = true) {
		if ($length == 0) return '';

		if (PKPString::strlen($string) > $length) {
			if ($skip_tags) {
				if ($middle) {
					$tagsReverse = array();
					$this->_removeTags($string, $tagsReverse, $length, true);
				}
				$tags = array();
				$string = $this->_removeTags($string, $tags, $length);
			}
			if (!empty($etc)) {
				$length = max($length, PKPString::strlen($etc));
			}
			$length--;
			if (!$middle) {
				if(!$break_words) {
					$string = PKPString::regexp_replace('/\s+?(\S+)?$/', '', PKPString::substr($string, 0, $length+1));
				} else $string = PKPString::substr($string, 0, $length+1);
				if ($skip_tags) $string = $this->_reinsertTags($string, $tags);
				return $this->_closeTags($string) . $etc;
			} else {
				$firstHalf = PKPString::substr($string, 0, $length/2);
				$secondHalf = PKPString::substr($string, -$length/2);

				if($break_words) {
					if($skip_tags) {
						$firstHalf = $this->_reinsertTags($firstHalf, $tags);
						$secondHalf = $this->_reinsertTags($secondHalf, $tagsReverse, true);
						return $this->_closeTags($firstHalf) . $etc . $this->_closeTags($secondHalf, true);
					} else {
						return $firstHalf . $etc . $secondHalf;
					}
				} else {
					for($i=$length/2; $string[$i] != ' '; $i++) {
						$firstHalf = PKPString::substr($string, 0, $i+1);
					}
					for($i=$length/2; PKPString::substr($string, -$i, 1) != ' '; $i++) {
						$secondHalf = PKPString::substr($string, -$i-1);
					}

					if ($skip_tags) {
						$firstHalf = $this->_reinsertTags($firstHalf, $tags);
						$secondHalf = $this->_reinsertTags($secondHalf, $tagsReverse, strlen($string));
						return $this->_closeTags($firstHalf) . $etc . $this->_closeTags($secondHalf, true);
					} else {
						return $firstHalf . $etc . $secondHalf;
					}
				}
			}
		} else {
			return $string;
		}
	}

	/**
	 * Helper function: Remove XHTML tags and insert them into a global array along with their position
	 * @author Matt Crider
	 * @param string
	 * @param array
	 * @param boolean
	 * @param int
	 * @return string
	 */
	function _removeTags($string, &$tags, $length, $reverse = false) {
		if($reverse) {
			return $this->_removeTagsAuxReverse($string, 0, $tags, $length);
		} else {
			return $this->_removeTagsAux($string, 0, $tags, $length);
		}
	}

	/**
	 * Helper function: Recursive function called by _removeTags
	 * @author Matt Crider
	 * @param string
	 * @param int
	 * @param array
	 * @param int
	 * @return string
	 */
	function _removeTagsAux($string, $loc, &$tags, $length) {
		$newString = '';

		for($i = 0; $i < strlen($string); $i++) {
			if(PKPString::substr($string, $i, 1) == '<') {
				// We've found the beginning of an HTML tag, find the position of its ending
				$closeBrack = PKPString::strpos($string, '>', $i);

				if($closeBrack) {
					// Add the tag and its position to the tags array reference
					$tags[] = array(PKPString::substr($string, $i, $closeBrack-$i+1), $i);
					$i += $closeBrack-$i;
					continue;
				}
			}
			$length--;
			$newString = $newString . PKPString::substr($string, $i, 1);
		}

		return $newString;
	}

	/**
	 * Helper function: Recursive function called by _removeTags
	 * Removes tags from the back of the string and keeps a record of their position from the back
	 * @author Matt Crider
	 * @param string
	 * @param int loc Keeps track of position from the back of original string
	 * @param array
	 * @param int
	 * @return string
	 */
	function _removeTagsAuxReverse($string, $loc, &$tags, $length) {
		$newString = '';

		for($i = PKPString::strlen($string); $i > 0; $i--) {
			if(PKPString::substr($string, $backLoc, 1) == '>') {
				$tag = '>';
				$openBrack = 1;
				while (PKPString::substr($string, $backLoc-$openBrack, 1) != '<') {
					$tag = PKPString::substr($string, $backLoc-$openBrack, 1) . $tag;
					$openBrack++;
				}
				$tag = '<' . $tag;
				$openBrack++;

				$tags[] = array($tag, $loc);
				$i -= $openBrack+1;
				continue;
			}
			$length--;
			$newString = $newString . PKPString::substr($string, $i, 1);
		}

		return $newString;
	}


	/**
	 * Helper function: Reinsert tags from the tag array into their original position in the string
	 * @author Matt Crider
	 * @param string
	 * @param array
	 * @param boolean Set to true to reinsert tags starting at the back of the string
	 * @return string
	 */
	function _reinsertTags($string, &$tags, $reverse = false) {
		if(empty($tags)) return $string;

		for($i = 0; $i < count($tags); $i++) {
			$length = PKPString::strlen($string);
			if ($tags[$i][1] < PKPString::strlen($string)) {
				if ($reverse) {
					if ($tags[$i][1] == 0) { // Cannot use -0 as the start index (its same as +0)
						$string = PKPString::substr_replace($string, $tags[$i][0], $length, 0);
					} else {
						$string = PKPString::substr_replace($string, $tags[$i][0], -$tags[$i][1], 0);
					}
				} else {
					$string = PKPString::substr_replace($string, $tags[$i][0], $tags[$i][1], 0);
				}
			}
		}

		return $string;
	}

	/**
	 * Helper function: Closes all dangling XHTML tags in a string
	 * Modified from http://milianw.de/code-snippets/close-html-tags
	 *  by Milian Wolff <mail@milianw.de>
	 * @param string
	 * @return string
	 */
	function _closeTags($string, $open = false){
		// Put all opened tags into an array
		PKPString::regexp_match_all("#<([a-z]+)( .*)?(?!/)>#iU", $string, $result);
		$openedtags = $result[1];

		// Put all closed tags into an array
		PKPString::regexp_match_all("#</([a-z]+)>#iU", $string, $result);
		$closedtags = $result[1];
		$len_opened = count($openedtags);
		$len_closed = count($closedtags);
		// All tags are closed
		if(count($closedtags) == $len_opened){
			return $string;
		}

		$openedtags = array_reverse($openedtags);
		$closedtags = array_reverse($closedtags);

		if ($open) {
			// Open tags
			for($i=0; $i < $len_closed; $i++) {
				if (!in_array($closedtags[$i],$openedtags)){
					$string = '<'.$closedtags[$i].'>' . $string;
				} else {
					unset($openedtags[array_search($closedtags[$i],$openedtags)]);
				}
			}
			return $string;
		} else {
			// Close tags
			for($i=0; $i < $len_opened; $i++) {
				if (!in_array($openedtags[$i],$closedtags)){
					$string .= '</'.$openedtags[$i].'>';
				} else {
					unset($closedtags[array_search($openedtags[$i],$closedtags)]);
				}
			}
			return $string;
		}
	}

	/**
	 * Split the supplied string by the supplied separator.
	 */
	function smartyExplode($string, $separator) {
		return explode($separator, $string);
	}

	/**
	 * Assign a value to a template variable.
	 */
	function smartyAssign($value, $varName, $passThru = false) {
		if (isset($varName)) {
			$this->assign($varName, $value);
		}
		if ($passThru) return $value;
	}

	/**
	 * Smarty usage: {load_url_in_el el="htmlElement" id="someHtmlId" url="http://the.url.to.be.loaded.into.the.grid"}
	 *
	 * Custom Smarty function for loading a URL via AJAX into any HTML element
	 * @param $params array associative array
	 * @param $smarty Smarty
	 * @return string of HTML/Javascript
	 */
	function smartyLoadUrlInEl($params, $smarty) {
		// Required Params
		if (!isset($params['el'])) {
			$smarty->trigger_error("el parameter is missing from load_url_in_el");
		}
		if (!isset($params['url'])) {
			$smarty->trigger_error("url parameter is missing from load_url_in_el");
		}
		if (!isset($params['id'])) {
			$smarty->trigger_error("id parameter is missing from load_url_in_el");
		}

		$this->assign(array(
			'inEl' => $params['el'],
			'inElUrl' => $params['url'],
			'inElElId' => $params['id'],
			'inElClass' => isset($params['class'])?$params['class']:null,
		));

		if (isset($params['placeholder'])) {
			$this->assign('inElPlaceholder', $params['placeholder']);
		} elseif (isset($params['loadMessageId'])) {
			$loadMessageId = $params['loadMessageId'];
			$this->assign('inElPlaceholder', __($loadMessageId, $params));
		} else {
			$this->assign('inElPlaceholder', $this->fetch('common/loadingContainer.tpl'));
		}

		return $this->fetch('common/urlInEl.tpl');
	}

	/**
	 * Smarty usage: {load_url_in_div id="someHtmlId" url="http://the.url.to.be.loaded.into.the.grid"}
	 *
	 * Custom Smarty function for loading a URL via AJAX into a DIV. Convenience
	 * wrapper for smartyLoadUrlInEl.
	 * @param $params array associative array
	 * @param $smarty Smarty
	 * @return string of HTML/Javascript
	 */
	function smartyLoadUrlInDiv($params, $smarty) {
		$params['el'] = 'div';
		return $this->smartyLoadUrlInEl( $params, $smarty );
	}

	/**
	 * Smarty usage: {load_stylesheet context="frontend" stylesheets=$stylesheets}
	 *
	 * Custom Smarty function for printing stylesheets attached to a context.
	 * @param $params array associative array
	 * @param $smarty Smarty
	 * @return string of HTML/Javascript
	 */
	function smartyLoadStylesheet($params, $smarty) {

		if (empty($params['stylesheets'])) {
			return;
		}

		if (empty($params['context'])) {
			$context = 'frontend';
		}

		$output = '';
		foreach($params['stylesheets'] as $context => $priorityList) {
			if ($context != $params['context']) {
				continue;
			}
			foreach($priorityList as $files) {
				foreach($files as $url) {
					$output .= '<link rel="stylesheet" href="' . $url . '" type="text/css" />';
				}
			}
		}

		return $output;
	}
}

?>
