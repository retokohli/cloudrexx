(function ($) {
    $(document).ready(function () {
        $('iframe').load(function () {
            $('#partner-form').height(parseInt(this.contentWindow.document.body.offsetHeight) + 120);

        });
        $("li.no-click>a").click(function(e) {
            e.preventDefault();
        });
        //Check to see if the window is top if not then display button
        $(window).scroll(function () {
            if ($(this).scrollTop() > 100) {
                $('.scrollToTop').fadeIn();
            } else {
                $('.scrollToTop').fadeOut();
            }
        });

        //Click event to scroll to top
        $('.scrollToTop').click(function () {
            $('html, body').animate({scrollTop: 0}, 800);
            return false;
        });


        $('img').svgmagic();


        //make the header a little darker
        $(window).on('scroll', function () {
            if ($(this).scrollTop() > 100) {
                $('header').css('background-color', 'rgb(250,250,250)').css('opacity', '0.9');
            } else {
                $('header').css('background-color', 'white').css('opacity', '1.0');
            }
        });

        $('#select-language').on('click', '.active', function (e) {
            e.preventDefault();
            if ($(this).parent().hasClass('open')) {
                $(this).siblings().hide();
                $(this).parent().removeClass('open');
            } else {
                $(this).siblings().css('display', 'block');
                $(this).parent().addClass('open');
            }
        });

        if ($('#select-language a:not(.active)').length > 0) {
            var activeLanguage = $('#select-language .active');
            var firstNonActive = $('#select-language a:not(.active)').eq(0);
            activeLanguage.insertBefore(firstNonActive);
        }

        $('#login-mask .close-button').on('click', function (e) {
            e.preventDefault();
            $('#login-mask').hide();
            $('body').removeClass('login-mask');
        });

        $('.show-login').on('click', function (e) {
                e.preventDefault();
                $('#login-mask').fadeIn(400, function (){}
                );
                $('body').addClass('login-mask');
            });

            if ($('#content-home').length > 0) {
                $('#content-home').css('height', $(window).height());
            }

            if ($('#content-customerpanel').length > 0) {
                $('#content-customerpanel').css('height', $(window).height());
            }

//        if($('.home section').length>0){
//            $('.home section').css('min-height',$( window ).height());
//        }

//        if($('#references .contentLead').length>0){
//            $('#references .contentLead').css('min-height',$( window ).height()/3);
//        }


            $(window).on('resize', function () {
                if ($('#content-home').length > 0) {
                    $('#content-home').css('height', $(window).height());
                }

//            if($('.home section').length>0){
//                $('.home section').css('min-height',$( window ).height());
//            }

//            if($('#references .contentLead').length>0){
//                $('#references .contentLead').css('min-height',$( window ).height()/3);
//            }


                if ($('#content-slider').length > 0) {
                    repositionContentSliderImage();
                }
            });




            $('#home-scroll-down').on('click', function (e) {
                e.preventDefault();
                var n = $(window).height() - 59;
                $('html, body').animate({scrollTop: n}, 400);
            });

            repositionContentSliderImage();

            $('.features-details').hide();
            $('.tell-me-more').on('click', function (e) {
                e.preventDefault();
                $(this).next().toggle(400);
                $(this).hasClass('open') ? $(this).removeClass('open') : $(this).addClass('open');
            });

            $('.tell-me-more').on('click', function (e) {
                e.preventDefault();
            });

            $('.toggle-features').on('click', function (e) {
                e.preventDefault();

                element = $(this).prev();

                if ($(this).parent().hasClass('open')) {
                    //element.animate({height: element.data('initialHeight')});
                    $('.features').animate({height: element.data('initialHeight')});
                    //$(this).parent().removeClass('open');
                    //$(this).html('&#xf13a;');
                    $('.prices > div').removeClass('open');
                    $('.toggle-features').html('&#xf13a;');
                } else {
                    currentHeight = element.height();
                    fullHeight = element.css('height', 'auto').height();
                    //element.css('height', currentHeight).animate({height: fullHeight});
                    $('.features').css('height', currentHeight).animate({height: fullHeight});
                    //$(this).parent().addClass('open');
                    $('.prices > div').addClass('open');
//                element.data('initialHeight', currentHeight);
                    $('.features').data('initialHeight', currentHeight);
                    //$(this).html('&#xf139;');
                    $('.toggle-features').html('&#xf139;');
                }
            });

            $('.prices-periods a').on('click', function (e) {
                e.preventDefault();
                $('.prices-periods a').removeClass('active');
                $(this).addClass('active');
                if ($(this).hasClass('monthly')) {
                    $('.monthly-prices').show();
                    $('.annual-prices').hide();
                    $('.biannual-prices').hide();
                } else if ($(this).hasClass('annually')) {
                    $('.monthly-prices').hide();
                    $('.annual-prices').show();
                    $('.biannual-prices').hide();
                } else if ($(this).hasClass('biannually')) {
                    $('.monthly-prices').hide();
                    $('.annual-prices').hide();
                    $('.biannual-prices').show();
                }
            });

            /*SMOOTH SCROLLING START*/
            /*$('a[href*=#]:not([href=#])').click(function() {
             if (location.pathname.replace(/^\//,'') == this.pathname.replace(/^\//,'') && location.hostname == this.hostname) {
             var target = $(this.hash);
             target = target.length ? target : $('[name=' + this.hash.slice(1) +']');
             if (target.length) {
             $('html,body').animate({
             scrollTop: target.offset().top
             }, 1000);
             return false;
             }
             }
             });*/
            /*SMOOTH SCROLLING END*/

        });

        if ($('#contact-google-maps').length > 0) {


            var mapOptions =
                    {
                        zoom: 5,
                        center: new google.maps.LatLng(48.050038, 8.58929)

                    };

            // markers
            var hauptsitz = new google.maps.Marker({
                position: new google.maps.LatLng(46.761453, 7.629617),
                title: "Comvation AG - web development competence - Hauptsitz"
            });
            var niederlassung = new google.maps.Marker({
                position: new google.maps.LatLng(47.383398, 8.495548),
                title: "Comvation AG - web development competence - Niederlassung"
            });


            var map = new google.maps.Map(document.getElementById("contact-google-maps"),
                    mapOptions);



            hauptsitz.setMap(map);
            niederlassung.setMap(map);

        }

        if (/Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(navigator.userAgent) == false) {
            $.stellar();
        }

        // Multisite - connect sign-up-form on homepage with sign-up-modal
        jQuery('#home-sign-up').submit(function () {

            if (typeof (cx_multisite_options) == 'undefined') {
                cx_multisite_options = {};
            }
            cx_multisite_options.address = jQuery('#home-sign-up').find('input[name=multisite_address]').val();
            jQuery('#multiSiteSignUp').modal({remote: 'https://admin.cloudrexx.com/api/MultiSite/Signup'});
            return false;
        });

        function repositionContentSliderImage() {
            var negativeMargin;
            var windowWidth = jQuery(window).width()
            var imageWidth = $('#content-slider img').eq(0).width();
            if (imageWidth > windowWidth) {
                if (windowWidth > imageWidth / 2) {
                    negativeMargin = windowWidth;
                } else {
                    negativeMargin = imageWidth / 2;
                }
                $('#content-slider img').css({'left': '50%', 'margin-left': '-' + negativeMargin + 'px'});
            }
        }
        $('#annually').click(
                function (e) {
                    $('.description > div').css('display', 'none');
                    $('#cloud.prices-new > h2').css('display', 'none');
                    if ($('input[name=curreny]:checked').attr('id') == 'euro') {
                        $('.annual-prices.euro').css('display', 'block');
                    } else {
                        $('.annual-prices.chf').css('display', 'block');
                    }
                }
        );
        $('#monthly').click(
                function (e) {
                    $('.description > div').css('display', 'none');
                    $('#cloud.prices-new > h2').css('display', 'none');
                    if ($('input[name=curreny]:checked').attr('id') == 'euro') {
                        $('.monthly-prices.euro').css('display', 'block');
                    } else {
                        $('.monthly-prices.chf').css('display', 'block');
                    }
                }
        );
        $('#biannually').click(
                function (e) {
                    $('.description > div').css('display', 'none');
                    $('#cloud.prices-new > h2').css('display', 'none');
                    if ($('input[name=curreny]:checked').attr('id') == 'euro') {
                        $('.biannual-prices.euro').css('display', 'block');
                    } else {
                        $('.biannual-prices.chf').css('display', 'block');
                    }
                }
        );
        $('#chf').click(
                function (e) {
                    $('.description > div').css('display', 'none');
                    $('#cloud.prices-new > h2').css('display', 'none');
                    if ($('input[name=period]:checked').attr('id') == 'monthly') {
                        $('.monthly-prices.chf').css('display', 'block');
                    } else if ($('input[name=period]:checked').attr('id') == 'annually') {
                        $('.annual-prices.chf').css('display', 'block');
                    } else {
                        $('.biannual-prices.chf').css('display', 'block');
                    }
                }
        );
        $('#euro').click(
                function (e) {
                    $('.description > div').css('display', 'none');
                    $('#cloud.prices-new > h2').css('display', 'none');
                    if ($('input[name=period]:checked').attr('id') == 'monthly') {
                        $('.monthly-prices.euro').css('display', 'block');
                    } else if ($('input[name=period]:checked').attr('id') == 'annually') {
                        console.log('here');
                        $('.annual-prices.euro').css('display', 'block');
                    } else {
                        $('.biannual-prices.euro').css('display', 'block');
                    }
                }
        );
        $(".chf").prop("checked", true);
        $('.more-informations').click(function(){
            if($(this).next('p').css('display') == 'none'){
                $(this).next('p').css('display', 'block');
                $(this).html('Weniger anzeigen <i class="fa fa-angle-up"></i>');
            }else{
                $(this).next('p').css('display', 'none');
                $(this).html('Mehr Infos <i class="fa fa-angle-down"></i>');
            }
        });
        $('.more-informations').each(function(index){
            if($(this).next('p').text().match(/\w/g) == null){
                $(this).css('display','none');
            }
        });
        if($('#downloads').height() == 0){
            $('#content-slider').css('margin-bottom', '1px');
        }
        if($('.category').length == 1){
            $('.category').addClass('single-category');
        }
        
        // modal window for users which are redirected from contrexx.com
        if(window.location.hash == '#contrexx'){
            if(getCookie('modal') != 'hide'){
                jQuery('#contrexx-modal').css('display', 'block');
                jQuery('body').prepend('<div class="modal-backdrop fade in"></div>');
            }
            // remove hash out of url because it looks better
            history.pushState("", document.title, window.location.pathname + window.location.search);
            // set cookie, so they get the message only once
            document.cookie ="modal=hide";
        }
        jQuery('.close').click(function(){
            jQuery('#contrexx-modal').css('display', 'none');
            jQuery('.modal-backdrop').remove();
            // remove hash out of url because it looks better
            history.pushState("", document.title, window.location.pathname + window.location.search);
        });
        function getCookie(name) {
          var value = "; " + document.cookie;
          var parts = value.split("; " + name + "=");
          if (parts.length == 2) return parts.pop().split(";").shift();
        }
        if(jQuery('#subcategories').length == 0 && jQuery('#download_entries').length == 1 ){
           jQuery('#download_entries').addClass('entry_only');
        }
        if(jQuery('#download_entries > h2').text() == "Kostenlose Webdesign Templates" || jQuery('.content-block > h2:first-child').text() == 'Installationspakete'){
         jQuery('#download_entries > h2').remove();
         jQuery('#backToOverview').remove();
        }
    })(jQuery)
