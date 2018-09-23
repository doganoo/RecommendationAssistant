<?php

namespace OCA\RecommendationAssistant\Tests;

use doganoo\PHPAlgorithms\Datastructure\Lists\ArrayLists\ArrayList;
use doganoo\PHPAlgorithms\Datastructure\Sets\HashSet;
use OCA\RecommendationAssistant\Log\ConsoleLogger;
use OCA\RecommendationAssistant\Objects\Item;
use OCA\RecommendationAssistant\Objects\Rater;
use OCA\RecommendationAssistant\Service\RecommendationService;

class SimilarityJobTest extends TestCase{
	public const MOVIE_COUNT = 10;

	/**
	 * @throws \OCA\RecommendationAssistant\Exception\InvalidRatingException
	 * @throws \doganoo\PHPAlgorithms\Common\Exception\IndexOutOfBoundsException
	 * @throws \doganoo\PHPAlgorithms\Common\Exception\InvalidKeyTypeException
	 * @throws \doganoo\PHPAlgorithms\Common\Exception\UnsupportedKeyTypeException
	 */
	public function testRead(){
		$users = new HashSet();
		$recommendationService = new RecommendationService();
		$items = $this->getMovies();
		$items = $this->addRatings($items, $users);
		$items = $recommendationService->computeCosine($items);
		$this->assertTrue($items->length() === $items->length());
		ConsoleLogger::debug(\serialize($items));
	}

	/**
	 * @return ArrayList
	 */
	private function getMovies(): ArrayList{
		ConsoleLogger::debug("start movie reading");
		$fileHandle = fopen("assets/movies.csv", "r");
		$items = new ArrayList();
		$i = 0;
		while(($row = fgetcsv($fileHandle, 0, ",")) !== false){
			$id = $row[0];
			$id = \intval($id);
			$title = $row[1];
			$item = new Item();
			$item->setId($id);
			$item->setName($title);
			$item->setOwnerId("doganoo");
			$items->add($item);
			$i ++;
			if($i === SimilarityJobTest::MOVIE_COUNT){
				break;
			}
		}
		ConsoleLogger::debug("end movie reading");
		return $items;
	}

	/**
	 * @param ArrayList $movies
	 * @param HashSet   $users
	 *
	 * @return ArrayList
	 * @throws \OCA\RecommendationAssistant\Exception\InvalidRatingException
	 * @throws \doganoo\PHPAlgorithms\common\Exception\InvalidKeyTypeException
	 * @throws \doganoo\PHPAlgorithms\common\Exception\UnsupportedKeyTypeException
	 */
	private function addRatings(ArrayList $movies, HashSet &$users): ArrayList{
		ConsoleLogger::debug("start rating reading");
		$fileHandle = fopen("assets/ratings.csv", "r");
		while(($row = fgetcsv($fileHandle, 0, ",")) !== false){
			$movieId = $row[1];
			$movieId = \floatval($movieId);
			$userId = $row[0];
			$rating = $row[2];
			/** @var Item $movie */
			foreach($movies as $movie){
				$users->add($userId);
				if($movie->getId() == $movieId){
					$movie->addRater($this->getRater($userId, $rating));
				}
			}
		}
		ConsoleLogger::debug("end rating reading");
		return $movies;
	}

	/**
	 * @param $uid
	 * @param $rating
	 *
	 * @return Rater
	 * @throws \OCA\RecommendationAssistant\Exception\InvalidRatingException
	 */
	private function getRater($uid, $rating){
		$rater = new Rater($uid);
		$rating = \floatval($rating);
		$rater->setRating($rating);
		return $rater;
	}
}