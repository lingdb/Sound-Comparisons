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
      if($r[0] === '' || $r[1] === '')
        return '';
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
      return preg_replace('/\\n/','<br>',$frd[0]);
    }
    /***/
    public function getColumnDescription(){
      if($this->column === null) return null;
      $key = 'description_contributor_'.$this->column;
      return $this->getValueManager()->getTranslator()->st($key);
    }
    /***/
    public function getAvatar(){
      $prefix = 'img/contributors/';
      $inits  = $this->getInitials();
      foreach(array('.jpg','.png','.gif') as $ext){
        $file = $prefix.$inits.$ext;
        if(file_exists($file))
          return $file;
      }
      return $prefix.'dummy.png';
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
      $dbConnection = Config::getConnection();
      $v = $language->getValueManager();
      $sid = $language->getValueManager()->getStudy()->getId();
      $id = $language->getId();
      $q = "SELECT ContributorSpokenBy, ContributorRecordedBy1, ContributorRecordedBy2"
         . ", ContributorPhoneticTranscriptionBy, ContributorReconstructionBy"
         . ", ContributorCitationAuthor1, Citation1Year, Citation1Pages"
         . ", ContributorCitationAuthor2, Citation2Year, Citation2Pages"
         . " FROM Languages_$sid WHERE LanguageIx = $id";
      $ret = array();
      $r = $dbConnection->query($q)->fetch_row();
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
    private static function contributors($v, $q){
      $set = Config::getConnection()->query($q);
      $ret = array();
      while($r = $set->fetch_row())
        array_push($ret, new ContributorFromId($v, $r[0]));
      return $ret;
    }
    /***/
    public static function mainContributors($v){
      $q = 'SELECT ContributorIx FROM Contributors WHERE SortIxForAboutPage != 0 ORDER BY SortIxForAboutPage ASC';
      return Contributor::contributors($v, $q);
    }
    /***/
    public static function citeContributors($v){
      $q = 'SELECT ContributorIx FROM Contributors WHERE SortIxForAboutPage = 0 ORDER BY Surnames ASC';
      return Contributor::contributors($v, $q);
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
