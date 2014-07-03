if (document.body) {
	document.write('<div id="news_rss_feeds"></div>');
}
fnWinOnload = window.onload;
window.onload = function() {
    if (typeof(fnWinOnload) != 'undefined' && fnWinOnload != null) {
        fnWinOnload();
    }

    var rssFeedNews = new Array();rssFeedNews[0] = new Array();
rssFeedNews[0]['title'] = 'Neue Webseite online';
rssFeedNews[0]['link'] = 'http://pkg.contrexxlabs.com/de/index.php?section=news&amp;cmd=details&amp;newsid=1&amp;teaserId=';
rssFeedNews[0]['date'] = '27.09.2012';
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

    rssFeedContainer = document.getElementById('news_rss_feeds');
    rssFeedContainer.innerHTML = '';

var rssFeedNewsDate = "";
for (nr = 0; nr < rssFeedLimit; nr++) {
    if (rssFeedShowDate) {
        rssFeedNewsDate = rssFeedNews[nr]['date'];
    }
        rssCode = '<a href="'+rssFeedNews[nr]['link']+'" '+rssFeedTarget+' '+style+'>'+rssFeedNewsDate+' '+rssFeedNews[nr]['title']+'</a><br />';
        rssFeedContainer.innerHTML += rssCode;
    }
}