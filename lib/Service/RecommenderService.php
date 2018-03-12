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
use OCA\RecommendationAssistant\Db\RecommendationManager;
use OCA\RecommendationAssistant\Db\UserProfileManager;
use OCA\RecommendationAssistant\Exception\InvalidRatingException;
use OCA\RecommendationAssistant\Log\ConsoleLogger;
use OCA\RecommendationAssistant\Log\Logger;
use OCA\RecommendationAssistant\Objects\HybridItem;
use OCA\RecommendationAssistant\Objects\HybridList;
use OCA\RecommendationAssistant\Objects\Item;
use OCA\RecommendationAssistant\Objects\ItemList;
use OCA\RecommendationAssistant\Objects\ItemToItemMatrix;
use OCA\RecommendationAssistant\Objects\Rater;
use OCA\RecommendationAssistant\Recommendation\CosineComputer;
use OCA\RecommendationAssistant\Recommendation\GroupWeightComputer;
use OCA\RecommendationAssistant\Recommendation\OverlapCoefficientComputer;
use OCA\RecommendationAssistant\Recommendation\RatingPredictor;
use OCA\RecommendationAssistant\Recommendation\TextProcessor;
use OCA\RecommendationAssistant\Util\NodeUtil;
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
	 * @param GroupWeightsManager $groupWeightsManager the manager for querying
	 * group weights
	 * @param RecommendationManager $recommendationManager database access to
	 * recommendations
	 * @param ChangedFilesManager $changedFilesManager database access to changed files
	 * @since 1.0.0
	 */
	public function __construct(
		IRootFolder $rootFolder,
		IUserManager $userManager,
		ITagManager $tagManager,
		IGroupManager $groupManager,
		UserProfileManager $userProfileManager,
		GroupWeightsManager $groupWeightsManager,
		RecommendationManager $recommendationManager,
		ChangedFilesManager $changedFilesManager
	) {
		$this->rootFolder = $rootFolder;
		$this->userManager = $userManager;
		$this->tagManager = $tagManager;
		$this->groupManager = $groupManager;
		$this->userProfileManager = $userProfileManager;
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
	 * in a database.
	 *
	 * @since 1.0.0
	 */
	public function run() {
		Logger::debug("RecommenderService start");
		ConsoleLogger::debug("RecommenderService start");

		$iniVals = [];
		$iniVals["max_execution_time"] = ini_get("max_execution_time");
		$iniVals["memory_limit"] = ini_get("memory_limit");
		$iniVals["pcre.backtrack_limit"] = ini_get("pcre.backtrack_limit");
		set_time_limit(0);
		ini_set("memory_limit", -1);
		ini_set("pcre.backtrack_limit", -1);
		Util::setErrorHandler(false);

		$users = [];
		$itemList = new ItemList();
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
				//TODO find a way to execute CosineComputer after the continue statements of the loop below
				$cosineComputer = new CosineComputer($item, $item1);
				$cosineSimilarity = $cosineComputer->compute();
				$itemItemMatrix->add($item, $item1, $cosineSimilarity);
			}

			/** @var IUser $user */
			foreach ($users as $user) {
				//do not recommend a file to its owner
				if (Util::sameUser($item->getOwner(), $user)) {
					continue;
				}
				//do not recommend a file to a user that is not a valid rater
				//notice that a 0-value is also a valid rating!
				//invalid rater = user has not rated the file = has no access
				$item1Rater = $item->getRater($user->getUID());
				if (!$item1Rater->isValid()) {
					continue;
				}
				//do not recommend a file to a user that has been recommended to him
				//in the past
				$isRecommended = $this->recommendationManager->isRecommendedToUser($item, $user);
				if ($isRecommended && !Application::DEBUG) {
					continue;
				}
				//this should not be necessary since there is already a "isValid()" check.
				//However, because it is critical not to recommend files to a user who
				//has no access to it, this check will remain for the moment
				//TODO check if this is covered by isValid()
				if (!$item->raterPresent($user->getUID())) {
					continue;
				}

				/** @var HybridItem $hybrid */
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
				$hybridList->add($hybrid, $user, $item);
			}
		}

		if (!Application::DEBUG) {
			$hybridList->removeNonRecommendable();
		}
		$this->recommendationManager->insertHybridList($hybridList);
		set_time_limit($iniVals["max_execution_time"]);
		ini_set("memory_limit", $iniVals["memory_limit"]);
		ini_set("pcre.backtrack_limit", $iniVals["pcre.backtrack_limit"]);
		Util::setErrorHandler(true);
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
	private function handleFolder(Folder $folder, IUser $currentUser): ItemList {
		//TODO do not process files twice
		$itemList = new ItemList();
		try {
			foreach ($folder->getDirectoryListing() as $node) {
				$valid = NodeUtil::validNode($node);
				if ($valid) {
					if ($node instanceof Folder) {
						$return = $this->handleFolder($node, $currentUser);
						$itemList->merge($return);
					} else if ($node instanceof File) {
						$return = $this->handleFile($node, $currentUser);
						$itemList->add($return);
					}
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
	 * <ul>file has not a supported mimetype</ul>
	 *
	 * @param File $file the actual file
	 * @param IUser $currentUser the actual user
	 * @return Item the item that represents the file or null under some
	 * circumstances
	 * @since 1.0.0
	 */
	private function handleFile(File $file, IUser $currentUser): Item {
		$valid = Util::validMimetype($file->getMimeType());
		if (!$valid) {
			return new Item();
		}

		$item = $this->createItem($file);
		$item = $this->addRater($item, $file, $currentUser);
		$item = $this->addKeywords($item, $file);
		return $item;
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
		$timeRating = $this->getChangeTimestamp($file, $currentUser, "edit");
		$favoriteRating = $this->getChangeTimestamp($file, $currentUser, "favorite");
		$rating = ((Application::RATING_WEIGHT_LAST_CHANGE * $timeRating) + (Application::RATING_WEIGHT_LAST_FAVORITE * $favoriteRating));
		$rater = $this->getRater($currentUser, $rating);
		$item->addRater($rater);
		return $item;
	}

	/**
	 * queries the database for the last change timestamp for an rating.
	 * If there is no timestamp available, 01.01.1970 will be returned which
	 * means that the file is not changed since then.
	 *
	 * @param File $file
	 * @param IUser $user
	 * @param string $type
	 * @return int
	 * @since 1.0.0
	 */
	private function getChangeTimestamp(File $file, IUser $user, string $type): int {
		$presentable = $this->changedFilesManager->isPresentable($file, $user->getUID(), $type);

		$changeTs = 0;
		if ($presentable) {
			$changeTs = $this->changedFilesManager->queryChangeTs($file, $user->getUID(), $type);
		}

		$then = new \DateTime();
		$then->setTimestamp($changeTs);

		$now = new \DateTime();

		$difference = $now->diff($then);
		$numberOfDays = $difference->format("%a");

		return $this->calculateRating($numberOfDays);

	}

	/**
	 * returns the rating based on date difference in days.
	 *
	 * @param $numberOfDays
	 * @return int
	 * @since 1.0.0
	 */
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
		if (Application::DISABLE_CONTENT_BASED_RECOMMENDATION) {
			Logger::info("content based recommendation is disabled");
			return $item;
		}
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
		$textProcessor->removeNumeric();
		$textProcessor->removeDate();
		$textProcessor->toLower();
		$keywordList = $textProcessor->getKeywordList();
		$item->setKeywordList($keywordList);
		return $item;
	}
}