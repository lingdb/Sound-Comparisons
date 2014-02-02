/**
  A collection of Study names currently known to the site.
*/
Studies = Backbone.Model.extend({
  defaults: {
    names: []
  , selected: null
  }
, initialize: function(){
    this.fetch();
  }
, fetch: function(){
    var t = this;
    return $.get('query/translation.php', {action: 'studies'}, function(data){
      t.set({names: $.parseJSON(data)});
    });
  }
, hasStudies: function(){
    return this.get('names').length > 0;
  }
});
