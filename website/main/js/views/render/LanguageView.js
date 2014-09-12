/***/
LanguageView = Backbone.View.extend({
  /***/
  initialize: function(){
    this.model = {};
  }
  /**
    Method to make it possible to check what kind of PageView this Backbone.View is.
  */
, getKey: function(){return 'language';}
  /**
    Overwrites the current model with the given one performing a deep merge.
  */
, setModel: function(m){
    this.model = $.extend(true, this.model, m);
  }
  /***/
, updateLanguageHeadline: function(){
    var language = App.languageCollection.getChoice();
    if(!language){
      console.log('LanguageView.updateLanguageHeadline() without a language.');
      return;
    }
    //The basic headline:
    var headline = {
      longName: language.getLongName()
    , LanguageLinks: this.buildLinks(language)
    , LanguageDescription: undefined // FIXME implement
    , playAll: App.translationStorage.translateStatic('language_playAll')
    };
    //Previous Language:
    //Next Language:
    //Contributors:
    //FIXME implement
    //Done:
    this.setModel({languageHeadline: headline});
  }
  /**
    Helper method for updateLanguageHeadline; generates LanguageLinks.
  */
, buildLinks: function(lang){
    var ls = [];
    //Various links:
    if(iso = lang.getISO()){
      ls.push(
        { href: 'http://www.ethnologue.com/show_language.asp?code='+iso
        , img:  'http://www.ethnologue.com/favicon.ico'
        , ttip: App.translationStorage.translateStatic('tooltip_languages_link_ethnologue')}
      , { href: 'http://www.glottolog.org/resource/languoid/iso/'+iso
        , img:  'img/extern/glottolog.png'
        , ttip: App.translationStorage.translateStatic('tooltip_languages_link_glottolog')}
      , { href: 'http://multitree.org/codes/'+iso+'.html'
        , img:  'http://multitree.org/images/favicon.ico'
        , ttip: App.translationStorage.translateStatic('tooltip_languages_link_multitree')}
      , { href:  'http://www.llmap.org/maps/by-code/'+iso+'.html'
        , style: 'width: 36px;'
        , img:   'img/extern/llmap.png'
        , ttip:  App.translationStorage.translateStatic('tooltip_languages_link_llmap')}
      );
    }
    //Wikipedia link:
    if(href = lang.getWikipediaLink()){
      ls.push({
        ttip:  App.translationStorage.translateStatic('tooltip_languages_link_wikipedia')
      , img:   'http://en.wikipedia.org/favicon.ico'
      , class: 'favicon favicon-bordered'
      , href:  href
      });
    }
    //Maps link:
    if(loc = lang.getLocation()){
      ls.push({
        ttip: App.translationStorage.translateStatic('tooltip_languages_link_mapview')
      , href: 'http://maps.google.com/maps?z=12&q='+loc.join()
      , img:  'img/langmap.png'
      });
    }
    return {links: ls};
  }
  /***/
, updateLanguageTable: function(){
    //FIXME implement
  }
  /***/
, render: function(){
    console.log('LanguageView.render()');
    if(App.pageState.isPageView(this)){
      this.$el.html(App.templateStorage.render('LanguageTable', this.model));
      this.$el.removeClass('hide');
    }else{
      this.$el.addClass('hide');
    }
  }
});
