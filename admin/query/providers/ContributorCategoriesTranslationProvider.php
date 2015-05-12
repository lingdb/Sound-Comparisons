<?php
/***/
require_once "TranslationProvider.php";
/*
  Mapping between tables ContributorCategories, Page_DynamicTranslation:
  SortGroup <-> Field
  Headline  <-> Trans
*/
class ContributorCategoriesTranslationProvider extends TranslationProvider {
  public function search($tId, $searchText, $searchAll = false){
    //Setup:
    $ret = array();
    $description = TranslationProvider::getDescription('dt_contributor_categories_trans');
    //Search queries:
    $qs = array($this->translationSearchQuery($tId, $searchText));
    if($searchAll){
      array_push($qs,
        "SELECT SortGroup, Headline, 1 FROM ContributorCategories "
      . "WHERE Headline LIKE '%$searchText%'");
    }
    foreach($this->runQueries($qs) as $r){
      $payload = $r[0];
      $match   = $r[1];
      $matchId = $r[2];
      $q = 'SELECT Headline FROM ContributorCategories '
         . "WHERE SortGroup = $payload";
      $original = $this->dbConnection->query($q)->fetch_row();
      $q = $this->getTranslationQuery($payload, $tId);
      $translation = $this->dbConnection->query($q)->fetch_row();
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
  public function offsets($tId, $study){
    $q = 'SELECT COUNT(*) FROM ContributorCategories';
    $r = $this->querySingleRow($q);
    return $this->offsetsFromCount(current($r));
  }
  public function page($tId, $study, $offset){
    //Setup:
    $ret = array();
    $description = TranslationProvider::getDescription('dt_contributor_categories_trans');
    //Page query:
    $o = ($offset == -1) ? '' : " LIMIT 30 OFFSET $offset";
    $q = "SELECT SortGroup, Headline FROM ContributorCategories$o";
    foreach($this->fetchRows($q) as $r){
      $q = $this->getTranslationQuery($r[0], $tId);
      $translation = $this->dbConnection->query($q)->fetch_row();
      array_push($ret, array(
        'Description' => $description
      , 'Original'    => $r[1]
      , 'Translation' => array(
          'TranslationId'       => $tId
        , 'Translation'         => $translation[0]
        , 'Payload'             => $r[0]
        , 'TranslationProvider' => $this->getName()
        )
      ));
    }
    return $ret;
  }
}
?>
