$J(document).ready(function(){
    if(document.all) { //This means it is IE
        if($J('#inner_banner').length != 0){
            var first_column = parseInt(document.getElementById('events_block').offsetHeight);
            var second_column = parseInt(document.getElementById('newsletter_block').offsetHeight);
            var third_column = parseInt(document.getElementById('news_block').offsetHeight);
            var maxheight = (first_column>second_column)?((first_column>third_column)?first_column:third_column):((third_column>second_column)?third_column:second_column);
            maxheight = maxheight + 'px';
            $J('#events_block').css('height',maxheight);
            $J('#newsletter_block').css('height',maxheight);
            $J('#news_block').css('height',maxheight);
        }
    }else if($J('#inner_banner').length != 0){
        first_column = parseInt($J('#events_block').css('height'));
        second_column = parseInt($J('#newsletter_block').css('height'));
        third_column = parseInt($J('#news_block').css('height'));
        maxheight = (first_column>second_column)?((first_column>third_column)?first_column:third_column):((third_column>second_column)?third_column:second_column);
        maxheight = maxheight + 'px';
        $J('#events_block').css('height',maxheight);
        $J('#newsletter_block').css('height',maxheight);
        $J('#news_block').css('height',maxheight);
    }
});