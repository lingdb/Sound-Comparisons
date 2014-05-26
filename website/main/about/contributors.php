<?php
  function sumContributor($c){
    return array(
      'initials' => $c->getInitials()
    , 'name'     => $c->getName()
    , 'img'      => $c->getAvatar()
    , 'email'    => $c->getEmail()
    , 'website'  => $c->getPersonalWebsite()
    , 'role'     => $c->getFullRoleDescription()
    );
  }
  $v = $valueManager;
  $t = $v->getTranslator();
  $conts = array(
    'citedTitle' => $t->st('whoarewe_citecontributors_title')
  , 'mainContributors'  => __(Contributor::mainContributors($v))->map('sumContributor')
  , 'citedContributors' => __(Contributor::citeContributors($v))->map('sumContributor')
  );
?>
