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
      //Map name -> Promise for DataStorage.loadStudy()
      this._loadStudyMap = {};
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
      var def = $.Deferred(), t = this;
      $.getJSON(t.get('target'), {global: null}).done(function(data){
        t.set({global: data});
        def.resolve();
      }).fail(function(f){
        def.reject(f);
      });
      return def.promise();
    }
    /**
      @param [name String]
      @return def Deferred
      This method makes use of this._loadStudyMap
      to make sure that the same study isn't requested twice at the same time.
      The name parameter may be omitted and will be replaced with the current study
      according to the StudyWatcher.
      If Study.isReady() is true, this method tests if name
      is different than the current study id.
    */
  , loadStudy: function(name){
      //Checking name parameter:
      name = name || App.studyWatcher.get('study');
      //Setup:
      var key = "Study_"+name
        , def = $.Deferred(), t = this;
      //Study already getting loaded:
      if(name in this._loadStudyMap){
        return this._loadStudyMap[name];
      }
      //Prevent reloading current study:
      if(App.study.isReady()){
        if(App.study.getId() === name){
          def.resolve();
          return def.promise();
        }
      }
      //Adding def to _loadStudyMap:
      this._loadStudyMap[name] = def;
      /*
        Fetching study:
        delete is not in always to make sure it happens
        before further propagation.
      */
      $.getJSON(t.get('target'), {study: name}).done(function(data){
        delete t._loadStudyMap[name];
        t.set({study: data});
        def.resolve();
      }).fail(function(f){
        delete t._loadStudyMap[name];
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
    /**
      @param [fragment String]
      @return Promise -> [{label: String, url: String}]
      Creates a (new) shortlink for the given fragment.
      If no fragment is given, Linker.linkCurrentConfig() will be used to obtain one.
      A promise is returned that will be resolved once the operation succeeds.
      Resolve parameter will be an array holding lables and corresponding urls.
      If the promise is resolved, the new key-value-pair will also already be inserted in the DataStorage.getShortLinksMap().
      The promise may be rejected iff the post fails.
    */
  , addShortLink: function(fragment){
      var def = $.Deferred(), t = this
        , prefix = App.router.getBaseUrl()+'#/sl';
      //Sanitizing fragment:
      fragment = _.isString(fragment)
               ? fragment : App.router.linkCurrentConfig();
      var query = {createShortLink: fragment};
      $.post('query/shortLink', query, function(data){
        //Entry maps key to fragment.
        var entry = {};
        entry[data.str] = data.url;
        //Adding entry to ShortLinksMap:
        _.extend(t.getShortLinksMap(), entry);
        //Resolving promise:
        var shortLink = App.study.getId()+'/'+data.str
          , tStorage = App.translationStorage
          , resolve = _.map(tStorage.getTranslationIds(), function(tId){
            return {
              label: tStorage.getName(tId)
            , url: [prefix, tStorage.getBrowserMatch(tId), shortLink].join('/')
            };
          }, this);
        resolve.push({label: 'default', url: prefix+'/'+shortLink});
        def.resolve(resolve);
      }, 'json').fail(function(){
        def.reject();
      });
      return def.promise();
    }
  });
});
