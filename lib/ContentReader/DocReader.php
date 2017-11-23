<?php
/**
 * Created by PhpStorm.
 * User: dogano
 * Date: 17.11.17
 * Time: 13:16
 */

namespace OCA\DoganMachineLearning\ContentReader;


use OCA\DoganMachineLearning\AppInfo\Application;
use OCP\Files\File;

class DocReader implements IContentReader {

	public function read(File $file): string {
		$dataDir = \OC::$server->getConfig()->getSystemValue('datadirectory', '');
		$filePath = $dataDir . "/" . $file->getPath();

		if (!is_file($filePath)) {
			return "no content";
		}
		$phpWord = new \PhpOffice\PhpWord\PhpWord();
		$content = "";
		foreach ($phpWord->getSections() as $section){
			$elements = $section->getElements();
			foreach ($elements as $element){
			}
		}
		return settype(count($phpWord->getSections()), "string");
	}


	private function debug($message) {
		$logger = \OC::$server->getLogger();
		$logger->debug($message, ["app" => Application::APPNAME]);
	}

}