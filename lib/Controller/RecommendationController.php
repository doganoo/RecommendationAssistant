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


use OCA\RecommendationAssistant\Db\Entity\Recommendation;
use OCA\RecommendationAssistant\Db\Mapper\RecommendationMapper;
use OCA\RecommendationAssistant\Log\Logger;
use OCA\RecommendationAssistant\Util\NodeUtil;
use OCP\AppFramework\Controller;
use OCP\AppFramework\Http\JSONResponse;
use OCP\Files\File;
use OCP\Files\InvalidPathException;
use OCP\Files\NotFoundException;
use OCP\IRequest;

/**
 * RecommendationController class serves as a gateway between the business logic
 * and the GUI. The class queries the database and passes the results as a
 * JSONResponse to the view.
 *
 * @package OCA\RecommendationAssistant\Controller
 * @since 1.0.0
 */
class RecommendationController extends Controller {

	/**
	 * @var RecommendationMapper $mapper the mapper class for recommendations
	 */
	private $mapper;

	/**
	 * @var string $userId the user id
	 */
	private $userId;

	/**
	 * Class constructor gets multiple instances injected
	 *
	 * @param string $AppName the apps name
	 * @param IRequest $request the request instance
	 * @param RecommendationMapper $mapper the mapper class that queries
	 * all data from the database
	 * @param string $UserId the user id of the logged in user
	 *
	 * @since 1.0.0
	 */
	public function __construct(
		$AppName,
		IRequest $request,
		RecommendationMapper $mapper,
		$UserId) {
		parent::__construct($AppName, $request);
		$this->mapper = $mapper;
		$this->userId = $UserId;
	}

	/**
	 * This method is defined in routes.php as the entry point to the this class.
	 *
	 * @NoAdminRequired
	 * @NoCSRFRequired
	 *
	 * @since 1.0.0
	 */
	public function index() {
		$entities = $this->mapper->findAll($this->userId);
		/** @var Recommendation $entity */
		foreach ($entities as $key => &$entity) {
			$id = $entity->fileId;
			/** @var File $node */
			$node = NodeUtil::getFile($id, $this->userId);
			if ($node == null) {
				unset($entities[$key]);
				continue;
			}
			$name = $node->getName();
			$size = 0;
			$mTime = (new \DateTime())->getTimestamp();
			try {
				$size = $node->getSize();
			} catch (InvalidPathException $e) {
				Logger::warn($e->getMessage());
			} catch (NotFoundException $e) {
				Logger::warn($e->getMessage());
			}
			try {
				$mTime = $node->getMTime();
			} catch (InvalidPathException $e) {
				Logger::warn($e->getMessage());
			} catch (NotFoundException $e) {
				Logger::warn($e->getMessage());
			}
			$extension = pathinfo($name, PATHINFO_EXTENSION);
			$fileNameAndExtension = $name;
			$entity->fileName = pathinfo($name, PATHINFO_FILENAME);
			$entity->mTime = $mTime;
			$entity->fileSize = $size;
			$entity->extension = $extension;
			$entity->fileNameAndExtension = $fileNameAndExtension;
		}
		$jsonResponse = new JSONResponse($entities);
		return $jsonResponse;
	}
}