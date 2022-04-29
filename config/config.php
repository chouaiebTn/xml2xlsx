<?php 

defined("DS") ? null : define("DS", DIRECTORY_SEPARATOR);
define(XML_DIR,dirname(__DIR__).DS.'www'.DS.'uploads'.DS.'xml'.DS);
define(XLSX_DIR,dirname(__DIR__).DS.'www'.DS.'uploads'.DS.'xlsx'.DS);
define(XLSX_DOWNLOAD,'uploads'.DS.'xlsx'.DS);
define(XML_FILE_UPLOAD_NAME,'xml_file');
define(DEBUG_ERRORS,false);
?>