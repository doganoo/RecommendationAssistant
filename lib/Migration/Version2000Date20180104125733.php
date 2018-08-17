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

namespace OCA\RecommendationAssistant\Migration;


use Doctrine\DBAL\Schema\Schema;
use Doctrine\DBAL\Types\Type;
use OCA\RecommendationAssistant\Db\DbConstants;
use OCP\Migration\IOutput;
use OCP\Migration\SimpleMigrationStep;

class Version2000Date20180104125733 extends SimpleMigrationStep {
	public function changeSchema(IOutput $output, \Closure $schemaClosure, array $options) {
		/** @var Schema $schema */
		$schema = $schemaClosure();

		if ($schema->hasTable(DbConstants::TABLE_NAME_USER_PROFILE)) {
			$table = $schema->getTable(DbConstants::TABLE_NAME_USER_PROFILE);
			if ($table->hasColumn(DbConstants::TB_UP_KEYWORD)) {
				$table->dropColumn(DbConstants::TB_UP_KEYWORD);

				$table->addColumn(DbConstants::TB_UP_KEYWORD, Type::TEXT, [
					DbConstants::NOTNULL => true,
					DbConstants::LENGTH => 250,
					DbConstants::COLUMN_DEFAULT => "",
				]);
			}
		}
		return $schema;
	}
}