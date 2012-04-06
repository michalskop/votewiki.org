//hide 'save' and textareas on load
$( document ).delegate(".pageRecord",'pageinit',function(){
    $('#save').hide();
    $('textarea').hide();
});

//show 'save' on any change on page
$( document ).delegate(".pageRecord",'pageinit',function(){
  $(".changable").keyup(function() {
    $('#save').show("slide");
  });
});

//calculates characters left in summaries
$( document ).delegate(".pageRecord",'pageinit',function(){ 
	$('textarea[maxlength]').keyup(function(){  
	    //get the limit from maxlength attribute  
	    var limit = parseInt($(this).attr('maxlength'));  
	    //get the current text inside the textarea  
	    var text = $(this).val();  
	    //count the number of characters in the text  
	    var chars = text.length;  
  
	    //check if there are more characters then allowed  
	    if(chars > limit){  
	        //and if there are use substr to get the text before the limit  
	        var new_text = text.substr(0, limit);    
	        //and change the current text with the new text  
	        $(this).val(new_text);  
	    }
	    textareaId = $(this).attr('id');
	    $("#"+textareaId+'-remaining').html((limit - text.length) + "");
	});  
}); 


//edit texts
var labelID;
$( document ).delegate(".pageRecord",'pageinit',function(){ 
  $('label').click(function() {
       labelID = $(this).attr('for');
       $('#'+labelID+'-text').hide();
       $('#'+labelID).show();
       
  });
});

//delete a tag
$( document ).delegate(".pageRecord",'pageinit',function(){ 
  $('.tag-delete').click(function() {
    tagId = $(this).attr('id');
    tagVal = $('#'+tagId+'-input').attr('value');
    $('#'+tagId+'-wrapper').remove();
    $('#tags').append("<input type='hidden' name='deleted-tag-"+tagId+"-input' id='deleted-tag-"+tagId+"-input' value='"+tagVal+"' />");
    $('#save').show("slide");
  });
});

//add a tag
$( document ).delegate(".pageRecord",'pageinit',function(){ 
  $('#tag-add').click(function() {
    rand = Math.floor(Math.random()*10000000);
    $('#tags').append("<input type='text' name='new-tag-"+rand+"-input' id='new-tag-"+rand+"-input' data-mini='true' />");
    $("#new-tag-"+rand+"-input").textinput();
    $('#save').show("slide");
  });
});

/*
jQuery("div:jqmData(role='page'):last").bind('pageshow', function(){


  alert('This page was just enhanced by jQuery Mobile!');
  $.mobile.activePage('#save').hide();
});
*/
/*
$(".pageRecord").live('pagecreate',function(event){
$(".changable").keyup(function() {
    $('#save').show("slide");
  });
});*/
