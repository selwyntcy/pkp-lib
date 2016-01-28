/**
 * @file js/controllers/UploaderHandler.js
 *
 * Copyright (c) 2014-2016 Simon Fraser University Library
 * Copyright (c) 2000-2016 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class UploaderHandler
 * @ingroup js_controllers
 *
 * @brief PKP file uploader widget handler.
 */
(function($) {


	/**
	 * @constructor
	 *
	 * @extends $.pkp.classes.Handler
	 *
	 * @param {jQueryObject} $uploader the wrapped HTML uploader element.
	 * @param {{
	 *  uploadUrl: string,
	 *  baseUrl: string
	 *  }} options options to be passed
	 *  into the validator plug-in.
	 */
	$.pkp.controllers.UploaderHandler = function($uploader, options) {
		this.parent($uploader, options);

		// Check whether we really got a div to attach
		// our uploader to.
		if (!$uploader.is('div')) {
			throw new Error(['An uploader widget controller can only be attached',
				' to a div!'].join(''));
		}

		// Create uploader settings.
		var uploaderOptions = $.extend(
				{ },
				// Default settings.
				this.self('DEFAULT_PROPERTIES_'),
				// Non-default settings.
				{
					url: options.uploadUrl,
					// Flash settings
					flash_swf_url: options.baseUrl +
							'/lib/pkp/lib/vendor/moxiecode/plupload/js/Moxie.swf',
					// Silverlight settings
					silverlight_xap_url: options.baseUrl +
							'/lib/pkp/lib/vendor/moxiecode/plupload/js/Moxie.xap'
				}),
				pluploader;

		// Create the uploader with the puploader plug-in.
		// Setup the upload widget.
		$uploader.plupload(uploaderOptions);

		// Hack to fix the add files button in non-FF browsers
		// courtesy of: http://stackoverflow.com/questions/5471141/
		pluploader = $uploader.plupload('getUploader');
		pluploader.refresh();
		if (!/Firefox[\/\s](\d+\.\d+)/.test(navigator.userAgent)) {
			// On my Iceweasel 9.0.1, running the hack below
			// results in the "Add Files" button being unclickable
			// (html5 runtime).
			$uploader.find('div.plupload').css('z-index', 99999);
		}

		$uploader.find('div.plupload input').css('z-index', 99999);

		// Bind to the pluploader for some configuration
		pluploader.bind('FilesAdded',
				this.callbackWrapper(this.limitQueueSize));

		pluploader.bind('QueueChanged',
				this.callbackWrapper(this.refreshUploader));
	};
	$.pkp.classes.Helper.inherits(
			$.pkp.controllers.UploaderHandler, $.pkp.classes.Handler);


	//
	// Public methods
	//
	/**
	 * Limit the queue size of the uploader to one file only.
	 * @param {Object} caller The original context in which the callback was called.
	 * @param {Object} pluploader The pluploader object.
	 * @param {Object} file The data of the uploaded file.
	 *
	 */
	$.pkp.controllers.UploaderHandler.prototype.
			limitQueueSize = function(caller, pluploader, file) {

		// Prevent > 1 files from being added.
		if (pluploader.files.length > 1) {
			pluploader.splice(0, 1);
			pluploader.refresh();
		}
	};


	/**
	 * Refresh the uploader interface so buttons work correctly.
	 * @param {Object} caller The original context in which the callback was called.
	 * @param {Object} pluploader The pluploader object.
	 * @param {Object} file The data of the uploaded file.
	 *
	 */
	$.pkp.controllers.UploaderHandler.prototype.
			refreshUploader = function(caller, pluploader, file) {
		pluploader.refresh();
	};


	//
	// Private static properties
	//
	/**
	 * Default options
	 * @private
	 * @type {Object}
	 * @const
	 */
	$.pkp.controllers.UploaderHandler.DEFAULT_PROPERTIES_ = {
		// General settings
		runtimes: 'html5,flash,silverlight,html4',
		max_file_size: $.pkp.cons.UPLOAD_MAX_FILESIZE,
		multi_selection: false,
		file_data_name: 'uploadedFile',
		multipart: true,
		headers: {'browser_user_agent': navigator.userAgent}
	};


/** @param {jQuery} $ jQuery closure. */
}(jQuery));
