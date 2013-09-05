var _gaq = _gaq || [];
function initLogging(){
  //Ga init:
  _gaq.push(['_setAccount', 'UA-28740209-1']);
  (function() {
    var ga = document.createElement('script'); ga.type = 'text/javascript'; ga.async = true;
    ga.src = ('https:' == document.location.protocol ? 'https://ssl' : 'http://www') + '.google-analytics.com/ga.js';
    var s = document.getElementsByTagName('script')[0]; s.parentNode.insertBefore(ga, s);
  })();
  //Custom Variables:
  var url = $('div#saveLocation').attr('href');
  var r = {
      study:    /.*study=([^&]*).*/
    , viewType: /.*pageView=([^&]*).*/
    , language: /.*languages=([^&]*).*/
    , word:     /.*words=([^&]*).*/
  };
  //console.log('Url is:\t' + url);
  //Slot 1: Study
  if(r.study.test(url)){
    var study = r.study.exec(url)[1];
    //console.log('Study is:\t' + study);
    _gaq.push(['_setCustomVar',1,'Study', study, 3]);
  }
  //Slot 2: ViewType
  var viewType = '';
  if(r.viewType.test(url)){
    viewType = r.viewType.exec(url)[1];
    //console.log('ViewType is:\t' + viewType);
    _gaq.push(['_setCustomVar',2,'ViewType', viewType, 3]);
  }
  //Slot 3: Language
  if(r.language.test(url) && viewType != 'multiWordView'){
    var language = r.language.exec(url)[1];
    //console.log('Language is:\t' + language);
    _gaq.push(['_setCustomVar',3,'Language', language, 3]);
  }
  //Slot 4: Word
  if(r.word.test(url) && viewType != 'multiWordView'){
    var word = r.word.exec(url)[1];
    //console.log('Word is:\t' + word);
    _gaq.push(['_setCustomVar',4,'Word', word, 3]);
  }
  //Send it all:
  _gaq.push(['_trackPageview']);
}
