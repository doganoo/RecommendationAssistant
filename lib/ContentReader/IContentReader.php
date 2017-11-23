<?php
/**
 * Created by PhpStorm.
 * User: dogano
 * Date: 17.11.17
 * Time: 11:27
 */

namespace OCA\DoganMachineLearning\ContentReader;

use OCP\Files\File;

interface IContentReader {
	public const TXT = "text/plain";
	public const HTML = "text/html";
	public const PDF = "application/pdf";
	public const JSON = "application/json";
	public const XML = "application/xml";
	public const DOC = "application/msword";
	public const RTF = "application/rtf";
	public const XLS = "application/vnd.ms-excel";
	public const PPT = 'application/vnd.ms-powerpoint';
	public const ODT = 'application/vnd.oasis.opendocument.text';
	public const ODS = 'application/vnd.oasis.opendocument.spreadsheet';

	public function read(File $file): string;
}