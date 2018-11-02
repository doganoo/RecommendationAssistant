<?php
/**
 * @copyright Copyright (c) 2017, Dogan Ucar (dogan@dogan-ucar.de)
 * @license   GNU AGPL version 3 or any later version
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as
 * published by the Free Software Foundation, either version 3 of the
 * License, or (at your option) any later version.
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Affero General Public License for more details.
 * You should have received a copy of the GNU Affero General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

namespace OCA\RecommendationAssistant\BackgroundJob;

use OC\BackgroundJob\TimedJob;
use OCA\RecommendationAssistant\AppInfo\Application;
use OCA\RecommendationAssistant\Db\ChangedFilesManager;
use OCA\RecommendationAssistant\Log\ConsoleLogger;
use OCA\RecommendationAssistant\Service\RecommendationService;

/**
 * Class SimilarityJob
 *
 * @package OCA\RecommendationAssistant\BackgroundJob
 */
class SimilarityJob extends TimedJob {
	/** @var float|int INTERVAL */
	const INTERVAL = 5 * 60 * 60;
	/** @var null|RecommendationService $recommendationService */
	private $recommendationService = null;
	/** @var null|ChangedFilesManager $filesManager */
	private $filesManager = null;

	/**
	 * SimilarityJob constructor.
	 *
	 * @param RecommendationService $recommendationService
	 * @param ChangedFilesManager $filesManager
	 */
	public function __construct(
		RecommendationService $recommendationService
		, ChangedFilesManager $filesManager
	) {
		$systemConfig = \OC::$server->getSystemConfig();
		$debug = $systemConfig->getValue("debug", false);

		if ($debug) {
			$this->setInterval(1);
		} else {
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
	 * @throws \OCA\RecommendationAssistant\Exception\InvalidRatingException
	 */
	protected function run($argument) {
		ConsoleLogger::debug("SimilarityJob start");
		$this->filesManager->deleteOutdated();
		$list = $this->filesManager->allFiles();
		ConsoleLogger::debug("number of files: {$list->length()} ");
		$list = $this->recommendationService->computeCosine($list);
		ConsoleLogger::debug("serializing {$list->length()} cosine files");
		$serialized = \serialize($list);
		\file_put_contents(Application::SERIALIZATION_FILE_NAME, $serialized);
		$list = null;
		ConsoleLogger::debug("SimilarityJob end");
	}

}