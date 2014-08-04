TemplateStorage = Backbone.Model.extend({
  defaults: {
    ready:    false // true iff partials and render method are ready.
  , partials: null  // PartialName -> Content
  }
, initialize: function(){
    var storage = this;
    $.getJSON('query/templateInfo').done(function(info){
      info = _.map(info, function(hash, path){
        var name = /\/(.+)\.html$/.exec(path)[1];
        return {
          name: name
        , path: path
        , hash: hash
        };
      });
      storage.process(info);
    }).fail(function(){
      window.App.linkInterceptor.set({enabled: false});
      console.log('Could not fetch templateInfo from host -> LinkInterceptor disabled.');
    });
  }
/**
  Processes templateInfo data as a list of template objects,
  given from the initialize method.
  Only templates with unknown/different hashes are fetched.
  Once all templates are fetched, the TemplateStorage becomes ready,
  and the render method can be invoked.
*/
, process: function(info){
    var fetches = [];
    _.each(info, function(i){
      var current = this.load(i.name);
      if(current && current.hash === i.hash){
        i.content = current.content;
      }else{
        console.log('Fetching template: '+i.name);
        fetches.push($.get(i.path, function(c){
          i.content = c;
        }));
      }
    }, this);
    var storage = this;
    $.when.apply($, fetches).done(function(){
      _.each(info, storage.store, storage);
      var ps = {};
      _.each(info, function(i){ps[i.name] = i.content});
      storage.set({ready: true, partials: ps});
    });
  }
//Loads a template object from localStorage
, load: function(name){
    var key = 'tmpl_'+name;
    if(key in localStorage)
      return $.parseJSON(LZString.decompressFromBase64(localStorage[key]));
    return null;
  }
//Stores a template object in localStorage.
, store: function(tmpl){
    if('content' in tmpl){
      var key = 'tmpl_'+tmpl.name;
      localStorage[key] = LZString.compressToBase64(JSON.stringify(tmpl));
    }
  }
/**
  @param name String name of the template
  @param view Object typical mustache view object
  @return rendered String
  Renders the template name via mustache, using all known templates as partials.
*/
, render: function(name, view){
    var ps = this.get('partials');
    return Mustache.render(ps[name], view, ps);
  }
});
