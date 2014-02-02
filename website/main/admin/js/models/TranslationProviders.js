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
      t.set(_.extend(ps, {ready: true, providerGroups: _.keys(ps), providers: _.flatten(_.values(ps))}));
    });
  }
, selected: function(){
    var s = this.get('selected');
    if(!s || s === '')
      return null;
    return this.get(s);
  }
});
