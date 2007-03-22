var rssFeedNews = new Array()
rssFeedNews[0] = new Array();
rssFeedNews[0]['title'] = 'Neues Webeangebot';
rssFeedNews[0]['link'] = 'http://contrexx.itsicherheit.ch/index.php?section=news&amp;cmd=details&amp;newsid=198';
rssFeedNews[0]['date'] = '07.02.2006';
rssFeedNews[1] = new Array();
rssFeedNews[1]['title'] = 'beo.ch wird immer gr&ouml;sser';
rssFeedNews[1]['link'] = 'http://contrexx.itsicherheit.ch/index.php?section=news&amp;cmd=details&amp;newsid=197';
rssFeedNews[1]['date'] = '07.02.2006';
rssFeedNews[2] = new Array();
rssFeedNews[2]['title'] = 'Google eingebunden';
rssFeedNews[2]['link'] = 'http://contrexx.itsicherheit.ch/index.php?section=news&amp;cmd=details&amp;newsid=196';
rssFeedNews[2]['date'] = '07.02.2006';
rssFeedNews[3] = new Array();
rssFeedNews[3]['title'] = 'Wer sucht der findet';
rssFeedNews[3]['link'] = 'http://contrexx.itsicherheit.ch/index.php?section=news&amp;cmd=details&amp;newsid=195';
rssFeedNews[3]['date'] = '07.02.2006';
rssFeedNews[4] = new Array();
rssFeedNews[4]['title'] = 'Schwester Portal allfind.ch aufgeschaltet';
rssFeedNews[4]['link'] = 'http://contrexx.itsicherheit.ch/index.php?section=news&amp;cmd=details&amp;newsid=194';
rssFeedNews[4]['date'] = '07.02.2006';
rssFeedNews[5] = new Array();
rssFeedNews[5]['title'] = 'Eintr&auml;ge k&ouml;nnen neu auch Bewertet werden';
rssFeedNews[5]['link'] = 'http://contrexx.itsicherheit.ch/index.php?section=news&amp;cmd=details&amp;newsid=193';
rssFeedNews[5]['date'] = '07.02.2006';
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