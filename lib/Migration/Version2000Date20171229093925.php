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

class Version2000Date20171229093925 extends SimpleMigrationStep {
	public function changeSchema(IOutput $output, \Closure $schemaClosure, array $options) {
		/** @var Schema $schema */
		$schema = $schemaClosure();

		if (!$schema->hasTable(DbConstants::TABLE_NAME_GROUP_WEIGHTS)) {
			$table = $schema->createTable(DbConstants::TABLE_NAME_GROUP_WEIGHTS);
			$table->addColumn(DbConstants::TB_GW_ID, Type::BIGINT, [
				DbConstants::AUTOINCREMENT => true,
				DbConstants::NOTNULL => true,
				DbConstants::LENGTH => 20,
			]);

			$table->addColumn(DbConstants::TB_GW_SOURCE_GROUP_ID, Type::STRING, [
				DbConstants::NOTNULL => true,
				DbConstants::LENGTH => 64,
				DbConstants::COLUMN_DEFAULT => 0,
			]);
			$table->addColumn(DbConstants::TB_GW_TARGET_GROUP_ID, Type::STRING, [
				DbConstants::NOTNULL => true,
				DbConstants::LENGTH => 64,
				DbConstants::COLUMN_DEFAULT => 0,
			]);
			$table->addColumn(DbConstants::TB_GW_CREATION_TS, Type::INTEGER, [
				DbConstants::NOTNULL => true,
				DbConstants::LENGTH => 4,
				DbConstants::COLUMN_DEFAULT => 0,
			]);
			$table->addColumn(DbConstants::TB_GW_VALUE, Type::FLOAT, [
				DbConstants::NOTNULL => true,
				DbConstants::COLUMN_DEFAULT => 0,
			]);
			$table->setPrimaryKey([DbConstants::TB_FP_ID]);
		}
		return $schema;
	}
}