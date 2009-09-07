<?php

/**
 * Popup
 * @copyright   CONTREXX CMS - COMVATION AG
 * @author      Comvation Development Team <info@comvation.com>
 * @version     1.0.1
 * @package     contrexx
 * @subpackage  module_popup
 * @todo        Edit PHP DocBlocks!
 */

/**
 * Includes
 */
require_once ASCMS_MODULE_PATH.'/popup/lib/popupLib.class.php';
require_once ASCMS_CORE_PATH.'/Tree.class.php';

/**
 * Popup admin
 * @copyright   CONTREXX CMS - COMVATION AG
 * @author      Comvation Development Team <info@comvation.com>
 * @access      public
 * @version     1.0.1
 * @package     contrexx
 * @subpackage  module_popup
 */
class popupManager extends popupLibrary
{
    /**
    * Template object
    *
    * @access private
    * @var object
    */
    var $_objTpl;

    /**
    * Page title
    *
    * @access private
    * @var string
    */
    var $_pageTitle;

    /**
    * Okay message
    *
    * @access private
    * @var string
    */
    var $_strOkMessage = '';

    /**
    * error message
    *
    * @access private
    * @var string
    */
    var $_strErrMessage = '';


    /**
    * PHP5 constructor
    *
    * @global object $objTemplate
    * @global array $_ARRAYLANG
    */
    function __construct()
    {
        global $objTemplate, $_ARRAYLANG;

        $_ARRAYLANG['TXT_NO_POPUP_FOUND'] = "keine PopUps gefunden";
        $_ARRAYLANG['TXT_POPUP_POPUPS'] = "PopUp Fenster";
        $_ARRAYLANG['TXT_POPUP_NAME'] = "Name";
        $_ARRAYLANG['TXT_POPUP_TYPE'] = "Typ";
        $_ARRAYLANG['TXT_POPUP_FUNCTIONS'] = "Funktionen";
        $_ARRAYLANG['TXT_POPUP_DATE'] = "Anzeigedauer";
        $_ARRAYLANG['TXT_POPUP_SUBMIT_SELECT'] = "Aktion wählen";
        $_ARRAYLANG['TXT_POPUP_SUBMIT_DELETE'] = "Markierte löschen";
        $_ARRAYLANG['TXT_POPUP_SUBMIT_ACTIVATE'] = "Markierte aktivieren";
        $_ARRAYLANG['TXT_POPUP_SUBMIT_DEACTIVATE'] = "Markierte deaktivieren";
        $_ARRAYLANG['TXT_POPUP_SELECT_ALL'] = "Alles markieren";
        $_ARRAYLANG['TXT_POPUP_DESELECT_ALL'] = "Markierung löschen";
        $_ARRAYLANG['TXT_POPUP_DELETE_SELECTED_POPUP'] = "Möchten Sie die markierten PopUps wirklich löschen?";
        $_ARRAYLANG['TXT_POPUP_OPERATION_IRREVERSIBLE'] = "Dieser Vorgang kann nicht rückgängig gemacht werden! ";
        $_ARRAYLANG['TXT_POPUP_CONFIRM_DELETE_POPUP'] = "Möchten Sie das PopUp %s wirklich löschen? ";
        $_ARRAYLANG['TXT_POPUP_ACTIVE'] = "Aktiv";
        $_ARRAYLANG['TXT_POPUP_INACTIVE'] = "Inaktiv";
        $_ARRAYLANG['TXT_POPUP_WINDOW'] = "Fenster";
        $_ARRAYLANG['TXT_POPUP_LAYER'] = "Layer";
        $_ARRAYLANG['TXT_POPUP_THICKBOX'] = "ThickBox";
        $_ARRAYLANG['TXT_POPUP_TO'] = "bis";
        $_ARRAYLANG['TXT_POPUP_SINCE'] = "ab";
        $_ARRAYLANG['TXT_POPUP_NO_DATE'] = "keine Angaben";
        $_ARRAYLANG['TXT_POPUP_UPDATED_SUCCESSFULLY'] = "Das Popup %s wurde erfolgreich editiert";
        $_ARRAYLANG['TXT_POPUP_COULD_NOT_BE_UPDATED'] = "Fehler beim Editieren des Popup %s";
        $_ARRAYLANG['TXT_POPUP_ADDED_SUCCESSFULLY'] = "Das Popup %s wurde erfolgreich hinzugefügt";
        $_ARRAYLANG['TXT_POPUP_COULD_NOT_BE_ADDED'] = "Fehler beim Hinzufügen des Popup %s";
        $_ARRAYLANG['TXT_POPUP_CONTENT']="Popup Inhalt";
        $_ARRAYLANG['TXT_POPUP_NAME']="Name";
        $_ARRAYLANG['TXT_POPUP_FRONTEND_LANGUAGES']="Frontend Sprachen";
        $_ARRAYLANG['TXT_POPUP_SAVE']="Speichern";
        $_ARRAYLANG['TXT_POPUP_SIZE']="Abmessungen";
        $_ARRAYLANG['TXT_POPUP_POSITION']="Positionierung";
        $_ARRAYLANG['TXT_POPUP_SCROLLBARS']="Scrollbalken";
        $_ARRAYLANG['TXT_POPUP_ADRESSLIST']="Adressliste";
        $_ARRAYLANG['TXT_POPUP_RESIZABLE']="Fenstergrösse veränderbar";
        $_ARRAYLANG['TXT_POPUP_MENULIST']="Menuliste";
        $_ARRAYLANG['TXT_POPUP_STATUSLIST']="Statusliste";
        $_ARRAYLANG['TXT_POPUP_TYPE']="Anzeigetyp";
        $_ARRAYLANG['TXT_POPUP_ACTIVE']="Aktiviert";
        $_ARRAYLANG['TXT_POPUP_INACTIVE']="Deaktiviert";
        $_ARRAYLANG['TXT_POPUP_HEIGHT']="Höhe";
        $_ARRAYLANG['TXT_POPUP_WIDTH']="Breite";
        $_ARRAYLANG['TXT_POPUP_TOP']="Oben";
        $_ARRAYLANG['TXT_POPUP_LEFT']="Links";
        $_ARRAYLANG['TXT_POPUP_WINDOW_DESC']="Öffnet mittels JavaScript ein neues Browser-Fenster. Diese Fenster werden jedoch durch Popup-Blocker blockiert.";
        $_ARRAYLANG['TXT_POPUP_LAYER_DESC']="Generiert einen Div-Layer, welcher über das gesamte Design gelegt wird und mittels Klick wieder verschwindet. Diese Art wird von Popup-Blockern nicht erkannt.";
        $_ARRAYLANG['TXT_POPUP_LIGHTBOX_DESC']="Generiert mittels JavaScript einen animierten Bereich, in welchem der entsprechende Inhalt angezeigt wird.";
        $_ARRAYLANG['TXT_POPUP_SIZE_DESC']="Definiert die Grösse des anzuzeigenden Fensters. Die angegebene Grösse wird bei jedem Anzeigetyp berücksichtigt. Werte in Pixel.";
        $_ARRAYLANG['TXT_POPUP_POSITION_DESC']="Definiert die Position des Popups bezogen auf den oberen und linken (x/y Koordinaten) Browserrand. Werte in Pixel.";
        $_ARRAYLANG['TXT_POPUP_ACTIVATE']="aktivieren";
        $_ARRAYLANG['TXT_POPUP_SHOW_ON_ALL_PAGES']="Popup auf jeder Seite dieser Sprache verwenden";
        $_ARRAYLANG['TXT_POPUP_FRONTEND_PAGES']="Frontend Seiten";
        $_ARRAYLANG['TXT_POPUP_LANG_SHOW']="Popup in dieser Sprache verwenden";
        $_ARRAYLANG['TXT_POPUP_FROM'] = "von";
        $_ARRAYLANG['TXT_POPUP_TO'] = "bis";
        $_ARRAYLANG['TXT_POPUP_DATE_DESC'] = "Um eine unbegrenzte Anzeigedauer zu definieren, müssen beiden Felder leer gelassen werden. Es kann auch nur ein Start- oder Enddatum angegeben werden.";

        $this->_objTpl = new HTML_Template_Sigma(ASCMS_MODULE_PATH.'/popup/template');
        CSRF::add_placeholder($this->_objTpl);
        $this->_objTpl->setErrorHandling(PEAR_ERROR_DIE);

        $objTemplate->setVariable("CONTENT_NAVIGATION", "<a href='index.php?cmd=popup&amp;act=overview'>".$_ARRAYLANG['TXT_POPUP_OVERVIEW']."</a><a href='index.php?cmd=popup&amp;act=modify'>".$_ARRAYLANG['TXT_PUPUP_ADD_PUPUP']."</a><a href='index.php?cmd=popup&amp;act=settings'>".$_ARRAYLANG['TXT_POPUP_SETTINGS']."</a>");
    }

    /**
    * Get page
    *
    * Get a page of the block system administration
    *
    * @access public
    * @global object $objTemplate
    */
    function getPage()
    {
        global $objTemplate, $_CONFIG;

        if (!isset($_REQUEST['act'])) {
            $_REQUEST['act'] = '';
        }

        if ($_CONFIG['blockStatus'] != '1') {
            $_REQUEST['act'] = 'settings';
        }

        switch ($_REQUEST['act']) {
        case 'modify':
            $this->_showModifyBlock();
            break;

        case 'copy':
            $this->_showModifyBlock(true);
            break;

        case 'settings':
            $this->_showSettings();
            break;

        case 'del':
            $this->_delPopup();
            $this->_showOverview();
            break;

        case 'activate':
            $this->_activatePopup();
            $this->_showOverview();
            break;

        case 'deactivate':
            $this->_deactivatePopup();
            $this->_showOverview();
            break;

        default:
            $this->_showOverview();
            break;
        }

        $objTemplate->setVariable(array(
            'CONTENT_TITLE'                => $this->_pageTitle,
            'CONTENT_OK_MESSAGE'        => $this->_strOkMessage,
            'CONTENT_STATUS_MESSAGE'    => $this->_strErrMessage,
            'ADMIN_CONTENT'                => $this->_objTpl->get()
        ));
    }

    /**
    * Show overview
    *
    * Show the blocks overview page
    *
    * @access private
    * @global array $_ARRAYLANG
    * @see blockLibrary::_getBlocks(), blockLibrary::blockNamePrefix
    */
    function _showOverview()
    {
        global $_ARRAYLANG;

        $this->_pageTitle = $_ARRAYLANG['TXT_POPUP_OVERVIEW'];
        $this->_objTpl->loadTemplateFile('module_popup_overview.html');

        $this->_objTpl->setVariable(array(
            'TXT_POPUP_POPUPS'                    => $_ARRAYLANG['TXT_POPUP_POPUPS'],
            'TXT_POPUP_NAME'                    => $_ARRAYLANG['TXT_POPUP_NAME'],
            'TXT_POPUP_TYPE'                    => $_ARRAYLANG['TXT_POPUP_TYPE'],
            'TXT_POPUP_DATE'                    => $_ARRAYLANG['TXT_POPUP_DATE'],
            'TXT_POPUP_FUNCTIONS'                => $_ARRAYLANG['TXT_POPUP_FUNCTIONS'],
            'TXT_POPUP_SUBMIT_SELECT'            => $_ARRAYLANG['TXT_POPUP_SUBMIT_SELECT'],
            'TXT_POPUP_SUBMIT_DELETE'            => $_ARRAYLANG['TXT_POPUP_SUBMIT_DELETE'],
            'TXT_POPUP_SUBMIT_ACTIVATE'            => $_ARRAYLANG['TXT_POPUP_SUBMIT_ACTIVATE'],
            'TXT_POPUP_SUBMIT_DEACTIVATE'        => $_ARRAYLANG['TXT_POPUP_SUBMIT_DEACTIVATE'],
            'TXT_POPUP_SELECT_ALL'                => $_ARRAYLANG['TXT_POPUP_SELECT_ALL'],
            'TXT_POPUP_DESELECT_ALL'            => $_ARRAYLANG['TXT_POPUP_DESELECT_ALL'],
            'TXT_POPUP_DELETE_SELECTED_POPUP'    => $_ARRAYLANG['TXT_POPUP_DELETE_SELECTED_POPUP'],
            'TXT_POPUP_OPERATION_IRREVERSIBLE'    => $_ARRAYLANG['TXT_POPUP_OPERATION_IRREVERSIBLE'],
            'TXT_POPUP_CONFIRM_DELETE_POPUP'    => $_ARRAYLANG['TXT_POPUP_CONFIRM_DELETE_POPUP']
        ));

        $arrPopups = &$this->_getPopups();
        if (count($arrPopups)>0) {
            $rowNr = 0;
            foreach ($arrPopups as $popupId => $arrPopup) {
                if ($arrPopup['status'] ==  '1') {
                    $status = "<a href='index.php?cmd=popup&amp;act=deactivate&amp;popupId=".$popupId."' title='".$_ARRAYLANG['TXT_POPUP_ACTIVE']."'><img src='images/icons/led_green.gif' width='13' height='13' border='0' alt='".$_ARRAYLANG['TXT_POPUP_ACTIVE']."' /></a>";
                }else{
                    $status = "<a href='index.php?cmd=popup&amp;act=activate&amp;popupId=".$popupId."' title='".$_ARRAYLANG['TXT_POPUP_INACTIVE']."'><img src='images/icons/led_red.gif' width='13' height='13' border='0' alt='".$_ARRAYLANG['TXT_POPUP_INACTIVE']."' /></a>";
                }

                if ($arrPopup['type'] ==  '1') {
                    $type = $_ARRAYLANG['TXT_POPUP_WINDOW'];
                } elseif ($arrPopup['type'] ==  '2') {
                    $type = $_ARRAYLANG['TXT_POPUP_LAYER'];
                } else {
                    $type = $_ARRAYLANG['TXT_POPUP_THICKBOX'];
                }

                if ($arrPopup['start'] !=  '0000-00-00' && $arrPopup['end'] !=  '0000-00-00') {
                    $date = $_ARRAYLANG['TXT_POPUP_FROM']." ".$arrPopup['start']." ".$_ARRAYLANG['TXT_POPUP_TO']." ".$arrPopup['end'];
                } elseif ($arrPopup['start'] !=  '0000-00-00' && $arrPopup['end'] ==  '0000-00-00') {
                    $date = $_ARRAYLANG['TXT_POPUP_SINCE']." ".$arrPopup['start'];
                } elseif ($arrPopup['start'] ==  '0000-00-00' && $arrPopup['end'] !=  '0000-00-00') {
                    $date = $_ARRAYLANG['TXT_POPUP_TO']." ".$arrPopup['end'];
                } else {
                    $date =  $_ARRAYLANG['TXT_POPUP_NO_DATE'];
                }


                $this->_objTpl->setVariable(array(
                    'POPUP_ROW_CLASS'        => $rowNr % 2 ? "row1" : "row2",
                    'POPUP_ID'                => $popupId,
                    'POPUP_NAME'            => htmlentities($arrPopup['name'], ENT_QUOTES, CONTREXX_CHARSET),
                    'POPUP_STATUS'            => $status,
                    'POPUP_TYPE'            => $type,
                    'POPUP_DATE'            => $date,
                ));
                $this->_objTpl->parse('popupList');

                $rowNr ++;
            }
        } else {
            $this->_objTpl->setVariable(array(
                    'TXT_NO_POPUP_FOUND'        => $_ARRAYLANG['TXT_NO_POPUP_FOUND'],
                ));
                $this->_objTpl->parse('noPopup');
        }
    }

    /**
    * Show modify block
    *
    * Show the block modification page
    *
    * @access private
    * @global array $_ARRAYLANG
    * @see blockLibrary::_getBlockContent(), blockLibrary::blockNamePrefix
    */
    function _showModifyBlock($copy = false)
    {
        global $_ARRAYLANG, $objDatabase;

        $popupId                 = isset($_REQUEST['popupId']) ? intval($_REQUEST['popupId']) : 0;
        $popupAssociatedLangIds = array();

        $this->_objTpl->loadTemplateFile('module_popup_modify.html');

        if (isset($_POST['popup_save_popup'])) {
            $popupContent             = isset($_POST['popupContent']) ? $_POST['popupContent'] : '';
            $popupName                 = isset($_POST['popupName']) ? $_POST['popupName'] : '';
            $popupType                 = isset($_POST['popupType']) ? $_POST['popupType'] : '';
            $popupWidth             = isset($_POST['popupWidth']) ? $_POST['popupWidth'] : '';
            $popupHeight             = isset($_POST['popupHeight']) ? $_POST['popupHeight'] : '';
            $popupTop                = isset($_POST['popupTop']) ? $_POST['popupTop'] : '';
            $popupLeft                 = isset($_POST['popupLeft']) ? $_POST['popupLeft'] : '';
            $popupScrollbars         = isset($_POST['popupScrollbars']) ? $_POST['popupScrollbars'] : '';
            $popupAdresslist         = isset($_POST['popupAdressList']) ? $_POST['popupAdressList'] : '';
            $popupResizable            = isset($_POST['popupResizable']) ? $_POST['popupResizable'] : '';
            $popupMenulist             = isset($_POST['popupMenuList']) ? $_POST['popupMenuList'] : '';
            $popupStatuslist         = isset($_POST['popupStatusList']) ? $_POST['popupStatusList'] : '';
            $popupStart             = isset($_POST['popupFrom']) ? $_POST['popupFrom'] : '';
            $popupEnd                 = isset($_POST['popupTo']) ? $_POST['popupTo'] : '';

// TODO: Never used
//            $popupPages             = array();
//            $popupPages             = isset($_POST['selectedPages']) ? $_POST['selectedPages'] : '';
            $popupAssociatedLangIds = $_POST['popup_associated_language'];

            if ($popupId != 0) {
                //update
                if ($this->_updatePopup($popupId, $popupContent, $popupName, $popupType, $popupScrollbars, $popupStatuslist, $popupMenulist, $popupAdresslist, $popupResizable, $popupWidth, $popupHeight, $popupTop, $popupLeft, $popupStart, $popupEnd, $popupAssociatedLangIds)) {
                    $this->_strOkMessage = sprintf($_ARRAYLANG['TXT_POPUP_UPDATED_SUCCESSFULLY'], $popupName);
                    return $this->_showOverview();
                } else {
                    $this->_strErrMessage = sprintf($_ARRAYLANG['TXT_POPUP_COULD_NOT_BE_UPDATED'], $popupName);
                }
            } else {
                //add
                if ($this->_addPopup($popupId, $popupContent, $popupName, $popupType, $popupScrollbars, $popupStatuslist, $popupMenulist, $popupAdresslist, $popupResizable, $popupWidth, $popupHeight, $popupTop, $popupLeft, $popupStart, $popupEnd, $popupAssociatedLangIds)) {
                    $this->_strOkMessage = sprintf($_ARRAYLANG['TXT_POPUP_ADDED_SUCCESSFULLY'], $popupName);
                    return $this->_showOverview();
                } else {
                    $this->_strErrMessage = sprintf($_ARRAYLANG['TXT_POPUP_COULD_NOT_BE_ADDED'], $popupName);
                }
            }
        } elseif (($arrPopup = &$this->_getPopup($popupId)) !== false) {
            //get popup data
            $popupName                 = $arrPopup['name'];
            $popopWidth             = $arrPopup['width'];
            $popopHeight             = $arrPopup['height'];
            $popopTop                 = $arrPopup['top'];
            $popopLeft                 = $arrPopup['left'];
            $popopFrom                 = $arrPopup['start'];
            $popopTo                 = $arrPopup['end'];
            $popupContent            = $arrPopup['content'];

            if ($arrPopup['scrollbars'] == 1) {
                $popopScrollOff     = '';
                $popopScrollOn         = 'checked="checked"';
            } else {
                $popopScrollOff     = 'checked="checked"';
                $popopScrollOn         = '';
            }

            if ($arrPopup['adress_list'] == 1) {
                $popopAdressOff     = '';
                $popopAdressOn         = 'checked="checked"';
            } else {
                $popopAdressOff     = 'checked="checked"';
                $popopAdressOn         = '';
            }

            if ($arrPopup['menu_list'] == 1) {
                $popopMenuOff     = '';
                $popopMenuOn     = 'checked="checked"';
            } else {
                $popopMenuOff     = 'checked="checked"';
                $popopMenuOn     = '';
            }

            if ($arrPopup['status_list'] == 1) {
                $popopStatusOff     = '';
                $popopStatusOn         = 'checked="checked"';
            } else {
                $popopStatusOff     = 'checked="checked"';
                $popopStatusOn         = '';
            }

            if ($arrPopup['resizeable'] == 1) {
                $popopResizableOff     = '';
                $popopResizableOn     = 'checked="checked"';
            } else {
                $popopResizableOff     = 'checked="checked"';
                $popopResizableOn     = '';
            }

            switch ($arrPopup['type']) {
                case 1:
                    $popopTypeWindow         = 'checked="checked"';
                    $popopTypeLayer         = '';
                    $popopTypeThickBox         = '';
                    break;
                case 2:
                    $popopTypeWindow         = '';
                    $popopTypeLayer         = 'checked="checked"';
                    $popopTypeThickBox         = '';
                    break;
                case 3:
                    $popopTypeWindow         = '';
                    $popopTypeLayer         = '';
                    $popopTypeThickBox         = 'checked="checked"';
                    break;
                }


            $popupAssociatedLangIds = $this->_getAssociatedLangIds($popupId);
        } else {
            //get popup default data
            $arrSettings = $this->_getSettings();

            $popopWidth             = $arrSettings['default_width'];
            $popopHeight             = $arrSettings['default_height'];
            $popopTop                 = $arrSettings['default_top'];
            $popopLeft                 = $arrSettings['default_left'];
            $popopTypeWindow         = 'checked="checked"';
            $popopScrollOff         = 'checked="checked"';
            $popopAdressOff         = 'checked="checked"';
            $popopResizableOff         = 'checked="checked"';
            $popopMenuOff             = 'checked="checked"';
            $popopStatusOff         = 'checked="checked"';
            $popopFrom                 = '';
            $popopTo                 = '';

            $popupAssociatedLangIds = array_keys(FWLanguage::getLanguageArray());
        }

        $pageTitle = $popupId != 0 ? "Popup bearbeiten" : "Popup hinzuf�gen";
        $this->_pageTitle = $pageTitle;

        if ($copy) {
            $popupId = 0;
            $pageTitle = "Popup kopieren";
        }

        $this->_objTpl->setVariable(array(
            'POPUP_ID'                                => $popupId,
            'POPUP_MODIFY_TITLE'                    => $pageTitle,
            'POPUP_NAME'                            => htmlentities($popupName, ENT_QUOTES, CONTREXX_CHARSET),
            'POPUP_CONTENT'                            => get_wysiwyg_editor('popupContent', $popupContent),
            'POPUP_TYPE_WINDOW'                        => $popopTypeWindow,
            'POPUP_TYPE_LAYER'                        => $popopTypeLayer,
            'POPUP_TYPE_LIGHTBOX'                    => $popopTypeThickBox,
            'POPUP_SCROLL_OFF'                        => $popopScrollOff,
            'POPUP_ADRESS_OFF'                        => $popopAdressOff,
            'POPUP_RESIZABLE_OFF'                    => $popopResizableOff,
            'POPUP_MENU_OFF'                        => $popopMenuOff,
            'POPUP_STATUS_OFF'                        => $popopStatusOff,
            'POPUP_SCROLL_ON'                        => $popopScrollOn,
            'POPUP_ADRESS_ON'                        => $popopAdressOn,
            'POPUP_RESIZABLE_ON'                    => $popopResizableOn,
            'POPUP_MENU_ON'                            => $popopMenuOn,
            'POPUP_STATUS_ON'                        => $popopStatusOn,
            'POPUP_WIDTH'                            => $popopWidth,
            'POPUP_HEIGHT'                            => $popopHeight,
            'POPUP_TOP'                                => $popopTop,
            'POPUP_LEFT'                            => $popopLeft,
            'POPUP_FROM'                            => $popopFrom,
            'POPUP_TO'                                => $popopTo,
        ));


        //language boxes
        $arrLanguages = FWLanguage::getLanguageArray();
        $langNr = 0;

        foreach ($arrLanguages as $langId => $arrLanguage) {
            $column = $langNr % 3;
            $langStatus = "";

            //show on all pages
            if ($popupId != 0) {
                $objResult = $objDatabase->Execute('SELECT    all_pages
                                                    FROM    '.DBPREFIX.'module_popup_rel_lang
                                                    WHERE    popup_id='.$popupId.' AND popup_id='.$langId.'
                                                ');

                if ($objResult->RecordCount() > 0) {
                    while (!$objResult->EOF) {
                        $langAllPages = $objResult->fields['all_pages'];
                        $objResult->MoveNext();
                    }
                }
            } else {
                $langAllPages = 1;
            }


            //page relation
            $objResult = $objDatabase->Execute('SELECT    page_id
                                                FROM    '.DBPREFIX.'module_popup_rel_pages
                                                WHERE    popup_id='.$popupId.'
                                            ');
            $arrRelationContent = array();

            if ($objResult->RecordCount() > 0) {
                while (!$objResult->EOF) {
                    $arrRelationContent[$objResult->fields['page_id']] = '';
                    $objResult->MoveNext();
                }
            }

            // create new ContentTree instance
            $objContentTree = new ContentTree($langId);
            $strSelectedPages     = '';
            $strUnselectedPages = '';

            foreach ($objContentTree->getTree() as $arrData) {
                $strSpacer     = '';
                $intLevel    = intval($arrData['level']);
                for ($i = 0; $i < $intLevel; $i++) {
                    $strSpacer .= '&nbsp;&nbsp;';
                }

                if (array_key_exists($arrData['catid'],$arrRelationContent)) {
                    $langStatus .= $arrData['catname'].", ";
                    $strSelectedPages .= '<option value="'.$arrData['catid'].'">'.$strSpacer.$arrData['catname'].' ('.$arrData['catid'].') </option>'."\n";
                } else {
                    $strUnselectedPages .= '<option value="'.$arrData['catid'].'">'.$strSpacer.$arrData['catname'].' ('.$arrData['catid'].') </option>'."\n";
                }
            }

            if (empty($strSelectedPages)) {
                $objResult = $objDatabase->Execute('SELECT    lang_id
                                                    FROM    '.DBPREFIX.'module_popup_rel_lang
                                                    WHERE    popup_id='.$popupId.' AND lang_id='.$langId.'
                                                ');

                if ($objResult->RecordCount() > 0) {
                    if ($langAllPages == '1') {
                        $langStatus = "alle";
                    } else {
                        $langStatus = "-";
                    }
                } else {
                    $langStatus = "-";
                }
            } else {
                 $langStatus = substr($langStatus, 0,-2);
            }

            $this->_objTpl->setVariable(array(
                'POPUP_LANG_ID'                        => $langId,
                'POPUP_LANG_ID2'                    => $langId,
                'POPUP_LANG_ASSOCIATED'                => in_array($langId, $popupAssociatedLangIds) ? 'checked="checked"' : '',
                'POPUP_SHOW_ON_ALL_PAGES'            => $langAllPages == 1 ? 'checked="checked"' : '',
                'POPUP_LANG_NAME'                    => $arrLanguage['name'],
                'POPUP_LANG_STATUS'                    => '('.$langStatus.')',
                'POPUP_SELECTED_LANG_SHORTCUT'        => $arrLanguage['lang'],
                'POPUP_SELECTED_LANG_NAME'            => $arrLanguage['name'],
                'TXT_POPUP_ACTIVATE'                => $_ARRAYLANG['TXT_POPUP_ACTIVATE'],
                'TXT_POPUP_SHOW_ON_ALL_PAGES'        => $_ARRAYLANG['TXT_POPUP_SHOW_ON_ALL_PAGES'],
                'TXT_POPUP_FRONTEND_PAGES'            => $_ARRAYLANG['TXT_POPUP_FRONTEND_PAGES'],
                'TXT_POPUP_LANG_SHOW'                => $_ARRAYLANG['TXT_POPUP_LANG_SHOW'],
                'POPUP_PAGES_DISPLAY'                => $langAllPages == 1 ? 'none' : 'block',
                'POPUP_RELATION_PAGES_UNSELECTED'    => $strUnselectedPages,
                'POPUP_RELATION_PAGES_SELECTED'        => $strSelectedPages,
            ));

            $this->_objTpl->parse('popup_associated_language_'.$column);
            $this->_objTpl->parse('popup_associated_language_details');

            $formOnSubmit .= "selectAll(document.getElementById('".$langId."SelectedPages')); selectAll(document.getElementById('".$langId."notSelectedPages')); ";

            $langNr++;
        }

        //lang vars
        $this->_objTpl->setVariable(array(
            'TXT_POPUP_CONTENT'                    => $_ARRAYLANG['TXT_POPUP_CONTENT'],
            'TXT_POPUP_NAME'                    => $_ARRAYLANG['TXT_POPUP_NAME'],
            'TXT_POPUP_FRONTEND_LANGUAGES'        => $_ARRAYLANG['TXT_POPUP_FRONTEND_LANGUAGES'],
            'TXT_POPUP_SAVE'                    => $_ARRAYLANG['TXT_POPUP_SAVE'],
            'TXT_POPUP_SIZE'                    => $_ARRAYLANG['TXT_POPUP_SIZE'],
            'TXT_POPUP_POSITION'                => $_ARRAYLANG['TXT_POPUP_POSITION'],
            'TXT_POPUP_SCROLLBARS'                => $_ARRAYLANG['TXT_POPUP_SCROLLBARS'],
            'TXT_POPUP_ADRESSLIST'                => $_ARRAYLANG['TXT_POPUP_ADRESSLIST'],
            'TXT_POPUP_RESIZABLE'                => $_ARRAYLANG['TXT_POPUP_RESIZABLE'],
            'TXT_POPUP_MENULIST'                => $_ARRAYLANG['TXT_POPUP_MENULIST'],
            'TXT_POPUP_STATUSLIST'                => $_ARRAYLANG['TXT_POPUP_STATUSLIST'],
            'TXT_POPUP_TYPE'                    => $_ARRAYLANG['TXT_POPUP_TYPE'],
            'TXT_POPUP_INACTIVE'                => $_ARRAYLANG['TXT_POPUP_INACTIVE'],
            'TXT_POPUP_ACTIVE'                    => $_ARRAYLANG['TXT_POPUP_ACTIVE'],
            'TXT_POPUP_HEIGHT'                    => $_ARRAYLANG['TXT_POPUP_HEIGHT'],
            'TXT_POPUP_WIDTH'                    => $_ARRAYLANG['TXT_POPUP_WIDTH'],
            'TXT_POPUP_TOP'                        => $_ARRAYLANG['TXT_POPUP_TOP'],
            'TXT_POPUP_LEFT'                    => $_ARRAYLANG['TXT_POPUP_LEFT'],
            'TXT_POPUP_WINDOW_DESC'                => $_ARRAYLANG['TXT_POPUP_WINDOW_DESC'],
            'TXT_POPUP_LAYER_DESC'                => $_ARRAYLANG['TXT_POPUP_LAYER_DESC'],
            'TXT_POPUP_LIGHTBOX_DESC'            => $_ARRAYLANG['TXT_POPUP_LIGHTBOX_DESC'],
            'TXT_POPUP_SIZE_DESC'                => $_ARRAYLANG['TXT_POPUP_SIZE_DESC'],
            'TXT_POPUP_POSITION_DESC'            => $_ARRAYLANG['TXT_POPUP_POSITION_DESC'],
            'TXT_POPUP_CONTENT'                    => $_ARRAYLANG['TXT_POPUP_CONTENT'],
            'TXT_POPUP_NAME'                    => $_ARRAYLANG['TXT_POPUP_NAME'],
            'TXT_POPUP_FROM'                    => $_ARRAYLANG['TXT_POPUP_FROM'],
            'TXT_POPUP_DATE'                    => $_ARRAYLANG['TXT_POPUP_DATE'],
            'TXT_POPUP_TO'                        => $_ARRAYLANG['TXT_POPUP_TO'],
            'TXT_POPUP_DATE_DESC'                => $_ARRAYLANG['TXT_POPUP_DATE_DESC'],
            'POPUP_FORM_ONSUBMIT'                => $formOnSubmit,
            'POPUP_SRC_NOTE_IMAGE'                => ASCMS_ADMIN_WEB_PATH.'/images/icons/note.gif',
        ));
    }

    /**
    * Save block
    *
    * Add or update the conten to a block
    *
    * @access private
    * @global array $_ARRAYLANG
    * @see blockLibrary::_updateBlock()
    */
    /*function _saveBlock()
    {
        global $_ARRAYLANG;

        $blockId = isset($_POST['blockId']) ? intval($_POST['blockId']) : 0;
        $blockContent = isset($_POST['blockBlockContent']) ? contrexx_addslashes($_POST['blockBlockContent']) : '';
        $blockName = htmlspecialchars($_POST['blockName'], ENT_QUOTES, CONTREXX_CHARSET);
        $blockRandom = $_POST['blockRandom'];
        $blockAssociatedLangIds = array();

        if (isset($_POST['block_associated_language'])) {
            foreach ($_POST['block_associated_language'] as $langId => $status) {
                if (intval($status) == 1) {
                    array_push($blockAssociatedLangIds, intval($langId));
                }
            }
        }

        if($blockName == null ){
            $blockName = $_ARRAYLANG['TXT_BLOCK_NO_NAME'];
        }

        if ($blockId != 0) {
            if ($this->_updateBlock($blockId, $blockContent, $blockName, $blockRandom, $blockAssociatedLangIds)) {
                $this->_strOkMessage = $_ARRAYLANG['TXT_BLOCK_BLOCK_UPDATED_SUCCESSFULLY'];
            }else{
                $this->_strErrMessage = $_ARRAYLANG['TXT_BLOCK_BLOCK_COULD_NOT_BE_UPDATED'];
            }
        }else{
            if($this->_addBlock($blockId, $blockContent, $blockName, $blockRandom, $blockAssociatedLangIds)) {
                $this->_strOkMessage = $_ARRAYLANG['TXT_BLOCK_BLOCK_UPDATED_SUCCESSFULLY'];
            }else{
                $this->_strErrMessage = $_ARRAYLANG['TXT_BLOCK_BLOCK_COULD_NOT_BE_UPDATED'];
            }
        }
    }*/

    /**
    * del block
    *
    * delete a block
    *
    * @access private
    * @global array $_ARRAYLANG
    */
    function _delPopup()
    {
        global $_ARRAYLANG, $objDatabase;

        $_ARRAYLANG['TXT_POPUP_COULD_NOT_DELETE_POPUP']="Fehler beim L�schen der Popups";
        $_ARRAYLANG['TXT_POPUP_SUCCESSFULLY_DELETED']="Popups erfolgreich gel�scht";

        $arrDelPopups = array();
        $arrFailedPopup = array();
        //$arrBlockPopups = array();

        if (isset($_GET['popupId']) && ($popupId = intval($_GET['popupId'])) > 0) {
            $popupId = intval($_GET['popupId']);
            array_push($arrDelPopups, $popupId);
            //$arrPopup = &$this->_getBlock($blockId);
            //$arrBlockNames[$blockId] = htmlentities($arrBlock['name'], ENT_QUOTES, CONTREXX_CHARSET);
        } elseif (isset($_POST['selectedPopupId']) && is_array($_POST['selectedPopupId'])) {
            foreach ($_POST['selectedPopupId'] as $popupId) {
                $id = intval($popupId);
                if ($id > 0) {
                    array_push($arrDelPopups, $id);
                    //$arrBlock = &$this->_getBlock($id);
                    //$arrBlockNames[$id] = htmlentities($arrBlock['name'], ENT_QUOTES, CONTREXX_CHARSET);
                }
            }
        }

        if (count($arrDelPopups) > 0) {
            //foreach ($arrDelBlocks as $blockId) {
                foreach ($arrDelPopups as $popupId) {
                    if ($objDatabase->Execute("DELETE FROM ".DBPREFIX."module_popup_rel_lang WHERE popup_id=".$popupId) === false || $objDatabase->Execute("DELETE FROM ".DBPREFIX."module_popup WHERE id=".$popupId) === false || $objDatabase->Execute("DELETE FROM ".DBPREFIX."module_popup_rel_pages WHERE popup_id=".$popupId) === false) {
                        array_push($arrFailedPopup, $popupId);
                    }
                }
            //}

            if (count($arrFailedPopup) > 0) {
                $this->_strErrMessage = $_ARRAYLANG['TXT_POPUP_COULD_NOT_DELETE_POPUP'];
            } else {
                $this->_strOkMessage = $_ARRAYLANG['TXT_POPUP_SUCCESSFULLY_DELETED'];
            }
        }
    }

    /**
    * activate block
    *
    * change the status from a block
    *
    * @access private
    * @global array $_ARRAYLANG
    */
    function _activatePopup()
    {
        global $_ARRAYLANG, $objDatabase;

        $arrStatusPopups = $_POST['selectedPopupId'];
        if($arrStatusPopups != null){
            foreach ($arrStatusPopups as $popupId){
                $query = "UPDATE ".DBPREFIX."module_popup SET active='1' WHERE id=$popupId";
                $objDatabase->Execute($query);
            }
        }else{
            if(isset($_GET['popupId'])){
                $popupId = $_GET['popupId'];
                $query = "UPDATE ".DBPREFIX."module_popup SET active='1' WHERE id=$popupId";
                $objDatabase->Execute($query);
            }
        }
    }

    /**
    * deactivate block
    *
    * change the status from a block
    *
    * @access private
    * @global array $_ARRAYLANG
    */
    function _deactivatePopup()
    {
        global $_ARRAYLANG, $objDatabase;

        $arrStatusPopups = $_POST['selectedPopupId'];
        if($arrStatusPopups != null){
            foreach ($arrStatusPopups as $popupId){
                $query = "UPDATE ".DBPREFIX."module_popup SET active='0' WHERE id=$popupId";
                $objDatabase->Execute($query);
            }
        }else{
            if(isset($_GET['popupId'])){
                $popupId = $_GET['popupId'];
                $query = "UPDATE ".DBPREFIX."module_popup SET active='0' WHERE id=$popupId";
                $objDatabase->Execute($query);
            }
        }
    }


    /**
    * Show settings
    *
    * Show the settings page
    *
    * @access private
    * @global array $_ARRAYLANG
    */
    function _showSettings()
    {
        global $_ARRAYLANG, $_CONFIG, $objDatabase;

        $this->_pageTitle = $_ARRAYLANG['TXT_POPUP_SETTINGS'];
        $this->_objTpl->loadTemplateFile('module_popup_settings.html');

        $this->_objTpl->setVariable(array(
            'TXT_POPUP_SETTINGS'                        => $_ARRAYLANG['TXT_POPUP_SETTINGS'],
            'TXT_POPUP_PLACEHOLDER'                        => $_ARRAYLANG['TXT_POPUP_PLACEHOLDER'],
            'TXT_POPUP_BORDER_COLOR'                    => $_ARRAYLANG['TXT_POPUP_BORDER_COLOR'],
            'TXT_POPUP_BORDER_SIZE'                        => $_ARRAYLANG['TXT_POPUP_BORDER_SIZE'],
            'TXT_POPUP_DEFAULT_TOP'                        => $_ARRAYLANG['TXT_POPUP_DEFAULT_TOP'],
            'TXT_POPUP_DEFAULT_LEFT'                    => $_ARRAYLANG['TXT_POPUP_DEFAULT_LEFT'],
            'TXT_POPUP_DEFAULT_WIDTH'                    => $_ARRAYLANG['TXT_POPUP_DEFAULT_WIDTH'],
            'TXT_POPUP_DEFAULT_HEIGHT'                    => $_ARRAYLANG['TXT_POPUP_DEFAULT_HEIGHT'],
            'TXT_POPUP_SAVE'                            => $_ARRAYLANG['TXT_SAVE'],
            'TXT_POPUP_JS_FUNCTION'                        => $_ARRAYLANG['TXT_POPUP_JS_FUNCTION'],
            'TXT_POPUP_ONLOAD'                            => $_ARRAYLANG['TXT_POPUP_ONLOAD'],
            'TXT_POPUP_LAYER'                            => $_ARRAYLANG['TXT_POPUP_LAYER'],
            'TXT_POPUP_PLACEHOLDER_DESC'                => $_ARRAYLANG['TXT_POPUP_PLACEHOLDER_DESC'],
        ));


        if (isset($_POST['saveSettings'])) {
            foreach ($_POST['popupSettings'] as $setName => $setValue){
                $query = "UPDATE ".DBPREFIX."module_popup_settings SET value='".contrexx_addslashes($setValue)."' WHERE name='".$setName."'";
                $objDatabase->Execute($query);
            }

            //CSRF::header('Location: index.php?cmd=popup&act=settings');
        }

        $objResult = $objDatabase->Execute("SELECT    name, value FROM    ".DBPREFIX."module_popup_settings");

        if ($objResult !== false) {
            while (!$objResult->EOF) {
                $this->_objTpl->setVariable(array(
                    'POPUP_'.strtoupper($objResult->fields['name'])        => $objResult->fields['value'],
                ));
                $objResult->MoveNext();
            }
        }
    }
}

?>
