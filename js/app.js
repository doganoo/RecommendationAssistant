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
		'<table id="filestable" data-allow-public-upload="<?php p($_[\'publicUploadEnabled\'])?>" data-preview-x="32" data-preview-y="32">' +
		'			<thead>' +
		'				<tr>' +
		'					<th id="headerSelection" class="hidden column-selection">' +
		'					</th>' +
		'					<th id="headerName" class="hidden column-name">' +
		'						<div id="headerName-container">' +
		'							<a class="name sort columntitle" data-sort="name">' +
		'								<span>Name</span>' +
		'								<span class="sort-indicator"></span>' +
		'							</a>' +
		'							<span id="selectedActionsList" class="selectedActions">' +
		'								<a href="" class="copy-move">' +
		'									<span class="icon icon-external"></span>' +
		'									<span>Move or copy</span>' +
		'								</a>' +
		'								<a href="" class="download">' +
		'									<span class="icon icon-download"></span>' +
		'									<span>Download</span>' +
		'								</a>' +
		'								<a href="" class="delete-selected">' +
		'									<span class="icon icon-delete"></span>' +
		'									<span>Delete</span>' +
		'								</a>' +
		'							</span>' +
		'						</div>' +
		'					</th>' +
		'					<th id="headerSize" class="hidden column-size">' +
		'						<a class="size sort columntitle" data-sort="size">' +
		'							<span>Size</span>' +
		'							<span class="sort-indicator"></span>' +
		'						</a>' +
		'					</th>' +
		'					<th id="headerDate" class="hidden column-mtime">' +
		'						<a id="modified" class="columntitle" data-sort="mtime">' +
		'							<span>Modified</span>' +
		'							<span class="sort-indicator"></span>' +
		'						</a>' +
		'						<span class="selectedActions">' +
		'							<a href="" class="delete-selected">' +
		'								<span>Delete</span>' +
		'								<span class="icon icon-delete"></span>' +
		'							</a>' +
		'						</span>' +
		'					</th>' +
		'			</tr>' +
		'		</thead>' +
		'		<tbody id="fileList">' +
		'{{#each this}}' +
		'			<tr>' +
		'				<td class="selection">' +
		'					<input id="select-files-8" class="selectCheckBox checkbox" type="hidden">' +
		'						<label for="select-files-8">' +
		'							<span class="hidden-visually">Auswählen</span>' +
		'						</label>' +
		'				</td>' +
		'				<td>' +
		'					<a class="name" href="/nextcloud/remote.php/webdav/{{ fileName }}">' +
		'						<div class="thumbnail-wrapper">' +
		'							<div class="thumbnail" style="background-image: url(&quot;/nextcloud/index.php/core/preview.png?file=%2F' + '{{ fileName }}' + '&amp;c=05b8db27cc3c06f7e8305e3480096761&amp;x=64&amp;y=64&amp;forceIcon=0&quot;);">' +
		'							</div>' +
		'						</div>' +
		'							<span class="nametext">' +
		'								<span class="innernametext">{{ fileName }}</span>' +
		'								<span class="fileactions">' +
		'							</span>' +
		'					</a>' +
		'				</td>' +
		'				<td class="filesize" style="color:rgb(160,160,160)">' + OC.Util.humanFileSize('{{ fileSize }}') + '</td>' +
		'				<td>' +
		'					<span class="modified live-relative-timestamp" title="" data-timestamp="{{ mTime }}" style="color:rgb(74,74,74)" data-original-title="10. Januar 2018 20:27">TODO</span>' +
		'				</td>' +
		'</tr>' +
		'{{/each}}' +
		'\t</tbody>\n' +
		'<tfoot>' +
		'	<tr style="height: 100px;">' +
		'		<td>' +
		'		</td>' +
		'		<td>' +
		'			<span class="info">' +
		'				<span class="dirinfo hidden"></span>' +
		'				<span class="connector hidden"></span>' +
		'				<span class="fileinfo"></span>' +
		'				<span class="hiddeninfo hidden"></span>' +
		'				<span class="filter hidden"></span>' +
		'				</span>' +
		'		</td>' +
		'		<td class="filesize"></td>' +
		'		<td class="date"></td>' +
		'	</tr>' +
		'</tfoot>' +
		'</table>' +
		'</div>' +
		'</div>';


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