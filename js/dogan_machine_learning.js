/**
 * @copyright (c) 2017 Joas Schilling <coding@schilljs.com>
 *
 * @author Joas Schilling <coding@schilljs.com>
 *
 * This file is licensed under the Affero General Public License version 3 or
 * later. See the COPYING file.
 */

(function(OC, OCA, _) {
	OCA = OCA || {};

	OCA.DoganMachineLearning = {

		initialise: function() {
			this._loadData();
		},

		_loadData: function() {
			$.ajax({
				url: OC.linkToOCS('apps/dogan_machine_learning', 2) + 'api',
				beforeSend: function (request) {
					request.setRequestHeader('Accept', 'application/json');
				},
				success: function(result) {
					_.each(result.ocs.data, function(data) {
						$('#container').append(data).append($('<br>'));
					});
				},
				error: function() {
					OC.Notification. showTemporary(t('dogan_machine_learning', 'Failed to load data'));
				}
			});
		}
	};
})(OC, OCA, _);

$(document).ready(function () {
	OCA.DoganMachineLearning.initialise();
});
