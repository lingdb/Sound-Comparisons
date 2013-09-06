<?
  $v   = $valueManager;
  $sid = $v->getStudy()->getId();
?>
<ul id="topmenuFamilies" class="nav">
  <li>
    <a class="dropdown-toggle topLink"
       data-toggle="dropdown"
       ><i class='icon-dropdown-custom'></i><? echo $v->getStudy()->getName($v); ?></a>
    <ul class="dropdown-menu"><?
      foreach($v->gsm()->getStudies() as $s){ 
        if($s->getId() === $sid)
          continue;
        $href = $v->gwo()->clear()->setRegions()->setLanguages()->setWords()->setStudy($s)->link();
      ?>
      <li>
        <a <? echo $href ?>><? echo $s->getName($v); ?></a>
      </li>
      <? } ?>
    </ul>
  </li>
</ul>
<? unset($sid); ?>
