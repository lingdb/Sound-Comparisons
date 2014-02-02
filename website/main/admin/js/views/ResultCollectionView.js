/**
  model: ResultCollection
, el: Table to display results in
*/
ResultCollectionView = Backbone.View.extend({
  initialize: function(){
    this.model.on('reset add remove', this.render, this);
  }
, render: function(){
    if(this.model.length === 0){
      this.$el.hide();
    }else{
      this.$el.show();
      var el = this.$('tbody').empty();
      //TODO groupSize could be used for spacing rows, if we'd like to.
      var groupSize = this.model.getGroupSize();
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
        var rRow = new ResultRowView({
          model: m, el: row
        });
      });
    }
  }
});
