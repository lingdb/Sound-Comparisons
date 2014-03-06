<?php
$v = $valueManager;
$t = $v->getTranslator();
$csvLink = $v->link('export/csv');
$csvCont = $t->st('topmenu_download_csv');
$sndLink = $v->link('export/soundfiles');
$sndCont = $t->st('topmenu_download_zip');
?>
<span id="topmenuDownloads" class="nav input-append">
  <a <?php echo $csvLink; ?>class="btn btn-mini" title="<?php echo $csvCont; ?>">
    <i class="icon-download-alt"></i>
    <img src="img/DownloadTranscriptions.png" style="height: 18px;">
  </a>
  <a <?php echo $sndLink; ?> target="_blank" class="btn btn-mini" title="<?php echo $sndCont; ?>">
    <i class="icon-download-alt"></i>
    <i class="icon-volume-up"></i>
  </a>
</span>
<?php
  unset($dwnload);
  unset($csvLink);
  unset($csvCont);
  unset($sndLink);
  unset($sndCont);
?>
