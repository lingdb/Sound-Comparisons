StudyWatcher = Backbone.Model.extend({
  initialize: function(){
    //Parsing the current study:
    var study  = 'Germanic';
    var sParse = /.*study=([^&]*).*/.exec(window.location.href);
    if(sParse !== null && sParse.length > 1)
      study = sParse[1];
    //Looking for the last study:
    var lastStudy = study;
    if(localStorage.lastStudy)
      lastStudy = localStorage.lastStudy;
    //The current study will become the last study:
    localStorage.lastStudy = study;
    //Setting the vals:
    this.set({study: study, lastStudy: lastStudy});
  }
, studyChanged: function(){
    var s  = this.get('study');
    var ls = this.get('lastStudy');
    return (s !== ls);
  }
});
