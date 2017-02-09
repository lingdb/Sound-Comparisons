/* global document: false */
"use strict";
define(['jquery','backbone'], function($, Backbone){
  /**
    An onscreen keyboard to insert IPA phonetic symbols into the phonetic filter.
    el: #ipaKeyboard
  */
  return Backbone.View.extend({
    initialize: function(){
      var t = this;
      //Calling render whenever IPAKeyboard is opened:
      this.$el.on('shown', function(){
        t.render();
      });
      //Callbacks for buttons:
      var buttons = this.$('.modal-footer > button').click(function(e){
        t.footerButton($(e.target), buttons);
      });
      //Clicking on table cells:
      this.symbolDescription = this.$('.modal-header > .symbolDescription');
      this.$('td[unicode]').click(function(){/* Inserting on click */
        var symbol = $(this).attr('unicode');
        if(symbol){
          t.insert(symbol);
        }
      }).mouseout(function(){/* Clearing the footer after mouseout */
        t.symbolDescription.text('');
      }).mouseover(function(){/* Displaying description on mouseover */
        var description = $(this).attr('description');
        t.symbolDescription.text(description);
      });
      //Hiding keyboard when hitting enter or escape:
      $(document).keyup(function(e){
        if(e.keyCode == 27 || e.keyCode == 13){
          t.$el.modal('hide');
        }
      });
      //Closing IPAKeyboard on click outside:
      $('body').click(function(e){t.closeOnClick(e);});
    }
  , render: function(){
      var t = this.$el;
      t.css('width', 'auto');
      t.css('right', '');
      t.css('left', '10px');
      //Updating width and position to the left:
      var ww = $(window).width();
      var rw = 0.17*ww;
      var ew = this.$el.width();
      var x = 0;
      if((ww-ew-rw)>10) {
        x = ww-ew-rw;
        this.$el.css('left', x + "px");
        this.$el.css('right', rw + "px");
      } else {
        x = ww-rw-10;
        this.$el.width(x);
        this.$el.css('left', "10px");
        this.$el.css('right', rw + "px");
      }
    }
  , footerButton: function(button, buttons){
      //Changing the buttons:
      buttons.removeClass('disabled');
      button.addClass('disabled');
      //Changing the tables:
      this.$('.modal-body > div:visible').addClass('hide');
      this.$(button.data('target')).removeClass('hide');
      //Updating the modal:
      this.render();
    }
  , insert: function(symbol){
      var pFilter = $('#PhoneticFilter');
      var myField = pFilter.get(0);
      var doc = myField.ownerDocument;
      if(doc.selection){ //IE support:
        myField.focus();
        var sel = doc.selection.createRange();
        sel.text = symbol;
      }else if(myField.selectionStart || myField.selectionStart == '0'){
        //Replacing a selection, FF and hopefully others:
        var startPos = myField.selectionStart;
        var endPos = myField.selectionEnd;
        myField.value = myField.value.substring(0, startPos) +
                        symbol + myField.value.substring(endPos, myField.value.length);
      }else{ //Fallback to appending it to the field:
        myField.value += symbol;
      }
      pFilter.keyup();
    }
  , closeOnClick: function(e){
      if(this.$el.is(':visible')){
        var close = $(e.target).parents('#ipaKeyboard,#IPAOpenKeyboard').length === 0;
        if(close){ this.$el.modal('hide'); }
      }
    }
  });
});
