"use strict";
define(['require',
        'underscore',
        'backbone',
        'Mustache',
        'models/Loader',
        // Templates to be provided by TemplateStorage:
        'extern/text!templates/body.html',
        'extern/text!templates/contributor.html',
        'extern/text!templates/contributors.html',
        'extern/text!templates/head.html',
        'extern/text!templates/index.html',
        'extern/text!templates/ipaConsonants.html',
        'extern/text!templates/ipaKeyboard.html',
        'extern/text!templates/ipaOthers.html',
        'extern/text!templates/ipaTone.html',
        'extern/text!templates/ipaVowels.html',
        'extern/text!templates/LanguageDescription.html',
        'extern/text!templates/LanguageHeadline.html',
        'extern/text!templates/LanguageLinks.html',
        'extern/text!templates/LanguageMenu.html',
        'extern/text!templates/LanguageSuperscript.html',
        'extern/text!templates/LanguageTable.html',
        'extern/text!templates/LoadModal.html',
        'extern/text!templates/login.html',
        'extern/text!templates/MapView.html',
        'extern/text!templates/MeaningGroupList.html',
        'extern/text!templates/MeaningGroupsHeadline.html',
        'extern/text!templates/Multitable.html',
        'extern/text!templates/MultitableSpace.html',
        'extern/text!templates/MultitableTransposed.html',
        'extern/text!templates/Phonetics.html',
        'extern/text!templates/PhoneticsProxy.html',
        'extern/text!templates/Projects.html',
        'extern/text!templates/RegionList.html',
        'extern/text!templates/SearchFilter.html',
        'extern/text!templates/shortlinkerror.html',
        'extern/text!templates/ShortLinkModal.html',
        'extern/text!templates/SortBy.html',
        'extern/text!templates/TopMenu.html',
        'extern/text!templates/WordHeadline.html',
        'extern/text!templates/WordList.html',
        'extern/text!templates/WordMapsLink.html',
        'extern/text!templates/WordMenu.html',
        'extern/text!templates/WordTable.html'],
       function(require, _, Backbone, Mustache, Loader){
  return Backbone.Model.extend({
    defaults: {
      ready:    false // true iff partials and render method are ready.
    , partials: null  // PartialName -> Content
    }
    /***/
  , initialize: function(){
      var tNames = ['body', 'contributor', 'contributors', 'head', 'index',
                    'ipaConsonants', 'ipaKeyboard', 'ipaOthers', 'ipaTone',
                    'ipaVowels', 'LanguageDescription', 'LanguageHeadline',
                    'LanguageLinks', 'LanguageMenu', 'LanguageSuperscript',
                    'LanguageTable', 'LoadModal', 'login', 'MapView',
                    'MeaningGroupList', 'MeaningGroupsHeadline', 'Multitable',
                    'MultitableSpace', 'MultitableTransposed', 'Phonetics',
                    'PhoneticsProxy', 'Projects', 'RegionList', 'SearchFilter',
                    'shortlinkerror', 'ShortLinkModal', 'SortBy', 'TopMenu',
                    'WordHeadline', 'WordList', 'WordMapsLink', 'WordMenu',
                    'WordTable'];
      var ps = {};
      _.each(tNames, function(name){
        var content = require("extern/text!templates/"+name+".html");
        ps[name] = _.map(content.split("\n"), function(s){
          return s.trim();
        }).join('');
      }, this);
      this.set({ready: true, partials: ps});
    }
  /**
    @param name String name of the template
    @param view Object typical mustache view object
    @return rendered String
    Renders the template name via mustache, using all known templates as partials.
  */
  , render: function(name, view){
      var ps = this.get('partials');
      if(ps === null || _.isUndefined(ps)){
        console.log('TemplateStorage.render() called before partials were ready :(');
        return null;
      }
      if(!(name in ps)){
        console.log('TemplateStorage.render():\nMissing partial: '+name+'\nin keys: '+_.keys(ps));
        return null;
      }
      return Mustache.render(ps[name], view, ps);
    }
  });
});
