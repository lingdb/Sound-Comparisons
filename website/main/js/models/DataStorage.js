/**
  Given that query/data allows us to fetch individual studies,
  it appears helpful, to keep the data in localStorage.
*/
DataStorage = Backbone.Model.extend({
  /* We track the age of all fetched data,
     so that we can delete them beginning with the oldest, if necessary.
  */
  time: Date.now || function(){
    return +new Date;
  }
});
