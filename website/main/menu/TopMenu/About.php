<?php
$v     = $valueManager;
$t     = $v->getTranslator();
$about = array(
  'title'         => $t->st('topmenu_about')
, 'who_href'      => $v->gpv()->setView('WhoAreWeView')->link()
, 'further_href'  => $t->st('topmenu_about_furtherinfo_href')
, 'research_href' => $t->st('topmenu_about_research_href')
, 'contact_href'  => $t->st('topmenu_about_contact_href')
, 'who'           => $t->st('topmenu_about_whoarewe')
, 'further'       => $t->st('topmenu_about_furtherinfo')
, 'research'      => $t->st('topmenu_about_research')
, 'contact'       => $t->st('topmenu_about_contact')
);
?>
<ul id="topmenuAbout" class="nav">
  <li>
    <a class="dropdown-toggle topLink"
       data-toggle="dropdown"
       title="<? echo $about['title']; ?>">
      <i class='icon-dropdown-custom'></i>
      <img src="img/info.png">
    </a>
    <ul class="dropdown-menu">
      <li><a <? echo $about['who_href']; ?>><? echo $about['who']; ?></a></li>
      <li><a href="<? echo $about['further_href'];  ?>"><? echo $about['further'];  ?></a></li>
      <li><a href="<? echo $about['research_href']; ?>"><? echo $about['research']; ?></a></li>
      <li><a href="<? echo $about['contact_href'];  ?>"><? echo $about['contact'];  ?></a></li>
    </ul>
  </li>
</ul>
<? unset($about); ?>
