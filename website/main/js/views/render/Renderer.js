/**
  The Renderer will coordinate an array of views as it's model.
  Once Renderer.render is called, the following steps are performed:
  1.: The render methods of the renderers views are called
  2.: The objects, which are expected from the views render methods are merged
  3.: The merged object is used with the App.templateStorage to render the body of the page.
  The .el of the Renderer shall be the body of the site.
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
      new TopMenuView()
      //FIXME add more views here.
    ];
    //Each model has a segment in the loadingBar, and Renderer itself has two:
    App.loadingBar.addSegment(this.model.length + 2);
  }
, render: function(){
    console.log('Renderer.render()');
    //First segment of the renderer:
    App.loadingBar.addLoaded();
    //Gather render objects from views:
    var os = _.map(this.model, function(v){return v.render();});
    console.log(os); // FIXME DEBUG
    //Merge view objects into one:
    var data = $.extend.apply($, os);
    //Render body content:
    this.$el.html(App.templateStorage.render('body', data));
    //Second segment of the renderer:
    App.loadingBar.addLoaded();
  }
});
