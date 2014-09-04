/***/
Selection = Backbone.Collection.extend({
  initialize: function(){
    this.selected = {}; // model.getId() -> model
    //Defaulting to all as selected:
    this.on('reset', function(){
      this.selected = {};
      this.each(function(m){
        this.selected[m.getId()] = m;
      }, this);
    }, this);
  }
  /**
    Runs the given iterator[ and context] over all currently selected models.
    Returns self for chaining.
  */
, forSelected: function(iterator, context){
    _.each(this.selected, function(v, k){
      iterator.call(context, v, k);
    }, this);
    return this;
  }
  /**
    Predicate to check selection of a model.
  */
, isSelected: function(m){
    return m.getId() in this.selected;
  }
  /**
    Adds a model to the selection.
    Returns self for chaining.
  */
, select: function(m){
    this.selected[m.getId()] = m;
    return this;
  }
  /**
    Removes a model from the selection.
    Returns self for chaining.
  */
, unselect: function(m){
    delete this.selected[m.getId()];
    return this;
  }
});
