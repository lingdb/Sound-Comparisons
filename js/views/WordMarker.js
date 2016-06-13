"use strict";
define(['underscore',
        'leaflet','leaflet-markercluster'],
       function(_, L){
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
    //Adding a marker to the map
    var icon = L.divIcon({
      html: content,
      className: 'mapAudio',
      iconSize: L.point(40, 40)});
    var marker = L.marker(data.latlng, {icon: icon});
    //Returning generated structures:
    return _.extend(data, {content: content, marker: marker});
  };
});
