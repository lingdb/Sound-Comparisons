/**
  model: ResultCollection
, el: Table to display results in
*/
ResultCollectionView = Backbone.View.extend({
  events: {
    'click .ResultSaveAll': 'saveAll'
  }
, initialize: function(){
    this.model.on('reset add remove', this.render, this);
  }
, render: function(){
    if(this.model.length === 0){
      this.$el.hide();
    }else{
      this.$el.show();
      var el = this.$('tbody').empty();
      this.$('tr > *:nth-child(2)').show();
      var hideCol2 = true;
      //TODO groupSize could be used for spacing rows, if we'd like to.
      var groupSize = this.model.getGroupSize();
      this.model.map(function(m){
        //Setup:
        var d        = m.get('Description')
          , t        = m.get('Translation')
          , flag     = m.getFlag()
          , match    = m.get('Match')    || ''
          , original = m.get('Original') || '';
        console.log('match: '+ JSON.stringify(match));
        //Cosmetics:
        if(t.Translation === null) t.Translation = '';
        if(match !== '') hideCol2 = false;
        //Building the row:
        var row = $('<tr>'
                + '<td class="description" data-req="'+d.Req+'">'
                + d.Description
                + '</td>'
                + '<td>'+match+'</td>'
                + '<td>'+original+'<a class="btn pull-right copy-over"><i class="icon-arrow-right"></i></a></td>'
                + '<td><form class="form-inline" '
                  + 'data-searchProvider="'+t.SearchProvider+'" '
                  + 'data-payload="'+t.Payload+'" '
                  + 'data-translationId="'+t.TranslationId+'">'
                  + '<img src="../'+window.Translation.currentTranslation.get('ImagePath')+'">'
                  + '<input class="updateInput" type="text" value="'+t.Translation+'">'
                  + '<button type="buttin" class="btn saveButton"><i class="icon-hdd"></i>Save</button>'
                + '</form></td></tr>').appendTo(el);
        var rRow = new ResultRowView({
          model: m, el: row
        });
      });
      if(hideCol2)
        this.$('tr > *:nth-child(2)').hide();
    }
  }
, saveAll: function(){
    var toSave = this.$('.btn-warning');
    if(toSave.length === 0){
      alert('Nothing changed -> nothing to save .)');
    }else{
      toSave.trigger('click');
    }
  }
});
