/**
  model: Result
, el: Row in a table
*/
ResultRowView = Backbone.View.extend({
  events: {
    'click .copy-over': 'copyOver'
  }
, initialize: function(){
    this.icon = '<i class="icon-hdd"></i>';
    this.btn  = this.$('.saveButton');
    var row   = this;
    this.$('.updateInput').keyup(function(){row.keystroke();});
    this.$('.saveButton').click(function(e){row.save(e);});
    var dv = new DescriptionView({el: this.$('.description')});
    //Put grey background for inputs iff match is empty:
    if(this.$('.copy-over').parent().text() == ''){
      this.$('.updateInput').css('background-color', '#F5F5F5');
    }
  }
, save: function(e){
    e.preventDefault();
    var icon = this.icon;
    var b = this.btn.removeClass('btn-warning btn-success').addClass('btn-danger').html(icon+'Saving…');
    var u = this.$('.updateInput').val();
    this.model.update(u).done(function(){
      b.removeClass('btn-danger btn-warning').addClass('btn-success').html(icon+'Saved OK');
    });
  }
, keystroke: function(){
    this.btn.removeClass('btn-success btn-danger').addClass('btn-warning').html(this.icon+'Not saved');
  }
, copyOver: function(){
    var original = this.$('.copy-over').parent().text();
    this.$('.updateInput').val(original).trigger('keyup');
  }
});
