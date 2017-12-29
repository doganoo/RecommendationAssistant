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

namespace OCA\RecommendationAssistant\Recommendation;


use OCA\RecommendationAssistant\Db\GroupWeightsManager;
use OCP\IGroup;

/**
 * GroupWeightComputer class that computes the total sum of all weights
 * of a given group list.
 * Actually, the sum is calculated as the average of all weights.
 *
 * @package OCA\RecommendationAssistant\Recommendation
 * @since 1.0.0
 */
class GroupWeightComputer {
	/**
	 * @var array the source groups list
	 */
	private $sourceGroups = [];

	/**
	 * @var array the target groups list
	 */
	private $targetGroups = [];

	/**
	 * @var GroupWeightsManager the manager class in order to retrieve the
	 * weights from the storage layer
	 */
	private $groupWeightManager = null;


	/**
	 * Class constructor gets two arrays of IGroupManager instances and the
	 * GroupWeightsManager injected.
	 *
	 * @param array $sourceGroups the source groups
	 * @param array $targetGroups the target groups
	 * @param GroupWeightsManager $groupWeightsManager the group weights manager
	 * instance
	 * @since 1.0.0
	 */
	public function __construct(
		array $sourceGroups,
		array $targetGroups,
		GroupWeightsManager $groupWeightsManager) {
		$this->sourceGroups = $sourceGroups;
		$this->targetGroups = $targetGroups;
		$this->groupWeightManager = $groupWeightsManager;
	}

	/**
	 * This method simply creates an average of all groups that are in
	 * $sourceGroups and $targetGroups.
	 *
	 * @return float average of all weights
	 * @since 1.0.0
	 */
	public function calculateWeight(): float {
		$sum = 0;
		$sourceGroupSize = count($this->sourceGroups);
		/** @var IGroup $sourceGroup */
		foreach ($this->sourceGroups as $sourceGroup) {
			/** @var IGroup $targetGroup */
			foreach ($this->targetGroups as $targetGroup) {
				$sum = $this->groupWeightManager->getGroupWeightForGroups($sourceGroup->getGID(), $targetGroup->getGID());
			}
		}
		if ($sourceGroupSize == 0) {
			return 1;
		}
		return $sum / $sourceGroupSize;
	}
}