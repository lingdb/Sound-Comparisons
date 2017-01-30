"use strict";
define(['views/render/SubView'], function(SubView){
  return SubView.extend({
    initialize: function(){
      this.model = {};
      //Connecting to the router
      App.router.on('route:languageWordView', this.route, this);
      App.router.on('route:languageWordView_', function(s,w,l){return this.route(s,l,w);}, this);
    }
    /**
      Method to make it possible to check what kind of PageView this Backbone.View is.
    */
  , getKey: function(){return 'languagesXwords';}
    /**
      Function to call non /^update.+/ methods that are necessary for the model, and to setup their callbacks.
    */
  , activate: function(){
      //Setting callbacks to update model:
      App.translationStorage.on('change:translationId', this.buildStatic, this);
      //Building statics the first time:
      this.buildStatic();
    }
    /***/
  , buildStatic: function(){
      //We reuse WordLanguageView.buildStatic:
      App.views.renderer.model.wordLanguageView.buildStatic.call(this);
    }
    /***/
  , updateTable: function(){
      //Setup:
      var words = App.wordCollection.getSelected()
        , rBkts = App.regionCollection.getRegionBuckets(App.languageCollection.getSelected())
        , rMap  = rBkts.rMap
        , lMap  = rBkts.lMap
        , table = {
            clearWordsLink:     'href="'+App.router.linkLanguageWordView({words: []})+'"'
          , clearLanguagesLink: 'href="'+App.router.linkLanguageWordView({languages: []})+'"'
          , transposeLink:      'href="'+App.router.linkWordLanguageView({
              words: words
            , languages: App.languageCollection.getSelected()
            })+'"'
          };
      //The Words:
      if(words.length === 0){//Faking words, if none selected:
        var tStart = App.translationStorage.translateStatic('tabulator_multi_wordcol');
        table.words = _.map([1,2,3], function(i){
          return {isFake: true, trans: tStart+' '+i};
        }, this);
      }else{//We've actually got some words \0/
        var spLang = App.pageState.getSpLang()
          , base = App.translationStorage.translateStatic({
          deleteTtip: 'tabulator_multi_tooltip_removeWord'
        , playTtip:   'tabulator_multi_playword'
        });
        base.isFake = false;
        table.words = _.map(words, function(w){
          var remaining = App.wordCollection.getDifference(words, [w])
            , alts = w.getModernName();
          if(_.isArray(alts)) alts = alts.join(', ');
          var ln = w.getLongName();
          return _.extend({}, base, {
            deleteLink: 'href="'+App.router.linkCurrent({words: remaining})+'"'
          , link:       'href="'+App.router.linkWordView({word: w})+'"'
          , ttip:       (!_.isEmpty(ln)) ? " title='"+ln+"'" : ''
          , trans:      alts
          , map: {
              link: 'href="'+App.router.linkMapView({word: w})+'"'
            , ttip: App.translationStorage.translateStatic('tooltip_words_link_mapview')
            }
          });
        }, this);
      }
      //The Regions:
      if(_.values(rMap).length === 0){//No regions to deal with, faking them:
        var lgs = _.map([1,2,3], function(i){
          var ts = _.map([0,1,2], function(j){
            return {
              isFake: true
            , fake: (i !== 2 && j !== 1) ? App.translationStorage.translateStatic('tabulator_multi_cell'+(i-1)+j) : ''
            };
          }, this);
          if(words.length > 3){
            ts.push({isFake: true, colspan: words.length - 3});
          }
          return {
            isFake: true
          , shortName: App.translationStorage.translateStatic('tabulator_multi_langrow')+' '+i
          , transcriptions: ts
          , isFirst: i === 1
          };
        }, this);
        table.regions = [{isFake: true, languages: lgs}];
      }else{//Deal with the regions as expected:
        var clearRow = {colspan: (words.length === 0) ? 6 : words.length + 3}
          , lastFamily = -1;
        table.regions = _.map(rMap, function(r, rId){
          var rgn = {
            isFake: false
          , rspan:  lMap[rId].length
          , rColor: r.getColor()
          , name:   r.getShortName()
          }, fId = r.getFamily().getId();
          //Handling of clearRows:
          if(fId !== lastFamily){
            lastFamily   = fId;
            rgn.clearRow = clearRow;
          }
          //The Languages:
          var lBase = App.translationStorage.translateStatic({
            deleteTtip: 'tabulator_multi_tooltip_removeLanguage'
          , playTtip:   'tabulator_multi_playlang'
          });
          rgn.languages = _.map(lMap[rId], function(l, i){
            var remaining = App.languageCollection.getDifference(_.flatten(_.values(lMap)), [l])
              , lng = _.extend({}, lBase, {
              link:         'href="'+App.router.linkLanguageView({language: l})+'"'
            , languageTtip: l.getLongName(false)
            , shortName:    l.getSuperscript(l.getShortName())
            , deleteLink:   'href="'+App.router.linkLanguageWordView({languages: remaining})+'"'
            , first:        i === 0
            });
            //The Transcriptions:
            if(words.length === 0){//Case that we don't have any words:
              lng.transcriptions = _.map([0,1,2], function(j){
                return {
                  isFake: true
                , fake: ((i === 1 || j === 1) && i < 3) ? App.translationStorage.translateStatic('tabulator_multi_cell'+i+j) : ''
                };
              }, this);
            }else{//We've got words, to build our transcriptions with:
              lng.transcriptions = _.map(words, function(w){
                var tr  = App.transcriptionMap.getTranscription(l, w)
                  , tsc = {isFake: false, phonetic: tr.getPhonetics()}
                  , spelling = tr.getAltSpelling();
                if(spelling)
                  tsc.spelling = spelling;
                return tsc;
              }, this);
            }
            //Done with language:
            return lng;
          }, this);
          //Done with a region:
          return rgn;
        }, this);
      }
      //Done:
      _.extend(this.model, table);
    }
    /***/
  , render: function(){
      if(App.pageState.isPageView(this)){
        this.$el.html(App.templateStorage.render('Multitable', {Multitable: this.model}));
        this.$el.removeClass('hide');
        //Updating sound related stuff:
        App.views.audioLogic.findAudio(this.$el);
        App.views.playSequenceView.update(this.getKey());
        //Checking if the alert should be shown:
        var t = this;
        window.setTimeout(function(){t.checkAlert();}, 1000);
      }else{
        this.$el.addClass('hide');
      }
    }
  , checkAlert: function(){
      var t = Math.ceil(this.$('table').width())
        , c = Math.ceil($('#contentArea').width())+1;
      if(t > c){
        this.$('div.alert').removeClass('hide');
        //Making sure proxyHideLinks are useful:
        window.App.views.hideLinks.handleProxyHideLinks();
      }
    }
    /***/
  , route: function(siteLanguage, study, languages, words){
      var parse = App.router.parseString;
      study = parse(study);
      console.log('LanguageWordView.route('+study+', '+languages+', '+words+')');
      var t = this;
      //Setting siteLanguage and study:
      this.loadBasic(siteLanguage, study).always(function(){
        var pv = t.getKey();
        //Setting the languages:
        App.languageCollection.setSelectedByKey(App.router.parseArray(languages),pv);
        //Setting the words:
        App.wordCollection.setSelectedByKey(App.router.parseArray(words),pv);
        //Set this pageView as active:
        App.pageState.setPageView(pv);
        //Render:
        App.views.renderer.render();
      });
    }
  });
});
