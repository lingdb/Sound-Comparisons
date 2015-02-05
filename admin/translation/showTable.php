<?php
//Function to generate a table for Translation::pageAll:
function showTable($tdata){
  $head = '<tr><th>Description:</th><th>Original:</th><th>Translation:'
        . '<input type="button" value="Save all" '
        . 'class="btn btn-primary pull-right saveAll"></th></tr>';
  echo "<table class='display table table-bordered'>"
     . "<thead>$head</thead><tbody>";
  $isAdmin = session_mayEdit() ? ' data-isadmin="1"' : '';
  while(count($tdata) > 0){
    $newTZ = array();
    foreach($tdata as $key => $field){
      echo "<tr>";
      //Value to echo as row:
      $value = array_shift($field);
      //Description:
      $desc = $value['Description'];
      $req  = $desc['Req'];
      $desc = $desc['Description'];
      echo "<td class='description'$isAdmin data-req='$req'>$desc</td>";
      //Match in case of search:
      $match = '';
      if(array_key_exists('Match', $value)){
        $match = ' title="'.$value['Match'].'"';
      }
      //Original:
      $orig = $value['Original'];
      echo '<td class="original"'.$match.'>'.$orig
         . '<a class="btn pull-right copy-over">'
         . '<i class="icon-arrow-right"></i></a></td>';
      //Translation:
      $trans = $value['Translation'];
      $prov  = $trans['TranslationProvider'];
      $tId   = $trans['TranslationId'];
      $pay   = $trans['Payload'];
      $trans = $trans['Translation'];
      echo "<td data-tId='$tId' data-provider='$prov' data-payload='$pay'>"
         . "<input type='text' value='$trans' class='translation'>"
         . '<a class="btn save"><i class="icon-hdd"></i>Save</a>'
         . '</td>';
      //Handling the exit condition:
      if(count($field) > 0){
        $newTZ[$key] = $field;
      }
      echo "</tr>";
    }
    $tdata = $newTZ;
  }
  echo "</tbody><tfoot>$head</tfoot></table>";
}
?>
