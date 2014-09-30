<?php
  /***/
  require_once "DynamicTranslationProvider.php";
  /*
    Mapping between Words_$s and Page_DynamicTranslation:
    CONCAT(Study,'-',IxElicitation,IxMorphologicalInstance) <-> Field
    $c (column)                                             <-> Trans
  */
  class WordsTranslationProvider extends DynamicTranslationProvider{
    public function migrate(){
      $category = $this->getName();
      $column   = $this->getColumn();
      $q = "INSERT INTO Page_DynamicTranslation (TranslationId, Category, Field, Trans) "
         . "SELECT TranslationId, '$category', CONCAT(Study,'-',IxElicitation,IxMorphologicalInstance), $column "
         . "FROM Page_DynamicTranslation_Words";
      $this->dbConnection->query($q);
    }
    public function getTable(){ return 'Words_';}
    public function searchColumn($c, $tId, $searchText){
      //Setup
      $ret         = array();
      $tCol        = $this->translateColumn($c);
      $description = $tCol['description'];
      $origCol     = $tCol['origCol'];
      //Search queries:
      $qs = array($this->translationSearchQuery($tId, $searchText));
      if($this->searchAllTranslations()){
        //We need all Studies to build the search queries:
        $q   = 'SELECT Name FROM Studies';
        $set = $this->dbConnection->query($q);
        foreach($this->fetchRows($set) as $s){
          $s = $s[0];
          $q = "SELECT CONCAT('$s-', IxElicitation, IxMorphologicalInstance), $origCol, 1 "
             . "FROM Words_$s "
             . "WHERE $origCol LIKE '%$searchText%'";
          array_push($qs, $q);
        }
      }
      //Search results:
      foreach($this->runQueries($qs) as $r){
        $payload = $r[0];
        $match   = $r[1];
        $matchId = $r[2];
        $parts   = explode('-', $payload);
        $study   = $parts[0];
        $wId     = $parts[1];
        $q = "SELECT $origCol "
           . "FROM Words_$study "
           . "WHERE CONCAT(IxElicitation, "
           . "IxMorphologicalInstance) = $wId";
        $original = $this->querySingleRow($q);
        $q = $this->getTranslationQuery($payload, $tId);
        $translation = $this->querySingleRow($q);
        array_push($ret, array(
          'Description' => $description
        , 'Match'       => $match
        , 'MatchId'     => $matchId
        , 'Original'    => $original[0]
        , 'Translation' => array(
            'TranslationId'       => $tId
          , 'Translation'         => $translation[0]
          , 'Payload'             => $payload
          , 'TranslationProvider' => $this->getName()
          )
        ));
      }
      return $ret;
    }
    public function offsetsColumn($c, $tId, $study){
      $q = "SELECT COUNT(*) FROM Words_$study";
      $r = $this->querySingleRow($q);
      return $this->offsetsFromCount(current($r));
    }
    public function pageColumn($c, $tId, $study, $offset){
      //Setup
      $ret         = array();
      $tCol        = $this->translateColumn($c);
      $description = $tCol['description'];
      $origCol     = $tCol['origCol'];
      //Page query:
      $q = "SELECT CONCAT('$study-', IxElicitation, IxMorphologicalInstance), $origCol "
         . "FROM Words_$study LIMIT 30 OFFSET $offset";
      foreach($this->fetchRows($q) as $r){
        $payload = $r[0];
        $q = $this->getTranslationQuery($payload, $tId);
        $translation = $this->querySingleRow($q);
        array_push($ret, array(
          'Description' => $description
        , 'Original'    => $r[1]
        , 'Translation' => array(
            'TranslationId'       => $tId
          , 'Translation'         => $translation[0]
          , 'Payload'             => $payload
          , 'TranslationProvider' => $this->getName()
          )
        ));
      }
      return $ret;
    }
    public function translateColumn($c){
      switch($c){
        case 'Trans_FullRfcModernLg01':
          $description = $this->getDescription('dt_words_fullRfcModernLg01');
          $origCol = 'FullRfcModernLg01';
        break;
        case 'Trans_LongerRfcModernLg01':
          $description = $this->getDescription('dt_words_longerRfcModernLg01');
          $origCol = 'LongerRfcModernLg01';
        break;
      }
      return array('description' => $description, 'origCol' => $origCol);
    }
  }
?>
