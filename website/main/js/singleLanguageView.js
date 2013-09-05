/**
  Rearranges cells in the languageTable.
  - Initially to fit the screen as good as possible
  - Whenever the visibillity of cells changes
*/
function singleLanguageView(){
  var table = $('#languageTable');
  if(table.length === 0) return;
  var p = new PlaySequence($('#language_playAll'));
  var fillP = function(){
    p.clear();
    $('#languageTable td:visible audio').each(function(){
      p.add(this);
    });
  };
  var cCount = Math.floor(table.width() / 100);
  var draw = function(){
    var otb = table.find('tbody').addClass('old');
    table.append('<tbody class="new"><tr></tr></tbody>');
    var ntb = table.find('tbody.new');
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
    fillP();
  };
  draw();
  table.on('redraw', draw);
}
