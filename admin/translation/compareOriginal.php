<?php
if(array_key_exists('tables', $_GET)){
  require_once('query/translationTableProjection.php');
  $tables = explode(',', $_GET['tables']);
  $projection = TranslationTableProjection::projectTables($tables);
  if($projection instanceof Exception){
    Config::error($projection->getMessage(), true, true);
  }else{
    echo "KRAGEN!";//FIXME IMPLEMENT HERE!
  }
}else{
  echo '<p class="well">GET parameter "tables" missing!</p>';
}
