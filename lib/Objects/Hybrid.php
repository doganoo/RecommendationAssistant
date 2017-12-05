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

namespace OCA\RecommendationAssistant\Objects;


use OCA\RecommendationAssistant\AppInfo\Application;
use OCA\RecommendationAssistant\Exception\UnsupportedHybridType;

/**
 * Class that is responsible for the hybridization after two or multiple
 * recommendation processes have calculated similarity.
 *
 * @package OCA\RecommendationAssistant\Objects
 * @since 1.0.0
 */
class Hybrid {
	/**
	 * @var array the array that contains all items and associated values
	 */
	private $array = [];

	/**
	 * @const TYPE_COLLABORATIVE collaborative filtering hybridization type
	 */
	public const TYPE_COLLABORATIVE = 1;

	/**
	 * @const TYPE_COLLABORATIVE_WEIGHT weight of the collaborative filtering hybridization type
	 */
	public const TYPE_COLLABORATIVE_WEIGHT = 0.5;

	/**
	 * @const TYPE_CONTENT_BASED content based recommendation hybridization type
	 */
	public const TYPE_CONTENT_BASED = 2;

	/**
	 * @const TYPE_CONTENT_BASED_WEIGHT weight of content based recommendation hybridization type
	 */
	public const TYPE_CONTENT_BASED_WEIGHT = 0.5;

	/**
	 * @var \OCP\IL10N $l l10n member
	 */
	private $l = null;

	/**
	 * Class constructor requests the l10n instance for translation
	 *
	 * @since 1.0.0
	 */
	public function __construct() {
		$this->l = \OC::$server->getL10N(Application::APP_ID);
	}

	/**
	 * adds a similarity value, the type and the item. The type has to be
	 * one of the defined in this class, otherwise an exception will be thrown.
	 *
	 * @param float $value the similarity value
	 * @param int $type the type for which the value is defined
	 * @param Item $item the item that for which the value is defined
	 * @throws UnsupportedHybridType
	 * @since 1.0.0
	 */
	public function addValue(float $value, int $type, Item $item) {
		if ($type == Hybrid::TYPE_COLLABORATIVE || $type == Hybrid::TYPE_CONTENT_BASED) {
			$this->array[$item->getId()][$type] = $value;
		} else {
			throw new UnsupportedHybridType($this->l->t("please specify a supported hybridization type"));
		}
	}

	/**
	 * requests the weighted average for a item. This method will return the value
	 * of 0 if the item is not present in the list.
	 *
	 * @param Item $item the item for that the value is requested
	 * @since 1.0.0
	 */
	public function getRecommendationValue(Item $item) {
		if (!isset($this->array[$item->getId()])) {
			return 0;
		}
		$collaborative = $this->array[$item->getId()][Hybrid::TYPE_COLLABORATIVE];
		$contentBased = $this->array[$item->getId()][Hybrid::TYPE_CONTENT_BASED];
		$recommendationValue = $collaborative * Hybrid::TYPE_COLLABORATIVE_WEIGHT + $contentBased * Hybrid::TYPE_CONTENT_BASED_WEIGHT;
		return $recommendationValue;
	}
}