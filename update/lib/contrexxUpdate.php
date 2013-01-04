<?php define('UPDATE_PATH', dirname(__FILE__));@include_once(UPDATE_PATH.'/../config/configuration.php');@header('content-type: text/html; charset='.(UPDATE_UTF8 ? 'utf-8' : 'iso-8859-1'));?>

var request_active = false;
var getDebugInfo = false;

if (typeof(DOMParser) == 'undefined') {
  useAjax = false;
} else {
  useAjax = true;
}

function doUpdate(goBack)
{
    getDebugInfo = false;

    if (useAjax) {
        if (request_active) {
            return false;
        } else {
            request_active = true;
        }

        formData = getFormData(goBack);

        if (formData.indexOf('debug_update') > -1) {
            getDebugInfo = true;
        }

        if ($J("#processUpdate").length || $J("#doGroup").length) {
            if (!getDebugInfo) {
                setContent('<div style="margin: 180px 0 0 155px;">Bitte haben Sie einen Moment Geduld.<br /><?php $txt = 'Das Update wird durchgeführt...';print UPDATE_UTF8 ? $txt : utf8_decode($txt);?><br /><br /><img src="template/contrexx/images/content/loading_animation.gif" width="208" height="13" alt="" /></div>');
                setNavigation('');
            }
        } else {
            setContent('Bitte warten. Die Seite wird geladen...');
        }

        jQuery.ajax({
            url: 'index.php',
            data: {'ajax': formData},
            success: parseResponse,
            error: cxUpdateErrorHandler,
        });
        return false;
    }
    return true;
}

function cxUpdateErrorHandler(jqXHR, status)
{
    showErrorScreen();
    request_active = false;
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
  
  var doGroup      = $J("#doGroup").length              ? ",doGroup:"      + $J("#doGroup").val()              : "";
  var similarPages = $J("#similarPages").length         ? ",similarPages:" + $J("#similarPages").val()         : "";
  var removePages  = $J("#removePages").length          ? ",removePages:"  + $J("#removePages").val()          : "";
  var delInAcLangs = $J("#delInAcLangs:checked").length ? ",delInAcLangs:" + $J("#delInAcLangs:checked").val() : "";
  
  var parameters = doGroup + similarPages + removePages + delInAcLangs;
  
  return '{' + aFormData.join(',') + parameters + '}';
}

function parseResponse(response)
{
    if (response.length > 0) {
      try {
        eval('oResponse='+response);
        if (oResponse.dialog) {
            langs        = oResponse.dialog.langs;
            similarPages = oResponse.dialog.similarPages;
            defaultLang  = oResponse.dialog.defaultLang;
            
            setContent('<div style="margin: 180px 0 0 155px;">Bitte haben Sie einen Moment Geduld.<br /><?php $txt = 'Das Update wird durchgeführt...';print UPDATE_UTF8 ? $txt : utf8_decode($txt);?><br /><br /><img src="template/contrexx/images/content/loading_animation.gif" width="208" height="13" alt="" /></div>');
            setNavigation('');
            
            cx.ui.dialog({
                width:         1020,
                height:        880,
                modal:         true,
                closeOnEscape: false,
                dialogClass:   "content-migration-dialog",
                title:         "Inhaltsseiten gruppieren",
                content:       oResponse.content,
                
                close: function() {
                    executeGrouping();
                },
                buttons: {
                    "Abschliessen": function() {
                        $J(this).dialog("close");
                    }
                }
            });
        } else {
            setContent(oResponse.content);
            setNavigation(oResponse.navigation);
            setLogout(oResponse.logout);
        }
        cx.ui.tooltip();
      } catch(e) {
        if (getDebugInfo) {
            jQuery('#debug_message').text(response).html();
            jQuery('#debug_message').show();
            jQuery('#debug_update').remove();
        } else {
            showErrorScreen();
        }
      }
    } else {
        showErrorScreen();
    }
    request_active = false;
}

function showErrorScreen()
{
    setContent('<?php $txt = '<div id="content-overview"><h1 class="first">Fehler beim Update...</h1><div class="message-alert">Das Update-Script gibt keine Antwort zurück!</div><div class="message-warning" id="debug_message"></div><input type="submit" value="Fehler anzeigen..." name="debug_update" id="debug_update" /></div>';print UPDATE_UTF8 ? $txt : utf8_encode($txt); ?>');
    //setContent('<?php $txt = '<div class="message-alert">Das Update-Script gibt keine Antwort zurück!</div>';print UPDATE_UTF8 ? $txt : utf8_encode($txt); ?>');
    setNavigation('<input type="submit" value="<?php $txt = 'Erneut versuchen...';print UPDATE_UTF8 ? utf8_encode($txt) : $txt;?>" name="updateNext" /><input type="hidden" name="processUpdate" id="processUpdate" />');
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

var langs          = $J.parseJSON(cx.variables.get('langs', 'update/contentMigration'));
var similarPages   = $J.parseJSON(cx.variables.get('similarPages', 'update/contentMigration'));
var defaultLang    = cx.variables.get('defaultLang', 'update/contentMigration');
var nodePageRegexp = /(\d+)_(\d+)/;
var removePages    = new Array();

$J(document).ready(function() {
    $J("body").delegate("select[id*=page_tree_]", "click", function() {
        $J('select[id*=page_tree_]').removeClass("focus");
        $J(this).addClass("focus");
    });
});

function delInAcLangs() {
    $J(".content-migration-select-wrapper.inactive-language").toggle();
};

function delPage() {
    page = $J("select[id*=page_tree_].focus option:selected");
    if (page.length) {
        lang = page.parent().attr("id").match(/\d/)[0];
        page.addClass('removed');
        removePages.push(nodePageRegexp.exec(page.val())[2]);
        move2NextUngroupedPage(page, lang);
    }
}

function undelPage() {
    page = $J("select[id*=page_tree_].focus option.removed:selected");
    if (page.length) {
        lang = page.parent().attr("id").match(/\d/)[0];
        page.removeClass('removed');
        var pageId = nodePageRegexp.exec(page.val())[2];
        var tmpRemovePages = removePages;
        removePages = new Array();
        for (idx in tmpRemovePages) {
            if (tmpRemovePages[idx] != pageId) {
                removePages.push(tmpRemovePages[idx]);
            }
        }
        move2NextUngroupedPage(page, lang);
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

function ungroupPages() {
    nodeId = null;
    pages = new Array();
    options = new Array();

    for (lIdx in langs) {
        lang = langs[lIdx];
        
        pageInfo = jQuery('#page_group_'+lang).val();
        if (!pageInfo) {
            continue;
        }

        selected = jQuery(jQuery('#page_tree_'+lang).find(':selected'));
        options[lang] = selected;

        if (lang == defaultLang) {
            nodeId = nodePageRegexp.exec(pageInfo)[1]
        }

    }

    if (nodeId) {
        delete similarPages[nodeId];

        for (lang in options) {
            if (lang) {
                options[lang].removeClass('grouped');
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
    $J('#doGroup').val(1);
    $J('#similarPages').val(JSON.stringify(similarPages));
    $J('#removePages').val(JSON.stringify(removePages));
    doUpdate();
}
