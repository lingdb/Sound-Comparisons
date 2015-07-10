"use strict";
define(['backbone','collections/Selection'], function(Backbone, Selection){
  /**
    Implements the aspect that a single model of a collection may be marked as chosen.
    If children implement 'getDefaultChoice', it will be used instead of null as default choice.
  */
  return Selection.extend({
    initialize: function(){
      this.choice = null;
      //Defaulting to null as choice:
      this.on('reset', function(){
        if('getDefaultChoice' in this){
          this.choice = this.getDefaultChoice();
        }else{
          this.choice = null;
        }
      }, this);
      //Calling superconstructor:
      Selection.prototype.initialize.apply(this, arguments);
    }
    /***/
  , getChoice: function(){return this.choice || null;}
    /***/
  , isChoice: function(m){
      var current = this.getChoice();
      if(current === null) return false;
      if(m instanceof Backbone.Model){
        return current.getId() === m.getId();
      }
      if(_.isNumber(m) || _.isString(m)){
        return m === current.getId();
      }
      return false;
    }
    /***/
  , setChoice: function(m){
      if(m instanceof Backbone.Model){
        this.choice = m;
      }else if(_.isNumber(m) || _.isString(m)){
        this.choice = this.find(function(ms){
          return ms.getId() === m;
        }, this) || null;
      }
      return this.choice;
    }
    /***/
  , setChoiceByKey: function(k){
      var m = null;
      if(_.isString(k)){
        m = this.find(function(x){return x.getKey() === k;}, this) || null;
      }
      if(m === null){
        if('getDefaultChoice' in this){
          m = this.getDefaultChoice();
        }else{
          m = this.models[0];
        }
      }
      return this.setChoice(m);
    }
  });
});
