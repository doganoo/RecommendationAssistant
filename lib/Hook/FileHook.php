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

namespace OCA\RecommendationAssistant\Hook;

use OCA\Files_External\NotFoundException;
use OCA\RecommendationAssistant\Db\ChangedFilesManager;
use OCA\RecommendationAssistant\Db\ProcessedFilesManager;
use OCA\RecommendationAssistant\Objects\Logger;
use OCP\Files\File;
use OCP\Files\IRootFolder;
use OCP\Files\Node;
use OCP\IRequest;
use OCP\IUserSession;


class FileHook {

	private $userSession = null;
	private $request = null;
	private $rootFolder = null;
	private $processedFileManager = null;
	private $changedFilesManager = null;

	public function __construct(IUserSession $userSession,
								IRequest $request,
								IRootFolder $rootFolder,
								ProcessedFilesManager $processedFilesManager,
								ChangedFilesManager $changedFilesManager
	) {
		$this->userSession = $userSession;
		$this->request = $request;
		$this->rootFolder = $rootFolder;
		$this->processedFileManager = $processedFilesManager;
		$this->changedFilesManager = $changedFilesManager;
	}

	public
	function run($params) {
		Logger::error("run");
		$path = $params["path"];

		// Do not add activities for .part-files
		if (substr($path, -5) === '.part') {
			return;
		}
		if ($this->userSession->getUser() === null) {
			// User is not logged in, this download is handled by the files_sharing app
			return;
		}
		if (!$this->request->isUserAgent([IRequest::USER_AGENT_CLIENT_DESKTOP])) {
//			return;
		}
		if ($path === '/' || $path === '' || $path === null) {
			return;
		}


		$node = $this->getSourcePathAndOwner($path);

		if ($node instanceof File) {
			$this->changedFilesManager->handle($node, "edit");
			$this->processedFileManager->deleteFile($node);
		}
		Logger::error("run ende");
	}

	public function runFavorite(string $userId, string $fileId, string $caller) {
		$node = $this->getNodeById($fileId);
		if ($caller == "addFavorite") {
			$this->changedFilesManager->handle($node, "favorite");
		} else if ($caller == "removeFavorite") {
			$this->changedFilesManager->deleteFile($node, "favorite");
		}
	}


	private
	function getNodeById($fileId): Node {
		try {
			$currentUserId = $this->userSession->getUser()->getUID();
			$userFolder = $this->rootFolder->getUserFolder($currentUserId);
			$node = $userFolder->getById($fileId);
		} catch (NotFoundException $exception) {
			Logger::error($exception->getMessage());
		}
		return $node[0];
	}

	private
	function getSourcePathAndOwner($path): Node {
		try {
			$currentUserId = $this->userSession->getUser()->getUID();
			$userFolder = $this->rootFolder->getUserFolder($currentUserId);
			$node = $userFolder->get($path);
		} catch (NotFoundException $exception) {
			Logger::error($exception->getMessage());
		}
		return $node;
	}


}