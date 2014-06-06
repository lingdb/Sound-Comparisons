/**
  el: .loadingBar
  model: undefined
*/
LoadingBar = Backbone.View.extend({
  initialize: function(){
    this.segments = 0;
    this.loaded   = 0;
  }
, addSegment: function(s){
    this.segments += s || 1;
    return this.segments;
  }
, addLoaded: function(l){
    this.loaded += l || 1;
    this.loaded %= this.segments;
    this.render();
    return this.loaded;
  }
, render: function(){
    if(this.segments === 0 || this.loaded === 0){
      this.$el.css('width', '0px');
    }else{
      var w = Math.floor(this.loaded / this.segments * $(window).width());
      this.$el.css('width', w+'px');
    }
    return this;
  }
});
