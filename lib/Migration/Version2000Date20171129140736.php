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
use OCP\Migration\IOutput;
use OCP\Migration\SimpleMigrationStep;

class Version2000Date20171129140736 extends SimpleMigrationStep {
	private const TABLE_NAME_RECOMMENDATIONS = "recommendations";
	private const TABLE_NAME_USER_PROFILE = "user_profile";
	private const ID = "id";
	private const CREATION_TS = "creation_ts";
	private const USER_ID = "user_id";
	private const FILE_ID = "file_id";
	private const KEYWORD = "keyword";

	public function changeSchema(IOutput $output, \Closure $schemaClosure, array $options) {
		/** @var Schema $schema */
		$schema = $schemaClosure();

		if (!$schema->hasTable(Version2000Date20171129140736::TABLE_NAME_RECOMMENDATIONS)) {
			$table = $schema->createTable(Version2000Date20171129140736::TABLE_NAME_RECOMMENDATIONS);
			$table->addColumn(Version2000Date20171129140736::ID, Type::BIGINT, [
				'autoincrement' => true,
				'notnull' => true,
				'length' => 20,
			]);
			$table->addColumn(Version2000Date20171129140736::CREATION_TS, Type::INTEGER, [
				'notnull' => true,
				'length' => 4,
				'default' => 0,
			]);
			$table->addColumn(Version2000Date20171129140736::USER_ID, Type::STRING, [
				'notnull' => true,
				'length' => 64,
				'default' => "",
			]);
			$table->addColumn(Version2000Date20171129140736::FILE_ID, Type::STRING, [
				'notnull' => true,
				'length' => 255,
				'default' => ""
			]);
			$table->setPrimaryKey([Version2000Date20171129140736::ID]);
		}

		if (!$schema->hasTable(Version2000Date20171129140736::TABLE_NAME_USER_PROFILE)) {
			$table = $schema->createTable(Version2000Date20171129140736::TABLE_NAME_USER_PROFILE);
			$table->addColumn(Version2000Date20171129140736::ID, Type::BIGINT,
				['autoincrement' => true,
					'notnull' => true,
					'length' => 20]
			);
			$table->addColumn(Version2000Date20171129140736::USER_ID, Type::STRING, [
				'notnull' => true,
				'length' => 64,
				'default' => "",
			]);
			$table->addColumn(Version2000Date20171129140736::KEYWORD, Type::STRING, [
				'notnull' => true,
				'length' => 250,
				'default' => "",
			]);
			$table->setPrimaryKey([Version2000Date20171129140736::ID]);
		}
		return $schema;
	}
}