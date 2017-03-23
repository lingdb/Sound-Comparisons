/* Functionality to create, edit and delete users. */
$(document).ready(function(){
  var updateUserEditTable; // Making this known for callbacks.
  //Buttons in the userEditTable:
  var userEditTableActions = function(){
    $('#userEditTable .editTableEntry').each(function(i, e){
      //Object to save the current entry data:
      var entryData = {userid: $('td:first', e).html()};
      //Entry fields to be watched:
      var login    = $('input[name="login"]', e);
      var password = $('input[name="password"]', e);
      var mayT     = $('input[name="mayTranslate"]', e);
      var mayE     = $('input[name="mayEdit"]', e);
      var mayU     = $('input[name="mayUpload"]', e);
      var mayS     = $('input[name="isSuperuser"]', e);
      //Watching the fields:
      login.keyup(function(){
        entryData.login = login.val();
      });
      password.keyup(function(){
        entryData.password = password.val();
      });
      mayT.change(function(){
        if(mayT.is(':checked'))
          entryData.mayTranslate = "1";
        else
          entryData.mayTranslate = "0";
      });
      mayE.change(function(){
        if(mayE.is(':checked'))
          entryData.mayEdit = "1";
        else
          entryData.mayEdit = "0";
      });
      mayU.change(function(){
        if(mayU.is(':checked'))
          entryData.mayUpload = "1";
        else
          entryData.mayUpload = "0";
      });
      mayS.change(function(){
        if(mayS.is(':checked')){
          entryData.isSuperuser = "1";
          entryData.mayEdit = "1";
          entryData.mayTranslate = "1";
        }else
          entryData.isSuperuser = "0";
      });
      //Buttons:
      $('button.update',e).click(function(){
        $.post("query/admin.php?action=update", entryData, function(data){
          alert(data);
          updateUserEditTable();
        });
      });
      $('button.delete',e).click(function(){
        if(confirm('Are you sure you want to delete a user?')){
          $.post("query/admin.php?action=delete", entryData, function(data){
            alert(data);
            updateUserEditTable();
          });
        }
      });
    });
  };
  userEditTableActions();
  //Updating the userEditTable:
  updateUserEditTable = function(){
    $.get("userEditTable.php", function(data, tStatus, xhr){
      console.log("Got new Table:\n" + data);
      //Deleting current content:
      $('#userEditTable .editTableEntry').remove();
      //Placing new content:
      $('#userEditTable tbody').html(data);
      //Rebinding buttons:
      userEditTableActions();
    });
  };
  //Creating new users:
  $('#addUser button').click(function(e){
    var query = {
      username:     $('#addUser input[name="username"]').val()
    , password:     $('#addUser input[name="password"]').val()
    , mayTranslate: $('#addUser input[name="mayTranslate"]').is(':checked')
    , mayEdit:      $('#addUser input[name="mayEdit"]').is(':checked')
    , mayUpload:    $('#addUser input[name="mayUpload"]').is(':checked')
    , isSuperuser:  $('#addUser input[name="isSuperuser"]').is(':checked')
    };
    if(query.mayTranslate) query.mayTranslate = 1;
    else query.mayTranslate = 0;
    if(query.mayEdit) query.mayEdit = 1;
    else query.mayEdit = 0;
    if(query.mayUpload) query.mayUpload = 1;
    else query.mayUpload = 0;
    if(query.isSuperuser){
      query.isSuperuser = 1;
      query.mayEdit = 1;
      query.mayTranslate = 1;
    }
    else query.isSuperuser = 0;
    $.post('query/admin.php?action=create', query, function(data){
      alert(data);
      updateUserEditTable();
    });
    e.preventDefault();
  });
});
