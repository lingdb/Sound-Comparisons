<?php
  foreach(Contributor::contributors($valueManager) as $contributor){
    $initials = $contributor->getInitials();
    $name     = $contributor->getName();
    $email    = $contributor->getEmail();
    $website  = $contributor->getPersonalWebsite();
    $role     = $contributor->getFullRoleDescription();
?>
<div id="<? echo $initials; ?>" class="well contributor">
  <h3><? echo $name; ?>:</h3>
  <dl class="dl-horizontal">
    <dt>Email</dt>
    <dd><? echo $email; ?></dd>
    <dt>Website</dt>
    <dd><a href="<? echo $website; ?>" target="_blank"><? echo $website; ?></a></dd>
    <dt>About</dt>
    <dd><? echo $role; ?></dd>
  </dl>
</div>
<?
  }
?>
