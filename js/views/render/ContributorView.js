/* global ContributorView: true */
"use strict";
/***/
var ContributorView = Renderer.prototype.SubView.extend({
  /***/
  initialize: function(){
    this.model = {};
    //Connecting to the router
    App.router.on('route:contributorView', this.route, this);
    //Scroll target: 
    this._scrollTo = null;
  }
  /**
    Method to make it possible to check what kind of PageView this Backbone.View is.
  */
, getKey: function(){return 'contributorView';}
  /***/
, updateContributors: function(){
    var sumContributor = function(c){
      return {
        initials: c.getInitials()
      , name:     c.getName()
      , img:      c.getAvatar()
      , email:    c.getEmail()
      , website:  c.getPersonalWebsite()
      , role:     c.getFullRoleDescription()
      };
    }, cats = [];
    App.contributorCategories.each(function(h,cs){
      if(cs.length === 0) return;
      cats.push({headline: h, contributors: cs.map(sumContributor)});
    }, this);
    this.model.categories = cats;
  }
  /***/
, render: function(){
    if(App.pageState.isPageView(this)){
      this.$el.html(App.templateStorage.render('contributors', this.model));
      this.$el.removeClass('hide');
      //We set the hash again, to make sure the browser scrolls to our target:
      if(this._scrollTo){
        var h = window.location.hash;
        window.location.hash = '#';
        window.location.hash = h;
      }
    }else{
      this.$el.addClass('hide');
    }
  }
  /***/
, route: function(initials){
    console.log('ContributorView.route('+initials+')');
    this._scrollTo = initials || null;
    App.pageState.setPageView(this);
    App.views.renderer.render();
  }
});
