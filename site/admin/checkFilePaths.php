<?php
require_once('common.php');
if(!session_validate($dbConnection))
  header('LOCATION: index.php');
if(!session_mayEdit($dbConnection))
  header('LOCATION: index.php');
?>
<!DOCTYPE HTML>
<html>
  <?php
    $title = 'Check File Paths';
    $jsFiles = array('extern/jquery.dataTables.js');
    require_once('head.php');
  ?>
  <body>
    <?php require_once('topmenu.php'); ?>
    <h3>&nbsp;&nbsp;Choose a study to check file paths, LanguageIx,<br />&nbsp;&nbsp;and number of transcriptions for study:</h3>
    <div class="btn-group">
    <?php
      $current = isset($_GET['study']) ? $_GET['study'] : '';
      foreach(DataProvider::getStudies() as $s){
        $style = ($s === $current) ? ' btn-inverse' : '';
        echo "<a class=\"btn$style\" href=\"?study=$s\">$s</a>";
      }
    ?>
    </div>

    <?php if(isset($_GET['study']) and !isset($_GET['lgix'])){
      DataProvider::checkFilePaths($_GET['study']);
      echo "<br /><i><small>&nbsp;&nbsp;".DataProvider::$checkFilePathsFurtherCheckOfDisk."</small></i>";
    ?>
    <table class="display table table-bordered">
      <?php
        $head = '<tr>'
                  .'<th>Sound Path (disk)</th>'
                  .'<th>Sound Path (database)</th>'
                  .'<th>Short Name</th>'
                  .'<th>LanguageIx</th>'
                  .'<th title="Number of non-empty Phonetic fields per language">#Transcriptions ('.DataProvider::$checkFilePathsNumberOfWords.' words)</th>'
               .'</tr>';
        echo "<thead>$head</thead>";
        //Making sure we're at the right location:
        chdir('../');
        $studyName = $_GET['study'];
        foreach(DataProvider::$checkFilePaths as $t){
          echo "<tr>";
          echo "<td>".$t['SoundPath']."</td>";
          echo "<td>".$t['FilePathPart']."</td>";
          if((0 === strpos(strval($t['LanguageIx']), "9999")) || (strval($t['LanguageIx']) === "")){
            echo "<td><span style='margin-right:16px'></span><span style='margin-left:2px;margin-right:16px'></span>&nbsp;".$t['ShortName']."</td>";
          }else{
            if(0 === strpos(strval($t['ShortName']), "✕")){
              echo "<td><a href='?study=$studyName&lgix={$t['LanguageIx']}'><img width=16px src='../img/info.png'></a>"
                  ."<span style='margin-left:2px;margin-right:16px'></span>&nbsp;".$t['ShortName']."</td>";
            }else{
              echo "<td><a href='?study=$studyName&lgix={$t['LanguageIx']}'><img width=16px src='../img/info.png'></a>"
                  ."<a target='_blank' href='../#/en/$studyName/language/{$t['FilePathPart']}'><img style='margin-left:2px;' width=16px src='../img/1l.png'></a>&nbsp;".$t['ShortName']."</td>";
            }
          }
          echo "<td>{$t['LanguageIx']}</td>";
          echo "<td>{$t['NumOfTrans']}</td>";
          echo "</tr>";
        }
      ?>
    </table>
    <script type="application/javascript">
      $(document).ready(function(){
        $('table.display').DataTable({paging: false, ordering: true, order: [[ 1, "asc" ]]});
      });
    </script>
    <?php }

    if(isset($_GET['study']) and isset($_GET['lgix'])){
      DataProvider::checkFilePathsForLanguageIx($_GET['study'], $_GET['lgix']);
    ?>
    ̇<h4 style="margin-left:20px">Short Name: <?php echo DataProvider::$checkFilePathsForLanguageIx['ShortName']?> – 
    LanguageIx: <?php echo DataProvider::$checkFilePathsForLanguageIx['LanguageIx']." – ".DataProvider::$checkFilePathsForLanguageIx['FilePathPart']?>
    <?php

    echo "<a target='_blank' href='http://www.soundcomparisons.com/#/en/".$_GET['study']."/language/".DataProvider::$checkFilePathsForLanguageIx['FilePathPart']."'><img style='margin-left:2px;' width=16px src='../img/1l.png'></a></h4>";

    if(DataProvider::$checkFilePathsForLanguageIx['ErrInfo']<>""){
      echo "<h5><span style='color:red'>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;".DataProvider::$checkFilePathsForLanguageIx['ErrInfo']."</span></h5>";
    }

    ?>
    <table class="display table table-bordered">
      <?php
        $head = '<tr>'
                  .'<th>Sound</th>'
                  .'<th>Sound Path (disk)</th>'
                  .'<th>Meaning</th>'
                  .'<th>IxElic</th>'
                  .'<th class="'.DataProvider::$checkFilePathsForLanguageIx['specIxElic'].'">Excel File Ix</th>'
                  .'<th>Phonetic</th>'
                  .'<th>IxMorph</th>'
                  .'<th>AltPhon</th>'
                  .'<th>AltLex</th>'
                  .'<th>AltSpell1</th>'
                  .'<th>AltSpell2</th>'
                  .'<th>Study</th>'
               .'</tr>';
        echo "<thead>$head</thead>";
        //Making sure we're at the right location:
        chdir('..');
        $cnt = 0;
        foreach(DataProvider::$checkFilePaths as $t){
          echo "<tr>";
          echo "<td>".$t['pathok']."</td>";
          echo "<td>".$t['SoundPath']."</td>";
          echo "<td>".$t['Meaning']."</td>";
          echo "<td>".$t['IxElicitation']."</td>";
          echo "<td class='".DataProvider::$checkFilePathsForLanguageIx['specIxElic']."'>".$t['IxEliciSpec']."</td>";
          echo "<td><audio id='player".$cnt."' src='".$t['SoundPathHref']."'></audio><button onclick=\"document.getElementById('player".$cnt."').play()\">".$t['hasSound']."</button>&nbsp;&nbsp;".$t['Phonetic']."</td>";
          echo "<td>".$t['IxMorph']."</td>";
          echo "<td>".$t['AltPhonReal']."</td>";
          echo "<td>".$t['AltLexem']."</td>";
          echo "<td>".$t['AltSpell1']."</td>";
          echo "<td>".$t['AltSpell2']."</td>";
          echo "<td>".$t['Study']."</td>";
          echo "</tr>";
          $cnt = $cnt + 1;
        }
      ?>
    </table>
    <?php
    if(count(DataProvider::$checkFilePathsForLanguageIx['remainingSndFiles'])>0){
      echo "<h5>Remaining sound files at the server:</h5>";
      foreach(DataProvider::$checkFilePathsForLanguageIx['remainingSndFiles'] as $s){
        echo "&nbsp;&nbsp;$s<br />";
      }
    }
    ?>
    <script type="application/javascript">
      $(document).ready(function(){
        $('table.display').DataTable({paging: false, ordering: true, order: [[ 3, "asc" ]]});
      });
    </script>
    <?php }?>
  </body>
</html>
