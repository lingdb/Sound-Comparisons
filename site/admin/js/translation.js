"use strict";
$(document).ready(function(){
  /* Initialize DTs: */
  var tables = []; // Collects all DTs
  $('table.display').each(function(){
    var table = $(this).DataTable({
      ordering: false
    , bPaginate: false
    , columns: [
        {searchable: false}
      , {searchable: true}
      , {searchable: true}
      ]
    , iDisplayLength: 1000000
    });
    tables.push(table);
    //The copy-over buttons:
    table.on('click', '.btn.copy-over', function(){
      var btn = $(this).changeInRow()
        , txt = btn.closest('td').find('code').text()
        , tgt = btn.closest('tr').find('input.translation');
      tgt.val(txt);
    });
    //Inputs:
    table.on('change', 'input.translation', function(){$(this).changeInRow();});
    //The save buttons:
    table.on('click', '.btn.save', function(){
      var btn = $(this);
      if(!btn.hasClass('btn-warning')) return;
      btn.removeClass('btn-warning').addClass('btn-danger');
      var td = btn.closest('td'), input = td.find('input.translation')
        , q  = {
        action: 'update'
      , TranslationId: td.data('tid')
      , Payload: td.data('payload')
      , Update: input.val()
      , Provider: td.data('provider')
      };
      $.get('query/translation.php', q, function(){
        btn.removeClass('btn-danger').addClass('btn-success');
      });
    });
    //The keep buttons, if any:
    table.on('click', '.btn.keep', function(){
      $(this).changeInRow();
    });
    //The Save All button:
    table.on('click', '.btn.saveAll', function(){
      table.$('.btn.save.btn-warning').trigger('click');
    });
    //Editing descriptions:
    table.on('dblclick', 'td.description', function(){
      var td = $(this);
      if(td.data('isadmin') != 1) return;
      if(td.find('textarea').length > 0) return;
      td.html('<textarea style="width: 90%; height: 90%; min-height: 15em;">'
        +td.data('title')+'</textarea>');
      var ta = td.find('textarea').blur(function(){
        var q = {
          action: 'updateDescription'
        , Req: td.data('req')
        , Description: ta.val()
        };
        $.get('query/translation.php', q, function(){
          //Replacing all Descriptions locally:
          $(tables).each(function(i, table){
            var newDesc = q.Description.replace(/<[^<>]+>/g, '');
            if(newDesc.length > 42){ newDesc = newDesc.substr(0,42); }
            var nodes = table.column(0, {page: 'all'}).nodes();
            $(nodes).each(function(i, node){
              var n = $(node);
              if(n.data('req') !== q.Req) return;
              n.html(newDesc);
              n.data('title', q.Description);
            });
          });
        });
      });
    });
  });
  $('.description').tooltip()
})
