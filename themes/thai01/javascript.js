browserName = navigator.appName;
browserVer = parseInt(navigator.appVersion);
var msie4 = (browserName == "Microsoft Internet Explorer" && browserVer >= 4);
if ((browserName == "Netscape" && browserVer >= 3) || msie4 || browserName=="Konqueror" || browserName=="Opera") {version = "n3";} else {version = "n2";}
// Blurring links:
function blurLink(theObject)	{	//
if (msie4)	{theObject.blur();}
}

