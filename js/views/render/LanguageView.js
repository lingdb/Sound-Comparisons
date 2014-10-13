"use strict";
/***/
var LanguageView = Renderer.prototype.SubView.extend({
  /***/
  initialize: function(){
    this.model = {};
    //Connecting to the router
    App.router.on('route:languageView', this.route, this);
  }
  /**
    Method to make it possible to check what kind of PageView this Backbone.View is.
  */
, getKey: function(){return 'language';}
  /**
    Overwrites the current model with the given one performing a deep merge.
  */
, setModel: function(m){
    this.model = $.extend(true, this.model, m);
  }
  /***/
, updateLanguageHeadline: function(){
    var language = App.languageCollection.getChoice();
    if(!language){
      console.log('LanguageView.updateLanguageHeadline() without a language.');
      return;
    }
    //The basic headline:
    var headline = {
      longName:            language.getSuperscript(language.getLongName())
    , LanguageLinks:       this.buildLinks(language)
    , LanguageDescription: this.buildDescription(language)
    , playAll: App.translationStorage.translateStatic('language_playAll')
    };
    //Neighbours:
    _.each(['tabulator_language_prev','tabulator_language_next'], function(v, k){
      var l = (k === 0) ? language.getPrev() : language.getNext()
        , k = (k === 0) ? 'prev' : 'next';
      headline[k] = {
        title: App.translationStorage.translateStatic(v)
      , link:  'href="'+App.router.linkLanguageView({language: l})+'"'
      , trans: l.getShortName()
      };
    }, this);
    //Contributors:
    headline.contributors = _.map(language.getContributors(), function(c, col){
      return {
        cdesc: c.getColumnDescription(col)
      , link: 'href="#/whoAreWe/'+c.getInitials()+'"'
      , name: c.getName()
      , info: c.getYearPages()
      };
    }, this);
    headline.contributorTooltip = App.translationStorage.translateStatic('tooltip_contributor_list');
    headline.hasContributors    = headline.contributors.length > 0;
    //Done:
    this.setModel({languageHeadline: headline});
  }
  /**
    Helper method for updateLanguageHeadline; generates LanguageLinks.
  */
, buildLinks: function(lang){
    var ls = [];
    //Various links:
    var iso = lang.getISO();
    if(iso){
      ls.push(
        { href: 'http://www.ethnologue.com/show_language.asp?code='+iso
        , img:  'http://www.ethnologue.com/favicon.ico'
        , ttip: App.translationStorage.translateStatic('tooltip_languages_link_ethnologue')}
      , { href: 'http://www.glottolog.org/resource/languoid/iso/'+iso
        , img:  'img/extern/glottolog.png'
        , ttip: App.translationStorage.translateStatic('tooltip_languages_link_glottolog')}
      , { href: 'http://multitree.org/codes/'+iso+'.html'
        , img:  'http://multitree.org/images/favicon.ico'
        , ttip: App.translationStorage.translateStatic('tooltip_languages_link_multitree')}
      , { href:  'http://www.llmap.org/maps/by-code/'+iso+'.html'
        , style: 'width: 36px;'
        , img:   'img/extern/llmap.png'
        , ttip:  App.translationStorage.translateStatic('tooltip_languages_link_llmap')}
      );
    }
    //Wikipedia link:
    var href = lang.getWikipediaLink();
    if(href){
      ls.push({
        ttip:  App.translationStorage.translateStatic('tooltip_languages_link_wikipedia')
      , img:   'http://en.wikipedia.org/favicon.ico'
      , class: 'favicon favicon-bordered'
      , href:  href
      });
    }
    //Maps link:
    var loc = lang.getLocation();
    if(loc){
      var ll = loc.join();
      ls.push({
        ttip: App.translationStorage.translateStatic('tooltip_languages_link_mapview')
      , href: "https://www.google.com/maps/place/"+ll+"/@"+ll+",7z/"
      , img:  'img/langmap.png'
      });
    }
    return {links: ls};
  }
  /***/
, buildDescription: function(lang){
    var lst   = lang.getLanguageStatusType()
      , desc  = lang.getDescriptionData()
      , lines = [];
    //Composing description lines:
    if('Tooltip' in desc){
      lines.push({desc: desc.Tooltip});
    }
    //Historical period:
    if('HistoricalPeriod' in desc){
      var line = {
        link: 'http://en.wikipedia.org/wiki/'+desc.HistoricalPeriodWikipediaString
      , img:  'http://en.wikipedia.org/favicon.ico'
      };
      if(lst !== null && parseInt(lst.getField()) === 1){
        line.desc = lst.getDescription()+' '+desc.HistoricalPeriod;
        lst = null;
      }else{
        line.desc = App.translationStorage.translateStatic('language_description_historical')
                  + ': ' + desc.HistoricalPeriod;
      }
      lines.push(line);
    }
    //Ethnic group:
    if(lst !== null && parseInt(lst.getField()) === 6){
      if('EthnicGroup' in desc){
        lines.push({desc: lst.getDescription()+' '+desc.EthnicGroup});
      }
      lst = null;
    }
    //Region:
    if('StateRegion' in desc){
      var line = {
        desc: (lst !== null) ? lst.getDescription()
            : App.translationStorage.translateStatic('language_description_region')
      };
      line.desc += ('NearestCity' in desc)
                 ? desc.NearestCity + ' (' + desc.StateRegion + ')'
                 : desc.StateRegion;
      lines.push(line);
      lst = null;
    }
    //Consume lst iff still available:
    if(lst !== null){
      lines.push({
        desc: [lst.getDescription(), lang.getLongName()].join(' ')
      });
    }
    //Locality:
    if('PreciseLocality' in desc){
      var spelling = ('PreciseLocalityNationalSpelling' in desc)
                   ? ' (='+desc.PreciseLocalityNationalSpelling+')' : '';
      lines.push({
        desc: App.translationStorage.translateStatic('language_description_preciselocality')
            + ': '+desc.PreciseLocality+spelling
      });
    }
    //External Weblink:
    if('ExternalWeblink' in desc){
      lines.push({
        desc: App.translationStorage.translateStatic('language_description_externalweblink')+': '
      , link: desc.ExternalWeblink
      , text: desc.ExternalWeblink
      });
    }
    //WebsiteSubgroup:
    if('WebsiteSubgroupName' in desc){
      lines.push({
        desc: App.translationStorage.translateStatic('language_description_subgroup')+': '
      , link: ('WebsiteSubgroupWikipediaString' in desc)
            ? 'http://en.wikipedia.org/wiki/' + desc.WebsiteSubgroupWikipediaString : null
      , img:  'http://en.wikipedia.org/favicon.ico'
      , afterLink: desc.WebsiteSubgroupName
      });
    }
    //Done:
    return {rows: lines};
  }
  /***/
, updateLanguageTable: function(){
    var language = App.languageCollection.getChoice();
    if(!language){
      console.log('LanguageView.updateLanguageTable() without a language.');
      return;
    }
    //Gathering transcriptions:
    var transcriptions = [];
    App.wordCollection.each(function(word){
      var tr = App.transcriptionMap.getTranscription(language, word);
      if(!tr) return;
      transcriptions.push({
        link:     'href="'+App.router.linkWordView({word: word})+'"'
      , ttip:     word.getLongName()
      , trans:    word.getNameFor(language)
      , spelling: tr.getAltSpelling()
      , phonetic: tr.getPhonetics()
      });
    }, this);
    //Finish:
    this.setModel({rows: _.chain(transcriptions).groupBy(function(x, i){
      //Creating rows as chunks of 6 transcriptions:
      return Math.floor(i/6);
    }).values().map(function(ts){
      //Adding a key to each chunk:
      return {transcriptions: ts};
    }).value()});
  }
  /***/
, render: function(){
    if(App.pageState.isPageView(this)){
      this.$el.html(App.templateStorage.render('LanguageTable', {LanguageTable: this.model}));
      this.$el.removeClass('hide');
      //Updating sound related stuff:
      App.views.audioLogic.findAudio(this.$el);
      App.views.playSequenceView.update(this.getKey());
      //Calling redraw:
      this.redraw();
    }else{
      this.$el.addClass('hide');
    }
  }
  /***/
, route: function(study, language){
    console.log('LanguageView.route('+study+', '+language+')');
    var pv = this.getKey();
    //Setting the study:
    App.study.setStudy(study).always(function(){
      //Setting the word:
      App.languageCollection.setChoiceByKey(language);
      //Set this pageView as active:
      App.pageState.setPageView(pv);
      //Render:
      App.views.renderer.render();
    });
  }
  /**
    Organizes the table items in a more clever way .)
    This method is to be called on these occasions:
    1: When LanguageView.render is done.
    2: When WordlistFilter filters some elements.
    3: SetupBarView.finish removed the setupBar.
    This is done in the following fashion:
    0: If we're not in single language view, abort
    1: Get width of the table
    2: Get maximum width of a visible cell
    3: Calculate number of cells per row
    4: Reorganize cells according to maximum number of cells per row
  */
, redraw: function(){
    if(!App.pageState.isPageView(this))
      return;//Step 0
    //Step 1:
    var tWidth = this.$el.width();
    //Step 2:
    var elWidth = 80, cells = [];
    this.$('#languageTable td.transcription').each(function(){
      //Building array of cells:
      var cell = $(this);
      cells.push(cell);
      //Finding max. width:
      var w = _.chain(cell.children()).map(function(e){return $(e).width();}).max().value();
      if(w > elWidth){
        elWidth = w;
      }
    });
    //Step 3:
    var cMax = Math.max(Math.floor(tWidth / elWidth), 1);
    //Step 4:
    var oldTr = this.$('#languageTable tr');
    while(cells.length > 0){
      var tr = $('<tr></tr>').appendTo(this.$('#languageTable'));
      var i = 0; // Outside of loop on purpose
      for(;i < cMax;){
        var c = cells.shift();
        if(!c) break;
        if(c.is(':visible')) i++;
        c.appendTo(tr);
      }
      var missing = cMax - i;
      if(missing > 0){
        tr.append('<td colspan="'+missing+'"></td>');
      }
    }
    oldTr.remove();
  }
});
