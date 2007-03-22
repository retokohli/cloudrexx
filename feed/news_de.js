var rssFeedNews = new Array()
rssFeedNews[0] = new Array();
rssFeedNews[0]['title'] = 'test meldung';
rssFeedNews[0]['link'] = 'http://dev.contrexx.org/index.php?section=news&amp;cmd=details&amp;newsid=227&amp;teaserId=0';
rssFeedNews[0]['date'] = '12.12.2006';
rssFeedNews[1] = new Array();
rssFeedNews[1]['title'] = 'changelog';
rssFeedNews[1]['link'] = 'http://dev.contrexx.org/index.php?section=news&amp;cmd=details&amp;newsid=226&amp;teaserId=';
rssFeedNews[1]['date'] = '09.11.2006';
rssFeedNews[2] = new Array();
rssFeedNews[2]['title'] = 'Redirekt-Meldung';
rssFeedNews[2]['link'] = 'http://dev.contrexx.org/index.php?section=news&amp;cmd=details&amp;newsid=224&amp;teaserId=0';
rssFeedNews[2]['date'] = '07.11.2006';
rssFeedNews[3] = new Array();
rssFeedNews[3]['title'] = 'Dies ist eine Newslmeldung';
rssFeedNews[3]['link'] = 'http://dev.contrexx.org/index.php?section=news&amp;cmd=details&amp;newsid=188&amp;teaserId=2';
rssFeedNews[3]['date'] = '20.12.2005';
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