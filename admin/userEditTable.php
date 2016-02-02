<?php
/*
  The userEditTable is used by overview.php.
  It is also an external file so that it can be called from js directly.
*/
require_once('common.php');
if(!session_mayEdit())
  die('Sorry, I cannot show you more here.');
$q = "SELECT UserId, Login, AccessTranslate, AccessEdit FROM Edit_Users";
$set = $dbConnection->query($q);
while($r = $set->fetch_row()){
  $UserId          = $r[0];
  $Login           = $r[1];
  $AccessTranslate = $r[2];
  $AccessEdit      = $r[3];
  if($AccessTranslate == '1')
    $AccessTranslate = ' checked="checked"';
  else
    $AccessTranslate = '';
  if($AccessEdit == '1')
    $AccessEdit = ' checked="checked"';
  else
    $AccessEdit = '';
  echo "<tr class='editTableEntry'><td>$UserId</td>"
     . "<td><input name='login' type='text' value='$Login'/></td>"
     . "<td><input name='password' type='text' placeholder='New Password'/></td>"
     . "<td><input name='mayTranslate' type='checkbox'$AccessTranslate/></td>"
     . "<td><input name='mayEdit' type='checkbox'$AccessEdit/></td>"
     . "<td><button class='btn update'>Update</button>"
     . "<button class='btn btn-danger delete'>Delete</button></td></tr>";
}
