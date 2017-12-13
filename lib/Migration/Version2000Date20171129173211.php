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

class Version2000Date20171129173211 extends SimpleMigrationStep {
	const TABLE_NAME_FILES_PROCESSED = "files_processed";
	const ID = "id";
	const CREATION_TS = "creation_ts";
	const FILE_ID = "file_id";
	const KEYWORD = "keyword";
	const TFIDF_VALUE = "tfidf_value";

	public function changeSchema(IOutput $output, \Closure $schemaClosure, array $options) {
		/** @var Schema $schema */
		$schema = $schemaClosure();

		if (!$schema->hasTable(Version2000Date20171129173211::TABLE_NAME_FILES_PROCESSED)) {
			$table = $schema->createTable(Version2000Date20171129173211::TABLE_NAME_FILES_PROCESSED);
			$table->addColumn(Version2000Date20171129173211::ID, Type::BIGINT, [
				'autoincrement' => true,
				'notnull' => true,
				'length' => 20,
			]);
			$table->addColumn(Version2000Date20171129173211::CREATION_TS, Type::INTEGER, [
				'notnull' => true,
				'length' => 4,
				'default' => 0,
			]);
			$table->addColumn(Version2000Date20171129173211::FILE_ID, Type::INTEGER, [
				'notnull' => true,
				'length' => 4,
				'default' => 0
			]);
			$table->setPrimaryKey([Version2000Date20171129173211::ID]);
		}
		return $schema;
	}
}