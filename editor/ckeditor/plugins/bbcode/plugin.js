/**
 * @file bbcode plugin
 * 2009 sj, Comvation AG
 */

(function(){
    CKEDITOR.plugins.add('bbcode', {
        requires : [ 'htmlwriter' ],
    	init : function(editor){
    		var pluginName = 'bbcode';
    		var match = [];
        	var dataProcessor = editor.dataProcessor;
        	dataProcessor.toHtml = function (data, fixForBody) {
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

        	    var div = document.createElement("div");
        	    div.innerHTML = "a" + data;
        	    data = div.innerHTML.substr(1);
        	    var fragment = CKEDITOR.htmlParser.fragment.fromHtml(data, fixForBody)
    	        var writer = new (CKEDITOR.htmlParser.basicWriter);
    	        fragment.writeHtml(writer, this.dataFilter);
    	        return writer.getHtml(true);
        	}

        	dataProcessor.toDataFormat = function (data, fixForBody) {
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

        	    var writer = this.writer;
        	    var fragment = CKEDITOR.htmlParser.fragment.fromHtml(data, fixForBody);
        	    writer.reset();
        	    fragment.writeHtml(writer, this.htmlFilter);
        	    return writer.getHtml(true);
        	}

    	},
    });
})();