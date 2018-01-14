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


// example for calling the PUT /notes/1 URL
var baseUrl = OC.generateUrl('/apps/recommendation_assistant_for_files');
$.ajax({
	url: baseUrl + '/recommendation_assistant_for_files/',
	type: 'GET',
	contentType: 'application/json',
}).done(function (response) {
	alert("success");
}).fail(function (response, code) {
	// handle failure
	alert(code);
});