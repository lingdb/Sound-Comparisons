<?php
$v     = $valueManager;
$t     = $v->getTranslator();
$about = array(
  'who_href'      => $v->gpv()->setView('WhoAreWeView')->link()
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
       data-toggle="dropdown">
      <img src="img/info.png">
    </a>
    <ul class="dropdown-menu">
      <li><a <?php echo $about['who_href']; ?>><?php echo $about['who']; ?></a></li>
      <li><a href="<?php echo $about['further_href'];  ?>"><?php echo $about['further'];  ?></a></li>
      <li><a href="<?php echo $about['research_href']; ?>"><?php echo $about['research']; ?></a></li>
      <li><a href="<?php echo $about['contact_href'];  ?>"><?php echo $about['contact'];  ?></a></li>
    </ul>
  </li>
</ul>
<?php unset($about); ?>
