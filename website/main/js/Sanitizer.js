/**
  The Sanitizer provides means to sanitize options such as used by the Linker.
*/
Sanitizer = Backbone.Router.extend({
  /**
    A proxy for the other sanitize methods.
    It chains all sanitize methods with the given suffixes,
    threading the given object trough all of them,
    to finally return the sanitized version.
  */
  sanitize: function(suffixes, o){
    o = o || {};
    _.each(suffixes, function(s){
      var key = 'sanitize'+s;
      if(key in this){
        o = this[key](o);
      }else{
        console.log('Router.sanitize() cannot sanitize with key: '+key);
      }
    }, this);
    return o;
  }
  /***/
, sanitizeLanguage: function(o){
    if(!('language' in o)){
      o.language = App.languageCollection.getChoice();
    }
    if(o.language instanceof Language){
      o.language = o.language.getKey();
    }
    return o;
  }
  /***/
, sanitizeLanguages: function(o){
    if(!('languages' in  o)){
      o.languages = App.languageCollection.getSelected();
    }
    if(o.languages instanceof Backbone.Collection){
      o.languages = o.languages.models;
    }
    if(_.isArray(o.languages)){
      var ls = _.map(o.languages, function(l){
        if(_.isString(l)) return l;
        return l.getKey();
      }, this);
      o.languages = this.sanitizeArray(ls);
    }
    return o;
  }
  /***/
, sanitizeStudy: function(o){
    if(!('study') in o){
      o.study = App.study;
    }
    if(o.study instanceof Study){
      o.study = o.study.getId();
    }
    return o;
  }
  /***/
, sanitizeWord: function(o){
    if(!('word' in o)){
      o.word = App.wordCollection.getChoice();
    }
    if(o.word instanceof Word){
      o.word = o.word.getKey();
    }
    return o;
  }
  /***/
, sanitizeWords: function(o){
    if(!('words' in o)){
      o.words = App.wordCollection.getSelected();
    }
    if(o.words instanceof Backbone.Collection){
      o.words = o.words.models;
    }
    if(_.isArray(o.words)){
      var ws = _.map(o.words, function(w){
        if(_.isString(w)) return w;
        return w.getKey();
      }, this);
      o.words = this.sanitizeArray(ws);
    }
    return o;
  }
  /***/
, sanitizeConfig: function(o){
    if('config' in o){
      if(o.config !== null){
        var vals = _.map(o.config, function(v, k){
          if(_.isArray(v)){
            v = this.sanitizeArray(_.filter(v, _.isString));
          }else{
            if(!_.isString(v)) v = ''+v;
            v = encodeURIComponent(v);
          }
          return encodeURIComponent(k)+'='+v;
        }, this);
        o.config = '?'+vals.join('&');
      }
    }else{
      o.config = null;
    }
    return o;
  }
  /***/
, sanitizeArray: function(a){
    return _.map(a, encodeURIComponent).join();
  }
  /**
    Parses a given config String c to return the config object.
    This should be the exact antipart to sanitizeConfig, so that the following two rules hold:
    sanitizeConfig(parseConfig(s)) === s, parseConfig(sanitizeConfig(config)) === config
    Note that these rules don't hold exactly, because we don't care about [,&] in keys/values,
    and values may only be String || [String].
  */
, parseConfig: function(c){
    var ret = {}
      , qs  = new QueryString(c)
      , isArray = {meaningGroups: '', regions: '', families: ''};
    _.each(qs.keys(), function(k){
      var v = qs.value(k);
      if(k in isArray){
        v = this.parseArray(v);
      }
      ret[k] = v;
    }, this);
    console.log('Sanitizer.parseConfig()');
    console.log(ret);
    return ret;
  }
  /***/
, parseArray: function(c){
    return _.map(c.split(','), decodeURIComponent);
  }
});
