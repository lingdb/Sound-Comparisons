/**
  TranslationProviders gathers all available providers on creation and sets ready=true, when done.
  After gathering, providerGroups will contain keys that are valid attributes of TranslationProviders,
  and will, via get, deliver an array of TranslationProviders that fall into the given group.
  Also providers will contain an array of all TranslationProviders known.
*/
TranslationProviders = Backbone.Model.extend({
  defaults: {
    ready: false
  , providerGroups: []
  , providers: []
  , selected: null //The selected providerGroup
  }
, initialize: function(){
    var t = this;
    $.get(window.Translation.url, {action: 'providers'}, function(data){
      var ps = $.parseJSON(data);
      ps.providerGroups = _.filter(_.keys(ps), function(p){
        return (p[0] !== '_');// Filtering providers only
      });
      ps.ready = true;
      ps.providers = _.flatten(_.map(ps.providerGroups, function(pGroup){
        ps[pGroup];
      }));
      t.set(ps);
    });
  }
, selected: function(){
    var s = this.get('selected');
    if(!s || s === '')
      return null;
    return this.get(s);
  }
, studyDependent: function(p){
    return (p in this.get('_dependsOnStudy'));
  }
});
