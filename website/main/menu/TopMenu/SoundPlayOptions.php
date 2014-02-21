<?php
$t     = $valueManager->getTranslator();
$click = $t->st('topmenu_soundoptions_tooltip');
$hover = $t->st('topmenu_soundoptions_hover');
?>
<ul id='topmenuSoundOptions' class='nav nav-tabs'>
  <li>
    <img value="hover" class="btn btn-mini hide" title="<?php echo $click; ?>" src="img/hover.png">
    <img value="click" class="btn btn-mini" title="<?php echo $hover; ?>" src="img/click.png">
  </li>
</ul>
<?php
  unset($click);
  unset($hover);
?>
