/*
 ******************** javascript functions ********************
 *
 * author :: janik tschanz
 */

//var jq191 = jQuery.noConflict();        

// remap jQuery to $
(function($)
{
    /**
     ********************  vars ********************
     *
     */   

    var url = window.location;   
    
    $('#content').css('min-height', ($(window).height()-$('header').outerHeight()));
    $('nav').css('min-height', ($(window).height()-$('header').outerHeight()));

    /**
     ********************  init functions ********************
     *
     */  
     
    /**
     ********************  click event handlers ********************
     *
     */

    /**
     ********************  navigation  ********************
     *
     */

    /**
     ********************  helpers ********************
     *
     */

    /**
     ********************  initialize by mediaqueries ********************
     *
     */


    $(document).ready(function()
    {
        $('body').scrollTop(1);  
        
        $('.register-content li').hide();
        
        $(".register-sections").each(function( index ) {
            $(this).children().each(function( index ) {
                $(this).attr('data-index', index);
                if(index == 0) {
                    $(this).addClass('active');
                }
            });
        });
        
        $(".register-content").each(function( index ) {
            $(this).children().each(function( index ) {
                $(this).attr('data-index', index);
                if(index == 0) {
                    $(this).show();
                }
            });
        });
        
        $('.register-sections li').bind('click', function () { 
            var elmParent = $(this).parent();
            var elmIndex = $(this).attr('data-index');
            var elmContent = elmParent.next('.register-content');
            
            elmParent.children().removeClass('active');
            elmContent.children().hide();
            
            elmParent.children().each(function( index ) {
                if(index == elmIndex) {
                    $(this).addClass('active');
                }
            });
            
            elmContent.children().each(function( index ) {
                if(index == elmIndex) {
                    $(this).show();
                }
            });
        });
    });  
                                  
    $(window).resize(function () { 
        $('#content').css('min-height', ($(window).height()-$('header').outerHeight()));
        $('nav').css('min-height', ($(window).height()-$('header').outerHeight()));
    });     
    
    $(window).scroll(function () {  
    });
})(jQuery);
