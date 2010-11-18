var Search = {
    /**
     * Time in miliseconds since 1970 when a key was pressed
     */
    keyTime : 0,
    /**
     * Time to wait until performing the search
     */
    waitTime : 100,
    /**
     * The id of the result box
     */
    resultBox : "resultbox",
    /**
     * The last request, so we can abort it
     */
    curRequest : null,
    /**
     * Assign the search to an input field
     */
    assign : function(field, resultBox)
    {
        $J('#'+field).bind('keypress', function() {
            Search.keyPress();
        });
        
        $J('#'+field).bind('blur',function() {
            Search.hideBoxDelayed();
        });
        
        this.resultBox = resultBox;
    },
    /**
     * Save the current time in milliseconds since 1970. Override
     * the existing time.
     */
    keyPress : function()
    {
        var d = new Date();    
        this.keyTime = d.getTime();
        var ref = this;
        setTimeout(function() {
            Search.timeout();
        }, this.waitTime+10);
    },
    /**
     * Compare the current time and the time saved. If it is bigger
     * than the wait time, perform the search
     */
    timeout : function()
    {
        var d = new Date();
        var actTime = d.getTime();
        if ((actTime - this.keyTime) > this.waitTime) {
            this.perform();
        }
    },
    /**
     * Perform the search
     */
    perform : function()
    {
        this.getData();
    },
    /**
     * Get the data
     */
    getData : function()
    {
        var ref = this;
        
        if (this.curRequest != null && this.curRequest.abort) {
            this.curRequest.abort();
        }
        
        this.curRequest = $J.getJSON(
            "modules/knowledge/search.php",
            {
                section : "knowledge",
                act : "liveSearch",
                searchterm : $J('#searchinput').val()
            },
            function(data)
            {
                if (data.status == 1) {
                    ref.clearBox();
                    $J('#'+ref.resultBox).html(data.content);
                    ref.showBox();
                } else {
                    ref.hideBox();
                } 
            });
    },
    clearBox : function()
    {
      $J('#'+this.resultBox).empty();
    },
    /**
     * Make the box visible
     */
    showBox : function() {
        $J('#'+this.resultBox).show();
    },
    /**
     * Hide the result box
     */
    hideBox : function() {
        $J('#'+this.resultBox).hide();
    },
    /**
     * Hide a box delayed
     *
     * Hide a box delayed. This is because the link mus be clickable.
     * Without delay the box hides before a link can be clicked.
     */
    hideBoxDelayed : function() {
        setTimeout(function() {
            Search.hideBox();
        }, 100);
   }
};


/** knowledge specific **/
function submitSearch(obj)
{
	var searchinput = $J('#searchinput');
	$J('#searchHidden').attr("value",searchinput.attr("value"));
	searchinput.attr("value","");
	searchinput.attr("name","");
	return true;
}

