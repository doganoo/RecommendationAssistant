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
use OCA\RecommendationAssistant\Db\ProcessedFilesManager;
use OCA\RecommendationAssistant\Db\UserProfileManager;
use OCA\RecommendationAssistant\Objects\Hybrid;
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

/**
 * RecommenderService service class that is called by the RecommenderJob
 * that is a background job within the Nextcloud framework.
 *
 * Shortdescription: this class processes all files of each user, performs a
 * Content Based and Collaborative similarity measurement and ???
 *
 * @package OCA\RecommendationAssistant\Service
 * @since 1.0.0
 */
class RecommenderService {
	/**
	 * @var IRootFolder $rootFolder the rootfolder of each user
	 */
	private $rootFolder = null;

	/**
	 * @var IUserManager $userManager the userManager instance to access
	 * all users
	 */
	private $userManager = null;

	/**
	 * @var ITagManager $tagManager the tag manager instance to access the tags.
	 * In this case: the favorites
	 */
	private $tagManager = null;

	/**
	 * @var UserProfileManager $userProfileManager data storage access instance
	 * in order to get/set the keywords associated to a user profile
	 */
	private $userProfileManager = null;

	/**
	 * @var ProcessedFilesManager $processedFileManager data storage access instance
	 * in order to get/set the processed files
	 */
	private $processedFileManager = null;

	/**
	 * @var Hybrid $hybrid Hybridization instance of the recommendation algorithm
	 */
	private $hybrid = null;

	/**
	 * Class constructor gets multiple instances injected
	 *
	 * @param IRootFolder $rootFolder the rootfolder of each user
	 * @param IUserManager $userManager the userManager instance to access
	 * all users
	 * @param ITagManager $tagManager the tag manager instance to access the tags.
	 * In this case: the favorites
	 * @param UserProfileManager $userProfileManager data storage access instance
	 * in order to get/set the keywords associated to a user profile
	 * @param ProcessedFilesManager $processedFileManager data storage access instance
	 * in order to get/set the processed files
	 * @since 1.0.0
	 */
	public function __construct(
		IRootFolder $rootFolder,
		IUserManager $userManager,
		ITagManager $tagManager,
		UserProfileManager $userProfileManager,
		ProcessedFilesManager $processedFilesManager) {
		$this->rootFolder = $rootFolder;
		$this->userManager = $userManager;
		$this->tagManager = $tagManager;
		$this->userProfileManager = $userProfileManager;
		$this->processedFileManager = $processedFilesManager;
		$this->hybrid = new Hybrid();
	}

	/**
	 * This method is the first method that is called by the RecommenderJob
	 * class. It represents the entry point the RecommenderService class.
	 *
	 * Shortdescription: this class iterates over all seen users, processes all
	 * files of the users and calculates a Content Based and Collaborative
	 * similarity value in order to make recommendations. The results are stored
	 * in ???
	 *
	 * @since 1.0.0
	 */
	public function run() {
		Logger::debug("RecommenderService start");
		$itemList = new ItemList();
		$users = [];
		$this->userManager->callForSeenUsers(function (IUser $user) use (&$itemList, &$users) {
			\OC\Files\Filesystem::initMountPoints($user->getUID());
			$folder = $this->rootFolder->getUserFolder($user->getUID());
			$result = $this->handleFolder($folder, $user);
			$itemList->merge($result);
			$users[] = $user;
		});

		foreach ($itemList as $item) {
			foreach ($users as $user) {
				$keywordList = $this->userProfileManager->getKeywordListByUser($user);
				if ($item->getOwner()->getUID() === $user->getUID()) {
					continue;
				}
				$sport1 = new Sport1Computer($item, $keywordList, $itemList);
				$sim = $sport1->compute();
				if ($sim == 0) {
					continue;
				}
				$this->hybrid->addValue($sim, Hybrid::TYPE_CONTENT_BASED, $item);
			}
		}

		foreach ($itemList as $item) {
			foreach ($itemList as $item1) {
				if ($item->equals($item1)) {
					continue;
				}
				$pearson = new PearsonComputer($item, $item1);
				$sim = $pearson->compute();
				if ($sim == 0) {
					continue;
				}
				$this->hybrid->addValue($sim, Hybrid::TYPE_COLLABORATIVE, $item);
			}
		}

		//TODO: what to do with the results?
		Logger::debug("RecommenderService end");
	}

	/**
	 * This method iterates over Folder instances recursively when the given
	 * node is a folder. The node is passed to the handleFile() method when the
	 * node is a file.
	 *
	 * The results are added/merged to a ItemList that is returned to the run()
	 * method and merged again into a final ItemList.
	 *
	 * @param Folder $folder the actual folder
	 * @param IUser $currentUser the actual user
	 * @return ItemList a list of all items that are processed either recursively
	 * or by the handleFile() method
	 * @since 1.0.0
	 */
	private function handleFolder(Folder $folder, IUser $currentUser): ItemList {
		$itemList = new ItemList();
		foreach ($folder->getDirectoryListing() as $node) {
			if ($node instanceof Folder) {
				$return = $this->handleFolder($node, $currentUser);
				$itemList->merge($return);
			} else if ($node instanceof File) {
				$return = $this->handleFile($node, $currentUser);
				//TODO check return whether null or not
				$itemList->add($return);
			}
		}
		return $itemList;
	}

	/**
	 * This method is responsible for a File instance and processes it in order
	 * to pass it to the Content Based and Collaborative recommendation process.
	 *
	 * First, the method checks some pre conditions: is the file already processed,
	 * encrpyted or readable. The file is checked whether it is a shared one or
	 * not if it passes the above checks. If this is the case, it is only determined
	 * whether the user that got the file shared has liked the file or not.
	 *
	 * If the file is not shared, meaning that the actual user owns the file, then
	 * it is further processed by reading out its content and determining whether
	 * it is liked or not. Finally, the file is added to the "processed files"
	 * database.
	 *
	 * The result of this method is a Item instance that actually represents a file.
	 * The method will return null if one of the above described conditions are true.
	 *
	 * @param File $file the actual file
	 * @param IUser $currentUser the actual user
	 * @return null|Item the item that represents the file or null under some
	 * circumstances
	 * @since 1.0.0
	 */
	private function handleFile(File $file, IUser $currentUser): ?Item {
		if ($this->isIndexed($file)) {
			return null;
		}
		if ($file->isEncrypted()) {
			return null;
		}
		if (!$file->isReadable()) {
			return null;
		}
		$item = new Item();
		$item->setId($file->getId());
		$item->setName($file->getName());
		$item->setOwner($file->getOwner());
		$isSharedStorage = $file->getStorage()->instanceOfStorage(Application::SHARED_INSTANCE_STORAGE);

		//if the file is a shared one, then we do not need to process the content
		//because it is already processed. We just need the rating of the user
		//that has access to the file
		if ($isSharedStorage) {
			$favorite = $this->checkForFavorite($currentUser, $file->getId());
			$rater = $this->getRater($currentUser, $favorite == 1);
			$item->addRater($rater);
			return $item;
		}

		$contentReader = ObjectFactory::getContentReader($file->getMimeType());
		$content = $contentReader->read($file);
		$textProcessor = new TextProcessor($content);
		$array = $textProcessor->getTextAsArray();
		$item->setKeywords($array);

		$isFavorite = $this->checkForFavorite($file->getOwner(), $file->getId());
		$rater = $this->getRater($file->getOwner(), $isFavorite);
		$item->addRater($rater);
		$this->processedFileManager->insertFile($file);
		return $item;
	}

	/**
	 * This method checks whether the file is marked as favorite (is liked) by
	 * a given user.
	 *
	 * @param IUser $currentUser the actual user
	 * @param int $fileId the actual file id
	 * @return bool whether the file is liked or not
	 * @since 1.0.0
	 */
	private function checkForFavorite(IUser $user, $fileId) {
		$tags = $this->tagManager->load("files", [], false, $user->getUID());
		$favorites = $tags->getFavorites();
		$isFavorite = in_array($fileId, $favorites);
		return $isFavorite;
	}

	/**
	 * This simply creates a Rater object
	 *
	 * @param IUser $currentUser the actual user
	 * @param bool $rating binary rating (1 or 0)
	 * @return Rater object that represents the rater
	 * @since 1.0.0
	 */
	private function getRater(IUser $user, bool $rating) {
		$rater = new Rater($user);
		$rater->setRating($rating ? 1 : 0);
		return $rater;
	}

	/**
	 * This method checks whether the file is already processed by
	 * RecommenderService.
	 *
	 * @param File $file actual file
	 * @return bool whether the file is already processed or not
	 * @since 1.0.0
	 */
	private function isIndexed(File $file): bool {
		$presentable = $this->processedFileManager->isPresentable($file);
		return $presentable;
	}
}