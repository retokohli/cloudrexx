(function($) {
    cx.ready(function() {  
        $("#emailAddress").keyup(function() {
            var length = $("#emailAddress").val().length;       
            if (length > 0)
            {
                if (/^\w+([\.-]?\w+)*@\w+([\.-]?\w+)*(\.\w{2,3})+$/.test($("#emailAddress").val())){
                    $("#email_error").html("");
                        return (true)	
                }else{
                    $("#email_error").html("Invalid E-mail Address!");
                }
                return (false)
            }
            else
            {
               $("#email_error").html("E-mail Address Required!");
               return (false)
            }
        });
        $("#blogAddress").keyup(function() {
            if($("#blogAddress").val()==""){
                $("#blog_error").html("Site Address Required!");
            }else{
                $("#blog_error").html("");
            }
        });
    });

    JQUERYVALIDATION = {
        regValidation: function() {
            var count=0;
            var emailAddress =  $("#emailAddress").val();
            var blogAddress = $("#blogAddress").val();
            var langId = $("#langId").val();
            var length = emailAddress.length;           
            if (length > 0)
            {
                if (/^\w+([\.-]?\w+)*@\w+([\.-]?\w+)*(\.\w{2,3})+$/.test(emailAddress)){
                    $("#email_error").html("");
                }else{ 
                    $("#email_error").html("Invalid E-mail Address!");
                    ++count;
                }
            }else{
               $("#email_error").html("E-mail Address Required!");
               ++count;
            }
            if(blogAddress==""){
                $("#blog_error").html("Site Address Required!");
                ++count;
            }else{
                if(blogAddress.match(/^\d+$/)){
                    $("#blog_error").html("Site Address can not be numeric!");
                    ++count;
                }else{
                    $("#blog_error").html("");
                }
            }
            if(count>0){
                return false;
            }else{
                domainUrl = cx.variables.get('baseUrl', 'MultiSite')+cx.variables.get('cadminPath', 'contrexx')+"index.php?cmd=JsonData&object=MultiSite&act=signup";
                //call get json function after 
                //all the validations are checked
                jQuery.ajax({
                    dataType: "json",
                    url: domainUrl,
                    data: {
                        websiteName : blogAddress,
                        email : emailAddress,
                        langId : langId
                    },
                    type: "POST",
                    beforeSend: function(){
                        $('.load').remove();
                        $("button[name='createBlog']").parent().append('<div class="load">Requesting...</div>');
                    },
                    success: function(data) {
                        //$('.load').remove();
                        if(data.status=="success"){
                            $('.load').html('<font color="green">Website created Successfully</font>');
                        }else{
                            $('.load').html('<font color="red">'+data.message+'</font>');    
                        }
                        
                    }
                });
                return false;
            }
        }
    }
})(jQuery);


