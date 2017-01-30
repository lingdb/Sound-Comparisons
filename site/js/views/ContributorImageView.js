"use strict";
/* global App */
define(['jquery','bootbox', 'underscore'], function($, bootbox, _){
  var displayImages = function(language){
    var imgs = '';
    _.each(language.getContributorImages(), function(src){
      imgs += '<img src="'+src+'">';
    });
    bootbox.dialog({
      title: '',
      message: imgs,
      buttons: {},
      show: true,
      className: 'contributorImage'
    });
  };
  /**
    When called, this function will bind the click handlers for all
    .contributorImage elements and display the full list of images
    with the aid of bootbox.
    We expect .contributorImage elements to have a data-languageix field.
    @param el :: $ if el ist given, only it's tree shall be used for click handling.
  */
  return function(el){
    //Sanitizing el:
    if(_.isUndefined(el) || _.isNull(el)){
      el = $('body');
    }
    //Searching el:
    el.find('.contributorImage').each(function(){
      var t = $(this), lIx = t.data('languageix');
      var l = _.head(App.languageCollection.where({LanguageIx: ''+lIx}));
      if(!_.isUndefined(l)){
        t.click(function(){
          displayImages(l);
        });
      }
    });
  };
});
