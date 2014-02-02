Search = Backbone.Collection.extend({
  model:  Match
, search: function(searchText){
    if(searchText === ''){
      this.reset();
      return;
    }
    var query = {
      TranslationId: window.Translation.currentTranslation.get('TranslationId') 
    , SearchText:    searchText
    , action:        'search'
    };
    if(!query.TranslationId){
      alert('Please select a Translation first.');
      return;
    }
    var s = this;
    $.get(window.Translation.url, query).done(function(ds){
      ds = $.parseJSON(ds);
      var ms = _.map(ds, function(d){return new Match(d);});
      s.reset(ms);
    });
  }
});
