"use strict";
define(['backbone'], function(Backbone){
  return Backbone.View.extend({
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
      this.setHover();
      window.App.translationStorage.on('change:translationId', this.setHover, this);
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
    /**
      It is possible to have a.proxyHideLink, which only have a data-name.
      If they are clicked, we shall act as if the original hidelink with the same data-name was clicked.
      To safeguard against being called multiple times without sense,
      the data-handleProxy attributes are used.
    */
  , handleProxyHideLinks: function(){
      //Proxy hideLinks as used in MultiTables for #171:
      var view = this;
      $('a.proxyHideLink').each(function(){
        var a = $(this);
        if(a.data('handleProxy')) return; // Safeguard
        a.data('handleProxy', 1).click(function(){
          var n = $(this).data('name');
          view.links[n].trigger('click');
        });
      });
    }
    /**
      Setting the correct translation for hover texts.
      This became apparent as a problem in #368.
    */
  , setHover: function(){
      _.each(this.links, function($t, name){
        $t.attr('title', window.App.translationStorage.translateStatic(name));
      }, this);
    }
  });
});
