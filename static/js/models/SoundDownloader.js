"use strict";
define(['backbone'], function(Backbone){
  /**
    The SoundDownloader aids downloading a .zip of all soundfiles currently in the content area.
  */
  return Backbone.Model.extend({
    /**
      Making sure we have the worker we need:
    */
    initialize: function(){
      //Promise for currently running download:
      this.promise = null;
      //Files that need to be downloaded:
      this.files = [];
      //Maximum number of downloads to run at the same time:
      this.maxDownloads = 5;
      //Worker to handle zipping:
      this.worker = null;
      if(_.isFunction(window.Worker)){
        this.worker = new Worker('js/worker/Zipper.js');
        var t = this;
        this.worker.onmessage = function(e){
          var m = e.data;
          if(t.promise !== null){
            if('task' in m){
              switch(m.task){
                case 'zip':
                  if('data' in m){
                    t.promise.resolve(m);
                  }else{
                    console.log('SoundDownloader received a malformed message:');
                    console.log(m);
                  }
                break;
                default:
                  console.log('SoundDownloader received a message with unknown task:');
                  console.log(m);
              }
            }else{
              console.log('SoundDownloader received a message without a task:');
              console.log(m);
            }
            t.promise = null;
          }else{
            console.log('SoundDownloader received an unexpected message:');
            console.log(m);
          }
        };
        this.worker.onerror = function(e){
          console.log('SoundDownloader.worker.onerror:');
          console.log(e);
          e.preventDefault();
        };
      }
    }
    /**
      Gathers an array of all paths of currently displayed soundfiles
    */
  , getTranscriptions: function(){
      var ps = App.pageState, ls = [], ws = [];
      if(_.any(['m','w'], ps.isPageView, ps)){
        //Transcriptions for all languages with the current word
        ws = [App.wordCollection.getChoice()];
        ls = App.languageCollection.models;
      }else if(ps.isPageView('l')){
        //Transcriptions for all words in the current language
        ls = [App.languageCollection.getChoice()];
        ws = App.wordCollection.models;
      }else if(ps.isMultiView()){
        //Transcriptions for all selected words in all selected languages
        ws = App.wordCollection.getSelected();
        ls = App.languageCollection.getSelected();
      }else{
        console.log('Unexpected case in SoundDownloader:getPaths!');
      }
      //Collecting transcriptions:
      var ts = [];
      _.each(ls, function(l){
        _.each(ws, function(w){
          var t = App.transcriptionMap.getTranscription(l, w);
          if(t) ts.push(t);
        }, this);
      }, this);
      return ts;
    }
    /**
      @return promise $.Deferred
      The returned promise will be resolved once the download is ready/done.
    */
  , download: function(){
      var p = $.Deferred();
      if(this.promise !== null){//Won't interrupt another download:
        p.reject('Another download is currently running.');
      }else if(this.worker === null){//Won't work if no web worker:
        p.reject('This browser does not support Web Workers, therefore the download does not work.');
      }else{
        this.promise = p;
        //Files to download:
        this.files = _.flatten(_.map(this.getTranscriptions(), function(t){
          return t.getSoundfiles();
        }));
        if(this.files.length === 0){
          p.reject('No files are selected for download -> nothing to do.');
        }else{
          //Function to download a sound file:
          var t = this, go = function(p){
            //p is a promise to be resolved when go finishes.
            var f = t.files.shift();
            if(!_.isUndefined(f)){
              var base = _.last(f.split('/'));
              $.get('export/singleSoundFile', {file: f, base64: 1}, function(d){
                var msg = {task: 'addFile', name: base, data: d};
                t.worker.postMessage(msg);
              }).fail(function(){
                t.worker.postMessage({task: 'missingFile', name: f});
              }).always(function(){ go(p); });
            }else{
              p.resolve();
            }
          };
          //Starting async downloads:
          var n = Math.min(this.maxDownloads, this.files.length), ps = [];
          for(var i = 0; i < n; i++){
            var prom = $.Deferred();
            go(prom);
            ps.push(prom);
          }
          //Waiting for downloads to finish:
          $.when.apply($, ps).then(function(){
            //Downloads finished -> generate .zip
            t.worker.postMessage({task: 'zip'});
          });
        }
      }
      return p.promise();
    }
  });
});
