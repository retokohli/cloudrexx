(function($) {
    $(document).ready(function() {
        $jq11('.carousel').carousel({
            interval: 2000
        });
        $jq11('.header').hover(function(){
            $jq11(this).css('opacity','1');
        }, function(){
            $jq11(this).css('opacity','0.6');
        });
        $('.btn-navbar').toggle(function() {
            $('section, footer').animate({right: '100%'}, 10);
            $('.mobile-nav').css('visibility','visible');
        }, function() {$('section, footer').animate({right: '0'}, 10);
            $('.mobile-nav').css('visibility','hidden');
        });
        $('.profile.networks .btn-google').removeClass('btn-google').addClass('btn-google-plus');
        $('.profile.networks .fa-google').removeClass('fa-google').addClass('fa-google-plus');
        $('.profile.networks .fa-facebook').removeClass('fa-facebook').addClass('fa-facebook-square');
        /* Metanavigation Start */
        if ($('#select-language a').length > 1) {
            var activeLanguage = $('#select-language a.active');
            activeLanguage.remove();
            $('#select-language a:not(\'.active\')').slice(0, 1).before(activeLanguage);
            $('#select-language a.active').click(function() {
                $('#select-language').toggleClass('active');
                $(':not(\'#select-language a\')').click(function() {
                    $('#select-language').toggleClass('active');
                    $(this).unbind('click');
                });
                return false;
            });
        } else {
            $('#select-language a').addClass('alone');
        }
        if ($('#metanavigation .login-toggle').length > 0) {
            var hideTimeout = null;
            $('#metanavigation .login-toggle').children('a').click(function(e) {
                e.preventDefault();
                if ($(this).next('div').css('display') == 'none') {
                    $(this).addClass('active');
                    $(this).next('div').show();
                } else {
                    $(this).removeClass('active');
                    $(this).next('div').hide();
                }
            });
            if ($('#metanavigation .login-toggle').children('div').hover(function() {
                clearTimeout(hideTimeout);
            }, function() {
                hideTimeout = setTimeout(function() {
                    $('#metanavigation .login-toggle').children('a').removeClass('active');
                    $('#metanavigation .login-toggle').children('div').hide();
                }, 2000);
            }))
                ;
        }
        /* Metanavigation End */
        /* Navigation Start */
        $('.navigation > li').hover(function() {
            $(this).children('ul').animate({
                height: 'toggle',
                opacity: 'toggle'
            }, 150);
        }, function() {
            $(this).children('ul').hide();
        });
        $('.navigation li.level-1:first').addClass('first');
        $('.navigation li.level-1:last').addClass('last');
        $('.navigation li.level-1').children('ul').each(function() {
            $(this).children('li:last').addClass('last');
        });
        $('.navigation li.active').parents('ul.menu').siblings('a').removeClass('starter_normal').addClass('starter_active').parent().removeClass('starter_normal').addClass('starter_active');
        if ($('#subnavigation li.level-2').length == 0) {
            $('#subnavigation').hide();
        }
        $('#subnavigation li.active').parents('ul.menu').siblings('a').removeClass('inactive').addClass('active').parent().removeClass('inactive').addClass('active');
        if ($('#subnavigation li.level-2:visible:last ul:visible').length == 0) {
            $('#subnavigation li.level-2 > a:visible:last').addClass('no-border');
        }
        if ($('#subnavigation li:visible:last').hasClass('level-3')) {
            $('#subnavigation li:visible:last').parent().addClass('no-border');
        }
        if ($('#subnavigation li:visible:last').hasClass('level-4')) {
            $('#subnavigation li:visible:last').parent().parent().parent().addClass('no-border');
        }
        /* Navigation End */
        /* Shop Start */
        $('#shop-categories li a:last').addClass('last');
        $('#shop-currencies a:last').addClass('last');
        /* Shop End */
        /* Cycle Start */
        if ($('#cycle').length > 0) {
            $('#cycle').after(
                    '<div class="cycle-button" id="cycle-prev" />' +
                    '<div class="cycle-button" id="cycle-next" />' +
                    '<div id="cycle-nav" />'
                    ).cycle({
                fx: 'fade',
                speed: 1000,
                timeout: 0,
                next: '#cycle-next',
                prev: '#cycle-prev',
                pager: '#cycle-nav'
            });
            $('#cycle-nav a').empty();
            $('#cycle-wrapper').hover(function(e) {
                $('#cycle-prev').stop(true, true).hide().css('left', '-35px').animate({
                    left: '+=45',
                    opacity: 'toggle'
                }, 100);
                $('#cycle-next').stop(true, true).hide().css('right', '-35px').animate({
                    right: '+=45',
                    opacity: 'toggle'
                }, 100);
            }, function(e) {
                $('#cycle-prev').stop(true, true).delay(500).animate({
                    left: '-=45',
                    opacity: 'toggle'
                }, 100);
                $('#cycle-next').stop(true, true).delay(500).animate({
                    right: '-=45',
                    opacity: 'toggle'
                }, 100);
            });
        }
        /* Cycle End */
        /* Scroll Start */
        $(window).scroll(function() {
            if ($(this).scrollTop() > 100) {
                $('#back-top').fadeIn();
                $('.header').css('opacity', '0.6');
            } else {
                $('#back-top').fadeOut();
                $('.header').css('opacity', '1');
            }
        });
        $('#back-top').click(function() {
            $('html, body').animate({
                scrollTop: 0
            }, 400);
            return false;
        });
        $(".login-toggle a.open").click(function() {
            $('.login-toggle').removeClass('open');
            $('.login-toggle').addClass('open');
            $(".login").slideDown();
            return false;
        });
        $(".login-toggle a.hidetext").click(function() {
            $('.login-toggle').removeClass('open');
            $(".login").slideUp();
            return false;
        });
    });
})(jQuery);
