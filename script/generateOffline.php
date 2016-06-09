<?php
// Settings:
$baseUrl = "http://127.0.0.1/query";
// Move into root directory:
chdir(__DIR__);
chdir('..');
// Compute $path and create it:
$date = trim(`date -I`);
$path = '/tmp/sndComp_'.$date.'_export';
`mkdir -p $path/data`;
// Create index.html:
`php -f index.php > $path/index.html`;
// Copy static directories:
`cp -rv css/ js/ img/ LICENSE README.md $path/`;
// Helper to fetch json files:
function fetchJSON($url, $file){
  echo $url.' -> '.$file."\n";
  $data = file_get_contents($url);
  file_put_contents($file, $data);
  return $data;
}
// Generate .json files to load for the site:
fetchJSON("$baseUrl/data", "$path/data/data");
$global = fetchJSON("$baseUrl/data?global", "$path/data/data_global");
// Parsing global data to iterate studies:
$global = json_decode($global, true);
foreach($global['studies'] as $study){
   fetchJSON("$baseUrl/data?study=$study", "$path/data/data_study_$study");
}
// Providing template files:
fetchJSON("$baseUrl/templateInfo", "$path/data/templateInfo");
`cp -rv templates/* $path/data`;
// Providing translation files:
$tdata = fetchJSON("$baseUrl/translations?action=summary",
                   "$path/data/translations_action_summary");
// Combined translations map:
$tdata = json_decode($tdata, true);
$url = "$baseUrl/translations?lng=";
$file = "$path/data/translations_lng_";
$lnames = array();
foreach($tdata as $t){
  array_push($lnames, $t['BrowserMatch']);
}
$lnames = implode('+', $lnames);
$url .= "$lnames&ns=translation";
$file .= $lnames."_ns_translation";
fetchJSON($url, $file);
// FIXME IMPLEMENT
// Fetch map tiles for offline usage:
// FIXME IMPLEMENT
