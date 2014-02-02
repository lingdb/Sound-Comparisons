ControlGroupHide = Backbone.View.extend({
  initialize: function(){
    this.hidden = false;
    this.target = this.$('.control-group-hide');
    var t = this;
    this.target.click(function(){
      t.clicked();
    });
  }
, clicked: function(){
    this.target.toggleClass('icon-eye-close')
               .toggleClass('icon-eye-open');
    this.$('.control-group').toggleClass('hide');
    this.hidden = !this.hidden;
  }
});
