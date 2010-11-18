/**
 * Constructor
 */
var Rating = function(nr, currentRate, options) 
{	
    /* the object number suffix */
    this.nr = nr;

    /* the amount of stars */
    this.stars = 8;

    /* the width of the div (calculated with the amount of stars) */
    this.width = 168;

    /* the width of one star picture */
    this.starWidth = 21;

    /* the heigth of the star picture */
    this.starHeight = 20;

    /* if the rating is already done */
    this.rated = false;

    /* the callback function when the user rates */
    this.onRate = function(nr, rating) {};

    /* if the rating is locked */
    this.locked = false;

    /* the picture in the background (grey) */
    this.bgStar = 'star_g.gif';

    /* the picture for the foreground (yellow) */
    this.fgStar = 'star_y.gif';

    /* the picture for the star to be display after
     * rating (red) */
    this.rateStar = 'star_r.gif';

    /* the path to the star pictures */
    this.starPath = 'media/';

    /* the prefix of the element id */
    this.elemPrefix = 'rating';

    /* the data to be passed to the callback function */
    this.callbackData = {};
    

    if (options) {
        if (options.starWidth) {
            this.starWidth = options.starWidth;
        }

        if (options.starHeight) {
            this.starHeight = options.starHeight;
        }

        if (options.stars) {
            this.stars = options.stars;
            this.width = options.stars* this.starWidth;
        }

        if (options.locked) {
            this.locked = options.locked;
        }

        if (options.onRate) {
            this.onRate = options.onRate;
        }

        if (options.bgStar) {
            this.bgStar = options.bgStar;
        }

        if (options.fgStar) {
            this.fgStar = options.fgStar;
        }

        if (options.rateStar) {
            this.rateStar = options.rateStar;
        }

        if (options.starPath) {
            this.starPath = options.starPath;
        }

        if (options.elemPrefix) {
            this.elemPrefix = options.elemPrefix;
        }

        if (options.callbackData) {
            this.callbackData = options.callbackData;
        }
    }
    

    this.currentSize = currentRate * (this.width / this.stars);
    var obj = $J('#'+this.elemPrefix+nr);

    // add the events
    var ref = this;
    if (!this.locked) { 
        obj.bind('mousemove', function(event) { ref.moving(event); });    
        obj.bind('mouseout', function(event) { ref.blur(event); });
        obj.bind('mouseover', function(event) { ref.over(event); });
        obj.bind('click', function(event) { ref.click(event); });
    }
    // ad the divs
    this.bg = $J('<div></div>');
    this.fg = $J('<div></div>');
    obj.append(this.bg);
    obj.append(this.fg);
 

    // ad the style
    obj.css({
      'position': 'relative',
      'width':    this.width + 'px',
      'height':   this.starHeight + 'px',
      'overlay':  'hidden'
    });

    this.bg.css({
      'width':            this.width+"px",
      'height':           this.starHeight+'px',
      'position':         'absolute',
      'left':             '0px',
      'top':              '0px',
      'background': 'url('+this.starPath+this.bgStar+') repeat-x'
    });
    if (!this.locked) {
       this.bg.css({
         'cursor': 'pointer'
       });
    } 	
    
    this.fg.css({
      'height': this.starHeight+'px',
      'position': 'absolute',
      'top': '0px',
      'left': '0px',
      'z-index': '2',
      'width': this.currentSize+'px'
    }); 

    if (this.locked) {
      this.fg.css({
        'background': 'url('+this.starPath+this.rateStar+') repeat-x'
      });

    } else {
	this.fg.css({
	  'background': 'url('+this.starPath+this.fgStar+') repeat-x',
          'cursor': 'pointer'
        });
    }
}

/**
 * The moving event
 */
Rating.prototype.moving = function(event) 
{
    if (!this.rated) {
        if (window.event) {
            var x = window.event.offsetX;
        } else {
            var x = event.layerX;
        }
        if (x < this.width) {
            if (x <= this.starWidth/4) {
                X = 0;
            } else {
                var X = (parseInt(x / (this.starWidth/2)) + 1) * (this.starWidth/2);
            }
            this.fg.css({
              'width': X+'px'
	    });
        }
    }
}

/**
 * The onmouseout event
 */
Rating.prototype.blur = function(event) 
{
    if (!this.rated) {
        this.fg.css({
	  'width': this.currentSize+'px'
        });
    }
}

/**
 * The over event
 */
Rating.prototype.over =  function(event) 
{
    if (!this.rated) {
        if (window.event) {
            var x = window.event.x;
        } else {
            var x = event.layerX;
        }
        if (x < this.width) {
            if (x <= this.starWidth/4) {
                X = 0;
            } else {
                var X = (parseInt(x / (this.starWidth/2)) + 1) * (this.starWidth/2);
            }
            this.fg.css({
              'width': X+'px'
            });
        }
    }
}

/**
 * The click event
 */
Rating.prototype.click = function(event) 
{
    if (window.event) {
        var x = window.event.x;
    } else {
        var x = event.layerX;
    }

    if (x < this.width) {
        if (x <= this.starWidth/4) {
            X = 0;
        } else {
            var X = (parseInt(x / (this.starWidth/2)) + 1) * (this.starWidth/2);
        }
        this.fg.css({
           'width': X+'px'
        });
        this.currentSize = X;
        this.rated = true;
    }

    var rating = (X / this.width) * this.stars;
    this.onRate(this.callbackData, rating);
    this.rated = true;
    this.fg.css({
      'background': 'url('+this.starPath+this.rateStar+') repeat-x'
    });
}
