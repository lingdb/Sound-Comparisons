/* eslint-disable no-unused-vars */
/* global importScripts: false, onmessage: true, postMessage: false, LZString: false */
"use strict";
/**
  (de-)compressing strings turned out to be a rather time intensive task.
  To circumvent a blocked page and browsers complaining to their users about long running scripts,
  we handle compression in the Compressor webworker, if webworkers are available to the Browser.
  If we don't have webworkers, we just hope that either the script/machine is fast,
  or the user lets the script run to completion even tough the Browser complains.

  Messages between this worker and the outside world are passed as strings of encoded JSON objects,
  with the following fields:
  task:  'compress' || 'decompress'
  data:  string
  label: string
  The label field will not be touched by the Compressor,
  but can be used by the outside to identify, where the data belongs to.
  In case of a problem, the encoded JSON object may contain a stingle error field instead of the above data.

  Source for more information:
  https://developer.mozilla.org/de/docs/Web/Guide/Performance/Using_web_workers
*/
importScripts('../extern/lz-string-1.3.3-min.js');

onmessage = function(e){
  var msg = e.data;
  if('task' in msg && 'data' in msg){
    switch(msg.task){
      case 'compressBase64':
        msg.data = LZString.compressToBase64(JSON.stringify(msg.data));
      break;
      case 'decompressBase64':
        msg.data = JSON.parse(LZString.decompressFromBase64(msg.data));
      break;
      case 'compress':
        msg.data = LZString.compress(JSON.stringify(msg.data));
      break;
      case 'decompress':
        msg.data = JSON.parse(LZString.decompress(msg.data));
      break;
      default:
        throw 'Compressor.onmessage() with unknown task: '+msg.task;
    }
    //Finished:
    postMessage(msg);
  }else{
    throw 'Compressor.onmessage() with missing task|data field:\n'+JSON.stringify(msg);
  }
};
