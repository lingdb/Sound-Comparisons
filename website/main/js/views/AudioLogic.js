/**
  The rewrite of AudioLogic to a Backbone.View.
  It will be used by the App, and PlaySequence will be adjusted to fit it.
*/
AudioLogic = Backbone.View.extend({
  initialize: function(){
    // Keeping track of wether we're currently playing something:
    this.playing = false;
    // Currently playing audio
    this.current = null;
    // Milliseconds to wait before starting to play an audio
    this.hoverDelay = 100;
    /**
      Used for hover, to check if the mouse rests over an audio for a while.
    */
    this.timeoutId = null;
    /**
      payFinished shall be a function that will be called
      whenever an audio finishes playing.
      It will be used by the PlaySequence as a callback.
    */
    this.playFinished = null;
    /**
      playCounts will be used for logging, and is a map 'src' -> count.
    */
    this.playCounts = {};
    //Adaptation from the former initAudio():
    this.findAudio();
  }
, findAudio: function(target){
    var t   = this, tgt = target || $('body');
    tgt.find('.audio').each(function(i){
      var a = $('audio', this).get(0);
      $(this).on('click mouseover mouseout touchstart', function(e){
        switch(e.type){
          case 'mouseover':
            t.mouseOver(a);
          break;
          case 'mouseout':
            t.mouseOut(a);
          break;
          case 'click':
          case 'touchstart':
          default:
            t.play(a);
        }
      });
    });
  }
, mouseOver: function(audio){
    if(!window.App.soundPlayOption.playOnHover())
      return;
    var t = this;
    this.timeoutId = window.setTimeout(function(){
      t.play(audio);
      t.timeoutId = null;
    }, this.hoverDelay);
  }
, mouseOut: function(audio){
    if(this.timeoutId){
      window.clearTimeout(this.timeoutId);
      this.timeoutId = null;
    }
  }
/**
  @param audio HTML5 audio tag to play
  Plays the given audio.
  Stops another audio, if it's currently playing.
*/
, play: function(audio){
    if(!audio){
      if(f = this.playFinished) f();
      return;
    }
    if(this.current) this.stop();
    this.current = audio;
    this.onDemand(audio);
    //Calling played when the sound is over:
    var t = this;
    $(audio).unbind('ended').on('ended', function(){ t.played(audio); });
    //Exit if audio lacks sources:
    if(0 == $('source', audio).length){
      this.played(audio);
      return;
    }
    //Playing the sound:
    this.playing = true;
    this.markPlaying(audio);
    audio.play();
    //Logging the play event:
    this.log(audio);
  }
, stop: function(){
    this.playing = false;
    if(this.current){
      this.current.pause();
      this.current.currentTime = 0;
      this.markNotPlaying(this.current);
    }
    this.current = null;
  }
/**
  @param audio HTML5 audio tag
  This function is called whenever AudioLogic finished playing an audio tag.
*/
, played: function(audio){
    this.playing = false;
    this.markNotPlaying(audio);
    this.current = null;
    if(f = this.playFinished) f();
  }
/**
  @param audio HTML5 audio tag
  Adds a class to the transcription that belongs to an audio tag
  so that it is marked as playing.
*/
, markPlaying: function(audio){
    $(audio).parent().find('.transcription').addClass('playing');
  }
/**
  @param audio HTML5 audio tag
  Removes the class that marks the transcription belonging to an audio tag as playing.
*/
, markNotPlaying: function(audio){
    $(audio).parent().find('.transcription').removeClass('playing');
  }
/**
  @param audio HTML5 audio tag
  Loads the onDemand sources that may be
  given with the source tags of an audio tag,
  so that not all sound files need be loaded immediate.
*/
, onDemand: function(audio){
    if(d = $(audio).attr('data-onDemand')){
      var src = "";
      $($.parseJSON(d)).each(function(i, s){
        src = src + "<source src='" + s + "'></source>";
      });
      $(audio).attr('onDemand', '').html(src);
      audio.load();
    }
  }
/**
  @param callback function
  Sets the playFinished of the AudioLogic to the passed callback function.
  That function is called everytime that an audio finished playing.
*/
, setPlayFinished: function(callback){
    this.playFinished = callback;
  }
/**
  @param audio HTML5 audio tag
  Logs the event of playing a sound with GoogleAnalytics
*/
, log: function(audio){
    var src = $('source', audio).attr('src');
    var r   = /.*sound\/(.*)\.(ogg|mp3)/;
    if(r.test(src)){
      src = r.exec(src)[1];
      var count = (this.playCounts[src] || 0) + 1;
      this.playCounts[src] = count;
      window.App.logger.logEvent('AudioLogic', 'play', src, count);
    }
  }
});
