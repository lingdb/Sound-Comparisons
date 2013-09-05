Match = Backbone.Model.extend({
  update: function(u){
    var m = this;
    var t = this.get('Translation');
    t.Update = u;
    t.action = 'update';
    return $.get('query/search.php', t).done(function(){
      m.set({Update: u});
    });
  }
});
