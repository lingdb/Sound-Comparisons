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
          if(0 === strpos(strval($t['LanguageIx']), "9999")){
            echo "<td><span style='margin-right:16px'></span><span style='margin-left:2px;margin-right:16px'></span>&nbsp;".$t['ShortName']."</td>";
          }else{
            if(0 === strpos(strval($t['ShortName']), "✕")){
              echo "<td><a href='?study=$studyName&lgix={$t['LanguageIx']}'><img width=16px src='../img/info.png'></a>"
                  ."<span style='margin-left:2px;margin-right:16px'></span>&nbsp;".$t['ShortName']."</td>";
            }else{
              echo "<td><a href='?study=$studyName&lgix={$t['LanguageIx']}'><img width=16px src='../img/info.png'></a>"
                  ."<a target='_blank' href='../#/en/$studyName/language/{$t['ShortName']}'><img style='margin-left:2px;' width=16px src='../img/1l.png'></a>&nbsp;".$t['ShortName']."</td>";
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
    ̇<h4 style="margin-left:20px">Short Name: <?php echo DataProvider::$checkFilePathsForLanguageIx['ShortName']?></h4>
    <h5>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;more is comming soon...</h5>
    <table class="display table table-bordered">
      <?php
        $head = '<tr>'
                  .'<th>Sound Path (disk)</th>'
                  .'<th>Sound Path (database)</th>'
                  .'<th>Short Name</th>'
                  .'<th>LanguageIx</th>'
                  .'<th title="Number of non empty Phonetic fields per language">#Transcriptions ('.DataProvider::$checkFilePathsNumberOfWords.' words)</th>'
               .'</tr>';
        echo "<thead>$head</thead>";
        //Making sure we're at the right location:
        chdir('..');
        foreach(DataProvider::$checkFilePaths as $t){
          echo "<tr>";
          echo "<td>".$t['SoundPath']."</td>";
          echo "<td>".$t['FilePathPart']."</td>";
          echo "<td><a href='?lgix={$t['LanguageIx']}'><img width=16px src='../img/info.png'></a>&nbsp;".$t['ShortName']."</td>";
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
    <?php }?>
  </body>
</html>
