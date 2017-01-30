$(document).ready(function(){
  /* Create an array with the values of all the input boxes in a column */
  $.fn.dataTable.ext.order['dom-text'] = function(settings, col){
    return this.api().column(col, {order:'index'}).nodes().map(function(td, i){
      return $('input', td).val();
    });
  };
  /* Create an array with the values of all the select options in a column */
  $.fn.dataTable.ext.order['dom-select'] = function(settings, col){
    return this.api().column(col, {order:'index'}).nodes().map(function( td, i){
      return $('select', td).val();
    });
  };
  /* Create an array with the values of all the checkboxes in a column */
  $.fn.dataTable.ext.order['dom-checkbox'] = function(settings, col){
    return this.api().column(col, {order:'index'}).nodes().map(function(td, i){
      return $('input', td).prop('checked') ? 1 : 0;
    });
  };
  /* Marking changes in a row: */
  $.fn.extend({changeInRow: function(){
    this.closest('tr').find('.btn.save').addClass('btn-warning').removeClass('btn-success');
    return this;
  }});
});
