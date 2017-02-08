"use strict";
define(['underscore',
        'jquery',
        'leaflet',
        'leaflet-markercluster',
        'leaflet.dom-markers'],
       function(_, $, L){
  /**
    Function to handle audio in .transcription parts in markers.
  */
  var handleAudio = function(div){
    $('.transcription', div).each(function(){
      var audio = $(this).next().get(0);
      if(audio){
        $(this).on('click mouseover touchstart', function(e){
          if(e.type === 'mouseover')
            if(!window.App.soundPlayOption.playOnHover())
              return;
          window.App.views.audioLogic.play(audio);
        });
//      //Logic to set WordOverlay.playing accordingly:
//      $(audio).on('play', function(){
//        o.model.playing(true);
//      }).on('ended pause', function(){
//        o.model.playing(false);
//      });
      }
    });
  };
  return {
    /**
      The task of this function shall be to place a transcription marker
      on a leaflet map.
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
    'mkWordMarker': function(map, data){
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
                + 'data-onDemand=\'' + JSON.stringify(sf.soundfiles) + '\' '
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
             + '&nbsp;' + sf.phonetic + '&nbsp;&nbsp;</div>'
             + audio+'</div>';
      }, this);
      content = content.join('');
      //Creating the div:
      var div = document.createElement('div')
        , $div = $(div).addClass('mapAudio', 'audio')
                       .html(content)
                       .css('background-color', data.color)
                       .attr('title', data.langName);
      //Adding a marker to the map:
      var icon = L.DomMarkers.icon({
        element: div,
        iconSize: L.point(40, 20)});
      //Add a way to fetch data from the icons options:
      icon.options.getData = function(){
        return data;
      };
      var marker = L.marker(data.latlng, {icon: icon});
      //Connect audio events when marker is added:
      marker.on('add', _.bind(handleAudio, null, div));
      //Generating a marker id:
      marker.id = _.uniqueId('WordMarker');
      //Function to fix the marker size:
      marker.fixSize = function(){
        var w = 0, h = 0;
        $div.find('.transcription').each(function(){
          var t = $(this);
          w = Math.max(w, t.width());
          h += 1.085*t.height();
        });
        //Update size:
        icon.options.iconSize = [w, h];
        this.setIcon(icon);
      };
      //Returning generated structures; expanding data:
      return _.extend(data, {
        content: content
      , marker: marker
      , getAudio: function(){
          return $div.find('audio').get();
        }
      });
    },
    /**
      This is a function that generates
      a cluster icon for several WordMarkers.
      cluster is described in
      https://github.com/Leaflet/Leaflet.markercluster#clusters-methods
    */
    'mkCluster': function(cluster){
      //div to embed into:
      var div = document.createElement('div')
        , $div = $(div)
        , w = 0, h = 0; // Sizes to calculate:
      _.each(cluster.getAllChildMarkers(), function(marker){
        var iOps = marker.options.icon.options;
        //Append icon to $div:
        $div.append(iOps.getData().content);
        //Updating size calculations:
        var size = iOps.iconSize;
        w = Math.max(w, size[0]);
        h += size[1];
      }, this);
      var cIcon = L.DomMarkers.icon({
        element: div,
        className: 'mapAudio',
        iconSize: L.point(w, h)});
      return cIcon;
    }
  };
});
