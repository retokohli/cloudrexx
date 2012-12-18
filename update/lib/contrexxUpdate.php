<?php define('UPDATE_PATH', dirname(__FILE__));@include_once(UPDATE_PATH.'/../config/configuration.php');@header('content-type: text/html; charset='.(UPDATE_UTF8 ? 'utf-8' : 'iso-8859-1'));?>
function getXMLHttpRequestObj()
{
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
      setContent('<div style="margin-left: 155px; margin-top: 180px;">Bitte haben Sie einen Moment Geduld.<br /><?php $txt = 'Das Update wird durchgef�hrt...';print UPDATE_UTF8 ?  utf8_encode($txt) : $txt;?><br /><br /><img src="template/contrexx/images/content/loading_animation.gif" width="208" height="13" alt="" /></div>');
      setNavigation('');
    } else {
      setContent('Bitte warten. Die Seite wird geladen...');
    }
    objHttp.open('get', '?ajax='+encodeURIComponent(formData.substring(1, formData.length-1)), true);
    objHttp.onreadystatechange = parseResponse;
    objHttp.send(null);
    return false;
  }
  return true;
}

function getFormData(goBack)
{
  oFormData = new Object;

  oElements = document.getElementById('wrapper').getElementsByTagName('input');
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

  oElements = document.getElementById('wrapper').getElementsByTagName('select');
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
        setContent(oResponse.content);
        setLogout(oResponse.logout);
        setNavigation(oResponse.navigation);
      } catch(e) {}
    } else {
      setContent('<?php $txt = '<div class="message-alert">Das Update-Script gibt keine Antwort zur�ck!</div>';print UPDATE_UTF8 ?  utf8_encode($txt) : $txt;?>');
      setNavigation('<input type="submit" value="<?php $txt = 'Erneut versuchen...';print UPDATE_UTF8 ? utf8_encode($txt) : $txt;?>" name="updateNext" /><input type="hidden" name="processUpdate" id="processUpdate" />');
    }
    request_active = false;
  } else if (objHttp.readyState == 3) {
    return false;
  }
}

function setContent(content)
{
  setHtml(content, 'wrapper-content');
}

function setLogout(logout)
{
  setHtml(logout, 'wrapper-bottom-left');
}

function setNavigation(navigation)
{
  setHtml(navigation, 'wrapper-bottom-right');
}

function setHtml(text, element)
{
  if (text.length > 0) {
    try {
      if (html2dom.getDOM(text, element) !== false) {
        document.getElementById(element).innerHTML = '';
        eval(html2dom.result);
      } else {
        throw 'error';
      }
    } catch(e) {
      document.getElementById(element).innerHTML = 'HTML-Code konnte nicht Interpretiert werden:<br /><br />';
      document.getElementById(element).innerHTML += text;
    }
  } else {
    document.getElementById(element).innerHTML = '';
  }
}

var langs          = cx.variables.get('langs', 'update/contentMigration');
var similarPages   = cx.variables.get('arrSimilarPagesJs', 'update/contentMigration');
var defaultLang    = cx.variables.get('defaultLang', 'update/contentMigration');
var nodePageRegexp = /(\d+)_(\d+)/;
var removePages    = new Array();

function handleEvent(event,select,lang) {
    // 46 = DELETE key
    if (event.keyCode == 46) {
        page = jQuery(select).find(':selected');
        removePages.push(nodePageRegexp.exec(page.val())[2]);
        page.addClass('removed');
        move2NextUngroupedPage(page,lang);
    }
}

function choose(select,selectLang) {
    var selectedPageId = nodePageRegexp.exec(jQuery(select).find(':selected').val())[2];

    associatedNode = null;
    for (node in similarPages) {
        for (page in similarPages[node]) {
            if (similarPages[node][page] == selectedPageId) {
                associatedNode = node;
                break;
            }
        }
    }

    if (associatedNode == null) {
        console.log('nope..');
        return;
    }

    for (page in similarPages[associatedNode]) {
        for (lIdx in langs) {
            lang = langs[lIdx];
            if (lang != selectLang) {
                jQuery(jQuery('#page_tree_'+lang).find('[value$=_'+similarPages[associatedNode][page]+']')).attr('selected', true);
            }
        }
    }
}

function selectPage(option,lang) {
    jQuery('#page_group_'+lang).val(jQuery(option).val());
}

function groupPages() {
    nodeId = null;
    pages = new Array();
    options = new Array();

    for (lIdx in langs) {
        lang = langs[lIdx];
        pageInfo = jQuery('#page_group_'+lang).val();
        if (!pageInfo) {
            continue;
        }

        pageId = parseInt(nodePageRegexp.exec(pageInfo)[2],10);
        pages.push(pageId);

        selected = jQuery(jQuery('#page_tree_'+lang).find(':selected'));
        options[lang] = selected;

        if (lang == defaultLang) {
            nodeId = nodePageRegexp.exec(pageInfo)[1]
        }

    }

    if (nodeId) {
        similarPages[nodeId] = pages;

        for (lang in options) {
            if (lang) {
                options[lang].addClass('grouped');
                move2NextUngroupedPage(options[lang],lang);
            }
        }
    }
}

function move2NextUngroupedPage(page,lang) {
    while (page = page.next()) {
        if (page.hasClass('grouped')) {
            continue;
        }

        page.attr('selected', true);
        break;
    }
    selectPage(page,lang);
}

function executeGrouping() {
    jQuery('#similarPages').val(JSON.stringify(similarPages));
    jQuery('#removePages').val(JSON.stringify(removePages));
}
