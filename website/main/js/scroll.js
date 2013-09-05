function initScroll(){
  //Checks if scrollDown should be displayed
  var chkBottom = function(){
    $('div.scrollDown').each(function(){
      var target  = $(this).prev();
      var tmax    = $.scrollTo.max(target[0]);
      var tcur    = target.scrollTop();
      if(tmax <= tcur){
        $(this).hide();
      }else{
        $(this).show();
      }
    });
  };
  //Checks if scrollUp should be displayed
  var chkTop = function(){
    $('div.scrollUp').each(function(){
      var target = $(this).next();
      var tcur   = target.scrollTop();
      if(tcur <= 0){
        $(this).hide();
      }else{
        $(this).show();
      }
    });
  };
  //Doublecheck
  var chkBoth = function(){
    chkTop();
    chkBottom();
  };
  //Click on scrollDown
  $('div.scrollDown').click(function(){
    var target = $(this).prev();
    target.prev().show();
    target.scrollTo("+=500px", 800, {onAfter: chkBottom});
  });
  //Click on scrollUp
  $('div.scrollUp').click(function(){
    var target = $(this).next();
    target.next().show();
    target.scrollTo("-=500px", 800, {onAfter: chkTop});
  })
  //Mousewheel
  $('div.scrollUp').next().bind('mousewheel', function(event, delta){
    var t = $(this);
    if(delta > 0){
      t.scrollTo("-=50px", 0, {onAfter: chkBoth});
    }else{
      t.scrollTo("+=50px", 0, {onAfter: chkBoth});
    }
  });
  //initial checks:
  chkBoth();
}
