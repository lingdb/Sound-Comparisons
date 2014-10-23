<?php
/**
  This script aims to work as a trampoline for downloads, that shall be saved rather than opened in the browser.
  To achieve this, it's necessary to set the http headers accordingly.
  We expect a file get parameter to be given.
*/
if(!array_key_exists('file', $_GET)){
  die('You must supply a file parameter.');
}
chdir('..');
require_once 'config.php';
$file = $_GET['file'];
//Guarding against traversal:
if(preg_match('/\\.\\./', $file)){
  error_log(__FILE__.' prevented access to '.$file);
  die('Sorry, I cannot serve this file.');
}
//Checking existence:
if(!file_exists($file)){
  error_log("Could not serve file: $file from directory: ".`pwd`);
  die('Sorry, the requested file does not appear to exist.');
}
//Setting headers:
header('Content-Disposition: attachment;filename="'.basename($file).'"');
//Handing over the file:
readfile($file);
?>
