/* global Renderer: true */
"use strict";
/**
  The Renderer will coordinate an array of views as it's model.
  This hands us a single entry point to trigger rendering if anything changed.
*/
var Renderer = Backbone.View.extend({
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
      //Updating fragment:
      (function(r){
        var fragment = r.linkCurrent({config: r.getConfig()});
        r.navigate(fragment, {trigger: false});
        App.study.trackLinks(fragment);
      })(App.router);
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
  })
});
