"use strict";
/* global App */
/* eslint-disable no-console */
define(['underscore','backbone','models/Contributor'], function(_, Backbone, Contributor){
  /***/
  return Backbone.Collection.extend({
    model: Contributor
    /**
      Custom comparator to make sure contributors are sorted by SortIxForAboutPage
    */
  , comparator: function(a, b){
      //First choice is DESC by SortIx:
      var x = a.get('SortIxForAboutPage'), y = b.get('SortIxForAboutPage');
      if(x > y) return  1;
      if(x < y) return -1;
      //Second choice is ASC by Surnames:
      x = a.get('Surnames'); y = b.get('Surnames');
      if(x > y) return  1;
      if(x < y) return -1;
      return 0;
    }
    /**
      The update method is connected by the App,
      to listen on change:global of the App.dataStorage.
    */
  , update: function(){
      var ds   = App.dataStorage
        , data = ds.get('global').global;
      if(data && 'contributors' in data){
        console.log('ContributorCollection.update()');
        this.reset(data.contributors);
      }
    }
    /***/
  , mainContributors: function(){
      return this.filter(function(c){
        return parseInt(c.get('SortIxForAboutPage')) !== 0;
      }, this);
    }
    /**
      returns the data set for a specific contributor via ContributorIx
    */
  , getContributorById: function(id){
      if(_.isString(id)){id = parseInt(id)}
      var e = this.find(function(e){return parseInt(e.get('ContributorIx')) === id;});
      if(e){return e;}
      console.log('Nothing found for ContributorCollection.getContributorById : '+id);
      return null;
    }
    /***/
  , citeContributors: function(){
      var cs = this.filter(function(c){
        return parseInt(c.get('SortIxForAboutPage')) === 0;
      }, this);
      //Note that these are already sorted by surnames, since their SortIx are the same.
      return cs;
    }
  });
});
