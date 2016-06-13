/*
 * Leaflet.DomMarkers is a simple plugin to create icons with a custom dom element.
 * https://github.com/ValentinH/Leaflet.DomMarkers
 */
(function (window) {
    "use strict";
    var L = window.L;

    L.DomMarkers = {};

    L.DomMarkers.Icon = L.DivIcon.extend({
        options: {
            element: null // a initialized DOM element
            //same options as divIcon except for html
        },

        createIcon: function() {
            if(!this.options.element) return;

            var div = this.options.element;
            this._setIconStyles(div, 'icon');
            return div;
        }
    });

    L.DomMarkers.icon = function(options) {
        return new L.DomMarkers.Icon(options);
    };


}(window));
