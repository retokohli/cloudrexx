if (document.body) {
	document.write('<div id="news_js_rss_feed"></div>');
}
fnWinOnload = window.onload;
window.onload = function() {
    if (typeof(fnWinOnload) != 'undefined' && fnWinOnload != null) {
        fnWinOnload();
    }

    var rssFeedNews = new Array();rssFeedNews[0] = new Array();
rssFeedNews[0]['title'] = 'Neue Webseite mit modernster Technologie online! ';
rssFeedNews[0]['link'] = 'http://localhost/index.php?section=news&amp;cmd=details&amp;newsid=1&amp;teaserId=';
rssFeedNews[0]['date'] = '05.05.2008';
if (typeof rssFeedFontColor != "string") {
    rssFeedFontColor = "";
} else {
    rssFeedFontColor = "color:"+rssFeedFontColor+";";
}
if (typeof rssFeedFontSize != "number") {
    rssFeedFontSize = "";
} else {
    rssFeedFontSize = "font-size:"+rssFeedFontSize+";";
}
if (typeof rssFeedTarget != "string") {
    rssFeedTarget = "target=\"_blank\"";;
} else {
    rssFeedTarget = "target=\""+rssFeedTarget+"\"";
}
if (typeof rssFeedFont != "string") {
    rssFeedFont = "";
} else {
    rssFeedFont = "font-family:"+rssFeedFont+";";
}
if (typeof rssFeedShowDate != "boolean") {
    rssFeedShowDate = false;
}

if (typeof rssFeedFontColor == "string" || typeof rssFeedFontSize != "number" || typeof rssFeedFont != "string") {
    style = 'style="'+rssFeedFontColor+rssFeedFontSize+rssFeedFont+'"';
}

if (typeof rssFeedLimit != 'number') {
    rssFeedLimit = 10;
}
if (rssFeedNews.length < rssFeedLimit) {
    rssFeedLimit = rssFeedNews.length;
}


var rssFeedNewsDate = "";
for (nr = 0; nr < rssFeedLimit; nr++) {
    if (rssFeedShowDate) {
        rssFeedNewsDate = rssFeedNews[nr]['date'];
    }
    document.write('<a href="'+rssFeedNews[nr]['link']+'" '+rssFeedTarget+' '+style+'>'+rssFeedNewsDate+' '+rssFeedNews[nr]['title']+'</a><br />');
}