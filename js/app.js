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
	'use strict';
	var source = '<div class="apps-header">' +
		'<h2 style="text-align: center">' + t('recommendation_assistant', 'Recommendations') + '</h2>' +
		'<div class="recommendation_grid_wrapper">' +
		'{{#each this}}' +
		'<div class="recommendation_box">' +
		'<a class="name" style="color: white" href="/nextcloud/remote.php/webdav/{{ fileName }}">' +
		'{{ fileName }}' +
		'</a>' +
		'</div>' +
		'{{/each}}' +
		'</div>' +
		'</div>'
	;


	var url = OC.generateUrl('apps/recommendation_assistant/recommendation_assistant_for_files');
	$.ajax({
		url: url,
		type: 'GET',
		contentType: 'application/json',
	}).done(function (response) {

		if (objectSize(response) > 0) {
			var div = $('<div id="recommendations"><span class="icon-loading"></span></div>');
			$('#controls').after(div);
			var template = Handlebars.compile(source);
			var html = template(response);
			div.html(html);
		}
	}).fail(function (response, code) {
	});

})();


function objectSize (obj) {
	var L = 0;
	$.each(obj, function (i, elem) {
		L++;
	});
	return L;
}