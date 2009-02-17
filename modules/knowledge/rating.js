/**
 * Constructor
 */
var Rating = function(nr, currentRate, options) 
{
    this.nr = nr;
    this.stars = 8;
    this.width = 168;
    this.starWidth = 21;
    this.starHeight = 20;
    this.rated = false;
    this.onRate = function(nr, rating) {};
    this.locked = false;
    this.bgStar = 'star_g.gif';
    this.fgStar = 'star_y.gif';
    this.starPath = 'media/';

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

        if (options.starPath) {
            this.starPath = options.starPath;
        }
    }

    this.currentSize = currentRate * (this.width / this.stars);
    var obj = $('rating'+nr);

    // add the events
    var ref = this;
    if (!this.locked) {
        obj.observe('mousemove', function(event) { ref.moving(event); });    
        obj.observe('mouseout', function(event) { ref.blur(event); });
        obj.observe('mouseover', function(event) { ref.over(event); });
        obj.observe('click', function(event) { ref.click(event); });
    }

    // ad the divs
    this.bg = document.createElement("div");
    this.overlay = document.createElement("div");
    obj.appendChild(this.bg);
    obj.appendChild(this.overlay);

    // ad the style
    obj.style.position = "relative"; 
    obj.style.width = this.width + 'px';
    obj.style.height = this.starHeight + 'px';
    obj.style.overlay = "hidden";

    this.bg.style.background = 'url('+this.starPath+this.bgStar+') repeat-x';
    this.bg.style.width = this.width+"px";
    this.bg.style.height = this.starHeight+'px';
    this.bg.style.position = "absolute";
    this.bg.style.left = '0px';
    this.bg.style.top = '0px';

    this.overlay.style.height = this.starHeight+'px';
    this.overlay.style.position = 'absolute';
    this.overlay.style.top = '0px';
    this.overlay.style.left = '0px';
    this.overlay.style.background = 'url('+this.starPath+this.fgStar+') repeat-x';
    this.overlay.style.zIndex = "2";
    this.overlay.style.width = this.currentSize+"px";

    this.overlay.show();
    this.bg.show();
}

/**
 * The moving event
 */
Rating.prototype.moving = function(event) 
{
    if (!this.rated) {
        if (window.event) {
            var x = window.event.x;
        } else {
            var x = event.layerX;
        }
        if (x <= this.width) {
            if (x <= this.starWidth/4) {
                X = 0;
            } else {
                var X = (parseInt(x / (this.starWidth/2)) + 1) * (this.starWidth/2)
            }
            this.overlay.style.width = X+'px';
        }
    }
}

/**
 * The onmouseout event
 */
Rating.prototype.blur = function(event) 
{
    if (!this.rated) {
        this.overlay.style.width = this.currentSize;
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
        if (x <= this.width) {
            if (x <= this.starWidth/4) {
                X = 0;
            } else {
                var X = (parseInt(x / (this.starWidth/2)) + 1) * (this.starWidth/2)
            }
            this.overlay.style.width = X+'px';
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
    if (x <= this.width) {
        if (x <= this.starWidth/4) {
            X = 0;
        } else {
            var X = (parseInt(x / (this.starWidth/2)) + 1) * (this.starWidth/2)
        }
        this.overlay.style.width = X+'px';
        this.currentSize = X;
        this.rated = true;
    }

    var rating = (X / this.width) * this.stars;
    this.onRate(this.nr, rating);
}
