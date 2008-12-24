var allSlider = new Hash();

/** TODO
 this is not very beautiful. it should be more like the ajax
 requester class in prototype which can get an unknown number
 of arguments
 
 arguments are:
    id:         id of the element which is to be slided
    counter:    sometimes a counter is needed to distinguish the items
                so that there is not the same id twice
    slideAll:   if this is true, only one item can be open. The others close
                when opening an item
    divPrefix:  the id prefix of the block which slides (the id and counter will
                be appended to that)
    imgPrefix:  the id prefix of the image which toggles the effect
    titleRowPrefix: the id prefix of the title row, where the image to fire the
                event off lies, so it can change its class. Can be left empty
    className:  mentioned class
    openedIcon:   the icon that shall appear when the block is open...
    closedIcon:  ... and when it's closed
 */
var Slider = function(id, counter, slideAll, divPrefix, imgPrefix, titleRowPrefix, className, openedIcon, closedIcon)
{
    this.divPrefix = divPrefix;
    this.imgPrefix = imgPrefix;
    this.slideAll = slideAll;
    this.titleRowPrefix = titleRowPrefix;
	this.opened = false;
	this.id = id+counter;
	this.blankId = id;
	this.row = $(this.titleRowPrefix+this.id);
	this.className = className;
	allSlider.set(this.id, this);
	this.slideDuration = 0.5;
	
	this.openedIcon = openedIcon;
	this.closedIcon = closedIcon;
}

Slider.prototype.toggle = function()
{
	if (this.opened) {
		this.close();
	} else {
		this.open();
	}
}

Slider.prototype.open = function()
{
	if (!this.opened) {    
	    Effect.SlideDown(this.divPrefix+this.id, {duration : this.slideDuration});
		this.opened = true;
		if (this.row != null) {
		  this.row.addClassName(this.className);
		}
		$(this.imgPrefix+this.id).src = this.openedIcon;
	}
	if (this.slideAll) {
	   this.closeOthers();
	}
}

Slider.prototype.close = function()
{
	if (this.opened) {
		Effect.SlideUp(this.divPrefix+this.id, {duration : this.slideDuration});
		this.opened = false;
		if (this.row != null) {
		  this.row.removeClassName(this.className);
		}
		$(this.imgPrefix+this.id).src = this.closedIcon;
	}
}

/*
	Effect.toggle('answer_'+this.id, 'slide');
	if (this.open) {
		this.row.removeClassName(this.className);
		this.open = false;
	} else {
		this.row.addClassName(this.className);
		this.open = true;
	}
	
	this.hit();
}
*/

Slider.prototype.closeOthers = function()
{
	var ref = this;
	allSlider.each(function(pair) {
		if (ref.id != pair.key) {
		    pair.value.close();
		}
	});
}