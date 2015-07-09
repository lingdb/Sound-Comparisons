<?php
/**
  This file shall aid fetching languoids, from either our own database,
  or a different source, probably given by R.Forkel.
  The schema used herein orientates on the structure of 'http://glottolog.org/resource/languoid/id/abon1238.json',
  which is presented under 'http://glottolog.org/resource/languoid/id/abon1238'.
  We expect GET requests, with the parameter 'id' and, optional, 'source'.
  In case a source parameter is specified, we look it up in a source arry,
  and if the source is known, this file acts as a proxy to that source.
*/
$sources = array(
  'glottolog' => function($id){return "http://glottolog.org/resource/languoid/id/$id.json";}
);
//FIXME this part is on hold, until I understand how glottolog does things.
