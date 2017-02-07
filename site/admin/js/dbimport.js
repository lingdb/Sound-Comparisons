$(document).ready(function(){
  //Function to update form fields for LanguageFamily:
  var updateLanguageFamilyForm = function(){
    var opt = $('#select_languagefamily option:selected');
    $('#text_languagefamily_studyix').val(opt.attr('data-studyix'));
    $('#text_languagefamily_familyix').val(opt.attr('data-familyix'));
    $('#text_languagefamily_subfamilyix').val(opt.attr('data-subfamilyix'));
    $('#text_languagefamily_name').val(opt.text());
  };
  //Load possible selection for languagefamilies:
  $.get('query/query.php', {action: 'fetchLanguageFamilySelection'}, function(data){
    $('#select_languagefamily').html(data);
    updateLanguageFamilyForm();
  });
  //Changing the selection of a languageFamily:
  $('#select_languagefamily').change(updateLanguageFamilyForm);
  //Bind create_languagefamily button:
  $('#create_languagefamily').click(function(e){
    e.preventDefault();
    var query = {
        action:       'createLanguageFamily'
      , studyix:      $('#text_languagefamily_studyix').val()
      , familyix:     $('#text_languagefamily_familyix').val()
      , subfamilyix:  $('#text_languagefamily_subfamilyix').val()
      , name:         $('#text_languagefamily_name').val()
    };
    $.get('query/query.php', query, function(data){
      if(data == 'FAIL'){
        alert('Failed to insert new languagefamily, sorry.');
      }else{
        $('#select_languagefamily option:selected').removeAttr('selected');
        $('#select_languagefamily').prepend(data);
        $('#select_languagefamily option:first-child').attr('selected', 'selected');
        updateLanguageFamilyForm();
        alert('Created new languagefamily.');
      }
    });
  });
  //Bind button_upload:
  $('#button_upload').click(function(e){
    e.preventDefault();
    //Trigger on iframe load:
    $('#iframe_post_form').on('load', function(){
      $('#iframe_post_form').unbind('load'); // No further listening
    });
    //Load it up
    $('#fileform').submit();
  });
});

