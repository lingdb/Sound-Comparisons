/*
  This view listens for changes to and from whoAreWe view,
  and does the following things:
  1.: If there was a switch to whoAreWe view, target the anchored contributor,
      to highlight it for the client.
  model: PageWatcher
*/
WhoAreWeView = Backbone.View.extend({
  initialize: function(){
  //FIXME substitute this for something!
  //this.model.on('change:pageView', this.render, this);
  }
, render: function(){
    var pv = this.model.get('pageView');
    if(pv === 'whoAreWe'){
      window.App.loadingBar.onFinish(function(){
        var frag = window.App.linkInterceptor.get('fragment');
        if(/^#/.test(frag)){ // Make sure we can match with this.
          window.location.hash = '#';
          window.location.hash = frag;
        }
      }, this);
    }
  }
});
