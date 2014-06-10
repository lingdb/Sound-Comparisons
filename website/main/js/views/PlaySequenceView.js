PlaySequenceView = Backbone.View.extend({
  initialize: function(){
    //Looking for the current pageView:
    var regex    = /.*pageView=([^&]*).*/;
    var pageView = regex.exec($('div#saveLocation').attr('href'))[1];
    //mapView and singleLanguageView ship their own solutions,
    //we care for the rest:
    switch(pageView){
      case 'singleWordView':
        var p = new PlaySequence($('#wordHeadline_playAll'));
        $('#singleWordTable audio').each(function(){
          p.add(this);
        });
      break;
      case 'multiWordView':
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
      case 'multiViewTransposed':
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
