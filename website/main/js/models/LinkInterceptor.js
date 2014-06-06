/*
  The LinkInterceptor identifies links once the TemplateStorage is ready,
  and can than be used to intercept loading their original localtion,
  but instead trigger various updates to different site parts,
  that are executed by the views.
  The LinkInterceptor has a task that is somehow between a model and a view,
  but for practical reasons I understand it as a model.
*/
LinkInterceptor = Backbone.Model.extend({
  defaults: {
    url: null // Changes everytime a link is clicked. Can be used to listen to LinkInterceptor.
  }
, initialize: function(){
    this.findLinks($('body'));
  }
, findLinks: function(tgt){
    var interceptor = this;
    tgt.find('a[href]').each(function(){
      var link = $(this), href = link.attr('href');
      //Consider only links that start with get parameters and are for the current site:
      if(!/^\?/.test(href)) return;
      //On Click prevent usual behaviour, and intercept:
      link.click(function(e){
        //Only intercept if someone cares:
        if(!interceptor.hasListeners()) return;
        //No once expects the spanish interception!
        e.preventDefault();
        interceptor.set({url: href});
      });
    });
  }
, hasListeners: function(){
    if(!this._events) return false;
    return _.keys(this._events).length > 0;
  }
});
