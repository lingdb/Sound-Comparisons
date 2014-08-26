/***/
Study = Backbone.Model.extend({
  defaults: {
  }
/**
  The update method is connected by the model,
  to listen on change:study of the window.App.dataStorage.
  Update also needs to be called once eventlistening is setup in App.js
*/
, update: function(){
    var ds   = window.App.dataStorage
      , data = ds.get('study');
    if(data && 'study' in data){
      console.log('Study.update()');
      this.set(data.study);
    }
  }
/**
  Returns the name for the current study in the current translation.
*/
, getName: function(){
    var category = 'StudyTranslationProvider'
      , field    = this.get('Name')
    return window.App.translationStorage.translateDynamic(category, field, field);
  }
/**
  Returns the title for the current study in the current translation.
  The title is typically composed with website_title_{prefix,suffix} into the page title.
  This composition, however should be done in the according view rather than the study.
*/
, getTitle: function(){
    var category = 'StudyTitleTranslationProvider'
      , field    = this.get('Name')
    return window.App.translationStorage.translateDynamic(category, field, field);
  }
});
