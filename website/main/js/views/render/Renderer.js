/**
  The Renderer will coordinate an array of views as it's model.
  This hands us a single entry point to trigger rendering if anything changed.
*/
Renderer = Backbone.View.extend({
  initialize: function(){
    //Makeing sure we render once setup finishes:
    App.setupBar.onFinish(function(){
      this.render();
      return false;
    }, this);
    //Views managed by the Renderer:
    this.model = {
      topMenuView:      new TopMenuView({el: this.$('#topMenu')})
    , languageMenuView: new LanguageMenuView({el: this.$('#leftMenu')})
    , wordMenuView:     new WordMenuView({el: this.$('#rightMenu')})
    , mapView:          null
    , wordView:         new WordView({el: this.$('#wordTableContainer')})
    , languageView:     new LanguageView({el: this.$('#languageTableContainer')})
    , languageWordView: new LanguageWordView({el: this.$('#multitableContainer')})
    , wordLanguageView: null
    };
    //Each model has a segment in the loadingBar, and Renderer itself has two:
    App.loadingBar.addSegment(this.model.length + 2);
    //Memoization wether models have been activated:
    this._activated = false;
  }
, render: function(){
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
    _.each(this.model, this.callUpdates, this);
    //First segment of the renderer:
    App.loadingBar.addLoaded();
    //Render dependant views:
    _.each(this.model, function(v){
      if(v === null) return;
      return v.render();
    }, this);
    //Second segment of the renderer:
    App.loadingBar.addLoaded();
  }
  /**
    Calls all methods that match /^update/ on the given Backbone.View.
  */
, callUpdates: function(v){
    if(v === null) return;
    _.each(_.keys(v.__proto__), function(k){
      if(k.match(/^update/) !== null){
        v[k].call(v);
      }
    }, this);
  }
});
