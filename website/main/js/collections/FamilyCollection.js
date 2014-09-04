/***/
FamilyCollection = Backbone.Collection.extend({
  model: Family
  /***/
, initialize: function(){
    //The FamilyCollection tracks selected families.
    this.selected = {}; // FamilyId -> Family
    //Defaulting to all as selected:
    this.on('reset', function(){
      this.selected = {};
      this.each(function(f){
        this.selected[f.getId()] = f;
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
    if(data && 'families' in data){
      console.log('FamilyCollection.update()');
      this.reset(data.families);
    }
  }
  /**
    Runs the given iterator[ and context] over all currently selected families.
    Returns self for chaining.
  */
, forSelected: function(iterator, context){
    _.each(this.selected, function(v, k){
      iterator.call(context, v, k);
    }, this);
    return this;
  }
  /**
    Predicate to check selection of a family.
  */
, isSelected: function(family){
    return family.getId() in this.selected;
  }
  /**
    Adds a family to the selection.
    Returns self for chaining.
  */
, select: function(family){
    this.selected[family.getId()] = family;
    return this;
  }
  /**
    Removes a family from the selection.
    Returns self for chaining.
  */
, unselect: function(family){
    delete this.selected[family.getId()];
    return this;
  }
});
