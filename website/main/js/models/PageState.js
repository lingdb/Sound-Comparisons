/**
  The PageState has a variety of tasks that lie at the core of our Application.
  - It tracks state for the site, where the different parts should not do so themselfs.
  - It aids construcing links for the site.
  - It assists in parsing links for the site.
*/
PageState = Backbone.Model.extend({
  defaults: {
    wordOrder: 'alphabetical'
  }
//Managing the wordOrder:
  /**
    Predicate to test if the wordOrder is logical
  */
, wordOrderIsLogical: function(){
    return this.get('wordOrder') === 'logical';
  }
  /**
    Predicate to test if the wordOrder is alphabetical
  */
, wordOrderIsAlphabetical: function(){
    return this.get('wordOrder') === 'alphabetical';
  }
  /**
    Sets the wordOrder to logical
  */
, wordOrderSetLogical: function(){
    this.set({wordOrder: 'logical'});
  }
  /**
    Sets the wordOrder to alphabetical
  */
, wordOrderSetAlphabetical: function(){
    this.set({wordOrder: 'alphabetical'});
  }
});
