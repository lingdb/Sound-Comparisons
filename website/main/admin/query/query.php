<?php
  /* Setup and session verification */
  chdir('..');
  require_once 'common.php';
  session_validate() or Config::error('403 Forbidden');
  session_mayEdit()  or Config::error('403 Forbidden');
  //Actions:
  switch($_GET['action']){
    /**
      @return String html - options for LanguageFamilies to choose, with their Id's as attributes.
    */
    case 'fetchLanguageFamilySelection':
      $q = 'SELECT CONCAT(StudyIx, FamilyIx, SubFamilyIx), StudyIx, FamilyIx, SubFamilyIx, Name FROM Studies';
      $set = $dbConnection->query($q);
      while($r = $set->fetch_row()){
        $id   = $r[0];
        $six  = $r[1];
        $fix  = $r[2];
        $sfix = $r[3];
        $name = $r[4];
        echo "<option data-dbid='$id' data-studyix='$six' data-familyix='$fix' data-subfamilyix='$sfix'>$name</option>";
      }
    break;
    /**
      @param studyix String     - Interpretation as int
      @param familyix String    - Interpretation as int
      @param subfamilyix String - Interpretation as int
      @param name String
      @return 'FAIL' | String html - new option for LanguageFamilies.
    */
    case 'createLanguageFamily':
      //Fetching expected parameters:
      $studyix      = $dbConnection->escape_string($_GET['studyix']);
      $familyix     = $dbConnection->escape_string($_GET['familyix']);
      $subfamilyix  = $dbConnection->escape_string($_GET['familyix']);
      $name         = $dbConnection->escape_string($_GET['name']);
      /*
        Depending tables have to be created first,
        because the procedure 'createTablesAndRecreateViews' aborts
        if the study already exists, to avoid recreating the views without need.
      */
      $dbConnection->query("CALL createTablesAndRecreateViews('$name')");
      //Inserting the new Study:
      $q = "INSERT INTO Studies(StudyIx, FamilyIx, SubFamilyIx, Name) "
         . "VALUES ($studyix, $familyix, $subfamilyix, '$name')";
      $dbConnection->query($q);
      if($dbConnection->affected_rows != 1)
        Config::error('FAIL');
      //Done:
      echo "<option data-dbid='$studyix$familyix$subfamilyix' data-studyix='$studyix'"
         . " data-familyix='$familyix' data-subfamilyix='$subfamilyix'>$name</option>";
    break;
  }
?>
