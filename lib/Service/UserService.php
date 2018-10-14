<?php


namespace OCA\RecommendationAssistant\Service;


use OCP\Files\IRootFolder;
use OCP\IUserManager;

class UserService {
	private $rootFolder = null;

	public function __construct(IUserManager $userManager, IRootFolder $rootFolder) {
		$this->rootFolder = $rootFolder;

	}

	public function hasAccess($uid, $fileId): bool {
		$nodes = $this->rootFolder->getUserFolder($uid)->getById($fileId);
		return null !== $nodes && \count($nodes) > 1;
	}

}