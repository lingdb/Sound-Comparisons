<?php
  $v   = $valueManager;
  $sid = $v->getStudy()->getId();
?>
<ul id="topmenuFamilies" class="nav">
  <li>
    <a class="dropdown-toggle topLink"
       data-toggle="dropdown"
       ><i class='icon-dropdown-custom'></i><?php echo $v->getStudy()->getName($v); ?></a>
    <ul class="dropdown-menu"><?php
      foreach(Study::getStudies() as $s){ 
        if($s->getId() === $sid)
          continue;
        $href = $v->gwo()->clear()->setRegions()->setLanguages()->setWords()->setStudy($s)->link();
      ?>
      <li>
        <a <?php echo $href ?>><?php echo $s->getName($v); ?></a>
      </li>
      <?php } ?>
    </ul>
  </li>
</ul>
<?php unset($sid); ?>
