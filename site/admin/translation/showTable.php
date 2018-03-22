<?php
//Function to generate a table for Translation::pageAll:
function showTable($tdata, $showKeep = false){
  //Handling $showKeep:
  $showKeep = $showKeep
            ? '<a class="btn btn-info keep"><i class="icon-ok"></i>Keep</a>' : '';
  //Building the table:
  $head = '<tr><th>Description:</th><th>Original:</th><th>Translation:'
        . '<input type="button" value="Save all" '
        . 'class="btn btn-primary pull-right saveAll"></th></tr>';
  echo "<table class='display table table-bordered'>"
     . "<thead>$head</thead><tbody>";
  $isAdmin = session_mayEdit() ? ' data-isadmin="1"' : '';
  while(count($tdata) > 0){
    $newTZ = array();
    foreach($tdata as $key => $field){
      if(count($field) === 0) continue;
      //Value to echo as row:
      $value = array_shift($field);
      $orig = $value['Original'];
      if(strlen($orig) === 0) continue;
      echo "<tr>";
      //Description:
      $desc = $value['Description'];
      $req  = array_key_exists('Req', $desc) ? $desc['Req'] : '';
      $desc = array_key_exists('Description', $desc) ? $desc['Description'] : '';
      //Shortening:
      $maxLen = 42;
      $short = preg_replace('/<[^<>]+>/','',$desc);
      if(strlen($short) > $maxLen){$short = substr($short, 0, $maxLen).'…';}
      //Encoding tooltip contents:
      $desc = preg_replace("/'/",'"',$desc);
      $desc = htmlspecialchars($desc);
      echo "<td class='description'$isAdmin' data-req='$req' data-html='true' data-container='body' data-title='$desc'  style='max-width:10% !important'>$short</td>";
      //Title in case of search:
      $title = '';
      if(array_key_exists('Match', $value)){
        if($value['Match'] !== $value['Original'] && $value['Match'] !== $value['Translation']){
          $title = ' title="'.$value['Match'].'"';
        }
      }
      //Original:
      if(array_key_exists('Study', $value)){//Study in case of search
        $stud = $value['Study'];
        $orig = "$stud:<code>$orig</code>";
      }else{$orig = "<code>$orig</code>";}
      echo '<td class="original"'.$title.' style="width:30% !important">'.$orig
         . '<a class="btn pull-right copy-over">'
         . '<i class="icon-arrow-right"></i></a></td>';
      //Translation:
      $trans = $value['Translation'];
      $prov  = $trans['TranslationProvider'];
      $tId   = $trans['TranslationId'];
      $pay   = $trans['Payload'];
      $trans = $trans['Translation'];
      echo "<td data-tId='$tId' data-provider='$prov' data-payload='$pay' style='width:80% !important'>"
         . "<input type='text' value='$trans'  style='width:80% !important' class='translation'>"
         . '<a class="btn save"><i class="icon-hdd"></i>Save</a>'
         . $showKeep . '</td>';
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
