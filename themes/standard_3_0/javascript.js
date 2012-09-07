(function ($) {
    $(document).ready(function () {
        /* Navigation Start */
        $('#navigation > li').hover(function () {
            $(this).children('ul').animate({
                height:  'toggle',
                opacity: 'toggle'
            }, 150);
        }, function () {
            $(this).children('ul').hide();
        });
        
        $('#navigation li.level-1:first').addClass('first');
        $('#navigation li.level-1:last').addClass('last');
        $('#navigation li.level-1').children('ul').each(function () {
            $(this).children('li:last').addClass('last');
        });
        $('#navigation li.active').parents('ul.menu').siblings('a').removeClass('starter_normal').addClass('starter_active').parent().removeClass('starter_normal').addClass('starter_active');
        $('#subnavigation li.active').parents('ul.menu').siblings('a').removeClass('inactive').addClass('active').parent().removeClass('inactive').addClass('active');
        /* Navigation End */
        
        /* Cycle Start */
        $('#cycle').wrap(
            '<div id="cycle-wrapper" />'
        ).after(
            '<div class="cycle-button" id="cycle-prev" />' +
            '<div class="cycle-button" id="cycle-next" />' +
            '<div id="cycle-nav" />'
        ).cycle({ 
            fx:      'fade', 
            speed:    1000, 
            timeout:  0,
            next:    '#cycle-next', 
            prev:    '#cycle-prev',
            pager:   '#cycle-nav'
        });
        
        $('#cycle-nav a').empty();
        
        $('#cycle-wrapper').hover(function (e) {
            $('#cycle-prev').stop(true, true).hide().css('left', '-35px').animate({
                left:    '+=45',
                opacity: 'toggle'
            }, 100);
            $('#cycle-next').stop(true, true).hide().css('right', '-35px').animate({
                right:   '+=45',
                opacity: 'toggle'
            }, 100);
        }, function (e) {
            $('#cycle-prev').stop(true, true).delay(500).animate({
                left:    '-=45',
                opacity: 'toggle'
            }, 100);
            $('#cycle-next').stop(true, true).delay(500).animate({
                right:   '-=45',
                opacity: 'toggle'
            }, 100);
        });
        /* Cycle End */
        
        /* Scroll Start */
        $(window).scroll(function () {
            if ($(this).scrollTop() > 100) {
                $('#back-top').fadeIn();
            } else {
                $('#back-top').fadeOut();
            }
        });
        
        $('#back-top').click(function () {
            $('html, body').animate({
                scrollTop: 0
            }, 800);
            return false;
        });
        /* Scroll End */
    });
})(jQuery);