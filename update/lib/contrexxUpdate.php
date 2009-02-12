<?php define('UPDATE_PATH', dirname(__FILE__));@include_once(UPDATE_PATH.'/../config/configuration.php');@header('content-type: text/html; charset='.(UPDATE_UTF8 ? 'utf-8' : 'iso-8859-1'));?>
function getXMLHttpRequestObj() {
	var objXHR;
	if (window.XMLHttpRequest) {
		objXHR = new XMLHttpRequest();
	} else if (window.ActiveXObject) {
		objXHR = new ActiveXObject('Microsoft.XMLHTTP');
	}
	return objXHR;
}

objHttp = getXMLHttpRequestObj();
var request_active = false;

if (typeof(DOMParser) == 'undefined') {
	useAjax = false;
} else {
	useAjax = true;
}

function doUpdate(goBack)
{
	if (useAjax) {
		if (request_active) {
			return false;
		} else {
			request_active = true;
		}

		formData = getFormData(goBack);

		if (document.getElementById('processUpdate') != null) {
			setContent('<div style="margin-left:150px; margin-top:150px;">Bitte haben Sie einen Moment Geduld.<br /><?php $txt = 'Das Update wird durchgeführt...';print UPDATE_UTF8 ?  utf8_encode($txt) : $txt;?><br /><br /><img src="template/contrexx/images/loadingAnimation.gif" width="208" height="13" alt="" /></div>');
			setHeader('');
			setNavigation('');
		} else {
			setContent('Bitte warten. Die Seite wird geladen...');
		}

		objHttp.open('get', '?ajax='+encodeURIComponent(formData.substring(1, formData.length-1)), true);
		objHttp.onreadystatechange = parseResponse;
		objHttp.send(null);

		return false;
	} else {
		return true;
	}
}

function getFormData(goBack)
{
	oFormData = new Object;

	oElements = document.getElementById('updateForm').getElementsByTagName('input');
	if (oElements.length > 0) {
		for (i = 0;i < oElements.length; i++) {
			if (document.getElementsByName(oElements[i].name).length > 1) {
				if (typeof(oFormData[oElements[i].name]) == 'undefined') {
					oFormData[oElements[i].name] = new Array();
				}
				if (oElements[i].value != '' && oElements[i].type != 'radio' && (oElements[i].type != 'checkbox' || oElements[i].checked == true)
				) {
					oFormData[oElements[i].name].push(oElements[i].value);
				} else if (oElements[i].type == 'radio' && oElements[i].checked == true) {
					oFormData[oElements[i].name] = oElements[i].value;
				}
			} else {
				if (!goBack || oElements[i].name != 'updateNext') {
					if (oElements[i].type != 'radio' && oElements[i].type != 'checkbox' || oElements[i].checked == true) {
						oFormData[oElements[i].name] = oElements[i].value;
					}
				}
			}
		}
	}

	oElements = document.getElementById('updateForm').getElementsByTagName('select');
	if (oElements.length > 0) {
		for (i = 0;i < oElements.length; i++) {

			if (oElements[i].name.search('\[[0-9]+\]$') >= 0) {
				if (typeof(oFormData[oElements[i].name.substr(0,oElements[i].name.search('\[[0-9]+\]$'))]) == 'undefined') {
					oFormData[oElements[i].name.substr(0,oElements[i].name.search('\[[0-9]+\]$'))] = new Array();
				}
				oFormData[oElements[i].name.substr(0,oElements[i].name.search('\[[0-9]+\]$'))][oElements[i].name.substr(oElements[i].name.search('\[[0-9]+\]$')+1,oElements[i].name.match('\[[0-9]+\]$')[0].length-2)] = oElements[i].value;
			} else {
				oFormData[oElements[i].name] = oElements[i].value;
			}
		}
	}

	aFormData = new Array();
	for (i in oFormData) {
		aFormData.push(i+':'+((typeof(oFormData[i]) == 'object') ? '["'+oFormData[i].join('","')+'"]' : '"'+oFormData[i]+'"'));
	}

	return '({'+aFormData.join(',')+'})';
}

function parseResponse()
{
	if (objHttp.readyState == 4 && objHttp.status == 200) {
		response = objHttp.responseText;

		if (response.length > 0) {
			try {
				eval('oResponse='+response);

				setHeader(oResponse.header);
				setContent(oResponse.content);
				setNavigation(oResponse.navigation);

			} catch(e){}
		} else {
			setContent('<?php $txt = 'Das Update-Script gibt keine Antwort zurück!';print UPDATE_UTF8 ?  utf8_encode($txt) : $txt;?>');
			setNavigation('<input type="submit" value="<?php $txt = 'Erneut versuchen...';print UPDATE_UTF8 ? utf8_encode($txt) : $txt;?>" name="updateNext" /><input type="hidden" name="processUpdate" id="processUpdate" />');
		}
		request_active = false;
	} else if (objHttp.readyState == 3) {
		return false;
	}
}


function setHeader(sHeader)
{
	setHtml(sHeader, 'header');
}

function setContent(sContent)
{
	setHtml(sContent, 'content');
}

function setNavigation(sNavigation)
{
	setHtml(sNavigation, 'navigation');
}

function setHtml(sText, sElement)
{
	if (sText.length > 0) {
		try {
			if (html2dom.getDOM(sText, sElement) !== false) {
				document.getElementById(sElement).innerHTML = '';
				eval(html2dom.result);
			} else {
				throw 'error';
			}
		} catch(e) {
			document.getElementById(sElement).innerHTML = 'HTML-Code konnte nicht Interpretiert werden:<br /><br />';
			document.getElementById(sElement).innerHTML += sText;
		}
	} else {
		document.getElementById(sElement).innerHTML = '';
	}
}