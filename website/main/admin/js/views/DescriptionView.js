DescriptionView = Backbone.View.extend({
  initialize: function(){
    if(!(this.el instanceof jQuery))
      this.el = $(this.el);
    var descriptionView = this;
    $(this.el).dblclick(function(){
      descriptionView.addInput();
    });
  }
, addInput: function(){
    if($('#topMenu').attr('data-isadmin') != '1') return;
    if(this.$('textarea').length !== 0) return;
    var c = this.el.html();
    this.el.html('<textarea style="width: 90%; height: 90%;">'+c+'</textarea>');
    var descriptionView = this;
    this.$('textarea').autoResize().blur(function(){
      descriptionView.closeInput();
    });
  }
, closeInput: function(){
    var q = {
      action:      'updateTranslationDescription'
    , Req:         this.el.attr('data-req')
    , Description: this.$('textarea').val()
    };
    var el = this.el;
    $.get(window.Translation.url, q).done(function(){
      el.html(q.Description);
    });
  }
});
