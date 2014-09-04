/***/
Study = Backbone.Model.extend({
  /**
    The update method is connected by the App,
    to listen on change:study of the window.App.dataStorage.
  */
  update: function(){
    var ds   = window.App.dataStorage
      , data = ds.get('study');
    if(data && 'study' in data){
      console.log('Study.update()');
      this.set(data.study);
    }
  }
  /**
    Returns the name for the current study in the current translation.
    @param field can be used to overwrite the study name, which is helpful to translate other studies.
  */
, getName: function(field){
    field = field || this.get('Name');
    var category = 'StudyTranslationProvider';
    return window.App.translationStorage.translateDynamic(category, field, field);
  }
  /**
    Returns an array of the names of all other studies.
  */
, getAllNames: function(){
    var studies = App.dataStorage.get('global').studies;
    return _.map(studies, function(n){return this.getName(n);}, this);
  }
  /**
    Returns the title for the current study in the current translation.
    The title is typically composed with website_title_{prefix,suffix} into the page title.
    This composition, however should be done in the according view rather than the study.
  */
, getTitle: function(){
    var category = 'StudyTitleTranslationProvider'
      , field    = this.get('Name');
    return window.App.translationStorage.translateDynamic(category, field, field);
  }
  /**
    Predicate to tell if the families colors should be used for coloring.
  */
, getColorByFamily: function(){
    return parseInt(this.get('ColorByFamily')) === 1;
  }
});
