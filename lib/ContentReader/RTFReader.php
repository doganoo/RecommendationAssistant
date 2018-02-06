<?php
/**
 * Created by PhpStorm.
 * User: dogano
 * Date: 05.12.17
 * Time: 13:45
 */

namespace OCA\RecommendationAssistant\ContentReader;


use OCA\RecommendationAssistant\Interfaces\IContentReader;
use OCA\RecommendationAssistant\Log\Logger;
use OCP\Files\File;
use OCP\Files\NotPermittedException;

class RTFReader implements IContentReader {

	/**
	 * reads the file content
	 *
	 * @param File $file the actual file
	 * @return string file content
	 * @since 1.0.0
	 */
	public function read(File $file): string {
		$parser = new \RtfReader();
		try {
			$parser->Parse($file->getContent());
		} catch (NotPermittedException $e) {
			Logger::error($e->getMessage());
			return "";
		}
		$formatter = new \RtfHtml();
		$text = $formatter->Format($parser->root);
		return strip_tags(str_replace('<', ' <', $text));
	}
}