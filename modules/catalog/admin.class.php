<?php

// Create thumbnails
require_once('thumb.php');

class Catalog
{
    public $objTpl;
    public $pageTitle='';
    public $strErrMessage = '';
    public $strOkMessage = '';
    public $imagePath;
    public $langId;
    public $arrSettings = array();


    /**
     * constructor
     */
    function __construct()
    {
        $this->objTpl = new HTML_Template_Sigma(ASCMS_MODULE_PATH.'/catalog/template');
        $this->objTpl->setErrorHandling(PEAR_ERROR_DIE);
        $this->imagePath = ASCMS_MODULE_IMAGE_WEB_PATH;
    }


    /**
     * Calls the appropriate methods for the requested view
     * @global   HTML_Template_Sigma  $objTemplate
     * @return   void
     */
    function getPage()
    {
        global $objTemplate;

        $act = (empty($_GET['act']) ? '' : $_GET['act']);
        switch($act) {
            case "add":
                $this->add();
                break;
            case "edit":
                $this->edit();
                break;
            case "delete":
                default:
                $this->overview();
                break;
        }
        $objTemplate->setVariable('CONTENT_NAVIGATION',
            '<a href="index.php?cmd=catalog">Übersicht</a>
            <a href="index.php?cmd=catalog&amp;act=add">Neuer Eintrag</a>');
        $objTemplate->setVariable(array(
            'CONTENT_TITLE' => $this->pageTitle,
            'CONTENT_OK_MESSAGE' => $this->strOkMessage,
            'CONTENT_STATUS_MESSAGE' => $this->strErrMessage,
            'ADMIN_CONTENT' => $this->objTpl->get()
        ));
    }


    function overview()
    {
        global $objDatabase, $_ARRAYLANG;

        $this->pageTitle = $_ARRAYLANG['TXT_OVERVIEW'];
        $this->objTpl->loadTemplateFile('module_catalog_overview.html');

        $_POST['save']   = !empty($_POST['save']) ? $_POST['save'] : "";
        $_GET['act']      = !empty($_GET['act']) ? $_GET['act'] : "";

        if ($_POST['save']) {
            $updateError = false;
            foreach ($_POST['arrSortOrder'] as $id => $order) {
                if (!$objDatabase->Execute("UPDATE ".DBPREFIX."module_catalog SET sortorder = '".$order."' WHERE id = '".$id."';")) {
                    $updateError = true;
                }
            }
            if ($updateError) {
                $this->strErrMessage  = "Änderungen konnten nicht gespeichert werden.";
            } else {
                $this->strOkMessage = "Änderungen wurden gespeichert.";
            }
        }

        if ($_GET['act'] == "changeStatus") {
            $this->changeStatus($_GET['id'], $_GET['status']);
        }

        if ($_GET['act'] == "delete") {
            if ($this->delete($_GET['id'])) {
                $this->strOkMessage  = "Eintrag wurde erfolgreich gelöscht.";
            } else {
                $this->strErrMessage = "Eintrag konnte nicht gelöscht werden.";
            }
        }

        $query = "
            SELECT *
              FROM ".DBPREFIX."module_catalog
             ORDER BY sortorder ASC";
        $objResult = $objDatabase->Execute($query);
        $i = 0;
        while (!$objResult->EOF) {
            $this->objTpl->setVariable(array(
                'CATALOG_ID' => $objResult->fields['id'],
                'STATUS' => $objResult->fields['status'] ? 'green' : 'red',
                'STATUS_X' => $objResult->fields['status'] ? 'a' : 'i',
                'TITLE' => $objResult->fields['title'],
                'DESCRIPTION' => $objResult->fields['description'],
                'SORTORDER' => $objResult->fields['sortorder'],
                'ROW_CLASS' => (++$i % 2 ? 'row2' : 'row1'),
            ));
            $this->objTpl->parse('catalog_row');
            $objResult->MoveNext();
        }
    }


    function add()
    {
        global $objDatabase, $_ARRAYLANG;

        $this->pageTitle = $_ARRAYLANG['TXT_NEW'];
        $this->objTpl->loadTemplateFile('module_catalog_add.html');

        $_POST['save'] = !empty($_POST['save']) ? $_POST['save'] : '';
        if ($_POST['save']) {
            $query = "
                INSERT INTO ".DBPREFIX."module_catalog (
                    title, description, picture, status
                ) VALUES (
                    '".$_POST['title']."',
                    '".$_POST['description']."',
                    '".$_POST['picture']."',
                    '1'
                )";
            if ($objDatabase->Execute($query)) {
                if (createThumb($_POST['picture'])) {
                    $this->strOkMessage = "Eintrag wurde hinzugefügt.";
                    return true;
                }
                $this->strErrMessage = "Thumbnail konnte nicht erstellt werden.";
            }
            $this->strErrMessage = "Eintrag konnte nicht hinzugefügt werden.";
        }
        return false;
    }


    function edit()
    {
        global $objDatabase, $_ARRAYLANG;

        $this->pageTitle = $_ARRAYLANG['TXT_EDIT'];
        $this->objTpl->loadTemplateFile('module_catalog_edit.html');

        if (!empty($_POST['save'])) {
            $query = "
                UPDATE ".DBPREFIX."module_catalog
                   SET title = '".$_POST['title']."',
                       description = '".$_POST['description']."',
                       picture = '".$_POST['picture']."'
                 WHERE id='".$_GET['id']."'";
            if ($objDatabase->Execute($query)) {
                if (createThumb($_POST['picture'])) {
                    $this->strOkMessage  = "Änderungen wurden gespeichert.";
                } else {
                    $this->strErrMessage = "Thumbnail konnte nicht erstellt werden.";
                }
            } else {
                $this->strErrMessage = "Änderungen konnten nicht gespeichert werden.";
            }
        }

        // DISPLAY
        $query = "
            SELECT *
              FROM ".DBPREFIX."module_catalog
             WHERE id='".$_GET['id']."'";
        $objResult = $objDatabase->Execute($query);
        $this->objTpl->setVariable(array(
            'CATALOG_ID' => $objResult->fields['id'],
            'TITLE' => $objResult->fields['title'],
            'DESCRIPTION' => $objResult->fields['description'],
            'PICTURE' => $objResult->fields['picture'],
        ));
    }


    function changeStatus($id, $currentStatus)
    {
        global $objDatabase;

        $status = ($currentStatus == 'a' ? 0 : 1);
        if ($objDatabase->Execute("
            UPDATE ".DBPREFIX."module_catalog
               SET status='$status'
             WHERE id='$id'")) {
            return true;
        }
        return false;
    }


    function delete($id)
    {
        global $objDatabase;

        if ($objDatabase->Execute("
            DELETE FROM ".DBPREFIX."module_catalog
             WHERE id='$id'")) {
            return true;
        }
        return false;
    }

}

?>
