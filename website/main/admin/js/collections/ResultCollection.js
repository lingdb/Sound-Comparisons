ResultCollection = Backbone.Collection.extend({
  model: Result
, setGroupSize: function(s){
    this.groupSize = s;
    return this;
  }
, getGroupSize: function(){
    return this.groupSize || 0;
  }
, isSearch: true
});
