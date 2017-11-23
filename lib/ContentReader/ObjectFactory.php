<?php
/**
 * Created by PhpStorm.
 * User: dogano
 * Date: 17.11.17
 * Time: 12:06
 */

namespace OCA\DoganMachineLearning\ContentReader;


class ObjectFactory {

	public static function getContentReader(string $mimeType): IContentReader {
		if ($mimeType === IContentReader::TXT) {
			return new TextfileReader();
		} else if ($mimeType === IContentReader::PDF) {
			return new PDFReader();
		} else if ($mimeType === IContentReader::HTML) {
			return new HTMLReader();
		} else if ($mimeType === IContentReader::JSON) {
			return new JSONReader();
		} else if ($mimeType === IContentReader::XML) {
			return new XMLReader();
		} else if ($mimeType === IContentReader::DOC) {
			return new DocReader();
		} else if ($mimeType === IContentReader::RTF) {
			return new RTFReader();
		} else if ($mimeType === IContentReader::XLS) {
			return new XLSReader();
		} else if ($mimeType === IContentReader::PPT) {
			return new PPTReader();
		} else if ($mimeType === IContentReader::ODT) {
			return new ODTReader();
		} else if ($mimeType === IContentReader::ODS) {
			return new ODSReader();
		} else {
			return new EmptyReader();
		}
	}

}