/***/
Family = Backbone.Model.extend({
  /**
    Returns a color string in the form of /#{[0-9a-fA-F]}6/, or null.
  */
  getColor: function(){
    var c = this.get('FamilyColorOnWebsite');
    if(typeof(c) === 'string' && c !== ''){
      return '#'+c;
    }
    return null;
  }
  /**
    Returns the name for the current family in the current translation.
  */
, getName: function(){
    var category = 'FamilyTranslationProvider'
      , field    = this.get('FamilyNm');
    return window.App.translationStorage.translateDynamic(category, field, field);
  }
});
