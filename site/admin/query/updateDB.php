<?php
  /**
  update tables
  */
  chdir('..');
  require_once('common.php');
  session_validate()     or Config::error('403 Forbidden');
  session_mayTranslate() or Config::error('403 Forbidden');
  //Actions:
  switch($_GET['action']){
    case 'update':
    if($_GET['Table'] == 'Transcriptions_'){
        $dbConnection    = Config::getConnection();
        $study = $dbConnection->escape_string($_GET['Study']);
        if(substr($_GET['Table'], -1) == '_'){
          $table = $dbConnection->escape_string($_GET['Table'].$_GET['Study']);
        }else{
          $table = $dbConnection->escape_string($_GET['Table']);
        }
        $pkeys = array();
        $transids = preg_split('/T/', $dbConnection->escape_string((string)$_GET['Transid']));
        $pfields = array('LanguageIx','IxElicitation','IxMorphologicalInstance','AlternativePhoneticRealisationIx','AlternativeLexemIx');
        $i = 0;
        foreach($transids as $k){
          array_push($pkeys, $pfields[$i] . "=" . $k);
          $i++;
        }
        $getfields = $_GET['Fields'];
        $fields = array();
        foreach($getfields as $k=>$v){
          array_push($fields, "$k='".ltrim(rtrim($dbConnection->escape_string($v)))."'");
        }
        $field = join(", ", $fields);
        $pkey = join(" AND ", $pkeys);
        $query = "UPDATE $table SET $field"
          ." WHERE $pkey";
        $dbConnection->query($query);
        //echo $query;
        if($dbConnection->error !== ""){
          echo $dbConnection->error." in: ".$query;
        }
    }
    break;
  }
