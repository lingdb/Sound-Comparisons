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
  }
, initialize: function(){
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
    });
  }
, load: function(){
    var data = localStorage['TranslationStorage'];
    if(!data) return;
    var d = $.parseJSON(data);
    if(!_.isObject(d)) return;
    this.set($.extend(d, {ready: true}));
    this.trigger('change:dynamics');
  }
, save: function(){
    var params = _.clone(this.saveFields);
    params.unshift(this.attributes);
    var data = _.pick.apply(_, params);
    localStorage['TranslationStorage'] = JSON.stringify(data);
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
});
