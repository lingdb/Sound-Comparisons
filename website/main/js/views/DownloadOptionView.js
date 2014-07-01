/*
  el:    body
  model: DownloadOptions
*/
DownloadOptionView = Backbone.View.extend({
  initialize: function(){
    window.App.loadingBar.onFinish(this.setup, this);
    this.model.on('change:wordByWord', this.wordByWord, this);
    this.model.on('change:format', this.format, this);
    this.setup();
  }
//Setup is called everytime the loadingBar finishes, and on initialize
, setup: function(){
    var chkBox = $('#wordByWordCheckbox')
      , fmts   = $('#topmenuDownloads input[name="wordByWordFormat"]')
      , m      = this.model
      , fmt    = m.get('format');
    //State and events:
    chkBox.prop('checked', m.get('wordByWord'));
    chkBox.click(function(){
      m.set({wordByWord: chkBox.is(':checked')});
    });
    fmts.each(function(){
      var t = $(this), f = t.attr('value');
      t.prop('checked', f === fmt);
      t.click(function(){
        m.set({format: f});
      });
    });
    //Rendering:
    this.wordByWord();
    this.format();
    //Be called again by loadingBar.onFinish:
    return true;
  }
, wordByWord: function(){
    if(this.model.get('wordByWord')){
      this.$('.wordByWord').show();
    }else{
      this.$('.wordByWord').hide();
    }
  }
, format: function(){
    var f = this.model.get('format');
    this.$('.wordByWord.soundFile > a').each(function(){
      var t = $(this), href = t.attr('href')
        , nhref = href.replace(/\.(mp3|ogg|wav)$/, '.'+f);
      t.attr('href', nhref);
    });
    var all  = this.$('#topmenuDownloads a.soundFile')
      , href = all.attr('href')
      , nref = href+"&format="+f;
    if(/format=[^&]{3,4}/.test(href)){
      nref = href.replace(/format=[^&]{3,4}/, "format="+f);
    }
    all.attr('href', nref);
  }
});
