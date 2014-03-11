TranslationSetupView = Backbone.View.extend({
  initialize: function(){
    this.queryTarget = 'query/translation.php';
    this.fetchTranslations();
    var view = this;
    //Selecting a flag:
    this.$('#flagChooser img.btn').click(function(){
      $('#Translations_ImagePath').attr('src', $(this).attr('src'));
      $('#flagChooser button.close').trigger('click');
    });
  }
, fetchTranslations: function(){
    var view = this;
    $.get(this.queryTarget, {action: 'translations'}, function(data){
      var translations = $.parseJSON(data);
      window.Translation.translations = translations;
      view.renderTranslations(translations);
    });
  }
, renderTranslations: function(translations){
    _.each(translations, function(t){
      $('#Translation_Select').append('<option>' + t.TranslationName + '</option>');
      $('#Translation_Select option:last').data('translation', t);
    });
  }
, events: {
    'change #Translation_Select': 'translationSelected'
  , 'click #Translations_Create': 'createTranslation'
  , 'click #Translations_Delete': 'deleteTranslation'
  , 'click #Translations_Update': 'updateTranslation'
  }
, translationSelected: function(){
    this.copyCurrentState(); // Taking care of recent changes…
    this.model.clear().set($('#Translation_Select option:selected').data('translation'));
    var ct = this.currentTranslation;
    //Displaying the new selection:
    $('#Translations_Name').val(this.model.get('TranslationName'));
    $('#Translations_Browsermatch').val(this.model.get('BrowserMatch'));
    $('#Translations_ImagePath').attr('src', '../'+this.model.get('ImagePath'));
    $('#Translations_RfcLanguage option[value="'+this.model.get('RfcLanguage')+'"]').attr('selected', 'selected');
    $('#Translations_Active').attr('checked', (this.model.get('Active ') == 1) ? 'checked' : '');
  }
, copyCurrentState: function(){
    if(_.size(this.model.attributes) === 0)
      return;
    this.model.set({
      TranslationName: $('#Translations_Name').val()
    , BrowserMatch: $('#Translations_Browsermatch').val()
    , ImagePath: $('#Translations_ImagePath').attr('src')
    , RfcLanguage: $('#Translations_RfcLanguage option:selected').attr('value')
    , Active: $('#Translations_Active:checked').length
    });
  }
, createTranslation: function(){
    if(_.size(this.model.attributes) > 0)
      if(this.model.get('TranslationName') == $('#Translations_Name').val()){
        alert('Can\'t create a new translation with a dublicate name.');
        return;
      }
    var newTranslation = {
        TranslationName: $('#Translations_Name').val()
      , BrowserMatch:    $('#Translations_Browsermatch').val()
      , ImagePath:       $('#Translations_ImagePath').attr('src')
      , RfcLanguage:     $('#Translations_RfcLanguage option:selected').attr('value')
      , Active:          ('' + $('#Translations_Active:checked').length)
    };
    if(newTranslation.TranslationName == ''){
      alert('You need a name to create a new translation');
      return;
    }
    var view = this;
    $.get(this.queryTarget, $.extend({action: 'createTranslation'}, newTranslation), function(data){
      newTranslation.TranslationId = data;
      view.model.clear().set(newTranslation);
      //Generate option:
      $('#Translation_Select').append('<option>' + newTranslation.TranslationName + '</option>');
      $('#Translation_Select option:last').data('translation', newTranslation);
      //Select newly generated option:
      $('#Translation_Select option:selected').removeAttr('selected');
      $('#Translation_Select option:last').attr('selected', 'selected');
      alert('Saved translation');
    });
  }
, deleteTranslation: function(){
    if(_.size(this.model.attributes) === 0){
      alert('You need to select a translation first, in order to delete it.');
      return;
    }
    var query = {
      action: 'deleteTranslation'
    , TranslationId: this.model.get('TranslationId')
    };
    var view = this;
    $.get(this.queryTarget, query, function(data){
      if(data != 'OK'){
        alert('Failed to delete selected Translation.');
        return;
      }
      //Clean it up:
      view.model.clean();
      view.clearTranslationInput();
      $('#Translation_Select option:selected').remove();
      $('#Translation_Select option.default').attr('selected', 'selected');
      alert('Translation deleted.');
    });
  }
, clearTranslationInput: function(){
    $('#Translations_Name').val('');
    $('#Translations_Browsermatch').val('');
    $('#Translations_ImagePath').attr('src', '');
    $('#Translations_RfcLanguage option:selected').removeAttr('selected');
    $('#Translations_RfcLanguage option.default').attr('selected', 'selected');
    $('#Translations_Active').removeAttr('checked');
  }
, updateTranslation: function(){
    if(_.size(this.model.attributes) === 0){
      alert('You need to have a translation selected in order to update it\'s contents.');
      return;
    }
    this.copyCurrentState();
    var q = $.extend({action: 'updateTranslation'}, this.model.attributes);
    var view = this;
    $.get(this.queryTarget, q, function(data){
      //Renaming possible changed TranslationName in <select>:
      $('#Translation_Select option:selected').html(view.model.get('TranslationName'));
      alert('Updated translation');
    });
  }
});
