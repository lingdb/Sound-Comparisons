<?php
  /* Setup and session verification */
  chdir('..');
  require_once 'common.php';
  session_validate($dbConnection) or die('403 Forbidden');
  session_mayEdit($dbConnection)  or die('403 Forbidden');
  //Actions:
  switch($_GET['action']){
    /**
      @return String html - options for LanguageFamilies to choose, with their Id's as attributes.
    */
    case 'fetchLanguageFamilySelection':
      $q = 'SELECT CONCAT(StudyIx, FamilyIx, SubFamilyIx), StudyIx, FamilyIx, SubFamilyIx, Name FROM Studies';
      $set = mysql_query($q, $dbConnection);
      while($r = mysql_fetch_row($set)){
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
      $studyix      = mysql_real_escape_string($_GET['studyix']);
      $familyix     = mysql_real_escape_string($_GET['familyix']);
      $subfamilyix  = mysql_real_escape_string($_GET['familyix']);
      $name         = mysql_real_escape_string($_GET['name']);
      /*
        Depending tables have to be created first,
        because the procedure 'createTablesAndRecreateViews' aborts
        if the study already exists, to avoid recreating the views without need.
      */
      mysql_query("CALL createTablesAndRecreateViews('$name')", $dbConnection);
      //Inserting the new Study:
      $q = "INSERT INTO Studies(StudyIx, FamilyIx, SubFamilyIx, Name) "
         . "VALUES ($studyix, $familyix, $subfamilyix, '$name')";
      mysql_query($q, $dbConnection);
      if(mysql_affected_rows($dbConnection) != 1)
        die('FAIL');
      //Done:
      echo "<option data-dbid='$studyix$familyix$subfamilyix' data-studyix='$studyix'"
         . " data-familyix='$familyix' data-subfamilyix='$subfamilyix'>$name</option>";
    break;
  }
?>
