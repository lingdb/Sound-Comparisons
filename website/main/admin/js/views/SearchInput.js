/**
  model: ResultCollection
  el: Search Input Area
*/
SearchInput = InputView.extend({
  events: {'click #SearchTranslationButton': 'search'}
, search: function(e){
    e.preventDefault();
    var text = this.$('#SearchTranslationInput').val();
    if(text === ''){
      this.model.reset();
    }else{
      var query = {
        TranslationId: window.Translation.currentTranslation.get('TranslationId') 
      , SearchText:    text
      , action:        'search'
      , searchAll:     this.$('#SearchTranslationCheckAll').is(':checked')
      };
      if(!query.TranslationId){
        alert('Please make sure a Translation is selected.');
      }else{
        var s = this;
        $.get(window.Translation.url, query).done(function(ds){
          ds = $.parseJSON(ds);
          var rs = _.map(ds, function(d){return new Result(d)});
          s.model.reset(rs);
        }).fail(function(err){
          alert('Search failed with: ' + err);
        });
      }
    }
  }
});
