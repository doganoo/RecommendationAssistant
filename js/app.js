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
(function (OC, OCA, _, $) {
	"use strict";

	OCA = OCA || {};

	OCA.RecommendationAssistant = {

		initialise: function () {
			this._loadData();
		},

		_loadData: function () {
			$.ajax({
				url: OC.linkToOCS('apps/recommendation_assistant', 2) + 'api',
				beforeSend: function (request) {
					request.setRequestHeader('Accept', 'application/json');
				},
				success: function (result) {
					_.each(result.ocs.data, function (data) {
						$('#container').append(data).append($('<br>'));
					});
				},
				error: function () {
					OC.Notification.showTemporary(t('recommendation_assistant', 'Failed to load data'));
				}
			});
		}
	};
})(OC, OCA, _, $);

$(document).ready(function () {
	OCA.RecommendationAssistant.initialise();
});
