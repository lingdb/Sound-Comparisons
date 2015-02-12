/* global LoadingBar: true */
"use strict";
var LoadingBar = Backbone.Model.extend({
  defaults: {
    segments: 0
  , loaded:   0
  , finished: true // Allows to track if loading finished.
  , onFinish: null // [[function, this, args]] - Stack of functions to call if finished.
  }
, initialize: function(){
    //We've got to set onFinish in init, to make sure it isn't shared among different models.
    this.set({onFinish: []});
    //Handling of callbacks on finished:
    this.on('change:finished', this.callbacks, this);
  }
, addSegment: function(s){
    var x = s || 1;
    x += this.get('segments');
    this.set({segments: x});
    return x;
  }
, addLoaded: function(l){
    var x = l || 1;
    x += this.get('loaded');
    x %= this.get('segments');
    this.set({loaded: x, finished: x === 0});
    return x;
  }
/**
  @param f Function
  @param this context
  @param arguments for f
  @return Bool true to be called again.
*/
, onFinish: function(){
    if(_.isEmpty(arguments) || !_.isFunction(arguments[0])) return;
    var stack = this.get('onFinish');
    stack.push(arguments);
    this.set({onFinish: stack});
  }
// Handles all callbacks installed via onFinish, if finished.
, callbacks: function(){
    if(this.get('finished') === true){
      var stack = this.get('onFinish'), again = [];
      _.each(stack, function(entry){
        var f    = entry[0]
          , t    = (entry.length > 1) ? entry[1] : this
          , args = (entry.length > 2) ? _.drop(entry, 2) : [];
        if(f.apply(t, args) === true){
          again.push(entry);
        }
      }, this);
      this.set({onFinish: again, finished: false});
    }
  }
});
