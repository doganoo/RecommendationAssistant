<?php
/**
 * @copyright Copyright (c) 2017 Joas Schilling <coding@schilljs.com>
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

namespace OCA\DoganMachineLearning;

use OCP\Files\IAppData;
use OCP\Files\NotFoundException;
use OCP\Files\SimpleFS\ISimpleFile;
use OCP\IUser;
use OCP\IUserSession;

class Listener {

	/** @var IUserSession */
	protected $userSession;

	/** @var IAppData */
	protected $appData;

	/**
	 * @param IUserSession $userSession
	 * @param IAppData $appData
	 */
	public function __construct(IUserSession $userSession, IAppData $appData) {
		$this->userSession = $userSession;
		$this->appData = $appData;
	}

	/**
	 * @param array $params
	 * @throws \OCP\Files\NotPermittedException
	 * @throws \RuntimeException
	 */
	public function fileCreated($params) {
		$this->storeAction($params['path'], 'created');
	}

	/**
	 * @param array $params
	 * @throws \OCP\Files\NotPermittedException
	 * @throws \RuntimeException
	 */
	public function fileUpdated($params) {
		$this->storeAction($params['path'], 'updated');
	}

	/**
	 * @param array $params
	 * @throws \OCP\Files\NotPermittedException
	 * @throws \RuntimeException
	 */
	public function fileDeleted($params) {
		$this->storeAction($params['path'], 'deleted');
	}

	/**
	 * @param string $path
	 * @param string $action
	 * @throws \OCP\Files\NotPermittedException
	 * @throws \RuntimeException
	 */
	protected function storeAction($path, $action) {
		$user = $this->userSession->getUser();
		if (!$user instanceof IUser) {
			// Guest user
			return;
		}

		$dataFile = $this->getUserDataFile($user->getUID());
		$data = $dataFile->getContent();
		$data .= json_encode(['action' => $action, 'path' => $path]) . "\n";
		$dataFile->putContent($data);
	}

	/**
	 * @param string $userId
	 * @return ISimpleFile
	 * @throws \OCP\Files\NotPermittedException
	 * @throws \RuntimeException
	 */
	protected function getUserDataFile($userId) {
		try {
			$usersFolder = $this->appData->getFolder('users');
		} catch (NotFoundException $e) {
			$usersFolder = $this->appData->newFolder('users');
		}

		try {
			return $usersFolder->getFile($userId . '.json');
		} catch (NotFoundException $e) {
			return $usersFolder->newFile($userId . '.json');
		}
	}
}
