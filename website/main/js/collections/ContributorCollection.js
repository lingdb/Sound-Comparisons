/***/
ContributorCollection = Backbone.Collection.extend({
  model: Contributor
  /**
    Custom comparator to make sure contributors are sorted by SortIxForAboutPage
  */
, comparator: function(a, b){
    var x = a.get('SortIxForAboutPage')
      , y = b.get('SortIxForAboutPage');
    if(x > y) return -1;
    if(x < y) return  1;
    return 0;
  }
  /**
    The update method is connected by the App,
    to listen on change:global of the App.dataStorage.
  */
, update: function(){
    var ds   = App.dataStorage
      , data = ds.get('global').global;
    if(data && 'contributors' in data){
      console.log('ContributorCollection.update()');
      this.reset(data.contributors);
    }
  }
});
