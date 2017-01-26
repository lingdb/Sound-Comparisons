"use strict";
define(['jquery','backbone'], function($, Backbone){
  return Backbone.View.extend({
    initialize: function(){
      this.model.on('change:playMode', this.render, this);
      this.render();
    }
  , events: {'click img': 'changeOptions'}
  , changeOptions: function(e){
      var mode = $(e.target).attr('value');
      if(!mode || mode === '') mode = 'hover';
      this.model.set({playMode: mode});
    }
  , render: function(){
      this.$('img').removeClass('hide');
      var mode = this.model.get('playMode');
      if(!mode || mode === '') mode = 'hover';
      this.$('img[value="'+mode+'"]').addClass('hide');
    }
  });
});
