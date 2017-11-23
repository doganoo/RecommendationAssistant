<?php
/**
 * Created by PhpStorm.
 * User: dogano
 * Date: 17.11.17
 * Time: 12:07
 */

namespace OCA\DoganMachineLearning\ContentReader;

use OCP\Files\File;

class TextfileReader implements IContentReader {

	public function read(File $file): string {
		return $file->getContent();
	}
}