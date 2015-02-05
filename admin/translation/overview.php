<?php if(session_mayEdit()){ ?>
<h3>Add a translation:</h3>
<form class="form-inline" id="addTranslation" method="get" action="query/translation.php">
  <input type="text" name="TranslationName" placeholder="Translation name" required>
  <input type="text" name="BrowserMatch" placeholder="Browsermatch" required>
  <input type="text" name="action" class="hide" value="createTranslation">
  <input type="text" name="Active" class="hide" value="0">
  <input type="text" name="ImagePath" class="hide" value="../img/flags/europeanunion.png">
  <input type="text" name="RfcLanguage" class="hide" value="">
  <button type="submit" class="btn">Create</button>
</form>
<?php } ?>
<h3>Current translations:</h3>
<table id="overviewTable" class="display table table-bordered">
  <thead>
  <?php
    $thead = '<tr>'
           . '<th>Translation name</th>'
           . '<th>Browsermatch</th>'
           . '<th>Flag</th>'
           . '<th>Equivalent language in database</th>'
           . '<th>Active</th>'
           . '<th>Actions<input type="button" class="btn btn-primary pull-right saveAll" value="Save all"></th>'
           . '</tr>';
    echo $thead;
  ?>
  </thead>
  <tbody>
  <?php
    $rfcLangs = Translation::getRfcLanguages();
    $genRfcLangOptions = function($rfcLang) use ($rfcLangs){
      $sel = array_key_exists($rfcLang, $rfcLangs) ? '' : ' selected="selected"';
      $opts = "<option class='default'$sel value='null'>none</option>";
      foreach($rfcLangs as $rfc => $sn){
        $sel = ($rfc === $rfcLang) ? ' selected="selected"' : '';
        $opts .= "<option$sel value='$rfc'>$sn</option>";
      }
      return "<select>$opts</select>";
    };
    $genTextInput = function($txt){ return "<input type='text' value='$txt'>"; };
    foreach(Translation::translations() as $t){
      //Fields to represent:
      $tId  = $t['TranslationId'];
      $name = $t['TranslationName'];
      $bm   = $t['BrowserMatch'];
      $img  = $t['ImagePath'];
      $rfcL = $t['RfcLanguage'];
      $act  = $t['Active'];
      //Transformations:
      $sLnk = "<a href='?action=translation&tId=$tId' class='btn btn-info'>Translate</a>";
      $dLnk = session_mayEdit() ? "<a href='query/translation.php?action=deleteTranslation&TranslationId=$tId' class='btn btn-danger'>Delete</a>" : '';
      $name = $genTextInput($name);
      $bm   = $genTextInput($bm);
      $rfcL = $genRfcLangOptions($rfcL);
      $chk  = ($act == 1) ? ' checked="checked"' : '';
      $act  = "<input type='checkbox'$chk>";
      //Row output:
      echo "<tr data-tId='$tId'>"
         . "<td class='name'>$name</td>"
         . "<td class='match'>$bm</td>"
         . "<td><img src='../$img' class='btn flag'></td>"
         . "<td>$rfcL</td>"
         . "<td>$act</td>"
         . "<td>$sLnk<input type='button' value='Save' class='btn save'>$dLnk</td>"
         . "</tr>";
    }
  ?>
  </tbody>
  <tfoot><?php echo $thead; ?></tfoot>
</table>
<div id="flagChooser" class="modal hide fade">
  <div class="modal-header">
    Click the desired Flag.
    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
  </div>
  <div class="modal-body"><?php
      $imgDir = '../img/flags';                  
      $files  = scandir("$imgDir");
      foreach($files as $f){
        if(preg_match('/^[^_]*\.png$/', $f))
          echo "<img src='$imgDir/$f' class='btn' style='width:16px; height: 11px;'>";
      }
  ?></div>
</div>
<script type="application/javascript">
$(document).ready(function(){
  /* Initialize DT */
  var table = $('#overviewTable').dataTable({
    paging: false
  , searching: false
  , language: {info: ''}
  , columnDefs: [ {targets: [2], orderable: false} ]
  , columns: [
      {orderDataType: 'dom-text', type: 'string'}
    , {orderDataType: 'dom-text', type: 'string'}
    , null
    , {orderDataType: 'dom-select'}
    , {orderDataType: 'dom-checkbox'}
    , null
    ]
  });
  /* Marking changes in a row: */
  table.find('tr').each(function(){
    var tr = $(this);
    tr.find('input[type="text"]').change(function(){$(this).changeInRow();});
    tr.find('input[type="checkbox"]').change(function(){$(this).changeInRow();});
    tr.find('select').change(function(){$(this).changeInRow();});
  });
  /* flagChooser modal: */
  var flagChooser = $('#flagChooser');
  //Clicking a flag button:
  $('img.btn.flag').each(function(){
    var t = $(this).click(function(){
      $('img.btn.flag').removeClass('clicked');
      t.addClass('clicked');
      flagChooser.modal('show');
    });
  });
  //Clicking a flag in the modal:
  flagChooser.find('img.btn').each(function(){
    var t = $(this).click(function(){
      $('img.btn.flag.clicked').attr('src', t.attr('src')).changeInRow();
      flagChooser.modal('hide');
    });
  });
  /* Save buttons: */
  $('.btn.save').each(function(){
    var btn = $(this).click(function(){
      if(!btn.hasClass('btn-warning')) return;
      btn.removeClass('btn-warning').addClass('btn-danger');
      var tr = btn.closest('tr')
        , q  = {
          action: 'updateTranslation'
        , TranslationId: tr.data('tid')
        , TranslationName: tr.find('td.name input').val()
        , BrowserMatch: tr.find('td.match input').val()
        , ImagePath: tr.find('img.flag').attr('src')
        , RfcLanguage: tr.find('select').val()
        , Active: tr.find('input[type="checkbox"]').is('selected') ? '1' : '0'
        };
      $.get('query/translation.php', q, function(){
        btn.removeClass('btn-danger').addClass('btn-success');
      });
    });
  });
  /* Save All button: */
  $('.btn.saveAll').each(function(){
    $(this).click(function(){
      table.find('.btn.save.btn-warning').trigger('click');
    });
  });
});
</script>
