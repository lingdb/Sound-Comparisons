/**
  el: .loadingBar
  model: LoadingBar
*/
LoadingBarView = Backbone.View.extend({
  initialize: function(){
    this.model.on('change:loaded', this.render, this);
  }
, render: function(){
    var segments = this.model.get('segments')
      , loaded   = this.model.get('loaded');
    if(segments === 0 || loaded === 0){
      this.$el.css('width', '0px');
    }else{
      var w = Math.floor(loaded / segments * $(window).width());
      this.$el.css('width', w+'px');
    }
  }
});
