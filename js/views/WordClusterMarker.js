"use strict";
define(['underscore',
        'leaflet',
        'leaflet-markercluster'],
       function(_, L){
  /**
    The task of this module is to provide a function that generates
    a cluster icon for several markers generated from the WordMarker module.
    cluster is described in
    https://github.com/Leaflet/Leaflet.markercluster#clusters-methods
  */
  return function(cluster){
    var n = cluster.getChildCount();
    return L.divIcon({html: 'merged: ' + n,
                      className: 'mapAudio',
                      iconSize: L.point(40, 40)});
  };
});
