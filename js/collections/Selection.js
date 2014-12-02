"use strict";
/**
  Implements the aspect that several models of a collection can be marked as selected.
  If children implement 'isDefaultSelection', it will be used to find the default selection,
  rather than selection all models per default.
*/
var Selection = Backbone.Collection.extend({
  initialize: function(){
    this.selected = {}; // model.getId() -> model
    //Defaulting selected models:
    this.on('reset', function(){
      var ms = ('getDefaultSelection' in this)
             ? this.getDefaultSelection()
             : this.models;
      this.selected = {};
      _.each(ms, function(m){
        this.selected[m.getId()] = m;
      }, this);
    }, this);
  }
  /**
    Returns the selected models as an array,
    because the final collection for them is not known.
  */
, getSelected: function(){
    return _.values(this.selected);
  }
  /**
    Changes the selected models to the given array or Backbone.Collection.
  */
, setSelected: function(ms){
    if(ms instanceof Backbone.Collection){
      ms = ms.models;
    }
    if(_.isArray(ms)){
      this.selected = {};
      _.each(ms, this.select, this);
    }
  }
  /***/
, setSelectedByKey: function(ks){
    var keys = {}; // Hash map for faster finding of keys
    _.each(ks, function(k){keys[k] = true;}, this);
    //Finding selected:
    var ms = this.filter(function(m){
      return m.getKey() in keys;
    }, this);
    //Adding models that have a matching key:
    return this.setSelected(ms);
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
  /**
    Method to tell if multiple models are selected.
    It works on both, collections and arrays.
    Returns {'all','some','none'}
  */
, areSelected: function(models){
    var all = true, none = true
      , iterator = function(m){
          if(this.isSelected(m)){
            none = false;
          }else{
            all = false;
          }
        };
    if(_.isArray(models)){
      _.each(models, iterator, this);
    }else{
      models.each(iterator, this);
    }
    if(all) return 'all';
    if(none) return 'none';
    return 'some';
  }
  /**
    Returns {} with a mapping of getId -> model
    for all models of the given bunch, or the collection itself.
    The bunch may be an array or a Backbone.Collection.
  */
, getIdMap: function(bunch){
    var ms = bunch || this;
    if(ms instanceof Backbone.Collection){
      ms = ms.models;
    }
    var map = {};
    _.each(ms, function(m){
      map[m.getId()] = m;
    }, this);
    return map;
  }
  /**
    Returns the difference of a and b with respect to their idMap.
    If !a, it will be replaced with this.
  */
, getDifference: function(a, b){
    var current = this.getIdMap(a||this)
      , remove  = this.getIdMap(b);
    _.each(_.keys(remove), function(k){
      if(k in current){
        delete current[k];
      }
    }, this);
    return _.values(current);
  }
  /**
    Returns the union of a and b with respect to their idMap.
    If !a, it will be replaced with this.
  */
, getUnion: function(a, b){
    var current = this.getIdMap(a||this)
      , add     = this.getIdMap(b);
    _.each(add, function(v, k){
      current[k] = v;
    }, this);
    return _.values(current);
  }
  /**
    Returns an array of all models where model.getKey() is in the given array of keys.
  */
, filterKeys: function(keys){
    var lookup = {};
    _.each(keys, function(k){lookup[k] = true;});
    return this.filter(function(m){
      return m.getKey() in lookup;
    }, this);
  }
});
