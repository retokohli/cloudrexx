/*
 * FCKeditor - The text editor for Internet - http://www.fckeditor.net
 * Copyright (C) 2003-2009 Frederico Caldeira Knabben
 *
 * == BEGIN LICENSE ==
 *
 * Licensed under the terms of any of the following licenses at your
 * choice:
 *
 *  - GNU General Public License Version 2 or later (the "GPL")
 *    http://www.gnu.org/licenses/gpl.html
 *
 *  - GNU Lesser General Public License Version 2.1 or later (the "LGPL")
 *    http://www.gnu.org/licenses/lgpl.html
 *
 *  - Mozilla Public License Version 1.1 or later (the "MPL")
 *    http://www.mozilla.org/MPL/MPL-1.1.html
 *
 * == END LICENSE ==
 *
 * This is a sample implementation for a custom Data Processor for basic BBCode.
 */

FCK.DataProcessor =
{
	/*
	 * Returns a string representing the HTML format of "data". The returned
	 * value will be loaded in the editor.
	 * The HTML must be from <html> to </html>, eventually including
	 * the DOCTYPE.
	 *     @param {String} data The data to be converted in the
	 *            DataProcessor specific format.
	 */
	ConvertToHtml : function( data )
	{
		// Convert < and > to their HTML entities.
        data = data.replace( /</ig, '&lt;' ) ;
        data = data.replace( />/ig, '&gt;' ) ;

        // Convert line breaks to <br>.
        data = data.replace( /(?:\r\n|\n|\r)/g, '<br />' ) ;

     	// [code]
		while(data.match(/\[code\](.+?)\[\/code\]/gi)){
        	data = data.replace( /\[code\](.+?)\[\/code\]/gi, 'Code:<br /><div class="code">$1</div><br />' ) ;
		}

        // [quote]
        while(data.match(/\[quote=([^\]]+)\](.+?)\[\/quote\]/gi)){
        	data = data.replace( /\[quote=([^\]]+)](.+?)\[\/quote\]/gi, '<span class="quote_from">quote $1:</span><br /><div class="quote">$2</div><br />' ) ;
		}

		while(data.match(/\[quote\](.+?)\[\/quote\]/gi)){
        	data = data.replace( /\[quote\](.+?)\[\/quote\]/gi, '<span class="quote_from">quote:</span><br /><div class="quote">$1</div><br />' ) ;
		}

        // [url]
        data = data.replace( /\[url=([^\]]+)](.+?)\[\/url\]/gi, '<a title="user-posted link" href="$1">$2</a>' ) ;
        data = data.replace( /\[url\](.+?)\[\/url\]/gi, '<a title="user-posted link" href="$1">$1</a>' ) ;

        // [img]
        data = data.replace( /\[img w=([0-9]+).+?h=([0-9]+).*?\](.+?)\[\/img]/gi, '<img src="$3" height="$2" width="$1" alt="user-posted image" border="0" />' ) ;
        data = data.replace( /\[img h=([0-9]+).+?w=([0-9]+).*?\](.+?)\[\/img]/gi, '<img src="$3" height="$1" width="$2" alt="user-posted image" border="0" />' ) ;
        data = data.replace( /\[img\](.+?)\[\/img]/gi, '<img src="$1" alt="user-posted image" border="0" />' ) ;

        // [b]
        data = data.replace( /\[b\](.+?)\[\/b\]/gi, '<b>$1</b>' ) ;

        // [s]
        data = data.replace( /\[s\](.+?)\[\/s\]/gi, '<strike>$1</strike>' ) ;

        // [i]
        data = data.replace( /\[i\](.+?)\[\/i\]/gi, '<i>$1</i>' ) ;

        // [u]
        data = data.replace( /\[u\](.+?)\[\/u\]/gi, '<u>$1</u>' ) ;

		return '<html><head><title></title></head><body>' + data + '</body></html>' ;
	},

	/*
	 * Converts a DOM (sub-)tree to a string in the data format.
	 *     @param {Object} rootNode The node that contains the DOM tree to be
	 *            converted to the data format.
	 *     @param {Boolean} excludeRoot Indicates that the root node must not
	 *            be included in the conversion, only its children.
	 *     @param {Boolean} format Indicates that the data must be formatted
	 *            for human reading. Not all Data Processors may provide it.
	 */
	ConvertToDataFormat : function( rootNode, excludeRoot, ignoreIfEmptyParagraph, format )
	{
  	var data = rootNode.innerHTML ;
		// Convert <br> to line breaks.
		data = data.replace( /<br(?=[ \/>]).*?>/gi, '\r\n') ;

	    // [code]
	    data = data.replace( /Code:[\r\n]*?<div .*?class="code".*?>([\s\S]*?)<\/div>\r\n/gi, '[code]$1[/code]') ;
	    // [quote]
		while(data.match(/<span .*?class="quote_from">quote:<\/span>\r\n*?<div .*?class="quote">([^#]*?)<\/div>\r\n/gi)){
        	data = data.replace( /<span .*?class="quote_from">quote:<\/span>\r\n*?<div .*?class="quote">([^#]*?)<\/div>\r\n/gi, '[quote]$1[/quote]');
		}
		while(data.match(/<span .*?class="quote_from">quote ([0-9a-zA-Z_ ]+):.*?<\/span>\r\n*?<div .*?class="quote">([\s\S]*?)<\/div>\r\n/gi)){
	    	data = data.replace( /<span .*?class="quote_from">quote ([0-9a-zA-Z_ ]+):.*?<\/span>[\s\S]*?<div .*?class="quote">([\s\S]*?)<\/div>\r\n/gi, '[quote=$1]$2[/quote]') ;
		}

		// [url]
		data = data.replace( /<a .*?href=(["'])(.+?)\1.*?>(.+?)<\/a>/gi, '[url=$2]$3[/url]') ; /*"*/

		// [img]
		data = data.replace( /<img .*?src=["']?([^"]+)["']? .*?height=["']?([0-9]+)["']? .*?width=["']?([0-9]+)["']?.*?>/gi, '[img h=$2 w=$3]$1[/img]') ;
		data = data.replace( /<img .*?height=["']?([0-9]+)["']? .*?src=["']?([^"]+)["']? .*?width=["']?([0-9]+)["']?.*?>/gi, '[img h=$1 w=$3]$2[/img]') ;

		data = data.replace( /<img .*?src=["']?([^"]+)["']? .*?style=["']?width:([0-9]+)px.*?;.*?height:([0-9]+)px.*?;.*?["']?.*?>/gi, '[img h=$3 w=$2]$1[/img]') ;
		data = data.replace( /<img .*?style=["']?width:.*?([0-9]+)px.*?;.*?height:.*?([0-9]+)px.*?src=["']?([^"]+)["']?.*?>/gi, '[img h=$2 w=$1]$3[/img]') ;

	  	data = data.replace( /<img .*?src=["']?(.+?)["']?.*?>/gi, '[img]$1[/img]') ; /*'"*/

		// [b]
		data = data.replace( /<(?:b|strong)>/gi, '[b]') ;
		data = data.replace( /<\/(?:b|strong)>/gi, '[/b]') ;

		// [i]
		data = data.replace( /<(?:i|em)>/gi, '[i]') ;
		data = data.replace( /<\/(?:i|em)>/gi, '[/i]') ;

		// [s]
		data = data.replace( /<strike>/gi, '[s]') ;
		data = data.replace( /<\/strike>/gi, '[/s]') ;

		// [u]
		data = data.replace( /<u>/gi, '[u]') ;
		data = data.replace( /<\/u>/gi, '[/u]') ;

		// Remove remaining tags.
		data = data.replace( /<[^>]+>/g, '') ;

		return data ;
	},

	/*
	 * Makes any necessary changes to a piece of HTML for insertion in the
	 * editor selection position.
	 *     @param {String} html The HTML to be fixed.
	 */
	FixHtml : function( html )
	{
		return html ;
	}
} ;

FCKConfig.EditorAreaStyles =
".quote {\
		border : 1px solid #7390AF;\
		padding: 3px;\
}";

// This Data Processor doesn't support <p>, so let's use <br>.
FCKConfig.EnterMode = 'br' ;

// To avoid pasting invalid markup (which is discarded in any case), let's
// force pasting to plain text.
FCKConfig.ForcePasteAsPlainText	= true ;

// Rename the "Source" buttom to "BBCode".
FCKToolbarItems.RegisterItem( 'Source', new FCKToolbarButton( 'Source', 'BBCode', null, FCK_TOOLBARITEM_ICONTEXT, true, true, 1 ) ) ;
FCKToolbarItems.RegisterItem( 'Quote', new FCKToolbarButton( 'Source', 'BBCode', null, FCK_TOOLBARITEM_ICONTEXT, true, true, 1 ) ) ;

// Let's enforce the toolbar to the limits of this Data Processor. A custom
// toolbar set may be defined in the configuration file with more or less entries.
FCKConfig.ToolbarSets["Default"] = [
	['Source'],
	['Bold','Italic','Underline','StrikeThrough','-','Link','Unlink','Image', 'SpecialChar'],
] ;
