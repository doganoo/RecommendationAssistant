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
use OCA\RecommendationAssistant\Db\ChangedFilesManager;
use OCA\RecommendationAssistant\Db\GroupWeightsManager;
use OCA\RecommendationAssistant\Db\ProcessedFilesManager;
use OCA\RecommendationAssistant\Db\RecommendationManager;
use OCA\RecommendationAssistant\Db\UserProfileManager;
use OCA\RecommendationAssistant\Exception\InvalidRatingException;
use OCA\RecommendationAssistant\Objects\ConsoleLogger;
use OCA\RecommendationAssistant\Objects\HybridItem;
use OCA\RecommendationAssistant\Objects\HybridList;
use OCA\RecommendationAssistant\Objects\Item;
use OCA\RecommendationAssistant\Objects\ItemList;
use OCA\RecommendationAssistant\Objects\ItemToItemMatrix;
use OCA\RecommendationAssistant\Objects\Logger;
use OCA\RecommendationAssistant\Objects\Rater;
use OCA\RecommendationAssistant\Recommendation\CosineComputer;
use OCA\RecommendationAssistant\Recommendation\GroupWeightComputer;
use OCA\RecommendationAssistant\Recommendation\OverlapCoefficientComputer;
use OCA\RecommendationAssistant\Recommendation\RatingPredictor;
use OCA\RecommendationAssistant\Recommendation\TextProcessor;
use OCA\RecommendationAssistant\Util\Util;
use OCP\Files\File;
use OCP\Files\Folder;
use OCP\Files\InvalidPathException;
use OCP\Files\IRootFolder;
use OCP\Files\NotFoundException;
use OCP\IGroupManager;
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
	 * @var IGroupManager $groupManager the GroupManager to determine the users groups
	 */
	private $groupManager = null;

	/**
	 * @var GroupWeightsManager $groupWeightsManager the manager for querying group weights
	 */
	private $groupWeightsManager = null;

	/**
	 * @var RecommendationManager $recommendationManager the manager for querying
	 * recommendations
	 */
	private $recommendationManager = null;

	/**
	 * @var ChangedFilesManager $changedFilesManager
	 */
	private $changedFilesManager = null;

	/**
	 * Class constructor gets multiple instances injected
	 *
	 * @param IRootFolder $rootFolder the rootfolder of each user
	 * @param IUserManager $userManager the userManager instance to access
	 * all users
	 * @param ITagManager $tagManager the tag manager instance to access the tags.
	 * In this case: the favorites
	 * @param IGroupManager $groupManager the manager for requesting user groups
	 * @param UserProfileManager $userProfileManager data storage access instance
	 * in order to get/set the keywords associated to a user profile
	 * @param ProcessedFilesManager $processedFilesManager
	 * @param GroupWeightsManager $groupWeightsManager the manager for querying
	 * group weights
	 * @param RecommendationManager $recommendationManager database access to
	 * recommendations
	 * @since 1.0.0
	 */
	public function __construct(
		IRootFolder $rootFolder,
		IUserManager $userManager,
		ITagManager $tagManager,
		IGroupManager $groupManager,
		UserProfileManager $userProfileManager,
		ProcessedFilesManager $processedFilesManager,
		GroupWeightsManager $groupWeightsManager,
		RecommendationManager $recommendationManager,
		ChangedFilesManager $changedFilesManager
	) {
		$this->rootFolder = $rootFolder;
		$this->userManager = $userManager;
		$this->tagManager = $tagManager;
		$this->groupManager = $groupManager;
		$this->userProfileManager = $userProfileManager;
		$this->processedFileManager = $processedFilesManager;
		$this->groupWeightsManager = $groupWeightsManager;
		$this->recommendationManager = $recommendationManager;
		$this->changedFilesManager = $changedFilesManager;
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
		ConsoleLogger::debug("RecommenderService start");
		$itemList = new ItemList();
		$users = [];
		$hybridList = new HybridList();
		$this->userManager->callForSeenUsers(function (IUser $user) use (&$itemList, &$users) {
			Filesystem::initMountPoints($user->getUID());
			$folder = $this->rootFolder->getUserFolder($user->getUID());
			$result = $this->handleFolder($folder, $user);
			$itemList->merge($result);
			$users[] = $user;
		});
		$itemItemMatrix = new ItemToItemMatrix();

		/** @var Item $item */
		foreach ($itemList as $item) {
			/** @var Item $item1 */
			foreach ($itemList as $item1) {
				//not need to check if both items the same because
				//an entire similarity matrix is needed
				$cosineComputer = new CosineComputer($item, $item1);
				$pearsonSimilarity = $cosineComputer->compute();
				$itemItemMatrix->add($item, $item1, $pearsonSimilarity);
			}

			/** @var IUser $user */
			foreach ($users as $user) {
				if (Util::sameUser($item->getOwner(), $user)) {
					continue;
				}
				$hybrid = $hybridList->getHybridByUser($item, $user);
				$hybrid->setUser($user);
				$hybrid->setItem($item);
				$keywordList = $this->userProfileManager->getKeywordListByUser($user);

				$overlap = new OverlapCoefficientComputer($item, $itemList, $keywordList);
				$contentBasedSimilarity = $overlap->compute();
				$groupWeightComputer = new GroupWeightComputer(
					$this->groupManager->getUserGroups($item->getOwner()),
					$this->groupManager->getUserGroups($item1->getOwner()),
					$this->groupWeightsManager);
				$userGroupWeights = $groupWeightComputer->calculateWeight();
				$hybrid->setGroupWeight($userGroupWeights);

				$itemComputer = new RatingPredictor($item, $user, $itemList, $itemItemMatrix);
				$collaborativeSimilarity = $itemComputer->predict();
				$hybrid->setContentBased($contentBasedSimilarity);
				$hybrid->setCollaborative($collaborativeSimilarity);
				if (!$item->raterPresent($user->getUID())) {
					$hybridList->add($hybrid, $user, $item);
				}

			}
		}

//		foreach ($hybridList as $key => $array) {
//			/**
//			 * @var  $key
//			 * @var HybridItem $hybrid
//			 */
//			foreach ($array as $key => $hybrid) {
//				$itemId = $hybrid->getItem()->getId();
//				if (in_array($itemId, [245, 10, 8]))
//					ConsoleLogger::debug($hybrid);
//			}
//		}
//		$hybridList->removeNonRecommendable();
		$this->recommendationManager->deleteAll();
		$this->recommendationManager->insertHybridList($hybridList);

		Logger::debug("RecommenderService end");
		ConsoleLogger::debug("RecommenderService end");
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
	private
	function handleFolder(Folder $folder, IUser $currentUser): ItemList {
		$itemList = new ItemList();
		try {
			foreach ($folder->getDirectoryListing() as $node) {
				if ($node instanceof Folder) {
					$return = $this->handleFolder($node, $currentUser);
					$itemList->merge($return);
				} else if ($node instanceof File) {
					$return = $this->handleFile($node, $currentUser);
					$itemList->add($return);
				}
			}
		} catch (NotFoundException $exception) {
			Logger::warn($exception->getMessage());
		}
		return $itemList;
	}

	/**
	 * This method is responsible for a File instance and processes it in order
	 * to pass it to the Content Based and Collaborative recommendation process.
	 *
	 * The method calls other private methods of the class in order to create
	 * an item, add raters and assign keywords to the item.
	 *
	 * The result of this method is a Item instance that actually represents a file.
	 * The method will return an invalid instance of Item if the following conditions
	 * are true:
	 *
	 * <ul>file is already processed</ul>
	 * <ul>file is encrypted</ul>
	 * <ul>file is not readable</ul>
	 *
	 * @param File $file the actual file
	 * @param IUser $currentUser the actual user
	 * @return Item the item that represents the file or null under some
	 * circumstances
	 * @since 1.0.0
	 */
	private
	function handleFile(File $file, IUser $currentUser): Item {
		if ($this->isProcessed($file)) {
			return new Item();
		}
		if ($file->isEncrypted()) {
			return new Item();
		}
		try {
			if (!$file->isReadable()) {
				return new Item();
			}
		} catch (InvalidPathException $exception) {
			Logger::warn($exception->getMessage());
			return new Item();
		} catch (NotFoundException $exception) {
			Logger::warn($exception->getMessage());
			return new Item();
		}

//		if (Application::DEBUG) {
//			if (!in_array($file->getName(), ["d1.txt", "d2.txt", "d3.txt", "d4.txt", "d5.txt"])) {
//				return new Item();
//			}
//		}

		$item = $this->createItem($file);
		$item = $this->addRater($item, $file, $currentUser);
		$item = $this->addKeywords($item, $file);
		$this->processedFileManager->insertFile($file);
		return $item;
	}

	/**
	 * this method adds the $currentUsers rating for $file and assigns the
	 * information to the item. The method returns the item without assigning
	 * a rating if the file is invalid or can not be found.
	 *
	 * @param Item $item
	 * @param File $file
	 * @param IUser $currentUser
	 * @return Item
	 * @since 1.0.0
	 */
	private function addRater(Item $item, File $file, IUser $currentUser) {
		$timeRating = $this->getChangeTimeRating($file);
		$favoriteRating = $this->getFavoriteRating($file);
		$rating = ((1 * $timeRating) + (0.0 * $favoriteRating));
		$rater = $this->getRater($currentUser, $rating);
		$item->addRater($rater);
		return $item;
	}

	private function getChangeTimeRating(File $file): int {
		$presentable = $this->changedFilesManager->isPresentable($file, "edit");
		$changeTs = 0;
		if ($presentable) {
			$changeTs = $this->changedFilesManager->queryChangeTs($file, "edit");
		}

		$then = new \DateTime();
		$then->setTimestamp($changeTs);

		$now = new \DateTime();
		$now->getTimestamp();

		$difference = $now->diff($then);
		$numberOfDays = $difference->format("%a");

		return $this->calculateRating($numberOfDays);

	}

	private function getFavoriteRating(File $file) {
		$presentable = $this->changedFilesManager->isPresentable($file, "favorite");
		$changeTs = 0;
		if ($presentable) {
			$changeTs = $this->changedFilesManager->queryChangeTs($file, "favorite");
		}

		$then = new \DateTime();
		$then->setTimestamp($changeTs);

		$now = new \DateTime();
		$now->getTimestamp();

		$difference = $now->diff($then);
		$numberOfDays = $difference->format("%a");
		$rating = $this->calculateRating($numberOfDays);
		return $rating;

	}

	private function calculateRating($numberOfDays): int {

		if ($numberOfDays <= 3) {
			return 5;
		} else if ($numberOfDays > 3 && $numberOfDays <= 5) {
			return 4;
		} else if ($numberOfDays > 5 && $numberOfDays <= 10) {
			return 3;
		} else if ($numberOfDays > 10 && $numberOfDays <= 15) {
			return 2;
		} else if ($numberOfDays > 15 && $numberOfDays <= 20) {
			return 1;
		} else {
			return 0;
		}
	}

	/**
	 * this method reads the file content and assigns them as an array to the
	 * passed item. If the file is an shared one, the keywords are not read
	 * because they will be processed for the owner.
	 *
	 * @param Item $item
	 * @param File $file
	 * @return Item
	 * @since 1.0.0
	 */
	public function addKeywords(Item $item, File $file) {
		$isSharedStorage = false;
		try {
			$isSharedStorage = $file->getStorage()->instanceOfStorage(Application::SHARED_INSTANCE_STORAGE);
		} catch (NotFoundException $exception) {
			Logger::warn($exception->getMessage());
		}
		/* sharedStorage means that the file is shared to the user.
		 * if this is the case we do not need to process the file twice.
		 */
		if ($isSharedStorage) {
			return $item;
		}
		$contentReader = ContentReaderFactory::getContentReader($file->getMimeType());
		$content = $contentReader->read($file);
		$textProcessor = new TextProcessor($content);
		$array = $textProcessor->getTextAsArray();
		$item->setKeywords($array);
		return $item;
	}

	/**
	 * This simply creates a Rater object. $rating has to be a valid rating:
	 * <ul>-1 for no rating</1>
	 * <ul>0 for dislike</1>
	 * <ul>1 for like</1>
	 *
	 * see class OCA\RecommendationAssistant\Objects\Rater for all types of
	 * ratings.
	 *
	 * @param IUser $user
	 * @param int $rating binary rating (1 or 0)
	 * @return Rater object that represents the rater
	 * @since 1.0.0
	 */
	private function getRater(IUser $user, int $rating) {
		$rater = new Rater($user);
		try {
			$rater->setRating($rating);
		} catch (InvalidRatingException $exception) {
			Logger::error($exception->getMessage());
		}
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
	private
	function isProcessed(File $file): bool {
		if (Application::DEBUG) {
			return false;
		}
		//TODO check the checksum of the file for changes?
		$presentable = $this->processedFileManager->isPresentable($file);
		return $presentable;
	}

	/**
	 * creates an instance of Item from a file. If the id is not readable
	 * the method returns and empty Item which is not valid.
	 *
	 * @param File $file
	 * @return Item
	 * @since 1.0.0
	 */
	private function createItem(File $file) {
		$item = new Item();
		try {
			$item->setId($file->getId());
		} catch (InvalidPathException $exception) {
			Logger::warn($exception->getMessage());
			return $item;
		} catch (NotFoundException $exception) {
			Logger::warn($exception->getMessage());
			return $item;
		}
		$item->setName($file->getName());
		$item->setOwner($file->getOwner());
		return $item;
	}
}