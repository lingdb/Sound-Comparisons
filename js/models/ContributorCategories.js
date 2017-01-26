"use strict";
/* global App */
define(['underscore','backbone','collections/ContributorCollection'], function(_, Backbone, ContributorCollection){
  /***/
  return Backbone.Model.extend({
    initialize: function(){
      this.sortGroups = [];
      this.sgCMap = {};// sortGroup -> ContributorCollection, filled lazily
      this.sgHMap = {};// sortGroup -> Headline
    }
  , getHeadline: function(sortGroup){
      var fallback = this.sgHMap[sortGroup];
      return App.translationStorage.translateDynamic('ContributorCategoriesTranslationProvider', sortGroup, fallback);
    }
  , getContributors: function(sortGroup){
      if(!(sortGroup in this.sgCMap)){
        var cs = [];
        App.contributorCollection.each(function(c){
          if(c.getSortGroup() === sortGroup){
            cs.push(c);
          }
        }, this);
        this.sgCMap[sortGroup] = new ContributorCollection(cs);
      }
      return this.sgCMap[sortGroup];
    }
  , getSortGroups: function(){return this.sortGroups;}
    /**
      The update method is connected by the App,
      to listen on change:global of the App.dataStorage.
    */
  , update: function(){
      var data = App.dataStorage.get('global').global;
      if(data && 'contributorCategories' in data){
        //Resetting some stuff:
        this.sortGroups = [];
        this.sgCMap = {};
        //Iterating categories:
        _.each(data.contributorCategories, function(cat){
          this.sortGroups.push(cat.SortGroup);
          this.sgHMap[cat.SortGroup] = cat.Headline;
        }, this);
      }
    }
  , each: function(λ, context){
      _.each(this.getSortGroups(), function(sg){
        λ.call(context, this.getHeadline(sg), this.getContributors(sg));
      }, this);
    }
  });
});
