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
  /***/
, mainContributors: function(){
    return this.filter(function(c){
      return parseInt(this.get('SortIxForAboutPage')) !== 0;
    }, this);
  }
  /***/
, citeContributors: function(){
    var cs = this.filter(function(c){
      return parseInt(this.get('SortIxForAboutPage')) === 0;
    }, this);
    cs.sort(function(a,b){
      var an = a.get('Surnames'), bn = b.get('Surnames');
      if(an > bn) return -1;
      if(an < bn) return  1;
      return 0;
    });
    return cs;
  }
});
