<?php
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

namespace OCA\RecommendationAssistant\Controller;

use OCA\RecommendationAssistant\AppInfo\Application;
use OCA\RecommendationAssistant\Db\RecommendationManager;
use OCA\RecommendationAssistant\Objects\Recommendation;
use OCP\AppFramework\Controller;
use OCP\AppFramework\Http\DataResponse;
use OCP\AppFramework\Http\JSONResponse;
use OCP\AppFramework\Http\TemplateResponse;
use OCP\Files\IRootFolder;
use OCP\IRequest;
use OCP\Template;

class FrontendController extends Controller {
	private $recommendationManager = null;
	private $userId = null;
	private $rootFolder = null;

	public function __construct(
		$appName,
		$UserId,
		IRequest $request,
		IRootFolder $rootFolder,
		RecommendationManager $recommendationManager) {
		parent::__construct($appName, $request);
		$this->recommendationManager = $recommendationManager;
		$this->userId = $UserId;
		$this->rootFolder = $rootFolder;
	}

	/**
	 * @NoAdminRequired
	 * @NoCSRFRequired
	 *
	 * @return TemplateResponse
	 */
	public function show() {
		return new TemplateResponse(Application::APP_ID, 'index', []);
	}
}
