<?php
$v = $valueManager;
$t = $v->getTranslator();
$dwnload = $t->st('topmenu_download_title');
$csvLink = $v->link('export/csv');
$csvCont = $t->st('topmenu_download_csv');
$sndLink = $v->link('export/soundfiles');
$sndCont = $t->st('topmenu_download_zip');
?>
<ul id="topmenuDownloads" class="nav">
  <li>
    <a class="dropdown-toggle topLink"
       data-toggle="dropdown"
       title="<? echo $dwnload; ?>"
       ><i class='icon-dropdown-custom'></i><i class="icon-download-alt"></i></a>
    <ul class="dropdown-menu">
      <li>
        <a <? echo $csvLink; ?>><? echo $csvCont; ?></a>
      </li>
      <li>
        <a target="_blank"
           <? echo $sndLink; ?>><? echo $sndCont; ?></a>
      </li>
    </ul>
  </li>
</ul>
<?
  unset($dwnload);
  unset($csvLink);
  unset($csvCont);
  unset($sndLink);
  unset($sndCont);
?>
