<?php
/**
  We can use this class for two things:
  1.: Abstract away some of the overhead introduced by the current layout of DynamicTranslationProviders
  2.: Implement a translation import from CSV as described in #189
*/
class TranslationTableDescription {
  /** @return self::$tableDescriptions */
  public static function getTableDescriptions(){
    return self::$tableDescriptions;
  }
  /**
    Structure of $tableDescriptions:
    [sNameRegex => [
        columns => [
          columnName => String
        , fieldSelect => String
        , description => String
        , category => String
        ]
      , dependsOnStudy => Boolean
      ]
    ]
    Field explanations:
    - sNameRegex is short for 'source name regex',
      and is a regex intended to match all tables that can serve
      as source tables for translation.
    - columns contains descriptions for each column of a source table that can be translated.
      Each columns entry has the following fields:
      - The columName that matches the original column name of the source table it belongs to.
      - The fieldSelect describes a part that can be put after the SELECT
        in a SQL query to fetch the Field value to target in the
        Page_DynamicTranslation table.
      - The description that is a Req entry of the Page_StaticDescription table.
      - The category field describes the Category entry used in the Page_DynamicTranslation table.
    - dependsOnStudy shall be true iff the source table name contains the study name.
      If this is the case, the sNameRegex shall capture the study name.
      If dependsOnStudy is true, the fieldSelect must be put into implode('-', array($studyName, $fieldSelect)),
      to obtain the final field name.
    FIXME: Not sure, what to do with these providers:
    - StaticTranslationProvider.php
    - StudyTitleTranslationProvider
      , '/^Page_DynamicTranslation$/' => array(
          'columns' => array(
            array(
              'columnName' => 'Trans'
            , 'fieldSelect' => '\'\''
            , 'description' => 'dt_studyTitle_trans'
            , 'category' => 'StudyTitleTranslationProvider'
            )
          )
        , 'dependsOnStudy' => false
        )
  */
  private static $tableDescriptions = array(
    '/^ContributorCategories$/' => array(
      'columns' => array(
        array(
          'columnName' => 'Headline'
        , 'fieldSelect' => 'SortGroup'
        , 'description' => 'dt_contributor_categories_trans'
        , 'category' => 'ContributorCategoriesTranslationProvider-Headline'
        )
      , array(
          'columnName' => 'Abbr'
        , 'fieldSelect' => 'SortGroup'
        , 'description' => 'dt_contributor_categories_trans_abbr'
        , 'category' => 'ContributorCategoriesTranslationProvider-Abbr'
        )
      )
    , 'dependsOnStudy' => false
    )
  , '/^Families$/' => array(
      'columns' => array(
        array(
          'columnName' => 'FamilyNm'
        , 'fieldSelect' => 'CONCAT(StudyIx, FamilyIx)'
        , 'description' => 'dt_families_trans'
        , 'category' => 'FamilyTranslationProvider'
        )
      )
    , 'dependsOnStudy' => false
    )
  , '/^LanguageStatusTypes$/' => array(
      'columns' => array(
        array(
          'columnName' => 'Status'
        , 'fieldSelect' => 'LanguageStatusType'
        , 'description' => 'dt_languageStatusTypes_status'
        , 'category' => 'LanguageStatusTypesTranslationProvider-LanguageStatusTypes-Trans_Status'
        )
      , array(
          'columnName' => 'Description'
        , 'fieldSelect' => 'LanguageStatusType'
        , 'description' => 'dt_languageStatusTypes_description'
        , 'category' => 'LanguageStatusTypesTranslationProvider-LanguageStatusTypes-Trans_Description'
        )
      , array(
          'columnName' => 'StatusTooltip'
        , 'fieldSelect' => 'LanguageStatusType'
        , 'description' => 'dt_languageStatusTypes_statusTooltip'
        , 'category' => 'LanguageStatusTypesTranslationProvider-LanguageStatusTypes-Trans_StatusTooltip'
        )
      )
    , 'dependsOnStudy' => false
    )
  , '/^Languages_(.+)$/' => array(
      'columns' => array(
        array(
          'columnName' => 'ShortName'
        , 'fieldSelect' => 'LanguageIx'
        , 'description' => 'dt_languages_shortName'
        , 'category' => 'LanguagesTranslationProvider-Languages_-Trans_ShortName'
        )
      , array(
          'columnName' => 'SpecificLanguageVarietyName'
        , 'fieldSelect' => 'LanguageIx'
        , 'description' => 'dt_languages_specificLanguageVarietyName'
        , 'category' => 'LanguagesTranslationProvider-Languages_-Trans_SpecificLanguageVarietyName'
        )
      , array(
          'columnName' => 'SpellingRfcLangName'
        , 'fieldSelect' => 'LanguageIx'
        , 'description' => 'dt_languages_spellingRfcLangName'
        , 'category' => 'LanguagesTranslationProvider-Languages_-Trans_SpellingRfcLangName'
        )
      , array(
          'columnName' => 'WebsiteSubgroupName'
        , 'fieldSelect' => 'LanguageIx'
        , 'description' => 'dt_languages_websiteSubgroupName'
        , 'category' => 'LanguagesTranslationProvider-Languages_-Trans_WebsiteSubgroupName'
        )
      )
    , 'dependsOnStudy' => true
    )
  , '/^MeaningGroups$/' => array(
      'columns' => array(
        array(
          'columnName' => 'Name'
        , 'fieldSelect' => 'MeaningGroupIx'
        , 'description' => 'dt_meaningGroups_trans'
        , 'category' => 'MeaningGroupsTranslationProvider'
        )
      )
    , 'dependsOnStudy' => false
    )
  , '/^RegionLanguages_(.+)$/' => array(
      'columns' => array(
        array(
          'columnName' => 'RegionGpMemberLgNameShortInThisSubFamilyWebsite'
        , 'fieldSelect' => 'LanguageIx'
        , 'description' => 'dt_regionLanguages_RegionGpMemberLgNameShortInThisSubFamilyWebsite'
        , 'category' => 'RegionLanguagesTranslationProvider-RegionLanguages_-Trans_RegionGpMemberLgNameShortInThisSubFamilyWebsite'
        )
      , array(
          'columnName' => 'RegionGpMemberLgNameLongInThisSubFamilyWebsite'
        , 'fieldSelect' => 'LanguageIx'
        , 'description' => 'dt_regionLanguages_RegionGpMemberLgNameLongInThisSubFamilyWebsite'
        , 'category' => 'RegionLanguagesTranslationProvider-RegionLanguages_-Trans_RegionGpMemberLgNameLongInThisSubFamilyWebsite'
        )
      )
    , 'dependsOnStudy' => true
    )
  , '/^Regions_(.+)$/' => array(
      'columns' => array(
        array(
          'columnName' => 'RegionGpNameShort'
        , 'fieldSelect' => 'CONCAT(StudyIx, FamilyIx, SubFamilyIx, RegionGpIx)'
        , 'description' => 'dt_regions_regionGpNameShort'
        , 'category' => 'RegionsTranslationProvider-Regions_-Trans_RegionGpNameShort'
        )
      , array(
          'columnName' => 'RegionGpNameLong'
        , 'fieldSelect' => 'CONCAT(StudyIx, FamilyIx, SubFamilyIx, RegionGpIx)'
        , 'description' => 'dt_regions_regionGpNameLong'
        , 'category' => 'RegionsTranslationProvider-Regions_-Trans_RegionGpNameLong'
        )
      )
    , 'dependsOnStudy' => true
    )
  , '/^Studies$/' => array(
      'columns' => array(
        array(
          'columnName' => 'Name'
        , 'fieldSelect' => 'Name'
        , 'description' => 'dt_studies_trans'
        , 'category' => 'StudyTranslationProvider'
        )
      )
    , 'dependsOnStudy' => false
    )
  , '/^TranscrSuperscriptInfo$/' => array(
      'columns' => array(
        array(
          'columnName' => 'Abbreviation'
        , 'fieldSelect' => 'Ix'
        , 'description' => 'dt_superscriptInfo_abbreviation'
        , 'category' => 'TranscrSuperscriptInfoTranslationProvider-TranscrSuperscriptInfo-Trans_Abbreviation'
        )
      , array(
          'columnName' => 'HoverText'
        , 'fieldSelect' => 'Ix'
        , 'description' => 'dt_superscriptInfo_hoverText'
        , 'category' => 'TranscrSuperscriptInfoTranslationProvider-TranscrSuperscriptInfo-Trans_HoverText'
        )
      )
    , 'dependsOnStudy' => false
    )
  , '/^TranscrSuperscriptLenderLgs$/' => array(
      'columns' => array(
        array(
          'columnName' => 'Abbreviation'
        , 'fieldSelect' => 'IsoCode'
        , 'description' => 'dt_superscriptLenderLgs_abbreviation'
        , 'category' => 'TranscrSuperscriptLenderLgsTranslationProvider-TranscrSuperscriptLenderLgs-Trans_Abbreviation'
        )
      , array(
          'columnName' => 'FullNameForHoverText'
        , 'fieldSelect' => 'IsoCode'
        , 'description' => 'dt_superscriptLenderLgs_fullNameForHoverText'
        , 'category' => 'TranscrSuperscriptLenderLgsTranslationProvider-TranscrSuperscriptLenderLgs-Trans_FullNameForHoverText'
        )
      )
    , 'dependsOnStudy' => false
    )
  , '/^Words_(.+)$/' => array(
      'columns' => array(
        array(
          'columnName' => 'FullRfcModernLg01'
        , 'fieldSelect' => 'CONCAT(IxElicitation, IxMorphologicalInstance)'
        , 'description' => 'dt_words_fullRfcModernLg01'
        , 'category' => 'WordsTranslationProvider-Words_-Trans_FullRfcModernLg01'
        )
      , array(
          'columnName' => 'LongerRfcModernLg01'
        , 'fieldSelect' => 'CONCAT(IxElicitation, IxMorphologicalInstance)'
        , 'description' => 'dt_words_longerRfcModernLg01'
        , 'category' => 'WordsTranslationProvider-Words_-Trans_LongerRfcModernLg01'
        )
      )
    , 'dependsOnStudy' => true
    )
  );
}
