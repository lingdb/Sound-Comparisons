/* global navigator: false, App */
/* eslint-disable no-console */
"use strict";
define(['jquery','underscore','backbone','models/Loader'], function($, _, Backbone, Loader){
  var i18n = Loader.translation.i18n;
  //Initializing options for i18n:
  var i18nOptions = {
    resGetPath: Loader.translation.i18nUrl
  , dynamicLoad: true
  , fallbackLng: 'en'
  , useCookie: false
  , useLocalStorage: false
  , lng: 'en' // To be changed to local BrowserMatch
  , load: 'unspecific'
  , preload: [] // To be filled in before i18n.init()
  , shortcutFunction: 'defaultValue'
  };
  //Building and returning Backbone.Model for TranslationStorage:
  return Backbone.Model.extend({
    defaults: {
      ready:         false // Ready as soon, as a translation is usable.
    , summary:       {}    // TranslationId -> Translation
    , nToTMap:       {}    // BrowserMatch  -> Translation
    , translationId: null
    }
    /**
      Cannot be named initialize, because we have to call it once App.dataStorage is available.
      FIXME maybe I can improve this situation together with #182
    */
  , init: function(){
      //Saving translationId on change:
      this.on('change:translationId', this.saveTranslationId, this);
      //Holding attributes up to date:
      this.on('change:summary',  this.mkNToTMap, this);
      /*
        Fetching information from the server works in several steps:
        1.: Get the current summary.
        2.: Figure out desired Translation for the client.
        3.: Set preload Translations
        4.: Initialize i18n
      */
      //Step 1:
      var storage = this;
      Loader.translation.summary().done(function(summary){
        //Setting the summary, and thus triggering mkNToTMap:
        storage.set({'summary': summary});
        //Step 2:
        var desiredTranslation = storage.getTranslationId();
        i18nOptions.lng = summary[desiredTranslation].BrowserMatch;
        //Step 3:
        _.each(storage.getOthers(desiredTranslation), function(tId){
          i18nOptions.preload.push(summary[tId].BrowserMatch);
        });
        //Step 4:
        i18n.init(i18nOptions, function(err){
          if(err === null){
            storage.set({ready: true});
            App.setupBar.addLoaded();
          }else{
            console.log('i18n.init had a problem fetching translations:');
            console.log(err);
            window.alert('Problem fetching translations, try again?');
          }
        });
      }).fail(function(){
        console.log('Could not fetch translation summary from host.');
      });
    }
    /**
      Gets executed via Backbone event system, every time that TranslationStorage summary changes.
      Updates the nToTMap.
    */
  , mkNToTMap: function(){
      try {
        var map = {};
        _.each(this.get('summary'), function(t){
          map[t.BrowserMatch] = t;
        }, this);
        this.set({nToTMap: map});
      } catch(e) {
        // this will mostly happen if a stored translationId (as cookie) 
        // is invalid, i.e. the Id doesn't exist anymore -> emergency exit := use default language
        var tId = this.defaultTranslationId();
        //Making sure tId is a number:
        if(_.isString(tId)){
          tId = parseInt(tId);
        }
        // save translationId as cookie
        var d = new Date();
        d.setTime(d.getTime() + (365*24*60*60*1000));
        var expires = "expires="+ d.toUTCString();
        document.cookie = 'translationId='+ App.storage.translationId + ";" + expires + ";path=/";
        //Setting the translationId, and returning:
        this.set({translationId: tId});
      }
    }
    /**
      @param bm String, BrowserMatch
      @return translation Object, Translation || null
      Tries to return the translation that corresponds to the given BrowserMatch.
    */
  , translationFromBrowserMatch: function(bm){
      var map = this.get('nToTMap');
      if(bm in map){
        return map[bm];
      }
      return null;
    }
    /**
      function to produce the default TranslationId
      for all occurences, to enable easy changing,
      and have it as a nice constant.
    */
  , defaultTranslationId: function(){return 1;}
    /**
      Saving the current TranslationId to App.storage:
    */
  , saveTranslationId: function(){
      App.storage.translationId = this.get('translationId');
      // save translationId as cookie
      var d = new Date();
      d.setTime(d.getTime() + (365*24*60*60*1000));
      var expires = "expires="+ d.toUTCString();
      document.cookie = 'translationId='+ App.storage.translationId + ";" + expires + ";path=/";
    }
    /**
      Figuring out the TranslationId of a client,
      works in multiple steps:
      0.: If we already figured out the TranslationId,
          we use the one given with this model.
      1.: If TranslationId is known from App.storage, we use that.
      2.: We see if the browser language matches a particular translation summary.
      3.: We fall back to the defaultTranslationId.
    */
  , getTranslationId: function(){
      var tId = this.get('translationId');
      if(tId !== null) return tId;

      //Look for a stored ID within the browser's cookies
      var nameTransID = "translationId=";
      var ca = document.cookie.split(';');
      for(var i = 0; i <ca.length; i++) {
          var c = ca[i];
          while (c.charAt(0) == ' ') {
              c = c.substring(1);
          }
          if (c.indexOf(nameTransID) == 0) {
              tId = parseInt(c.substring(nameTransID.length, c.length), 10);
              break;
          }
      }
      if(tId === null) {
        //Is the translationId known from App.storage?
        if('translationId' in App.storage){
          tId = App.storage.translationId;
        }else{
          //Finding the translationId via browser language:
          var lang = navigator.language || navigator.userLanguage
            , summary = _.find(this.get('summary'), function(s){
              var index = lang.indexOf(s.BrowserMatch);
              return index >= 0;
            }, this);
          if(summary) tId = summary.TranslationId;
        }
      }
      //Defaulting translationId:
      if(tId === null) tId = this.defaultTranslationId();
      //Making sure tId is a number:
      if(_.isString(tId)){
        tId = parseInt(tId);
      }
      // save translationId as cookie
      var d = new Date();
      d.setTime(d.getTime() + (365*24*60*60*1000));
      var expires = "expires="+ d.toUTCString();
      document.cookie = 'translationId='+ App.storage.translationId + ";" + expires + ";path=/";
      //Setting the translationId, and returning:
      this.set({translationId: tId});
      return tId;
    }
    /**
      @return tIds [TranslationId]
      Returns an array of TranslationIds that can be tried to try
      and get a translation for something.
      Usually this will be [tId, defaultTid].
    */
  , getTranslationIds: function(){
      var tIds = [this.getTranslationId(), this.defaultTranslationId()];
      return _.unique(_.map(tIds, function(t){
        if(_.isString(t)) return parseInt(t);
        return t;
      }));
    }
    /**
      @param tId TranslationId
      Set the translation using a given TranslationId.
      Returns a promise to check if i18n loading completed.
      This will most likely succeed immediately, because of preload.
    */
  , setTranslationId: function(tId){
      var prom = $.Deferred();
      //Checking if tId is different than current translationId:
      if(tId == this.getTranslationId()){
        //Produce accepting promise as tId is already set:
        prom.resolve();
        return prom;
      }
      //Checking if given tId is known at all::
      var summary = this.get('summary');
      if(!(tId in summary)){
        //Produce failing promise as tId is not okay:
        prom.reject('Cannot TranslationStorage.setTranslationId('+tId+')');
        return prom;
      }
      //Updating translationId:
      var bm = summary[tId].BrowserMatch;
      i18n.setLng(bm, function(err){
        if(err === null){
          prom.resolve();
        }else{
          prom.reject(err);
        }
      });
      this.set({translationId: tId});
      return prom;
    }
    /**
      @param bm String, BrowserMatch || TranslationId
      @return prom Deferred
      Set the translation using a given BrowserMatch.
      Returns a promise to check if i18n loading completed.
      This will most likely succeed immediately, because of preload.
      Also accepts a TranslationId instead of a BrowserMatch to aid routing.
    */
  , setTranslation: function(bm){
      var t = this.translationFromBrowserMatch(bm);
      if(t === null){
        //Maybe our 'BrowserMatch' is a TranslationId?
        return this.setTranslationId(bm);
      }
      return this.setTranslationId(t.TranslationId);
    }
    /**
      @param req
        req is a String -> String translation will be returned
        req is an Array -> translateStatic is mapped over array, array returned
        req is an Object -> translateStatic is mapped over values, object returned
    */
  , translateStatic: function(req){
      if(_.isObject(req)){
        if(_.isArray(req)){
          return _.map(req, this.translateStatic, this);
        }
        _.each(req, function(v,k){
          req[k] = this.translateStatic(v);
        }, this);
        return req;
      }
      var data = i18n.t(req);
      if(_.isUndefined(data) || data === req){
        console.log('data is '+data+' in translateStatic!');
        return 'translateStatic(FIXME)!';
      }
      return data;
    }
  //Dynamic translations:
  , translateDynamic: function(category, field, fallback){
      var key = category+field
        , ret = i18n.t(category+field, fallback);
      if(ret === key || _.isUndefined(ret)){
        //FIXME re-enable below log!
        //console.log('Could not find translation:\n'+key);
        return fallback;
      }
      return ret;
    }
    /**
      @param [tId TranslationId]
      @return path String, ImagePath
      Method to produce the path to the flag for the current translation.
    */
  , getFlag: function(tId){
      tId = tId || this.getTranslationId();
      var summary = this.get('summary');
      return summary[tId].ImagePath;
    }
    /**
      @param [tId TranslationId]
      @return name String
      Returns the name of the current translation.
    */
  , getName: function(tId){
      tId = tId || this.getTranslationId();
      var summary = this.get('summary');
      return summary[tId].TranslationName;
    }
    /**
      @param [tId TranslationId]
      @return tId [TranslationId]
      Returns all translationIds that are not the current or the one passed as parameter.
    */
  , getOthers: function(tId){
      tId = tId || this.getTranslationId();
      var ret = [];
      _.each(_.keys(this.get('summary')), function(t){
        if(_.isString(t))
          t = parseInt(t);
        if(t !== tId)
          ret.push(t);
      }, this);
      return ret;
    }
    /**
      @return language Language || null
      Returns the RfcLanguage for the current translationId, or null.
    */
  , getRfcLanguage: function(){
      var tId   = this.getTranslationId()
        , query = {LanguageIx: this.get('summary')[tId].RfcLanguage};
      return App.languageCollection.findWhere(query) || null;
    }
    /**
      @param [tId TranslationId]
      @return b String, BrowserMatch
      Returns the BrowserMatch for the current translation.
    */
  , getBrowserMatch: function(tId){
      tId = tId || this.getTranslationId();
      return this.get('summary')[tId].BrowserMatch;
    }
    /**
      @param bm String BrowserMatch
      @return is Bool
    */
  , isBrowserMatch: function(bm){
      return (bm in this.get('nToTMap'));
    }
    /**
      @param trans String
      @param values [String]
      @return String
      Replaces occurences of $1,$2,… with the according fields from values.
      Only the first occurence of $i is replaced.
    */
  , placeInTranslation: function(trans, values){
      if(_.isString(trans)){
        _.each(values, function(v,i){
          trans = trans.replace(new RegExp("\\$"+(i+1)), v);
        }, this);
        return trans;
      }
      console.log('Fail in TranslationStorage.placeInTranslation();');
      return '';
    }
  });
});
