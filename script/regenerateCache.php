<?php
/**
  It became apparent, that generating the JSON files export/download/$study.json
  can be quite expansive, and may fail on our server iff memory or time consumption grow too high.
  This is also documented in #237.
  To combat this problem this script (re-)generates all the JSON files as necessary.
*/
chdir(__DIR__);
require_once('../config.php');
require_once('../query/cacheProvider.php');
chdir('..');

echo "Regenerating Study Cache:\n";
foreach(DataProvider::getStudies() as $study){
  if(CacheProvider::hasCache($study))
    continue;
  echo "$study..\n";
  $chunk = DataProvider::getStudyChunk($study);
  CacheProvider::setCache($study, json_encode($chunk));
}
echo "Done.\n";
