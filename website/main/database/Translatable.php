<?php
require_once 'DBEntry.php';
/**
  The Translatable class aids the translation of fields of DBEntries,
  in cases where the translation is done via TranslationProviders,
  and stored in the Page_DynamicTranslation table.
  To get this right, a certain Category and Field combination are required
  depending on the table and column that are translated.
*/
abstract class Translatable extends DBEntry{
  /**
    Fetches a Translation for a given combination of
    TranslationId, Category and Field.
  */
  public static function getTrans($tId, $category, $field){
    $q = Translatable::getTransQuery($tId, $category, $field);
    if($set = Config::getConnection()->query($q))
      return $set->fetch_row();
    return null;
  }
  /**
    Builds a query to fetch a Translation for a given combination
    of TranslationId, Category and Field.
  */
  public static function getTransQuery($tId, $category, $field){
    return "SELECT Trans FROM Page_DynamicTranslation "
         . "WHERE TranslationId = $tId "
         . "AND Category = '$category' "
         . "AND Field = '$field'";
  }
  /**
    Since most Categories in the Page_DynamicTranslation table share
    a common prefix, we have a method to return this prefix.
  */
  protected abstract static function getTranslationPrefix();
  /**
    Required option fields:
    tId, prefix, column, id, [study]
    This function may be overwritten by children,
    and thus behave differently!
  */
  public static function getTranslation($options){
    $study = (array_key_exists('study', $options)) ? $options['study']
           : RedirectingValueManager::getInstance()->gsm()->getStudy()->getId();
    $category = $options['prefix'].$options['column'];
    $field    = $options['study'].'-'.$options['id'];
    return Translatable::getTrans($options['tId'], $category, $field);
  }
  /**
    Basically the non-static version of getTranslation.
    Since it's not static, it may need fewer values in $options.
    Required option fields:
    tId, column
    Given options can overwrite study and id values.
    This implementation simply enriches options with values for prefix, study and id,
    before forwarding to getTranslation.
  */
  public function translate($options){
    $add = array(
      'study'  => $this->getValueManager()->getStudy()->getId()
    , 'id'     => $this->id
    , 'prefix' => $this->getTranslationPrefix()
    );
    return $this::getTranslation(array_merge($add, $options));
  }
}
?>
