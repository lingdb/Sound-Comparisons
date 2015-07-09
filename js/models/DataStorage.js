"use strict";
define(['backbone'], function(Backbone){
  /**
    Given that query/data allows us to fetch individual studies,
    it appears helpful, to keep the data in App.storage.
    We distinguish between study related data and global data.
    Study related data can/will be cleaned out once the space limit is reached,
    while global data will only be overwritten.
  */
  return Backbone.Model.extend({
    defaults: {
      global: null
    , lastUpdate: 0 // timestamp aquired by fetching target in initialize
    , study: null
    , target: 'query/data'
    }
    /**
      initialize accounts for 3 segments of App.setupBar
    */
  , initialize: function(){
      //Compressor setup, if possible:
      this.compressor = null;
      if(_.isFunction(window.Worker)){
        this.compressor = new Worker('js/worker/Compressor.js');
        this.set({compressorCallbacks: {}}); // Label -> [[callback, context]]
        this.compressor.onmessage = function(m){
          App.dataStorage.handleCompressor(m);
        };
        this.compressor.onerror = function(e){
          console.log('DataStorage.compressor.onerror:');
          console.log(e);
        };
      }
      //Fetching initial data from target:
      var t = this;
      $.getJSON(this.get('target')).done(function(data){
        delete data.Description;
        console.log("Got target data: "+JSON.stringify(data));
        App.setupBar.addLoaded();
        t.set(data);
        t.loadGlobal().done(function(){
          App.setupBar.addLoaded();
          t.loadStudy().always(function(){
            console.log('DataStorage.loadstudy() done with setup.');
            App.setupBar.addLoaded();
          });
        }).fail(function(){
          console.log('DataStorage.loadGlobal() done with setup.');
          App.setupBar.addLoaded(2);
        });
      }).fail(function(){
        console.log('DataStorage done with setup.');
        App.setupBar.addLoaded(3);
      });
    }
    /***/
  , loadGlobal: function(){
      var promise = $.Deferred(), t = this;
      $.getJSON(t.get('target'), {global: null}).done(function(data){
        t.set({global: data});
        promise.resolve();
      }).fail(function(f){
        promise.reject(f);
      });
      return promise;
    }
    /***/
  , loadStudy: function(name){
      name = name || App.studyWatcher.get('study');
      var key = "Study_"+name
        , def = $.Deferred(), t = this;
      $.getJSON(t.get('target'), {study: name}).done(function(data){
        t.set({study: data});
        def.resolve();
      }).fail(function(f){
        def.reject(f);
      });
      return def.promise();
    }
    /***/
  , getWikipediaLinks: function(){
      return this.get('global').global.wikipediaLinks;
    }
    /**
      Handles a message from the Compressor by executing all functions on the stack for the given label once,
      handing them the data parameter of the received message.
      The stack for the according label is clean afterwards.
    */
  , handleCompressor: function(e){
      var msg = e.data, cbks = this.get('compressorCallbacks');
      if(msg.label in cbks){
        var stack = cbks[msg.label];
        if(_.isArray(stack) && !_.isEmpty(stack)){
          _.each(stack, function(cbk){
            cbk[0].call(cbk[1], msg);
          }, this);
        }
        cbks[msg.label] = [];
      }
    }
    /**
      Sets up a given function and context to be called once the Compressor finishes for a given label.
    */
  , onCompressor: function(label, func, ctx){
      if(this.compressor !== null){
        var cbks = this.get('compressorCallbacks');
        if(!(label in cbks)) cbks[label] = [];
        cbks[label].push([func,ctx]);
      }
    }
    /**
      Returns a map from ShortLink names to URLs
    */
  , getShortLinksMap: function(){
      return this.get('global').global.shortLinks;
    }
  });
});
