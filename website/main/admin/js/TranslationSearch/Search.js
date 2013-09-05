Search = Backbone.Collection.extend({
  model:  Match
, search: function(searchText){
    if(searchText === ''){
      this.reset();
      return;
    }
    var query = {
      TranslationId: window.Translation.translationId
    , SearchText:    searchText
    , action:        'search'
    };
    if(query.TranslationId === null){
      alert('Please select a Translation first.');
      return;
    }
    var s = this;
    $.get('query/search.php', query).done(function(ds){
      ds = $.parseJSON(ds);
      var ms = _.map(ds, function(d){return new Match(d);});
      s.reset(ms);
    });
  }
});
