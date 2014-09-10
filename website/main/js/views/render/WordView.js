/***/
WordView = Backbone.View.extend({
  /**
    Method to make it possible to check what kind of PageView this Backbone.View is.
  */
  getKey: function(){return 'word';}
  /**
    Function to activate update methods, and run them the first time.
    This will be called by the Renderer.
  */
, activate: function(){}
  /**
    Overwrites the current model with the given one performing a deep merge.
  */
, setModel: function(m){
    this.model = $.extend(true, this.model, m);
  }
  /***/
, updateWordHeadline: function(){
    var word     = App.wordCollection.getChoice()
      , spLang   = App.pageState.getSpLang()
      , headline = {name: word.getLongName() || word.getNameFor(spLang)};
    //Sanitize name:
    if(_.isArray(headline.name))
      headline.name = headline.name.join(', ');
    //MapsLink:
    if(!App.pageState.isPageView('map')){
      headline.mapsLink = {
        link: 'href="'+App.router.linkMapView({word: word})+'"'
      , ttip: App.translationStorage.translateStatic('tooltip_words_link_mapview')
      };
    }
    //Neighbours:
    var withN = function(w){
      return {
        link:  App.router.linkCurrent({word: w})
      , ttip:  w.getLongName()
      , trans: w.getNameFor(spLang)
      };
    };
    //Previous Word:
    headline.prev = $.extend(withN(word.getPrev())
    , {title: App.translationStorage.translateStatic('tabulator_word_prev')});
    //Next Word:
    headline.next = $.extend(withN(word.getNext())
    , {title: App.translationStorage.translateStatic('tabulator_word_next')});
    //Done:
    this.setModel({wordHeadline: headline});
  }
  /***/
, render: function(){
    console.log('WordView.render()');
    //FIXME implement
  }
});
