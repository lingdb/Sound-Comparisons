"use strict";
define(['jquery','underscore', 'i18n'], function($, _, i18n){
  /**
    This module shall produce a simple object
    that provides functionality to fetch special AJAX resources.
    The idea is to bundle possible requests here
    and to also provide alternative ways of fetching resources
    (via 'file:' rather than 'http:' protocol).
  */
  var useAjax = window.location.protocol !== 'file:';
  /**
    @param path String
    @param expectJSON Bool
    @return Deferred
    The fetchFile function uses the AMD loader to load the given file,
    and returns a promise that will be resolved with the data from it.
  */
  var fetchFile = function(path, expectJSON){
    var def = $.Deferred();
    // Timeout to reject the promise:
    var timeout = window.setTimeout(function(){
      def.reject('Timeout');
    }, 1000);
    // Trying to resolve the promise:
    require([path], function(data){
      window.clearTimeout(timeout);
      if(expectJSON === true){
        try{
          def.resolve(JSON.parse(data));
        }catch(e){
          def.reject('Could not parse JSON.', e)
        }
      }else{
        def.resolve(data);
      }
    });
    // The prommise to use elsewhere:
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
        target += '_'+k+'_'+v;
    });
    return fetchFile(target, expectJSON);
  };
  /**
    @return fetch' function
    A short wrapper function for _.bind,
    which allows for shorter code below.
    Returns a modified version of the fetch function with the parameters prefilled.
  */
  var mkFetch = function(target, query, expectJSON){
    return _.bind(fetch, null, target, query, expectJSON)
  };
  /** The endpoints to use: */
  var dataRoute = 'query/data'
    , templateRoute = 'query/templateInfo'
    , translationRoute = 'query/translations'
    , i18nRoute = (useAjax ? translationRoute+'?lng=__lng__&ns=__ns__'
                           : translationRoute+'_i18n');
  /** The object to aid fetching: */
  return {
    'data': {
      'initial': mkFetch(dataRoute, {}, true)
    , 'global':  mkFetch(dataRoute, {global: null}, true)
    , 'study':   function(studyName){
                   return fetch(dataRoute, {study: studyName}, true);
                 }
    }
  , 'template': {
      'info': mkFetch(templateRoute, {}, true)
    , 'fetch': function(path){
                 return fetch(path, {}, false);
               }
    }
  , 'translation': {
      'summary': mkFetch(translationRoute, {action: 'summary'}, true)
    , 'i18nUrl': i18nRoute
    , 'i18n': (function(){
                 if(!useAjax){
                   //Modify i18n to change file loadings:
                   i18n.sync._fetch = mkFetch(i18nRoute, {}, true);
                 }
                 return i18n;
               })()
    }
  };
});
