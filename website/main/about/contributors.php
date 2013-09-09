<?php
  function renderContributor($c){
    $initials = $c->getInitials();
    $name     = $c->getName();
    $email    = $c->getEmail();
    $website  = $c->getPersonalWebsite();
    $role     = $c->getFullRoleDescription();
    $img      = $c->getAvatar();
    echo "<div id='$initials' class='well contributor'>"
         . "<h3>$name<a href='#$initials' class='anchor'> &para;</a>:</h3>"
         . "<img src='$img' class='img-rounded pull-right avatar'>"
         . "<dl class='dl-horizontal'>"
           . "<dt>Email</dt>"
           . "<dd>$email</dd>"
           . "<dt>Website</dt>"
           . "<dd><a href='$website' target='_blank'>$website</a></dd>"
           . "<dt>About</dt>"
           . "<dd>$role;</dd>"
         . "</dl>"
       . "</div>";
  }
  $v = $valueManager;
  $t = $v->getTranslator();
  foreach(Contributor::mainContributors($v) as $c)
    renderContributor($c);
  $cTitle = $t->st('whoarewe_citecontributors_title');
?>
<h2><? echo $cTitle; ?>:</h2><hr>
<?
  foreach(Contributor::citeContributors($v) as $c)
    renderContributor($c);
?>
