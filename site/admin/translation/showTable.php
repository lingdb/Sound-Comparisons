<?php
//Function to generate a table for Translation::pageAll:
function showTable($tdata, $showKeep = false){
  //Handling $showKeep:
  $showKeep = $showKeep
            ? '<a class="btn btn-info keep"><i class="icon-ok"></i>Keep</a>' : '';
  //Building the table:
  $head = '<tr><th style="width:80px;">Description</th><th style="width:20%;">Original</th><th>Translation'
        . '<input type="button" value="Save all" '
        . 'class="btn btn-primary pull-right saveAll"></th></tr>';
  echo "<table class='display table table-bordered' style='table-layout:fixed;'>"
     . "<thead>$head</thead><tbody>";
  $isAdmin = session_mayEdit() ? ' data-isadmin="1"' : '';
  while(count($tdata) > 0){
    $newTZ = array();
    foreach($tdata as $key => $field){
      if(count($field) === 0) continue;
      //Value to echo as row:
      $value = array_shift($field);
      $orig = $value['Original'];
      // show only entries whose 'Original' fields are set
      // if(strlen(trim($orig)) > 0){
        echo "<tr>";
        //Description:
        $desc = $value['Description'];
        $req  = array_key_exists('Req', $desc) ? $desc['Req'] : '';
        $desc = array_key_exists('Description', $desc) ? $desc['Description'] : '';
        //Shortening:
        $maxLen = 12;
        $short = preg_replace('/<[^<>]+>/','',$desc);
        if(strlen($short) > $maxLen){$short = substr($short, 0, $maxLen).'…';}
        //Encoding tooltip contents:
        $desc = preg_replace("/'/",'"',$desc);
        $desc = htmlspecialchars($desc);
        echo "<td class='description'$isAdmin' data-req='$req' data-html='true' data-container='body' data-title='$desc' style='white-space: nowrap;'>$short</td>";
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
          $orig = "$stud:<span style='color:red'>$orig</span>";
        }else{$orig = "<span style='color:red'>$orig</span>";}
        echo '<td class="original"'.$title.'>'.$orig
           . '<a class="btn btn-small pull-right copy-over">'
           . '<i class="icon-arrow-right"></i></a></td>';
        //Translation:
        $trans = $value['Translation'];
        $prov  = $trans['TranslationProvider'];
        $tId   = $trans['TranslationId'];
        $pay   = $trans['Payload'];
        $trans = $trans['Translation'];
        echo "<td data-tId='$tId' data-provider='$prov' data-payload='$pay'>"
           . "<input type='text' value='$trans' class='translation' style='width:90%'>"
           . '<a class="btn btn-small save"><i class="icon-hdd"></i>Save</a>'
           . $showKeep . '</td>';
        echo "</tr>";
      // }
      //Handling the exit condition:
      if(count($field) > 0){
        $newTZ[$key] = $field;
      }
    }
    $tdata = $newTZ;
  }
  echo "</tbody><tfoot>$head</tfoot></table>";
}
