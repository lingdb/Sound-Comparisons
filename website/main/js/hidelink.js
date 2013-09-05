/**
  Adds the functionality to hide sidemenues.
*/
function hidelink(){
  var toggleChevron = function(t){
    if(t.hasClass('icon-chevron-right')){
      t.addClass('icon-chevron-left').removeClass('icon-chevron-right');
    }else if(t.hasClass('icon-chevron-left')){
      t.addClass('icon-chevron-right').removeClass('icon-chevron-left');
    }
  }
  var getSpan = function(t){
    for(var i = 1; i <= 12; i++)
      if(t.hasClass('span'+i))
        return i;
    return 0;
  }
  var deltaSpan = function(t, d){
    var s = getSpan(t);
    t.removeClass('span'+s).addClass('span'+(s+d));
  }
  var content = $('#contentArea');
  $('a.hidelink').click(function(){
    var t = $(this);
    toggleChevron(t.find('i'));
    var target = $(t.attr('data-target'));
    var d = getSpan(target);
    if(target.is(':visible')){
      target.hide();
      deltaSpan(content, d);
    }else{
      target.show();
      deltaSpan(content, d * -1);
    }
  });
}
