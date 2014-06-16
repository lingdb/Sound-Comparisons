/*
  el:    determined by child
  model: TemplateStorage
*/
PartView = Backbone.View.extend({
  initialize: function(){
    window.App.linkInterceptor.on('change:url', this.update, this);
    window.App.loadingBar.addSegment(2);
    this.replaceEl();
  }
, update: function(interceptor){
    var url  = 'query/sitePart'
             + interceptor.get('url')
             + '&part=' + this.part
             + interceptor.get('fragment')
      , view = this;
    window.App.loadingBar.addLoaded();
    $.getJSON(url, function(data){
      view.render(data);
      window.App.loadingBar.addLoaded();
    }).fail(function(e){
      console.log('Failed in PartView:' + JSON.stringify(e));
      window.App.loadingBar.addLoaded();
    });
  }
, render: function(data){
    var html = this.model.render(this.part, data);
    this.$el.replaceWith(html);
    var fn = this.replaceEl();
    //Updating link interceptor:
    window.App.linkInterceptor.findLinks(this.$el);
    //Updating sound listeners:
    window.App.views.audioLogic.findAudio(this.$el);
    //Updates required by replaceEl:
    if(typeof(fn) === "function") fn();
  }
// Children have to implement replaceEl.
// replaceEl may return a function that will be called at the end of render.
// Childten also must have a this.part that is a string.
});
