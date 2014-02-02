<?php
  $v = $valueManager;
  $t = $v->getTranslator();
?>
<ul id="topmenuSiteLang" class="nav">
  <li>
    <a class="dropdown-toggle topLink"
       data-toggle="dropdown">
       <i class='icon-dropdown-custom'></i>
       <?php echo $t->showFlag(); ?>
       </a>
    <ul class="dropdown-menu">
      <?php foreach($t->getOthers() as $ot){
           $href = $v->setTranslator($ot)->link();
           $cont = $ot->showFlag()
                 . $ot->showName();
      ?>
      <li><a <?php echo $href; ?>><?php echo $cont; ?></a></li>
      <?php } ?>
    </ul>
  </li>
</ul>
