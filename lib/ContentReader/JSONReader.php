<?php
/**
 * Created by PhpStorm.
 * User: dogano
 * Date: 17.11.17
 * Time: 13:05
 */

namespace OCA\DoganMachineLearning\ContentReader;


use OCP\Files\File;

class JSONReader implements IContentReader {

	public function read(File $file): string {
		$array = json_decode($file->getContent());
		return implode(" ", $array);
	}
}