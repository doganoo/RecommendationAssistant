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
use OCA\RecommendationAssistant\ContentReader\ContentReaderFactory;
use OCA\RecommendationAssistant\ContentReader\EmptyReader;
use OCA\RecommendationAssistant\Db\ProcessedFilesManager;
use OCA\RecommendationAssistant\Db\UserProfileManager;
use OCA\RecommendationAssistant\Exception\InvalidRatingException;
use OCA\RecommendationAssistant\Log\ConsoleLogger;
use OCA\RecommendationAssistant\Log\Logger;
use OCA\RecommendationAssistant\Objects\Item;
use OCA\RecommendationAssistant\Objects\ItemList;
use OCA\RecommendationAssistant\Objects\KeywordList;
use OCA\RecommendationAssistant\Objects\Rater;
use OCA\RecommendationAssistant\Recommendation\TextProcessor;
use OCA\RecommendationAssistant\Recommendation\TFIDFComputer;
use OCA\RecommendationAssistant\Util\NodeUtil;
use OCA\RecommendationAssistant\Util\Util;
use OCP\Files\File;
use OCP\Files\Folder;
use OCP\Files\InvalidPathException;
use OCP\Files\IRootFolder;
use OCP\Files\NotFoundException;
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
	 * @var ProcessedFilesManager $processedFilesManager manager class in order to
	 * query the database
	 */
	private $processedFilesManager = null;

	/**
	 * Class constructor gets multiple instances injected
	 *
	 * @param IRootFolder $rootFolder the rootfolder of each user
	 * @param IUserManager $userManager the userManager instance to access
	 * all users
	 * @param UserProfileManager $userProfileManager data storage access instance
	 * in order to get/set the keywords associated to a user profile
	 * @param ProcessedFilesManager $processedFilesManager manager class in order to
	 * query the database
	 * @since 1.0.0
	 */
	public function __construct(
		IRootFolder $rootFolder,
		IUserManager $userManager,
		UserProfileManager $userProfileManager,
		ProcessedFilesManager $processedFilesManager) {
		$this->rootFolder = $rootFolder;
		$this->userManager = $userManager;
		$this->userProfileManager = $userProfileManager;
		$this->processedFilesManager = $processedFilesManager;
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
		ConsoleLogger::debug("UserProfileService start");

		if (Application::DISABLE_CONTENT_BASED_RECOMMENDATION) {
			ConsoleLogger::debug("UserProfileService end");
			return;
		}

		$iniVals = [];
		$iniVals["max_execution_time"] = ini_get("max_execution_time");
		$iniVals["memory_limit"] = ini_get("memory_limit");
		$iniVals["pcre.backtrack_limit"] = ini_get("pcre.backtrack_limit");
		set_time_limit(0);
		ini_set("memory_limit", -1);
		ini_set("pcre.backtrack_limit", -1);
		Util::setErrorHandler();
		$itemList = new ItemList();
		$users = [];
		$this->userManager->callForSeenUsers(function (IUser $user) use (&$itemList, &$users) {
			Filesystem::initMountPoints($user->getUID());
			$folder = $this->rootFolder->getUserFolder($user->getUID());
			$return = $this->handleFolder($folder, $user);
			$itemList->merge($return);
			$users[] = $user;
		});
		/** @var IUser $user */
		foreach ($users as $user) {
			$keywordList = new KeywordList();
			/** @var Item $item */
			foreach ($itemList as $item) {
				/*
				 * The keywords are added to the keyword list only when
				 * the user is part of the raters. A user may be a rater without
				 * rating the item (in this case, his rating equals to 0). If
				 * the user is not a rater, he has no access to the file.
				 */
				if (!$item->raterPresent($user->getUID())) {
					continue;
				}
				$tfidf = new TFIDFComputer($item, $itemList);
				$preparedList = $tfidf->compute();
				$keywordList->merge($preparedList);
				$file = NodeUtil::getFile($item->getId(), $user->getUID());
				$this->processedFilesManager->insertFile($file, "userprofile");
			}
			$filteredNumber = $keywordList->removeStopwords();
			Logger::info("$filteredNumber elements for user {$user->getUID()} are removed from the keyword list. Actual size: " . $keywordList->size());
			$inserted = $this->userProfileManager->insertKeywords($keywordList, $user);
			$message = false === $inserted ? "at least one element is not inserted for {$user->getUID()}" : $keywordList->size() . " elements inserted for " . $user->getUID();
			Logger::info($message);
		}
		set_time_limit($iniVals["max_execution_time"]);
		ini_set("memory_limit", $iniVals["memory_limit"]);
		ini_set("pcre.backtrack_limit", $iniVals["pcre.backtrack_limit"]);
		Util::setErrorHandler(true);
		Logger::debug("UserProfileService end");
		ConsoleLogger::debug("UserProfileService end");
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
	 * @param IUser $user the user
	 * @return ItemList a list of all items that are processed either recursively
	 * or by the handleFile() method
	 * @since 1.0.0
	 */
	private function handleFolder(Folder $folder, IUser $user): ItemList {
		$itemList = new ItemList();
		try {
			foreach ($folder->getDirectoryListing() as $node) {
				$valid = NodeUtil::validNode($node);
				if ($valid) {
					if ($node instanceof Folder) {
						$return = $this->handleFolder($node, $user);
						$itemList->merge($return);
					} else if ($node instanceof File) {
						$return = $this->handleFile($node, $user);
						$itemList->add($return);
					}
				}
			}
		} catch (NotFoundException $exception) {
			Logger::error($exception->getMessage());
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
	 * @param IUser $user the current user
	 * @return null|Item the item that represents the file or null under some
	 * circumstances
	 * @since 1.0.0
	 */
	private function handleFile(File $file, IUser $user): Item {
		$item = new Item();
		$isSharedStorage = false;

		$valid = Util::validMimetype($file->getMimeType());
		if (!$valid) {
			return new Item();
		}

		$fileId = "";
		try {
			$isSharedStorage = $file->getStorage()->instanceOfStorage(Application::SHARED_INSTANCE_STORAGE);
		} catch (NotFoundException $e) {
			Logger::warn($e->getMessage());
		}
		if ($isSharedStorage) {
			return $item;
		}


		try {
			$fileId = $file->getId();
		} catch (InvalidPathException $e) {
			Logger::error($e->getMessage());
			return new  Item();
		} catch (NotFoundException $e) {
			Logger::error($e->getMessage());
			return new  Item();
		}
		$hasChanges = NodeUtil::hasChanges($fileId, $user->getUID());
		$processed = $this->processedFilesManager->isPresentable($file, "userprofile");

		/*
		 * a file has to be:
		 *
		 * <ul>changed</ul>
		 * <ul>not processed in the past</ul>
		 *
		 * in order to get processed
		 */
		if (!($hasChanges || !$processed)) {
			return $item;
		}
		$contentReader = ContentReaderFactory::getContentReader($file->getMimeType());

		if ($contentReader instanceof EmptyReader) {
			return $item;
		}

		$content = $contentReader->read($file);
		$textProcessor = new TextProcessor($content);
		$textProcessor->removeNumeric();
		$textProcessor->removeDate();
		$textProcessor->toLower();
		$keywordList = $textProcessor->getKeywordList();
		$item->setId($fileId);
		$item->setOwner($file->getOwner());
		$item->setName($file->getName());
		$rater = $this->getRater($user, true);
		$item->addRater($rater);
		$item->setKeywordList($keywordList);
		return $item;
	}

	/**
	 * This simply creates a Rater object
	 *
	 * @param IUser $user
	 * @param bool $rating binary rating (1 or 0)
	 * @return Rater object that represents the rater
	 * @since 1.0.0
	 */
	private
	function getRater(IUser $user, bool $rating) {
		$rater = new Rater($user);
		try {
			$rater->setRating($rating ? 1 : 0);
		} catch (InvalidRatingException $exception) {
			Logger::error($exception->getMessage());
		}
		return $rater;
	}

}