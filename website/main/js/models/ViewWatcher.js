ViewWatcher = Backbone.Model.extend({
  initialize: function(){
    //Parsing the current view:
    var view = 'mapView';
    var vParse = /.*pageView([^&]*).*/.exec(window.location.href);
    if(vParse !== null && vParse.length > 1)
      view = vParse[1];
    //Looking for the last view:
    var lastView = view;
    if(localStorage.lastView)
      lastView = localStorage.lastView;
    //The current view will become the last view:
    localStorage.lastView = view;
    //Setting the vals:
    this.set({view: view, lastView: lastView});
  }
, viewChanged: function(){
    var v  = this.get('view');
    var lv = this.get('lastView');
    return (v !== lv);
  }
});
