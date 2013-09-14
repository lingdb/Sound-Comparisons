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
         . "<div class='row-fluid'>"
           . "<div class='span1'><img src='$img' class='img-rounded avatar'></div>"
           . "<div class='offset1 span11'>"
             . "<dl class='dl-horizontal'>"
               . "<dt>Email</dt>"
               . "<dd>$email</dd>"
               . "<dt>Website</dt>"
               . "<dd><a href='$website' target='_blank'>$website</a></dd>"
               . "<dt>About</dt>"
               . "<dd>$role;</dd>"
             . "</dl>"
           . "</div>"
         . "</div>"
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
