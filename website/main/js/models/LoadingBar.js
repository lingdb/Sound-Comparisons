LoadingBar = Backbone.Model.extend({
  defaults: {
    segments: 0
  , loaded:   0
  , finished: true // Allows to track if loading finished.
  }
, addSegment: function(s){
    var x = s || 1;
    x += this.get('segments');
    this.set({segments: x});
    return x;
  }
, addLoaded: function(l){
    var x = l || 1;
    x += this.get('loaded');
    x %= this.get('segments');
    this.set({loaded: x, finished: x > 0});
    return x;
  }
});
