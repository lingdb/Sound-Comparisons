"use strict";
define(['backbone','bootstrap'], function(Backbone){
  /**
    el: #shortLinkModal
    model: undefined
  */
  return Backbone.View.extend({
    initialize: function(){
      this.$el.modal({backdrop: true, show: false});
    }
    /**
      @param model [{label: String || 'default', url: String}]
      The passed model shall be of the same structure as the resolved promise
      from models/DataStorage.addShortLink().
    */
  , render: function(model){
      //Sanity check:
      if(!_.isArray(model)){
        console.log('Unexpected model structure in ShortLinkModalView.render()!');
        return;
      }
      //Building content to render with:
      var tStorage = App.translationStorage;
      var content = {
        headline: tStorage.translateStatic('shortLinkModal_headline')
      , entries: _.map(model, function(m){
          if(m.label === 'default'){
            m.label = tStorage.translateStatic('shortLinkModal_default');
          }
          m.copyBtn = tStorage.translateStatic('shortLinkModal_copyButton');
          return m;
        }, this)
      };
      //Rendering:
      this.$el.html(App.templateStorage.render('ShortLinkModal', content));
      this.$el.modal('show');
      //Binding button events:
      this.handleButtons();
    }
    /**
      Binds to the click events of .copyBtn inputs in a newly rendered modal,
      to realize copy-to-clipboard functionality.
      Informed by:
      https://stackoverflow.com/q/400212/448591
      https://developer.mozilla.org/en-US/docs/Web/API/Document/execCommand
      https://stackoverflow.com/q/15005059/448591
    */
  , handleButtons: function(){
      this.$('.copyBtn').each(function(){
        var btn = $(this).click(function(){
          btn.removeClass('btn-info');
          //Status function:
          var done = function(ok){
            if(ok){
              btn.removeClass('btn-danger').addClass('btn-success');
            }else{
              btn.removeClass('btn-success').addClass('btn-danger');
            }
          };
          //Copying to clipboard:
          var input = btn.closest('tr').find('.urlInput'), ok = false;
          if(input.length === 1){
            input.get(0).select();
            ok = document.execCommand('copy', true);
          }
          done(ok);
        });
      });
    }
  });
});
