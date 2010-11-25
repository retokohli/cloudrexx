/**
 * Handles comment-related stuff for the frontend.
 * 
 * @author severin räz
 */

/**
 * Constructor.
 * 
 * @param int articleContentId comments are on a per-content basis. id of commented content.
 */
var CommentInterface = function(articleContentId)
{
    this.articleContentId = articleContentId;
    this.target = $J('#commentDiv');
    this.template = $J('#commentTemplate'); //get user defined comment template
};

/**
 * loads article comments on first call.
 * if articles are already loaded, this toggles display.
 */
CommentInterface.prototype.loadComments = function()
{
    //div where comments are appended to
    var target = this.target;
    var template = this.template;
    var ref = this;
   
    if(target.html() == '') //div empty; no data loaded jet
    {
	//load comments
	$J.getJSON(
	    'index.php?section=knowledge&act=getComments&id='+this.articleContentId,
	    {},
	    function(data)
	    {
		var tpl = template;

		$J.each(data, function(index, commentData) {
	 	    var comment = ref.commentFromData(commentData);
		    comment.appendTo(target);
	        });
		//display comments
		target.slideDown();
	    });
    }
    else if(target.is(':visible')) //content already loaded, toggle to hidden
    {
	target.slideUp();
    }
    else //content already loaded, toggle to visible
    {
	target.slideDown();
    }
};

/**
 * Fills a comment-div with the data provided. Template is used as defined in @link CommentInterface::CommentInterface().
 * @param array data { <field> => string, ... } the data to place into the respective comment_<field>-elements.
 * @return Element the div
 */
CommentInterface.prototype.commentFromData = function(data)
{
    //clone template for current article
    var contentDiv = this.template.clone().removeAttr('id','');
    $J.each(data, function(field, value) {
	//find the element with class comment_<field> and fill it with it's values
	contentDiv.find('.comment_' + field + ':first').html(value);
    });
    return contentDiv.show();
};

/**
 * Displays a new comment after it's been succesfully sent to the server.
 * This is called from outside.
 * 
 * @param Element link the comment-link so we can remove it (no double commenting)
 * @param array data @see CommentInterface::commentFromData()
 */
CommentInterface.prototype.displayNewComment = function(link, data)
{
    var comment = this.commentFromData(data);
    comment.hide();
    link.after(comment);
    link.remove();
    comment.fadeIn('slow');
};