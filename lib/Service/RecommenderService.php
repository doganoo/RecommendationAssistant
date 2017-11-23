<?php
/**
 * Created by PhpStorm.
 * User: dogano
 * Date: 08.11.17
 * Time: 15:52
 */

namespace OCA\DoganMachineLearning\Service;


use OCA\DoganMachineLearning\AppInfo\Application;
use OCA\DoganMachineLearning\ContentReader\DocReader;
use OCA\DoganMachineLearning\ContentReader\ObjectFactory;
use OCA\DoganMachineLearning\Objects\Item;
use OCA\DoganMachineLearning\Objects\ItemList;
use OCA\DoganMachineLearning\Objects\Rater;
use OCA\DoganMachineLearning\Recommendation\PearsonComputer;
use OCA\DoganMachineLearning\Recommendation\Sport1Computer;
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
		if ($this->isIndexed($file)) {
			return false;
		}
		if ($file->isEncrypted()) {
			return false;
		}
		if (!$file->isReadable()) {
			return false;
		}
		$item = new Item();
		$item->setId($file->getId());
		$item->setName($file->getName());
		$isSharedStorage = $file->getStorage()->instanceOfStorage('\OCA\Files_Sharing\SharedStorage'); //TODO extract to constant!

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

		if ($contentReader instanceof DocReader) {
			$this->debug("Content: " . $content);
		}

		$textProcessor = new TextProcessor($content);
		$array = $textProcessor->getTextAsArray();
		$item->setKeywords($array);

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