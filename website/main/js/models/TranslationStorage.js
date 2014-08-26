TranslationStorage = Backbone.Model.extend({
  defaults: {
    ready:        false // Ready as soon, as a translation is usable.
  , summary:      {}    // TranslationId -> Translation
  , statics:      {}    // TranslationId -> Req -> Trans
  , dynamics:     {}    // TranslationId -> [dynamics]
  , staticTimes:  {}    // TranslationId -> Timestamp
  , dynamicTimes: {}    // TranslationId -> Timestamp
  , nToTMap:      {}    // BrowserMatch  -> Translation
  , cToDMap:      {}    // TranslationId -> Category -> [dynamics]
  , fToDMap:      {}    // TranslationId -> Field    -> [dynamics]
  , translationId: null
  }
, initialize: function(){
    //Saving translationId on change:
    this.on('change:translationId', this.saveTranslationId, this);
    //Holding attributes up to date:
    this.on('change:summary',  this.mkNToTMap, this);
    this.on('change:dynamics', this.mkCToDMap, this);
    this.on('change:dynamics', this.mkFToDMap, this);
    //Load already known Translaton data:
    this.load();
    /*
      Cases to save TranslationStorage:
      Save will not be triggered by first load, because load has already finished.
    */
    this.saveFields = ['summary', 'staticTimes', 'statics', 'dynamicTimes', 'dynamics'];
    _.each(this.saveFields, function(f){
      this.on('change:'+f, this.save, this);
    }, this);
    /*
      Fetching information from the server works in several steps:
      1.: Get the current summary
      2.: Update static  translations, iff outdated
      3.: Update dynamic translations, iff outdated
    */
    var storage = this;
    //Step 1:
    $.getJSON('query/translations', {action: 'summary'}).done(function(summary){
      storage.set({'summary': summary, ready: true}); // Maybe premature true?
      var sTimes = storage.get('staticTimes'),  _sTimes = {}
        , dTimes = storage.get('dynamicTimes'), _dTimes = {};
      _.each(summary, function(translation){
        var tId = translation.TranslationId
          , cS  = translation.lastChangeStatic
          , cD  = translation.lastChangeDynamic;
        //Step 2:
        if(sTimes[tId] != cS){ // Inequality is enough; we don't know from the future.
          $.getJSON('query/translations', {action: 'static', translationId: tId}).done(function(elems){
            var stats  = storage.get('statics');
            stats[tId] = elems;
            storage.set({statics: stats});
          });
        }
        _sTimes[tId] = cS;
        //Step 3:
        if(dTimes[tId] != cD){
          $.getJSON('query/translations', {action: 'dynamic', translationId: tId}).done(function(elems){
            var stats  = storage.get('dynamics');
            stats[tId] = elems;
            storage.set({dynamics: stats});
            storage.trigger('change:dynamics');
          });
        }
        _dTimes[tId] = cD;
      }, storage);
      storage.set({staticTimes: _sTimes, dynamicTimes: _dTimes});
    }).fail(function(){
      window.App.linkInterceptor.set({enabled: false});
      console.log('Could not fetch translation summary from host -> LinkInterceptor disabled.');
    });
  }
, load: function(){
    var data = localStorage['TranslationStorage'];
    if(!data) return;
    var d = $.parseJSON(LZString.decompressFromBase64(data));
    if(!_.isObject(d)) return;
    this.set($.extend(d, {ready: true}));
    this.trigger('change:dynamics');
  }
, save: function(){
    var params = _.clone(this.saveFields);
    params.unshift(this.attributes);
    var data = _.pick.apply(_, params);
    localStorage['TranslationStorage'] = LZString.compressToBase64(JSON.stringify(data));
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
    }, this)
    this.set({fToDMap: map});
  }
/**
  function to produce the default TranslationId
  for all occurences, to enable easy changing,
  and have it as a nice constant.
*/
, defaultTranslationId: function(){return 1;}
/**
  Saving the current TranslationId to localStorage:
*/
, saveTranslationId: function(){
    localStorage['translationId'] = this.get('translationId');
  }
/**
  Figuring out the TranslationId of a client,
  works in multiple steps:
  0.: If we already figured out the TranslationId,
      we use the one given with this model.
  1.: If TranslationId is known from localStorage, we use that.
  2.: We see if the browser language matches a particular translation summary.
  3.: We fall back to the defaultTranslationId.
*/
, getTranslationId: function(){
    var tId = this.get('translationId');
    if(tId !== null) return tId;
    //Is the translationId known from localStorage?
    if('translationId' in localStorage){
      tId = localStorage['translationId'];
    }else{
      //Finding the translationId via browser language:
      var lang = navigator.language || navigator.userLanguage
        , summary = _.find(this.get('summary'), function(s){
          var index = lang.indexOf(s.BrowserMatch);
          return index >= 0
        }, this);
      if(summary) tId = summary.TranslationId;
    }
    //Defaulting translationId:
    if(tId === null) tId = this.defaultTranslationId();
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
    return _.unique([this.getTranslationId(), this.defaultTranslationId()]);
  }
//Static translations:
, translateStatic: function(req){
    var tId  = this.getTranslationId()
      , data = this.get('statics')[tId];
    //Fallback of tId to 1, iff necessary:
    if(!(req in data) && tId !== this.defaultTranslationId()){
      data = this.get('statics')[1];
    }
    return data[req];
  }
//Dynamic translations:
, translateDynamic: function(category, field, fallback){
    var tIds = this.getTranslationIds()
      , data = this.get('cToDMap');
    for(var i = 0; i < tIds.length; i++){
      var currentMap = data[tIds[i]];
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
    console.log('Could not find translation:\n'+JSON.stringify({
      tIds: tIds, category: category, field: field
    }));
    return fallback;
  }
});
