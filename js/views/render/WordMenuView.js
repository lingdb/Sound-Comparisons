"use strict";
define(['backbone','views/WordlistFilter'], function(Backbone, WordlistFilter){
  /**
    The WordMenuView will be used by the Renderer.
    It will set it's own model and handle it similar to TopMenuView.
  */
  return Backbone.View.extend({
    initialize: function(){
      //Setting the initial model:
      this.model = {searchFilter: {}};
      //We need a WordlistFilter:
      this.wordlistFilter = new WordlistFilter();
    }
    /**
      Generates the soundPath part of the model for WordMenuView.
    */
  , buildSoundPath: function(){
      var path = '', g = App.dataStorage.get('global');
      if(g){
        if('global' in g && 'soundPath' in g.global){
          path = g.global.soundPath;
        }
      }
      return path;
    }
    /**
      Generates the sortBy part of the model for WordMenuView.
    */
  , updateSortBy: function(){
      var data = App.translationStorage.translateStatic({
        sortBy: 'menu_words_selectortext'
      , aOrder: 'menu_words_selector_rfclangs'
      , lOrder: 'menu_words_sortelicitation'
      });
      data.isLogical = App.pageState.wordOrderIsLogical();
      data.link = data.isLogical ? App.router.linkConfig({WordOrderAlphabetical: []})
                                 : App.router.linkConfig({WordOrderLogical: []});
      data.link = 'data-href="'+data.link+'"';
      _.extend(this.model, {sortBy: data});
    }
    /**
      Since big parts of the searchFilter are already done by updateStatic,
      this method has a focus on building the {sp,ph}List entries.
    */
  , updateSearchFilter: function(){
      var data = App.translationStorage.translateStatic({
        sfby:             'menu_words_filter_head'
      , spelling:         'menu_words_filterspelling'
      , phonetics:        'menu_words_filterphonetics'
      , fonetics:         'menu_words_filter_fonetics'
      , setPhLangHover:   'menu_words_filter_setPhLangHover'
      , psTarget:         'menu_words_filter_regex_link'
      , psHover:          'menu_words_filter_regex_hover'
      , in:               'menu_words_filter_spphin'
      , ipaOpenTitle:     'menu_words_open_ipaKeyboard'
      , filterFoundWords: 'menu_words_filterFoundWords'
      , fAddAll:          'menu_words_filterAddMultiWords'
      , fRefresh:         'menu_words_filterRefreshMultiWords'
      , fClearAll:        'menu_words_filterClearAllWords'
      });
      _.extend(data, {
        spList: {options: []}
      , phList: {}
      , hideFilterButtons: !App.pageState.isMultiView()
      });
      //Setting setPhLang, if 1L view:
      /*
        We only set setPhLang, iff:
        - 1L view is active
        - Current phLang !== languageCollection.getChoice()
      */
      var phLang = App.pageState.getPhLang();
      if(App.pageState.isPageView('l')){
        var language = App.languageCollection.getChoice();
        if(language && phLang){
          if(language.getId() !== phLang.getId()){
            data.setPhLang = App.router.linkConfig({PhLang: language});
          }
        }
      }
      //Filling spList:
      var spLang = App.pageState.getSpLang();
      data.spList.current = spLang ? spLang.getSpellingName()
                                   : App.translationStorage.getName();
      //First item:
      if(spLang){
        data.spList.options.push({
          link: 'data-href="'+App.router.linkConfig({SpLang: null})+'"'
        , name: App.translationStorage.getName()
        });
      }
      //Function to add a seperator:
      var addSeparator = function(options){
        var last = _.last(options);
        if(last && last.disabled){
          return;
        }
        options.push({
          disabled: true
        , name: '---------------'
        });
      };
      //Separator item:
      addSeparator(data.spList.options);
      //Other items:
      var spId = spLang ? spLang.getId() : -1, entries = [];
      App.languageCollection.getSpellingLanguages().each(function(l){
        if(l.getId() === spId) return;
        entries.push({
          link: 'data-href="'+App.router.linkConfig({SpLang: l, PhLang: l})+'"'
        , name: l.getSpellingName()
        });
      }, this);
      //Sort entries, and push them into options:
      entries = _.sortBy(entries, function(e){return e.name;}, this);
      data.spList.options.push.apply(data.spList.options, entries);
      //Filling phList:
      if(phLang){
        var phId = phLang.getId();
        //Initial data for phList
        data.phList.current = phLang.getShortName();
        data.phList.options = [];
        //Other phLangs:
        App.regionCollection.each(function(r){
          addSeparator(data.phList.options);
          r.getLanguages().each(function(l){
            if(l.getId() === phId) return;
            //Checking if we have transcriptions, first word decides.
            var w = App.wordCollection.models[0]
              , t = l.getTranscription(w);
            if(!t.hasPhonetics()) return;
            //Adding as phLang:
            var config = l.isSpellingLanguage() ? {PhLang: l, SpLang: l}
                                                : {PhLang: l};
            data.phList.options.push({
              link: 'data-href="'+App.router.linkConfig(config)+'"'
            , name: l.getShortName()
            });
          }, this);
        }, this);
      }
      //Use App.pageState.get{Sp,Ph}Lang
      _.extend(this.model, {searchFilter: data});
    }
    /**
      @return self for chaining
    */
  , updateWordList: function(){
      var data = App.translationStorage.translateStatic({
        title:       'menu_words_words'
      , meaningSets: 'menu_words_meaningSets_title'
      , expand:      'menu_words_meaningSets_expand'
      , collapse:    'menu_words_meaningSets_collapse'
      });
      data.isLogical = App.pageState.wordOrderIsLogical();
      if(data.isLogical){
        data.ahref = App.router.linkConfig({
          MeaningGroups: App.meaningGroupCollection});
        data.nhref = App.router.linkConfig({MeaningGroups: []});
        data.meaningGroups = [];
        var isMulti = App.pageState.isMultiView();
        App.meaningGroupCollection.each(function(m){
          //Each meaningGroup should have some words:
          var words = m.getFilteredWords();
          if(words.length === 0) return;
          //Data for the current MeaningGroup:
          var collapsed = !App.meaningGroupCollection.isSelected(m)
            , mg = {
                name: m.getName()
              , fold: collapsed ? 'mgFold' : 'mgUnfold'
              , triangle: collapsed ? 'icon-chevron-up rotate90'
                                    : 'icon-chevron-down'
              };
          //Building the link to toggle the MeaningGroup:
          var toggleGroup = [], mgCol = App.meaningGroupCollection;
          if(collapsed){
            toggleGroup = mgCol.select(m).getSelected();
            mgCol.unselect(m);
          }else{
            toggleGroup = mgCol.unselect(m).getSelected();
            mgCol.select(m);
          }
          mg.link = App.router.linkConfig({MeaningGroups: toggleGroup});
          //Building the checkbox:
          if(isMulti){
            var box = {icon: 'icon-chkbox-custom'};
            switch(App.wordCollection.areSelected(words)){
              case 'all':
                var remaining = words.getDifference(
                  App.filteredWordCollection.getSelected(), words);
                box.icon = 'icon-check';
                box.link = App.router.linkCurrent({words: remaining});
                box.ttip = App.translationStorage.translateStatic(
                  'multimenu_tooltip_minus');
              break;
              case 'some':
                box.icon = 'icon-chkbox-half-custom';
              /* falls through */
              case 'none':
                var additional = words.getUnion(
                  App.filteredWordCollection.getSelected(), words);
                box.link = App.router.linkCurrent({words: additional});
                box.ttip = App.translationStorage.translateStatic(
                  'multimenu_tooltip_plus');
            }
            mg.checkbox = box;
          }
          //Finishing:
          if(!collapsed){
            mg.wordList = this.buildWordList(words);
          }
          data.meaningGroups.push(mg);
          if('wordList' in this.model){
            delete this.model.wordList;
          }
        }, this);
      }else{
        data.wordList = this.buildWordList(App.filteredWordCollection);
        if('meaningGroups' in this.model){
          delete this.model.meaningGroups;
        }
      }
      _.extend(this.model, data);
      return this;
    }
    /**
      This is a helper method for updateWordList.
      It creates the wordList for a given Collection of Words,
      so that they can be embedded in meaningGroups or in the WordMenu directly.
    */
  , buildWordList: function(words){
      var spLang = App.pageState.getSpLang();
      var ws = [], isMulti = App.pageState.isMultiView();
      words.each(function(word){
        var w = {
          wid:   word.getId()
        , trans: word.getNameFor(spLang)
        , ttip:  word.getLongName()
        };
        //Deciding if a word is selected:
        if(isMulti){
          w.selected = App.wordCollection.isSelected(word);
        }else if(App.pageState.isPageView('w')){
          w.selected = App.wordCollection.isChoice(word);
        }else{
          w.selected = false;
        }
        //The checkbox/icon:
        if(isMulti){
          if(w.selected){
            var remaining = App.wordCollection.getDifference(
              App.wordCollection.getSelected(), [word]);
            w.icon = {
              ttip: App.translationStorage.translateStatic(
                'multimenu_tooltip_del')
            , link: App.router.linkCurrent({words: remaining})
            , icon: 'icon-check'
            };
          }else{
            var additional = App.wordCollection.getUnion(
              App.wordCollection.getSelected(), [word]);
            w.icon = {
              ttip: App.translationStorage.translateStatic(
                'multimenu_tooltip_add')
            , link: App.router.linkCurrent({words: additional})
            , icon: 'icon-chkbox-custom'
            };
          }
        }
        //Link for each word:
        w.link = App.pageState.isMapView() ? App.router.linkMapView({word: word})
                                           : App.router.linkWordView({word: word});
        //Phonetics:
        var phonetics = ['*'+word.getProtoName()]
          , phLang = App.pageState.getPhLang();
        if(phLang){
          var tr = word.getTranscription(phLang);
          if(tr) phonetics = tr.getPhonetics();
        }
        //Finish it:
        _.each(phonetics, function(p){
          ws.push(_.extend({}, w, {phonetic: p}));
        }, this);
      }, this);
      return {words: ws};
    }
    /***/
  , render: function(){
      //Updating the WordMenu html representation:
      this.$el.html(App.templateStorage.render('WordMenu', {
        WordMenu: this.model}));
      //Soundfiles and related:
      this.setupMgWordEvents();
      //Reinitializing the WordlistFilter:
      this.wordlistFilter.reinitialize();
      //Setting up callbacks:
      //WordOrder:
      this.$('#sortBy input[data-href]').click(function(){
        App.router.navigate($(this).attr('data-href'));
      });
      //Sp-/Phlang:
      this.$('select').each(function(){
        $(this).change(function(){
          App.router.navigate($(this).find(':selected').data('href'));
        });
      });
      //IPAKeyboard:
      this.$('#IPAOpenKeyboard').click(function(){
        $('#ipaKeyboard').toggleClass('hide').trigger('shown');
      });
    }
    /**
      Redraws only the meaningGroups/WordList part of the WordMenu.
    */
  , renderMgWords: function(){
      var tStore = App.templateStorage;
      if(this.model.isLogical){
        var mgHeadline = tStore.render('MeaningGroupsHeadline', this.model)
          , mgList = tStore.render('MeaningGroupList', this.model);
        this.$('.meaninggroupHeadline').replaceWith(mgHeadline);
        this.$('.meaningGroupList').replaceWith(mgList);
      }else{
        var wordList = tStore.render('WordList', this.model.wordList);
        this.$('.wordList').replaceWith(wordList);
      }
      this.setupMgWordEvents();
    }
    /**
      Readys events for meaningGroups/WordList part of the WordMenu.
      Used by this.render{,MgWords}().
    */
  , setupMgWordEvents: function(){
      //Updating soundfiles:
      App.views.audioLogic.findAudio(this.$el);
      //Checkboxes in MultiViews:
      if(App.pageState.isMultiView() && App.pageState.wordOrderIsLogical()){
        this.$('dl.meaninggroupList > dt > a[data-href]').click(function(){
          App.router.navigate($(this).attr('data-href'));
        });
      }
    }
  });
});
