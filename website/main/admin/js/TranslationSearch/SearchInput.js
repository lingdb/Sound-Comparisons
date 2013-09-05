SearchInput = Backbone.View.extend({
  initialize: function(){
    this.model.on('reset add remove', this.render, this);
  }
, render: function(){
    var el = this.$('tbody').empty();
    if(this.model.length === 0){
      el.append('<tr class="error"><td colspan="4">Nothing found, sorry.</td></tr>');
    }else{
      this.model.map(function(m){
        var d = m.get('Description');
        var t = m.get('Translation');
        var row = $('<tr>'
        + '<td class="description" data-req="'+d.Req+'">'
        + d.Description
        + '</td>'
        + '<td>'+m.get('Match')+'</td>'
        + '<td>'+m.get('Original')+'</td>'
        + '<td><form class="form-inline" '
          + 'data-searchProvider="'+t.SearchProvider+'" '
          + 'data-payload="'+t.Payload+'" '
          + 'data-translationId="'+t.TranslationId+'">'
          + '<input class="updateInput" type="text" value="'+t.Translation+'">'
          + '<button type="buttin" class="btn saveButton"><i class="icon-hdd"></i>Save</button>'
        + '</form></td></tr>').appendTo(el);
        var sRow = new SearchInputRow({
          model: m, el: row
        });
      });
    }
  }
, events: {'click #SearchTranslationButton': 'search'}
, search: function(e){
    e.preventDefault();
    this.model.search($('#SearchTranslationInput').val());
  }
});

