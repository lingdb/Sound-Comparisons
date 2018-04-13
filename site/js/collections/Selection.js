"use strict";
/* global App */
/* eslint-disable no-console */
define(['underscore','backbone'], function(_, Backbone){
  /**
    Implements the aspect that several models of a collection can be marked as selected per PageView.
    If children implement 'getDefaultSelection', it will be used to find the default selection,
    otherwise all models will be marked as selected per default.
    The getDefaultSelection method may present two kinds of return values:
    - Either an Object mapping PageViewKeys to arrays of models,
    - Or an array of models that will be used for all PageViewKeys.
  */
  return Backbone.Collection.extend({
    initialize: function(){
      this.selected = this.mkSelectionMap();
      //Defaulting selected models:
      this.on('reset', function(){
        var ms = ('getDefaultSelection' in this) ? this.getDefaultSelection()
                                                 : this.models;
        this.selected = this.mkSelectionMap();
        if(_.isArray(ms)){//Default selection is an array
          _.each(App.pageState.get('pageViews'), function(pvk){
            this.setSelected(ms, pvk);
          }, this);
        }else if(_.isObject(ms)){//Default selection maps PageViewKeys to arrays of models
          _.each(ms, function(ms, pvk){
            this.setSelected(ms, pvk);
          }, this);
        }
      }, this);
    }
    /**
      Generates an empty selection map.
      It maps pageViewKey -> model.getId() -> model
    */
  , mkSelectionMap: function(){
      var selected = {};
      _.each(App.pageState.get('pageViews'), function(pvk){
        selected[pvk] = {};
      });
      return selected;
    }
    /**
      @param [pvk] String
      @return selected [Backbone.Model]
      Returns the selected models as an array,
      because the final collection for them is not known.
      If pvk is given, it is used as the pageView to retrieve the selection for.
      Otherwise the current pageView will be used.
    */
  , getSelected: function(pvk){
      pvk = pvk || App.pageState.getPageViewKey();
      return _.values(this.selected[pvk]);
    }
    /**
      @param ms [Backbone.Model] || Backbone.Collection
      @param [pvk String PageViewKey]
      @return self for chaining
      Changes the selected models to the given array or Backbone.Collection.
      The pvk parameter may be omitted.
    */
  , setSelected: function(ms, pvk){
      pvk = pvk || App.pageState.getPageViewKey();
      if(ms instanceof Backbone.Collection){
        ms = ms.models;
      }
      this.selected[pvk] = {};
      if(_.isArray(ms)){
        _.each(ms, function(m){this.select(m, pvk);}, this);
      }
      return this;
    }
    /**
      @param ks [String]
      Selects models by their getKey method.
      Must allow '{Lgs,Wds}_{All,Sln,None}' for #188.
    */
  , setSelectedByKey: function(ks, pvk){
      pvk = pvk || App.pageState.getPageViewKey();
      var keys = {} // Hash map for faster finding of keys
        , special = null;// null || {All,Sln,None}
      //Adds k to keys if it's not a special case.
      _.each(ks, function(k){
        var matches = k.match(/_(All|Sln|None)/);
        if(matches){
          special = matches[1];
        }else{
          keys[k] = true;
        }
      }, this);
      //Special case found, processing:
      if(special !== null){
        switch(special){
          case 'All'://Selects all models:
            return this.setSelected(this.models, pvk);
          case 'Sln'://Doesn't modify selection:
            return this;
          case 'None'://Selects no models:
            return this.setSelected([], pvk);
          default://Like Sln, but with debug output:
            console.log('Unexpected selection shortcut: '+special);
            return this;
        }
      }else{
        //Finding selected:
        var ms = this.filter(function(m){
          return m.getKey() in keys;
        }, this);
        if(ms.length === 0 && App.studyWatcher.studyChanged()){
          if('getDefaultSelection' in this){
            ms = this.getDefaultSelection(pvk);
          }else{
            ms = _.take(this.models, 5);
          }
        }
        //Adding models that have a matching key:
        return this.setSelected(ms, pvk);
      }
    }
    /**
      @param m Model to check selection for
      @param [pvk] String pageView to use instead of current pageView.
      @return boolean True if m is selected with respect to the pageView.
    */
  , isSelected: function(m, pvk){
      pvk = pvk || App.pageState.getPageViewKey();
      return m.getId() in (this.selected[pvk]);
    }
    /**
      @param m model to add to the selection
      @param [pvk] String
      @return self for chaining
      If pvk is given, that will be used as the pageView to determine the selection.
    */
  , select: function(m, pvk){
      pvk = pvk || App.pageState.getPageViewKey();
      this.selected[pvk][m.getId()] = m;
      return this;
    }
    /**
      @param m model to add to the selection
      @param [pvk] String
      @return self for chaining
      If pvk is given, that will be used as the pageView to determine the selection.
    */
  , unselect: function(m, pvk){
      pvk = pvk || App.pageState.getPageViewKey();
      delete this.selected[pvk][m.getId()];
      return this;
    }
    /**
      @param models [Model]|| Backbone.Collection<Model>
      @param pvk String to use as pageView instead of current one.
      @return String from the set {'all','some','none'}
      Method to tell if multiple models are selected.
      It works on both, collections and arrays.
    */
  , areSelected: function(models, pvk){
      var all = true, none = true
        , iterator = function(m){
            if(this.isSelected(m, pvk)){
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
      @param xs [key/id]
      @return filtered [model]
      Returns an array of all models where model.getKey() or model.getId()
      is in the given array of keys/ids.
    */
  , filterKeyOrId: function(xs){
      var lookup = {};
      _.each(xs, function(x){lookup[x] = true;});
      return this.filter(function(m){
        if(m.getId() in lookup) return true;
        return m.getKey() in lookup;
      }, this);
    }
  });
});
