/* global HideLinks: true */
"use strict";
var HideLinks = Backbone.View.extend({
  initialize: function(){
    this.content = $('#contentArea');
    this.links   = {}; // name -> jQuery
    var view     = this;
    $('a.hidelink').click(function(){
      view.click($(this));
    }).each(function(){
      var t = $(this)
        , n = t.data('name');
      view.links[n] = t;
    });
  }
, toggleChevron: function(t){
    if(t.hasClass('icon-chevron-right')){
      t.addClass('icon-chevron-left').removeClass('icon-chevron-right');
    }else if(t.hasClass('icon-chevron-left')){
      t.addClass('icon-chevron-right').removeClass('icon-chevron-left');
    }
  }
, getSpan: function(t){
    for(var i = 1; i <= 12; i++)
      if(t.hasClass('span'+i))
        return i;
    return 0;
  }
, deltaSpan: function(t, d){
    var s = this.getSpan(t); 
    t.removeClass('span'+s).addClass('span'+(s+d));
  }
, click: function(t){
    this.toggleChevron(t.find('i'));
    var target = $(t.data('target'))
      , delta  = this.getSpan(target);
    if(target.is(':visible')){
      target.hide();
      this.deltaSpan(this.content, delta);
    }else{
      target.show();
      this.deltaSpan(this.content, -delta);
    }
    //Telling the MapView to adjust:
    if(window.App.views.mapView){
      window.App.views.mapView.adjustCanvasSize();
    }
    //Adjusting nanoScrollers:
    window.App.views.renderer.nanoScroller();
  }
});
