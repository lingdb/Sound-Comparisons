/*
  Load listens for links with a load attribute and changes their behaviour.
  It does so by looking at each page and examining the current pageView.
  The last Page of each pageView is saved to a cookie.
  If a link has a load that matches a pageview already saved to cookie,
  the link is not followed, but it's cookies location is restored.
*/
function initLoad(){
  //save:
  var regex = /.*pageView=([^&]*).*/;
  var currentLocation = $('div#saveLocation').attr('href');
  var save = 'load_' + regex.exec(currentLocation)[1];
  $.cookie(save, currentLocation);
  //load:
  $('a[load]').click(function(event){
    var load = 'load_' + $(this).attr('load');  // The cookie that we want
    var target = $.cookie(load);                // Fetching the cookie
    if(target === null)                         // If cookie can't be found
      target = $(this).attr('href');            // Follow the href of the link
    //If the studies are the same, we use target, otherwise we follow the link.
    var studyRegex = /.*study=([^&]*).*/;
    var currentStudy = studyRegex.exec(window.location.href)[1];
    var targetStudy = studyRegex.exec(target)[1];
    if(targetStudy == currentStudy){
      event.preventDefault();
      window.location.href = target;
    }
  });
}
