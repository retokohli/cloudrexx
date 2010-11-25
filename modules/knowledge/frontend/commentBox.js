/**
 * Replaces a link with CommentBox from a preset div wherever needed.
 */
var CommentBox = function(link, commentBoxDiv, responseHandler)
{
    //clone template div & remove id to keep it unique
    this.commentBox = $J(commentBoxDiv).clone().attr('id','');
    this.theForm = this.commentBox.find('form');
   
    this.link = $J(link);
    this.responseHandler = responseHandler;
    this.init();
};

CommentBox.prototype.init = function()
{
    var href = this.link.attr('href');
    var target = '';
    var id = '';
    if(href.substr(0,9) == '#article_') //is the link pointing to an article?
    {
      	target = '&target=article';
	id = '&id=' + href.substr(9); //cutting off " #article_ "
    }
    else //link points to a comment
    {
	target = '&target=comment';
        id = '&id=' + href.substr(9); //cutting off " #comment_ "
    }

    //append our target to form action
    this.theForm.attr('action',this.theForm.attr('action') + target + id);

    //hide link & visually replace by our box
    this.link.hide();
    this.commentBox.insertAfter(this.link);
    this.commentBox.show();

    //hook comment box submit event to our class
    var ref = this;
    this.theForm.bind('submit', function(event) {
      return ref.submit(event);
    });
};

//form submission handler
CommentBox.prototype.submit = function(event)
{
    var ref = this;
    var data = formToArray(this.theForm, 'commentData');
    $J.post(this.theForm.attr('action'),
	   data, 
	   function(data) //on success
	   {
	     if(data.status == 'error') {
		 ref.showError();
	     }		 
	     else {
		 ref.close();
		 ref.responseHandler(ref.link, data.data);
	     }
	   },
	   "json"
	  );
    event.stopPropagation();
    return false;
};

CommentBox.prototype.close = function()
{
    this.commentBox.remove();
    this.link.show();
};

CommentBox.prototype.showError = function()
{
    this.commentBox.css({'background-color':'#a00'});
}