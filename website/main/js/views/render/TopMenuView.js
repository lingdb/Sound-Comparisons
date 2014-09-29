/**
  The TopMenuView will be used by the Renderer.
  The TopMenuView will set it's own model to handle and smartly update it's render data.
*/
TopMenuView = Backbone.View.extend({
  initialize: function(){
    this.model = {
      formats: ['mp3','ogg']
    };
  }
  /**
    Function to call non /^update.+/ methods that are necessary for the model, and to setup their callbacks.
  */
, activate: function(){
    //Setting callbacks to update model:
    App.translationStorage.on('change:translationId', this.buildStatic, this);
    //Building statics the first time:
    this.buildStatic();
  }
  /**
    Overwrites the current model with the given one performing a deep merge.
  */
, setModel: function(m){
    this.model = $.extend(true, this.model, m);
  }
  /**
    Builds the static translations for the model.
  */
, buildStatic: function(){
    var staticT = App.translationStorage.translateStatic({
      logoTitle:       'website_logo_hover'
    , pageViewTitle:   'topmenu_views'
    , csvTitle:        'topmenu_download_csv'
    , sndLink:         'export/soundfiles'
    , sndTitle:        'topmenu_download_zip'
    , cogTitle:        'topmenu_download_cogTitle'
    , wordByWord:      'topmenu_download_wordByWord'
    , format:          'topmenu_download_format'
    , soundClickTitle: 'topmenu_soundoptions_tooltip'
    , soundHoverTitle: 'topmenu_soundoptions_hover'
    });
    this.setModel(staticT);
  }
  /**
    Generates the study part of the TopMenu.
  */
, updateStudy: function(){
    var data = {
      currentStudyName: App.study.getName()
    };
    data.studies = _.map(App.study.getAllIds(), function(n){
      var name = App.study.getName(n);
      return {
        currentStudy: name === data.currentStudyName
      , link: 'href="'+App.router.linkCurrent({study: n})+'"'
      , studyName: name
      };
    }, this);
    this.setModel(data);
  }
  /**
    Generates the PageViews part of the TopMenu.
  */
, updatePageViews: function(){
    var hovers = App.translationStorage.translateStatic({
      m:  'topmenu_views_mapview_hover'
    , w:  'topmenu_views_wordview_hover'
    , l:  'topmenu_views_languageview_hover'
    , lw: 'topmenu_views_multiview_hover'
    , wl: 'topmenu_views_multitransposed_hover'
    });
    var names = App.translationStorage.translateStatic({
      m:  'topmenu_views_mapview'
    , w:  'topmenu_views_wordview'
    , l:  'topmenu_views_languageview'
    , lw: 'topmenu_views_multiview'
    , wl: 'topmenu_views_multitransposed'
    });
    var images = {
      m:  'maps.png'
    , w:  '1w.png'
    , l:  '1l.png'
    , lw: 'lw.png'
    , wl: 'wl.png'
    };
    var links = {
      m:  App.router.linkMapView()
    , w:  App.router.linkWordView()
    , l:  App.router.linkLanguageView()
    , lw: App.router.linkLanguageWordView()
    , wl: App.router.linkWordLanguageView()
    };
    this.setModel({pageViews: _.map(_.keys(names), function(key){
      return {
        link:    'href="'+links[key]+'"'
      , content: this.tColor(key, names[key])
      , title:   hovers[key]
      , img:     images[key]
      , active:  App.pageState.isPageView(key)};
    }, this)});
  }
  /**
    Generates the translations part of the TopMenu.
  */
, updateTranslations: function(){
    this.setModel({
      currentFlag: App.translationStorage.getFlag()
    , otherTranslations: _.map(App.translationStorage.getOthers(), function(tId){
        return {
          link: 'href="'+App.router.linkConfig({Translation: tId})+'"'
        , flag: this.getFlag(tId)
        , name: this.getName(tId)
        };
      }, App.translationStorage)
    });
  }
  /**
    Generates the about/info links part of the TopMenu.
  */
, updateEntries: function(){
    var entries = App.translationStorage.translateStatic([
      { link:  'topmenu_about_furtherinfo_href'
      , about: 'topmenu_about_furtherinfo'}
    , { link:  'topmenu_about_research_href'
      , about: 'topmenu_about_research'}
    , { link:  'topmenu_about_contact_href'
      , about: 'topmenu_about_contact'}
    ]);
    _.each(entries, function(e){e.link = 'href="'+e.link+'"';});
    entries.unshift({
      link:  'href="#/whoAreWe"'
    , about: App.translationStorage.translateStatic('topmenu_about_whoarewe') 
    });
    this.setModel({aboutEntries: entries});
  }
  /**
    Reflects the current SoundPlayOption:
  */
, updatePlayOption: function(){
    this.setModel({soundOptionHover: App.soundPlayOption.playOnHover()});
  }
  /***/
, render: function(){
    this.$el.html(App.templateStorage.render('TopMenu', {TopMenu: this.model}));
    //The SoundPlayOption:
    var options = this.$('#topmenuSoundOptions img').click(function(){
      App.soundPlayOption.set({playMode: this.attributes['value'].value});
      options.each(function(){
        $(this).toggleClass('hide');
      });
    });
  }
  /**
    Helper method to color strings for updatePageViews.
    @param mode is expected to be an enum like string
    @param content is expected to be a string.
    @return content html string
  */
, tColor: function(mode, content){
    var modes = {
      m:  'color-map'
    , w:  'color-word'
    , l:  'color-language'
    , lw: {c1: 'color-language', c2: 'color-word'}
    , wl: {c1: 'color-word', c2: 'color-language'}
    };
    var color = modes[mode], cType = typeof(color);
    if(cType === 'string'){
      return '<div class="inline '+color+'">'+content+'</div>';
    }else if(cType === 'object'){
      var matches = content.match(/^(.*) [Xx×] (.*)$/)
        , m1 = matches[1], m2 = matches[2];
      return '<div class="inline '+color.c1+'">'+m1+'</div>×<div class="inline '+color.c2+'">'+m2+'</div>';
    }
    console.log('Unexpected behaviour in TopMenuView.tColor() with mode: '+mode);
    return content;
  }
});
