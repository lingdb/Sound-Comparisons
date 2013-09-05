/**
  An onscreen keyboard to insert IPA phonetic symbols into the phonetic filter.
  This is a remix of some cool prior art. (Dunno if author want's to be mentioned)
*/
function initIpaKeyboard(){
  /* Function to deactivate the last displayed thingy. */
  var deAct = function(){
    $('.ipaButtonAct').removeClass('ipaButtonAct').addClass('ipaButtonInAct');
    $('#ipaKeyboard .fadeIn').removeClass('fadeIn').addClass('fadeOut');
  };
  var keyboard = $('#ipaKeyboard');
  var position = function(){
    var mLeft = (window.innerWidth  - keyboard.width() ) / 2;
    var mTop  = (window.innerHeight - keyboard.height()) / 2;
    keyboard.css('margin-left', mLeft + 'px').css('margin-top', mTop + 'px');
  };
  var keyboardButton = $('#IPAOpenKeyboard');
  var hideKeyboard = function(){
    if(keyboard.is(':visible'))
      keyboardButton.trigger('click');
  };
  /* Displaying the keyboard on click */
  keyboardButton.toggle(
      function(){keyboard.show(); position();}
    , function(){keyboard.hide();});
  /* Closing the keyboard on click */
  $('#ipaClose, #contentArea, #leftMenu').click(hideKeyboard);
  /* Closing the keyboard when pressing the escape key */
  $(document).keyup(function(e){
    if(e.keyCode == 27){
      hideKeyboard();
    }
  });
  /* Closing the keyboard when typing 'enter' */
  $('#PhoneticFilter').keyup(function(e){
    if(e.keyCode == 13){
      hideKeyboard();
    }
  });
  /* The buttons in the keyboard header: */
  $('#ipaButtonVowels').click(function(){deAct();
    $(this).removeClass('ipaButtonInAct').addClass('ipaButtonAct');
    $('#ipaVowels').removeClass('fadeOut').addClass('fadeIn');
    position();
  });
  $('#ipaButtonConsonants').click(function(){deAct();
    $(this).removeClass('ipaButtonInAct').addClass('ipaButtonAct');
    $('#ipaConsonants').removeClass('fadeOut').addClass('fadeIn');
    position();
  });
  $('#ipaButtonOthers').click(function(){deAct();
    $(this).removeClass('ipaButtonInAct').addClass('ipaButtonAct');
    $('#ipaOthers').removeClass('fadeOut').addClass('fadeIn');
    position();
  });
  $('#ipaButtonTone').click(function(){deAct();
    $(this).removeClass('ipaButtonInact').addClass('ipaButtonAct');
    $('#ipaTone').removeClass('fadeOut').addClass('fadeIn');
    position();
  });
  /* A function to insert the symbols: */
  var insert = function(symbol){
    var pFilter = $('#PhoneticFilter');
    var myField = pFilter.get(0);
    var doc = myField.ownerDocument;
    //IE support
    if (doc.selection) {
      myField.focus();
      sel = doc.selection.createRange();
      sel.text = symbol;
    }
    //Replacing a selection, FF and hopefully others
    else if (myField.selectionStart || myField.selectionStart == '0') {
      var startPos = myField.selectionStart;
      var endPos = myField.selectionEnd;
      myField.value = myField.value.substring(0, startPos) + 
                      symbol + myField.value.substring(endPos, myField.value.length);
    } 
    //Fallback to appending it to the field
    else {
      myField.value += symbol;
    }
    pFilter.keyup();
  };
  /* The wanted table cells */
  $('#ipaKeyboard td[unicode]').click(function(){/* Inserting on click */
    var symbol = $(this).attr('unicode');
    if(symbol == null || symbol == '')
      return;
    insert(symbol);
  }).mouseout(function(){/* Clearing the footer after mouseout */
    $('#keyboardFooter').text('');
  }).mouseover(function(){/* Displaying description on mouseover */
    var description = $(this).attr('description');
    $('#keyboardFooter').text(description);
  });
}
