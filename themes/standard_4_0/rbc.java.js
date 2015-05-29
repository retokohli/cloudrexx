(function ($) {
    $(document).ready(function () {
        $("#mediadir .mediadirSearchForm form > input[type='hidden']").attr('name','');
        $('#mediadir .normal .mediadirButtonSearch').prop('type','hidden');
/*/
//Navigation
/*/
    // set width of navigation as wide as the container
    var containerwidth = jQuery('.container').width();
    $('.navigation > li > ul').css("width", containerwidth);

    // add important classes to navigation for bootstrap dropdown menu
    $('.navigation li.level-1 > ul.menu').addClass('dropdown-menu dropdown-menu-large row');
    $('.navigation li.level-1.dropdown.dropdown-large > a').addClass('dropdown-toggle');

    // add important attributes to navigation for bootstrap dropwdown menu
    $('.navigation li.level-1.dropdown.dropdown-large > a').attr("data-toggle", "dropdown");
    // disable links of 2. level navigation
    $(".navigation li.level-2 > a").click(function() {
     return false;
    });  
/*/
//   Set the divs with the same (.sameheight) class name to the same height
/*/
    var baseHeight = 0;
    $("#home-boxes .sameheight").each(function(index, box) {
       var height = $(box).height();
       if (height > baseHeight)
       {
            baseHeight = height;
       }            
    });

    $('#home-boxes .sameheight').css('height', (baseHeight+10) + 'px');
/*/
//   Set the divs with the same (.sameheight) class name to the same height
/*/
    var baseHeight = 0;
    $("#mediadir .sameheight").each(function(index, box) {
       var height = $(box).height();
       if (height > baseHeight)
       {
            baseHeight = height;
       }            
    });

    $('#mediadir .sameheight').css('height', (baseHeight+10) + 'px');
/*/
//Top Searcbar
/*/
        var t = $(".top-meta-nav form.search"),
            a = $('input[name="term"]', t),
            n = $("button", t);
        $("html").click(function() {
            a.stop().animate({
                width: a.data("width"),
                "padding-left": a.data("padding-left"),
                "padding-right": a.data("padding-right")
            }, 400, "swing", function() {
                n.data("prevented", !1), a.removeClass("active")
            }), n.removeClass("active"), t.removeClass("active")
        }), t.click(function(t) {
            t.stopPropagation()
        }), n.click(function(e) {
            n.data("prevented") !== !0 && (e.preventDefault(), a.data("width", a.width()), a.data("padding-left", a.css("padding-left")), a.data("padding-right", a.css("padding-right")), t.addClass("active"), a.addClass("active"), n.addClass("active"), a.stop().animate({
                width: 120,
                "padding-left": "10px",
                "padding-right": "10px"
            }, 400, "swing", function() {
                n.data("prevented", !0), a.focus()
            }))
        })
/*/
// Sliders
/*/
    var swiper = new Swiper('.swiper-top', {
          pagination: '.swiper-pagination',
          nextButton: '.swiper-button-next',
          prevButton: '.swiper-button-prev',
          slidesPerView: 1,
          paginationClickable: true,
          spaceBetween: 0,
          autoplay: 4000,
          autoplayDisableOnInteraction: true,
          loop: true
      });
      var swiper = new Swiper('.swiper-bottom', {
          slidesPerView: 1,
          spaceBetween: 0,
          effect: 'fade',
          centeredSlides: true,
          autoplay: 7000,
          autoplayDisableOnInteraction: false,
          loop: true
      });
/*/
//Footer setup
/*/   
      var footerHeight = $('#footer').outerHeight();
      $('#content-wrapper').css('padding-bottom', footerHeight);
/*/
// Testamonial Link change
/*/  
      var a_href = jQuery('#testamonial-link').find('a').attr('href');
      jQuery(".swiper-bottom .slide-top-text a").attr("href", a_href);
/*/
// News Link change
/*/   
      var myDiv = document.getElementById("jahreszahl");

      //Create array of options to be added
      var array = [];
      jQuery('.news_archive li a').each(function(index, value){
          var multiArray = [];
          multiArray[0] = value;
          multiArray[1] = value.text;
          array.push(multiArray);
      });

      jQuery('.news_archive').empty();

      if ( $("select").length > 0 && myDiv){
          //Create and append select list
          var selectList = document.createElement("select");
          selectList.setAttribute("id", "javaSelections");
          var option = document.createElement("option");
          option.setAttribute("value", "");
          option.text = "Auswahl Monat";
          selectList.appendChild(option);
          myDiv.appendChild(selectList);

          //Create and append the options
          for (var i = 0; i < array.length; i++) {
            option = document.createElement("option");
            option.setAttribute("value", array[i][0]);
            option.text = array[i][1];
            selectList.appendChild(option);
          }  
      }

      $('#javaSelections').bind('click', function () {
        var url = $(this).val(); // get selected value
        if (url) { // require a URL
            window.location = url; // redirect
        }
        return false;
      });

      /* modal window for custom twitter share link */
      $('.popup').click(function(event) {
        var width  = 575,
            height = 400,
            left   = ($(window).width()  - width)  / 2,
            top    = ($(window).height() - height) / 2,
            url    = this.href,
            opts   = 'status=1' +
                     ',width='  + width  +
                     ',height=' + height +
                     ',top='    + top    +
                     ',left='   + left;
        
        window.open(url, 'twitter', opts);
     
        return false;
      });

      //set correct google + share link
      var url = window.location.href;
      $('.google-plus a').attr("href", "https://plus.google.com/share?url=" + url);

	//Shorten string length
	$('.newstext span').each(function(){
		$(this).text($(this).text().substring(0,130)+"...");
	 });
/*/
// Mediadir Team Search
/*/ 

    // submit form on select-change
    $( "#mediadir div.expanded select.mediadirInputfieldSearch" ).on("change", function() {
        $( "#mediadir" ).find( "form" ).submit();
    });

    // remove the labels on the expanded search-form
    $( "#mediadir div.expanded label" ).remove();

    // set standard selection content on references
    if ( $(".references").length > 0 ){
        $( "#mediadir div.expanded p:first-child select.mediadirInputfieldSearch option:first-child" ).html( "Leistungsbereich" );
        $( "#mediadir div.expanded p:last-child select.mediadirInputfieldSearch option:first-child" ).html( "Branche" );
    }

    // set standard selection content on team
    if ( $(".team").length > 0 ){
        $( "#mediadir div.expanded p:first-child select.mediadirInputfieldSearch option:first-child" ).html( "Alle Bereiche" );
    }

    // display two random divs
    if ( $(".passende-best-practice").length > 0 ){
        var random1 = Math.floor(Math.random() * $('div.bestpractice').length);
        var random2 = Math.floor(Math.random() * $('div.bestpractice').length);

        if ( random1 == random2 ) {

            while ( random1 == random2 ) {
                random2 = Math.floor(Math.random() * $('div.bestpractice').length);
            }

        }

        $('div.bestpractice').hide().eq(random1).show();
        $('div.bestpractice').eq(random2).show();
    }

  })
})(jQuery);
