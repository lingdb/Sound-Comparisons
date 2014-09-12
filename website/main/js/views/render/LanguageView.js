/***/
LanguageView = Backbone.View.extend({
  /***/
  initialize: function(){
    this.model = {};
  }
  /**
    Method to make it possible to check what kind of PageView this Backbone.View is.
  */
, getKey: function(){return 'language';}
  /**
    Overwrites the current model with the given one performing a deep merge.
  */
, setModel: function(m){
    this.model = $.extend(true, this.model, m);
  }
  /***/
, updateLanguageHeadline: function(){
    var language = App.languageCollection.getChoice();
    if(!language){
      console.log('LanguageView.updateLanguageHeadline() without a language.');
      return;
    }
    //The basic headline:
    var headline = {
      longName: language.getLongName()
    , LanguageLinks: undefined // FIXME implement
    , LanguageDescription: undefined // FIXME implement
    , playAll: App.translationStorage.translateStatic('language_playAll')
    };
    //Previous Language:
    //Next Language:
    //Contributors:
    //FIXME implement
    //Done:
    this.setModel({languageHeadline: headline});
  }
  /***/
, updateLanguageTable: function(){
    //FIXME implement
  }
  /***/
, render: function(){
    console.log('LanguageView.render()');
    if(App.pageState.isPageView(this)){
      this.$el.html(App.templateStorage.render('LanguageTable', this.model));
      this.$el.removeClass('hide');
    }else{
      this.$el.addClass('hide');
    }
  }
});
