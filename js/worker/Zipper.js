/* global importScripts: false, onmessage: true, postMessage: false, JSZip: false, Date: true */
"use strict";
/**
  This worker is expected to be used with models/SoundDownloader.
  Messages accepted by this worker may look like this:
  {task: 'addFile', name: string, data: <data magic>}
  {task: 'missingFile', name: string}
  {task: 'zip'}
  onmessage may throw exceptions.
  Messages passed outside via postMessage may look like this:
  {task: 'zip', data: <JSZip blob output>, name: string}
*/
importScripts('../extern/jszip.min.js');
// Something to generate our zip file with:
var zip = new JSZip();
//Folder to zip into:
var folder = null;
//Filename & Directoryname to use:
var fname = '';
// Missing files:
var missing = [];
// Handling messages from the great beyond:
onmessage = function(e){
  var m = e.data;
  if('task' in m){
    //Generating the filename:
    if(fname === ''){
      var date = new Date();
      fname = 'Soundfiles.'+date.toISOString()
      folder = zip.folder(fname);
    }
    //Investigating the task:
    switch(m.task){
      case 'addFile':
        if('name' in m && 'data' in m){
          folder.file(m.name, m.data, {base64: true});
        }else{
          throw 'Zipper.onmessage(), addFile without name||data: '+JSON.stringify(m);
        }
      break;
      case 'missingFile':
        if('name' in m){
          missing.push(m.name);
        }else{
          throw 'Zipper.onmessage(), missingFile without name: '+JSON.stringify(m);
        }
      break;
      case 'zip':
        //Checking for missing files:
        if(missing.length > 0){
          var mtxt = missing[0];
          for(var i = 1; i < missing.length; i++){
            mtxt += "\n"+missing[i];
          }
          folder.file('missing.txt', mtxt);
          missing = [];
        }
        //Building answer:
        var answer = {
          task: 'zip'
        , data: zip.generate({type: 'blob'})
        , name: fname+'.zip'
        };
        //Cleanup and answer:
        zip = new JSZip();
        folder = null;
        fname = '';
        postMessage(answer);
      break;
      default:
        throw 'Zipper.onmessage() with unknown task: '+m.task;
    }
  }else{
    throw 'Zipper.onmessage() with missing task field:\n'+JSON.stringify(m);
  }
};
/*
  https://stackoverflow.com/questions/2573521/how-do-i-output-an-iso-8601-formatted-string-in-javascript
  Patch Date, if necessary:
*/
if(!Date.prototype.toISOString){
  (function(){
    function pad(number){
      var r = String(number);
      return (r.length === 1) ? ('0'+r) : r;
    }

    Date.prototype.toISOString = function(){
      return this.getUTCFullYear()
      + '-' + pad( this.getUTCMonth() + 1 )
      + '-' + pad( this.getUTCDate() )
      + 'T' + pad( this.getUTCHours() )
      + ':' + pad( this.getUTCMinutes() )
      + ':' + pad( this.getUTCSeconds() )
      + '.' + String( (this.getUTCMilliseconds()/1000).toFixed(3) ).slice( 2, 5 )
      + 'Z';
    };
  }());
}
