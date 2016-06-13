"use strict";
define(['underscore', 'jquery',
        'leaflet','leaflet.dom-markers'],
       function(_, $, L){
  /**
    The task of this module shall be to place a transcription marker on a leaflet map.
    It replaces the work formerly done by WordOverlay{View}.
    map :: L.map()
    data :: {
      altSpelling:        (tr !== null) ? tr.getAltSpelling() : ''
    , translation:        word.getNameFor(l)
    , latlng:             L.latlng
    , historical:         l.isHistorical() ? 1 : 0
    , phoneticSoundfiles: [{phonetic: String, soundfiles: [[String]]}]
    , langName:           l.getShortName()
    , languageLink:       'href="'+App.router.linkLanguageView({language: l})+'"'
    , familyIx:           l.getFamilyIx()
    , color:              proxyColor(l.getColor())
    , languageIx:         l.getId()
    }
  */
  return function(map, data){
    //Checking if data was already processed:
    if('marker' in data && 'content' in data){
      return data; // Don't process a second time.
    }
    //Generating the marker content:
    var content = _.map(data.phoneticSoundfiles, function(sf){
      //Building audioelements:
      var audio = '';
      if(sf.soundfiles.length > 0){
        audio = '<audio '
              + 'data-onDemand="' + JSON.stringify(sf.soundfiles) + '" '
              + 'autobuffer="" preload="auto"></audio>';
      }
      var fileMissing = ''; //Historical entries -> no files
      if(data.historical === 1 || audio === ""){
        fileMissing = ' fileMissing';
      }
      var smallCaps = (sf.phonetic === 'play')
                    ? ' style="font-variant: small-caps;"' : '';
      if(data.historical === 1){
        sf.phonetic = "*" + sf.phonetic;
      }
      return '<div style="display: inline;">'
           + '<div class="transcription' + fileMissing + '"'+smallCaps+'>'
           + sf.phonetic + '</div>'
           + audio+'</div>';
    }, this);
    content = content.join(',<br>');
    //Creating the div:
    var div = document.createElement('div');
    var $div = $(div).addClass('mapAudio', 'audio')
                     .html(content)
                     .css('background-color', data.color)
                     .attr('title', data.langName);
    //Adding a marker to the map
    var icon = L.DomMarkers.icon({
      element: div,
      iconSize: L.point(40, 40)});
    var marker = L.marker(data.latlng, {icon: icon});
    //Fixing the icon size when the marker is added:
    marker.on('add', function(){
      var w = 0, h = 0;
      $div.find('.transcription').each(function(){
        var t = $(this);
        w = Math.max(w, t.width());
        h += t.height();
      });
      //Fix size plus a tick:
      icon.options.iconSize = [w+2, h+2];
      marker.setIcon(icon);
    });
    //Returning generated structures:
    return _.extend(data, {content: content, marker: marker});
  };
});
