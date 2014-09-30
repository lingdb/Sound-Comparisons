Result = Backbone.Model.extend({
  update: function(u){
    var r = this
      , t = this.get('Translation');
    t.Update = u;
    t.action = 'update';
    return $.get(window.Translation.url, t).done(function(){
      r.set({Update: u});
    });
  }
, getFlag: function(){
    var ret = '';
    if(mId = this.get('MatchId')){
      if(ts = window.Translation.translations){
        _.each(ts, function(t){
          if(t.TranslationId == mId){
            var src = t.ImagePath;
            ret = '<img src="../'+src+'">';
          }
        }, this);
      }
    }
    return ret;
  }
});
