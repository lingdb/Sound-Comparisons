/**
  The PageWatcher has two main tasks:
  1.: Update the page history and url to keep things clear for the user
  2.: Dissect the url into several parts that can be analyzed independantly
*/
PageWatcher = Backbone.Model.extend({
  defaults: { // Example state of the site to be overwritten on init.
    hl: 'en'
  , languages: []
  , words: []
  , pageView: 'languageView'
  , study: 'Germanic'
  , wo_order: 'alphabetical'
  , wo_phLang: 'RP'
  , wo_spLang: 'RP'
  }
, initialize: function(){
    //Watching for events to log:
    this.countMap = {}; // Used for logging.
    this.on('change', this.log, this);
    //Initial parsing of the location:
    //Could aswell use window.location.href,
    //but it's not that detailed in the beginning.
    this.parseHref($('#saveLocation').attr('href'));
    //FIXME this should parse default values from global.
  }
, update: function(href, fragment){
    if(typeof(window.history) !== 'object') return;
    window.history.pushState('', 'On site navigation', href+fragment);
    this.parseHref(href);
  }
, parseHref: function(href){
    if(href === null || typeof(href) === 'undefined'){
      console.log('PageWatcher.parseHref() called with empty href.');
      return;
    }
    //Parsing parameters:
    var params = {};
    _.each(href.split('?')[1].split('&'), function(p){
      var parts = p.split('=');
      if(parts.length === 2){
        params[parts[0]] = parts[1];
      }else if(parts.length === 1){
        params[parts[0]] = true;
      }
    }, this);
    //Dissecting arrays:
    _.each(['words','languages'], function(k){
      if(k in params){
        params[k] = params[k].split(',');
      }
    }, this);
    //Saving:
    this.set(params);
  }
, getLocation: function(){
    var href = window.location.href;
    return '?' + href.split('?')[1];
  }
//Logs the complete page as events.
, log: function(){
    _.each(this.attributes, function(v, k){
      //View all values as arrays:
      var vs = _.isArray(v) ? v : [v];
      //Iterate values:
      _.each(vs, function(v){
        var key = k + v;
        var count = (this.countMap[key] || 0 ) + 1;
        this.countMap[key] = count;
        if(window.App && window.App.logger){
          window.App.logger.logEvent('PageWatcher', k, v, count);
        }else{//Gotta use some interval:
          var ref = window.setInterval(function(){
            if(window.App && window.App.logger){
              window.clearInterval(ref);
              window.App.logger.logEvent('PageWatcher', k, v, count);
            }
          }, 500);
        }
      }, this);
    }, this);
  }
});
