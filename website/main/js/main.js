/**
 * Initializes stuff.
 * */
$(document).ready(function () {
//initAudio();
  initPlaySequence();
  singleLanguageView();
//initLoad();
  initLogging();
//initScroll();
//initIpaKeyboard();
  //Handling of special links
  $('*[data-href]').click(function(){
    window.location.href = $(this).attr('data-href');
  });
  //Link following on changing <select> (when no click is triggered):
  $('#wordlistfilter select').change(function(){
    window.location.href = $('option:selected', this).attr('data-href');
  });
});
