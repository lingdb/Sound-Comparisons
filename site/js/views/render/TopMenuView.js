/* global Date: true */
"use strict";
define(['backbone'], function(Backbone){
  /**
    The TopMenuView will be used by the Renderer.
    The TopMenuView will set it's own model to handle and smartly update it's render data.
  */
  return Backbone.View.extend({
    initialize: function(){
      this.model = {
        formats: ['mp3','ogg'],
        IPATooltipFontSize: ['100%', '125%', '150%'],
        ShowDataAs: [
          { val:'dots',   display:'dots' },
          { val:'labels', display:'labels' }
        ],
        ColoriseDataAs: [
          { val:'region',  display:'region' },
          { val:'cognate', display:'cognate'}
        ],
        isOnline: (window.location.protocol !== 'file:'),
        isOffline: (window.location.protocol === 'file:')
      };
      // init IPATooltipFontSize from stored cookies if given
      var nameFontSize = "IPATooltipFontSize=";
      var ca = document.cookie.split(';');
      var fontSize = '100%';
      for(var i = 0; i <ca.length; i++) {
          var c = ca[i];
          while (c.charAt(0) == ' ') {
              c = c.substring(1);
          }
          if (c.indexOf(nameFontSize) == 0) {
              fontSize = c.substring(nameFontSize.length, c.length);
              break;
          }
      }
      App.storage.IPATooltipFontSize = fontSize;
      // init ShowDataAs from stored cookies if given
      var nameShowDataAs = "ShowDataAs=";
      var ca = document.cookie.split(';');
      var showDataAs = 'labels';
      for(var i = 0; i <ca.length; i++) {
          var c = ca[i];
          while (c.charAt(0) == ' ') {
              c = c.substring(1);
          }
          if (c.indexOf(nameShowDataAs) == 0) {
              showDataAs = c.substring(nameShowDataAs.length, c.length);
              break;
          }
      }
      App.storage.ShowDataAs = showDataAs;
      // init ColoriseDataAs from stored cookies if given
      var nameColoriseDataAs = "ColoriseDataAs=";
      var ca = document.cookie.split(';');
      var coloriseDataAs = 'region';
      for(var i = 0; i <ca.length; i++) {
          var c = ca[i];
          while (c.charAt(0) == ' ') {
              c = c.substring(1);
          }
          if (c.indexOf(nameColoriseDataAs) == 0) {
              coloriseDataAs = c.substring(nameColoriseDataAs.length, c.length);
              break;
          }
      }
      App.storage.ColoriseDataAs = coloriseDataAs;
    }
    /**
      Function to call non /^update.+/ methods that are necessary for the model, and to setup their callbacks.
    */
  , activate: function(){
      //Setting callbacks to update model:
      App.translationStorage.on('change:translationId', this.buildStatic, this);
      //Building statics the first time:
      this.buildStatic();
    }
    /**
      Overwrites the current model with the given one performing a deep merge.
    */
  , setModel: function(m){
      this.model = $.extend(true, this.model, m);
    }
    /**
      Builds the static translations for the model.
    */
  , buildStatic: function(){
      var staticT = App.translationStorage.translateStatic({
        logoTitle:          'website_logo_hover'
      , csvTitle:           'topmenu_download_csv'
      , tsvTitle:           'topmenu_download_tsv'
      , sndTitle:           'topmenu_download_zip'
      , cogTitle:           'topmenu_download_cogTitle'
      , settingTitle:       'topmenu_settings_title'
      , wordByWord:         'topmenu_download_wordByWord'
      , format:             'topmenu_download_format'
      , ipaTooltipFontSize: 'topmenu_settings_ipaFontSizeMap'
      , showDataAs:         'topmenu_settings_showDataAs'
      , showDataAsDots:     'topmenu_settings_showDataAsDots'
      , showDataAsLabels:   'topmenu_settings_showDataAsLabels'
      , coloriseDataAs:     'topmenu_settings_coloriseDataAs'
      , coloriseDataAsRegion:     'topmenu_settings_coloriseDataAsRegion'
      , coloriseDataAsCognate:    'topmenu_settings_coloriseDataAsCognate'
      , soundClickTitle:    'topmenu_soundoptions_tooltip'
      , soundHoverTitle:    'topmenu_soundoptions_hover'
      , createShortLink:    'topmenu_createShortLink'
      , viewContributors:   'topmenu_about_whoarewe'
      });
      this.setModel(staticT);
      this.model.ShowDataAs[0].display = this.model.showDataAsDots;
      this.model.ShowDataAs[1].display = this.model.showDataAsLabels;
      this.model.ColoriseDataAs[0].display = this.model.coloriseDataAsRegion;
      this.model.ColoriseDataAs[1].display = this.model.coloriseDataAsCognate;
    }
    /**
      Generates the study part of the TopMenu.
      A name == "--" will be marked as 'divider'.
    */
  , updateStudy: function(){
      var data = {
        currentStudyName: App.study.getName()
      };
      data.studies = _.map(App.study.getAllIds(), function(n){
        var name = App.study.getName(n)
          , link = App.study.getLink(n);
        return {
          currentStudy: name === data.currentStudyName
        , isDivider: name === "--"
        , link: 'href="'+link+'"'
        , studyName: name
        };
      }, this);
      this.setModel(data);
    }
    /**
      Generates the PageViews part of the TopMenu.
    */
  , updatePageViews: function(){
      var hovers = App.translationStorage.translateStatic({
        m:  'topmenu_views_mapview_hover'
      , w:  'topmenu_views_wordview_hover'
      , l:  'topmenu_views_languageview_hover'
      , lw: 'topmenu_views_multiview_hover'
      , wl: 'topmenu_views_multitransposed_hover'
      });
      var names = App.translationStorage.translateStatic({
        m:  'topmenu_views_mapview'
      , w:  'topmenu_views_wordview'
      , l:  'topmenu_views_languageview'
      , lw: 'topmenu_views_multiview'
      , wl: 'topmenu_views_multitransposed'
      });
      var images = {
        m:  'maps.png'
      , w:  '1w.png'
      , l:  '1l.png'
      , lw: 'lw.png'
      , wl: 'wl.png'
      };
      var getDefaults = function(pvk){
        return {
          words:     App.wordCollection.getSelected(pvk)
        , languages: App.languageCollection.getSelected(pvk)
        };
      };
      var links = {
        m:  App.router.linkMapView()
      , w:  App.router.linkWordView()
      , l:  App.router.linkLanguageView()
      , lw: App.router.linkLanguageWordView(getDefaults('languagesXwords'))
      , wl: App.router.linkWordLanguageView(getDefaults('wordsXlanguages'))
      };
      this.setModel({pageViews: _.map(_.keys(names), function(key){
        return {
          link:    'href="'+links[key]+'"'
        , content: this.tColor(key, names[key])
        , title:   hovers[key]
        , img:     images[key]
        , active:  App.pageState.isPageView(key)};
      }, this)});
    }
    /**
      Generates the translations part of the TopMenu.
    */
  , updateTranslations: function(){
      this.setModel({
        currentFlag: App.translationStorage.getFlag()
      , otherTranslations: _.chain(App.translationStorage.getOthers()).map(function(tId){
          return {
            tId: tId
          , flag: this.getFlag(tId)
          , name: this.getName(tId)
          };
        }, App.translationStorage).sortBy('name').value()
      });
    }
    /**
      Generates the about/info links part of the TopMenu.
    */
  , updateEntries: function(){
      var entries = App.translationStorage.translateStatic([
        { link:  'topmenu_about_furtherinfo_href'
        , about: 'topmenu_about_furtherinfo'}
      , { link:  'topmenu_about_research_href'
        , about: 'topmenu_about_research'}
      , { link:  'topmenu_about_contact_href'
        , about: 'topmenu_about_contact'}
      , { link:  'topmenu_about_imprint_href'
        , about: 'topmenu_about_imprint'}
      , { link:  'topmenu_about_privacypolicy_href'
        , about: 'topmenu_about_privacypolicy'}
      ]);
      _.each(entries, function(e){
        e.target = (e.link.match(/#/)) ? '' : 'target="_blank"';
        e.link = 'href="'+e.link+'"';
      });
      this.setModel({aboutEntries: entries});
    }
    /**
      Reflects the current SoundPlayOption:
    */
  , updatePlayOption: function(){
      this.setModel({soundOptionHover: App.soundPlayOption.playOnHover()});
    }
    /***/
  , updateCsvLink: function(){
      var s = App.study.getId(), ls = [], ws = [];
      if(App.pageState.isMultiView()){
        ls = App.languageCollection.getSelected();
        ws = App.wordCollection.getSelected();
      }else if(App.pageState.isPageView('l')){
        ls = [App.languageCollection.getChoice()];
        ws = App.wordCollection.models;
      }else if(_.any(['m','w'], App.pageState.isPageView, App.pageState)){
        ls = App.languageCollection.models;
        ws = [App.wordCollection.getChoice()];
      }
      var go = function(xs){return _.map(xs, function(x){return x.getId();}).join(',');};
      var w = go(ws), l = go(ls);
      this.setModel({csvLink: 'export/csv?study='+s+'&languages='+l+'&words='+w});
      this.setModel({tsvLink: 'export/csv?study='+s+'&languages='+l+'&words='+w+'&tsv'});
    }
    /***/
  , render: function(){
      this.$el.html(App.templateStorage.render('TopMenu', {TopMenu: this.model}));
      //The wordByWord option:
      var wordByWord = this.$('#wordByWordCheckbox').click(function(){
        App.pageState.set({wordByWord: wordByWord.is(':checked')});
        App.views.renderer.render();
      });
      if(App.pageState.get('wordByWord')){
        wordByWord.prop('checked', true);
      }
      //Download sounds:
      var sndBtn = this.$('a.soundFile').click(function(){
        App.soundDownloader.download().done(function(msg){
          sndBtn.removeClass('btn-danger');
          window.saveAs(msg.data, msg.name);
        }).fail(function(f){
          var msg = 'An error occured when trying to download sound files:\n'+f;
          console.log(msg);
          window.alert(msg);
        });
        sndBtn.addClass('btn-danger');
      });
      //The wordByWordFormat selection:
      var radios = this.$('input[name="wordByWordFormat"]').click(function(){
        var val = $(this).val();
        if(val !== App.pageState.get('wordByWordFormat')){
          App.pageState.set({wordByWordFormat: val});
          App.views.renderer.render();
        }
      }).each(function(){
        var t = $(this), val = t.val();
        if(val === App.pageState.get('wordByWordFormat')){
          t.prop('checked', true);
        }
      });
      //The IPATooltipFontSize selection:
      var radios = this.$('input[name="IPATooltipFontSize"]').click(function(){
        var val = $(this).val();
        if(val !== App.storage.IPATooltipFontSize){
          App.storage.IPATooltipFontSize = val;
          // save font size as cookie
          var d = new Date();
          d.setTime(d.getTime() + (365*24*60*60*1000));
          var expires = "expires="+ d.toUTCString();
          document.cookie = 'IPATooltipFontSize='+ val + ";" + expires + ";path=/";
          App.views.renderer.render();
        }
      }).each(function(){
        var t = $(this), val = t.val();
        if(val === App.storage.IPATooltipFontSize){
          t.prop('checked', true);
        }
      });
      //The ShowDataAs selection:
      var radios = this.$('input[name="ShowDataAs"]').click(function(){
        var val = $(this).val();
        if(val !== App.storage.ShowDataAs){
          App.storage.ShowDataAs = val;
          // save font size as cookie
          var d = new Date();
          d.setTime(d.getTime() + (365*24*60*60*1000));
          var expires = "expires="+ d.toUTCString();
          document.cookie = 'ShowDataAs='+ val + ";" + expires + ";path=/";
          App.views.renderer.render();
        }
      }).each(function(){
        var t = $(this), val = t.val();
        if(val === App.storage.ShowDataAs){
          t.prop('checked', true);
        }
      });
      //The ColoriseDataAs selection:
      var radios = this.$('input[name="ColoriseDataAs"]').click(function(){
        var val = $(this).val();
        if(val !== App.storage.ColoriseDataAs){
          App.storage.ColoriseDataAs = val;
          // save font size as cookie
          var d = new Date();
          d.setTime(d.getTime() + (365*24*60*60*1000));
          var expires = "expires="+ d.toUTCString();
          document.cookie = 'ColoriseDataAs='+ val + ";" + expires + ";path=/";
          App.views.renderer.render();
        }
      }).each(function(){
        var t = $(this), val = t.val();
        if(val === App.storage.ColoriseDataAs){
          t.prop('checked', true);
        }
      });
      //The SoundPlayOption:
      var options = this.$('#topmenuSoundOptions img').click(function(){
        App.soundPlayOption.set({playMode: this.attributes.value.value});
        options.each(function(){
          $(this).toggleClass('hide');
        });
      });
      //The shortLink button:
      var shortLink = this.$('#createShortLink').click(function(){
        App.dataStorage.addShortLink().done(function(data){
          App.views.shortLinkModalView.render(data);
        }).fail(function(){
          console.log('User could not create short link!');
          var msg = App.translationStorage.translateStatic('shortLinkCreationFailed');
          window.alert(msg);
        });
      });
      //The Contributor button:
      this.$('#openContributors').click(function(){
        App.router.navigate('#/Contributors');
      });
    }
    /**
      Helper method to color strings for updatePageViews.
      @param mode is expected to be an enum like string
      @param content is expected to be a string.
      @return content html string
    */
  , tColor: function(mode, content){
      //Sanitizing:
      if(!_.isString(content)) content = '';
      //Data to operate on:
      var modes = {
        m:  'color-map'
      , w:  'color-word'
      , l:  'color-language'
      , lw: {c1: 'color-language', c2: 'color-word'}
      , wl: {c1: 'color-word', c2: 'color-language'}
      };
      var color = modes[mode];
      if(_.isString(color)){
        return '<div class="inline '+color+'">'+content+'</div>';
      }else if(_.isObject(color)){
        var matches = content.match(/^(.*) [Xx×] (.*)$/);
        if(matches){
          var m1 = matches[1], m2 = matches[2];
          return '<div class="inline '+color.c1+'">'+m1+'</div>×<div class="inline '+color.c2+'">'+m2+'</div>';
        }
        return '<div class="inline color-map">'+content+'</div>';
      }
      console.log('Unexpected behaviour in TopMenuView.tColor() with mode: '+mode);
      return content;
    }
  });
});
