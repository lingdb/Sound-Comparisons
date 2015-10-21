"use strict";
define(['backbone'], function(Backbone){
  /***/
  return Backbone.Model.extend({
    /**
      Returns the full name of a Contributor.
    */
    getName: function(){
      var fn = this.get('Forenames')
        , sn = this.get('Surnames');
      return fn+' '+sn;
    }
    /***/
  , getInitials: function(){return this.get('Initials');}
    /***/
  , getEmail: function(){
      var x = this.get('EmailUpToAt')
        , y = this.get('EmailAfterAt');
      if(x === '' || y === '' || !x || !y)
        return null;
      return x+' [ at ] '+y;
    }
    /***/
  , getColumnDescription: function(col){
      if(_.isEmpty(col)) return null;
      if(col === 'ContributorCitationAuthor2'){
        return this.getColumnDescription()+' (2)';
      }
      return App.translationStorage.translateStatic('description_contributor_'+col);
    }
    /***/
  , getYearPages: function(){
      var str = _.values(this.pick('Year','Pages')).join(' : ');
      if(str.length > 0) return '('+str+')';
      return str;
    }
    /***/
  , getAvatar: function(){
      return this.get('Avatar') || 'static/img/contributors/dummy.png';
    }
    /***/
  , getPersonalWebsite: function(){
      return this.get('PersonalWebsite');
    }
    /***/
  , getFullRoleDescription: function(){
      var desc = this.get('FullRoleDescription');
      return desc.replace("\n", '<br>');
    }
    /***/
  , getSortGroup: function(){
      return this.get('SortGroup');
    }
  });
});
