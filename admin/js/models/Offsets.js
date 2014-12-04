/**
  Since the offsets are generated from Studies and TranslationProviders,
  this model needs to listen to both of them.
*/
Offsets = Backbone.Model.extend({
  defaults: {
    offsets: {} // Map from offsets to arrays of providers
  , selected: null
  }
, initialize: function(){
    //We want shorter access:
    this.providers = window.Translation.translationProviders;
    this.studies   = window.Translation.studies;
    //We listen to changes:
    this.providers.on('change', this.fetch, this);
    this.studies.on('change',   this.fetch, this);
  }
, fetch: function(){
    var query = {
      action: 'offsets'
    , Study: this.studies.get('selected')
    , Providers: this.providers.selected()
    };
    //Validating query:
    if(!query.Study || query.Study === '' || !query.Providers){
      this.set({offsets: {}, selected: null});
    }else{
      query.Providers = JSON.stringify(query.Providers);
      //Fetching:
      var t = this;
      $.get(window.Translation.url, query).done(function(d){
        d = $.parseJSON(d);
        //Transforming offsets:
        var offs = {};
        _.each(_.keys(d), function(p){
          _.each(d[p], function(o){
            var ps = offs[o] || [];
            ps.push(p);
            offs[o] = ps;
          });
        });
        //Saving results:
        var save = {offsets: offs};
        //Automatic selection of first choice:
        if(offs[0]){
          save.selected = 0;
        }
        // We set silent, because we trigger anyway. We trigger in case set doesn't change anything.
        t.set(save, {silent: true});
        t.trigger('change');
      });
    }
  }
, offsetProviders: function(){
    var s = this.get('selected');
    if(s === null) return null;
    return this.get('offsets')[s];
  }
});
