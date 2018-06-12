"use strict";
/* global App */
/* eslint-disable no-eval, no-console */
define(['jquery','underscore','i18n'], function($, _, i18n){
  /**
    This module shall produce a simple object
    that provides functionality to fetch special AJAX resources.
    The idea is to bundle possible requests here
    and to also provide alternative ways of fetching resources
    (via 'file:' rather than 'http:' protocol).
  */
  var useAjax = window.location.protocol !== 'file:';
  /**
    Memo for files to load.
  */
  var fileMemo = null, interaction = null;
  /**
    @param path String
    @param expectJSON Bool
    @return Deferred
    The fetchFile function uses the AMD loader to load the given file,
    and returns a promise that will be resolved with the data from it.
  */
  var fetchFile = function(path){
    path = _.last(path.split('/'));
    // the vn's have to set while generating the offline version
    var inputFiles = [
      {f: 'data', vn: 'localData'},
      {f: 'translations_i18n', vn: 'localTranslationsI18n'},
      {f: 'translations_action_summary', vn: 'localTranslationsActionSummary'}
    ]
    var def = $.Deferred()
      , deliver = function(){
                    def.resolve(fileMemo[path]);
                };
    if(fileMemo === null){
      // load all data by appending data files as <script> elements
      fileMemo = {};
      interaction = $.Deferred();
      var waits = [];

      // first load data_global to get all defined studies and append them to 'inputFiles'
      var f = 'data_global';
      var vn = 'localDataGlobal';
      var script = document.createElement('script');
      script.type = 'text/javascript';
      script.src = "data/" + f + ".js";
      script.language = "javascript";
      script.charset = "utf-8";
      script.async = true;
      script.onload = function () {
        fileMemo[f] = eval(vn);
        eval(vn + " = null;");
        // append all studies
        _.each(fileMemo[f].studies, function(study){
          if(study !== '--'){
            inputFiles.push(
              {f: "data_study_"+study, vn: "localDataStudy"+study}
            );
          }
        }, this);
        // load all studies and additional data
        _.each(inputFiles, function(file){
            try{
              var script = document.createElement('script');
              var waitReader = $.Deferred();
              waits.push(waitReader);
              script.type = 'text/javascript';
              script.src = "data/" + file.f + ".js";
              script.language = "javascript";
              script.charset = "utf-8";
              script.async = true;
              script.onload = function () {
                fileMemo[file.f] = eval(file.vn);
                eval(file.vn + " = null;");
                waitReader.resolve();
                if(App.views.setupBar){App.views.setupBar.render();}
              }
              document.head.appendChild(script);
            }catch(e){
              console.log("Could not load local data for " + file.f + " variable " + file.vn);
            }
        }, this);
        $.when.apply($, waits).always(function(){
          if(interaction !== null){
            interaction.resolve();
            interaction = null;
          }
        });
        interaction.done(deliver).fail(function(){
          def.reject(arguments);
        });
      }
      document.head.appendChild(script);
    }else if(interaction !== null){
      interaction.always(function(){
        if(path in fileMemo){
          deliver();
        }else{
          def.reject();
        }
      });
    }else if(path in fileMemo){
      deliver();
    }else{
      def.reject();
    }
    // The promise to use elsewhere:
    return def.promise();
  };
  /**
    @param target String
    @param query Object of GET parameters
    @param expectJSON boolean
    @return Deferred
    Tries to fetch the given target using either AJAX or fetchFile.
    Instructs fetchFile to parse JSON.
  */
  var fetch = function(target, query, expectJSON){
    // Sanity checks:
    if(!_.isObject(query)){ query = {}; }
    // Maybe using AJAX?
    if(useAjax){
      if(expectJSON === true){
          return $.getJSON(target, query);
      }
      return $.get(target, query);
    }
    // Folding query into target:
    _.each(query, function(v, k){
        if(v === null){
          target += '_'+k;
        }else{
          target += '_'+k+'_'+v;
        }
    });
    return fetchFile(target);
  };
  /**
    @return fetch' function
    A short wrapper function for _.bind,
    which allows for shorter code below.
    Returns a modified version of the fetch function with the parameters prefilled.
  */
  var mkFetch = function(target, query, expectJSON){
    return _.bind(fetch, null, target, query, expectJSON);
  };
  /** The endpoints to use: */
  var dataRoute = 'query/data'
    , translationRoute = 'query/translations'
    , i18nRoute = (useAjax ? translationRoute+'?lng=__lng__&ns=__ns__'
                           : translationRoute+'_i18n')
    , aboutRoute = 'query/about';
  /** The object to aid fetching: */
  return {
    'data': {
      'initial': mkFetch(dataRoute, {}, true)
    , 'global':  mkFetch(dataRoute, {global: null}, true)
    , 'study':   function(studyName){
                   return fetch(dataRoute, {study: studyName}, true);
                 }
    }
  , 'translation': {
      'summary': mkFetch(translationRoute, {action: 'summary'}, true)
    , 'i18nUrl': i18nRoute
    , 'i18n': (function(){
                 if(!useAjax){
                   var func = mkFetch(i18nRoute, {}, true);
                   //Modify i18n to change file loadings:
                   i18n.sync._fetch = function(lngs, options, cb){
                     func().done(function(data){
                         cb(null, data);
                     }).fail(function(err){
                         cb(err);
                     });
                   };
                 }
                 return i18n;
               })()
    }
  , 'github': {
      'about': function(page){
                 return fetch(aboutRoute, {page: page}, false);
               }
    }
  , 'isOnline': useAjax
  };
});
