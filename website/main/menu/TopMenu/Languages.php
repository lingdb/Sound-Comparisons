<?php
  $v = $valueManager;
  $t = $v->getTranslator();
?>
<ul id="topmenuSiteLang" class="nav">
  <li>
    <a class="dropdown-toggle topLink"
       data-toggle="dropdown">
       <i class='icon-dropdown-custom'></i>
       <? echo $t->showFlag(); ?>
       </a>
    <ul class="dropdown-menu">
      <? foreach($t->getOthers() as $ot){
           $href = $v->setTranslator($ot)->link();
           $cont = $ot->showFlag()
                 . $ot->showName();
      ?>
      <li><a <? echo $href; ?>><? echo $cont; ?></a></li>
      <? } ?>
    </ul>
  </li>
</ul>
