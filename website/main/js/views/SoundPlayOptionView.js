SoundPlayOptionView = Backbone.View.extend({
  initialize: function(){
    this.model.on('change:playMode', this.render, this);
    this.render();
  }
, events: {'click button': 'changeOptions'}
, changeOptions: function(e){
    var mode = $(e.target).val();
    if(!mode || mode == '') mode = 'hover';
    this.model.set({playMode: $(e.target).val()});
  }
, render: function(){
    this.$('button').removeClass('btn-inverse').removeAttr('disabled');
    var mode = this.model.get('playMode');
    if(!mode || mode == '') mode = 'hover';
    this.$('button[value="'+mode+'"]').addClass('btn-inverse').attr('disabled', 'disabled');
  }
});
