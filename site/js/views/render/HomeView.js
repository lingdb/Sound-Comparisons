"use strict";
define(['views/render/SubView', 'models/Loader', 'jquery'],
       function(SubView, Loader, $){
  return SubView.extend({
  /***/
  initialize: function(){
    this.model = {
      studies: this.getAllStudyData
    , siteLanguage: function(){return App.translationStorage.getBrowserMatch();}
    , currentFlag: function(){return App.translationStorage.getFlag();}
    , otherTranslations: function(){return _.chain(App.translationStorage.getOthers()).map(function(tId){
        return {
          tId: tId
        , flag: this.getFlag(tId)
        , name: this.getName(tId)
        };
      }, App.translationStorage).sortBy('name').value();}
    };
    //Connecting to the router
    App.router.on('route:homeView', this.route, this);
    //Scroll target: 
    this._scrollTo = null;
  }
  /**
    Activates callbacks to build parts of the model, which shall not be produced by /^update.+/ methods.
  */
  , activate: function(){
      //Setting callbacks to update model:
      App.translationStorage.on('change:translationId', this.buildStatic, this);
      //Building statics:
      this.buildStatic();
    }
    /**
      Builds the static translations, and is, in contrast to /^update.+/ methods,
      only called on activate and change of translationId.
    */
  , buildStatic: function(){
      var staticT = App.translationStorage.translateStatic({
          title: 'website_title_prefix'
        , subtitle: 'website_title_suffix'
        , funding: 'website_logo_hover'
        , imprint: 'topmenu_about_imprint'
        , imprintHref: 'topmenu_about_imprint_href'
        , privacypolicy: 'topmenu_about_privacypolicy'
        , privacypolicyHref: 'topmenu_about_privacypolicy_href'
        , licenceText: 'topmenu_about_licencetext'
        , licenceTextHref: 'topmenu_about_licencetext_href'
      });
      _.extend(this.model, staticT);
    }
    /**
    */
  , getAllStudyData: function(){
      var allStudies = App.study.getAllIds().filter(function(item) {
          return item !== '--'
      })
      var studiesData = _.map(allStudies, function(n){
        var name = App.study.getName(n)
          , title = App.study.getTitle(n);
        return {
          studyName: name
        , imgName: n
        , studyTitle: title
        };
      }, this);
      return studiesData;
  }
    /**
      Method to make it possible to check what kind of PageView this SubView is.
    */
  , getKey: function(){return 'homeView';}
    /**
      @param page string
    */
  , route: function(){
      //Log what we're doing:
      console.log('HomeView.route()');
      //Set currentPage and do some stuff:
      App.pageState.setPageView(this);
      App.views.renderer.render();
    }
    /***/
    , render: function(){
      if(App.pageState.isPageView(this)){
        // since homeView is no study the title has to be set manually
        document.title = this.model.title;
        this.$el.html(App.templateStorage.render('home', this.model));
        this.$el.removeClass('hide');
      }else{
        this.$el.addClass('hide');
      }
      //We set the hash again, to make sure the browser scrolls to our target:
      if(this._scrollTo){
        var h = window.location.hash;
        window.location.hash = '#';
        window.location.hash = h;
      }
    }
  });
});
