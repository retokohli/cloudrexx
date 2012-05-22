/**
 * Equal Heights Plugin
 * Equalize the heights of elements. Great for columns or any elements
 * that need to be the same size (floats, etc).
 *
 */
$J.fn.equalHeight = function () {
    var height		= 0;
    var maxHeight	= 0;
    // Store the tallest element's height
    this.each(function () {
        height		= $J(this).outerHeight();
        maxHeight	= (height > maxHeight) ? height : maxHeight;
    });
    // Set element's min-height to tallest element's height
    return this.each(function () {
        var t			= $J(this);
        var minHeight	= maxHeight - (t.outerHeight() - t.height());
        var property	= $J.browser.msie && $J.browser.version < 7 ? 'height' : 'min-height';
        t.css(property, minHeight + 'px');
    });
};