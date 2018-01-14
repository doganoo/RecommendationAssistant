<?php
/**
 * Created by PhpStorm.
 * User: dogano
 * Date: 14.01.18
 * Time: 13:32
 */

namespace OCA\RecommendationAssistant\Controller;


use OCA\RecommendationAssistant\Db\RecommendationMapper;
use OCP\AppFramework\Controller;
use OCP\AppFramework\Http\JSONResponse;
use OCP\IRequest;


class RecommendationController extends Controller {
	private $mapper;
	private $userId;

	public function __construct($AppName, IRequest $request, RecommendationMapper $mapper, $UserId) {
		parent::__construct($AppName, $request);
		$this->mapper = $mapper;
		$this->userId = $UserId;
	}

	/**
	 * @NoAdminRequired
	 * @NoCSRFRequired
	 */
	public function index() {
		return new JSONResponse(
			$this->mapper->findAll($this->userId)
		);
	}
}