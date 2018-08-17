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

namespace OCA\RecommendationAssistant\Db;

/**
 * Class that servers as collection for database constants. This class contains
 * table and field names as well as general database related attributes.
 *
 * Notice that this serves only for the abstraction layer. The underlying storage
 * type may differ.
 *
 * @package OCA\RecommendationAssistant\Db
 * @since 1.0.0
 */
class DbConstants {
	/**
	 * General database related attributes
	 */
	const AUTOINCREMENT = "autoincrement";
	const NOTNULL = "notnull";
	const LENGTH = "length";
	const COLUMN_DEFAULT = "default";

	/**
	 * RECOMMENDATIONS TABLE
	 */
	const TABLE_NAME_RECOMMENDATIONS = "recommendations";
	const TB_RC_CREATION_TS = "creation_ts";
	const TB_RC_ID = "id";
	const TB_RC_USER_ID = "user_id";
	const TB_RC_OWNER_ID = "owner_id";
	const TB_RC_FILE_ID = "file_id";
	const TB_RC_FILE_NAME = "file_name";
	const TB_RC_TRANSPARENCY_CODE = "transparency_code";
	const TB_RC_RECOMMENDATION_SCORE = "recommendation_score";

	/**
	 * USER PROFILE TABLE
	 */
	const TABLE_NAME_USER_PROFILE = "user_profile";
	const TB_UP_ID = "id";
	const TB_UP_USER_ID = "user_id";
	const TB_UP_CREATION_TS = "creation_ts";
	const TB_UP_KEYWORD = "keyword";
	const TB_UP_TFIDF_VALUE = "tfidf_value";

	/**
	 * FILES PROCESSED TABLE
	 */
	const TABLE_NAME_FILES_PROCESSED = "files_processed";
	const TB_FP_ID = "id";
	const TB_FP_CREATION_TS = "creation_ts";
	const TB_FP_FILE_ID = "file_id";
	const TB_FP_KEYWORD = "keyword";
	const TB_FP_TFIDF_VALUE = "tfidf_value";
	const TB_FP_TYPE = "type";

	/**
	 * GROUP WEIGHTS TABLE
	 */
	const TABLE_NAME_GROUP_WEIGHTS = "group_weights";
	const TB_GW_ID = "id";
	const TB_GW_SOURCE_GROUP_ID = "source_group_id";
	const TB_GW_TARGET_GROUP_ID = "target_group_id";
	const TB_GW_CREATION_TS = "creation_ts";
	const TB_GW_VALUE = "value";

	/**
	 * FILE CHANGE LOG
	 */
	const TABLE_NAME_CHANGED_FILES_LOG = "changed_files_log";
	const TB_CFL_ID = "id";
	const TB_CFL_FILE_ID = "file_id";
	const TB_CFL_CHANGE_TS = "change_ts";
	const TB_CFL_CREATION_TS = "creation_ts";
	const TB_CFL_TYPE = "type";
	const TB_CFL_USER_ID = "user_id";

}