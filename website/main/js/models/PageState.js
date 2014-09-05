/**
  The PageState has a variety of tasks that lie at the core of our Application.
  - It tracks state for the site, where the different parts should not do so themselfs.
  - It aids construcing links for the site.
  - It assists in parsing links for the site.
*/
PageState = Backbone.Model.extend({
  defaults: {
    wordOrder: 'alphabetical'
  , spLang: null
  , phLang: null
  }
  /**
    Sets up callbacks to manipulate PageState when necessary.
  */
, activate: function(){
    //{sp,ph}Lang need resetting sometimes:
    var reset = function(){this.set({spLang: null, phLang: null})};
    App.study.on('change', reset, this);
    App.translationStorage.on('change:translationId', reset, this);
  }
//Managing the wordOrder:
  /**
    Predicate to test if the wordOrder is logical
  */
, wordOrderIsLogical: function(){
    return this.get('wordOrder') === 'logical';
  }
  /**
    Predicate to test if the wordOrder is alphabetical
  */
, wordOrderIsAlphabetical: function(){
    return this.get('wordOrder') === 'alphabetical';
  }
  /**
    Sets the wordOrder to logical
  */
, wordOrderSetLogical: function(){
    this.set({wordOrder: 'logical'});
  }
  /**
    Sets the wordOrder to alphabetical
  */
, wordOrderSetAlphabetical: function(){
    this.set({wordOrder: 'alphabetical'});
  }
//Managing {sp,ph}Lang:
  /**
    Returns the current spellingLanguage
  */
, getSpLang: function(){
    var spl = this.get('spLang');
    if(spl === null){
      spl = App.translationStorage.getRfcLanguage();
      this.attributes.spLang = spl;
    }
    return spl;
  }
  /**
    Returns the current phoneticLanguage
  */
, getPhLang: function(){
    var phl = this.get('phLang');
    if(phl === null){
      if(spl = this.getSpLang()){
        phl = spl;
      }else{
        phl = App.languageCollection.getDefaultPhoneticLanguage();
      }
      this.attributes.phLang = phl;
    }
    return phl;
  }
});
