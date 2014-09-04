/***/
LanguageCollection = Backbone.Collection.extend({
  model: Language
, initialize: function(){
    //The LanguageCollection tracks selected languages.
    this.selected = {}; // LanguageIx -> Language
    //Defaulting to all as selected:
    this.on('reset', function(){
      this.selected = {};
      this.each(function(l){
        this.selected[l.get('LanguageIx')] = l;
      }, this);
    }, this);
  }
  /**
    The update method is connected by the App,
    to listen on change:study of the window.App.dataStorage.
  */
, update: function(){
    var ds   = window.App.dataStorage
      , data = ds.get('study');
    if(data && 'languages' in data){
      console.log('LanguageCollection.update()');
      this.reset(data.languages);
    }
  }
  /**
    Runs iterator[ and context] over all selected languages.
    Returns self for chaining.
  */
, forSelected: function(iterator, context){
    _.each(this.selected, function(v, k){
      iterator.call(context, v, k);
    }, this);
    return this;
  }
  /**
    Predicate to tell if a language is selected.
  */
, isSelected: function(l){
    return l.get('LanguageIx') in this.selected;
  }
  /**
    Method to tell if multiple languages are selected.
    Returns {'all','some','none'}
  */
, areSelected: function(ls){
    var all = true, none = true;
    _.each(ls, function(l){
      if(this.isSelected(l)){
        none = false;
      }else{
        all = false;
      }
    }, this);
    if(all) return 'all';
    if(none) return 'none';
    return 'some';
  }
  /**
    Adds the given language to the selected ones.
    Returns self for chaining.
  */
, select: function(l){
    this.selected[l.get('LanguageIx')] = l;
    return this;
  }
  /**
    Removes the given language from the selected ones.
    Returns self for chaining.
  */
, unselect: function(l){
    delete this.selected[l.get('LanguageIx')];
    return this;
  }
});
