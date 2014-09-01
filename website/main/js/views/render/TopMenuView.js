/**
  The TopMenuView will be used by the Renderer, and won't have an el.
  The TopMenuView will set it's own model to handle and smartly update it's render data.
*/
TopMenuView = Backbone.View.extend({
  initialize: function(){
    //FIXME IMPLEMENT
  }
, render: function(){
    console.log('TopMenuView.render()');
    //FIXME IMPLEMENT
    return {TopMenu: {currentStudyName: 'Kragen!'}};
  }
});
