/**
  el:    google.maps.Map
  model: WordOverlay
*/
if(typeof(google) !== 'undefined'){
  WordOverlayView = Backbone.View.extend({
    initialize: function(){
      this.model.set({view: this});
      this.model.on('change:edge', this.edgeChanged, this);
      this.setMap(this.el);
    }
  // Called when a WordOverlayView is added to a map:
  , onAdd: function(){
      //Creating the div:
      var div   = document.createElement("div")
        , color = this.model.get('color');
      //Calculating the color values:
      color.opacity /= 100;
      /*
        Settings from Paul:
        50:  Keep color as is
        100: Blackest setting allowed
        Wanted darkest  lum: 80
        Wanted lightest lum: 200
        let y = (x - 50)/50      | How much black to add from 0 to 1
            t = (200 - 80) * y   | How much of the distance between both extrems shall be walked
            z = 200 - t          | The darkness we want to have
            l = z / -(200-80)    | The lum parameter in [-1,0] to change the darkness
        in  (200 - (200-80) * (x - 50)/50) / -(200-80)
        Best current mapping: (200 - (200-80) * (ColorDepth - 50)/50)
        Simplyfied:           ColorDepth * -2.4 + 320
        Formula from Paul:    ((ColorDepth - 1) * 240) + 80
      */
      color.color = this.getColorDepth(color.color, color.colorDepth);
      //Filling and styling the div:
      $(div).addClass('mapAudio', 'audio')
            .html(this.model.get('content'))
            .css('background-color', color.color)
            .css('opacity', color.opacity);
      //Handling the callbacks:
      $('.transcription', div).each(function(){
        var audio = $(this).next().get(0);
        if(audio){
          $(this).on('click mouseover touchstart', function(e){
            if(e.type === 'mouseover')
              if(!App.soundPlayOption.playOnHover())
                return;
            window.App.views.audioLogic.play(audio);
          });
        }
      });
      //Adding the div to the panes:
      var panes = this.getPanes();
      panes.overlayMouseTarget.appendChild(div);
      //Creating the marker:
      var marker = new google.maps.Marker({
        icon: {
          fillColor:    '#000000'
        , fillOpacity:  1
        , path:         google.maps.SymbolPath.CIRCLE
        , scale:        5
        , strokeWeight: 0
        }
      , map:      this.el
      , position: this.model.get('position')
      , title:    this.model.get('hoverText')
      , visible:  true
      });
      //Saving stuff:
      this.model.set({
        div:    div
      , marker: marker
      , added:  true
      });
    }
  , getPoint: function(){
      var p = this.model.get('position');
      return this.getProjection().fromLatLngToDivPixel(p);
    }
  , draw: function(){
      var bbox = this.model.getBBox()
        , div  = this.model.get('div');
      div.style.left = bbox.x1 + 'px';
      div.style.top  = bbox.y1 + 'px';
    }
  , onRemove: function(){
      this.model.get('div').parentNode.removeChild(this.div);
      this.model.get('marker').setMap(null);
      this.model.set({div: null, marker: null});
    }
  , onScreen: function(){
      var p = this.model.get('position');
      return this.el.getBounds().contains(p);
    }
  , edgeChanged: function(){
      if(this.model.get('added'))
        this.draw();
    }
  //A hand full of color functions to follow below:
  , getColorDepth: function(hex, colorDepth){
      var lum = colorDepth * -2.4 + 320;
      return this.getLuminocity(hex, lum);
    }
  , getLuminocity: function(hex, lum){
    var rgb = this.splitRgb(hex);
    var hsl = this.rgbToHsl(rgb[0], rgb[1], rgb[2]); // Apply or call here
    hsl[2]  = lum;
    rgb     = this.hslToRgb(hsl[0], hsl[1], hsl[2]); // Apply or call here
    return  this.mergeRgb(rgb);
  }
  // From http://stackoverflow.com/questions/5623838/rgb-to-hex-and-hex-to-rgb
  , splitRgb: function(hex){
      var result = /^#?([a-f\d]{2})([a-f\d]{2})([a-f\d]{2})$/i.exec(hex)
        , ret    = [], i;
      for(i = 1; i < 4; i++)
        ret.push(parseInt(result[i], 16));
      return ret;
  }
  // With a little help from http://www.sitepoint.com/javascript-generate-lighter-darker-color/
  , mergeRgb: function(rgb){
    var hex = '#', i, c;
    for(i = 0; i < rgb.length; i++){
      c = rgb[i].toString(16);
      hex += ('00'+c).substr(c.length);
    }
    return hex;
  }
  //Color functions below adapted from http://stackoverflow.com/questions/2353211/hsl-to-rgb-color-conversion
   /**
    * Converts an HSL color value to RGB. Conversion formula
    * adapted from http://en.wikipedia.org/wiki/HSL_color_space.
    * Assumes h, s, and l are contained in the set [0, 1] and
    * returns r, g, and b in the set [0, 255].
    *
    * @param   Number  h       The hue
    * @param   Number  s       The saturation
    * @param   Number  l       The lightness
    * @return  Array           The RGB representation
    */
  , hslToRgb: function(h, s, l){
      var r, g, b;
      if(s == 0){
        r = g = b = 1; // achromatic
      }else{
        function hue2rgb(p, q, t){
          if(t < 0) t += 1;
          if(t > 1) t -= 1;
          if(t < 1/6) return p + (q - p) * 6 * t;
          if(t < 1/2) return q;
          if(t < 2/3) return p + (q - p) * (2/3 - t) * 6;
          return p;
        }

        var q = l < 0.5 ? l * (1 + s) : l + s - l * s;
        var p = 2 * l - q;
        r = hue2rgb(p, q, h + 1/3);
        g = hue2rgb(p, q, h);
        b = hue2rgb(p, q, h - 1/3);
      }
      return [r*255, g*255, b*255];
    }
   /**
    * Converts an RGB color value to HSL. Conversion formula
    * adapted from http://en.wikipedia.org/wiki/HSL_color_space.
    * Assumes r, g, and b are contained in the set [0, 255] and
    * returns h, s, and l in the set [0, 1].
    *
    * @param   Number  r       The red color value
    * @param   Number  g       The green color value
    * @param   Number  b       The blue color value
    * @return  Array           The HSL representation
    */
  , rgbToHsl: function(r, g, b){
      r /= 255, g /= 255, b /= 255;
      var max = Math.max(r, g, b), min = Math.min(r, g, b);
      var h, s, l = (max + min) / 2;

      if(max == min){
        h = s = 0; // achromatic
      }else{
        var d = max - min;
        s = l > 0.5 ? d / (2 - max - min) : d / (max + min);
        switch(max){
          case r: h = (g - b) / d + (g < b ? 6 : 0); break;
          case g: h = (b - r) / d + 2; break;
          case b: h = (r - g) / d + 4; break;
        }
        h /= 6;
      }

      return [h, s, l];
    }
  });
  WordOverlayView.prototype = _.extend(
    WordOverlayView.prototype
  , google.maps.OverlayView.prototype
  );
}
