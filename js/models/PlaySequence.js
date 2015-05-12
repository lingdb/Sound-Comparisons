"use strict";
/**
  @param target JQuery object to listen on for clicks.
  The PlaySequence can be triggered to play a sequence of audiofiles.
  Audio files can be added to the sequence, to fill it.
  The sequence itself can be started and stopped.
*/
function PlaySequence(target){
  this.clear();
  this.target = target;
  var t = this;
  target.click(function(){ t.togglePlay(); });
}
/**
  Adds the given audio tag to the sequence.
*/
PlaySequence.prototype.add = function(audio){
  if(_.isArray(audio)){
    _.each(audio, this.add, this);
  }else{
    this.sequence.push(audio);
  }
};
/***/
PlaySequence.prototype.clear = function(){
  this.next     = 0;     // The next element in the sequence to be played
  this.playing  = false; // Whether the PlaySequence is currently playing
  this.sequence = [];    // The sequence of HTML5 audio tags to be played
};
/**
  Starts the PlaySequence.
*/
PlaySequence.prototype.play = function(){
  this.playing = true;
  var t = this;
  App.views.audioLogic.setPlayFinished(function(){ t._play(); });
  this._play();
  //Stopping on spacebar:
  var stop = function(e){
    var key = e.keyCode ? e.keyCode : e.which;
    if(key === 80 && t.playing){
      t.togglePlay();
      $(window).unbind('keyup', stop);
    }else return;
  };
  $(window).keyup(stop);
};
/**
  The internal play function for PlaySequence.
*/
PlaySequence.prototype._play = function(){
  //Check if the sequence is played,
  //and if so, stop playing and reset next:
  if(this.next >= this.sequence.length){
    this.next    = 0;
    this.playing = false;
    this.showPlay();
  }
  if(!this.playing)
    return; //We leave if we shouldn't play on
  //Fetching the audio and incrementing next:
  var audio = this.sequence[this.next];
  this.next++;
  //Playing the audio:
  App.views.audioLogic.play(audio);
};
/**
  Stops the PlaySequence from playing further.
  This one doesn't reset next, so that playing
  will restart from where it left off.
*/
PlaySequence.prototype.stop = function(){
  this.playing = false;
};
/***/
PlaySequence.prototype.rotate = function(target, mathWise){
  if(target.hasClass('rotate90')){target.addClass('rotate180').removeClass('rotate90');}
  else if(target.hasClass('rotate180')){target.addClass('rotate270').removeClass('rotate180');}
  else if(target.hasClass('rotate270')){target.removeClass('rotate270');}
  else {target.addClass('rotate90');}
  if(mathWise){
    this.rotate(target);
    this.rotate(target);
  }
};
/***/
PlaySequence.prototype.showPlay = function(){
  if(this.target.hasClass('icon-eject')) return;
  this.target.addClass('icon-eject').removeClass('icon-pause');
  this.rotate(this.target);
};
/***/
PlaySequence.prototype.showPause = function(){
  if(this.target.hasClass('icon-pause')) return;
  this.target.addClass('icon-pause').removeClass('icon-eject');
  this.rotate(this.target, true);
};
/***/
PlaySequence.prototype.togglePlay = function(){
  if(this.target.hasClass('icon-eject')){
    this.showPause();
    this.play();
  }else{
    this.showPlay();
    this.stop();
  }
};
