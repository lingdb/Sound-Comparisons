/**
  The .js counterpart to query/dynamicTranslation.php
*/
function DynamicTranslation(){
  //Initializing DynamicTranslation:
  var qTarget = 'query/dynamicTranslation.php';
  var t = this;
  this.study         = null; // The Name of the current study
  this.suffix        = null; // The Suffix to work with
  this.translationId = null; // The current TranslationId
  this.offset        = null; // The current offset
  //Helper function to toggle disables on buttons and display study selection:
  var toggleDisabled = function(target){
    target = $(target);
    target.closest('form').find('button').removeClass('btn-info');
    target.addClass('btn-info');
  };
  //Helper function to tell if a suffix needs to know the Study.
  var studyIndependent = function(suffix){
    switch(suffix){
      case 'Families':
      case 'LanguageStatusTypes':
      case 'MeaningGroups':
      case 'Studies':
      case 'StudyTitle':
        return true;
      default:
        return false;
    }
  };
  //Helper function to hide study selection:
  var displayStudySelection = function(suffix){
    if(!studyIndependent(suffix)){
      $('#DynamicTranslations_StudyList').show();
    }else{
      $('#DynamicTranslations_StudyList').hide();
    }
  };
  //Fetching the studies:
  $.getJSON(qTarget, {action: 'fetchStudies'}, function(studies){
    var studyList = $('#DynamicTranslations_StudyList');
    $(studies).each(function(i, s){
      studyList.append('<button class="btn">'+s+'</button>');
    });
    studyList.find('button').click(function(e){
      e.preventDefault();
      toggleDisabled(this);
      var s = $(this).text();
      t.study = s;
      t.fetchOffsets();
    });
  });
  //Fetching the suffixes:
  $.getJSON(qTarget, {action: 'fetchSuffixes'}, function(suffixes){
    var suffixList = $('#DynamicTranslations_SuffixList');
    $(suffixes).each(function(i, s){
      suffixList.append('<button class="btn">'+s+'</button>');
    });
    suffixList.find('button').click(function(e){
      e.preventDefault();
      var s = $(this).text();
      t.suffix = s;
      t.fetchOffsets();
      toggleDisabled(this);
      displayStudySelection(s);
    });
  });
  //The Handler to work with received Offsets:
  this.offsetHandler = function(offsets){
    var pageList = $('#DynamicTranslations_PageList');
    pageList.find('button').remove();
    $(offsets).each(function(i, o){
      var start = o + 1;
      var end   = o + 30;
      pageList.append('<button class="btn" data-offset="'+o+'">'+start+'-'+end+'</button>');
    });
    pageList.find('button').click(function(e){
      e.preventDefault();
      toggleDisabled(this);
      var offset = $(this).attr('data-offset');
      t.offset = offset;
      t.fetchTranslations();
    });
    pageList.find('button:first').trigger('click');
  };
  //Fetching Offsets:
  this.fetchOffsets = function(){
    if(!t.suffix || (!studyIndependent(t.suffix) && !t.study))
      return;
    var q = {
      action: 'fetchOffsets'
    , Study:  t.study
    , Suffix: t.suffix
    };
    $.getJSON(qTarget, q, function(offsets){
      t.offsetHandler(offsets);
    });
  };
  //The Handler to work with received Translations:
  this.tHandler = function(translations){
    var tbody = $('#DynamicTranslations_Table tbody');
    tbody.empty();
    console.log(translations);
    var th = this; // Necessary for row reordering
    $(translations).each(function(i, t){
      var sCells = [];
      var dCells = [];
      var tCells = [];
      $.each(t.Source, function(k, v){
        var cell = '<td></td>';
        if(typeof(v) === 'string'){
          cell = '<td class="source" data-SourceField="'+k+'" title="'+k+'" data-copyVal="'+v+'">'+v
               + '<div class="icon pull-right btn"><i class="icon-arrow-right" title="Copy value."></i></div></td>';
        }else if(v.rfc && v.form){
          cell = '<td>Reference form in '+v.rfc+': '+v.form+'</td>';
        }
        sCells.push(cell);
      });
      $.each(t.Description, function(k, v){
        dCells.push('<td class="description" data-DescriptionField="'+k+'">'+v+'</td>');
      });
      $.each(t.Source, function(k, v){
        if(typeof(v) === 'string'){
          var tVal = t.Translation[k] ? t.Translation[k] : '';
          tCells.push('<td class="translation" data-TranslationField="'+k+'"><input type="text" value="'+tVal+'"></td>');
        }else{
          tCells.push('<td></td>');
        }
      });
      //Building the rows:
      var rows = [];
      for(var j = 0; j < sCells.length; j++){
        var rclass = (i%2 === 0) ? 'success' : 'warning';
        if(!dCells[j] && !sCells[j] && !tCells[j])
          continue;
        if(!dCells[j]) dCells[j] = '<td></td>';
        if(tCells[j] === sCells[j] && sCells[j] === '<td></td>')
          continue;
        var row = '<tr data-Key="'         + t.Key
                + '" data-Study="'         + t.Study
                + '" data-TableSuffix="'   + t.TableSuffix
                + '" data-TranslationId="' + t.TranslationId
                + '" data-TArray="'        + i 
                + '" class="'              + rclass + '">'
                + dCells[j] + sCells[j] + tCells[j]
                + '</tr>';
        rows.push(row);
      }
      //Reordering rows if necessary:
      if(th.suffix === "LanguageStatusTypes")
        rows = th.swapFields(rows, 0, 1);
      if(th.suffix === "Words")
        rows = th.swapFields(rows, 0, 1);
      //Placing rows:
      $(rows.join('')).appendTo(tbody).find('.icon').click(function(){
        var t = $(this).parent().attr('data-copyVal');
        $(this).closest('tr').find('.translation input').val(t);
      });
    });
    //Editing descriptions:
    $('td.description').dblclick(function(){
      var el = $(this);
      if(el.find('textarea').length > 0) return;
      var q = {
        action:      'updateTranslationDescription'
      , Req:         el.attr('data-DescriptionField')
      , Description: el.html()
      };
      el.html('<textarea style="width:100%;">'+q.Description+'</textarea>');
      el.find('textarea').blur(function(){
        q.Description = $(this).val();
        $.get('query/translation.php', q);
        el.html(q.Description);
      }).autoResize();
    });
  };
  //Fetching a bunch of Translations:
  this.fetchTranslations = function(){
    //Cases where we can't do this:
    if(!t.suffix || !t.translationId || !t.offset) return;
    if(!studyIndependent(t.suffix) && !t.study)    return;
    //Building a query:
    var q = {
      action:        'fetchTranslations'
    , Study:         t.study
    , Suffix:        t.suffix
    , TranslationId: t.translationId
    , Offset:        t.offset
    };
    t.tHandler([]); // To show that we're fetching…
    $.getJSON(qTarget, q, function(translations){t.tHandler(translations);});
  };
  //Storing Translations on the Server:
  this.storeTranslations = function(){
    //Gathering TArray values:
    var TArray = {};
    $('#DynamicTranslations_Table tbody tr').each(function(i, e){
      var t = $(this).attr('data-TArray');
      if(t) TArray[t] = true;
    });
    //Iterating TArray values:
    $(Object.keys(TArray)).each(function(t){
      var fields = $('#DynamicTranslations_Table tbody tr[data-TArray="'+t+'"]');
      var translation = {};
      fields.find('.source').each(function(){
        translation[$(this).attr('data-SourceField')] = '';
      });
      var hasContent = false;
      fields.find('.translation').each(function(){
        var key  = $(this).attr('data-TranslationField');
        var tVal = $('input', this).val();
        if(tVal !== '') hasContent = true;
        translation[key] = tVal;
      });
      if(!hasContent) return;
      var data = {
        Key:           fields.attr('data-Key')
      , Study:         fields.attr('data-Study')
      , TableSuffix:   fields.attr('data-TableSuffix')
      , Translation:   translation
      , TranslationId: fields.attr('data-TranslationId')
      , action:        'storeTranslation'
      };
      console.log(data);
      $('#DynamicTranslations_SaveAll~img').css('display', 'inline');
      $.get(qTarget, data, function(data){
        $('#DynamicTranslations_SaveAll~img').hide();
      });
    });
  };
  $('#DynamicTranslations_SaveAll').click(function(){ t.storeTranslations(); });
  //Called by Translation to let the user work with dynamic Translations:
  this.show = function(){
    $('#DynamicTranslations').show();
  };
  //Called by Translation to let the user work with something else:
  this.hide = function(){
    $('#DynamicTranslations').hide();
  };
  //Called by Translation to set a new TranslationId:
  this.setTranslationId = function(tid){
    t.translationId = tid;
    t.fetchTranslations();
    window.Translation.translationId = tid;
  };
  //Help method.
  this.swapFields = function(arr, i, j){
    if(i >= arr.length || j >= arr.length)
      return arr;
    var x  = arr[i];
    arr[i] = arr[j];
    arr[j] = x;
    return arr;
  };
}
