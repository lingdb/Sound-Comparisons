/**
  Rearranges cells in the languageTable.
  - Initially to fit the screen as good as possible
  - Whenever the visibillity of cells changes
*/
SingleLanguageView = Backbone.View.extend({
  initialize: function(){
    //Finding the table:
    this.table = $('#languageTable');
    //Aborting iff necessary:
    if(this.table.length === 0){//Abort
      this.table = null;
      this.playSequence = null;
      return;
    }
    //Resetting the playSequence:
    this.playSequence = new PlaySequence($('#language_playAll'));
    //Rendering:
    var view = this;
    this.table.on('redraw', function(){view.render();});
    this.render();
  }
, fillP: function(){
    if(!this.playSequence) return;
    var p = this.playSequence;
    p.clear();
    $('#languageTable td:visible audio').each(function(){
      p.add(this);
    });
  }
, render: function(){
    if(!this.table) return;
    var cCount = Math.floor(this.table.width() / 100)
      , otb    = this.table.find('tbody').addClass('old');
    this.table.append('<tbody class="new"><tr></tr></tbody>');
    var ntb = this.table.find('tbody.new');
    var c = 0;
    otb.find('td').each(function(){
      if(c === cCount){
        ntb.append('<tr></tr>');
        c = 0;
      }
      if($(this).is(':visible')) c++;
      ntb.find('tr').last().append(this);
    });
    otb.remove();
    ntb.removeClass('new');
    this.fillP();
  }
});
