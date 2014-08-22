/**
  Given that query/data allows us to fetch individual studies,
  it appears helpful, to keep the data in localStorage.
  We distinguish between study related data and global data.
  Study related data can/will be cleaned out once the space limit is reached,
  while global data will only be overwritten.
*/
DataStorage = Backbone.Model.extend({
  defaults: {
    global: null
  , lastUpdate: 0
  , study: null
  , target: 'query/data'
  }
, initialize: function(){
    //Self dependant events:
    this.on('change:global', this.saveGlobal, this);
    this.on('change:study', this.saveStudy, this);
    //Fetching initial data from target:
    var t = this;
    $.getJSON(this.get('target')).done(function(data){
      console.log("Got target data: "+JSON.stringify(data));
      delete data['Description'];
      t.set(data);
      t.loadGlobal().done(function(){
        var study = App.studyWatcher.get('study');
        t.loadStudy(study);
      });
    });
  }
/**
  Makes the DataStorage listen to the StudyWatcher,
  to trigger loading a different study.
*/
, listenStudy: function(){
    var sw = window.App.studyWatcher;
    sw.on('change:study', function(){
      this.loadStudy(sw.get('study'));
    }, this);
  }
  /* We track the age of all fetched data,
     so that we can delete them beginning with the oldest, if necessary.
  */
, time: Date.now || function(){
    return +new Date;
  }
  /*
    Cleans up localStorage a little to see that we get some space back.
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
        delete localStorage[key];
        collected = true;
      }
    }, this);
    if(collected) return true;
    //Stage 2:
    _.each(studies, function(study){
      var key = "Study_"+study;
      if(key in localStorage){
        console.log('DataStorage.collectGarbadge() unused: '+study);
        delete localStorage[key];
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
      , value = LZString.compressToBase64(JSON.stringify(data))
      , saved = false;
    do{
      try{
        localStorage[key] = value;
        saved = true;
      }catch(e){
        //We cancel saving and say it's true, iff we couldn't free any space:
        saved = this.collectGarbadge() ? false : true;
      }
    }while(saved !== true);
  }
, saveGlobal: function(){
    var data = this.get('global');
    console.log('DataStorage.saveGlobal()');
    this.save('global', data);
  }
, saveStudy: function(){
    var data = this.get('study')
      , name = "Study_"+data.study.Name;
    console.log('DataStorage.saveStudy(): '+data.study.Name);
    this.save(name, data);
  }
/**
  The generalized load function of DataStorage, it handles compression and the key name.
  The more specialized load functions only bother the server,
  iff nothing is found by the load function,
  or the information given by load is outdated.
*/
, load: function(name){
    var key = "DataStorage_"+name;
    if(key in localStorage)
      return $.parseJSON(LZString.decompressFromBase64(localStorage[key]));
    return null;
  }
, loadGlobal: function(){
    var timestamp = this.get('lastUpdate')
      , current   = this.load('global')
      , promise   = $.Deferred();
    if(!current || current.timestamp < timestamp){
      var t = this;
      $.getJSON(this.get('target'), {global: null}).done(function(data){
        data.timestamp = timestamp;
        t.set({global: data});
        promise.resolve();
      }).fail(function(f){
        promise.reject(f);
      });
    }else{
      this.set({global: current}, {silent: true});
      promise.resolve();
    }
    return promise;
  }
, loadStudy: function(name){
    var key   = "Study_"+name
      , study = this.load(key)
      , timestamp = this.get('lastUpdate');
    if(!study || study.timestamp < timestamp){
      var t = this;
      $.getJSON(this.get('target'), {study: name}).done(function(data){
        data.timestamp = timestamp;
        t.set({study: data});
      });
    }else{
      this.set({study: study}, {silent: true});
    }
  }
});
