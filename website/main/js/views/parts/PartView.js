/*
  el:    TopMenu
  model: TemplateStorage
*/
PartView = Backbone.View.extend({
  initialize: function(){
    window.App.linkInterceptor.on('change:url', this.update, this);
    window.App.views.loadingBar.addSegment(2);
    this.replaceEl();
  }
, update: function(interceptor){
    var url  = 'query/sitePart'
             + interceptor.get('url')
             + '&part=' + this.part
      , view = this;
    window.App.views.loadingBar.addLoaded();
    $.getJSON(url, function(data){
      window.App.views.loadingBar.addLoaded();
      view.render(data);
    });
  }
, render: function(data){
    var html = this.model.render(this.part, data);
    this.$el.replaceWith(html);
    this.replaceEl();
    window.App.linkInterceptor.findLinks(this.$el);
  }
});
