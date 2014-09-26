/***/
WordLanguageView = Renderer.prototype.SubView.extend({
  initialize: function(){
    this.model = {};
    //Connecting to the router
    App.router.on('route:wordLanguageView', this.route, this);
  }
  /**
    Method to make it possible to check what kind of PageView this Backbone.View is.
  */
, getKey: function(){return 'wordsXlanguages';}
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
    var rBkts = App.regionCollection.getRegionBuckets(App.languageCollection.getSelected())
      , rMap  = rBkts.rMap // RegionId -> Region
      , lMap  = rBkts.lMap // RegionId -> [Language]
      , wBkts = App.meaningGroupCollection.getMeaningGroupBuckets(App.wordCollection.getSelected())
      , mMap  = wBkts.mMap // MgId -> MeaningGroup
      , wMap  = wBkts.wMap // MgId -> [Word]
      , table = {
          isLogical:          App.pageState.wordOrderIsLogical()
        , clearWordsLink:     'href="'+App.router.linkWordLanguageView({words: []})+'"'
        , clearLanguagesLink: 'href="'+App.router.linkWordLanguageView({languages: []})+'"'
        , transposeLink:      'href="'+App.router.linkLanguageWordView()+'"'
        , rows: []
        };
    /*
      The thead consists of multiple rows: regions, delete and languages, plays.
    */
    //The Regions:
    table.regions = _.map(rMap, function(r, rId){
      return { cspan: lMap[rId].length
             , color: r.getColor()
             , name:  r.getShortName() };
    }, this);
    //The Languages:
    var languages = _.flatten(_.values(lMap));
    if(languages.length === 0){
      var trans = App.translationStorage.translateStatic('tabulator_multi_langrow');
      table.languages = _.map([1,2,3], function(i){
        return {isFake: true, shortName: trans+' '+i};
      }, this);
    }else{
      var basic = _.extend({isFake: false}, App.translationStorage.translateStatic({
        deleteLanguageTtip: 'tabulator_multi_tooltip_removeLanguage'
      , playTtip:           'tabulator_multi_playlang'
      }));
      table.languages = _.map(languages, function(l){
        var remaining = App.languageCollection.getDifference(languages, [l]);
        return _.extend({}, basic, {
          shortName: l.getShortName()
        , ttip:      l.getLongName()
        , link:      'href="'+App.router.linkLanguageView({language: l})+'"'
        , deleteLanguageLink: 'href="'+App.router.linkWordLanguageView({languages: remaining})+'"'
        });
      }, this);
    }
    //thead complete, content for rows:
    //MeaningGroups and Words:
    var meaningGroups = [], words = [];
    if(table.isLogical){
      var clearRow = 3 + (languages.length || 3);
      meaningGroups = _.map(mMap, function(m, mId){
        words.push(wMap[mId]);//We also fill the words:
        return { clearRow: clearRow
               , name:     m.getName()
               , rSpan:    wMap[mId].length };
      }, this);
      words = _.flatten(words);
    }else{ // For non logical order:
      words = _.flatten(_.values(wMap));
      words.sort(App.wordCollection.comparator);
    }
    //Helper function for faking Transcriptions:
    var fakeTrans = function(i, j){
      return { fake: _.all([i < 3, j < 3, i === 3 || j === 3])
             ? App.translationStorage.translateStatic('tabulator_multi_cell'+i+j) : ''
      };
    };
    //Faking words iff necessary:
    if(words.length === 0){
      var trans = App.translationStorage.translateStatic('tabulator_multi_wordcol');
      words = _.map([0,1,2], function(j){
        var w = { fake: true
                , trans: trans+' '+(j+1)
                , isLogical: table.isLogical
                , transcriptions: [] }
          , iMax = languages.length || 3;
        //Transcriptions:
        for(var i = 0; i < iMax; i++){
          w.transcriptions.push(fakeTrans(i, j));
        }
        return w;
      }, this);
    }else{//Filling non-fake words with content:
      var basic = _.extend({fake: false}, App.translationStorage.translateStatic({
            deleteWordTtip: 'tabulator_multi_tooltip_removeWord'
          , playTtip:       'tabulator_multi_playword' }))
        , spLang = App.pageState.getSpLang()
        , mTtip  = App.translationStorage.translateStatic('tooltip_words_link_mapview');
      words = _.map(words, function(w, j){
        var remaining = App.wordCollection.getDifference(words, [w])
          , word = _.extend({}, basic, {
          link:           'href="'+App.router.linkWordView({word: w})+'"'
        , ttip:           w.getLongName()
        , trans:          w.getNameFor(spLang)
        , deleteWordLink: 'href="'+App.router.linkWordLanguageView({words: remaining})+'"'
        , maps:           {link: 'href="'+App.router.linkMapView({word: w})+'"', ttip: mTtip}
        });
        //Transcriptions:
        if(languages.length === 0){
          word.transcriptions = _.map([0,1,2], function(i){return fakeTrans(i,j);}, this);
        }else{
          word.transcriptions = _.map(languages, function(l){
            var tr = App.transcriptionMap.getTranscription(l, w);
            return {spelling: tr.getAltSpelling(), phonetic: tr.getPhonetics()};
          }, this);
        }
        return word;
      }, this);
    }
    //Composing the rows:
    _.each(meaningGroups, function(m){
      var count = m.rSpan;
      _.each(_.first(words, count), function(w, i){
        var row = {words: [w]};
        if(i === 0) row.meaningGroup = m;
        table.rows.push(row);
      }, this);
      words = _.rest(words, count);
    }, this);
    _.each(words, function(w){table.rows.push({words: [w]});}, this);
    //Done:
    _.extend(this.model, table);
  }
  /***/
, render: function(){
    if(App.pageState.isPageView(this)){
      this.$el.html(App.templateStorage.render('MultitableTransposed', {MultitableTransposed: this.model}));
      this.$el.removeClass('hide');
      //Updating sound related stuff:
      App.views.audioLogic.findAudio(this.$el);
      App.views.playSequenceView.update(this.getKey());
    }else{
      this.$el.addClass('hide');
    }
  }
  /***/
, route: function(study, words, languages){
    console.log('WordLanguageView.route('+study+', '+words+', '+languages+')');
    var pv = this.getKey();
    //Setting the study:
    App.study.setStudy(study).always(function(){
      //Setting the words:
      App.wordCollection.setSelectedByKey(App.router.parseArray(words));
      //Setting the languages:
      App.languageCollection.setSelectedByKey(App.router.parseArray(languages));
      //Set this pageView as active:
      App.pageState.setPageView(pv);
      //Render:
      App.views.renderer.render();
    });
  }
});
