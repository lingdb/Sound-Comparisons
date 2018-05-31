"use strict";
define(['views/render/SubView', 'models/Loader', 'jquery'],
       function(SubView, Loader, $){
  return SubView.extend({
    /***/
    initialize: function(){

      this.model = {
        studies: this.getAllStudyData
      , siteLanguage: function(){return App.translationStorage.getBrowserMatch();}
      , title: function(){return App.translationStorage.translateStatic('website_title_prefix');}
      , subtitle: function(){return App.translationStorage.translateStatic('website_title_suffix');}
      , funding: function(){return App.translationStorage.translateStatic('website_logo_hover');}
      , imprint: function(){return App.translationStorage.translateStatic('topmenu_about_imprint');}
      , imprintHref: function(){return App.translationStorage.translateStatic('topmenu_about_imprint_href');}
      , privacypolicy: function(){return App.translationStorage.translateStatic('topmenu_about_privacypolicy');}
      , privacypolicyHref: function(){return App.translationStorage.translateStatic('topmenu_about_privacypolicy_href');}
      , licenceText: function(){return App.translationStorage.translateStatic('topmenu_about_licencetext');}
      , licenceTextHref: function(){return App.translationStorage.translateStatic('topmenu_about_licencetext_href');}
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
    */
  , getAllStudyData: function(){
      var allStudies = App.study.getAllIds().filter(function(item) {
          return item !== '--'
      })
      var studiesData = _.map(allStudies, function(n){
        var name = App.study.getName(n)
          , tileLink = App.study.getLink(n)
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
