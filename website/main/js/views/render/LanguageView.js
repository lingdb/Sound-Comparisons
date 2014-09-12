/***/
LanguageView = Backbone.View.extend({
  /***/
  initialize: function(){
    this.model = {};
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
      longName:            language.getLongName()
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
      , link: 'href="#FIXME/implement linking to whoAreWe view with a specific target."'
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
    if(iso = lang.getISO()){
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
    if(href = lang.getWikipediaLink()){
      ls.push({
        ttip:  App.translationStorage.translateStatic('tooltip_languages_link_wikipedia')
      , img:   'http://en.wikipedia.org/favicon.ico'
      , class: 'favicon favicon-bordered'
      , href:  href
      });
    }
    //Maps link:
    if(loc = lang.getLocation()){
      ls.push({
        ttip: App.translationStorage.translateStatic('tooltip_languages_link_mapview')
      , href: 'http://maps.google.com/maps?z=12&q='+loc.join()
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
    if(lst !== null && parseInt(lst.getField) === 6){
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
    //FIXME implement
  }
  /***/
, render: function(){
    console.log('LanguageView.render()');
    if(App.pageState.isPageView(this)){
      this.$el.html(App.templateStorage.render('LanguageTable', this.model));
      this.$el.removeClass('hide');
    }else{
      this.$el.addClass('hide');
    }
  }
});
