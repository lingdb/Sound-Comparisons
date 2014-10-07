"use strict";
/**
  Given that query/data allows us to fetch individual studies,
  it appears helpful, to keep the data in App.storage.
  We distinguish between study related data and global data.
  Study related data can/will be cleaned out once the space limit is reached,
  while global data will only be overwritten.
*/
var DataStorage = Backbone.Model.extend({
  defaults: {
    global: null
  , lastUpdate: 0
  , study: null
  , target: 'query/data'
  }
  /**
    initialize accounts for 3 segments of App.setupBar
  */
, initialize: function(){
    //Self dependant events:
    this.on('change:global', this.saveGlobal, this);
    this.on('change:study', this.saveStudy, this);
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
      delete data['Description'];
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
  /*
    Cleans up App.storage a little to see that we get some space back.
    Returns true iff some cleanup was performed.
    We work in two stages here:
    1.: Try to find outdated studies and remove them.
    2.: Find studies different from the current one,
        and remove them.
    We only perform stage 2 cleanup, iff stage 1 didn't free any space.
  */
, collectGarbadge: function(){
    //Setup:
    var collected = false
      , current   = this.get('study')
      , studies   = this.get('global').studies
      , timestamp = this.get('lastUpdate');
    //We only want not-current studies:
    studies = _.filter(studies, function(study){
      return study !== current.study.Name;
    }, this);
    //Stage 1:
    _.each(studies, function(study){
      var key = "Study_"+study
        , s   = this.load("Study_"+study);
      if(s && s.timestamp < timestamp){
        console.log('DataStorage.collectGarbadge() outdated: '+study);
        delete App.storage[key];
        collected = true;
      }
    }, this);
    if(collected) return true;
    //Stage 2:
    _.each(studies, function(study){
      var key = "Study_"+study;
      if(key in App.storage){
        console.log('DataStorage.collectGarbadge() unused: '+study);
        delete App.storage[key];
        collected = true;
      }
    }, this);
    return collected;
  }
  /**
    The generalized save function of DataStorage, it handles compression and the key name.
    This method triggers collectGarbadge, iff storing doesn't work as it should,
    so that hopefully some storage will be freed, and storing can occur anyway.
  */
, save: function(name, data){
    var key   = "DataStorage_"+name
      , msg   = {label: 'save'+key, data: data, task: 'compress'}
      , saved = false, def = $.Deferred();
    if(this.compressor){
      this.onCompressor(msg.label, function(m){
        msg.data = m.data;
        def.resolve();
      }, this);
      this.compressor.postMessage(msg);
    }else{
      msg.data = LZString.compressToBase64(JSON.stringify(msg.data));
      def.resolve();
    }
    def.done(function(){
      do{
        try{
          App.storage[key] = msg.data;
          saved = true;
        }catch(e){
          //We cancel saving and say it's true, iff we couldn't free any space:
          saved = App.dataStorage.collectGarbadge() ? false : true;
        }
      }while(saved !== true);
    });
  }
  /***/
, saveGlobal: function(){
    this.save('global', this.get('global'));
  }
  /***/
, saveStudy: function(){
    var data = this.get('study')
      , name = "Study_"+data.study.Name;
    this.save(name, data);
  }
  /**
    The generalized load function of DataStorage, it handles compression and the key name.
    The more specialized load functions only bother the server,
    iff nothing is found by the load function,
    or the information given by load is outdated.
  */
, load: function(name){
    var key = "DataStorage_"+name, def = $.Deferred();
    if(key in App.storage){
      var msg = {label: 'load:'+key, data: App.storage[key], task: 'decompress'};
      if(this.compressor){
        this.onCompressor(msg.label, function(m){
          def.resolve(m.data);
        }, this);
        this.compressor.postMessage(msg);
      }else{
        def.resolve($.parseJSON(LZString.decompressFromBase64(msg.data)));
      }
    }else{
      def.resolve(null);
    }
    return def;
  }
  /***/
, loadGlobal: function(){
    var timestamp = this.get('lastUpdate')
      , current   = this.load('global')
      , promise   = $.Deferred(), t = this;
    current.done(function(c){
      if(c === null || c.timestamp < timestamp){
        $.getJSON(t.get('target'), {global: null}).done(function(data){
          data.timestamp = timestamp;
          t.set({global: data});
          promise.resolve();
        }).fail(function(f){
          promise.reject(f);
        });
      }else{
        t.set({global: c});
        promise.resolve();
      }
    });
    return promise;
  }
  /***/
, loadStudy: function(name){
    name = name || App.studyWatcher.get('study');
    var key       = "Study_"+name
      , study     = this.load(key)
      , timestamp = this.get('lastUpdate')
      , promise   = $.Deferred(), t = this;
    study.done(function(s){
      if(!s || s.timestamp < timestamp){
        $.getJSON(t.get('target'), {study: name}).done(function(data){
          data.timestamp = timestamp;
          t.set({study: data});
          promise.resolve();
        }).fail(function(f){
          promise.reject(f);
        });
      }else{
        t.set({study: s});
        promise.resolve();
      }
    });
    return promise;
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
