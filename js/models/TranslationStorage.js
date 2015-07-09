/* global navigator: false */
"use strict";
define(['backbone'], function(Backbone){
  return Backbone.Model.extend({
    defaults: {
      ready:        false // Ready as soon, as a translation is usable.
    , summary:      {}    // TranslationId -> Translation
    , statics:      {}    // TranslationId -> Req -> Trans
    , dynamics:     {}    // TranslationId -> [dynamics]
    , nToTMap:      {}    // BrowserMatch  -> Translation
    , cToDMap:      {}    // TranslationId -> Category -> [dynamics]
    , fToDMap:      {}    // TranslationId -> Field    -> [dynamics]
    , translationId: null
    }
    /**
      Cannot be named initialize, bacause we have to call it once App.dataStorage is available.
    */
  , init: function(){
      //Saving translationId on change:
      this.on('change:translationId', this.saveTranslationId, this);
      //Holding attributes up to date:
      this.on('change:summary',  this.mkNToTMap, this);
      this.on('change:dynamics', this.mkCToDMap, this);
      this.on('change:dynamics', this.mkFToDMap, this);
      /*
        Fetching information from the server works in several steps:
        1.: Get the current summary, iterate all existing translations.
        For iteration:
        2.: Fetch static translations
        3.: Fetch dynamic translations
      */
      //Step 1:
      var storage = this;
      $.getJSON('query/translations', {action: 'summary'}).done(function(summary){
        storage.set({'summary': summary});
        var  promises = [], fetchError = false;
        _.each(summary, function(translation){
          var tId = translation.TranslationId;
          //Step 2:
          var promStatic = $.getJSON('query/translations', {action: 'static', translationId: tId}).done(function(elems){
            var stats  = storage.get('statics');
            stats[tId] = elems;
            storage.set({statics: stats});
          }).fail(function(){fetchError = true;});
          //Step 3:
          var promDynamic = $.getJSON('query/translations', {action: 'dynamic', translationId: tId}).done(function(elems){
            var stats  = storage.get('dynamics');
            stats[tId] = elems;
            storage.set({dynamics: stats});
            storage.trigger('change:dynamics');
          }).fail(function(){fetchError = true;});
          //Push promises
          promises.push(promStatic, promDynamic);
        }, storage);
        //Once fetches are completed, we acknowledge progress:
        $.when.apply($, promises).done(function(){
          App.setupBar.addLoaded();
        }).always(function(){
          if(fetchError){
            console.log('TranslationStorage.init could not fetch all translations!');
            window.alert('Problem fetching translations, try again?');
          }else{
            storage.set({ready: true});
          }
        });
      }).fail(function(){
        App.linkInterceptor.set({enabled: false});
        console.log('Could not fetch translation summary from host -> LinkInterceptor disabled.');
      });
    }
  , mkNToTMap: function(){
      var map = {};
      _.each(this.get('summary'), function(t){
        map[t.BrowserMatch] = t;
      }, this);
      this.set({nToTMap: map});
    }
  , translationFromBrowserMatch: function(bm){
      return this.get('nToTMap')[bm];
    }
  , mkCToDMap: function(){
      var map = {};
      _.each(this.get('dynamics'), function(ds, tId){
        var cMap = {};
        _.each(ds, function(d){
          var c = d.Category;
          if(c in cMap){
            cMap[c].push(d);
          }else{
            cMap[c] = [d];
          }
        }, this);
        map[tId] = cMap;
      }, this);
      this.set({cToDMap: map});
    }
  , mkFToDMap: function(){
      var map = {};
      _.each(this.get('dynamics'), function(ds, tId){
        var fMap = {};
        _.each(ds, function(d){
          var f = d.Field;
          if(f in fMap){
            fMap[f].push(d);
          }else{
            fMap[f] = [d];
          }
        }, this);
        map[tId] = fMap;
      }, this);
      this.set({fToDMap: map});
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
      //Defaulting translationId:
      if(tId === null) tId = this.defaultTranslationId();
      //Making sure tId is a number:
      if(typeof(tId) === 'string'){
        tId = parseInt(tId);
      }
      //Setting the translationId, and returning:
      this.set({translationId: tId});
      return tId;
    }
    /**
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
    /***/
  , setTranslationId: function(tId){
      this.set({translationId: tId});
    }
    /**
      @param req
        req is a String -> String translation will be returned
        req is an Array -> translateStatic is mapped over array, array returned
        req is an Object -> translateStatic is mapped over values, object returned
    */
  , translateStatic: function(req){
      var type = typeof(req);
      if(type === 'object'){
        if(_.isArray(req)){
          return _.map(req, this.translateStatic, this);
        }
        _.each(req, function(v,k){
          req[k] = this.translateStatic(v);
        }, this);
        return req;
      }
      var tId  = this.getTranslationId()
        , data = this.get('statics')[tId] || {};
      //Fallback of tId to 1, iff necessary:
      if(!(req in data) && tId !== this.defaultTranslationId()){
        data = this.get('statics')[1];
      }
      if(_.isUndefined(data)){
        console.log('data is '+data+' in translateStatic!');//FIXME THIS IS A BUG, methinks
        return 'translateStatic(FIXME)!';
      }
      return data[req];
    }
  //Dynamic translations:
  , translateDynamic: function(category, field, fallback){
      var tIds = this.getTranslationIds()
        , data = this.get('cToDMap');
      for(var i = 0; i < tIds.length; i++){
        var currentMap = data[tIds[i]] || {};
        if(category in currentMap){
          var ts = _.chain(currentMap[category]).where({Field: field}).pluck('Trans').value();
          if(ts.length > 0){
            if(ts.length === 1)
              return ts[0];
            return ts;
          }
        }
      }
      //Translation not found:
      if(tIds.length > 1){//Only log if we tried more than tId = 1
        if(this.get('ready')){//Only log if we're ready
          console.log('Could not find translation:\n'+JSON.stringify({
            tIds: tIds, category: category, field: field
          }));
        }
      }
      return fallback;
    }
    /**
      Method to produce the path to the flag for the current translation.
    */
  , getFlag: function(tId){
      tId = tId || this.getTranslationId();
      var summary = this.get('summary');
      return summary[tId].ImagePath;
    }
    /***/
  , getName: function(tId){
      tId = tId || this.getTranslationId();
      var summary = this.get('summary');
      return summary[tId].TranslationName;
    }
    /**
      Returns all translationIds that are not the current or the one passed as parameter.
    */
  , getOthers: function(tId){
      tId = tId || this.getTranslationId();
      var ret = [];
      _.each(_.keys(this.get('summary')), function(t){
        if(typeof(t) === 'string')
          t = parseInt(t);
        if(t !== tId)
          ret.push(t);
      }, this);
      return ret;
    }
    /**
      Returns the RfcLanguage for the current translationId, or null.
    */
  , getRfcLanguage: function(){
      var tId   = this.getTranslationId()
        , query = {LanguageIx: this.get('summary')[tId].RfcLanguage};
      return App.languageCollection.findWhere(query) || null;
    }
    /***/
  , getBrowserMatch: function(){
      var tId = this.getTranslationId();
      return this.get('summary')[tId].BrowserMatch;
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
