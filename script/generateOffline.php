<?php
/*
To call this script from console use commands like these:
env EXPORT_TASK=data php -f generateOffline.php
*/
// Settings:
$homeUrl = "http://soundcomparisons.com";
$baseUrl = $homeUrl."/query";
// Move into root directory:
chdir(__DIR__);
chdir('..');
// Compute $path and create it:
$date = trim(`date -I`);
$path = './sndComp_'.$date.'_export';
`mkdir -p $path/data`;
`mkdir -p $path/js`;
`mkdir -p $path/js/extern`;
// Copy static directories:
`cp -rv site/css/ site/img/ LICENSE README.md $path/`;
// Copy js staff
`cp -v site/js/extern/FileSaver.js $path/js/extern/`;
// Copy js app file
`find js/ -maxdepth 1 -type f -name "App\-minified*" -exec cp {} $path/js/`;
// Create index.html:
`curl $homeUrl > $path/index.html`;
preg_match('/js\/App.minified\..+\.js/', `grep "js/App-minified" $path/index.html`, $matches);
$jsFile = $matches[0];
`curl "$homeUrl/$jsFile" > $path/$jsFile`;
// Helper to fetch json files:
function fetchJSON($url, $file, $prefix){
  echo $url.' -> '.$file.".js\n";
  $data = file_get_contents($url);
  file_put_contents($file.".js", $prefix.$data);
  return $data;
}
if(preg_match('/^$|data|all/', getenv('EXPORT_TASK'))){
  // Generate .json files to load for the site:
  fetchJSON("$baseUrl/data", "$path/data/data", "var localData=");
  $global = fetchJSON("$baseUrl/data?global", "$path/data/data_global", "var localDataGlobal=");
  // Parsing global data to iterate studies:
  $global = json_decode($global, true);
  foreach($global['studies'] as $study){
    if($study !== '--'){ //skip delimiters
      fetchJSON("$baseUrl/data?study=$study", "$path/data/data_study_$study", "var localDataStudy$study=");
    }
  }
  // Providing translation files:
  $tdata = fetchJSON("$baseUrl/translations?action=summary",
                     "$path/data/translations_action_summary", "var localTranslationsActionSummary=");
  // Combined translations map:
  $tdata = json_decode($tdata, true);
  $url = "$baseUrl/translations?lng=";
  $file = "$path/data/translations_i18n";
  $lnames = array();
  foreach($tdata as $t){
    array_push($lnames, $t['BrowserMatch']);
  }
  $url .= implode('+', $lnames)."&ns=translation";
  fetchJSON($url, $file, "var localTranslationsI18n=");
}
if(preg_match('/^$|map|all/', getenv('EXPORT_TASK'))){
  // Fetch map tiles for offline usage:
  function getMapUrl($z, $x, $y){
    return "http://127.0.0.1:27374/mapbox-studio-osm-bright/$z/$x/$y.png";
  }
  /*
  $bounds :: [{x :: {min :: Int, max :: Int},
          ::   y :: {min :: Int, max :: Int},
          ::   z :: Int}]
  */
  $bounds = array();
  // Fill bounds with base zoom  levels:
  for($z = 0; $z <= 8; $z++){
    array_push($bounds, array('x' => array('min' => 0, 'max' => pow(2, $z)),
                              'y' => array('min' => 0, 'max' => pow(2, $z)),
                              'z' => $z));
  }
  // Add bounds for high resolution areas:
  $addBounds = array( // [{1 :: {lat :: Double, lon :: Double}, 2 :: {lat :: Double, lon :: Double}}]
    //Bounds for Malakula:
    array(array('lat' => -15.844522, 'lon' => 167.106752),
          array('lat' => -16.612215, 'lon' => 167.869164))
  );
  /*
    @param $lat :: Double, Deg
    @param $lon :: Double, Deg
    @param $zoom :: Int
  */
  function projectToTile($lat, $lon, $zoom){
    $x = floor(($lon + 180) / 360 * (1<<$zoom));
    $y = floor((1 - log(tan(deg2rad($lat)) + 1 / cos(deg2rad($lat))) / pi()) / 2 * (1<<$zoom));
    return array('x' => $x, 'y' => $y);
  }
  //Adding $addBounds to $bounds:
  foreach($addBounds as $addBound){
    $b0 = $addBound[0];
    $b1 = $addBound[1];
    for($z = 9; $z <= 17; $z++){
      $t0 = projectToTile($b0['lat'], $b0['lon'], $z);
      $t1 = projectToTile($b1['lat'], $b1['lon'], $z);
      array_push($bounds, array('x' => array('min' => min($t0['x'], $t1['x']),
                                             'max' => max($t0['x'], $t1['x']) + 1),
                                'y' => array('min' => min($t0['y'], $t1['y']),
                                             'max' => max($t0['y'], $t1['y']) + 1),
                                'z' => $z));
    }
  }
  // Download bounds:
  foreach($bounds as $bound){
    $z = $bound['z'];
    for($x = $bound['x']['min']; $x < $bound['x']['max']; $x++){
      `mkdir -p $path/mapnik/$z/$x`;
      for($y = $bound['y']['min']; $y < $bound['y']['max']; $y++){
        $url = "http://127.0.0.1:27374/mapbox-studio-osm-bright/$z/$x/$y.png";
        $file = "$path/mapnik/$z/$x/$y.png";
        echo "Loading map file: $url\n";
        file_put_contents($file, fopen($url, 'r'));
      }
    }
  }
}
