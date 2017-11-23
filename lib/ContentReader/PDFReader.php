<?php
/**
 * Created by PhpStorm.
 * User: dogano
 * Date: 17.11.17
 * Time: 11:28
 */

namespace OCA\DoganMachineLearning\ContentReader;

use OCP\Files\File;
use Smalot\PdfParser\Parser;

class PDFReader implements IContentReader {

	private $parser = null;

	public function __construct() {
		$this->parser = new Parser();
	}

	public function read(File $file): string {
		$content = $this->parser->parseContent($file->getContent());
		return $content->getText();
	}
}