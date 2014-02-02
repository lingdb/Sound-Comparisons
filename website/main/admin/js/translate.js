function Translation(dynamicTranslation){
  this.currentTranslation = null;
  this.dynamicTranslation = dynamicTranslation;
}

Translation.prototype.initial = function(){
  var t = this;
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
