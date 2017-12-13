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

use OC\Files\Filesystem;
use OCA\RecommendationAssistant\AppInfo\Application;
use OCA\RecommendationAssistant\ContentReader\EmptyReader;
use OCA\RecommendationAssistant\Db\UserProfileManager;
use OCA\RecommendationAssistant\Objects\Item;
use OCA\RecommendationAssistant\Objects\ItemList;
use OCA\RecommendationAssistant\Objects\KeywordList;
use OCA\RecommendationAssistant\Objects\Logger;
use OCA\RecommendationAssistant\Objects\ObjectFactory;
use OCA\RecommendationAssistant\Recommendation\TFIDFComputer;
use OCP\Files\File;
use OCP\Files\Folder;
use OCP\Files\IRootFolder;
use OCP\IUser;
use OCP\IUserManager;

/**
 * UserProfileService service class that is called by the UserProfileJob
 * that is a background job within the Nextcloud framework.
 *
 * Shortdescription: this class processes all files of each user, determines
 * all keywords of them and stores them into a database in order to build a
 * user profile
 *
 * @package OCA\RecommendationAssistant\Service
 * @since 1.0.0
 */
class UserProfileService {
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
	 * @var ItemList the list that contains all items (files)
	 */
	private $itemList = null;

	/**
	 * @var UserProfileManager $userProfileManager data storage access instance
	 * in order to get/set the keywords associated to a user profile
	 */
	private $userProfileManager = null;

	/**
	 * Class constructor gets multiple instances injected
	 *
	 * @param IRootFolder $rootFolder the rootfolder of each user
	 * @param IUserManager $userManager the userManager instance to access
	 * all users
	 * @param UserProfileManager $userProfileManager data storage access instance
	 * in order to get/set the keywords associated to a user profile
	 * @since 1.0.0
	 */
	public function __construct(
		IRootFolder $rootFolder,
		IUserManager $userManager,
		UserProfileManager $userProfileManager) {
		$this->rootFolder = $rootFolder;
		$this->userManager = $userManager;
		$this->userProfileManager = $userProfileManager;
		$this->itemList = new ItemList();
	}

	/**
	 * This method is the first method that is called by the UserProfileJob
	 * class. It represents the entry point the UserProfileService class.
	 *
	 * Shortdescription: this class iterates over all seen users, processes all
	 * files of the users in order to get the keywords out of them and stores
	 * them finally into the database to create a user profile that describes
	 * the users preferences.
	 *
	 * @since 1.0.0
	 */
	public function run() {
		Logger::debug("UserProfileService start");
		$itemList = new ItemList();
		$users = [];
		$this->userManager->callForSeenUsers(function (IUser $user) use (&$itemList, &$users) {

			Filesystem::initMountPoints($user->getUID());
			$folder = $this->rootFolder->getUserFolder($user->getUID());
			$return = $this->handleFolder($folder);
			$itemList->merge($return);
			$users[] = $user;
		});

		foreach ($itemList as $item) {
			foreach ($users as $user) {
				$keywordList = new KeywordList();
				foreach ($itemList as $item) {
					$tfidf = new TFIDFComputer($item, $itemList);
					$keywordList = $tfidf->compute();
					$keywordList->sort();
				}
				$this->userProfileManager->insertKeywords($keywordList, $user);
//				$itemList->deleteList();
			}
		}
		Logger::debug("UserProfileService end");
	}

	/**
	 * This method iterates over Folder instances recursively when the given
	 * node is a folder. The node is passed to the handleFile() method when the
	 * node is a file.
	 *
	 * The results are added/merged to a ItemList that is returned to the run()
	 * method.
	 *
	 * @param Folder $folder the actual folder
	 * @return ItemList a list of all items that are processed either recursively
	 * or by the handleFile() method
	 * @since 1.0.0
	 */
	private function handleFolder(Folder $folder): ItemList {
		$itemList = new ItemList();
		foreach ($folder->getDirectoryListing() as $node) {
			if ($node instanceof Folder) {
				$return = $this->handleFolder($node);
				$itemList->merge($return);
			} else if ($node instanceof File) {
				$return = $this->handleFile($node);
				$itemList->add($return);
			}
		}
		return $itemList;
	}

	/**
	 * This method is responsible for a File instance and processes it in order
	 * to read all keywords out of a single file.
	 *
	 * First, the method checks some pre conditions: is the file encrpyted, readable,
	 * a shared one or has no content. The file is not processed if one of this
	 * conditions are true.
	 *
	 * If one of the above described conditions are not true the file will be
	 * further processed by reading out its content. The most important keywords
	 * of the file are determined and returned.
	 *
	 * The result of this method is a Item instance that actually represents a file.
	 * The method will return null if one of the above described conditions are true.
	 *
	 * @param File $file the actual file
	 * @return null|Item the item that represents the file or null under some
	 * circumstances
	 * @since 1.0.0
	 */
	private function handleFile(File $file): Item {
		$item = new Item();
		$isSharedStorage = $file->getStorage()->instanceOfStorage(Application::SHARED_INSTANCE_STORAGE);
		if ($file->isEncrypted()) {
			return $item;
		}
		if (!$file->isReadable()) {
			return $item;
		}
		if ($isSharedStorage) {
			return $item;
		}
		$contentReader = ObjectFactory::getContentReader($file->getMimeType());

		if ($contentReader instanceof EmptyReader) {
			return $item;
		}

		$content = $contentReader->read($file);
		$textProcessor = new TextProcessor($content);
		$textProcessor->removeNumeric();
		$textProcessor->removeDate();
		$textProcessor->toLower();
		$array = $textProcessor->getTextAsArray();

		$item->setId($file->getId());
		$item->setKeywords($array);
		return $item;
	}

}