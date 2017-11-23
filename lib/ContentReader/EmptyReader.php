<?php
/**
 * Created by PhpStorm.
 * User: dogano
 * Date: 18.11.17
 * Time: 13:23
 */

namespace OCA\DoganMachineLearning\ContentReader;


use OCP\Files\File;

class EmptyReader implements IContentReader {

	public function read(File $file): string {
		return "";
	}
}