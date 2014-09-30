"use strict";
/*
  The LinkInterceptor identifies links once the TemplateStorage is ready,
  and can than be used to intercept loading their original localtion,
  but instead trigger various updates to different site parts,
  that are executed by the views.
  The LinkInterceptor has a task that is somehow between a model and a view,
  but for practical reasons I understand it as a model.
*/
var LinkInterceptor = Backbone.Model.extend({
  defaults: {
    url: null // Changes everytime a link is clicked. Can be used to listen to LinkInterceptor.
  , fragment: '' // Should be appended to the url, will have a leading '#' if not ''.
  , enabled: true // Will be switched to false once something crucial fails.
  }
, initialize: function(){
    this.findLinks($('body'));
  }
, findLinks: function(tgt){
    var interceptor = this;
    //Typical links:
    tgt.find('a[href]').each(function(){
      var lnk = $(this);
      lnk.click(interceptor.linkProcessor(lnk.attr('href')));
    });
    //Special links:
    tgt.find('[data-href]').each(function(){
      var lnk = $(this);
      lnk.click(interceptor.linkProcessor(lnk.attr('data-href')));
    });
    //Select links:
    tgt.find('#wordlistfilter select').change(function(){
      var href = $('option:selected', this).attr('data-href');
      (interceptor.linkProcessor(href))();
    });

  }
/*
  @param href target to navigate to.
  @return function to call to trigger navigation.
*/
, linkProcessor: function(href){
    //Consider only links that start with get parameters and are for the current site:
    if(!/^\?/.test(href)) return;
    //Split the href into url and fragment:
    var parts = href.match(/^(\?[^#]*)(#?.*)/), frag = '';
    if(parts.length === 3){//We have a fragment.
      href = parts[1];
      frag = parts[2];
    }
    //On Click prevent usual behaviour, and intercept:
    var interceptor = this;
    return function(e){
      //Only intercept if enabled, and there are listeners:
      if(!interceptor.get('enabled') || !interceptor.hasListeners()){
        window.location.href = href; // This is necessary for data-href cases
        return;
      }
      //No once expects the spanish interception!
      if(e) e.preventDefault();
      //Logging if possible:
      window.App.logger.logLink(href);
      //Updating the PageWatcher:
      window.App.pageWatcher.update(href, frag);
      //Setting the url, so that event listeners fire.
      interceptor.set({url: href, fragment: frag});
    };
  }
, hasListeners: function(){
    if(!this._events) return false;
    return _.keys(this._events).length > 0;
  }
, navigate: function(href){
    var call = this.linkProcessor(href);
    if(_.isFunction(call)) call();
    return this;
  }
});
