"use strict";
define(['Sanitizer',
        'models/Language',
        'backbone',
        'underscore'],
       function(Sanitizer, Language, Backbone, _){
  /**
    The Configurator provides means to build configuration such as used by the Linker.
  */
  return Sanitizer.extend({
    /**
      @param config String || Object
      @return promise Deferred
      This method parses a config String and adjusts different page setttings accordingly.
      The given config is also allowed to be an object, in which case parsing will be omitted.
      Fields added for #188 are {siteLanguage,study,language{,s},word{,s}}
      Since the study and siteLanguage fields may require loading some things first,
      the configure method may run in several stages building on top of each other.
    */
    configure: function(config){
      //Parsing config:
      if(_.isString(config)){
        config = this.parseConfig(config);
      }
      //Promise to be returned by this method:
      var def = $.Deferred();
      //Stage 1 if study and/or siteLanguage are in config:
      if(_.any(['siteLanguage','study'], function(field){return (field in config);})){
        var proms = [];//Stack of promises for this case:
        var sortWords = false;//Set to true to make sure words will be sorted.
        if('siteLanguage' in config){
          var siteLanguage = config.siteLanguage;
          if(_.isString(siteLanguage) && !_.isEmpty(siteLanguage)){
            sortWords = App.pageState.wordOrderIsAlphabetical();
            proms.push(App.translationStorage.setTranslation(siteLanguage));
          }
          delete config.siteLanguage;
        }
        if('study' in config){
          var cStudy = config.study;
          if(_.isString(cStudy) && !_.isEmpty(cStudy)){
            var study = App.study;
            if(study){
              proms.push(study.setStudy(config.study));
            }else{
              console.log('App.study missing in Configurator.configure: '+study);
            }
          }
          delete config.study;
        }
        //Handling promises:
        if(proms.length > 0){
          var t = this;
          $.when.apply($, proms).always(function(){
            //Sorting words iff necessary:
            if(sortWords === true){ App.wordCollection.sort(); }
            //Resolving further:
            if(_.isEmpty(config)){
              def.resolve();
            }else{
              t.configure(config).done(function(){
                def.resolve(arguments);
              }).fail(function(){
                def.reject(arguments);
              });
            }
          });
        }else{
          def.resolve();
        }
        return def.promise();
      }
      //Stage 2 - other fields:
      if('wordOrder' in config){
        var callMap = { alphabetical: 'wordOrderSetAlphabetical'
                      , logical:      'wordOrderSetLogical'};
        if(config.wordOrder in callMap){
          App.pageState[callMap[config.wordOrder]]();
        }else{
          console.log('Configurator.configure() could not configure wordOrder: '+config.wordOrder);
        }
      }
      if('spLang' in config){
        var spLang = App.languageCollection.find(function(l){
          if(l.getId() === config.spLang) return true;
          return l.getKey() === config.spLang;
        }, this);
        App.pageState.setSpLang(spLang);
      }
      if('phLang' in config){
        var phLang = App.languageCollection.find(function(l){
          if(l.getId() === config.phLang) return true;
          return l.getKey() === config.phLang;
        }, this);
        App.pageState.setPhLang(phLang);
      }
      if('meaningGroups' in config){
        var mgs = App.meaningGroupCollection.filterKeyOrId(config.meaningGroups);
        App.meaningGroupCollection.setSelected(mgs);
      }
      if('regions' in config){
        var rs = App.regionCollection.filterKeyOrId(config.regions);
        App.regionCollection.setSelected(rs);
      }
      if('families' in config){
        var fs = App.familyCollection.filterKeyOrId(config.families);
        App.familyCollection.setSelected(fs);
      }
      if('translation' in config){//FIXME WHAT ABOUT THE PROMISE?
        App.translationStorage.setTranslationId(config.translation);
      }
      if('mapViewIgnoreSelection' in config){
        App.pageState.set({mapViewIgnoreSelection: config.mapViewIgnoreSelection === 'true'});
      }
      if('wordByWord' in config){
        App.pageState.set({wordByWord: config.wordByWord === 'true'});
      }
      if('language' in config){//Choice
        App.languageCollection.setChoice(config.language);
      }
      if('languages' in config){//Selection
        //If languages are a string, we need to produce a [Language].
        if(_.isString(config.languages)){
          var langs = this.parseArray(config.languages);
          config.languages = App.languageCollection.filterKeyOrId(langs);
        }
        //languages are expected to be of [Language]
        App.languageCollection.setSelected(config.languages);
      }
      if('word' in config){//Choice
        App.wordCollection.setChoice(config.word);
      }
      if('words' in config){//Selection
        //If words are a string, we need to produce a [Word].
        if(_.isString(config.words)){
          var wds = this.parseArray(config.words);
          config.words = App.wordCollection.filterKeyOrId(wds);
        }
        //words are expected to be of [Word]
        App.wordCollection.setSelected(config.words);
      }
      if('pageView' in config){
        var pv = config.pageView, ps = App.pageState;
        if(!ps.isPageView(pv)){//Only set if it's not the current one.
          ps.setPageView(pv);
        }
      }
      //Promises solved automatically:
      def.resolve();
      return def.promise();
    }
    /**
      @param [pvk String, PageViewKey]
      @param [useIds Boolean]
      The reverse operation to configure.
      It shall generate an object describing the whole configuration.
    */
  , getConfig: function(pvk, useIds){
      pvk = pvk || App.pageState.getPageViewKey();
      useIds = (_.isBoolean(useIds)) ? useIds : false;
      var config = {}, ps = App.pageState;
      //wordOrder:
      if(ps.wordOrderIsAlphabetical()){
        config.wordOrder = 'alphabetical';
      }else{
        config.wordOrder = 'logical';
      }
      //spLang:
      var spLang = ps.getSpLang();
      if(spLang){
        config.spLang = useIds ? spLang.getId() : spLang.getKey();
      }
      //phLang:
      var phLang = ps.getPhLang();
      if(phLang){
        config.phLang = useIds ? phLang.getId() : phLang.getKey();
      }
      //Helper functions:
      var getKeys = function(xs){return _.map(xs, function(x){return x.getKey();});}
        , getIds  = function(xs){return _.map(xs, function(x){return x.getId();});};
      //meaningGroups:
      var mgs = App.meaningGroupCollection.getSelected(pvk);
      config.meaningGroups = useIds ? getIds(mgs) : getKeys(mgs);
      //regions:
      var regions = App.regionCollection.getSelected(pvk);
      config.regions = useIds ? getIds(regions) : getKeys(regions);
      //families:
      var families = App.familyCollection.getSelected(pvk);
      config.families = useIds ? getIds(families) : getKeys(families);
      //translation:
      config.translation = App.translationStorage.getTranslationId();
      //mapViewIgnoreSelection:
      config.mapViewIgnoreSelection = ps.get('mapViewIgnoreSelection');
      //wordByWord:
      config.wordByWord = ps.get('wordByWord');
      //done:
      return config;
    }
    /**
      @param calls [Suffix -> Args]
      @param config Object
      @return config' Object
      Takes a calls Object that maps Suffixes to args,
      where Suffix is the match of /configSet(.+)/ for methods of Router,
      and args will be supplied to the Router method as second argument,
      whereas config is the first argument.
      Router:sanitize works in a similar fashion.
    */
  , configSet: function(calls, config){
      config = config || {};
      _.each(calls, function(arg, suffix){
        var method = 'configSet'+suffix;
        if(method in this){
          config = this[method].call(this, config, arg);
        }else{
          console.log('Router:configSet() cannot call method: '+method);
        }
      }, this);
      return config;
    }
    /**
      Builds configuration to set the WordOrder managed by PageState to alphabetical.
    */
  , configSetWordOrderAlphabetical: function(config){
      config = config || {};
      config.wordOrder = 'alphabetical';
      return config;
    }
    /**
      Builds configuration to set the WordOrder managed by PageState to logical.
    */
  , configSetWordOrderLogical: function(config){
      config = config || {};
      config.wordOrder = 'logical';
      return config;
    }
    /**
      Builds configuration to set the spLang to the given spLang.
    */
  , configSetSpLang: function(config, spLang){
      config = config || {};
      if(spLang instanceof Language){
        config.spLang = spLang.getKey();
      }else if(spLang === null){
        config.spLang = null;
      }
      return config;
    }
    /**
      Builds configuration to set the phLang to the given phLang.
    */
  , configSetPhLang: function(config, phLang){
      config = config || {};
      if(phLang instanceof Language){
        config.phLang = phLang.getKey();
      }
      return config;
    }
    /**
      Builds configuration to set the given MeaningGroups as selected.
    */
  , configSetMeaningGroups: function(config, mgs){
      config = config || {};
      var ms = this.configMkKeyArray(mgs);
      if(ms){
        config.meaningGroups = this.sanitizeArray(ms);
      }
      return config;
    }
    /**
      Builds configuration to set the given Regions as selected.
    */
  , configSetRegions: function(config, regions){
      config = config || {};
      var rs = this.configMkKeyArray(regions);
      if(rs){
        config.regions = this.sanitizeArray(rs);
      }
      return config;
    }
    /**
      Builds configuration to set the given Families as selected.
    */
  , configSetFamilies: function(config, families){
      config = config || {};
      var fs = this.configMkKeyArray(families);
      if(fs){
        config.families = this.sanitizeArray(fs);
      }
      return config;
    }
    /***/
  , configSetTranslation: function(config, translationId){
      config = config || {};
      config.translation = translationId;
      return config;
    }
    /***/
  , configSetMapViewIgnoreSelection: function(config, ignore){
      config = config || {};
      config.mapViewIgnoreSelection = ignore ? 'true' : 'false';
      return config;
    }
    /***/
  , configSetWordByWord: function(config, wordByWord){
      config = config || {};
      config.wordByWord = wordByWord ? 'true' : false;
      return config;
    }
    /**
      Helper method to build arrays of keys.
      Backbone.Collection || [Model] -> [String] || null
    */
  , configMkKeyArray: function(ms){
      if(ms instanceof Backbone.Collection){
        ms = ms.models;
      }
      if(_.isArray(ms)){
        return _.map(ms, function(m){
          if(_.isString(m)) return m;
          if(_.isFunction(m.getKey)) return m.getKey();
          console.log(m);
          throw 'Configurator.configMkKeyArray() could not convert correctly.';
        }, this);
      }
      return [];
    }
  });
});
