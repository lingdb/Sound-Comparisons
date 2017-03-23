<?php
/*
  The userEditTable is used by overview.php.
  It is also an external file so that it can be called from js directly.
*/
require_once('common.php');
if(!session_isSuperuser())
  die('Sorry, I cannot show you more here.');
$q = "SELECT UserId, Login, AccessTranslate, AccessEdit, AccessUpload, AccessSuperuser FROM Edit_Users";
$set = $dbConnection->query($q);
while($r = $set->fetch_row()){
  $UserId          = $r[0];
  $Login           = $r[1];
  $AccessTranslate = $r[2];
  $AccessEdit      = $r[3];
  $AccessUpload    = $r[4];
  $AccessSuperuser = $r[5];
  if($AccessTranslate == '1')
    $AccessTranslate = ' checked="checked"';
  else
    $AccessTranslate = '';
  if($AccessEdit == '1')
    $AccessEdit = ' checked="checked"';
  else
    $AccessEdit = '';
  if($AccessUpload == '1')
    $AccessUpload = ' checked="checked"';
  else
    $AccessUpload = '';
  if($AccessSuperuser == '1')
    $AccessSuperuser = ' checked="checked"';
  else
    $AccessSuperuser = '';
  echo "<tr class='editTableEntry'><td>$UserId</td>"
     . "<td><input name='login' type='text' value='$Login'/></td>"
     . "<td><input name='password' type='text' placeholder='New Password' autocomplete='off'/></td>"
     . "<td><input name='mayTranslate' type='checkbox'$AccessTranslate/></td>"
     . "<td><input name='mayEdit' type='checkbox'$AccessEdit/></td>"
     . "<td><input name='mayUpload' type='checkbox'$AccessUpload/></td>"
     . "<td><input name='isSuperuser' type='checkbox'$AccessSuperuser/></td>"
     . "<td><button class='btn update'>Update</button>"
     . "<button class='btn btn-danger delete'>Delete</button></td></tr>";
}
