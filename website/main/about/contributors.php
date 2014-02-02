<?php
  function renderContributor($c){
    //Fetching
    $initials = $c->getInitials();
    $name     = $c->getName();
    $email    = $c->getEmail();
    $website  = $c->getPersonalWebsite();
    $role     = $c->getFullRoleDescription();
    $img      = $c->getAvatar();
    //Prerender
    if($email !== ''){
      $email = "<dt>Email</dt><dd>$email</dd>";
    }
    if($website !== ''){
      $website = "<dt>Website</dt><dd><a href='$website' target='_blank'>$website</a></dd>";
    }
    if($role !== ''){
      $role = "<dt>About</dt><dd>$role</dd>";
    }
    //Finish:
    echo "<div id='$initials' class='well contributor'>"
         . "<h3>$name<a href='#$initials' class='anchor'> &para;</a>:</h3>"
         . "<div class='row-fluid'>"
           . "<div class='span1'><img src='$img' class='img-rounded avatar'></div>"
           . "<div class='offset1 span11'>"
             . "<dl class='dl-horizontal'>"
               . $email . $website . $role
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
<h2><?php echo $cTitle; ?>:</h2><hr>
<?php
  foreach(Contributor::citeContributors($v) as $c)
    renderContributor($c);
?>
