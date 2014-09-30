"use strict";
var Logger = Backbone.Model.extend({
  defaults: {
    stack: [] // Actions to perform later on.
  }
//Performs all actions on the stack and returns how many actions processed.
, runStack: function(){
    var s = this.get('stack');
    _.each(s, function(pair){
      var fn = pair[0], params = pair[1];
      fn.apply(this, params);
    }, this);
    this.set({stack: []});
    return s.length;
  }
//Checks, if ga is available, and if so runs stack.
, ready: function(){
    var rdy = (typeof(window.ga) === "function");
    if(rdy) this.runStack();
    return rdy;
  }
//Pushes a function with params to the stack, returns this for chaining
, pushStack: function(fn, params){
    var s = this.get('stack');
    s.push([fn, params]);
    this.set({stack: s});
    return this;
  }
//Logs a href by pushing it on the stack, processing the stack if possible, and returning a bool if logged.
, logLink: function(href){
    this.pushStack(function(h){
      window.ga('send', 'pageview', h);
    }, [href]);
    return this.ready();
  }
, logEvent: function(category, action, label, value){
    //See https://developers.google.com/analytics/devguides/collection/analyticsjs/events
    this.pushStack(function(c,a,l,v){
      if(typeof(v) === 'number'){
        ga('send', 'event', c, a, l, v);
      }else{
        ga('send', 'event', c, a, l);
      }
    }, [category, action, label, value]);
    return this.ready();
  }
});
