<?php
require_once('translationTableDescription.php');
/**
  The CsvTranslationImporter handles the following task:
  - For a given set of Tables find all entries that can be translated.
  - Compare these entries against a translation.
  - List all of these entries that are different from the selected translation.
  - Deliver enough information to update the translations in question,
    so that a different part of the site may use the CsvTranslationImporter
    to selectivly overwrite current translation.
  This class was conceived for issues #161, #189.
*/
class CsvTranslationImporter {
}
