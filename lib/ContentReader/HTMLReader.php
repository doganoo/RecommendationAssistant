<?php
/**
 * Created by PhpStorm.
 * User: dogano
 * Date: 17.11.17
 * Time: 13:00
 */

namespace OCA\DoganMachineLearning\ContentReader;


use OCP\Files\File;

class HTMLReader implements IContentReader {

	public function read(File $file): string {
		return strip_tags($file->getContent());
	}
}