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
      , mapView:          new MapView(this.SubView)({el: this.$('#mapViewContainer')})
      , wordView:         new WordView(this.SubView)({el: this.$('#wordTableContainer')})
      , languageView:     new LanguageView(this.SubView)({el: this.$('#languageTableContainer')})
      , languageWordView: new LanguageWordView(this.SubView)({el: this.$('#multitableContainer')})
      , wordLanguageView: new WordLanguageView(this.SubView)({el: this.$('#multitableTransposedContainer')})
      , contributorView:  new ContributorView(this.SubView)({el: this.$('#contributors')})
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
          if(typeof(m.activate) === 'function')
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
      if(!App.pageState.isPageView('c')){
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
      var height = _.max([$('#contentArea').height(), window.innerHeight - 55])+'px';
      $('#leftMenu,#rightMenu').each(function(){
        $(this).css({'min-height': '600px', 'height': height});
      }).nanoScroller();
      if(this._nano === false){
        this._nano = true;
        window.setTimeout(this.nanoScroller, 500);
      }
    }
    /**
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
    /**
      To be extended by views that are used as models for the Renderer.
    */
  , SubView: Backbone.View.extend({
      getKey: function(){throw 'Renderer.SubView:getKey should be overwritten!';}
    , render: function(){throw 'Renderer.SubView:render should be overwritten!';}
    , isActive: function(){return App.pageState.isPageView(this);}
    /**
      Function to be called when loading a study failes.
      Default implementation will cause a translated alert
      explaining the situation.
      Afterwards the SubView may do what it likes.
    */
    , noStudy: function(study){
        var t = App.translationStorage, msg = t.translateStatic('failedFetchStudy');
        window.alert(t.placeInTranslation(msg, [study]));
      }
    })
  });
});
