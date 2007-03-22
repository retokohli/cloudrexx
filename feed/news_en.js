var rssFeedNews = new Array()
rssFeedNews[0] = new Array();
rssFeedNews[0]['title'] = 'news1';
rssFeedNews[0]['link'] = 'http://dev.contrexx.org/index.php?section=news&amp;cmd=details&amp;newsid=213&amp;teaserId=23';
rssFeedNews[0]['date'] = '23.02.2006';
rssFeedNews[1] = new Array();
rssFeedNews[1]['title'] = 'news2';
rssFeedNews[1]['link'] = 'http://dev.contrexx.org/index.php?section=news&amp;cmd=details&amp;newsid=212&amp;teaserId=24';
rssFeedNews[1]['date'] = '23.02.2006';
rssFeedNews[2] = new Array();
rssFeedNews[2]['title'] = 'news3';
rssFeedNews[2]['link'] = 'http://dev.contrexx.org/index.php?section=news&amp;cmd=details&amp;newsid=211&amp;teaserId=25';
rssFeedNews[2]['date'] = '23.02.2006';
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