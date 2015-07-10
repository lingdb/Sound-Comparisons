"use strict";
define(['backbone','models/PlaySequence'], function(Backbone, PlaySequence){
  return Backbone.View.extend({
    update: function(pageView){
      var p; // variable for PlaySequence
      //mapView and singleLanguageView ship their own solutions,
      //we care for the rest:
      switch(pageView){
        case 'word':
          p = new PlaySequence($('#wordHeadline_playAll'));
          $('#singleWordTable audio').each(function(){
            p.add(this);
          });
        break;
        case 'language':
          p = new PlaySequence($('#language_playAll'));
          $('#languageTable td:visible audio').each(function(){
            p.add(this);
          });
        break;
        case 'languagesXwords':
          //Play all in one language:
          $('.multitablePlayWe').each(function(){
            var p = new PlaySequence($(this));
            $(this).closest('tr').find('.transcription audio').each(function(){
              p.add(this);
            });
          });
          //Play all for one word:
          $('.multitablePlayNs').each(function(){
            var p = new PlaySequence($(this));
            var i = $(this).closest('th').index();
            $('#multitable tr').each(function(){
              var b = $('td:first-child', this).is('.regionCell');
              var j = b ? i + 1 : i;
              $('td:nth-child('+j+') audio', this).each(function(){
                p.add(this);
              });
            });
          });
        break;
        case 'wordsXlanguages':
          //Play all for one word:
          $('.multitablePlayWe').each(function(){
            var p = new PlaySequence($(this));
            $(this).closest('tr').find('.transcription audio').each(function(){
              p.add(this);
            });
          });
          //Play all in one language:
          $('.multitablePlayNs').each(function(){
            var p = new PlaySequence($(this));
            var i = $(this).closest('th').index();
            if($('#multitabletrans .regionCell').length === 0) i++;
            $('#multitabletrans tr').each(function(){
              var b = $('td:first-child', this).is('.regionCell');
              var j = b ? i + 1 : i;
              $('td:nth-child('+j+') audio', this).each(function(){
                p.add(this);
              });
            });
          });
        break;
      }
    }
  });
});
