<?php

namespace OCA\RecommendationAssistant\BackgroundJob;

use OC\BackgroundJob\TimedJob;
use OCA\RecommendationAssistant\AppInfo\Application;
use OCA\RecommendationAssistant\Db\ChangedFilesManager;
use OCA\RecommendationAssistant\Log\ConsoleLogger;
use OCA\RecommendationAssistant\Service\RecommendationService;

class SimilarityJob extends TimedJob{
	const INTERVAL = 5 * 60 * 60;
	private $recommendationService = null;
	private $filesManager = null;

	public function __construct(
		RecommendationService $recommendationService
		, ChangedFilesManager $filesManager
	){
		if(Application::DEBUG){
			$this->setInterval(1);
		} else{
			$this->setInterval(SimilarityJob::INTERVAL);
		}
		$this->recommendationService = $recommendationService;
		$this->filesManager = $filesManager;
	}

	/**
	 * @param $argument
	 *
	 * @throws \OCP\Files\InvalidPathException
	 * @throws \OCP\Files\NotFoundException
	 * @throws \doganoo\PHPAlgorithms\Common\Exception\IndexOutOfBoundsException
	 * @throws \doganoo\PHPAlgorithms\Common\Exception\InvalidKeyTypeException
	 * @throws \doganoo\PHPAlgorithms\Common\Exception\UnsupportedKeyTypeException
	 */
	protected function run($argument){
		ConsoleLogger::debug("SimilarityJob start");
		$this->filesManager->deleteOutdated();
		$list = $this->filesManager->allFiles();
		$list = $this->recommendationService->computeCosine($list);
		$serialized = \serialize($list);
		\file_put_contents(Application::SERIALIZATION_FILE_NAME, $serialized);
		ConsoleLogger::debug("SimilarityJob end");
	}

}