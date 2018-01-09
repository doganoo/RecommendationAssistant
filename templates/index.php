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
script(\OCA\RecommendationAssistant\AppInfo\Application::APP_ID, 'app');

/** @var $l \OCP\IL10N */
/** @var $_ array */

/** @var \OCP\Files\IRootFolder $rootFolder */
$rootFolder = $_["rootFolder"];
?>


<div id="app-content">
	<?
	/** @var \OCA\RecommendationAssistant\Objects\Recommendation $recommendation */
	foreach ($_["recommendations"] as $recommendation) {
		/** @var \OCP\Files\Node $node */
		$node = $rootFolder->getById($recommendation->getFileId());
		/** @var \OCP\Files\Node $value */
		foreach ($node as $value) {
			if ($value->getId() == $recommendation->getFileId()) {
				echo '<div id="container">';
				echo $value->getName();
				echo " to ";
				echo $recommendation->getUserId();
				echo '</div>';
			}
		}
	}
	?>
</div>
