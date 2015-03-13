<?php
/**
  Integrity assumes that Config and DataProvider are available.
  It checks the database for expected constraints.
  Build to deal with https://github.com/sndcomp/website/issues/66
*/
class Integrity {
  /**
    returns the combined result of check{Pk,Fk,NotValues}.
  */
  public static function checkIntegrity(){
    return array(
      'pk' => static::checkPk()
    , 'fk' => static::checkFk()
    , 'notValues' => static::checkNotValues()
    );
  }
  /**
    @return [TableName => [JsonRow]]
    Checks all pk entries of Integrity::$constraints.
    TableName is expected to be a complete name, not a prefix.
  */
  public static function checkPk(){
    //FIXME IMPLEMENT
  }
  /**
    @return [TableName => [JsonRow]]
    Checks all fk entries of Integrity::$constraints.
    TableName is expected to be a complete name, not a prefix.
  */
  public static function checkFk(){
    //FIXME IMPLEMENT
  }
  /**
    @return [TableName => [JsonRow]]
    Checks all notValues entries of Integrity::$constraints.
    TableName is expected to be a complete name, not a prefix.
  */
  public static function checkNotValues(){
    //FIXME IMPLEMENT
  }
  /**
    Array tree that describes different constraints on database tables.
    [TableName => [
      perStudy => Boolean // If existent and true, TableName is only a TablePrefix that needs a Study as Suffix.
    , pk => [ColNames] // If existent, the concat of cols is supposed to be a primary key for the table.
    , fk => [
        table => TableName // Entry of the ArrayTree.
      , key => [ColNames] // concat of cols that are supposed to be local representation of foreign key.
      , ref => [ColNames] // concat of cols that are supposed to be referenced representation of foreign key.
      ]
    , notValues => [ // The or of the below combinations is not allowed.
        [ColName => Value] // The and of the combination of ColName+Value is not allowed.
      ]
    ]]
  */
  public static $constraints = array(
    'FlagTooltip' => array(
      'pk' => array('Flag')
    )
  , 'LanguageStatusTypes' => array(
      'pk' => array('LanguageStatusType')
    )
  , 'Studies' => array(
      'pk' => array('StudyIx', 'FamilyIx', 'SubFamilyIx')
    )
  , 'Families' => array(
      'pk' => array('StudyIx', 'FamilyIx', 'FamilyNm')
    )
  , 'Edit_Users' => array(/* Nothing */)
  , 'Edit_Imports' => array(
      'fk' => array(
        array('table' => 'Edit_Users', 'key' => array('Who'), 'ref' => array('UserId'))
      )
    )
  , 'Export_Soundfiles' => array(
      'pk' => array('FileName') 
    )
  , 'Page_Translations' => array(/* Nothing */)
  , 'Page_ShortLinks' => array(
      'pk' => array('Name')
    )
  , 'Page_StaticDescription' => array(
      'pk' => array('Req')
    )
  , 'Page_StaticTranslation' => array(
      'fk' => array(
        array('table' => 'Page_Translations', 'key' => array('TranslationId'), 'ref' => array('TranslationId'))
      , array('table' => 'Page_StaticDescription', 'key' => array('Req'), 'ref' => array('Req'))
      )
    )
  , 'Page_DynamicTranslation' => array(
      'pk' => array('TranslationId', 'Category','Field') 
    , 'fk' => array(
        array('table' => 'Page_Translations', 'key' => array('TranslationId'), 'ref' => array('TranslationId'))
      )
    )
  , 'Default_Words' => array(
      'fk' => array(
        array('table' => 'Words_', 'key' => array('IxElicitation', 'IxMorphologicalInstance', 'StudyIx', 'FamilyIx'), 'ref' => array('IxElicitation', 'IxMorphologicalInstance', 'StudyIx', 'FamilyIx'))
      , array('table' => 'Studies', 'key' => array('StudyIx', 'FamilyIx'), 'ref' => array('StudyIx', 'FamilyIx'))
      )
    )
  , 'Default_Languages' => array(
      'pk' => array('LanguageIx', 'StudyIx', 'FamilyIx')
    , 'fk' => array(
        array('table' => 'Languages_', 'key' => array('LanguageIx'), 'ref' => array('LanguageIx'))
      , array('table' => 'Studies', 'key' => array('StudyIx', 'FamilyIx'), 'ref' => array('StudyIx', 'FamilyIx'))
      )
    )
  , 'Default_Multiple_Words' => array(
      'fk' => array(
        array('table' => 'Words_', 'key' => array('IxElicitation', 'IxMorphologicalInstance', 'StudyIx', 'FamilyIx'), 'ref' => array('IxElicitation', 'IxMorphologicalInstance', 'StudyIx', 'FamilyIx')
      , array('table' => 'Studies', 'key' => array('StudyIx', 'FamilyIx'), 'ref' => array('StudyIx', 'FamilyIx')
      )
    )
  , 'Default_Multiple_Languages' => array(
      'pk' => array('LanguageIx', 'StudyIx', 'FamilyIx')
    , 'fk' => array(
        array('table' => 'Languages_', 'key' => array('LanguageIx'), 'ref' => array('LanguageIx'))
      , array('table' => 'Studies', 'key' => array('StudyIx', 'FamilyIx'), 'ref' => array('StudyIx', 'FamilyIx'))
      )
    )
  , 'Default_Languages_Exclude_Map' => array(
      'pk' => array('LanguageIx', 'StudyIx', 'FamilyIx')
    )
  , 'MeaningGroups' => array(
      'pk' => array('MeaningGroupIx')
    )
  , 'MeaningGroupMembers' => array(
      'pk' => array('MeaningGroupIx', 'StudyIx', 'FamilyIx', 'IxElicitation', 'IxMorphologicalInstance')
    , 'fk' => array(
        array('table' => 'Studies', 'key' => array('StudyIx', 'FamilyIx'), 'ref' => array('StudyIx', 'FamilyIx'))
      , array('table' => 'MeaningGroups', 'key' => array('MeaningGroupIx'), 'ref' => array('MeaningGroupIx'))
      )
    )
  , 'WikipediaLinks' => array(
      'pk' => array('BrowserMatch', 'ISOCode', 'WikipediaLinkPart')
    )
  , 'Contributors' => array(
      'pk' => array('ContributorIx')
    )
  , 'TranscrSuperscriptInfo' => array(
      'pk' => array('Ix')
    )
  , 'TranscrSuperscriptLenderLgs' => array(
      'pk' => array('IsoCode')
    )
  , 'Regions_' => array(
      'pk' => array('StudyIx', 'FamilyIx', 'SubFamilyIx', 'RegionGpIx')
    , 'fk' => array(
        array('table' => 'Studies', 'key' => array('StudyIx', 'FamilyIx', 'SubFamilyIx'), 'ref' => array('StudyIx', 'FamilyIx', 'SubFamilyIx'))
      )
    , 'perStudy' => true
    )
  , 'Languages_' => array(
      'pk' => array('LanguageIx')
    , 'fk' => array(
        array('table' => 'LanguageStatusTypes', 'key' => array('LanguageStatusType'), 'ref' => array('LanguageStatusType'))
      , array('table' => 'FlagTooltip', 'key' => array('Flag'), 'ref' => array('Flag'))
      , array('table' => 'Contributors', 'key' => array('ContributorSpokenBy'), 'ref' => array('ContributorIx'))
      , array('table' => 'Contributors', 'key' => array('ContributorRecordedBy1'), 'ref' => array('ContributorIx'))
      , array('table' => 'Contributors', 'key' => array('ContributorRecordedBy2'), 'ref' => array('ContributorIx'))
      , array('table' => 'Contributors', 'key' => array('ContributorSoundEditingBy'), 'ref' => array('ContributorIx'))
      , array('table' => 'Contributors', 'key' => array('ContributorPhoneticTranscriptionBy'), 'ref' => array('ContributorIx'))
      , array('table' => 'Contributors', 'key' => array('ContributorReconstructionBy'), 'ref' => array('ContributorIx'))
      , array('table' => 'Contributors', 'key' => array('ContributorCitationAuthor1'), 'ref' => array('ContributorIx'))
      , array('table' => 'Contributors', 'key' => array('ContributorCitationAuthor2'), 'ref' => array('ContributorIx'))
      , array('table' => 'RegionLanguages_', 'key' => array('LanguageIx'), 'ref' => array('LanguageIx'))
      )
    , 'perStudy' => true
    , 'notValues' => array(
        array('Latitude' => 0, 'Longtitude' => 0)
      )
    )
  , 'RegionLanguages_' => array(
      'pk' => array('StudyIx', 'FamilyIx', 'SubFamilyIx', 'RegionGpIx', 'LanguageIx')
    , 'fk' => array(
        array('table' => 'Regions_', 'key' => array('StudyIx', 'FamilyIx', 'SubFamilyIx', 'RegionGpIx'), 'ref' => array('StudyIx', 'FamilyIx', 'SubFamilyIx', 'RegionGpIx'))
      , array('table' => 'Languages_', 'key' => array('LanguageIx'), 'ref' => array('LanguageIx'))
      )
    , 'perStudy' => true
    )
  , 'Words_' => array(
      'pk' => array('IxElicitation', 'IxMorphologicalInstance')
    , 'perStudy' => true
    )
  , 'Transcriptions_' => array(
      'pk' => array('StudyIx', 'FamilyIx', 'IxElicitation', 'IxMorphologicalInstance', 'AlternativePhoneticRealisationIx', 'AlternativeLexemIx', 'LanguageIx')
    , 'fk' => array(
        array('table' => 'Studies', 'key' => array('StudyIx', 'FamilyIx'), 'ref' => array('StudyIx', 'FamilyIx'))
      , array('table' => 'Words_', 'key' => array('IxElicitation', 'IxMorphologicalInstance'), 'ref' => array('IxElicitation', 'IxMorphologicalInstance'))
      , array('table' => 'Languages_', 'key' => array('LanguageIx'), 'ref' => array('LanguageIx'))
      )
    , 'perStudy' => true
    )
  );
}
?>
