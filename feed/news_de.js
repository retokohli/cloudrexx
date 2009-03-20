var rssFeedNews = new Array()
rssFeedNews[0] = new Array();
rssFeedNews[0]['title'] = 'Neue Website für Velok.ch';
rssFeedNews[0]['link'] = 'http://velok.ch.magellan.sui-inter.net/index.php?section=news&amp;cmd=details&amp;newsid=12&amp;teaserId=0';
rssFeedNews[0]['date'] = '03.05.2007';
rssFeedNews[1] = new Array();
rssFeedNews[1]['title'] = 'Velok AG an den Bike Days 07';
rssFeedNews[1]['link'] = 'http://velok.ch.magellan.sui-inter.net/index.php?section=news&amp;cmd=details&amp;newsid=13&amp;teaserId=4';
rssFeedNews[1]['date'] = '11.03.2007';
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