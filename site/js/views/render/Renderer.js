"use strict";
define([
    'backbone'
  , 'views/render/TitleView'
  , 'views/render/TopMenuView'
  , 'views/render/LanguageMenuView'
  , 'views/render/WordMenuView'
  , 'views/render/MapView'
  , 'views/render/WordView'
  , 'views/render/LanguageView'
  , 'views/render/LanguageWordView'
  , 'views/render/WordLanguageView'
  , 'views/render/ContributorView'
  , 'views/render/AboutView'
  , 'views/render/HomeView'
  , 'jquery.nanoscroller'
  ], function(
    Backbone
  , TitleView
  , TopMenuView
  , LanguageMenuView
  , WordMenuView
  , MapView
  , WordView
  , LanguageView
  , LanguageWordView
  , WordLanguageView
  , ContributorView
  , AboutView
  , HomeView
  ){
  /**
    The Renderer will coordinate an array of views as it's model.
    This hands us a single entry point to trigger rendering if anything changed.
  */
  return Backbone.View.extend({
    initialize: function(){
      //Views managed by the Renderer:
      this.model = {
        titleView:        new TitleView({model: App.translationStorage, el: $('head>title')})
      , topMenuView:      new TopMenuView({el: this.$('#topMenu')})
      , languageMenuView: new LanguageMenuView({el: this.$('#leftMenu')})
      , wordMenuView:     new WordMenuView({el: this.$('#rightMenu')})
      , mapView:          new MapView({el: this.$('#mapViewContainer')})
      , wordView:         new WordView({el: this.$('#wordTableContainer')})
      , languageView:     new LanguageView({el: this.$('#languageTableContainer')})
      , languageWordView: new LanguageWordView({el: this.$('#multitableContainer')})
      , wordLanguageView: new WordLanguageView({el: this.$('#multitableTransposedContainer')})
      , contributorView:  new ContributorView({el: this.$('#contributors')})
      , aboutView: new AboutView({el: this.$('#aboutViewContainer')})
      , homeView: new HomeView({el: this.$('#homeViewContainer')})
      };
      //Flag if models have been activated:
      this._activated = false;
      //Flag if nanoScroller has been initialized:
      this._nano = false;
    }
  , render: function(){
      console.log('Renderer.render()');
      //Activation:
      if(this._activated === false){
        //Installing update methods of contained views:
        _.each(this.model, function(m){
          if(m === null) return;
          if(_.isFunction(m.activate))
            m.activate();
        }, this);
        //Rerender on changing templates:
        App.templateStorage.on('change:partials', this.render, this);
        //Making sure we adjust nanoScrollers on resize/zoom change:
        $(window).resize(function(){
          App.views.renderer.nanoScroller();
        });
        //Renderer is activated now:
        this._activated = true;
      }
      //Updates:
      _.each(this.model, function(m){
        if(_.isEmpty(m)) return;
        var call = (_.isFunction(m.isActive)) ? m.isActive() : true;
        if(call) this.callUpdates(m);
      }, this);
      //Render dependant views:
      _.each(this.model, function(v){
        v.render();
      }, this);
      //We only update the fragment, if we're in one of the 'typical' views.
      if(!App.pageState.isPageView(['c','a','h'])){
        App.router.updateFragment();
      }
      //Triggering nanoScroller updates:
      this.nanoScroller();
      //Making sure studyWatcher is updated :
      App.studyWatcher.update();
    }
    /**
      Makes sure nanoScroller works as expected.
      Computes the height of .nano containers,
      and installed/updated the scrollbars.
      Uses a timeout the first time, to make sure rendering is ok.
    */
  , nanoScroller: function(){
      var height = _.max([$('#contentArea').height(), window.innerHeight - 55])
        , style  = {'min-height': '600px', 'height': height+'px'};
      $('#leftMenu,#rightMenu').each(function(){
        var menuTop = this.getBoundingClientRect().top, $t = $(this);
        $t.find('.nano').each(function(){
          /*
            Using [1] to adjust height based on top style obj.
            [1]: https://stackoverflow.com/a/11396681/448591
          */
          var nanoTop = this.getBoundingClientRect().top
            , nanoCss = {'max-height': height - (nanoTop - menuTop)};
          $(this).css(nanoCss);
        }).nanoScroller();
        $t.css(style);
      });
      if(this._nano === false){
        this._nano = true;
        window.setTimeout(this.nanoScroller, 500);
      }
    }
    /**
      @param v Backbone.View
      Calls all methods that match /^update/ on the given Backbone.View.
    */
  , callUpdates: function(v){
      if(v === null) return;
      _.each(_.keys(v.constructor.prototype), function(k){
        if(k.match(/^update/) !== null){
          v[k].call(v);
        }
      }, this);
    }
  });
});
