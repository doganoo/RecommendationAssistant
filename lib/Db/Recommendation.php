<?php
/**
 * Created by PhpStorm.
 * User: dogano
 * Date: 14.01.18
 * Time: 13:36
 */

namespace OCA\RecommendationAssistant\Db;


use OCP\AppFramework\Db\Entity;

class Recommendation extends Entity implements \JsonSerializable {

	protected $fileId;
	protected $fileName;

	/**
	 * Specify data which should be serialized to JSON
	 *
	 * @link http://php.net/manual/en/jsonserializable.jsonserialize.php
	 * @return mixed data which can be serialized by <b>json_encode</b>,
	 * which is a value of any type other than a resource.
	 * @since 5.4.0
	 */
	public function jsonSerialize() {
		return ["fileId" => $this->fileId, "fileName" => $this->fileName];
	}
}