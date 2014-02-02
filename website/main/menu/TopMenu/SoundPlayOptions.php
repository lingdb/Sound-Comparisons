<?php
$t     = $valueManager->getTranslator();
$ttip  = $t->st('topmenu_soundoptions_tooltip');
$hover = $t->st('topmenu_soundoptions_hover');
$click = $t->st('topmenu_soundoptions_click');
?>
<ul id='topmenuSoundOptions' class='nav nav-tabs' title='<?php echo $ttip; ?>'>
  <li>
    <div class='btn-group'>
      <button type='button'
              value='hover'
              class='btn btn-mini btn-inverse'
              disabled="disabled"
              title='<?php echo $hover; ?>'>
        <img src='img/hover.png'>
      </button>
      <button type='button'
              value='click'
              class='btn btn-mini'
              title='<?php echo $click; ?>'>
        Click
      </button>
    </div>
  </li>
</ul>
<?php
  unset($ttip);
  unset($hover);
  unset($click);
?>
