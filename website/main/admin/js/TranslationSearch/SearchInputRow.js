SearchInputRow = Backbone.View.extend({
  initialize: function(){
    this.btn = this.$('.saveButton');
    var row  = this;
    this.$('.updateInput').keyup(function(){row.keystroke();});
    this.$('.saveButton').click(function(e){row.save(e);});
    var dv = new DescriptionView({el: this.$('.description')});
  }
, save: function(e){
    e.preventDefault();
    var b = this.btn.removeClass('btn-warning btn-success').addClass('btn-danger');
    var u = this.$('.updateInput').val();
    this.model.update(u).done(function(){
      b.removeClass('btn-danger btn-warning').addClass('btn-success');
    });
  }
, keystroke: function(){
    this.btn.removeClass('btn-success btn-danger').addClass('btn-warning')
  }
});
