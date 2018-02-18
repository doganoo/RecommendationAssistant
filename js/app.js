/**
 * @copyright Copyright (c) 2017, Dogan Ucar (dogan@dogan-ucar.de)
 *
 * @license GNU AGPL version 3 or any later version
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as
 * published by the Free Software Foundation, either version 3 of the
 * License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 */

(function () {

	if (!OCA.Recommendations) {
		OCA.Recommendations = {};
	}
	/**
	 * @namespace
	 */
	OCA.Recommendations.Util = {
		/**
		 * Initialize the recommendations plugin.
		 *
		 * @param {OCA.Files.FileList} fileList file list to be extended
		 */

		attach: function (fileList) {
			var that = this;

			if (fileList.getCurrentDirectory() === '/') {
				that.updateRecommendationsView(true);
			}

			/**
			 * listen to the changeDirectory event
			 */
			fileList.$el.on('changeDirectory', function (data) {
				var dir = data.dir.toString();
				that.updateRecommendationsView(dir === "/");
			});
			// fileList.$el.on('fileActionsReady', function (data) {
			// });

		},

		/**
		 * updates the recommendation view.
		 *
		 * @param isRootDir
		 */
		updateRecommendationsView: function (isRootDir) {
			// request new recommendations and update the rendered template
			'use strict';
			var source = '<div class="apps-header">' +
				'<span id="recommendation-headline" class="extension">' + t('recommendation_assistant', 'Recommendations') + '</span>' +
				'<div id="recommendation-content" class="section group">' +
				'{{#each this}}' +
				'<div class="col recommendation-columns">' +
				'	<a id="{{fileId}}" class="name" href="/nextcloud/remote.php/webdav/{{ fileNameAndExtension }}">' +
				'' +
				'' +
				'' +
				'<style>' +
				'	#{{fileId}}:hover {' +
				'		background: blue;' +
				'	}' +
				'</style>' +
				'' +
				'' +
				' 	<div class="thumbnail-wrapper">' +
				'		<div id="recommendation-file-thumbnail" class="thumbnail" style="background-image: url(\'{{ getPreviewUrl mimeType fileNameAndExtension }} \'); height: 64px; width: 64px; float: left">' +
				'		</div>' +
				'			<span class="nametext">' +
				'				<span id="recommendation-content-file-name" class="innernametext">{{fileName}}</span>' +
				'				<span class="extension">.{{extension}}</span>' +
				'			</span>' +
				'			<div style="clear: right;"></div>' +
				'				<span class="nametext">' +
				'				<span class="extension">{{ getTransparencyDescription transparancyCode }}</span>' +
				'			</span>' +
				'	</div>' +
				'	</a>' +
				'</div>' +
				'{{/each}}' +
				'' +
				'</div>';

			//empty the view before it is reloaded / for hiding on sub dirs
			$("#recommendations").html('');

			var url = OC.generateUrl('apps/recommendation_assistant/recommendation_assistant_for_files');
			$.ajax({
				url: url,
				type: 'GET',
				contentType: 'application/json',
			}).done(function (response) {
				if (objectSize(response) > 0 && isRootDir) {
					var div = $('<div id="recommendations"><span class="icon-loading"></span></div>');
					$('#controls').after(div);
					var template = Handlebars.compile(source);
					var html = template(response);
					div.html(html);
				}
			}).fail(function (response, code) {
				//TODO what to do in case of error?
			});
		}

	};

})();

/**
 * count the element size of an object
 *
 * @param obj
 * @returns {number}
 */
function objectSize (obj) {
	var count = 0;
	$.each(obj, function () {
		count++;
	});
	return count;
}

/**
 * returns an preview for a given urlSpec
 *
 * @param urlSpec
 * @returns {string}
 */
function generatePreviewUrl (urlSpec) {
	urlSpec.x = 64;
	urlSpec.y = 64;
	urlSpec.forceIcon = 0;
	return OC.generateUrl('/core/preview.png?') + $.param(urlSpec);
}

/**
 * helper method to call getPreviewUrl / generatePreviewUrl methods
 * in order to generate a file preview
 */
Handlebars.registerHelper("getPreviewUrl", function (mime, name) {
	var iconURL = OC.MimeType.getIconUrl(mime);
	var previewURL,
		urlSpec = {};

	urlSpec.file = "/" + name;

	previewURL = generatePreviewUrl(urlSpec);
	previewURL = previewURL.replace('(', '%28');
	previewURL = previewURL.replace(')', '%29');
	// var img = new Image();
	// img.onload = function () {
	// 	// if loading the preview image failed (no preview for the mimetype) then img.width will < 5
	// 	if (img.width > 5) {
	// 	} else {
	// 	}
	// };
	// img.src = previewURL;

	var imageExists = urlExists(previewURL);
	if (imageExists) {
		return previewURL;
	} else {
		return iconURL;
	}
});

function urlExists (url) {
	var http = new XMLHttpRequest();
	http.open('HEAD', url, false);
	http.send();
	return http.status != 404;
}


Handlebars.registerHelper("getTransparencyDescription", function (code) {
		code = parseInt(code);
		/*
		 *please check OCA\RecommendationAssistant\Objects\HybridItem for
		 * transparency codes
		 */
		if (code === 0) {
			return "similarity with your documents and preferences";
		} else if (code === 1) {
			return "similarity with preferences";
		} else if (code === 2) {
			return "similarity with your documents";
		}
		return "";
	}
);

/**
 * Register the Util class to the files app
 */
OC.Plugins.register('OCA.Files.FileList', OCA.Recommendations.Util);