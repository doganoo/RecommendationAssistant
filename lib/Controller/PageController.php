<?php
/**
 * Created by PhpStorm.
 * User: dogano
 * Date: 14.01.18
 * Time: 13:30
 */

namespace OCA\RecommendationAssistant\Controller;


use OCA\RecommendationAssistant\AppInfo\Application;
use OCP\AppFramework\Controller;
use OCP\AppFramework\Http\TemplateResponse;
use OCP\IRequest;


class PageController extends Controller {
	public function __construct($AppName, IRequest $request) {
		parent::__construct($AppName, $request);
	}

	/**
	 * @NoAdminRequired
	 * @NoCSRFRequired
	 */
	public function index() {
		return new TemplateResponse(Application::APP_ID, 'main');
	}
}