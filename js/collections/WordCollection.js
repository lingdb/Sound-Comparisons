"use strict";
define(['collections/Choice','models/Word','collections/Selection','backbone'], function(Choice, Word, Selection, Backbone){
  /***/
  return Choice.extend({
    model: Word
    /**
      The update method is connected by the App,
      to listen on change:study of the window.App.dataStorage.
    */
  , update: function(){
      var ds   = window.App.dataStorage
        , data = ds.get('study');
      if(data && 'words' in data){
        console.log('WordCollection.update()');
        this.reset(data.words);
      }
    }
    /**
      Placeholder, to mark that this is overwritten by WordCollection.sort()
    */
  , comparator: function(a, b){
      throw "Unexpected call to WordCollection.comparator()";
    }
    /**
      Produces a function that compares two words for their alphabetical order and returns their ordering.
      @return (Word,Word)->{-1,0,1}
    */
  , getAlphaComparator: function(){
      var spLang = App.pageState.getSpLang()
        , toName = function(w){
            var s = w.getNameFor(spLang);
            if(_.isArray(s)){ s = _.head(s); }
            s = s.toLowerCase();
            //Test for magic pattern (#140):
            var matches = s.match(/\((.+)\) ?(.+)/);
            if(_.isArray(matches)){
              return [matches[2], matches[1]];
            }
            return [s];
          };
      return function(a, b){
        var na = toName(a), nb = toName(b), iMax = Math.min(na.length, nb.length);
        //Compare fieldwise:
        for(var i = 0; i < iMax; i++){
          if(na > nb) return  1;
          if(na < nb) return -1;
        }
        //Fallback on length:
        if(na.length > nb.length) return  1;
        if(na.length < nb.length) return -1;
        //When in doubt, words are equal:
        return 0;
      };
    }
    /**
      Produces a function that compares two words for their logical order and returns their ordering.
      @return (Word,Word)->{-1,0,1}
    */
  , getLogicComparator: function(){
      var aComp = this.getAlphaComparator();
      return function(a,b){
        var ma = a.getMeaningGroup(), mb = b.getMeaningGroup();
        if(ma && mb){
          var maId = ma.getId(), mbId = mb.getId();
          if(maId > mbId) return  1;
          if(maId < mbId) return -1;
          var aId = a.getMgId(), bId = b.getMgId();
          if(aId > bId) return  1;
          if(aId < bId) return -1;
        }
        //When in doubt, use alphabetical order:
        return aComp(a,b);
      };
    }
    /***/
  , sort: function(){
      var isAlpha = App.pageState.wordOrderIsAlphabetical();
      this.comparator = isAlpha ? this.getAlphaComparator()
                                : this.getLogicComparator();
      Backbone.Collection.prototype.sort.apply(this, arguments);
    }
    /**
      Called by App, to make sure wordCollection is sorted by the current WordOrder.
    */
  , listenWordOrder: function(){
      App.pageState.on('change:wordOrder', this.sort, this);
      App.pageState.on('change:spLang', this.sort, this);
    }
    /**
      Returns the default Words as array to be used as selection for the WordCollection.
    */
  , getDefaultSelection: function(){
      var sel = App.defaults.getWords();
      if(sel.length === 0){
        return _.take(this.models, 5);
      }
      return sel;
    }
    /**
      Returns the default Word to be used as Choice for the WordCollection.
    */
  , getDefaultChoice: function(){
      return App.defaults.getWord();
    }
    /**
      Overwriting Selection:getSelected to sort elements according to WordCollection:comparator.
    */
  , getSelected: function(pvk){
      var selected = Selection.prototype.getSelected.call(this, pvk);
      selected.sort(this.comparator);
      return selected;
    }
  });
});
