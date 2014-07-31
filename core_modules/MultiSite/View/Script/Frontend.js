(function($) {
    cx.ready(function() {
        $("#multisite_signup_form").validate({
            errorElement: "div",
            submitHandler: function(form) {
                var multisite_email_address =  $("#multisite_email_address").val();
                var multisite_address = $("#multisite_address").val();
                loaderImg = 
                domainUrl = cx.variables.get('baseUrl', 'MultiSite')+cx.variables.get('cadminPath', 'contrexx')+"index.php?cmd=JsonData&object=MultiSite&act=signup";
                //call get json function after 
                //all the validations are checked
                cx.jQuery.ajax({
                    dataType: "json",
                    url: domainUrl,
                    data: {
                        multisite_email_address : multisite_email_address,
                        multisite_address : multisite_address,
                    },
                    type: "POST",
                    beforeSend: function(){
                        $("#multisite_create_website").parent().append(getLoader());
                        $("#multisite_create_website").attr('disabled', true);
                    },
                    success: function(response) {
                        $('#ajaxLoader').remove();
                        $("#multisite_create_website").attr('disabled', false);
                        var message = $('#message');
                        if (response.status == 'success') {
                            message.html(response.data);
                            message.css('color', '#006400');
                        } else {
                            message.html(response.message);
                            message.css('color', '#FF0000');
                        }
                    }
                });
            }
        });
        
        getLoader = function (){
            var loadingImagePath = '/lib/javascript/jquery/jstree/themes/default/throbber.gif';    
            var img = $('<img></img>');
            img.attr('src',loadingImagePath);
            img.attr('id','ajaxLoader');
            return img;
        }
    });

    cx_multisite = {
        showSignUp : function (){
            //url = cx.variables.get('baseUrl', 'MultiSite')+cx.variables.get('cadminPath', 'contrexx')+"index.php?cmd=JsonData&object=MultiSite&act=signup&fetchForm=1";
            url = "/cadmin/index.php?cmd=JsonData&object=MultiSite&act=signup&fetchForm=1";
            cx.jQuery.ajax({
                dataType: "json",
                url: url,
                type: "post",
                success: function(response) {
                    cx.ui.dialog({
                        title: 'Sign Up',
                        content: response.data,
                        width:350,
                        height:400
                    });
                }
            });
        }
    }
})(jQuery);


