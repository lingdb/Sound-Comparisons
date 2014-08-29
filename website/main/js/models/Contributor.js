/***/
Contributor = Backbone.Model.extend({
  /**
    Returns the full name of a Contributor.
  */
  getName: function(){
    var fn = this.get('Forenames')
      , sn = this.get('Surnames')
    return fn+' '+sn;
  }
  /***/
, getEmail: function(){
    var x = this.get('EmailUpToAt')
      , y = this.get('EmailAfterAt');
    if(x === '' || y === '' || !x || !y)
      return null;
    return x+' [ at ] '+y;
  }
});
