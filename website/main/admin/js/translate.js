function Translation(dynamicTranslation){
  this.currentTranslation = null;
  this.dynamicTranslation = dynamicTranslation;
}

Translation.prototype.initial = function(){
  var t = this;
  //Binding Flagselection:
  $('#flagChooser img.btn').click(function(){
    $('#Translations_ImagePath').attr('src', $(this).attr('src'));
    $('#flagChooser button.close').trigger('click');
  });
  //Fetching existing translations:
  $.get('query/translation.php', {action: 'getPageTranslations'}, function(data){
    var translations = $.parseJSON(data);
    $(translations).each(function(){
      var option = '<option>' + this.TranslationName + '</option>';
      $('#Translation_Select').append(option);
      $('#Translation_Select option:last').data('translation', this);
    });
  });
  //Bind Transcription_Select:
  $('#Translation_Select').change(function(){
    //Save current state:
    t.saveCurrentState();
    //Fetch new state:
    t.currentTranslation = $('#Translation_Select option:selected').data('translation');
    //Notify dynamicTranslation:
    if(t.currentTranslation){
      t.dynamicTranslation.setTranslationId(t.currentTranslation.TranslationId);
    }
    //Clearing inputs:
    t.clearTranslationInput();
    t.hideStaticTranslations();
    //Filling inputs:
    if(t.currentTranslation){//Something selected
      var ct = t.currentTranslation;
      //Setting text fields:
      $('#Translations_Name').val(ct.TranslationName);
      $('#Translations_Browsermatch').val(ct.BrowserMatch);
      //Setting flag icon:
      $('#Translations_ImagePath').attr('src', '../' + ct.ImagePath);
      //Setting rfcLanguage:
      $('#Translations_RfcLanguage option').each(function(){
        var o = $(this);
        if(o.attr('value') == ct.RfcLanguage){
          $('#Translations_RfcLanguage option.default').removeAttr('selected');
          o.attr('selected', 'selected');
          return false;
        }
      });
      //Setting active:
      if(ct.Active == '1')
        $('#Translations_Active').attr('checked', 'checked');
    }
  });
  //Bind Create-button:
  $('#Translations_Create').click(function(){
    if(t.currentTranslation)
      if(t.currentTranslation.TranslationName == $('#Translations_Name').val()){
        alert('Can\'t create a new translation with a dublicate name.');
        return;
      }
    var newTranslation = {
        TranslationName:  $('#Translations_Name').val()
      , BrowserMatch:     $('#Translations_Browsermatch').val()
      , ImagePath:        $('#Translations_ImagePath').attr('src')
      , RfcLanguage:      $('#Translations_RfcLanguage option:selected').attr('value')
      , Active:           ('' + $('#Translations_Active:checked').length)
    };
    if(newTranslation.TranslationName == ''){
      alert('You need a name to create a new translation');
      return;
    }
    $.get(
        'query/translation.php'
      , $.extend({action: 'createPageTranslation'}, newTranslation)
      , function(data){
          newTranslation.TranslationId = data;
          t.currentTranslation = newTranslation;
          //Generate option:
          $('#Translation_Select').append('<option>' + newTranslation.TranslationName + '</option>');
          $('#Translation_Select option:last').data('translation', newTranslation);
          //Select newly generated option:
          $('#Translation_Select option:selected').removeAttr('selected');
          $('#Translation_Select option:last').attr('selected', 'selected');
          alert('Saved translation');
    });
  });
  //Bind Update-button:
  $('#Translations_Update').click(function(){
    if(!t.currentTranslation){
      alert('You need to have a translation selected in order to update it\'s contents.');
      return;
    }
    t.saveCurrentState();
    var q = $.extend({action: 'updatePageTranslation'}, t.currentTranslation);
    $.get('query/translation.php', q, function(data){
      //Renaming possible changed TranslationName in <select>:
      $('#Translation_Select option:selected').html(t.currentTranslation.TranslationName);
      alert('Updated translation');
    });
  });
  //Bind Delete-button:
  $('#Translations_Delete').click(function(){
    if(!t.currentTranslation){
      alert('You need to select a translation first, in order to delete it.');
      return;
    }
    $.get(
        'query/translation.php'
      , { action: 'deletePageTranslation'
        , TranslationId: t.currentTranslation.TranslationId}
      , function(data){
          if(data != 'OK'){
            alert('Failed to delete selected Translation.');
            return;
          }
          //Clean it up:
          t.currentTranslation = null;
          t.clearTranslationInput();
          $('#Translation_Select option:selected').remove();
          $('#Translation_Select option.default').attr('selected', 'selected');
          alert('Translation deleted.');
    });
  });
  //Bind Translate-button:
  $('#Translations_Translate').click(function(){
    t.dynamicTranslation.hide();
    $('#SearchTranslations').hide();
    if(!t.currentTranslation){
      alert('You need to select a translation first.');
      return;
    }
    if($('#StaticTable tr.dataRow').length > 0){
      alert('You\'ve already clicked this.\nMaybe you want to select another translation first?');
      return;
    }
    t.fetchStaticTranslations();
  });
  //Bind the save-all-button:
  $('#Translations_Translate_SaveAll').click(function(){
    //Save all the things!
    $('#StaticTable td.action').each(function(){
      $(this).find('button').trigger('click');
    });
  });
  //Bind TranslateDynamic-button:
  $('#Translations_TranslateDynamic').click(function(){
    t.hideStaticTranslations();
    $('#SearchTranslations').hide();
    if(!t.currentTranslation){
      alert('Please select a translation first.');
    }else{
      t.dynamicTranslation.show();
    }
  });
  //Bind TranslationBySearch-button:
  $('#Translations_TranslateSearch').click(function(){
    t.hideStaticTranslations();
    t.dynamicTranslation.hide();
    if(!t.currentTranslation){
      alert('Please select a translation first.');
    }else{
      $('#SearchTranslations').show();
    }
  });
  //Bind Export-button:
  $('#Translations_Export').click(function(){
    window.open('query/export.php','_blank');
  });
};

/**
  Saves the current state of the input in #Translations to this.currentTranslation
  - to be used by update and on selection of a Translation.
  - remember that the different states are also kept as .data() on their options :)
*/
Translation.prototype.saveCurrentState = function(){
  if(!this.currentTranslation)
    return;
  var ct = this.currentTranslation;
  //Gather data:
  ct.TranslationName  = $('#Translations_Name').val();
  ct.BrowserMatch     = $('#Translations_Browsermatch').val();
  ct.ImagePath        = $('#Translations_ImagePath').attr('src');
  ct.RfcLanguage      = $('#Translations_RfcLanguage option:selected').attr('value');
  ct.Active           = $('#Translations_Active:checked').length;
};

/**
  Used to clear input fields.
  Typical cases are:
  - The current selection has been deleted
  - Another selection needs clean fields to fill in
*/
Translation.prototype.clearTranslationInput = function(){
  $('#Translations_Name').val('');
  $('#Translations_Browsermatch').val('');
  $('#Translations_ImagePath').attr('src', '');
  $('#Translations_RfcLanguage option:selected').removeAttr('selected');
  $('#Translations_RfcLanguage option.default').attr('selected', 'selected');
  $('#Translations_Active').removeAttr('checked');
};

/**
  Cleans and hides the #StaticTable
*/
Translation.prototype.hideStaticTranslations = function(){
  $('#StaticTable tr.dataRow').remove('');
  $('#StaticTranslations').hide();
};

/**
  Requires a Translation to be selected
*/
Translation.prototype.fetchStaticTranslations = function(){
  var query = {
      action: 'fetchStaticTranslations'
    , source : '1'
    , target: this.currentTranslation.TranslationId
  };
  //Fetching data:
  $.get('query/translation.php', query, function(data){
    //Display content:
    $('#StaticTranslations').show();
    var statics = $.parseJSON(data);
    $(statics).each(function(){
      var id = 'Translation_'+this.Req;
      var editBox = '<input class="translationText" type="text" value="' + this.Trans + '"/>';
      var row = '<tr id="'+id+'" class="dataRow" data-req="'+this.Req+'">'
        + '<td class="description">' + this.Desc + '</td>'
        + '<td class="source">' + this.SourceTrans + '</td>'
        + '<td class="edit">' + editBox + '</td>'
        + '<td class="action">'
          + '<button class="saveButton btn">Save</button>'
          + '<br /><img class="hide" src="js/ajax-loader.gif" />'
        + '</td></tr>';
      $('#StaticTable').append(row);
      //Bind button:
      var t = this;
      $('#' + id + ' .saveButton').click(function(){
        var query = {
            action: 'putStaticTranslation'
          , TranslationId: t.TranslationId
          , Req: t.Req
          , Trans: $('#' + id + ' .translationText').attr('value')
          , IsHtml: t.IsHtml
        };
        $('#' + id + ' img').show();
        $.get('query/translation.php', query, function(data){
          if(data == 'OK')
            $('#' + id + ' img').hide();
        });
      });
    });
    //Admins may edit descriptions:
    if($('#topMenu').data('isadmin') == '1')
      $('#StaticTable tr.dataRow').each(function(){
        var q = {
            action: 'updateTranslationDescription'
          , Req: $(this).data('req')
          , Description: $('td.description', this).text()
        };
        $('td.description', this).html(
            '<div>'+q.Description+'</div>'
          + '<textarea style="width: 100%;" class="hide">'
          + q.Description
          + '</textarea>');
        var div      = $('td.description>div',     this);
        var textarea = $('td.description>textarea',this);
        div.dblclick(function(){
          div.hide();
          textarea.show().autoResize();
        });
        textarea.blur(function(){
          q.Description = textarea.val();
          $.get('query/translation.php', q);
          div.html(q.Description);
          textarea.hide();
          div.show();
        });
      });
  });
};

$(document).ready(function(){
  var d = new DynamicTranslation();
  var t = new Translation(d);
  t.initial();
});
