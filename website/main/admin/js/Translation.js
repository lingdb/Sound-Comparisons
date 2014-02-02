$(function(){
  window.Translation = {
    currentTranslation: new Backbone.Model()
  , results: new ResultCollection()
  , studies: new Studies()
  , url: 'query/translation.php'
  , views: {}
  };
  window.Translation.translationProviders = new TranslationProviders();
  window.Translation.offsets = new Offsets();
  //Building views:
  window.Translation.views.setupView = new TranslationSetupView({
    model: window.Translation.currentTranslation
  , el: $('#Translations')
  });
  window.Translation.views.basicInput = new BasicInput({
    model: window.Translation.results
  , el: $('#BasicTranslation')
  });
  window.Translation.views.searchInput = new SearchInput({
    model: window.Translation.results
  , el: $('#SearchTranslations')
  });
  window.Translation.views.controlGroupHide = new ControlGroupHide({
    el: $('#Translations form:first')
  });
  window.Translation.views.withSelectedTranslationView = new WithSelectedTranslationView({
    model: window.Translation.currentTranslation
  , el: $('#Translations form:nth-child(2)')
  });
  window.Translation.views.resultCollectionView = new ResultCollectionView({
    model: window.Translation.results
  , el: $('#ResultCollectionView')
  });
});
