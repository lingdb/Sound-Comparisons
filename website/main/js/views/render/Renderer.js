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
    this.model = [
      new TopMenuView({el: this.$('#topMenu')})
      //FIXME add more views here.
    ];
    //Each model has a segment in the loadingBar, and Renderer itself has two:
    App.loadingBar.addSegment(this.model.length + 2);
  }
, render: function(){
    //First segment of the renderer:
    App.loadingBar.addLoaded();
    //Render dependant views:
    _.each(this.model, function(v){return v.render();}, this);
    //Second segment of the renderer:
    App.loadingBar.addLoaded();
  }
});
