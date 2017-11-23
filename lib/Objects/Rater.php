<?php
/**
 * Created by PhpStorm.
 * User: dogano
 * Date: 15.11.17
 * Time: 14:10
 */

namespace OCA\DoganMachineLearning\Objects;


use OCP\IUser;

class Rater {
	private $user;
	private $rating;

	public const LIKE = 1;
	public const NO_LIKE = 0;

	public function __construct($user) {
		$this->user = $user;
	}

	public function setRating(int $rating) {
		$this->rating = $rating;
	}

	public function getRating(): int {
		return $this->rating;
	}

	public function getUser(): IUser {
		return $this->user;
	}
}