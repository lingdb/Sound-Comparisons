"use strict";
define(['jquery','underscore','i18n','bootbox'], function($, _, i18n, bootbox){
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
  var fetchFile = function(path, expectJSON){
    path = _.last(path.split('/'));
    var def = $.Deferred()
      , deliver = function(){
                    if(expectJSON === true){
                      try{
                        def.resolve(JSON.parse(fileMemo[path]));
                      }catch(err){
                        console.log('Problems parsing file:',
                                    {path: path, expectJSON: expectJSON},
                                    fileMemo[path], _.keys(fileMemo));
                      }
                    }else{
                      def.resolve(fileMemo[path]);
                    }
                  };
    if(fileMemo === null){
      fileMemo = {};
      interaction = $.Deferred();
      var dialog = bootbox.dialog({
        title: 'Please select module files…'
      , message: '<input id="moduleFiles" type="file" multiple="">'
      , buttons: {}
      , show: false
      });
      $('#moduleFiles').on('change', function(e){
        var input = event.target, waits = [];
        dialog.modal({show: false});
        _.each(input.files, function(file){
          var reader = new FileReader(), waitReader = $.Deferred();
          waits.push(waitReader);
          reader.onload = function(){
            fileMemo[file.name] = reader.result;
            waitReader.resolve();
          };
          reader.readAsText(file, 'utf-8');
        }, this);
        $.when.apply($, waits).always(function(){
          if(interaction !== null){
            interaction.resolve();
            interaction = null;
          }
        });
      });
      dialog.modal({show: true});
      interaction.done(deliver).fail(function(){
        def.reject(arguments);
      });
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
    return fetchFile(target, expectJSON);
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
    , templateRoute = 'query/templateInfo'
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
