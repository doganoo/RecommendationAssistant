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

namespace OCA\RecommendationAssistant\Service;


use OCA\RecommendationAssistant\AppInfo\Application;
use OCA\RecommendationAssistant\ContentReader\DocxReader;
use OCA\RecommendationAssistant\Objects\Item;
use OCA\RecommendationAssistant\Objects\ItemList;
use OCA\RecommendationAssistant\Objects\Logger;
use OCA\RecommendationAssistant\Objects\ObjectFactory;
use OCA\RecommendationAssistant\Objects\Rater;
use OCA\RecommendationAssistant\Recommendation\PearsonComputer;
use OCA\RecommendationAssistant\Recommendation\Sport1Computer;
use OCP\Files\File;
use OCP\Files\Folder;
use OCP\Files\IRootFolder;
use OCP\ITagManager;
use OCP\IUser;
use OCP\IUserManager;

class RecommenderService {
	private $rootFolder = null;
	private $userManager = null;
	private $itemList = null;
	private $tagManager = null;

	public function __construct(IRootFolder $rootFolder, IUserManager $userManager, ITagManager $tagManager) {
		$this->rootFolder = $rootFolder;
		$this->userManager = $userManager;
		$this->tagManager = $tagManager;
		$this->itemList = new ItemList();
	}


	public function run() {
		$this->log("started app");
		$this->userManager->callForSeenUsers(function (IUser $user) {
			\OC\Files\Filesystem::initMountPoints($user->getUID());
			$folder = $this->rootFolder->getUserFolder($user->getUID());
			$this->handleFolder($folder, $user);
		});

		foreach ($this->itemList as $item) {
			foreach ($this->itemList as $item1) {
				if ($item->equals($item1)) {
					continue;
				}
				$sport1 = new Sport1Computer($item, $item1, $this->itemList);
				$sport1Result = $sport1->compute();

				$pearson = new PearsonComputer($item, $item1);
				$pearsonResult = $pearson->compute();
			}
		}

		$this->log("finished app");
	}


	private function handleFolder(Folder $folder, IUser $currentUser) {
		foreach ($folder->getDirectoryListing() as $node) {
			if ($node instanceof Folder) {
				$this->handleFolder($node, $currentUser);
			} else if ($node instanceof File) {
				$this->handleFile($node, $currentUser);
			} else {
				$this->debug("node is whether folder nor file");
			}
		}
	}

	private function handleFile(File $file, IUser $currentUser) {
		if (strpos($file->getName(), "demo") !== false) {
			$this->debug("file: " . $file->getName());
		}
		if ($this->isIndexed($file)) {
			$this->debug("is already indexed");
			return false;
		}
		if ($file->isEncrypted()) {
			$this->debug("is encrpyted");
			return false;
		}
		if (!$file->isReadable()) {
			$this->debug("is not readable");
			return false;
		}
		$item = new Item();
		$item->setId($file->getId());
		$item->setName($file->getName());
		$isSharedStorage = $file->getStorage()->instanceOfStorage(Application::SHARED_INSTANCE_STORAGE);

		//if the file is a shared one, then we do not need to process the content
		//because it is already processed. We just need the rating of the user
		//that has access to the file
		if ($isSharedStorage) {
			$favorite = $this->checkForFavorite($currentUser, $file->getId());
			$rater = $this->getRater($currentUser, $favorite == 1);
			$item->addRater($rater);
			$this->itemList->add($item);
			return false;
		}

		$contentReader = ObjectFactory::getContentReader($file->getMimeType());
		$content = $contentReader->read($file);
		$textProcessor = new TextProcessor($content);
		$array = $textProcessor->getTextAsArray();
		$item->setKeywords($array);

		if ($contentReader instanceof DocxReader) {
			Logger::debug($content);
		}

		$isFavorite = $this->checkForFavorite($file->getOwner(), $file->getId());
		$rater = $this->getRater($file->getOwner(), $isFavorite);
		$item->addRater($rater);
		$this->itemList->add($item);
	}

	private function checkForFavorite(IUser $user, $fileId) {
		$tags = $this->tagManager->load("files", [], false, $user->getUID());
		$favorites = $tags->getFavorites();
		$isFavorite = in_array($fileId, $favorites);
		return $isFavorite;
	}

	private function getRater(IUser $user, bool $rating) {
		$rater = new Rater($user);
		$rater->setRating($rating ? 1 : 0);
		return $rater;
	}

	private function log($message) {
		$logger = \OC::$server->getLogger();
		$logger->info($message, ["app" => Application::APPNAME]);
	}

	private function debug($message) {
		$logger = \OC::$server->getLogger();
		$logger->debug($message, ["app" => Application::APPNAME]);
	}

	private function isIndexed(File $file): bool {
		//TODO implement!!
		return false;
	}
}