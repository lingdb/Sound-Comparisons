/***/
LanguageWordView = Renderer.prototype.SubView.extend({
  initialize: function(){
    this.model = {};
    //Connecting to the router
    App.router.on('route:languageWordView', this.route, this);
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
    var staticT = App.translationStorage.translateStatic({
      deleteAll:          'tabulator_multi_clear_all'
    , clearWordsText:     'tabulator_multi_clear_words'
    , clearLanguagesText: 'tabulator_multi_clear_languages'
    , transposeTtip:      'tabulator_multi_transpose'
    });
    _.extend(this.model, staticT);
  }
  /***/
, updateTable: function(){
    //Setup:
    var words = App.wordCollection.getSelected()
      , rBkts = App.regionCollection.getRegionBuckets(App.languageCollection.getSelected())
      , rMap  = rBkts.rMap
      , lMap  = rBkts.lMap
      , table = {
          displayMGs:         App.pageState.wordOrderIsLogical() && words.length !== 0
        , clearWordsLink:     'href="'+App.router.linkLanguageWordView({words: []})+'"'
        , clearLanguagesLink: 'href="'+App.router.linkLanguageWordView({languages: []})+'"'
        , transposeLink:      'href="'+App.router.linkWordLanguageView()+'"'
        };
    //The MeaningGroups:
    if(table.displayMGs){
      var mgBkts = App.meaningGroupCollection.getMeaningGroupBuckets(words);
      table.meaningGroups = _.map(mgBkts.mMap, function(mg, mId){
        return {name: mg.getName(), span: mgBkts.wMap[mId].length};
      }, this);
    }
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
          , alts = w.getNameFor(spLang);
        if(_.isArray(alts)) alts = alts.join(', ');
        return _.extend({}, base, {
          deleteLink: 'href="'+App.router.linkCurrent({words: remaining})+'"'
        , link:       'href="'+App.router.linkWordView({word: w})+'"'
        , ttip:       (ln = w.getLongName()) ? " title='"+ln+"'" : ''
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
              , fake: ((i === 1 || j === 1) && i < 3)
                    ? App.translationStorage.translateStatic('tabulator_multi_cell'+i+j) : ''
              };
            }, this);
          }else{//We've got words, to build our transcriptions with:
            lng.transcriptions = _.map(words, function(w){
              var tr  = App.transcriptionMap.getTranscription(l, w)
                , tsc = {isFake: false, phonetic: tr.getPhonetics()};
              if(spelling = tr.getAltSpelling())
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
    }else{
      this.$el.addClass('hide');
    }
  }
  /***/
, route: function(study, languages, words){
    console.log('LanguageWordView.route('+study+', '+languages+', '+words+')');
    var pv = this.getKey();
    //Setting the study:
    App.study.setStudy(study).always(function(){
      //Setting the languages:
      App.languageCollection.setSelectedByKey(App.router.parseArray(languages));
      //Setting the words:
      App.wordCollection.setSelectedByKey(App.router.parseArray(words));
      //Set this pageView as active:
      App.pageState.setPageView(pv);
      //Render:
      App.views.renderer.render();
    });
  }
});
