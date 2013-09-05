$(function(){
  window.Translation = {
    search: new Search()
  , translationId: null
  , views:  {}
  };
  window.Translation.views.searchInput = new SearchInput({
    model: window.Translation.search
  , el:    $('#SearchTranslations')
  });
});
