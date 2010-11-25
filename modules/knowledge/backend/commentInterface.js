/**
 * This class groups functionality to maintain comments.
 * For backend use. Depends on JQuery UI.
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
    this.editBox = $J('#commentEditBox');
    this.commentTemplate = $J('#commentTemplate');
    this.deleteConfirmBox = $J('#commentDeleteConfirmBox');
};

/**
 * Localize strings.
 * 
 * @param array strings { 'TXT_DELETE' => string, 'TXT_CANCEL' => string } 
 */
CommentInterface.prototype.i18n = function(strings)
{
    this.strings = strings;
};

/**
 * Set new content id. Useful if switching language (e.g. Tabs)
 * 
 * @param int id 
 * @see CommentInterface::CommentInterface()
 */
CommentInterface.prototype.setArticleContentId = function(id)
{
    this.articleContentId = id;
};

/**
 * Loads comments and displays them in a JQuery UI dialog.
 */
CommentInterface.prototype.showCommentEditBox = function ()
{
    //could be filled from last call
    this.editBox.empty();
    if(this.articleContentId != -1) //-1 means new article, a new article won't have comments
    {
    	var ref = this;
	$J.getJSON(
	    'index.php?&cmd=knowledge&section=articles&act=getComments&id='+this.articleContentId,
	    {},
	    function(data) {
		$J.each(data, function (index, commentData) {
	            //generate a div for each comment, append it to our dialog
		    var comment = ref.commentFromData(commentData);
		    comment.appendTo(ref.editBox);
		});
		//show the dialog
		ref.editBox.dialog({ width: 400, height: 500, modal:true });
	    });
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
    var commentDiv = this.commentTemplate.clone().removeAttr('id','');
    $J.each(data, function(field, value) {
	//find the element with class comment_<field> and fill it with it's  values
	commentDiv.find('.comment_' + field + ':first').html(value);
    });

    //add delete handler
    var ref = this;
    commentDiv.find('.adminCommentDelete').bind('click', function(event) {ref.deleteComment(event); return false; });

    return commentDiv.show();
};

/**
 * Delete callback for comments.
 * 
 * @param Event event
 */
CommentInterface.prototype.deleteComment = function(event) {
    var theComment = $J(event.target).parent();
    var id = theComment.find('.comment_id').html();

    var deleteAction = function(){
	//make delete call and remove the div.
	$J.get(
	    'index.php?&cmd=knowledge&section=articles&act=delComment&id='+id,
	    {},
	    function(data) {
		theComment.remove();
	    }
	);
    };

    var deleteConfirmBox = this.deleteConfirmBox;
    var theButtons = {};
    theButtons[this.strings['TXT_DELETE']] = function() {
					  deleteAction();
					  deleteConfirmBox.dialog( "close" );
				      };
    theButtons[this.strings['TXT_CANCEL']] = function() {
					  deleteConfirmBox.dialog( "close" );
				      };


    this.deleteConfirmBox.dialog({resizable: false,
				  height:140,
				  modal: true,
				  buttons: theButtons
				 });
};