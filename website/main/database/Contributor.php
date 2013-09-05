<?php
  require_once 'DBEntry.php';
  /***/
  class Contributor extends DBEntry{
    /***/
    protected function buildSelectQuery($fs){
      $id  = $this->id;
      return "SELECT $fs FROM Contributors WHERE ContributorIx = $id";
    }
    /***/
    public $column = null;
    public $year   = null;
    public $pages  = null;
    /***/
    public function getForename(){
      $fn = $this->fetchFields('Forenames');
      return $fn[0];
    }
    /***/
    public function getSurname(){
      $sn = $this->fetchFields('Surnames');
      return $sn[0];
    }
    /***/
    public function getName(){
      $name = $this->getForename().' '.$this->getSurname();
      return $name;
    }
    /***/
    public function getInitials(){
      $i = $this->fetchFields('Initials');
      return $i[0];
    }
    /***/
    public function getEmail(){
      $r = $this->fetchFields('EmailUpToAt','EmailAfterAt');
      return $r[0].' [ at ] '.$r[1];
    }
    /***/
    public function getPersonalWebsite(){
      $pw = $this->fetchFields('PersonalWebsite');
      return $pw[0];
    }
    /***/
    public function getFullRoleDescription(){
      $frd = $this->fetchFields('FullRoleDescription');
      return $frd[0];
    }
    /***/
    public function getColumnDescription(){
      if($this->column === null) return null;
      $key = 'description_contributor_'.$this->column;
      return $this->getValueManager()->getTranslator()->st($key);
    }
    /***/
    protected static function mkContributor($v, $id, $column, $year = null, $pages = null){
      $c = new ContributorFromId($v, $id);
      $c->column = $column;
      $c->year   = $year;
      $c->pages  = $pages;
      return $c;
    }
    /***/
    public static function forLanguage($language){
      $dbConnection = $language->getConnection();
      $v  = $language->getValueManager();
      $id = $language->getId();
      $q  = "SELECT ContributorSpokenBy, ContributorRecordedBy1, ContributorRecordedBy2"
          . ", ContributorPhoneticTranscriptionBy, ContributorReconstructionBy"
          . ", ContributorCitationAuthor1, Citation1Year, Citation1Pages"
          . ", ContributorCitationAuthor2, Citation2Year, Citation2Pages"
          . " FROM Languages WHERE LanguageIx = $id";
      $ret = array();
      $r = mysql_fetch_row(mysql_query($q, $dbConnection));
      if($i = $r[0]) array_push($ret, Contributor::mkContributor($v, $i, 'ContributorSpokenBy'));
      if($i = $r[1]) array_push($ret, Contributor::mkContributor($v, $i, 'ContributorRecordedBy1'));
      if($i = $r[2]) array_push($ret, Contributor::mkContributor($v, $i, 'ContributorRecordedBy2'));
      if($i = $r[3]) array_push($ret, Contributor::mkContributor($v, $i, 'ContributorPhoneticTranscriptionBy'));
      if($i = $r[4]) array_push($ret, Contributor::mkContributor($v, $i, 'ContributorReconstructionBy'));
      if($i = $r[5]) array_push($ret, Contributor::mkContributor($v, $i, 'ContributorCitationAuthor1', $r[6], $r[7]));
      if($i = $r[8]) array_push($ret, Contributor::mkContributor($v, $i, 'ContributorCitationAuthor2', $r[9], $r[10]));
      return $ret;
    }
    /***/
    public static function contributors($v){
      $dbConnection = $v->getConnection();
      $q = "SELECT ContributorIx FROM Contributors ORDER BY SortIxForAboutPage ASC";
      $set = mysql_query($q, $dbConnection);
      $ret = array();
      while($r = mysql_fetch_row($set))
        array_push($ret, new ContributorFromId($v, $r[0]));
      return $ret;
    }
  }
  /***/
  class ContributorFromId extends Contributor{
    public function __construct($v, $id){
      $this->setup($v);
      $this->id = $id;
    }
  }
?>
