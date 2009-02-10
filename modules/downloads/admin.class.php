<?php
if ($_COOKIE['debug']) {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
    $objDatabase->debug = 1;
} else {
    error_reporting(0);
    ini_set('display_errors', 0);
    $objDatabase->debug = 0;
}
/**
 * Downloads module
 * @copyright   CONTREXX CMS - COMVATION AG
 * @author      Comvation Development Team <info@comvation.com>
 * @version     1.0.0
 * @package     contrexx
 * @subpackage  downloads
 */

/**
 * Includes
 */

//error_reporting(E_ALL);
//ini_set('display_errors', 1);

require_once dirname(__FILE__).'/lib/downloadsLib.class.php';




class downloads extends DownloadsLibrary
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
     * Error status message
     *
     * @access private
     * @var string
     */
    var $_strErrMessage = '';

   /**
     * Ok status message
     *
     * @access private
     * @var string
     */
    var $_strOkMessage = '';

    /**
     * Contains the info messages about done operations
     *
     * @var array
     * @access private
     */
    private $arrStatusMsg = array('ok' => array(), 'error' => array());

    private $arrPermissionDependencies = array(
        'read' => array(
            'add_subcategories' => array(
                'manage_subcategories' => null
            ),
            'add_files' => array(
                'manage_files' => null
            )
        )
    );

    private $parentCategoryId = 0;

    /**
     * PHP5 constructor
     *
     * @global object $objTemplate
     * @global array $_ARRAYLANG
     */
    function __construct()
    {
        global $objTemplate, $_ARRAYLANG;


// new placeholders
$_ARRAYLANG['TXT_DOWNLOADS_NEW'] = 'Neu';
$_ARRAYLANG['TXT_DOWNLOADS_DOWNLOAD_ALL_ACCESS_DESC'] = 'Jeder der auf eine der zugeordneten Kategorien Zugriff hat, ist berechtigt auf diesen Download zuzugreifen.';
$_ARRAYLANG['TXT_DOWNLOADS_DOWNLOAD_SELECTED_ACCESS_DESC'] = 'Nur ausgewählte Gruppen dürfen auf diesen Download zugreifen.';
$_ARRAYLANG['TXT_DOWNLOADS_LOCAL_FILE'] = 'Lokale Datei';
$_ARRAYLANG['TXT_DOWNLOADS_URL'] = 'URL';
$_ARRAYLANG['TXT_DOWNLOADS_BYTES'] = 'Bytes';
$_ARRAYLANG['TXT_DOWNLOADS_AVAILABLE_CATEGORIES'] = 'Verfügbare Kategorien';
$_ARRAYLANG['TXT_DOWNLOADS_ASSIGNED_CATEGORIES'] = 'Zugewiesene Kategorien';
$_ARRAYLANG['TXT_DOWNLOADS_FAILED_UPDATE_DOWNLOAD'] = 'Beim Aktualisieren des Downloads trat ein Fehler auf!';
$_ARRAYLANG['TXT_DOWNLOADS_FAILED_UPDATE_CATEGORY'] = 'Beim Aktualisieren der Kategorie trat ein Fehler auf!';
$_ARRAYLANG['TXT_DOWNLOADS_FAILED_ADD_DOWNLOAD'] = 'Beim Hinzufügen des Downloads trat ein Fehler auf!';
$_ARRAYLANG['TXT_DOWNLOADS_FAILED_ADD_CATEGORY'] = 'Beim Hinzufügen der Kategorie trat ein Fehler auf!';
$_ARRAYLANG['TXT_DOWNLOADS_COULD_NOT_STORE_LOCALES'] = 'Beim Speichern des Beschreibung trat ein Fehler auf!';
$_ARRAYLANG['TXT_DOWNLOADS_COULD_NOT_STORE_PERMISSIONS'] = 'Beim Speichern der Zugriffsberechtigungen trat ein Fehler auf!';
$_ARRAYLANG['TXT_DOWNLOADS_COULD_NOT_STORE_CATEGORY_ASSOCIATIONS'] = 'Beim Speichern der Kategoriezugehörigkeiten trat ein Fehler auf!';
$_ARRAYLANG['TXT_DOWNLOADS_COULD_NOT_STORE_DOWNLOAD_RELATIONS'] = 'Beim Speichern der Verwanten Downloads trat ein Fehler auf!';
$_ARRAYLANG['TXT_DOWNLOADS_DEACTIVATE_DOWNLOAD_DESC'] = 'Klicken Sie hier, um diesen Download zu deaktivieren. ';
$_ARRAYLANG['TXT_DOWNLOADS_ACTIVATE_DOWNLOAD_DESC'] = 'Klicken Sie hier, um diesen Download zu aktivieren. ';
$_ARRAYLANG['TXT_DOWNLOADS_DOWNLOAD'] = 'Herunter laden';
$_ARRAYLANG['TXT_DOWNLOADS_CONFIRM_DELETE_DOWNLOAD'] = 'Möchten Sie den Download %s wirklich löschen?';
$_ARRAYLANG['TXT_DOWNLOADS_CONFIRM_DELETE_DOWNLOADS'] = 'Möchten Sie die ausgewählten Downloads wirklich löschen?';
$_ARRAYLANG['TXT_DOWNLOADS_DOWNLOAD_DELETE_SUCCESS'] = 'Der Download <strong>%s</strong> wurde erfolgreich gelöscht. ';
$_ARRAYLANG['TXT_DOWNLOADS_NO_PERM_DEL_DOWNLOAD'] = 'Sie sind nicht berechtigt den Download %s zu löschen!';
$_ARRAYLANG['TXT_DOWNLOADS_DOWNLOAD_DELETE_FAILED'] = 'Beim Löschen des Downloads <strong>%s</strong> trat ein Fehler auf!';
$_ARRAYLANG['TXT_DOWNLOADS_DOWNLOAD_ORDER_SET_FAILED'] = 'Die Reihenfolge der Downloads <strong>%s</strong> konnte nicht geändert werden!';
$_ARRAYLANG['TXT_DOWNLOADS_DOWNLOAD_ORDER_SET_SUCCESS'] = 'Die Reihenfolge der Downloads wurde erfolgreich geändert.';
$_ARRAYLANG['TXT_DOWNLOADS_DOWNLOADS_DELETE_SUCCESS'] = 'Die Downloads wurden erfolgreich gelöscht.';
$_ARRAYLANG['TXT_DOWNLOADS_CHANGE_SORT_DIRECTION'] = 'Sortierreihenfolge ändern';
$_ARRAYLANG['TXT_DOWNLOADS_DOWNLOAD_VISIBILITY_DESC'] = 'Diesen Download immer auflisten, auch wenn der aktuelle Benutzer keine Zugriffsberechtigung darauf hat.';
$_ARRAYLANG['TXT_DOWNLOADS_PARENT_CATEGORY'] = 'Übergeordnete Kategorie';
$_ARRAYLANG['TXT_DOWNLOADS_MAIN_CATEGORY'] = 'Haupt Kategorie';
$_ARRAYLANG['TXT_DOWNLOADS_ADD_MAIN_CATEGORY_PROHIBITED'] = 'Sie sind nicht berechtigt eine neue Haupt Kategorie zu erstellen!';
$_ARRAYLANG['TXT_DOWNLOADS_ADD_SUBCATEGORY_TO_CATEGORY_PROHIBITED'] = 'Sie sind nicht berechtigt in der Kategorie <strong>%s</strong> eine neue Unterkategorie anzulegen!';
$_ARRAYLANG['TXT_DOWNLOADS_CHANGE_PARENT_CATEGORY_PROHIBITED'] = 'Sie sind nicht berechtigt, die Übergeordnete Kategorie zu ändern!';
$_ARRAYLANG['TXT_DOWNLOADS_UPDATE_CATEGORY_PROHIBITED'] = 'Sie sind nicht berechtigt die Kategorie <strong>%s</strong> zu aktualisieren!';
$_ARRAYLANG['TXT_DOWNLOADS_APPLY_PERMISSIONS_RECURSIVEJ'] = 'Diese Berechtigungen für alle Unterkategorien übernehmen.';
$_ARRAYLANG['TXT_DOWNLOADS_NO_CATEGORIES_AVAILABLE'] = 'Es sind keine Kategorien vorhanden.';
$_ARRAYLANG['TXT_DOWNLOADS_DOWNLOADS_OF_CATEGORY'] = 'Downloads der Kategory %s';
$_ARRAYLANG['TXT_DOWNLOADS_UNLINK'] = 'Den Download aus dieser Kategorie entfernen.';
$_ARRAYLANG['TXT_DOWNLOADS_INACTIVE'] = 'Inaktiv';
$_ARRAYLANG['TXT_DOWNLOADS_CONFIRM_UNLINK_DOWNLOAD'] = 'Möchten Sie den Download %s aus dieser Kategorie entfernen?';
$_ARRAYLANG['TXT_DOWNLOADS_COULD_NOT_STORE_DOWNLOAD_ASSOCIATIONS'] = 'Beim Speichern der Download Zuweisungen trat ein Fehler aus!';
$_ARRAYLANG['TXT_DOWNLOADS_ADD_DOWNLOADS_TO_CATEGORY'] = 'Downloads zu dieser Kategorie hinzufügen';
$_ARRAYLANG['TXt_DOWNLOADS_ADD_DOWNLOADS_TO_CATEGORY'] = 'Downloads zur Kategorie %s hinzufügen';
$_ARRAYLANG['TXT_DOWNLOADS_NO_DOWNLOADS_ENTERED'] = 'Es sind keine Downloads erfasst.';
$_ARRAYLANG['TXT_DOWNLOADS_ADD_NEW_DOWNLOAD'] = 'Neuen Download hinzufügen';


// those might exist already
$_ARRAYLANG['TXT_DOWNLOADS_TYPE_UNDEFINED'] = "Undefiniert";
$_ARRAYLANG['TXT_DOWNLOADS_TYPE_IMAGE'] = "Bild";
$_ARRAYLANG['TXT_DOWNLOADS_TYPE_TEXT'] = "Text";
$_ARRAYLANG['TXT_DOWNLOADS_TYPE_MEDIA'] = "Media";
$_ARRAYLANG['TXT_DOWNLOADS_TYPE_ARCHIVE'] = "Archiv";
$_ARRAYLANG['TXT_DOWNLOADS_TYPE_APPLICATION'] = "Applikation";
$_ARRAYLANG['TXT_DOWNLOADS_STATUS'] = "Status";




$_ARRAYLANG['TXT_MANAGE_CATEGORIES'] = "Kategorien verwalten";
$_ARRAYLANG['TXT_ADD_CATEGORY'] = "Kategorie hinzufügen";
$_ARRAYLANG['TXT_SETTINGS'] = "Einstellungen";
$_ARRAYLANG['TXT_PLACEHOLDER'] = "Platzhalter";
$_ARRAYLANG['TXT_MANAGE_FILES'] = "Dateien verwalten";
$_ARRAYLANG['TXT_ADD_FILE'] = "Dateie hinzufügen";
$_ARRAYLANG['TXT_DOWNLOADS_MANAGE_DOWNLOADS'] = "Downloads verwalten";
$_ARRAYLANG['TXT_DOWNLOADS_ADD_DOWNLOAD'] = "Download hinzufügen";
$_ARRAYLANG['TXT_LANGUAGES'] = "Sprachen";
$_ARRAYLANG['TXT_DOWNLOADS_LANGUAGE'] = "Sprache";
$_ARRAYLANG['TXT_NAME'] = "Name";
$_ARRAYLANG['TXT_DESCRIPTION'] = "Beschreibung";
$_ARRAYLANG['TXT_IMAGE'] = "Bild";
$_ARRAYLANG['TXT_BROWSE'] = "Durchsuchen";
$_ARRAYLANG['TXT_DOWNLOADS_ADD_SUCCESSFULL'] = "Erfolgreich hinzugefügt";
$_ARRAYLANG['TXT_DOWNLOADS_ADD_FAILED'] = "Fehler beim speichern";
$_ARRAYLANG['TXT_DOWNLOADS_AUTHOR'] = "Autor";
$_ARRAYLANG['TXT_DOWNLOADS_PERMISSIONS'] = "Zugriffsberechtigungen";
$_ARRAYLANG['TXT_REMOVE_SELECTION'] = "Auswahl entfernen";
$_ARRAYLANG['TXT_SELECT_ACTION'] = "Aktion auswählen";
$_ARRAYLANG['TXT_DOWNLOADS_EDIT_CATEGORY'] = "Kategorie bearbeiten";
$_ARRAYLANG['TXT_DOWNLOADS_UPDATE_SUCCESSFULL'] = "Erfolgreich aktualisiert";
$_ARRAYLANG['TXT_DOWNLOADS_UPDATE_FAILED'] = "Fehler beim Speichern";
$_ARRAYLANG['TXT_DOWNLOADS_ORDER'] = "Reihenfolge";
$_ARRAYLANG['TXT_DOWNLOADS_FILE'] = "Datei";
$_ARRAYLANG['TXT_DOWNLOADS_CATEGORIES'] = "Kategorien";
$_ARRAYLANG['TXT_DOWNLOADS_CATEGORY'] = "Kategorie";
$_ARRAYLANG['TXT_DOWNLOADS_ADDED_CATEGORIES'] = "Hinzugefügte Kategorien";
$_ARRAYLANG['TXT_DOWNLOADS_FILES'] = "Dateien";
$_ARRAYLANG['TXT_DOWNLOADS_STATUS'] = "Status";
$_ARRAYLANG['TXT_DOWNLOADS_TYPE'] = "Typ";

$_ARRAYLANG['TXT_DOWNLOADS_FILEINFO'] = "Info";
$_ARRAYLANG['TXT_DOWNLOADS_SIZE'] = "Grösse";
$_ARRAYLANG['TXT_DOWNLOADS_LICENSE'] = "Lizenz";
$_ARRAYLANG['TXT_DOWNLOADS_VERSION'] = "Version";
$_ARRAYLANG['TXT_DOWNLOADS_RELATED_DOWNLOADS'] = "Verwandte Downloads";
$_ARRAYLANG['TXT_DOWNLOADS_PROTECTED_DOWNLOAD'] = "Geschützter Download";
$_ARRAYLANG['TXT_DOWNLOADS_YES'] = "Ja";
$_ARRAYLANG['TXT_DOWNLOADS_NO'] = "Nein";
$_ARRAYLANG['TXT_DOWNLOADS_DELETE_SUCCESSFULL'] = "Erfolgreich gelöscht";
$_ARRAYLANG['TXT_DOWNLOADS_DELETE_FAILED'] = "Fehler beim löschen";
$_ARRAYLANG['TXT_DOWNLOADS_EDIT_DOWNLOAD'] = "Download bearbeiten";
$_ARRAYLANG['TXT_DOWNLOADS_AVAILABLE_USER_GROUPS'] = "Verfügbare Benutzergruppen";
$_ARRAYLANG['TXT_DOWNLOADS_ASSIGNED_USER_GROUPS'] = "Zugewiesene Benutzergruppen";
$_ARRAYLANG['TXT_DOWNLOADS_SOURCE'] = "Source";
$_ARRAYLANG['TXT_DOWNLOADS_AVAILABLE_DOWNLOADS'] = "Verfügbare Downloads";
$_ARRAYLANG['TXT_DOWNLOADS_ASSIGNED_DOWNLOADS'] = "Zugewiesene Downloads";
$_ARRAYLANG['TXT_DOWNLOADS_STATUS'] = "Status";
$_ARRAYLANG['TXT_DOWNLOADS_DOWNLOADS'] = "Downloads";
$_ARRAYLANG['TXT_DOWNLOADS_ICONS'] = "Icons";
$_ARRAYLANG['TXT_PLACEHOLDER_FILE_ID'] = "Eindeutige ID";
$_ARRAYLANG['TXT_PLACEHOLDER_FILE_NAME'] = "Name des Downloads";
$_ARRAYLANG['TXT_PLACEHOLDER_FILE_DESC'] = "Beschreibung";
$_ARRAYLANG['TXT_PLACEHOLDER_FILE_TYPE'] = "Download-Typ (image, text, media, archive, applikation)";
$_ARRAYLANG['TXT_PLACEHOLDER_FILE_SIZE'] = "Grösse (KB)";
$_ARRAYLANG['TXT_PLACEHOLDER_FILE_IMG'] = "Screenshot/Vorschaubild";
$_ARRAYLANG['TXT_PLACEHOLDER_FILE_AUTHOR'] = "Autor";
$_ARRAYLANG['TXT_PLACEHOLDER_FILE_CREATED'] = "Datum der Erstellung";
$_ARRAYLANG['TXT_PLACEHOLDER_FILE_LICENSE'] = "Lizenz";
$_ARRAYLANG['TXT_PLACEHOLDER_FILE_VERSION'] = "Version";
$_ARRAYLANG['TXT_PLACEHOLDER_CATEGORY_ID'] = "Eindeutige ID";
$_ARRAYLANG['TXT_PLACEHOLDER_CATEGORY_NAME'] = "Name der Kategorie";
$_ARRAYLANG['TXT_PLACEHOLDER_CATEGORY_DESC'] = "Beschreibung";
$_ARRAYLANG['TXT_PLACEHOLDER_ICON_DISPLAY'] = "Ausgabe: block oder none. Je nach Einstellung: Icons oder keine Icons";
$_ARRAYLANG['TXT_PLACEHOLDER_ICON_FILTERS'] = "Filter/Suche-Icon (filter.gif)";
$_ARRAYLANG['TXT_PLACEHOLDER_ICON_INFO'] = "Information-Icon (info.gif)";
$_ARRAYLANG['TXT_PLACEHOLDER_ICON_CATEGORY'] = "Kategorie-Icon (category.gif oder das Kategoriebild)";
$_ARRAYLANG['TXT_PLACEHOLDER_ICON_FILE'] = "Datei/File-Icon. Je nach Downlaodtyp: file.gif, archive.gif, image.gif, media.gif, text.gif";
$_ARRAYLANG['TXT_PLACEHOLDER_ICON_DOWNLOAD'] = "Download-Icon/Button. Je nach Anmeldung: download.gif oder lock.gif";

$_ARRAYLANG['TXT_DOWNLOADS_FILTER'] = "Suche";
$_ARRAYLANG['TXT_DOWNLOADS_DESIGN'] = "Design / Icon-Sets";
$_ARRAYLANG['TXT_DOWNLOADS_NO_ICONS'] = "Keine Icons";
$_ARRAYLANG['TXT_DOWNLOADS_ICON_SET'] = "Icon-Set";










        $this->_objTpl = new HTML_Template_Sigma(ASCMS_MODULE_PATH.'/downloads/template');
        $this->_objTpl->setErrorHandling(PEAR_ERROR_DIE);

        $objTemplate->setVariable("CONTENT_NAVIGATION", "<a href='index.php?cmd=downloads'>".$_ARRAYLANG['TXT_DOWNLOADS_OVERVIEW']."</a>
                                                        <a href='index.php?cmd=downloads&amp;act=download'>".$_ARRAYLANG['TXT_DOWNLOADS_NEW']."</a>
                                                        <a href='index.php?cmd=downloads&amp;act=categories'>".$_ARRAYLANG['TXT_DOWNLOADS_CATEGORIES']."</a>
                                                        <a href='index.php?cmd=downloads&amp;act=settings'>".$_ARRAYLANG['TXT_SETTINGS']."</a>
                                                        ");
        parent::__construct();
    }


    /**
     * Set the backend page
     *
     * @access public
     * @global object $objTemplate
     * @global array $_ARRAYLANG
     */
    function getPage()
    {
        global $objTemplate, $_ARRAYLANG;
        if (!isset($_REQUEST['act'])) {
            $_REQUEST['act'] = '';
        }

        $this->parentCategoryId = isset($_REQUEST['parent_id']) ? intval($_REQUEST['parent_id']) : 0;


        switch ($_REQUEST['act']) {
//            case 'files':
//                $this->_files();
//                break;

            case 'delete_category':
                $this->deleteCategory();
                $this->loadCategoryNavigation();
                $this->categories();
                $this->parseCategoryNavigation();
                break;

            case 'switch_category_status':
                $this->switchCategoryStatus();
                $this->loadCategoryNavigation();
                $this->categories();
                $this->parseCategoryNavigation();
                break;

            case 'categories':
                $this->loadCategoryNavigation();
                $this->categories();
                $this->parseCategoryNavigation();
                break;
            case 'category':
                $this->loadCategoryNavigation();
                $this->category();
                $this->parseCategoryNavigation();
                break;

            case 'download':
                $this->download();
                break;

            case 'delete_download':
                $this->deleteDownload();
                $this->downloads();
                break;

            case 'switch_download_status':
                $this->switchDownloadStatus();
                if (!empty($_GET['parent_id'])) {
                    $this->loadCategoryNavigation();
                    $this->categories();
                    $this->parseCategoryNavigation();
                } else {
                    $this->downloads();
                }
                break;

            case 'unlink_download':
                $this->unlinkDownloadFromCategory();
                $this->loadCategoryNavigation();
                $this->categories();
                $this->parseCategoryNavigation();
                break;

            case 'add_downloads_to_category':
                if ($this->addDownloadsToCategory()) {
                    $this->loadCategoryNavigation();
                    $this->categories();
                    $this->parseCategoryNavigation();
                }
                break;

//            case 'placeholder':
//                $this->_placeholder();
//                break;
            case 'settings':
                $this->_settings();
                break;
//            case 'download_old':
//                $this->_download();
//                break;
            default:
                $this->downloads();
                break;
        }

        $objTemplate->setVariable(array(
            'CONTENT_TITLE'             => $this->_pageTitle,
            'CONTENT_OK_MESSAGE'        => implode("<br />\n", $this->arrStatusMsg['ok']),
            'CONTENT_STATUS_MESSAGE'    => implode("<br />\n", $this->arrStatusMsg['error']),
            'ADMIN_CONTENT'             => $this->_objTpl->get()
        ));
    }

    private function deleteCategory()
    {
        global $_LANGID, $_ARRAYLANG;

        $objCategory = Category::getCategory(isset($_GET['id']) ? $_GET['id'] : 0);

        if (!$objCategory->EOF) {
            $name = '<strong>'.htmlentities($objCategory->getName($_LANGID), ENT_QUOTES, CONTREXX_CHARSET).'</strong>';
            if ($objCategory->delete(isset($_GET['subcategories']) && $_GET['subcategories'] == 'true')) {
                $this->arrStatusMsg['ok'][] = sprintf($_ARRAYLANG['TXT_DOWNLOADS_CATEGORY_DELETE_SUCCESS'], $name);
            } else {
                $this->arrStatusMsg['error'] = array_merge($this->arrStatusMsg['error'], $objCategory->getErrorMsg());
            }
        }
    }

    private function deleteCategories($arrCategoryIds, $recursive = false)
    {
        global $_LANGID, $_ARRAYLANG;

        $succeded = true;

        foreach ($arrCategoryIds as $categoryId) {
            $objCategory = Category::getCategory($categoryId);

            if (!$objCategory->EOF) {
                if (!$objCategory->delete($recursive)) {
                    $succeded = false;
                    $this->arrStatusMsg['error'] = array_merge($this->arrStatusMsg['error'], $objCategory->getErrorMsg());
                }
            }
        }

        if ($succeded) {
            $this->arrStatusMsg['ok'][] = $_ARRAYLANG['TXT_DOWNLOADS_CATEGORIES_DELETE_SUCCESS'];
        }
    }

    private function switchCategoryStatus()
    {
        $objCategory = Category::getCategory(isset($_GET['id']) ? intval($_GET['id']) : 0);
        if (!$objCategory->EOF) {
            $objCategory->setActiveStatus(!$objCategory->getActiveStatus());
            $objCategory->store();
        }
    }

    /**
     * category edit
     *
     * @global object $objDatabase
     * @global array $_ARRAYLANG
     */
    private function category()
    {
        global $_ARRAYLANG, $objLanguage, $_LANGID;

        $status = true;


        $objFWUser = FWUser::getFWUserObject();
        $objCategory = Category::getCategory(isset($_REQUEST['id']) ? intval($_REQUEST['id']) : 0);


        //$arrCategory = $this->getCategory(isset($_REQUEST['id']) ? intval($_REQUEST['id']) : 0);

        if (!isset($objLanguages)) {
            $objLanguages = new FWLanguage();
        }

        if (isset($_POST['downloads_category_save'])) {
            // check if user is allowed to change that stuff
            // check if the user is allowed to create a category within the selected parentId
            $status = $objCategory->setParentId(isset($_POST['downloads_category_parent_id']) ? intval($_POST['downloads_category_parent_id']) : 0);
            $objCategory->setActiveStatus(isset($_POST['downloads_category_active']) && $_POST['downloads_category_active']);
            $objCategory->setImage(isset($_POST['downloads_category_image']) ? contrexx_stripslashes($_POST['downloads_category_image']) : '');
            $objCategory->setVisibility((!isset($_POST['downloads_category_read']) || !$_POST['downloads_category_read']) || isset($_POST['downloads_category_visibility']) && $_POST['downloads_category_visibility']);
            $objCategory->setNames(isset($_POST['downloads_category_name']) ? array_map('trim', array_map('contrexx_stripslashes', $_POST['downloads_category_name'])) : array());
            $objCategory->setDescriptions(isset($_POST['downloads_category_description']) ? array_map('trim', array_map('contrexx_stripslashes', $_POST['downloads_category_description'])) : array());
            $objCategory->setDownloads(isset($_POST['downloads_category_associated_downloads']) ? array_map('intval', $_POST['downloads_category_associated_downloads']) : array());

            if (Permission::checkAccess(142, 'static', true)) {
                $objCategory->setOwner(isset($_POST['downloads_category_owner_id']) ? intval($_POST['downloads_category_owner_id']) : $objFWUser->objUser->getId());
                $objCategory->setDeletableByOwner($objCategory->getOwnerId() == $objFWUser->objUser->getId() || isset($_POST['downloads_category_deletable_by_owner']) && $_POST['downloads_category_deletable_by_owner']);
                $objCategory->setModifyAccessByOwner($objCategory->getOwnerId() == $objFWUser->objUser->getId() || isset($_POST['downloads_category_manage_by_owner']) && $_POST['downloads_category_manage_by_owner']);
            }

            foreach ($this->arrPermissionTypes as $protectionType) {
                $arrCategoryPermissions[$protectionType]['protected'] = isset($_POST['downloads_category_'.$protectionType]) && $_POST['downloads_category_'.$protectionType];
                $arrCategoryPermissions[$protectionType]['groups'] = !empty($_POST['downloads_category_'.$protectionType.'_associated_groups']) ? array_map('intval', $_POST['downloads_category_'.$protectionType.'_associated_groups']) : array();
            }

            $objCategory->setPermissionsRecursive(!empty($_POST['downloads_category_apply_recursive']));
            $objCategory->setPermissions($arrCategoryPermissions);

            if ($status && $objCategory->store()) {
                $this->parentCategoryId = $objCategory->getParentId();
                return $this->categories();
            } else {
                $this->arrStatusMsg['error'] = array_merge($this->arrStatusMsg['error'], $objCategory->getErrorMsg());
            }
        } else {
            $objCategory->setParentId($this->parentCategoryId);
        }

        $this->_pageTitle = $objCategory->getId() ? $_ARRAYLANG['TXT_DOWNLOADS_EDIT_CATEGORY'] : $_ARRAYLANG['TXT_DOWNLOADS_ADD_CATEGORY'];
        $this->_objTpl->addBlockFile('DOWNLOADS_CATEGORY_TEMPLATE', 'module_downloads_categories', 'module_downloads_category_modify.html');

        /*
TXT_DOWNLOADS_GENERAL
TXT_DOWNLOADS_PERMISSIONS
TXT_DOWNLOADS_ADD_CATEGORY
TXT_DOWNLOADS_EDIT_CATEGORY
TXT_DOWNLOADS_NAME
TXT_DOWNLOADS_EXTENDED
TXT_DOWNLOADS_DESCRIPTION
TXT_DOWNLOADS_OWNER
TXT_DOWNLOADS_IMAGE
TXT_DOWNLOADS_CATEGORY_IMAGE
TXT_DOWNLOADS_REMOVE_IMAGE
TXT_DOWNLOADS_VIEW_CONTENT
TXT_DOWNLOADS_AVAILABLE_GROUPS
TXT_DOWNLOADS_CHECK_ALL
TXT_DOWNLOADS_UNCHECK_ALL
TXT_DOWNLOADS_ASSOCIATED_GROUPS
//TXT_DOWNLOADS_ADD_SUBCATEGORIES
//TXT_DOWNLOADS_MANAGE_SUBCATEGORIES
//TXT_DOWNLOADS_ADD_FILES
//TXT_DOWNLOADS_MANAGE_FILES
TXT_DOWNLOADS_CANCEL
TXT_DOWNLOADS_SAVE
TXT_DOWNLOADS_ACTIVE
TXT_DOWNLOADS_SUBCATEGORIES
TXT_DOWNLOADS_ADD
TXT_DOWNLOADS_MANAGE
TXT_DOWNLOADS_CATEGORY_DELETABLE_BY_OWNER
TXT_DOWNLOADS_CATEGORY_MANAGE_BY_OWNER
TXT_DOWNLOADS_CATEG0RY_VISIBILITY_DESC
         */


        $this->_objTpl->setVariable(array(
            'TXT_DOWNLOADS_GENERAL'                                     => $_ARRAYLANG['TXT_DOWNLOADS_GENERAL'],
            'TXT_DOWNLOADS_PERMISSIONS'                                 => $_ARRAYLANG['TXT_DOWNLOADS_PERMISSIONS'],
            'TXT_DOWNLOADS_NAME'                                        => $_ARRAYLANG['TXT_DOWNLOADS_NAME'],
            'TXT_DOWNLOADS_DESCRIPTION'                                 => $_ARRAYLANG['TXT_DOWNLOADS_DESCRIPTION'],
            'TXT_DOWNLOADS_PARENT_CATEGORY'                             => $_ARRAYLANG['TXT_DOWNLOADS_PARENT_CATEGORY'],
            'TXT_DOWNLOADS_ACTIVE'                                      => $_ARRAYLANG['TXT_DOWNLOADS_ACTIVE'],
            'TXT_DOWNLOADS_STATUS'                                      => $_ARRAYLANG['TXT_DOWNLOADS_STATUS'],
            'TXT_DOWNLOADS_OWNER'                                       => $_ARRAYLANG['TXT_DOWNLOADS_OWNER'],
            'TXT_DOWNLOADS_IMAGE'                                       => $_ARRAYLANG['TXT_DOWNLOADS_IMAGE'],
            'TXT_DOWNLOADS_CATEGORY_IMAGE'                              => $_ARRAYLANG['TXT_DOWNLOADS_CATEGORY_IMAGE'],
            'TXT_DOWNLOADS_REMOVE_IMAGE'                                => $_ARRAYLANG['TXT_DOWNLOADS_REMOVE_IMAGE'],
            'TXT_DOWNLOADS_FILES'                                       => $_ARRAYLANG['TXT_DOWNLOADS_FILES'],
            'TXT_DOWNLOADS_AVAILABLE_USER_GROUPS'                       => $_ARRAYLANG['TXT_DOWNLOADS_AVAILABLE_USER_GROUPS'],
            'TXT_DOWNLOADS_ASSIGNED_USER_GROUPS'                        => $_ARRAYLANG['TXT_DOWNLOADS_ASSIGNED_USER_GROUPS'],
            'TXT_DOWNLOADS_CHECK_ALL'                                   => $_ARRAYLANG['TXT_DOWNLOADS_CHECK_ALL'],
            'TXT_DOWNLOADS_UNCHECK_ALL'                                 => $_ARRAYLANG['TXT_DOWNLOADS_UNCHECK_ALL'],
            'TXT_DOWNLOADS_VIEW_CONTENT'                                => $_ARRAYLANG['TXT_DOWNLOADS_VIEW_CONTENT'],
            'TXT_DOWNLOADS_SUBCATEGORIES'                               => $_ARRAYLANG['TXT_DOWNLOADS_SUBCATEGORIES'],
            'TXT_DOWNLOADS_ADD'                                         => $_ARRAYLANG['TXT_DOWNLOADS_ADD'],
            'TXT_DOWNLOADS_MANAGE'                                      => $_ARRAYLANG['TXT_DOWNLOADS_MANAGE'],
            'TXT_DOWNLOADS_CATEGORY_DELETABLE_BY_OWNER'                 => $_ARRAYLANG['TXT_DOWNLOADS_CATEGORY_DELETABLE_BY_OWNER'],
            'TXT_DOWNLOADS_CATEGORY_MANAGE_BY_OWNER'                    => $_ARRAYLANG['TXT_DOWNLOADS_CATEGORY_MANAGE_BY_OWNER'],
//            'TXT_DOWNLOADS_ADD_SUBCATEGORIES'                           => $_ARRAYLANG['TXT_DOWNLOADS_ADD_SUBCATEGORIES'],
//            'TXT_DOWNLOADS_MANAGE_SUBCATEGORIES'                        => $_ARRAYLANG['TXT_DOWNLOADS_MANAGE_SUBCATEGORIES'],
//            'TXT_DOWNLOADS_ADD_FILES'                                   => $_ARRAYLANG['TXT_DOWNLOADS_ADD_FILES'],
//            'TXT_DOWNLOADS_MANAGE_FILES'                                => $_ARRAYLANG['TXT_DOWNLOADS_MANAGE_FILES'],
            'TXT_DOWNLOADS_CANCEL'                                      => $_ARRAYLANG['TXT_DOWNLOADS_CANCEL'],
            'TXT_DOWNLOADS_SAVE'                                        => $_ARRAYLANG['TXT_DOWNLOADS_SAVE'],
            'TXT_DOWNLOADS_BROWSE'                                      => $_ARRAYLANG['TXT_DOWNLOADS_BROWSE'],
            'TXT_DOWNLOADS_CATEG0RY_VISIBILITY_DESC'                    => $_ARRAYLANG['TXT_DOWNLOADS_CATEG0RY_VISIBILITY_DESC'],
            'TXT_DOWNLOADS_READ_ALL_ACCESS_DESC'                        => $_ARRAYLANG['TXT_DOWNLOADS_READ_ALL_ACCESS_DESC'],
            'TXT_DOWNLOADS_READ_SELECTED_ACCESS_DESC'                   => $_ARRAYLANG['TXT_DOWNLOADS_READ_SELECTED_ACCESS_DESC'],
            'TXT_DOWNLOADS_ADD_SUBCATEGORIES_ALL_ACCESS_DESC'           => $_ARRAYLANG['TXT_DOWNLOADS_ADD_SUBCATEGORIES_ALL_ACCESS_DESC'],
            'TXT_DOWNLOADS_ADD_SUBCATEGORIES_SELECTED_ACCESS_DESC'      => $_ARRAYLANG['TXT_DOWNLOADS_ADD_SUBCATEGORIES_SELECTED_ACCESS_DESC'],
            'TXT_DOWNLOADS_MANAGE_SUBCATEGORIES_ALL_ACCESS_DESC'        => $_ARRAYLANG['TXT_DOWNLOADS_MANAGE_SUBCATEGORIES_ALL_ACCESS_DESC'],
            'TXT_DOWNLOADS_MANAGE_SUBCATEGORIES_SELECTED_ACCESS_DESC'   => $_ARRAYLANG['TXT_DOWNLOADS_MANAGE_SUBCATEGORIES_SELECTED_ACCESS_DESC'],
            'TXT_DOWNLOADS_ADD_FILES_ALL_ACCESS_DESC'                   => $_ARRAYLANG['TXT_DOWNLOADS_ADD_FILES_ALL_ACCESS_DESC'],
            'TXT_DOWNLOADS_ADD_FILES_SELECTED_ACCESS_DESC'              => $_ARRAYLANG['TXT_DOWNLOADS_ADD_FILES_SELECTED_ACCESS_DESC'],
            'TXT_DOWNLOADS_MANAGE_FILES_ALL_ACCESS_DESC'                => $_ARRAYLANG['TXT_DOWNLOADS_MANAGE_FILES_ALL_ACCESS_DESC'],
            'TXT_DOWNLOADS_MANAGE_FILES_SELECTED_ACCESS_DESC'           => $_ARRAYLANG['TXT_DOWNLOADS_MANAGE_FILES_SELECTED_ACCESS_DESC'],
            'TXT_DOWNLOADS_APPLY_PERMISSIONS_RECURSIVEJ'                => $_ARRAYLANG['TXT_DOWNLOADS_APPLY_PERMISSIONS_RECURSIVEJ'],
            'TXT_DOWNLOADS_DOWNLOADS'                                   => $_ARRAYLANG['TXT_DOWNLOADS_DOWNLOADS'],
            'TXT_DOWNLOADS_AVAILABLE_DOWNLOADS'                         => $_ARRAYLANG['TXT_DOWNLOADS_AVAILABLE_DOWNLOADS'],
            'TXT_DOWNLOADS_ASSIGNED_DOWNLOADS'                          => $_ARRAYLANG['TXT_DOWNLOADS_ASSIGNED_DOWNLOADS']
        ));


        // parse general attributes
        $this->_objTpl->setVariable(array(
            'DOWNLOADS_CATEGORY_ID'                         => $objCategory->getId(),
            'DOWNLOADS_CATEGORY_PARENT_ID'                  => $objCategory->getParentId(),
            'DOWNLOADS_CATEGORY_OPERATION_TITLE'            => $objCategory->getId() ? $_ARRAYLANG['TXT_DOWNLOADS_EDIT_CATEGORY'] : $_ARRAYLANG['TXT_DOWNLOADS_ADD_CATEGORY'],
            'DOWNLOADS_CATEGORY_OWNER'                      => Permission::checkAccess(142, 'static', true) ? $this->getUserDropDownMenu($objCategory->getOwnerId(), $objFWUser->objUser->getId()) : $this->getParsedUsername($objCategory->getOwnerId()),
            'DOWNLOADS_CATEGORY_OWNER_CONFIG_DISPLAY'       => Permission::checkAccess(142, 'static', true) && $objCategory->getOwnerId() != $objFWUser->objUser->getId() ? '' : 'none',
            'DOWNLOADS_CATEGORY_DELETABLE_BY_OWNER_CHECKED' => $objCategory->getDeletableByOwner() ? 'checked="checked"' : '',
            'DOWNLOADS_CATEGORY_MANAGE_BY_OWNER_CHECKED'    => $objCategory->getModifyAccessByOwner() ? 'checked="checked"' : '',
            'DOWNLOADS_CATEGORY_ACTIVE_CHECKED'             => $objCategory->getActiveStatus() ? 'checked="checked"' : '',
            'DOWNLOADS_CATEGORY_VISIBILITY_CHECKED'         => $objCategory->getVisibility() ? 'checked="checked"' : ''
        ));


        // parse image attribute
        $image = $objCategory->getImage();
        if (!empty($image) && file_exists(ASCMS_PATH.$image)) {
            if (file_exists(ASCMS_PATH.$image.'.thumb')) {
                $imageSrc = $image.'.thumb';
            } else {
                $imageSrc = $image;
            }
        } else {
            $image = '';
            $imageSrc = $this->defaultCategoryImage['src'];
        }

        $this->_objTpl->setVariable(array(
            'DOWNLOADS_CATEGORY_IMAGE'                  => $image,
            'DOWNLOADS_CATEGORY_IMAGE_SRC'              => $imageSrc,
            'DOWNLOADS_DEFAULT_CATEGORY_IMAGE'          => $this->defaultCategoryImage['src'],
            'DOWNLOADS_DEFAULT_CATEGORY_IMAGE_WIDTH'    => $this->defaultCategoryImage['width'].'px',
            'DOWNLOADS_DEFAULT_CATEGORY_IMAGE_HEIGHT'   => $this->defaultCategoryImage['height'].'px',
            'DOWNLOADS_CATEGORY_IMAGE_REMOVE_DISPLAY'   => empty($image) ? 'none' : ''
        ));


        // parse name and description attributres
        if (!isset($arrLanguages)) {
            $arrLanguages = $objLanguage->getLanguageArray();
        }
        foreach ($arrLanguages as $langId => $arrLanguage) {
            $this->_objTpl->setVariable(array(
                'DOWNLOADS_CATEGORY_NAME'       => htmlentities($objCategory->getName($langId), ENT_QUOTES, CONTREXX_CHARSET),
                'DOWNLOADS_CATEGORY_LANG_ID'    => $langId,
                'DOWNLOADS_CATEGORY_LANG_NAME'  => htmlentities($arrLanguage['name'], ENT_QUOTES, CONTREXX_CHARSET)
            ));
            $this->_objTpl->parse('downloads_category_name_list');

            $this->_objTpl->setVariable(array(
                'DOWNLOADS_CATEGORY_DESCRIPTION'        => htmlentities($objCategory->getDescription($langId), ENT_QUOTES, CONTREXX_CHARSET),
                'DOWNLOADS_CATEGORY_LANG_ID'            => $langId,
                'DOWNLOADS_CATEGORY_LANG_DESCRIPTION'   => htmlentities($arrLanguage['name'], ENT_QUOTES, CONTREXX_CHARSET)
            ));
            $this->_objTpl->parse('downloads_category_description_list');
        }

        $this->_objTpl->setVariable(array(
            'DOWNLOADS_CATEGORY_NAME'   => htmlentities($objCategory->getName($_LANGID), ENT_QUOTES, CONTREXX_CHARSET),
            'TXT_DOWNLOADS_EXTENDED'    => $_ARRAYLANG['TXT_DOWNLOADS_EXTENDED']
        ));
        $this->_objTpl->parse('downloads_category_name');

        $this->_objTpl->setVariable(array(
            'DOWNLOADS_CATEGORY_DESCRIPTION'    => htmlentities($objCategory->getDescription($_LANGID), ENT_QUOTES, CONTREXX_CHARSET),
            'TXT_DOWNLOADS_EXTENDED'            => $_ARRAYLANG['TXT_DOWNLOADS_EXTENDED']
        ));
        $this->_objTpl->parse('downloads_category_description');


        // parse parent category menu
        $this->_objTpl->setVariable('DOWNLOADS_CATEGORY_PARENT_CATEGORY_MENU', $this->getCategoryMenu('add_subcategory', $objCategory->getParentId(), $_ARRAYLANG['TXT_DOWNLOADS_MAIN_CATEGORY'], null, $objCategory->getId()));


        // parse download associations
        $arrAssociatedDownloads = $objCategory->getAssociatedDownloadIds();
        $associatedDownloads = '';
        $notAssociatedDownloads = '';
        $objDownload = new Download();
        $objDownload->loadDownloads();
        while (!$objDownload->EOF) {
            $option = '<option value="'.$objDownload->getId().'">'.htmlentities($objDownload->getName($_LANGID), ENT_QUOTES, CONTREXX_CHARSET).'</option>';

            if (in_array($objDownload->getId(), $arrAssociatedDownloads)) {
                $associatedDownloads .= $option;
            } else {
                $notAssociatedDownloads .= $option;
            }

            $objDownload->next();
        }
        $this->_objTpl->setVariable(array(
            'DOWNLOADS_CATEGORY_NOT_ASSOCIATED_DOWNLOADS'   => $notAssociatedDownloads,
            'DOWNLOADS_CATEGORY_ASSOCIATED_DOWNLOADS'       => $associatedDownloads
        ));

        // parse access permissions
        $arrPermissions = $objCategory->getPermissions();

        $objGroup = $objFWUser->objGroup->getGroups();
        while (!$objGroup->EOF) {
            $option = '<option value="'.$objGroup->getId().'">'.htmlentities($objGroup->getName(), ENT_QUOTES, CONTREXX_CHARSET).' ['.$objGroup->getType().']</option>';

            foreach ($this->arrPermissionTypes as $permissionType) {
                if (in_array($objGroup->getId(), $arrPermissions[$permissionType]['groups'])) {
                    $arrPermissions[$permissionType]['associated_groups'][] = $option;
                } else {
                    $arrPermissions[$permissionType]['not_associated_groups'][] = $option;
                }
            }

            $objGroup->next();
        }

        foreach ($arrPermissions as $permissionType => $arrPermissionType) {
            $permissionTypeUC = strtoupper($permissionType);
            $this->_objTpl->setVariable(array(
                'DOWNLOADS_CATEGORY_'.$permissionTypeUC.'_ALL_CHECKED'              => !$arrPermissionType['protected'] ? 'checked="checked"' : '',
                'DOWNLOADS_CATEGORY_'.$permissionTypeUC.'_SELECTED_CHECKED'         => $arrPermissionType['protected'] ? 'checked="checked"' : '',
                'DOWNLOADS_CATEGORY_'.$permissionTypeUC.'_DISPLAY'                  => $arrPermissionType['protected'] ? '' : 'none',
                'DOWNLOADS_CATEGORY_'.$permissionTypeUC.'_NOT_ASSOCIATED_GROUPS'    => implode("\n", $arrPermissionType['not_associated_groups']),
                'DOWNLOADS_CATEGORY_'.$permissionTypeUC.'_ASSOCIATED_GROUPS'        => implode("\n", $arrPermissionType['associated_groups'])
            ));
        }

        $this->_objTpl->setVariable('DOWNLOADS_CATEGORY_APPLY_RECURSIVE_CHECKED', $objCategory->hasToSetPermissionsRecursive() ? 'checked="checked"' : '');



/*
$this->_objTpl->setVariable(array(
    'DOWNLOADS_CATEGORY_READ_ACCESS_ALL_CHECKED'
    'DOWNLOADS_CATEGORY_READ_ACCESS_SELECTED_CHECKED'
    'DOWNLOADS_CATEGORY_READ_ACCESS_DISPLAY'
    'DOWNLOADS_CATEGORY_READ_ACCESS_NOT_ASSOCIATED_GROUPS'  =>
    'DOWNLOADS_CATEGORY_READ_ACCESS_ASSOCIATED_GROUPS'
));

'DOWNLOADS_CATEGORY_ADD_SUBCATEGORIES_ALL_CHECKED'
'DOWNLOADS_CATEGORY_ADD_SUBCATEGORIES_SELECTED_CHECKED'
'DOWNLOADS_CATEGORY_ADD_SUBCATEGORIES_DISPLAY'
'DOWNLOADS_CATEGORY_ADD_SUBCATEGORIES_NOT_ASSOCIATED_GROUPS'
'DOWNLOADS_CATEGORY_ADD_SUBCATEGORIES_ASSOCIATED_GROUPS'

'DOWNLOADS_CATEGORY_MANAGE_SUBCATEGORIES_ALL_CHECKED'
'DOWNLOADS_CATEGORY_MANAGE_SUBCATEGORIES_SELECTED_CHECKED'
'DOWNLOADS_CATEGORY_MANAGE_SUBCATEGORIES_DISPLAY'
'DOWNLOADS_CATEGORY_MANAGE_SUBCATEGORIES_NOT_ASSOCIATED_GROUPS'
'DOWNLOADS_CATEGORY_MANAGE_SUBCATEGORIES_ASSOCIATED_GROUPS'

'DOWNLOADS_CATEGORY_ADD_FILES_ALL_CHECKED'
'DOWNLOADS_CATEGORY_ADD_FILES_SELECTED_CHECKED'
'DOWNLOADS_CATEGORY_ADD_FILES_DISPLAY'
'DOWNLOADS_CATEGORY_ADD_FILES_NOT_ASSOCIATED_GROUPS'
'DOWNLOADS_CATEGORY_ADD_FILES_ASSOCIATED_GROUPS'

'DOWNLOADS_CATEGORY_MANAGE_FILES_ALL_CHECKED'
'DOWNLOADS_CATEGORY_MANAGE_FILES_SELECTED_CHECKED'
'DOWNLOADS_CATEGORY_MANAGE_FILES_DISPLAY'
'DOWNLOADS_CATEGORY_MANAGE_FILES_NOT_ASSOCIATED_GROUPS'
'DOWNLOADS_CATEGORY_MANAGE_FILES_ASSOCIATED_GROUPS'
*/


//
//
//        $category = intval($_REQUEST["id"]);
//        if ($category<1) {
//            header('location: index.php?cmd=downloads&act=categories');
//            exit;
//        }
//
//        $CategoriyInfo = $this->_CategoryInfo($category);
//        // -----------------------------------------
//        // checkboxes & languagetabs 4 languages
//        // -----------------------------------------
//        $checkboxesSource = '';
//        $languageTabsNavi = '';
//        $languageTabsSource = '';
//        $js_arr = '';
//        $hideJS = '';
//        $fieldsArray = array();
//        $LiClass = 'active';
//        $StyleDisplay = 'block';
//        foreach ($this->_arrLang as $langId => $LangInfo) {
//            $fieldsArray = array('category_name_'.$langId => array('name' => $_ARRAYLANG['TXT_NAME'], 'value' => $CategoriyInfo['category_loc']['lang'][$langId]['name'], 'rte' => 0), 'category_desc_'.$langId => array('name' => $_ARRAYLANG['TXT_DESCRIPTION'], 'value' => $CategoriyInfo['category_loc']['lang'][$langId]['desc'], 'rte' => 2));
//
//            /*
//            if ($this->_CatLang($category, $langId)) {
//                $checked = 'checked="checked"';
//            } else {
//                $checked = '';
//                $hideJS .= '
//                document.getElementById("addEntry_'.$LangInfo['name'].'").style.display = "none";';
//            }
//            */
//
//            $checkboxesSource .= '<td><input  name="frmEditEntry_Languages[]" value="'.$langId.'" onclick="switchBoxAndTab(this, \'addEntry_'.$LangInfo['name'].'\');" type="checkbox" />'.$LangInfo['name'].' ['.$LangInfo['lang'].']</td>';
//            $languageTabsNavi .= '<li><a id="addEntry_'.$LangInfo['name'].'" class="'.$LiClass.'" href="javascript:{}" onclick="selectTab(\''.$LangInfo['name'].'\')" title="'.$LangInfo['name'].'" style="display: inline;">'.$LangInfo['name'].'</a></li>';
//            $LiClass = 'inactive';
//            $languageTabsSource .= $this->_LangTabHTML($LangInfo['name'], 'display: '.$StyleDisplay.';', $LangInfo['name'], $fieldsArray);
//            $StyleDisplay = 'none';
//            $js_arr .= 'arrTabToDiv["addEntry_'.$LangInfo['name'].'"] = "'.$LangInfo['name'].'"; ';
//        }
//
//        $languageTabsNavi = '<ul id="tabmenu">'.$languageTabsNavi.'</ul>';
//        $GroupsSelect = $this->_permissionsSelect('AddCategory');
//
//        $this->_objTpl->setVariable(array(
//            'TXT_DOWNLOADS_EDIT_CATEGORY' => $_ARRAYLANG['TXT_DOWNLOADS_EDIT_CATEGORY'],
//            'TXT_LANGUAGES' => $_ARRAYLANG['TXT_LANGUAGES'],
//            'TXT_IMAGE' => $_ARRAYLANG['TXT_IMAGE'],
//            'TXT_BROWSE' => $_ARRAYLANG['TXT_BROWSE'],
//            'TXT_SAVE' => $_ARRAYLANG['TXT_SAVE'],
//            'TXT_AUTHOR' => $_ARRAYLANG['TXT_DOWNLOADS_AUTHOR'],
//            'TXT_DOWNLOADS_PERMISSIONS' => $_ARRAYLANG['TXT_DOWNLOADS_PERMISSIONS'],
//            'GROUP_SELECT' => $GroupsSelect,
//            'LANG_SELECT' => $checkboxesSource,
//            'LANG_TABS' => $languageTabsSource,
//            'LANG_TAB_NAVI' => $languageTabsNavi,
//            'JS_ARR' => $js_arr,
//            'JS_HIDE' => $hideJS
//        ));
//
//        $this->_objTpl->setVariable(array(
//            'VALUE_AUTHOR' => $CategoriyInfo["category_author"],
//            'VALUE_IMG' => $CategoriyInfo["category_img"],
//            'VALUE_ID' => $category,
//        ));
    }



//    private function getParsedAccessPermissions($arrCategory)
//    {
//        $arrPermissions = array();
//        $objFWUser = FWUser::getFWUserObject();
//
//        foreach ($this->arrPermissionTypes as $permissionType) {
//            $arrPermissions[$permissionType] = array(
//                'set'                   => false,
//                'group_ids'             => array(),
//                'not_associated_groups' => array(),
//                'associated_groups'     => array()
//            );
//
//            if (isset($arrCategory['tmp_permissions'])) {
//                if ($arrCategory['tmp_permissions'][$permissionType]['protected']) {
//                    $arrPermissions[$permissionType]['set'] = true;
//                    $arrPermissions[$permissionType]['group_ids'] = $arrCategory['tmp_permissions'][$permissionType]['groups'];
//                }
//            } elseif ($arrCategory[$permissionType.'_access_id']) {
//                $arrPermissions[$permissionType]['set'] = true;
//                $objGroup = $objFWUser->objGroup->getGroups(array('dynamic' => $arrCategory[$permissionType.'_access_id']));
//                $arrPermissions[$permissionType]['group_ids'] = $objGroup->getLoadedGroupIds();
//            }
//        }
//
//        $objGroup = $objFWUser->objGroup->getGroups();
//        while (!$objGroup->EOF) {
//            $option = '<option value="'.$objGroup->getId().'">'.htmlentities($objGroup->getName(), ENT_QUOTES, CONTREXX_CHARSET).' ['.$objGroup->getType().']</option>';
//
//            foreach ($this->arrPermissionTypes as $permissionType) {
//                if (in_array($objGroup->getId(), $arrPermissions[$permissionType]['group_ids'])) {
//                    $arrPermissions[$permissionType]['associated_groups'][] = $option;
//                } else {
//                    $arrPermissions[$permissionType]['not_associated_groups'][] = $option;
//                }
//            }
//
//            $objGroup->next();
//        }
//
//        return $arrPermissions;
//    }

    private function downloads()
    {
        global $_ARRAYLANG, $_LANGID, $_CONFIG;

        $this->_pageTitle = $_ARRAYLANG['TXT_DOWNLOADS_OVERVIEW'];
        $this->_objTpl->loadTemplateFile('module_downloads_downloads.html');

        $limitOffset = isset($_GET['pos']) ? intval($_GET['pos']) : 0;
        $orderDirection = !empty($_GET['sort']) ? $_GET['sort'] : 'asc';
        $orderBy = !empty($_GET['by']) ? $_GET['by'] : 'order';
        $arrOrder[$orderBy] = $orderDirection;

        if (isset($_POST['downloads_download_select_action'])) {
            switch ($_POST['downloads_download_select_action']) {
                case 'order':
                    $this->updateDownloadOrder(isset($_POST['downloads_download_order']) && is_array($_POST['downloads_download_order']) ? $_POST['downloads_download_order'] : array());
                    break;

                case 'delete':
                    $this->deleteDownloads(isset($_POST['downloads_download_id']) && is_array($_POST['downloads_download_id']) ? $_POST['downloads_download_id'] : array());
                    break;
            }
        }

        $objDownload = new Download();
        $objDownload->loadDownloads($filter = null, $search = null, $arrOrder, $arrAttributes = null, $_CONFIG['corePagingLimit'], $limitOffset);
        if ($objDownload->EOF) {
            $this->_objTpl->setVariable(array(
                'TXT_DOWNLOADS_DOWNLOADS'               => $_ARRAYLANG['TXT_DOWNLOADS_DOWNLOADS'],
                'TXT_DOWNLOADS_NO_DOWNLOADS_ENTERED'    => $_ARRAYLANG['TXT_DOWNLOADS_NO_DOWNLOADS_ENTERED'],
                'TXT_DOWNLOADS_ADD_NEW_DOWNLOAD'        => $_ARRAYLANG['TXT_DOWNLOADS_ADD_NEW_DOWNLOAD']
            ));
            $this->_objTpl->parse('downloads_download_no_data');
            $this->_objTpl->hideBlock('downloads_download_data');
            return;
        } else {
            $this->_objTpl->hideBlock('downloads_download_no_data');
        }

        $this->_objTpl->setGlobalVariable(array(
            'TXT_DOWNLOADS_EDIT'    => $_ARRAYLANG['TXT_DOWNLOADS_EDIT'],
            'TXT_DOWNLOADS_DELETE'  => $_ARRAYLANG['TXT_DOWNLOADS_DELETE']
        ));

        // parse sorting
        $this->_objTpl->setVariable(array(
            'DOWNLOADS_SORT_DIRECTION'      => $orderDirection,
            'DOWNLOADS_SORT_BY'             => $orderBy,
            'DOWNLOADS_SORT_ID'             => ($orderBy == 'id' && $orderDirection == 'asc') ? 'desc' : 'asc',
            'DOWNLOADS_SORT_STATUS'         => ($orderBy == 'is_active' && $orderDirection == 'asc') ? 'desc' : 'asc',
            'DOWNLOADS_SORT_ORDER'          => ($orderBy == 'order' && $orderDirection == 'asc') ? 'desc' : 'asc',
            'DOWNLOADS_SORT_NAME'           => ($orderBy == 'name' && $orderDirection == 'asc') ? 'desc' : 'asc',
            'DOWNLOADS_SORT_DESCRIPTION'    => ($orderBy == 'description' && $orderDirection == 'asc') ? 'desc' : 'asc',
            'DOWNLOADS_SORT_AUTHOR'         => ($orderBy == 'author' && $orderDirection == 'asc') ? 'desc' : 'asc',
            'DOWNLOADS_SORT_SOURCE'         => ($orderBy == 'source' && $orderDirection == 'asc') ? 'desc' : 'asc',
            'DOWNLOADS_ID'                  => $_ARRAYLANG['TXT_DOWNLOADS_ID'].($orderBy == 'id' ? $orderDirection == 'asc' ? ' &uarr;' : ' &darr;' : ''),
            'DOWNLOADS_STATUS'              => $_ARRAYLANG['TXT_DOWNLOADS_STATUS'].($orderBy == 'is_active' ? $orderDirection == 'asc' ? ' &uarr;' : ' &darr;' : ''),
            'DOWNLOADS_ORDER'               => $_ARRAYLANG['TXT_DOWNLOADS_ORDER'].($orderBy == 'order' ? $orderDirection == 'asc' ? ' &uarr;' : ' &darr;' : ''),
            'DOWNLOADS_NAME'                => $_ARRAYLANG['TXT_DOWNLOADS_NAME'].($orderBy == 'name' ? $orderDirection == 'asc' ? ' &uarr;' : ' &darr;' : ''),
            'DOWNLOADS_DESCRIPTION'         => $_ARRAYLANG['TXT_DOWNLOADS_DESCRIPTION'].($orderBy == 'description' ? $orderDirection == 'asc' ? ' &uarr;' : ' &darr;' : ''),
            'DOWNLOADS_AUTHOR'              => $_ARRAYLANG['TXT_DOWNLOADS_AUTHOR'].($orderBy == 'author' ? $orderDirection == 'asc' ? ' &uarr;' : ' &darr;' : ''),
            'DOWNLOADS_SOURCE'              => $_ARRAYLANG['TXT_DOWNLOADS_SOURCE'].($orderBy == 'source' ? $orderDirection == 'asc' ? ' &uarr;' : ' &darr;' : '')
        ));

        $this->_objTpl->setVariable(array(
            'TXT_DOWNLOADS_CHANGE_SORT_DIRECTION'   => $_ARRAYLANG['TXT_DOWNLOADS_CHANGE_SORT_DIRECTION'],
            'TXT_DOWNLOADS_DOWNLOADS'               => $_ARRAYLANG['TXT_DOWNLOADS_DOWNLOADS'],
            'TXT_DOWNLOADS_ORDER'                   => $_ARRAYLANG['TXT_DOWNLOADS_ORDER'],
            'TXT_DOWNLOADS_FUNCTIONS'               => $_ARRAYLANG['TXT_DOWNLOADS_FUNCTIONS'],
            'TXT_DOWNLOADS_DOWNLOAD'                => $_ARRAYLANG['TXT_DOWNLOADS_DOWNLOAD'],
            'TXT_DOWNLOADS_CHECK_ALL'               => $_ARRAYLANG['TXT_DOWNLOADS_CHECK_ALL'],
            'TXT_DOWNLOADS_UNCHECK_ALL'             => $_ARRAYLANG['TXT_DOWNLOADS_UNCHECK_ALL'],
            'TXT_DOWNLOADS_SELECT_ACTION'           => $_ARRAYLANG['TXT_DOWNLOADS_SELECT_ACTION'],
            'DOWNLOADS_CONFIRM_DELETE_DOWNLOAD_TXT' => preg_replace('#\n#', '\\n', addslashes($_ARRAYLANG['TXT_DOWNLOADS_CONFIRM_DELETE_DOWNLOAD'])),
            'DOWNLOADS_CONFIRM_DELETE_DOWNLOADS_TXT'    => preg_replace('#\n#', '\\n', addslashes($_ARRAYLANG['TXT_DOWNLOADS_CONFIRM_DELETE_DOWNLOADS'])),
            'TXT_DOWNLOADS_OPERATION_IRREVERSIBLE'  => $_ARRAYLANG['TXT_DOWNLOADS_OPERATION_IRREVERSIBLE']
        ));


        //$this->parseLetterIndexList('index.php?cmd=access&amp;act=user&amp;groupId='.$groupId.'&amp;user_status_filter='.$userStatusFilter.'&amp;user_role_filter='.$userRoleFilter, 'username_filter', $usernameFilter);

        $downloadCount = $objDownload->getFilteredSearchDownloadCount();
        if ($downloadCount > $_CONFIG['corePagingLimit']) {
            $this->_objTpl->setVariable('DOWNLOADS_DOWNLOAD_PAGING', getPaging($downloadCount, $limitOffset, "&amp;cmd=downloads&amp;sort=".htmlspecialchars($orderDirection)."&amp;by=".htmlspecialchars($orderBy), "<b>".$_ARRAYLANG['TXT_DOWNLOADS_DOWNLOADS']."</b>"));
        }

        $nr = 0;
        while (!$objDownload->EOF)
        {
            $description = $objDownload->getDescription($_LANGID);
            if (strlen($description) > 100) {
                $description = substr($description, 0, 97).'...';
            }

            $source = $objDownload->getSource();
            if (strlen($source) > 100) {
                $source = substr($source, 0, 97).'...';
            }

            $this->_objTpl->setVariable(array(
                'DOWNLOADS_DOWNLOAD_ROW_CLASS'          => $nr++ % 2 ? 'row1' : 'row2',
                'DOWNLOADS_DOWNLOAD_ID'                 => $objDownload->getId(),
                'DOWNLOADS_DOWNLOAD_SWITCH_STATUS_DESC' => $objDownload->getActiveStatus() ? $_ARRAYLANG['TXT_DOWNLOADS_DEACTIVATE_DOWNLOAD_DESC'] : $_ARRAYLANG['TXT_DOWNLOADS_ACTIVATE_DOWNLOAD_DESC'],
                'DOWNLOADS_DOWNLOAD_STATUS_LED'         => $objDownload->getActiveStatus() ? 'led_green.gif' : 'led_red.gif',
                'DOWNLOADS_DOWNLOAD_ORDER'              => $objDownload->getOrder(),
                'DOWNLOADS_DOWNLOAD_ICON'               => $objDownload->getIcon(),
                'DOWNLOADS_DOWNLOAD_NAME'               => htmlentities($objDownload->getName($_LANGID), ENT_QUOTES, CONTREXX_CHARSET),
                'DOWNLOADS_DOWNLOAD_NAME_JS'            => htmlspecialchars($objDownload->getName($_LANGID), ENT_QUOTES, CONTREXX_CHARSET),
                'DOWNLOADS_DOWNLOAD_DESCRIPTION'        => htmlentities($description, ENT_QUOTES, CONTREXX_CHARSET),
                'DOWNLOADS_DOWNLOAD_AUTHOR'             => htmlentities($objDownload->getAuthor(), ENT_QUOTES, CONTREXX_CHARSET),
                'DOWNLOADS_DOWNLOAD_SOURCE'             => htmlentities($source, ENT_QUOTES, CONTREXX_CHARSET),
            ));

            $this->_objTpl->parse('downloads_download_list');

            $objDownload->next();
        }

    }

    private function deleteDownload()
    {
        global $_LANGID, $_ARRAYLANG;

        $objDownload = new Download();
        $objDownload->load(isset($_GET['id']) ? $_GET['id'] : 0);

        if (!$objDownload->EOF) {
            $name = '<strong>'.htmlentities($objDownload->getName($_LANGID), ENT_QUOTES, CONTREXX_CHARSET).'</strong>';
            if ($objDownload->delete()) {
                $this->arrStatusMsg['ok'][] = sprintf($_ARRAYLANG['TXT_DOWNLOADS_DOWNLOAD_DELETE_SUCCESS'], $name);
            } else {
                $this->arrStatusMsg['error'] = array_merge($this->arrStatusMsg['error'], $objDownload->getErrorMsg());
            }
        }
    }

    private function deleteDownloads($arrDownloadIds)
    {
        global $_LANGID, $_ARRAYLANG;

        $succeded = true;

        $objDownload = new Download();
        foreach ($arrDownloadIds as $downloadId) {
            $objDownload->load($downloadId);

            if (!$objDownload->EOF) {
                if (!$objDownload->delete()) {
                    $succeded = false;
                    $this->arrStatusMsg['error'] = array_merge($this->arrStatusMsg['error'], $objDownload->getErrorMsg());
                }
            }
        }

        if ($succeded) {
            $this->arrStatusMsg['ok'][] = $_ARRAYLANG['TXT_DOWNLOADS_DOWNLOADS_DELETE_SUCCESS'];
        }
    }

    private function switchDownloadStatus()
    {
        $objDownload = new Download();
        $objDownload->load(isset($_GET['id']) ? intval($_GET['id']) : 0);
        if (!$objDownload->EOF) {
            $objDownload->setActiveStatus(!$objDownload->getActiveStatus());
            $objDownload->store();
        }
    }

    private function unlinkDownloadFromCategory()
    {
        $categoryId = isset($_GET['parent_id']) ? intval($_GET['parent_id']) : 0;
        $objDownload = new Download();
        $objDownload->load(isset($_GET['id']) ? intval($_GET['id']) : 0);
        if (!$objDownload->EOF) {
            $arrCategoryAssociations = $objDownload->getAssociatedCategoryIds();
            unset($arrCategoryAssociations[array_search($categoryId, $arrCategoryAssociations)]);
            $objDownload->setCategories($arrCategoryAssociations);
            $objDownload->store();
        }
    }

    private function addDownloadsToCategory()
    {
        global $_ARRAYLANG, $_LANGID;

        $objCategory = Category::getCategory(!empty($_REQUEST['parent_id']) ? $_REQUEST['parent_id'] : 0);
        if ($objCategory->EOF) {
            return true;
        }

        if (isset($_POST['downloads_category_save_downloads'])) {
            $objCategory->setDownloads(isset($_POST['downloads_category_associated_downloads']) ? array_map('intval', $_POST['downloads_category_associated_downloads']) : array());

            if ($objCategory->storeDownloadAssociations()) {
                return true;
            } else {
                $this->arrStatusMsg['error'] = array_merge($this->arrStatusMsg['error'], $objCategory->getErrorMsg());
            }
        }

        $pageTitle = sprintf($_ARRAYLANG['TXt_DOWNLOADS_ADD_DOWNLOADS_TO_CATEGORY'], htmlentities($objCategory->getName($_LANGID), ENT_QUOTES, CONTREXX_CHARSET));
        $this->_pageTitle = $pageTitle;
        $this->_objTpl->loadTemplateFile('module_downloads_category_add_downloads.html');

        // parse download associations
        $arrAssociatedDownloads = $objCategory->getAssociatedDownloadIds();
        $hasRemoveRight = Permission::checkAccess(142, 'static', true) || $objCategory->getId() && (!$objCategory->getManageFilesAccessId() || Permission::checkAccess($objCategory->getManageFilesAccessId(), 'dynamic', true) || ($objFWUser = FWUser::getFWUserObject()) && $objFWUser->objUser->login() && $objCategory->getOwnerId() == $objFWUser->objUser->getId());
        $associatedDownloads = '';
        $notAssociatedDownloads = '';
        $objDownload = new Download();
        $objDownload->loadDownloads();
        while (!$objDownload->EOF) {
            if (in_array($objDownload->getId(), $arrAssociatedDownloads)) {
                $associatedDownloads .= '<option value="'.$objDownload->getId().'"'.($hasRemoveRight ? '' : ' disabled="disabled"').'>'.htmlentities($objDownload->getName($_LANGID), ENT_QUOTES, CONTREXX_CHARSET).'</option>';
            } else {
                $notAssociatedDownloads .= '<option value="'.$objDownload->getId().'">'.htmlentities($objDownload->getName($_LANGID), ENT_QUOTES, CONTREXX_CHARSET).'</option>';
            }

            $objDownload->next();
        }
        $this->_objTpl->setVariable(array(
            'DOWNLOADS_CATEGORY_NOT_ASSOCIATED_DOWNLOADS'   => $notAssociatedDownloads,
            'DOWNLOADS_CATEGORY_ASSOCIATED_DOWNLOADS'       => $associatedDownloads
        ));



        $this->_objTpl->setVariable(array(
            'TXT_DOWNLOADS_CANCEL'                          => $_ARRAYLANG['TXT_DOWNLOADS_CANCEL'],
            'TXT_DOWNLOADS_SAVE'                            => $_ARRAYLANG['TXT_DOWNLOADS_SAVE'],
            'TXT_DOWNLOADS_AVAILABLE_DOWNLOADS'             => $_ARRAYLANG['TXT_DOWNLOADS_AVAILABLE_DOWNLOADS'],
            'TXT_DOWNLOADS_ASSIGNED_DOWNLOADS'              => $_ARRAYLANG['TXT_DOWNLOADS_ASSIGNED_DOWNLOADS'],
            'TXT_DOWNLOADS_CHECK_ALL'                       => $_ARRAYLANG['TXT_DOWNLOADS_CHECK_ALL'],
            'TXT_DOWNLOADS_UNCHECK_ALL'                     => $_ARRAYLANG['TXT_DOWNLOADS_UNCHECK_ALL'],
            'DOWNLOADS_CATEGORY_ID'                         => $objCategory->getId(),
            'DOWNLOADS_ADD_DOWNLOADS_TO_CATEGORY_TXT'       => $pageTitle,
            'DOWNLOADS_CATEGORY_NOT_ASSOCIATED_DOWNLOADS'   => $notAssociatedDownloads,
            'DOWNLOADS_CATEGORY_ASSOCIATED_DOWNLOADS'       => $associatedDownloads
        ));
        return false;
    }

    private function updateDownloadOrder($arrDownloadOrder)
    {
        global $_LANGID, $_ARRAYLANG;

        $arrFailedDownloads = array();

        $objDownload = new Download();
        foreach ($arrDownloadOrder as $downloadId => $orderNr) {
            $objDownload->load($downloadId);
            if (!$objDownload->EOF) {
                $objDownload->setOrder($orderNr);
                if (!$objDownload->store()) {
                    $arrFailedDownloads[] = htmlentities($objDownload->getName($_LANGID), ENT_QUOTES, CONTREXX_CHARSET);
                }
            }
        }

        if (count($arrFailedDownloads)) {
            $this->arrStatusMsg['error'][] = sprintf($_ARRAYLANG['TXT_DOWNLOADS_DOWNLOAD_ORDER_SET_FAILED'], implode(', ', $arrFailedDownloads));
        } else {
            $this->arrStatusMsg['ok'][] = $_ARRAYLANG['TXT_DOWNLOADS_DOWNLOAD_ORDER_SET_SUCCESS'];
        }
    }

    private function download()
    {
        global $_ARRAYLANG, $objLanguage, $_LANGID;

        $objFWUser = FWUser::getFWUserObject();
        $objDownload = new Download();
        $objDownload->load(isset($_REQUEST['id']) ? intval($_REQUEST['id']) : 0);
        $categoryId = isset($_REQUEST['category_id']) ? intval($_REQUEST['category_id']) : 0;

        $arrAssociatedGroupOptions = array();
        $arrNotAssociatedGroupOptions = array();
        $arrAssociatedGroups = array();
        $arrAssociatedCategoryOptions = array();
        $arrNotAssociatedCategoryOptions = array();
        $arrAssociatedCategories = array();
        $arrAssociatedDownloadOptions = array();
        $arrNotAssociatedDownloadOptions = array();
        $arrAssociatedDownloads = array();


        if (!isset($objLanguages)) {
            $objLanguages = new FWLanguage();
        }

        if (isset($_POST['downloads_download_save'])) {
            $objDownload->setNames(isset($_POST['downloads_download_name']) ? array_map('trim', array_map('contrexx_stripslashes', $_POST['downloads_download_name'])) : array());
            $objDownload->setDescriptions(isset($_POST['downloads_download_description']) ? array_map('trim', array_map('contrexx_stripslashes', $_POST['downloads_download_description'])) : array());
            $objDownload->setType(isset($_POST['downloads_download_type']) ? contrexx_stripslashes($_POST['downloads_download_type']) : '');
            $objDownload->setSource(isset($_POST['downloads_download_'.$objDownload->getType().'_source']) ? contrexx_stripslashes($_POST['downloads_download_'.$objDownload->getType().'_source']) : '');
            $objDownload->setActiveStatus(!empty($_POST['downloads_download_is_active']));
            $objDownload->setMimeType(isset($_POST['downloads_download_mime_type']) ? contrexx_stripslashes($_POST['downloads_download_mime_type']) : '');
            $objDownload->setSize(isset($_POST['downloads_download_size']) ? intval($_POST['downloads_download_size']) : '');
            $objDownload->setLicense(isset($_POST['downloads_download_license']) ? contrexx_stripslashes($_POST['downloads_download_license']) : '');
            $objDownload->setVersion(isset($_POST['downloads_download_version']) ? contrexx_stripslashes($_POST['downloads_download_version']) : '');
            $objDownload->setAuthor(isset($_POST['downloads_download_author']) ? contrexx_stripslashes($_POST['downloads_download_author']) : '');
            $objDownload->setImage(isset($_POST['downloads_download_image']) ? contrexx_stripslashes($_POST['downloads_download_image']) : '');
            $objDownload->setVisibility(!empty($_POST['downloads_download_visibility']));
            $objDownload->setProtection(!empty($_POST['downloads_download_access']));
            $objDownload->setGroups($objDownload->getProtection() && !empty($_POST['downloads_download_access_associated_groups']) ? array_map('intval', $_POST['downloads_download_access_associated_groups']) : array());
            $objDownload->setCategories(!empty($_POST['downloads_download_associated_categories']) ? array_map('intval', $_POST['downloads_download_associated_categories']) : array());
            $objDownload->setDownloads(!empty($_POST['downloads_download_associated_downloads']) ? array_map('intval', $_POST['downloads_download_associated_downloads']) : array());

            if ($objDownload->store()) {
                return $this->downloads();
            } else {
                $this->arrStatusMsg['error'] = array_merge($this->arrStatusMsg['error'], $objDownload->getErrorMsg());
            }
        }

        $this->_pageTitle = $objDownload->getId() ? $_ARRAYLANG['TXT_DOWNLOADS_ADD_DOWNLOAD'] : $_ARRAYLANG['TXT_DOWNLOADS_EDIT_DOWNLOAD'];
        $this->_objTpl->loadTemplateFile('module_downloads_download_modify.html');

        $this->_objTpl->setVariable(array(
            'TXT_DOWNLOADS_GENERAL' => $_ARRAYLANG['TXT_DOWNLOADS_GENERAL'],
            'TXT_DOWNLOADS_PERMISSIONS' => $_ARRAYLANG['TXT_DOWNLOADS_PERMISSIONS'],
            'TXT_DOWNLOADS_DOWNLOAD_VISIBILITY_DESC'        => $_ARRAYLANG['TXT_DOWNLOADS_DOWNLOAD_VISIBILITY_DESC'],
            'TXT_DOWNLOADS_NAME'                            => $_ARRAYLANG['TXT_DOWNLOADS_NAME'],
            'TXT_DOWNLOADS_DESCRIPTION'                     => $_ARRAYLANG['TXT_DOWNLOADS_DESCRIPTION'],
            'TXT_DOWNLOADS_SOURCE'                          => $_ARRAYLANG['TXT_DOWNLOADS_SOURCE'],
            'TXT_DOWNLOADS_LOCAL_FILE'                      => $_ARRAYLANG['TXT_DOWNLOADS_LOCAL_FILE'],
            'TXT_DOWNLOADS_URL'                             => $_ARRAYLANG['TXT_DOWNLOADS_URL'],
            'TXT_DOWNLOADS_BROWSE'                          => $_ARRAYLANG['TXT_DOWNLOADS_BROWSE'],
            'TXT_DOWNLOADS_STATUS'                          => $_ARRAYLANG['TXT_DOWNLOADS_STATUS'],
            'TXT_DOWNLOADS_ACTIVE'                          => $_ARRAYLANG['TXT_DOWNLOADS_ACTIVE'],
            'TXT_DOWNLOADS_TYPE'                            => $_ARRAYLANG['TXT_DOWNLOADS_TYPE'],
            'TXT_DOWNLOADS_SIZE'                            => $_ARRAYLANG['TXT_DOWNLOADS_SIZE'],
            'TXT_DOWNLOADS_BYTES'                           => $_ARRAYLANG['TXT_DOWNLOADS_BYTES'],
            'TXT_DOWNLOADS_LICENSE'                         => $_ARRAYLANG['TXT_DOWNLOADS_LICENSE'],
            'TXT_DOWNLOADS_VERSION'                         => $_ARRAYLANG['TXT_DOWNLOADS_VERSION'],
            'TXT_DOWNLOADS_AUTHOR'                          => $_ARRAYLANG['TXT_DOWNLOADS_AUTHOR'],
            'TXT_DOWNLOADS_IMAGE'                           => $_ARRAYLANG['TXT_DOWNLOADS_IMAGE'],
            'TXT_DOWNLOADS_CATEGORIES'                      => $_ARRAYLANG['TXT_DOWNLOADS_CATEGORIES'],
            'TXT_DOWNLOADS_AVAILABLE_CATEGORIES'            => $_ARRAYLANG['TXT_DOWNLOADS_AVAILABLE_CATEGORIES'],
            'TXT_DOWNLOADS_ASSIGNED_CATEGORIES'             => $_ARRAYLANG['TXT_DOWNLOADS_ASSIGNED_CATEGORIES'],
            'TXT_DOWNLOADS_RELATED_DOWNLOADS'               => $_ARRAYLANG['TXT_DOWNLOADS_RELATED_DOWNLOADS'],
            'TXT_DOWNLOADS_AVAILABLE_DOWNLOADS'             => $_ARRAYLANG['TXT_DOWNLOADS_AVAILABLE_DOWNLOADS'],
            'TXT_DOWNLOADS_ASSIGNED_DOWNLOADS'              => $_ARRAYLANG['TXT_DOWNLOADS_ASSIGNED_DOWNLOADS'],
            'TXT_DOWNLOADS_DOWNLOAD_ALL_ACCESS_DESC'        => $_ARRAYLANG['TXT_DOWNLOADS_DOWNLOAD_ALL_ACCESS_DESC'],
            'TXT_DOWNLOADS_DOWNLOAD_SELECTED_ACCESS_DESC'   => $_ARRAYLANG['TXT_DOWNLOADS_DOWNLOAD_SELECTED_ACCESS_DESC'],
            'TXT_DOWNLOADS_AVAILABLE_USER_GROUPS'           => $_ARRAYLANG['TXT_DOWNLOADS_AVAILABLE_USER_GROUPS'],
            'TXT_DOWNLOADS_ASSIGNED_USER_GROUPS'            => $_ARRAYLANG['TXT_DOWNLOADS_ASSIGNED_USER_GROUPS'],
            'TXT_DOWNLOADS_CHECK_ALL'                       => $_ARRAYLANG['TXT_DOWNLOADS_CHECK_ALL'],
            'TXT_DOWNLOADS_UNCHECK_ALL'                     => $_ARRAYLANG['TXT_DOWNLOADS_UNCHECK_ALL'],
            'TXT_DOWNLOADS_CANCEL'                          => $_ARRAYLANG['TXT_DOWNLOADS_CANCEL'],
            'TXT_DOWNLOADS_SAVE'                            => $_ARRAYLANG['TXT_DOWNLOADS_SAVE']
        ));


        // parse id
        $this->_objTpl->setVariable('DOWNLOADS_DOWNLOAD_ID', $objDownload->getId());

        // parse name and description attributres
        if (!isset($arrLanguages)) {
            $arrLanguages = $objLanguage->getLanguageArray();
        }
        foreach ($arrLanguages as $langId => $arrLanguage) {
            $this->_objTpl->setVariable(array(
                'DOWNLOADS_DOWNLOAD_NAME'       => htmlentities($objDownload->getName($langId), ENT_QUOTES, CONTREXX_CHARSET),
                'DOWNLOADS_DOWNLOAD_LANG_ID'    => $langId,
                'DOWNLOADS_DOWNLOAD_LANG_NAME'  => htmlentities($arrLanguage['name'], ENT_QUOTES, CONTREXX_CHARSET)
            ));
            $this->_objTpl->parse('downloads_download_name_list');

            $this->_objTpl->setVariable(array(
                'DOWNLOADS_DOWNLOAD_DESCRIPTION'        => htmlentities($objDownload->getDescription($langId), ENT_QUOTES, CONTREXX_CHARSET),
                'DOWNLOADS_DOWNLOAD_LANG_ID'            => $langId,
                'DOWNLOADS_DOWNLOAD_LANG_DESCRIPTION'   => htmlentities($arrLanguage['name'], ENT_QUOTES, CONTREXX_CHARSET)
            ));
            $this->_objTpl->parse('downloads_download_description_list');
        }

        $this->_objTpl->setVariable(array(
            'DOWNLOADS_DOWNLOAD_NAME'   => htmlentities($objDownload->getName($_LANGID), ENT_QUOTES, CONTREXX_CHARSET),
            'TXT_DOWNLOADS_EXTENDED'    => $_ARRAYLANG['TXT_DOWNLOADS_EXTENDED']
        ));
        $this->_objTpl->parse('downloads_download_name');

        $this->_objTpl->setVariable(array(
            'DOWNLOADS_DOWNLOAD_DESCRIPTION'    => htmlentities($objDownload->getDescription($_LANGID), ENT_QUOTES, CONTREXX_CHARSET),
            'TXT_DOWNLOADS_EXTENDED'            => $_ARRAYLANG['TXT_DOWNLOADS_EXTENDED']
        ));
        $this->_objTpl->parse('downloads_download_description');


        // parse type
        $this->_objTpl->setVariable(array(
            'DOWNLOADS_DOWNLOAD_TYPE_FILE_CHECKED'          => $objDownload->getType() == 'file' ? 'checked="checked"' : '',
            'DOWNLOADS_DOWNLOAD_TYPE_URL_CHECKED'           => $objDownload->getType() == 'url' ? 'checked="checked"' : '',
            'DOWNLOADS_DOWNLOAD_FILE_SOURCE'                => $objDownload->getType() == 'file' ? htmlentities($objDownload->getSource(), ENT_QUOTES, CONTREXX_CHARSET) : '',
            'DOWNLOADS_DOWNLOAD_URL_SOURCE'                 => $objDownload->getType() == 'url' ? htmlentities($objDownload->getSource(), ENT_QUOTES, CONTREXX_CHARSET) : 'http://',
            'DOWNLOADS_DOWNLOAD_TYPE_FILE_CONFIG_DISPLAY'   => $objDownload->getType() == 'file' ? '' : 'none',
            'DOWNLOADS_DOWNLOAD_TYPE_URL_CONFIG_DISPLAY'    => $objDownload->getType() == 'url' ? '' : 'none'
        ));


        // parse active status
        $this->_objTpl->setVariable('DOWNLOADS_DOWNLOAD_IS_ACTIVE_CHECKED', $objDownload->getActiveStatus() ? 'checked="checked"' : '');


        // parse mime type
        $this->_objTpl->setVariable('DOWNLOADS_DOWNLOAD_MIME_TYPE_MENU', $this->getDownloadMimeTypeMenu($objDownload->getMimeType()));


        // parse size
        $this->_objTpl->setVariable('DOWNLOADS_DOWNLOAD_SIZE', $objDownload->getSize());


        // parse license
        $this->_objTpl->setVariable('DOWNLOADs_DOWNLOAD_LICENSE', htmlentities($objDownload->getLicense(), ENT_QUOTES, CONTREXX_CHARSET));


        // parse version
        $this->_objTpl->setVariable('DOWNLOADS_DOWNLOAD_VERSION', htmlentities($objDownload->getVersion(), ENT_QUOTES, CONTREXX_CHARSET));


        // parse author
        $this->_objTpl->setVariable('DOWNLOADS_DOWNLOAD_AUTHOR', htmlentities($objDownload->getAuthor(), ENT_QUOTES, CONTREXX_CHARSET));


        // parse image attribute
        $image = $objDownload->getImage();
        if (!empty($image) && file_exists(ASCMS_PATH.$image)) {
            if (file_exists(ASCMS_PATH.$image.'.thumb')) {
                $imageSrc = $image.'.thumb';
            } else {
                $imageSrc = $image;
            }
        } else {
            $image = '';
            $imageSrc = $this->defaultDownloadImage['src'];
        }

        $this->_objTpl->setVariable(array(
            'DOWNLOADS_DOWNLOAD_IMAGE'                  => $image,
            'DOWNLOADS_DOWNLOAD_IMAGE_SRC'              => $imageSrc,
            'DOWNLOADS_DEFAULT_DOWNLOAD_IMAGE'          => $this->defaultDownloadImage['src'],
            'DOWNLOADS_DEFAULT_DOWNLOAD_IMAGE_WIDTH'    => $this->defaultDownloadImage['width'].'px',
            'DOWNLOADS_DEFAULT_DOWNLOAD_IMAGE_HEIGHT'   => $this->defaultDownloadImage['height'].'px',
            'DOWNLOADS_DOWNLOAD_IMAGE_REMOVE_DISPLAY'   => empty($image) ? 'none' : ''
        ));


        // parse associated categories
        $arrCategories = $this->getParsedCategoryListForDownloadAssociation();
        $arrAssociatedCategories = $objDownload->getAssociatedCategoryIds();
        $length = count($arrCategories);
        for ($i = 0; $i < $length; $i++) {
            if (// managers are allowed to change the category association
                Permission::checkAccess(142, 'static', true)
                // the download isn't associated with the category
                || !in_array($arrCategories[$i]['id'], $arrAssociatedCategories) && (
                    // everyone is allowed to associate new files with this category
                    !$arrCategories[$i]['add_files_access_id']
                    // only those who have the sufficent permissions are allowed to add new files to this category
                    || Permission::checkAccess($arrCategories[$i]['add_files_access_id'], 'dynamic', true)
                )
                // the download is associated with the category
                || in_array($arrCategories[$i]['id'], $arrAssociatedCategories) && (
                    // every body is allowd to delete file associations of this category
                    !$arrCategories[$i]['manage_files_access_id']
                    // only those with sufficent permissions are allowed to delete file associations of this category
                    || Permission::checkAccess($arrCategories[$i]['manage_files_access_id'], 'dynamic', true)
                )
                // the owner is allowed to change the file associations of the category
                || $objFWUser->objUser->login() && $arrCategories[$i]['owner_id'] == $objFWUser->objUser->getId()
            ) {
                $disabled = false;
            } else {
                $disabled = true;
            }
            $option = '<option value="'.$arrCategories[$i]['id'].'"'.($disabled ? ' disabled="disabled"' : '').'>'.str_repeat('&nbsp;', $arrCategories[$i]['level'] * 4).htmlentities($arrCategories[$i]['name'], ENT_QUOTES, CONTREXX_CHARSET).'</option>';

            if (in_array($arrCategories[$i]['id'], $arrAssociatedCategories)) {
                $arrAssociatedCategoryOptions[] = $option;
            } else {
                $arrNotAssociatedCategoryOptions[] = $option;
            }
        }

        $this->_objTpl->setVariable(array(
            'DOWNLOADS_DOWNLOAD_ASSOCIATED_CATEGORIES'  => implode("\n", $arrAssociatedCategoryOptions),
            'DOWNLOADS_DOWNLOAD_NOT_ASSOCIATED_CATEGORIES'  => implode("\n", $arrNotAssociatedCategoryOptions)
        ));


        // parse related downloads
        $arrRelatedDownloads = $objDownload->getRelatedDownloadIds();
        $objAvailableDownload = new Download();
        $objAvailableDownload->loadDownloads(null, null, array('order' => 'ASC', 'name' => 'ASC', 'id' => 'ASC'));
        while (!$objAvailableDownload->EOF) {
            if ($objAvailableDownload->getId() == $objDownload->getId()) {
                $objAvailableDownload->next();
                continue;
            }

            $option = '<option value="'.$objAvailableDownload->getId().'">'.htmlentities($objAvailableDownload->getName($_LANGID), ENT_QUOTES, CONTREXX_CHARSET).' ('.htmlentities($objAvailableDownload->getDescription($_LANGID), ENT_QUOTES, CONTREXX_CHARSET).')</option>';

            if (in_array($objAvailableDownload->getId(), $arrRelatedDownloads)) {
                $arrAssociatedDownloadOptions[] = $option;
            } else {
                $arrNotAssociatedDownloadOptions[] = $option;
            }

            $objAvailableDownload->next();
        }

        $this->_objTpl->setVariable(array(
            'DOWNLOADS_DOWNLOAD_ASSOCIATED_DOWNLOADS'  => implode("\n", $arrAssociatedDownloadOptions),
            'DOWNLOADS_DOWNLOAD_NOT_ASSOCIATED_DOWNLOADS'  => implode("\n", $arrNotAssociatedDownloadOptions)
        ));


        // parse access permissions
        if ($objDownload->getAccessId()) {
            $objGroup = $objFWUser->objGroup->getGroups(array('dynamic', $objDownload->getAccessId()));
            $arrAssociatedGroups = $objGroup->getLoadedGroupIds();
        } elseif ($objDownload->getProtection()) {
            $arrAssociatedGroups = $objDownload->getAccessGroupIds();
            print_r($arrAssociatedGroups);
        } else {
            //$arrAssociatedCategories = $objDownload->getAssociatedCategoryIds();
            if (count($arrAssociatedCategories)) {
                $objCategory = Category::getCategories(array('id' => $arrAssociatedCategories), null, null, array('id', 'read_access_id'));
                while (!$objCategory->EOF) {
                    if ($objCategory->getReadAccessId()) {
                        $objGroup = $objFWUser->objGroup->getGroups(array('dynamic', $objCategory->getReadAccessId()));
                        $arrAssociatedGroups = array_merge($arrAssociatedGroups, $objGroup->getLoadedGroupIds());
                    }
                    $objCategory->next();
                }
            } else {
                // TODO: WHY THAT?
                $objGroup = $objFWUser->objGroup->getGroups();
                $arrAssociatedGroups = $objGroup->getLoadedGroupIds();
            }
        }

        $objGroup = $objFWUser->objGroup->getGroups();
        while (!$objGroup->EOF) {
            $option = '<option value="'.$objGroup->getId().'">'.htmlentities($objGroup->getName(), ENT_QUOTES, CONTREXX_CHARSET).' ['.$objGroup->getType().']</option>';

            if (/*$objDownload->getProtection() || */in_array($objGroup->getId(), $arrAssociatedGroups)) {
                $arrAssociatedGroupOptions[] = $option;
            } else {
                $arrNotAssociatedGroupOptions[] = $option;
            }

            $objGroup->next();
        }

        $this->_objTpl->setVariable(array(
            'DOWNLOADS_DOWNLOAD_ACCESS_ALL_CHECKED'              => !$objDownload->getProtection() ? 'checked="checked"' : '',
            'DOWNLOADS_DOWNLOAD_ACCESS_SELECTED_CHECKED'         => $objDownload->getProtection() ? 'checked="checked"' : '',
            'DOWNLOADS_DOWNLOAD_ACCESS_DISPLAY'                  => $objDownload->getProtection() ? '' : 'none',
            'DOWNLOADS_DOWNLOAD_ACCESS_ASSOCIATED_GROUPS'        => implode("\n", $arrAssociatedGroupOptions),
            'DOWNLOADS_DOWNLOAD_ACCESS_NOT_ASSOCIATED_GROUPS'    => implode("\n", $arrNotAssociatedGroupOptions),
            'DOWNLOADS_DOWNLOAD_VISIBILITY_CHECKED'              => $objDownload->getVisibility() ? 'checked="checked"' : ''
        ));


        // parse cancel link
        $this->_objTpl->setVariable(array(
            'DOWNLOADS_DOWNLOAD_CANCEL_LINK_SECITON'    => $categoryId ? 'categories' : 'downloads',
            'DOWNLOADS_PARENT_CATEGORY_ID'              => $categoryId
        ));
    }

//    /**
//     * download edit
//     *
//     * @global object $objDatabase
//     * @global array $_ARRAYLANG
//     */
//    function _download()
//    {
//        global $_ARRAYLANG, $objDatabase;
//        $this->_pageTitle = $_ARRAYLANG['TXT_DOWNLOADS_EDIT_DOWNLOAD'];
//        $this->_objTpl->loadTemplateFile('download.html');
//
//        $download_id = intval($_REQUEST["id"]);
//        $DownloadInfo = $this->_FileInfo($download_id);
//
//        if ($DownloadInfo["file_name"]!="") {
//            $RadioFile_1 = "checked";
//        } else {
//            $RadioFile_2 = "checked";
//        }
//
//        $languageTabsSource = '';
//        $languageTabsNavi = '';
//        $fieldsArray = array();
//        $LiClass = 'active';
//        $StyleDisplay = 'block';
//        foreach ($this->_arrLang as $langId => $LangInfo) {
//            $fieldsArray = array('file_name_'.$langId => array('name' => $_ARRAYLANG['TXT_NAME'], 'value' => $DownloadInfo['file_loc']['lang'][$langId]['name'], 'rte' => 0), 'file_desc_'.$langId => array('name' => $_ARRAYLANG['TXT_DESCRIPTION'], 'value' => $DownloadInfo['file_loc']['lang'][$langId]['desc'], 'rte' => 2));
//            $languageTabsSource .= $this->_LangTabHTML($LangInfo['name'], 'display: '.$StyleDisplay.';', $LangInfo['name'], $fieldsArray);
//            $languageTabsNavi .= '<li><a id="addEntry_'.$LangInfo['name'].'" class="'.$LiClass.'" href="javascript:{}" onclick="selectTab(\''.$LangInfo['name'].'\')" title="'.$LangInfo['name'].'" style="display: inline;">'.$LangInfo['name'].'</a></li>';
//            $LiClass = 'inactive';
//            $StyleDisplay = 'none';
//// TODO: Never used
////            $js_arr .= 'arrTabToDiv["addEntry_'.$LangInfo['name'].'"] = "'.$LangInfo['name'].'"; ';
//        }
//        $languageTabsNavi = '<ul id="tabmenu">'.$languageTabsNavi.'</ul>';
//
//        $TypeSelected_1 = '';
//        $TypeSelected_2 = '';
//        $TypeSelected_3 = '';
//        $TypeSelected_4 = '';
//        switch ($DownloadInfo["file_type"]) {
//            case "image":
//                $TypeSelected_1 = 'selected';
//                break;
//            case "text":
//                $TypeSelected_2 = 'selected';
//                break;
//            case "media":
//                $TypeSelected_3 = 'selected';
//                break;
//            case "archive":
//                $TypeSelected_4 = 'selected';
//                break;
//        }
//
//        $ValueCategories = '';
//        $ValueAddedCategories = '';
//
//        $query = "
//            SELECT category_id
//            FROM ".DBPREFIX."module_downloads_categories
//            ORDER BY category_order";
//        $objResult = $objDatabase->Execute($query);
//
//        if ($objResult) {
//            while (!$objResult->EOF) {
//                $CategoryInfo = $this->_CategoryInfo($objResult->fields["category_id"]);
//                $added = false;
//                for($xx=0; $xx<count($DownloadInfo['file_categories']); $xx++) {
//                    if ($DownloadInfo['file_categories'][$xx]['id']==$objResult->fields["category_id"]) {
//                        $added = true;
//                    }
//                }
//                if ($added) {
//                    $ValueAddedCategories .= '<option value="'.$objResult->fields["category_id"].'">'.$CategoryInfo['category_loc'][0]['name'].'</option>\n';
//                } else {
//                    $ValueCategories .= '<option value="'.$objResult->fields["category_id"].'">'.$CategoryInfo['category_loc'][0]['name'].'</option>\n';
//                }
//                $objResult->MoveNext();
//            }
//        }
//
//        $protected_yes = '';
//        $protected_no = '';
//        if ($DownloadInfo["file_protected"]==1) {
//            $protected_yes = 'checked';
//        } else {
//            $protected_no = 'checked';
//        }
//
//        // --------------
//        // Frontend Groups
//        $valueGroups = '';
//        $valueAddedGroups = '';
//        //$arrAssignedFrontendGroups=$this->_getAssignedGroups($groupType="frontend",$pageId);
//
//        $objFWUser = FWUser::getFWUserObject();
//        $objGroup = $objFWUser->objGroup->getGroups(array('type' => 'frontend'));
//        while (!$objGroup->EOF) {
//            $added = false;
//            for($xx=0; $xx<count($DownloadInfo['file_access_groups']); $xx++) {
//                if ($DownloadInfo['file_access_groups'][$xx]['id']==$objGroup->getId()) {
//                    $added = true;
//                }
//            }
//            if ($added) {
//                $valueAddedGroups .="<option value=\"".$objGroup->getId()."\">".htmlentities($objGroup->getName(), ENT_QUOTES, CONTREXX_CHARSET)."</option>\n";
//            } else {
//                $valueGroups .="<option value=\"".$objGroup->getId()."\">".htmlentities($objGroup->getName(), ENT_QUOTES, CONTREXX_CHARSET)."</option>\n";
//            }
//
//            $objGroup->next();
//        }
//
//        // related downloads
//        // ------------------------
//        $query = "
//                SELECT file_id, file_name
//                FROM ".DBPREFIX."module_downloads_files
//                ORDER BY file_name";
//        $objResult = $objDatabase->Execute($query);
//        $ValueDownloads = '';
//        $ValueAddedDownloads = '';
//        if ($objResult) {
//            while (!$objResult->EOF) {
//                $fileInfo = $this->_FileInfo($objResult->fields["file_id"]);
//                $added = false;
//                for($xx=0; $xx<count($DownloadInfo['file_related_files']); $xx++) {
//                    if ($DownloadInfo['file_related_files'][$xx]['id']==$objResult->fields["file_id"]) {
//                        $added = true;
//                    }
//                }
//
//                $fileName = $fileInfo["file_name"];
//                if ($fileName=="") {
//                    $fileName = $fileInfo["file_url"];
//                }
//
//                if ($added) {
//                    $ValueAddedDownloads .= '<option value="'.$fileInfo["file_id"].'">'.$fileInfo["file_loc"][0]["name"].'</option>';
//                } else {
//                    if ($fileInfo["file_id"]!=$DownloadInfo["file_id"]) {
//                        $ValueDownloads .= '<option value="'.$fileInfo["file_id"].'">'.$fileInfo["file_loc"][0]["name"].'</option>';
//                    }
//                }
//                $objResult->MoveNext();
//            }
//        }
//
//        if ($DownloadInfo["file_name"]=="") {
//            $js_fileswitch = "FileSwitch('DIVfile_url', 'DIVfile_source');";
//        }
//
//        if ($DownloadInfo["file_protected"]==0) {
//            $js_groupselect = 'HideGroupSelect();';
//        }
//
//        if ($DownloadInfo["file_state"]==1) {
//            $state_checked = 'checked';
//        } else {
//            $state_checked = '';
//        }
//
//        $this->_objTpl->setVariable(array(
//            'TXT_DOWNLOADS_EDIT_DOWNLOAD' => $_ARRAYLANG["TXT_DOWNLOADS_EDIT_DOWNLOAD"],
//            'TXT_MANAGE_FILES' => $_ARRAYLANG['TXT_MANAGE_FILES'],
//            'TXT_ADD_FILE' => $_ARRAYLANG['TXT_ADD_FILE'],
//            'TXT_DOWNLOADS_MANAGE_DOWNLOADS' => $_ARRAYLANG['TXT_DOWNLOADS_MANAGE_DOWNLOADS'],
//            'TXT_DOWNLOADS_ADD_DOWNLOAD' => $_ARRAYLANG['TXT_DOWNLOADS_ADD_DOWNLOAD'],
//            'TXT_AUTHOR' => $_ARRAYLANG['TXT_DOWNLOADS_AUTHOR'],
//            'TXT_DOWNLOADS_FILE' => $_ARRAYLANG['TXT_DOWNLOADS_FILE'],
//            'TXT_IMAGE' => $_ARRAYLANG['TXT_IMAGE'],
//            'TXT_BROWSE' => $_ARRAYLANG['TXT_BROWSE'],
//            'TXT_LANGUAGES' => $_ARRAYLANG['TXT_LANGUAGES'],
//            'TXT_DOWNLOADS_PERMISSIONS' => $_ARRAYLANG['TXT_DOWNLOADS_PERMISSIONS'],
//            'TXT_SAVE' => $_ARRAYLANG['TXT_SAVE'],
//            'TXT_SEARCH' => $_ARRAYLANG['TXT_SEARCH'],
//            'TXT_DOWNLOADS_CATEGORIES' => $_ARRAYLANG['TXT_DOWNLOADS_CATEGORIES'],
//            'TXT_DOWNLOADS_CATEGORY' => $_ARRAYLANG['TXT_DOWNLOADS_CATEGORY'],
//            'TXT_DOWNLOADS_ADDED_CATEGORIES' => $_ARRAYLANG['TXT_DOWNLOADS_ADDED_CATEGORIES'],
//            'TXT_DOWNLOADS_FILES' => $_ARRAYLANG['TXT_DOWNLOADS_FILES'],
//            'TXT_DOWNLOADS_STATUS' => $_ARRAYLANG['TXT_DOWNLOADS_STATUS'],
//            'TXT_DOWNLOADS_TYPE' => $_ARRAYLANG['TXT_DOWNLOADS_TYPE'],
//            'TXT_FUNCTIONS' => $_ARRAYLANG['TXT_FUNCTIONS'],
//            'TXT_MARKED' => $_ARRAYLANG['TXT_MARKED'],
//            'TXT_SELECT_ALL' => $_ARRAYLANG['TXT_SELECT_ALL'],
//            'TXT_REMOVE_SELECTION' => $_ARRAYLANG['TXT_REMOVE_SELECTION'],
//            'TXT_SELECT_ACTION' => $_ARRAYLANG['TXT_SELECT_ACTION'],
//            'TXT_DELETE' => $_ARRAYLANG['TXT_DELETE'],
//            'TXT_CONFIRM_DELETE' => $_ARRAYLANG['TXT_CONFIRM_DELETE'],
//            'TXT_ACTION_IS_IRREVERSIBLE' => $_ARRAYLANG['TXT_ACTION_IS_IRREVERSIBLE'],
//            'TXT_DOWNLOADS_TYPE_IMAGE' => $_ARRAYLANG['TXT_DOWNLOADS_TYPE_IMAGE'],
//            'TXT_DOWNLOADS_TYPE_TEXT' => $_ARRAYLANG['TXT_DOWNLOADS_TYPE_TEXT'],
//            'TXT_DOWNLOADS_TYPE_MEDIA' => $_ARRAYLANG['TXT_DOWNLOADS_TYPE_MEDIA'],
//            'TXT_DOWNLOADS_TYPE_ARCHIVE' => $_ARRAYLANG['TXT_DOWNLOADS_TYPE_ARCHIVE'],
//            'TXT_DOWNLOADS_TYPE_APPLICATION'=> $_ARRAYLANG['TXT_DOWNLOADS_TYPE_APPLICATION'],
//            'TXT_DOWNLOADS_FILEINFO' => $_ARRAYLANG['TXT_DOWNLOADS_FILEINFO'],
//            'TXT_DOWNLOADS_SIZE' => $_ARRAYLANG['TXT_DOWNLOADS_SIZE'],
//            'TXT_DOWNLOADS_LICENSE' => $_ARRAYLANG['TXT_DOWNLOADS_LICENSE'],
//            'TXT_DOWNLOADS_VERSION' => $_ARRAYLANG['TXT_DOWNLOADS_VERSION'],
//            'TXT_DOWNLOADS_TYPE_UNDEFINED' => $_ARRAYLANG['TXT_DOWNLOADS_TYPE_UNDEFINED'],
//            'TXT_DOWNLOADS_RELATED_DOWNLOADS' => $_ARRAYLANG['TXT_DOWNLOADS_RELATED_DOWNLOADS'],
//            'TXT_DOWNLOADS_PROTECTED_DOWNLOAD' => $_ARRAYLANG['TXT_DOWNLOADS_PROTECTED_DOWNLOAD'],
//            'TXT_DOWNLOADS_YES' => $_ARRAYLANG['TXT_DOWNLOADS_YES'],
//            'TXT_DOWNLOADS_NO' => $_ARRAYLANG['TXT_DOWNLOADS_NO'],
//            'DOWNLOAD_ID' => $download_id,
//            'RADIO_FILE_1' => $RadioFile_1,
//            'RADIO_FILE_2' => $RadioFile_2,
//            'DOWNLOAD_FILENAME' => $DownloadInfo["file_name"],
//            'DOWNLOAD_URL' => $DownloadInfo["file_url"],
//            'LANG_TABS' => $languageTabsSource,
//            'LANG_TAB_NAVI' => $languageTabsNavi,
//            'DOWNLOAD_TYPE_SELECTED_1' => $TypeSelected_1,
//            'DOWNLOAD_TYPE_SELECTED_2' => $TypeSelected_2,
//            'DOWNLOAD_TYPE_SELECTED_3' => $TypeSelected_3,
//            'DOWNLOAD_TYPE_SELECTED_4' => $TypeSelected_4,
//            'DOWNLOAD_FILE_SIZE' => $DownloadInfo["file_size"],
//            'DOWNLOAD_FILE_LICENSE' => $DownloadInfo["file_license"],
//            'DOWNLOAD_FILE_VERSION' => $DownloadInfo["file_version"],
//            'DOWNLOAD_FILE_AUTOR' => $DownloadInfo["file_autor"],
//            'DOWNLOAD_FILE_IMG' => $DownloadInfo["file_img"],
//            'VALUE_CATEGORIES' => $ValueCategories,
//            'VALUE_ADDED_CATEGORIES' => $ValueAddedCategories,
//            'DOWNLOAD_FILE_PROTECTED_YES' => $protected_yes,
//            'DOWNLOAD_FILE_PROTECTED_NO' => $protected_no,
//            'VALUE_GROUPS' => $valueGroups,
//            'VALUE_ADDED_GROUPS' => $valueAddedGroups,
//            'VALUE_DOWNLOADS' => $ValueDownloads,
//            'VALUE_ADDED_DOWNLOADS' => $ValueAddedDownloads,
//            'JS_FILESWITCH' => $js_fileswitch,
//            'JS_GROUPSELECT' => $js_groupselect,
//            'TXT_DOWNLOADS_AVAILABLE_USER_GROUPS' => $_ARRAYLANG['TXT_DOWNLOADS_AVAILABLE_USER_GROUPS'],
//            'TXT_DOWNLOADS_ASSIGNED_USER_GROUPS' => $_ARRAYLANG['TXT_DOWNLOADS_ASSIGNED_USER_GROUPS'],
//            'TXT_DOWNLOADS_SOURCE' => $_ARRAYLANG['TXT_DOWNLOADS_SOURCE'],
//            'TXT_DOWNLOADS_AVAILABLE_DOWNLOADS' => $_ARRAYLANG['TXT_DOWNLOADS_AVAILABLE_DOWNLOADS'],
//            'TXT_DOWNLOADS_ASSIGNED_DOWNLOADS' => $_ARRAYLANG['TXT_DOWNLOADS_ASSIGNED_DOWNLOADS'],
//            'STATE_CHECKED' => $state_checked,
//        ));
//    }


//    /**
//     * files
//     *
//     * @global object $objDatabase
//     * @global array $_ARRAYLANG
//     */
//    function _files()
//    {
//        global $_ARRAYLANG, $objDatabase;
//
//        $this->_pageTitle = $_ARRAYLANG['TXT_DOWNLOADS_MANAGE_DOWNLOADS'];
//        $this->_objTpl->loadTemplateFile('files.html');
//
//        if ($_REQUEST["deletefiles"]=="exe") {
//            $Deleted = true;
//            for($i=0; $i<=count($_REQUEST["selectedFileId"]); $i++) {
//                if (intval($_REQUEST["selectedFileId"][$i])>0) {
//                    if (!$this->_DeleteDownload(intval($_REQUEST["selectedFileId"][$i]))) {
//                        $Deleted = false;
//                    }
//                }
//            }
//            if ($Deleted) {
//                $this->arrStatusMsg['ok'][] = $_ARRAYLANG['TXT_DOWNLOADS_DELETE_SUCCESSFULL'];
//            } else {
//                $this->arrStatusMsg['error'][] = $_ARRAYLANG['TXT_DOWNLOADS_DELETE_FAILED'];
//            }
//        }
//
//        if (isset($_REQUEST["mode"])) {
//
//            // INSERT
//            // ---------------------------------
//            if ($_REQUEST["mode"] == "insert") {
//                $InserFile = $this->InsertFile();
//                if ($InserFile) {
//                    $this->arrStatusMsg['ok'][] = $_ARRAYLANG['TXT_DOWNLOADS_ADD_SUCCESSFULL'];
//                } else {
//                    $this->arrStatusMsg['error'][] = $_ARRAYLANG['TXT_DOWNLOADS_ADD_FAILED'];
//                }
//            }
//
//            // UPDATE
//            // ---------------------------------
//            if ($_REQUEST["mode"] == "update") {
//                $UpdateDownload = $this->UpdateDownload();
//                if ($UpdateDownload) {
//                    $this->arrStatusMsg['ok'][] = $_ARRAYLANG['TXT_DOWNLOADS_UPDATE_SUCCESSFULL'];
//                } else {
//                    $this->arrStatusMsg['error'][] = $_ARRAYLANG['TXT_DOWNLOADS_UPDATE_FAILED'];
//                }
//            }
//
//            // DELETE
//            // ---------------------------------
//            if ($_REQUEST["mode"] == "delete") {
//                if (intval($_REQUEST["download"])>0) {
//                    if ($this->_DeleteDownload(intval($_REQUEST["download"]))) {
//                        $this->arrStatusMsg['ok'][] = $_ARRAYLANG['TXT_DOWNLOADS_DELETE_SUCCESSFULL'];
//                    } else {
//                        $this->arrStatusMsg['error'][] = $_ARRAYLANG['TXT_DOWNLOADS_DELETE_FAILED'];
//                    }
//                }
//            }
//        }
//
//        $checkboxesSource = '';
//        $languageTabsSource = '';
//        $languageTabsNavi = '';
//        $fieldsArray = array();
//        $LiClass = 'active';
//        $StyleDisplay = 'block';
//        $LangSelectValue = '';
//        foreach ($this->_arrLang as $langId => $LangInfo) {
//            $fieldsArray = array('file_name_'.$langId => array('name' => $_ARRAYLANG['TXT_NAME'], 'value' => '', 'rte' => 0), 'file_desc_'.$langId => array('name' => $_ARRAYLANG['TXT_DESCRIPTION'], 'value' => '', 'rte' => 2));
//            $languageTabsSource .= $this->_LangTabHTML($LangInfo['name'], 'display: '.$StyleDisplay.';', $LangInfo['name'], $fieldsArray);
//            $languageTabsNavi .= '<li><a id="addEntry_'.$LangInfo['name'].'" class="'.$LiClass.'" href="javascript:{}" onclick="selectTab(\''.$LangInfo['name'].'\')" title="'.$LangInfo['name'].'" style="display: inline;">'.$LangInfo['name'].'</a></li>';
//            $checkboxesSource .= '<td><input checked="checked" name="frmEditEntry_Languages[]" value="'.$langId.'" onclick="switchBoxAndTab(this, \'addEntry_'.$LangInfo['name'].'\');" type="checkbox" />'.$LangInfo['name'].' ['.$LangInfo['lang'].']</td>';
//            $LiClass = 'inactive';
//            $StyleDisplay = 'none';
//// TODO: Never used
////            $js_arr .= 'arrTabToDiv["addEntry_'.$LangInfo['name'].'"] = "'.$LangInfo['name'].'"; ';
//
//            $LangSelectValue .= '<option value="'.$langId.'">'.$LangInfo['name'].'</option>';
//
//        }
//        $languageTabsNavi = '<ul id="tabmenu">'.$languageTabsNavi.'</ul>';
//        $GroupsSelect = $this->_permissionsSelect('AddFileForm');
//
//        $ValueCategories = '';
//
//        $query = "
//            SELECT category_id
//            FROM ".DBPREFIX."module_downloads_categories
//            ORDER BY category_order";
//        $objResult = $objDatabase->Execute($query);
//
//        if ($objResult) {
//            while (!$objResult->EOF) {
//                $CategoryInfo = $this->_CategoryInfo($objResult->fields["category_id"]);
//
//                if (intval($_REQUEST["category"])==$objResult->fields["category_id"]) {
//                    $selectedtext = 'selected';
//                } else {
//                    $selectedtext = '';
//                }
//
//                $ValueCategories .= '<option value="'.$objResult->fields["category_id"].'" '.$selectedtext.'>'.$CategoryInfo['category_loc'][0]['name'].'</option>\n';
//                $objResult->MoveNext();
//            }
//        }
//
//        $CategoriesSelect = '<select name="category" style="width: 300px;">';
//        $CategoriesSelect .= '<option value=""> --- '.$_ARRAYLANG['TXT_DOWNLOADS_CATEGORY'].' --- </option>';
//        $CategoriesSelect .= $ValueCategories;
//        $CategoriesSelect .= '</select>';
//
//        $LangSelect = '<select name="lang" style="width: 300px;">';
//        $LangSelect .= '<option value=""> --- '.$_ARRAYLANG['TXT_DOWNLOADS_LANGUAGE'].' --- </option>';
//        $LangSelect .= $LangSelectValue;
//        $LangSelect .= '</select>';
//
//        // Files list
//        // ---------------------------------
//        if (intval($_REQUEST["category"])>0) {
//            $query = "
//                SELECT rel_file, rel_category, file_id, file_name
//                FROM ".DBPREFIX."module_downloads_rel_files_cat
//                JOIN ".DBPREFIX."module_downloads_files ON ".DBPREFIX."module_downloads_rel_files_cat.rel_file=".DBPREFIX."module_downloads_files.file_id
//                WHERE rel_category=".intval($_REQUEST["category"])."
//                ORDER BY file_name";
//        } else {
//            $query = "
//                SELECT file_id, file_name, rel_file
//                FROM ".DBPREFIX."module_downloads_files
//                LEFT JOIN ".DBPREFIX."module_downloads_rel_files_cat ON ".DBPREFIX."module_downloads_files.file_id=".DBPREFIX."module_downloads_rel_files_cat.rel_file
//                WHERE rel_file is NULL
//                ORDER BY file_name";
//        }
//
//        $objResult = $objDatabase->Execute($query);
//        $ValueDownloads = '';
//        if ($objResult) {
//            $this->_objTpl->setCurrentBlock('filesList');
//            $i = 0;
//            while (!$objResult->EOF) {
//
//                $fileInfo = $this->_FileInfo($objResult->fields["file_id"]);
//
//                $ValueDownloads .= '<option value="'.$fileInfo["file_id"].'">'.$fileInfo["file_loc"][0]["name"].' ('.$fileInfo["file_name"].')</option>';
//
//                $fileName = $fileInfo["file_name"];
//                if ($fileInfo["file_name"]=="") {
//                    $fileName = $fileInfo["file_url"];
//                }
//
//                if ($fileInfo["file_state"]==1) {
//                    $file_state = '<img src="/cadmin/images/icons/status_green.gif" border="0" alt="'.$_ARRAYLANG['TXT_DOWNLOADS_STATUS'].'" />';
//                } else {
//                    $file_state = '<img src="/cadmin/images/icons/status_red.gif" border="0" alt="'.$_ARRAYLANG['TXT_DOWNLOADS_STATUS'].'" />';
//                }
//
//                $this->_objTpl->setVariable(array(
//                        'ROWCLASS' =>($i % 2) ? 'row1' : 'row2',
//                        'FILE_ID' => $fileInfo["file_id"],
//                        'FILE_NAME' => $fileInfo["file_loc"][0]["name"]."",
//                        'FILE_TYPE' => $fileInfo["file_type"],
//                        'FILE_AUTOR' => $fileInfo["file_autor"],
//                        'FILE_SOURCE' => $fileName,
//                        'FILE_STATE' => $file_state,
//                ));
//                $this->_objTpl->parse('filesList');
//                $i++;
//                $objResult->MoveNext();
//            }
//        }
//
//        // Value Downloads
//        // ---------------------------------
//         $ValueDownloads = '';
//         $query = "
//                SELECT file_id, file_name
//                FROM ".DBPREFIX."module_downloads_files WHERE file_state=1
//                ORDER BY file_name, file_type";
//        $objResult = $objDatabase->Execute($query);
//         if ($objResult) {
//             while (!$objResult->EOF) {
//                 $fileInfo = $this->_FileInfo($objResult->fields["file_id"]);
//                 $ValueDownloads .= '<option value="'.$fileInfo["file_id"].'">'.$fileInfo["file_loc"][0]["name"].'</option>';
//                 $objResult->MoveNext();
//             }
//         }
//
//        // Frontend Groups
//        $valueGroups = '';
//        //$arrAssignedFrontendGroups=$this->_getAssignedGroups($groupType="frontend",$pageId);
//        $objFWUser = FWUser::getFWUserObject();
//        $objGroup = $objFWUser->objGroup->getGroups(array('type' => 'frontend'));
//        while (!$objGroup->EOF) {
//            $valueGroups.="<option value=\"".$objGroup->getId()."\">".htmlentities($objGroup->getName(), ENT_QUOTES, CONTREXX_CHARSET)."</option>\n";
//            $objGroup->next();
//        }
//
//        $this->_objTpl->setVariable(array(
//            'TXT_MANAGE_FILES' => $_ARRAYLANG['TXT_MANAGE_FILES'],
//            'TXT_ADD_FILE' => $_ARRAYLANG['TXT_ADD_FILE'],
//            'TXT_DOWNLOADS_MANAGE_DOWNLOADS' => $_ARRAYLANG['TXT_DOWNLOADS_MANAGE_DOWNLOADS'],
//            'TXT_DOWNLOADS_ADD_DOWNLOAD' => $_ARRAYLANG['TXT_DOWNLOADS_ADD_DOWNLOAD'],
//            'TXT_AUTHOR' => $_ARRAYLANG['TXT_DOWNLOADS_AUTHOR'],
//            'TXT_DOWNLOADS_FILE' => $_ARRAYLANG['TXT_DOWNLOADS_FILE'],
//            'TXT_IMAGE' => $_ARRAYLANG['TXT_IMAGE'],
//            'TXT_BROWSE' => $_ARRAYLANG['TXT_BROWSE'],
//            'TXT_LANGUAGES' => $_ARRAYLANG['TXT_LANGUAGES'],
//            'TXT_DOWNLOADS_PERMISSIONS' => $_ARRAYLANG['TXT_DOWNLOADS_PERMISSIONS'],
//            'TXT_SAVE' => $_ARRAYLANG['TXT_SAVE'],
//            'TXT_SEARCH' => $_ARRAYLANG['TXT_SEARCH'],
//            'TXT_DOWNLOADS_CATEGORIES' => $_ARRAYLANG['TXT_DOWNLOADS_CATEGORIES'],
//            'TXT_DOWNLOADS_CATEGORY' => $_ARRAYLANG['TXT_DOWNLOADS_CATEGORY'],
//            'TXT_DOWNLOADS_ADDED_CATEGORIES' => $_ARRAYLANG['TXT_DOWNLOADS_ADDED_CATEGORIES'],
//            'TXT_DOWNLOADS_FILES' => $_ARRAYLANG['TXT_DOWNLOADS_FILES'],
//            'TXT_DOWNLOADS_STATUS' => $_ARRAYLANG['TXT_DOWNLOADS_STATUS'],
//            'TXT_DOWNLOADS_TYPE' => $_ARRAYLANG['TXT_DOWNLOADS_TYPE'],
//            'TXT_FUNCTIONS' => $_ARRAYLANG['TXT_FUNCTIONS'],
//            'TXT_MARKED' => $_ARRAYLANG['TXT_MARKED'],
//            'TXT_SELECT_ALL' => $_ARRAYLANG['TXT_SELECT_ALL'],
//            'TXT_REMOVE_SELECTION' => $_ARRAYLANG['TXT_REMOVE_SELECTION'],
//            'TXT_SELECT_ACTION' => $_ARRAYLANG['TXT_SELECT_ACTION'],
//            'TXT_DELETE' => $_ARRAYLANG['TXT_DELETE'],
//            'TXT_CONFIRM_DELETE' => $_ARRAYLANG['TXT_CONFIRM_DELETE'],
//            'TXT_ACTION_IS_IRREVERSIBLE'=> $_ARRAYLANG['TXT_ACTION_IS_IRREVERSIBLE'],
//            'TXT_DOWNLOADS_TYPE_IMAGE' => $_ARRAYLANG['TXT_DOWNLOADS_TYPE_IMAGE'],
//            'TXT_DOWNLOADS_TYPE_TEXT' => $_ARRAYLANG['TXT_DOWNLOADS_TYPE_TEXT'],
//            'TXT_DOWNLOADS_TYPE_MEDIA' => $_ARRAYLANG['TXT_DOWNLOADS_TYPE_MEDIA'],
//            'TXT_DOWNLOADS_TYPE_ARCHIVE'=> $_ARRAYLANG['TXT_DOWNLOADS_TYPE_ARCHIVE'],
//            'TXT_DOWNLOADS_TYPE_APPLICATION'=> $_ARRAYLANG['TXT_DOWNLOADS_TYPE_APPLICATION'],
//            'TXT_DOWNLOADS_FILEINFO' => $_ARRAYLANG['TXT_DOWNLOADS_FILEINFO'],
//            'TXT_DOWNLOADS_SIZE' => $_ARRAYLANG['TXT_DOWNLOADS_SIZE'],
//            'TXT_DOWNLOADS_LICENSE' => $_ARRAYLANG['TXT_DOWNLOADS_LICENSE'],
//            'TXT_DOWNLOADS_VERSION' => $_ARRAYLANG['TXT_DOWNLOADS_VERSION'],
//            'TXT_DOWNLOADS_TYPE_UNDEFINED' => $_ARRAYLANG['TXT_DOWNLOADS_TYPE_UNDEFINED'],
//            'TXT_DOWNLOADS_RELATED_DOWNLOADS' => $_ARRAYLANG['TXT_DOWNLOADS_RELATED_DOWNLOADS'],
//            'TXT_DOWNLOADS_PROTECTED_DOWNLOAD' => $_ARRAYLANG['TXT_DOWNLOADS_PROTECTED_DOWNLOAD'],
//            'TXT_DOWNLOADS_YES' => $_ARRAYLANG['TXT_DOWNLOADS_YES'],
//            'TXT_DOWNLOADS_NO' => $_ARRAYLANG['TXT_DOWNLOADS_NO'],
//            'LANG_SELECT' => $checkboxesSource,
//            'LANG_TABS' => $languageTabsSource,
//            'LANG_TAB_NAVI' => $languageTabsNavi,
//            'GROUP_SELECT' => $GroupsSelect,
//            'VALUE_CATEGORIES' => $ValueCategories,
//// TODO: Undefined
////            'VALUE_ADDED_CATEGORIES' => $ValueAddedCategories,
//            'CATEGORY_SELECT' => $CategoriesSelect,
//            'LANGUAGE_SELECT' => $LangSelect,
//            'VALUE_DOWNLOADS' => $ValueDownloads,
//            'VALUE_GROUPS' => $valueGroups,
//            'VALUE_ADDED_GROUPS' => '',
//            'VALUE_USER' => $_SESSION['auth']['username'],
//            'TXT_DOWNLOADS_AVAILABLE_USER_GROUPS' => $_ARRAYLANG['TXT_DOWNLOADS_AVAILABLE_USER_GROUPS'],
//            'TXT_DOWNLOADS_ASSIGNED_USER_GROUPS' => $_ARRAYLANG['TXT_DOWNLOADS_ASSIGNED_USER_GROUPS'],
//            'TXT_DOWNLOADS_SOURCE' => $_ARRAYLANG['TXT_DOWNLOADS_SOURCE'],
//            'TXT_DOWNLOADS_AVAILABLE_DOWNLOADS' => $_ARRAYLANG['TXT_DOWNLOADS_AVAILABLE_DOWNLOADS'],
//            'TXT_DOWNLOADS_ASSIGNED_DOWNLOADS' => $_ARRAYLANG['TXT_DOWNLOADS_ASSIGNED_DOWNLOADS'],
//            'TXT_DOWNLOADS_STATUS' => $_ARRAYLANG['TXT_DOWNLOADS_STATUS'],
//        ));
//    }


    private function updateCategoryOrder($parentCategoryId, $arrCategoryOrder)
    {
        global $_LANGID, $_ARRAYLANG;

        // TODO: check subcategory manage access permission of $parentCategoryId

        $arrFailedCategories = array();

        foreach ($arrCategoryOrder as $categoryId => $orderNr) {
            $objCategory = Category::getCategory($categoryId);
            if (!$objCategory->EOF) {
                $objCategory->setOrder($orderNr);
                if (!$objCategory->store()) {
                    $arrFailedCategories[] = htmlentities($objCategory->getName($_LANGID), ENT_QUOTES, CONTREXX_CHARSET);
                }
            }
        }

        if (count($arrFailedCategories)) {
            $this->arrStatusMsg['error'][] = sprintf($_ARRAYLANG['TXT_DOWNLOADS_CATEGORY_ORDER_SET_FAILED'], '<strong>'.implode(', ', $arrFailedCategories).'</strong>');
        } else {
            $this->arrStatusMsg['ok'][] = $_ARRAYLANG['TXT_DOWNLOADS_CATEGORY_ORDER_SET_SUCCESS'];
        }
    }

    private function loadCategoryNavigation()
    {
        $this->_objTpl->loadTemplateFile('module_downloads_category.html');
    }

    private function parseCategoryNavigation()
    {
        global $_ARRAYLANG;

        $this->_objTpl->setVariable(array(
            'TXT_DOWNLOADS_OVERVIEW'    => $_ARRAYLANG['TXT_DOWNLOADS_OVERVIEW'],
            'TXT_ADD_CATEGORY'          => $_ARRAYLANG['TXT_ADD_CATEGORY'],
            'DOWNLOADS_NAV_CATEGORY_ID' => $this->parentCategoryId
        ));

        $this->_objTpl->parse('module_downloads_categories');
    }
    /**
     * categories list
     *
     * @global object $objDatabase
     * @global array $_ARRAYLANG
     */
    private function categories()
    {
        global $_ARRAYLANG, $_LANGID, $_CONFIG;

        $objCategory = Category::getCategory($this->parentCategoryId);

        // check access permission
        if (// managers are allowed to see the content of every category
            !Permission::checkAccess(142, 'static', true)
            && $objCategory->getReadAccessId()
            && !Permission::checkAccess($objCategory->getReadAccessId(), 'dynamic', true)
            && (($objFWUser = FWUser::getFWUserObject()) == false || !$objFWUser->objUser->login() || $objCategory->getOwnerId() != $objFWUser->objUser->getId())
        ) {
            return Permission::noAccess();
        }


        // TODO: clean up
        $this->_pageTitle = $_ARRAYLANG['TXT_DOWNLOADS_CATEGORIES'];
        $this->_objTpl->addBlockFile('DOWNLOADS_CATEGORY_TEMPLATE', 'module_downloads_categories', 'module_downloads_categories.html');

        $filter = array();
        $minColspan = 6;

        if (isset($_POST['downloads_category_select_action'])) {
            switch ($_POST['downloads_category_select_action']) {
                case 'order':
                    $this->updateCategoryOrder($this->parentCategoryId, isset($_POST['downloads_category_order']) && is_array($_POST['downloads_category_order']) ? $_POST['downloads_category_order'] : array());
                    break;

                case 'delete':
                    $this->deleteCategories(isset($_POST['downloads_category_id']) && is_array($_POST['downloads_category_id']) ? $_POST['downloads_category_id'] : array(), isset($_POST['downloads_category_delete_recursive']) && $_POST['downloads_category_delete_recursive']);
                    break;
            }
        }

        $pos = isset($_GET['pos']) ? intval($_GET['pos']) : 0;

        $categoryLimitOffset = isset($_GET['category_pos']) ? intval($_GET['category_pos']) : $pos;
        $categoryOrderDirection = !empty($_GET['category_sort']) ? $_GET['category_sort'] : 'asc';
        $categoryOrderBy = !empty($_GET['category_by']) ? $_GET['category_by'] : 'order';
        $arrCategoryOrder[$categoryOrderBy] = $categoryOrderDirection;

        if ($categoryOrderBy != 'order') {
            $arrCategoryOrder['order'] = 'asc';
        }
        if ($categoryOrderBy != 'name') {
            $arrCategoryOrder['name'] = 'asc';
        }
        if ($categoryOrderBy != 'id') {
            $arrCategoryOrder['id'] = 'asc';
        }

        $downloadLimitOffset = isset($_GET['download_pos']) ? intval($_GET['download_pos']) : $pos;
        $downloadOrderDirection = !empty($_GET['download_sort']) ? $_GET['download_sort'] : 'asc';
        $downloadOrderBy = !empty($_GET['download_by']) ? $_GET['download_by'] : 'order';


        $objSubcategory = Category::getCategories(array('parent_id' => $objCategory->getId()), null, $arrCategoryOrder, null, $_CONFIG['corePagingLimit'], $categoryLimitOffset);






        $this->_objTpl->setGlobalVariable(array(
            'TXT_DOWNLOADS_EDIT'    => $_ARRAYLANG['TXT_DOWNLOADS_EDIT'],
            'TXT_DOWNLOADS_DELETE'  => $_ARRAYLANG['TXT_DOWNLOADS_DELETE']
        ));

//        // check if user is allowed to add a subcategory
//        if (// managers are allowed to add subcategories
//            Permission::checkAccess(142, 'static', true)
//            // the selected category must be valid to proceed future permission checks.
//            // this is required to protect the overview section from non-admins
//            || $objCategory->getId() && (
//                // the category isn't protected => everyone is allowed to add subcategories
//                !$objCategory->getAddSubcategoriesAccessId()
//                // the category is protected => only those who have the sufficent permissions are allowed to add subcategories
//                || Permission::checkAccess($objCategory->getAddSubcategoriesAccessId(), 'dynamic', true)
//                // the owner is allowed to add subcategories
//                || ($objFWUser = FWUser::getFWUserObject()) && $objFWUser->objUser->login() && $objCategory->getOwnerId() == $objFWUser->objUser->getId()
//            )
//        ) {
//            $this->_objTpl->setVariable(array(
//                'DOWNLOADS_CATEGORY_ID' => $objCategory->getId(),
//                // TODO: rename
//                //'TXT_ADD_CATEGORY'      => $_ARRAYLANG['TXT_ADD_CATEGORY']
//            ));
//            $this->_objTpl->parse('downloads_category_add_buttom');
//        } else {
//            $this->_objTpl->hideBlock('downloads_category_add_buttom');
//        }

        // check of it is allowed to change the sort order
        if (// managers are allowed to manage every subcategory
            Permission::checkAccess(142, 'static', true)
            // the selected category must be valid to proceed future permission checks.
            // this is required to protect the overview section from non-admins
            || $objCategory->getId() && (
                // the category isn't protected => everyone is allowed to modify subcategories
                !$objCategory->getManageSubcategoriesAccessId()
                // the category is protected => only those who have the sufficent permissions are allowed to modify subcategories
                || Permission::checkAccess($objCategory->getManageSubcategoriesAccessId(), 'dynamic', true)
                // the owner is allowed to manage its subcategories
                || $objSubcategory->getModifyAccessByOwner() && ($objFWUser = FWUser::getFWUserObject()) && $objFWUser->objUser->login() && $objCategory->getOwnerId() == $objFWUser->objUser->getId()
            )
        ) {
            $this->_objTpl->setVariable(array(
                'DOWNLOADS_CATEGORY_SORT_ORDER'          => ($categoryOrderBy == 'order' && $categoryOrderDirection == 'asc') ? 'desc' : 'asc',
                'DOWNLOADS_CATEGORY_SORT_ORDER_LABEL'    => $_ARRAYLANG['TXT_DOWNLOADS_ORDER'].($categoryOrderBy == 'order' ? $categoryOrderDirection == 'asc' ? ' &uarr;' : ' &darr;' : '')
            ));
            $this->_objTpl->parse('downloads_category_order_label');
            $changeSortOrder = true;
        } else {
            $changeSortOrder = false;
            $this->_objTpl->hideBlock('downloads_category_order_label');
        }

        // parse select posibilities and dropdown action menu
        if (// managers are allowed to operate on subcategories
            Permission::checkAccess(142, 'static', true)
            // the selected category must be valid to proceed future permission checks.
            // this is required to protect the overview section from non-admins
            || $objCategory->getId() && (
                // the category isn't protected => everyone is allowed to operate on subcategories
                !$objCategory->getManageSubcategoriesAccessId()
                // the category is protected => only those who have the sufficent permissions are allowed to operate on subcategories
                || Permission::checkAccess($objCategory->getManageSubcategoriesAccessId(), 'dynamic', true)
            )
        ) {
            $operateOnSubcategories = true;
        } else {
            $operateOnSubcategories = false;
        }


        // parse add downloads button
        if (// we can only add downloads to a category
            $objCategory->getId() && (
                // managers are allowed to add new downloads
                Permission::checkAccess(142, 'static', true)
                // the category isn't protected => everyone is allowed to add new downloads
                || !$objCategory->getAddFilesAccessId()
                // the category is protected => only those who have the sufficent permissions are allowed to add new downloads
                || Permission::checkAccess($objCategory->getAddFilesAccessId(), 'dynamic', true)
            )
        ) {
            $this->_objTpl->setVariable(array(
                'DOWNLOADS_CATEGORY_ID'                     => $objCategory->getId(),
                'TXT_DOWNLOADS_ADD_DOWNLOADS_TO_CATEGORY'   => $_ARRAYLANG['TXT_DOWNLOADS_ADD_DOWNLOADS_TO_CATEGORY']
            ));
            $this->_objTpl->parse('downloads_add_downloads_button');
        } else {
            $this->_objTpl->hideBlock('downloads_add_downloads_button');
        }



            // rename
            //'TXT_MANAGE_CATEGORIES' => $_ARRAYLANG['TXT_MANAGE_CATEGORIES'],



        $nr = 0;
        if ($objSubcategory->EOF) {
            $this->_objTpl->setVariable('TXT_DOWNLOADS_NO_CATEGORIES_AVAILABLE', $_ARRAYLANG['TXT_DOWNLOADS_NO_CATEGORIES_AVAILABLE']);
            $this->_objTpl->parse('downloads_category_no_data');
            $this->_objTpl->hideBlock('downloads_category_data');
        } else {
            if ($operateOnSubcategories) {
                $this->_objTpl->setVariable(array(
                    'TXT_DOWNLOADS_CHECK_ALL'       => $_ARRAYLANG['TXT_DOWNLOADS_CHECK_ALL'],
                    'TXT_DOWNLOADS_UNCHECK_ALL'     => $_ARRAYLANG['TXT_DOWNLOADS_UNCHECK_ALL'],
                    'TXT_DOWNLOADS_SELECT_ACTION'   => $_ARRAYLANG['TXT_DOWNLOADS_SELECT_ACTION'],
                    'TXT_DOWNLOADS_ORDER'           => $_ARRAYLANG['TXT_DOWNLOADS_ORDER'],
                    'TXT_DOWNLOADS_DELETE'          => $_ARRAYLANG['TXT_DOWNLOADS_DELETE']
                ));
                $this->_objTpl->parse('downloads_category_action_dropdown');
                $this->_objTpl->touchBlock('downloads_category_select_label');
            } else {
                $this->_objTpl->hideBlock('downloads_category_action_dropdown');
                $this->_objTpl->hideBlock('downloads_category_select_label');
            }

            // parse sorting
            $this->_objTpl->setVariable(array(
                'DOWNLOADS_CATEGORY_SORT_PARENT_ID'         => $objCategory->getId(),
                'DOWNLOADS_CATEGORY_SORT_DIRECTION'         => $categoryOrderDirection,
                'DOWNLOADS_CATEGORY_SORT_BY'                => $categoryOrderBy,
                'DOWNLOADS_CATEGORY_SORT_ID'                => ($categoryOrderBy == 'id' && $categoryOrderDirection == 'asc') ? 'desc' : 'asc',
                'DOWNLOADS_CATEGORY_SORT_STATUS'            => ($categoryOrderBy == 'is_active' && $categoryOrderDirection == 'asc') ? 'desc' : 'asc',
                'DOWNLOADS_CATEGORY_SORT_NAME'              => ($categoryOrderBy == 'name' && $categoryOrderDirection == 'asc') ? 'desc' : 'asc',
                'DOWNLOADS_CATEGORY_SORT_DESCRIPTION'       => ($categoryOrderBy == 'description' && $categoryOrderDirection == 'asc') ? 'desc' : 'asc',
                'DOWNLOADS_CATEGORY_SORT_OWNER'             => ($categoryOrderBy == 'author' && $categoryOrderDirection == 'asc') ? 'desc' : 'asc',
                'DOWNLOADS_CATEGORY_SORT_ID_LABEL'          => $_ARRAYLANG['TXT_DOWNLOADS_ID'].($categoryOrderBy == 'id' ? $categoryOrderDirection == 'asc' ? ' &uarr;' : ' &darr;' : ''),
                'DOWNLOADS_CATEGORY_SORT_STATUS_LABEL'      => $_ARRAYLANG['TXT_DOWNLOADS_STATUS'].($categoryOrderBy == 'is_active' ? $categoryOrderDirection == 'asc' ? ' &uarr;' : ' &darr;' : ''),
                'DOWNLOADS_CATEGORY_SORT_NAME_LABEL'        => $_ARRAYLANG['TXT_DOWNLOADS_NAME'].($categoryOrderBy == 'name' ? $categoryOrderDirection == 'asc' ? ' &uarr;' : ' &darr;' : ''),
                'DOWNLOADS_CATEGORY_SORT_DESCRIPTION_LABEL' => $_ARRAYLANG['TXT_DOWNLOADS_DESCRIPTION'].($categoryOrderBy == 'description' ? $categoryOrderDirection == 'asc' ? ' &uarr;' : ' &darr;' : ''),
                'DOWNLOADS_CATEGORY_SORT_OWNER_LABEL'       => $_ARRAYLANG['TXT_DOWNLOADS_OWNER'].($categoryOrderBy == 'owner_id' ? $categoryOrderDirection == 'asc' ? ' &uarr;' : ' &darr;' : ''),
                'DOWNLOADS_CATEGORY_DOWNLOAD_SORT'          => $downloadOrderDirection,
                'DOWNLOADS_CATEGORY_DOWNLOAD_BY'            => $downloadOrderBy,
                'DOWNLOADS_CATEGORY_DOWNLOAD_OFFSET'        => $downloadLimitOffset
            ));

            // parse paging
            $categoryCount = $objSubcategory->getFilteredSearchCategoryCount();
            if ($categoryCount > $_CONFIG['corePagingLimit']) {
                $pagingLink = "&amp;cmd=downloads&amp;act=categories&amp;parent_id=".$objCategory->getId()
                    ."&amp;category_sort=".htmlspecialchars($categoryOrderDirection)
                    ."&amp;category_by=".htmlspecialchars($categoryOrderBy)
                    ."&amp;download_sort=".htmlspecialchars($downloadOrderDirection)
                    ."&amp;download_by=".htmlspecialchars($downloadOrderBy)
                    ."&amp;download_pos=".$downloadLimitOffset;
                $this->_objTpl->setVariable('DOWNLOADS_CATEGORY_PAGING', getPaging($categoryCount, $categoryLimitOffset, $pagingLink, "<b>".$_ARRAYLANG['TXT_DOWNLOADS_CATEGORIES']."</b>"));
            }

            while (!$objSubcategory->EOF) {



                // parse order input box
                if ($changeSortOrder) {
                    $this->_objTpl->setVariable(array(
                        'DOWNLOADS_CATEGORY_ID'     => $objSubcategory->getId(),
                        'DOWNLOADS_CATEGORY_ORDER'  => $objSubcategory->getOrder()
                    ));
                    $this->_objTpl->parse('downloads_category_orderbox');
                } else {
                    $this->_objTpl->hideBlock('downloads_category_orderbox');
                }

                // parse status link and modify button
                if (// managers are allowed to manage every subcategory
                    Permission::checkAccess(142, 'static', true)
                    // the selected category must be valid to proceed future permission checks.
                    // this is required to protect the overview section from non-admins
                    || $objCategory->getId() && (
                        // the category isn't protected => everyone is allowed to modify subcategories
                        !$objCategory->getManageSubcategoriesAccessId()
                        // the category is protected => only those who have the sufficent permissions are allowed to modify subcategories
                        || Permission::checkAccess($objCategory->getManageSubcategoriesAccessId(), 'dynamic', true)
                    )
                    // the owner is allowed to manage its subcategories
                    || $objSubcategory->getOwnerId() && $objSubcategory->getModifyAccessByOwner() && ($objFWUser = FWUser::getFWUserObject()) && $objFWUser->objUser->login() && $objSubcategory->getOwnerId() == $objFWUser->objUser->getId()
                ) {
                    $this->_objTpl->setVariable(array(
                        'DOWNLOADS_CATEGORY_ID'                     => $objSubcategory->getId(),
                        'DOWNLOADS_CATEGORY_PARENT_ID'              => $objCategory->getId(),
                        //'DOWNLOADS_CATEGORY_STATUS_JS'           => $objSubcategory->getActiveStatus(),
                        //'DOWNLOADS_CATEGORY_NAME_JS'             => htmlspecialchars($objSubcategory->getName($_LANGID), ENT_QUOTES, CONTREXX_CHARSET),
                        'DOWNLOADS_CATEGORY_SWITCH_STATUS_DESC'     => $objSubcategory->getActiveStatus() ? $_ARRAYLANG['TXT_DOWNLOADS_DEACTIVATE_CATEGORY_DESC'] : $_ARRAYLANG['TXT_DOWNLOADS_ACTIVATE_CATEGORY_DESC'],
                        'DOWNLOADS_CATEGORY_SWITCH_STATUS_IMG_DESC' => $objSubcategory->getActiveStatus() ? $_ARRAYLANG['TXT_DOWNLOADS_DEACTIVATE_CATEGORY_DESC'] : $_ARRAYLANG['TXT_DOWNLOADS_ACTIVATE_CATEGORY_DESC']
                    ));
                    $this->_objTpl->parse('downloads_category_status_link_open');
                    $this->_objTpl->touchBlock('downloads_category_status_link_close');

                    // parse modify icon
                    $this->_objTpl->setVariable(array(
                        'DOWNLOADS_CATEGORY_ID'         => $objSubcategory->getId(),
                        'DOWNLOADS_CATEGORY_PARENT_ID'  => $objCategory->getId()
                    ));
                    $this->_objTpl->parse('downloads_category_function_modify_link');
                    $this->_objTpl->hideBlock('downloads_category_function_no_modify_link');
                } else {
                    $this->_objTpl->setVariable(array(
                        'DOWNLOADS_CATEGORY_SWITCH_STATUS_DESC'     => $objSubcategory->getActiveStatus() ? $_ARRAYLANG['TXT_DOWNLOADS_ACTIVE'] : $_ARRAYLANG['TXT_DOWNLOADS_INACTIVE'],
                        'DOWNLOADS_CATEGORY_SWITCH_STATUS_IMG_DESC' => $objSubcategory->getActiveStatus() ? $_ARRAYLANG['TXT_DOWNLOADS_ACTIVE'] : $_ARRAYLANG['TXT_DOWNLOADS_INACTIVE']
                    ));
                    $this->_objTpl->hideBlock('downloads_category_status_link_open');
                    $this->_objTpl->hideBlock('downloads_category_status_link_close');

                    // hide modify icon
                    $this->_objTpl->touchBlock('downloads_category_function_no_modify_link');
                    $this->_objTpl->hideBlock('downloads_category_function_modify_link');
                }

                // parse delete button
                if (// managers are allowed to see delete every category
                    Permission::checkAccess(142, 'static', true)
                    // the selected category must be valid to proceed future permission checks.
                    // this is required to protect the overview section from non-admins
                    || $objCategory->getId() && (
                        // the category isn't protected => everyone is allowed to delete its subcategories
                        !$objCategory->getManageSubcategoriesAccessId()
                        // the category is protected => only those who have the sufficent permissions are allowed to delete its subcategories
                        || Permission::checkAccess($objCategory->getManageSubcategoriesAccessId(), 'dynamic', true)
                        // the owner is allowed to delete the subcategory
                        || $objSubcategory->getDeletableByOwner() && ($objFWUser = FWUser::getFWUserObject()) && $objFWUser->objUser->login() && $objSubcategory->getOwnerId() == $objFWUser->objUser->getId()
                    )
                ) {
                    $this->_objTpl->setVariable(array(
                        'TXT_DOWNLOADS_DELETE'                  => $_ARRAYLANG['TXT_DOWNLOADS_DELETE'],
                        'DOWNLOADS_CATEGORY_NAME_JS'            => htmlspecialchars($objSubcategory->getName($_LANGID), ENT_QUOTES, CONTREXX_CHARSET),
                        'DOWNLOADS_CATEGORY_HAS_SUBCATEGORIES'  => $objSubcategory->hasSubcategories()
                    ));

                    // parse delete icon
                    $this->_objTpl->parse('downloads_category_function_delete_link');
                    $this->_objTpl->hideBlock('downloads_category_function_no_delete_link');
                } else {
                    // hide delete icon
                    $this->_objTpl->touchBlock('downloads_category_function_no_delete_link');
                    $this->_objTpl->hideBlock('downloads_category_function_delete_link');
                }

                //parse select checkbox
                if ($operateOnSubcategories) {
                    $this->_objTpl->setVariable('DOWNLOADS_CATEGORY_ID', $objSubcategory->getId());
                    $this->_objTpl->parse('downloads_category_checkbox');
                } else {
                    $this->_objTpl->hideBlock('downloads_category_checkbox');
                }

                // parse detail link
                if (// managers are allowed to see the content of every category
                    Permission::checkAccess(142, 'static', true)
                    // the category isn't protected => everyone is allowed to the it's content
                    || !$objSubcategory->getReadAccessId()
                    // the category is protected => only those who have the sufficent permissions are allowed to see it's content
                    || Permission::checkAccess($objSubcategory->getReadAccessId(), 'dynamic', true)
                    // the owner is allowed to see the content of the category
                    || ($objFWUser = FWUser::getFWUserObject()) && $objFWUser->objUser->login() && $objSubcategory->getOwnerId() == $objFWUser->objUser->getId()
                ) {
                    $this->_objTpl->setVariable('DOWNLOADS_CATEGORY_ID', $objSubcategory->getId());
                    //$this->_objTpl->parse('downloads_category_name_link_open');
                    $this->_objTpl->touchBlock('downloads_category_name_link_close');
                } else {
                    $this->_objTpl->hideBlock('downloads_category_name_link_open');
                    $this->_objTpl->hideBlock('downloads_category_name_link_close');
                }


                $description = $objSubcategory->getDescription($_LANGID);
                if (strlen($description) > 100) {
                    $description = substr($description, 0, 97).'...';
                }

                $this->_objTpl->setVariable(array(
                    'DOWNLOADS_CATEGORY_ROW_CLASS'      => $nr++ % 2 ? 'row1' : 'row2',
                    'DOWNLOADS_CATEGORY_ID'             => $objSubcategory->getId(),
                    'DOWNLOADS_CATEGORY_STATUS_LED'     => $objSubcategory->getActiveStatus() ? 'led_green.gif' : 'led_red.gif',
                    'DOWNLOADS_OPEN_CATEGORY_DESC'      => sprintf($_ARRAYLANG['TXT_DOWNLOADS_SHOW_CATEGORY_CONTENT'], htmlentities($objSubcategory->getName($_LANGID), ENT_QUOTES, CONTREXX_CHARSET)),
                    'DOWNLOADS_CATEGORY_NAME'           => htmlentities($objSubcategory->getName($_LANGID), ENT_QUOTES, CONTREXX_CHARSET),
                    'DOWNLOADS_CATEGORY_DESCRIPTION'    => htmlentities($description, ENT_QUOTES, CONTREXX_CHARSET),
                    'DOWNLOADS_CATEGORY_AUTHOR'         => $this->getParsedUsername($objSubcategory->getOwnerId())
                ));

                $this->_objTpl->parse('downloads_category_list');



                $objSubcategory->next();
            }

            $this->_objTpl->touchBlock('downloads_category_data');
            $this->_objTpl->hideBlock('downloads_category_no_data');
        }

        // parse category id (will be used as the parent_id when creating a new directory
        $this->_objTpl->setVariable(array(
            'DOWNLOADS_CATEGORY_ID'     => $objCategory->getId(),
            'DOWNLOADS_CATEGORY_MENU'   => $this->getCategoryMenu('read', $objCategory->getId(), $_ARRAYLANG['TXT_DOWNLOADS_OVERVIEW'], 'onchange="window.location.href=\'index.php?cmd=downloads&amp;act=categories&amp;parent_id=\'+this.value"')
        ));

        // TODO: clean up
        $this->_objTpl->setVariable(array(
            //'TXT_DOWNLOADS_DELETE'          => $_ARRAYLANG['TXT_DOWNLOADS_DELETE'],
            //'TXT_DOWNLOADS_ORDER'           => $_ARRAYLANG['TXT_DOWNLOADS_ORDER'],


        ));




//        $this->_objTpl->setVariable(array(
//            'TXT_MANAGE_CATEGORIES' => $_ARRAYLANG['TXT_MANAGE_CATEGORIES'],
//            'TXT_ADD_CATEGORY'      => $_ARRAYLANG['TXT_ADD_CATEGORY']
//        ));
        // TODO: Add file list

        if ($objCategory->getId() && $objCategory->getAssociatedDownloadsCount()) {
            $this->parseCategoryDownloads($objCategory, $downloadOrderBy, $downloadOrderDirection, $downloadLimitOffset, $categoryOrderBy, $categoryOrderDirection, $categoryLimitOffset);
            $this->_objTpl->parse('downloads_category_downloads');
        } else {
            $this->_objTpl->hideBlock('downloads_category_downloads');
        }

        // TODO: clean up
        $this->_objTpl->setVariable(array(
            //'TXT_DOWNLOADS_STATUS'                      => $_ARRAYLANG['TXT_DOWNLOADS_STATUS'],
            //'TXT_DOWNLOADS_ID'                          => $_ARRAYLANG['TXT_DOWNLOADS_ID'],
            //'TXT_DOWNLOADS_NAME'                        => $_ARRAYLANG['TXT_DOWNLOADS_NAME'],
            //'TXT_DOWNLOADS_DESCRIPTION'                 => $_ARRAYLANG['TXT_DOWNLOADS_DESCRIPTION'],
            //'TXT_DOWNLOADS_AUTHOR'                      => $_ARRAYLANG['TXT_DOWNLOADS_AUTHOR'],
            'TXT_DOWNLOADS_FUNCTIONS'                   => $_ARRAYLANG['TXT_DOWNLOADS_FUNCTIONS'],
            'TXT_DOWNLOADS_OPERATION_IRREVERSIBLE'      => $_ARRAYLANG['TXT_DOWNLOADS_OPERATION_IRREVERSIBLE'],
            'TXT_DOWNLOADS_DELETE_SUBCATEGORIES'        => $_ARRAYLANG['TXT_DOWNLOADS_DELETE_SUBCATEGORIES'],
            'TXT_DOWNLOADS_DELETE_SUBCATEGORIES_MULTI'  => $_ARRAYLANG['TXT_DOWNLOADS_DELETE_SUBCATEGORIES_MULTI'],
            'DOWNLOADS_CONFIRM_DELETE_CATEGORY_TXT'     => preg_replace('#\n#', '\\n', addslashes($_ARRAYLANG['TXT_DOWNLOADS_CONFIRM_DELETE_CATEGORY'])),
            'DOWNLOADS_CONFIRM_DELETE_CATEGORIES_TXT'   => preg_replace('#\n#', '\\n', addslashes($_ARRAYLANG['TXT_DOWNLOADS_CONFIRM_DELETE_CATEGORIES'])),
            'DOWNLOADS_CONFIRM_UNLINK_DOWNLOAD_TXT'     => preg_replace('#\n#', '\\n', addslashes($_ARRAYLANG['TXT_DOWNLOADS_CONFIRM_UNLINK_DOWNLOAD'])),
            'DOWNLOADS_CATEGORY_COLSPAN'                => $minColspan + $operateOnSubcategories + $changeSortOrder,
        ));
    }

    private function parseCategoryDownloads($objCategory, $downloadOrderBy, $downloadOrderDirection, $downloadLimitOffset, $categoryOrderBy, $categoryOrderDirection, $categoryLimitOffset)
    {
        global $_ARRAYLANG, $_LANGID, $_CONFIG;

        $objDownload = new Download();
        $objDownload->loadDownloads(array('category_id' => $objCategory->getId(), 'visibility' => 1), null, array($downloadOrderBy => $downloadOrderDirection), null, $_CONFIG['corePagingLimit'], $downloadLimitOffset);

        if ($objDownload->EOF) {
            $this->_objTpl->hideBlock('downloads_download_action_dropdown');
        } else {
            $this->_objTpl->setVariable(array(
                'TXT_DOWNLOADS_CHECK_ALL'   => $_ARRAYLANG['TXT_DOWNLOADS_CHECK_ALL'],
                'TXT_DOWNLOADS_UNCHECK_ALL' => $_ARRAYLANG['TXT_DOWNLOADS_UNCHECK_ALL'],
                'TXT_DOWNLOADS_SELECT_ACTION'   => $_ARRAYLANG['TXT_DOWNLOADS_SELECT_ACTION'],
                'TXT_DOWNLOADS_ORDER'           => $_ARRAYLANG['TXT_DOWNLOADS_ORDER'],
                'TXT_DOWNLOADS_UNLINK'          => $_ARRAYLANG['TXT_DOWNLOADS_UNLINK']
            ));
            $this->_objTpl->parse('downloads_download_action_dropdown');

            // parse sorting
            $this->_objTpl->setVariable(array(
                'DOWNLOADS_DOWNLOAD_SORT_PARENT_ID'         => $objCategory->getId(),
                'DOWNLOADS_DOWNLOAD_SORT_DIRECTION'         => $downloadOrderDirection,
                'DOWNLOADS_DOWNLOAD_SORT_BY'                => $downloadOrderBy,
                'DOWNLOADS_DOWNLOAD_SORT_ID'                => ($downloadOrderBy == 'id' && $downloadOrderDirection == 'asc') ? 'desc' : 'asc',
                'DOWNLOADS_DOWNLOAD_SORT_STATUS'            => ($downloadOrderBy == 'is_active' && $downloadOrderDirection == 'asc') ? 'desc' : 'asc',
                'DOWNLOADS_DOWNLOAD_SORT_ORDER'             => ($downloadOrderBy == 'order' && $downloadOrderDirection == 'asc') ? 'desc' : 'asc',
                'DOWNLOADS_DOWNLOAD_SORT_NAME'              => ($downloadOrderBy == 'name' && $downloadOrderDirection == 'asc') ? 'desc' : 'asc',
                'DOWNLOADS_DOWNLOAD_SORT_DESCRIPTION'       => ($downloadOrderBy == 'description' && $downloadOrderDirection == 'asc') ? 'desc' : 'asc',
                'DOWNLOADS_DOWNLOAD_SORT_AUTHOR'            => ($downloadOrderBy == 'author' && $downloadOrderDirection == 'asc') ? 'desc' : 'asc',
                'DOWNLOADS_DOWNLOAD_SORT_ID_LABEL'          => $_ARRAYLANG['TXT_DOWNLOADS_ID'].($downloadOrderBy == 'id' ? $downloadOrderDirection == 'asc' ? ' &uarr;' : ' &darr;' : ''),
                'DOWNLOADS_DOWNLOAD_SORT_STATUS_LABEL'      => $_ARRAYLANG['TXT_DOWNLOADS_STATUS'].($downloadOrderBy == 'is_active' ? $downloadOrderDirection == 'asc' ? ' &uarr;' : ' &darr;' : ''),
                'DOWNLOADS_DOWNLOAD_SORT_ORDER_LABEL'       => $_ARRAYLANG['TXT_DOWNLOADS_ORDER'].($downloadOrderBy == 'order' ? $downloadOrderDirection == 'asc' ? ' &uarr;' : ' &darr;' : ''),
                'DOWNLOADS_DOWNLOAD_SORT_NAME_LABEL'        => $_ARRAYLANG['TXT_DOWNLOADS_NAME'].($downloadOrderBy == 'name' ? $downloadOrderDirection == 'asc' ? ' &uarr;' : ' &darr;' : ''),
                'DOWNLOADS_DOWNLOAD_SORT_DESCRIPTION_LABEL' => $_ARRAYLANG['TXT_DOWNLOADS_DESCRIPTION'].($downloadOrderBy == 'description' ? $downloadOrderDirection == 'asc' ? ' &uarr;' : ' &darr;' : ''),
                'DOWNLOADS_DOWNLOAD_SORT_AUTHOR_LABEL'      => $_ARRAYLANG['TXT_DOWNLOADS_AUTHOR'].($downloadOrderBy == 'author' ? $downloadOrderDirection == 'asc' ? ' &uarr;' : ' &darr;' : ''),
                'DOWNLOADS_DOWNLOAD_CATEGORY_SORT'          => $categoryOrderDirection,
                'DOWNLOADS_DOWNLOAD_CATEGORY_BY'            => $categoryOrderBy,
                'DOWNLOADS_DOWNLOAD_CATEGORY_OFFSET'        => $categoryLimitOffset
            ));

            // parse paging
            $downloadCount = $objDownload->getFilteredSearchDownloadCount();
            if ($downloadCount > $_CONFIG['corePagingLimit']) {
                $pagingLink = "&amp;cmd=downloads&amp;act=categories&amp;parent_id=".$objCategory->getId()
                    ."&amp;category_sort=".htmlspecialchars($categoryOrderDirection)
                    ."&amp;category_by=".htmlspecialchars($categoryOrderBy)
                    ."&amp;download_sort=".htmlspecialchars($downloadOrderDirection)
                    ."&amp;download_by=".htmlspecialchars($downloadOrderBy)
                    ."&amp;category_pos=".$categoryLimitOffset;
                $this->_objTpl->setVariable('DOWNLOADS_DOWNLOAD_PAGING', getPaging($downloadCount, $downloadLimitOffset, $pagingLink, "<b>".$_ARRAYLANG['TXT_DOWNLOADS_DOWNLOADS']."</b>").'<br />');
            }
        }

        $nr = 0;
        while (!$objDownload->EOF) {
            // parse select checkbox
            if (true) {
                $this->_objTpl->setVariable('DOWNLOADS_DOWNLOAD_ID', $objDownload->getId());
                $this->_objTpl->parse('downloads_download_checkbox');
            } else {
                $this->_objTpl->hideBlock('downloads_download_checkbox');
            }


            // parse order box
            if (true) {
                // TODO: should we use an own order id, just for this category relation
                $this->_objTpl->setVariable(array(
                    'DOWNLOADS_DOWNLOAD_ID'     => $objDownload->getId(),
                    'DOWNLOADS_DOWNLOAD_ORDER'  => $objDownload->getOrder()
                ));
                $this->_objTpl->parse('downloads_download_orderbox');
            } else {
                $this->_objTpl->hideBlock('downloads_download_orderbox');
            }

            // parse status link and modify button
            if (// managers are allowed to manage every download
                Permission::checkAccess(142, 'static', true)
                // the selected category must be valid to proceed future permission checks.
                // this is required to protect the overview section from non-admins
                || $objCategory->getId() && (
                    // the category isn't protected => everyone is allowed to modify downloads
                    !$objCategory->getManageFilesAccessId()
                    // the category is protected => only those who have the sufficent permissions are allowed to modify downloads
                    || Permission::checkAccess($objCategory->getManageFilesAccessId(), 'dynamic', true)
                    // the owner of the category is allowed to manage its downloads
                    || $objCategory->getModifyAccessByOwner() && ($objFWUser = FWUser::getFWUserObject()) && $objFWUser->objUser->login() && $objCategory->getOwnerId() == $objFWUser->objUser->getId()
                )
                // the owner of the download is allowed to manage it
                || ($objFWUser = FWUser::getFWUserObject()) && $objFWUser->objUser->login() && $objDownload->getOwnerId() == $objFWUser->objUser->getId()
            ) {
                $this->_objTpl->setVariable(array(
                    'DOWNLOADS_DOWNLOAD_ID'                     => $objDownload->getId(),
                    'DOWNLOADS_DOWNLOAD_CATEGORY_PARENT_ID'     => $objCategory->getId(),
                    'DOWNLOADS_DOWNLOAD_SWITCH_STATUS_DESC'     => $objDownload->getActiveStatus() ? $_ARRAYLANG['TXT_DOWNLOADS_DEACTIVATE_DOWNLOAD_DESC'] : $_ARRAYLANG['TXT_DOWNLOADS_ACTIVATE_DOWNLOAD_DESC'],
                    'DOWNLOADS_DOWNLOAD_SWITCH_STATUS_IMG_DESC' => $objDownload->getActiveStatus() ? $_ARRAYLANG['TXT_DOWNLOADS_DEACTIVATE_DOWNLOAD_DESC'] : $_ARRAYLANG['TXT_DOWNLOADS_ACTIVATE_DOWNLOAD_DESC']
                ));
                $this->_objTpl->parse('downloads_download_status_link_open');
                $this->_objTpl->parse('downloads_download_status_link_close');

                // parse modify icon
                $this->_objTpl->setVariable(array(
                    'DOWNLOADS_DOWNLOAD_ID'                 => $objDownload->getId(),
                    'DOWNLOADS_DOWNLOAD_CATEGORY_PARENT_ID' => $objCategory->getId()
                ));
                $this->_objTpl->parse('downloads_download_function_modify_link');
                $this->_objTpl->hideBlock('downloads_download_function_no_modify_link');
            } else {
                $this->_objTpl->setVariable(array(
                    'DOWNLOADS_DOWNLOAD_SWITCH_STATUS_DESC'     => $objDownload->getActiveStatus() ? $_ARRAYLANG['TXT_DOWNLOADS_ACTIVE'] : $_ARRAYLANG['TXT_DOWNLOADS_INACTIVE'],
                    'DOWNLOADS_DOWNLOAD_SWITCH_STATUS_IMG_DESC' => $objDownload->getActiveStatus() ? $_ARRAYLANG['TXT_DOWNLOADS_ACTIVE'] : $_ARRAYLANG['TXT_DOWNLOADS_INACTIVE']
                ));
                $this->_objTpl->hideBlock('downloads_download_status_link_open');
                $this->_objTpl->hideBlock('downloads_download_status_link_close');

                // hide modify icon
                $this->_objTpl->touchBlock('downloads_download_function_no_modify_link');
                $this->_objTpl->hideBlock('downloads_download_function_modify_link');
            }


            // parse download link
            if (// managers are allowed to delete every download
                Permission::checkAccess(142, 'static', true)
                // the selected category must be valid to proceed future permission checks.
                // this is required to protect the overview section from non-admins
                || $objCategory->getId() && (
                    // the category isn't protected => everyone is allowed to delete downloads
                    !$objCategory->getManageFilesAccessId()
                    // the category is protected => only those who have the sufficent permissions are allowed to delete downloads
                    || Permission::checkAccess($objCategory->getManageFilesAccessId(), 'dynamic', true)
                    // the owner of the category is allowed to download its downloads
                    || $objCategory->getModifyAccessByOwner() && ($objFWUser = FWUser::getFWUserObject()) && $objFWUser->objUser->login() && $objCategory->getOwnerId() == $objFWUser->objUser->getId()
                )
                // the owner of the download is allowed to delete it
                || ($objFWUser = FWUser::getFWUserObject()) && $objFWUser->objUser->login() && $objDownload->getOwnerId() == $objFWUser->objUser->getId()
            ) {
                $this->_objTpl->setVariable('DOWNLOADS_DOWNLOAD_ID', $objDownload->getId());
                //$this->_objTpl->parse('downloads_download_name_link_open');
                $this->_objTpl->touchBlock('downloads_download_name_link_close');
            } else {
                $this->_objTpl->hideBlock('downloads_download_name_link_open');
                $this->_objTpl->hideBlock('downloads_download_name_link_close');
            }


            // parse unlink button
            if (// managers are allowed to unlink every download
                Permission::checkAccess(142, 'static', true)
                // the selected category must be valid to proceed future permission checks.
                // this is required to protect the overview section from non-admins
                || $objCategory->getId() && (
                    // the category isn't protected => everyone is allowed to unlink downloads
                    !$objCategory->getManageFilesAccessId()
                    // the category is protected => only those who have the sufficent permissions are allowed to unlink downloads
                    || Permission::checkAccess($objCategory->getManageFilesAccessId(), 'dynamic', true)
                    // the owner of the category is allowed to unlink all downloads
                    || ($objFWUser = FWUser::getFWUserObject()) && $objFWUser->objUser->login() && $objCategory->getOwnerId() == $objFWUser->objUser->getId()
                )
                // the owner of the download is allowed to unlink it
                || ($objFWUser = FWUser::getFWUserObject()) && $objFWUser->objUser->login() && $objDownload->getOwnerId() == $objFWUser->objUser->getId()
            ) {
                $this->_objTpl->setVariable(array(
                    'TXT_DOWNLOADS_UNLINK'                  => $_ARRAYLANG['TXT_DOWNLOADS_UNLINK'],
                    'DOWNLOADS_DOWNLOAD_NAME_JS'            => htmlspecialchars($objDownload->getName($_LANGID), ENT_QUOTES, CONTREXX_CHARSET),
                ));

                // parse delete icon
                $this->_objTpl->parse('downloads_download_function_unlink_link');
                $this->_objTpl->hideBlock('downloads_download_function_no_unlink_link');
            } else {
                // hide delete icon
                $this->_objTpl->touchBlock('downloads_download_function_no_unlink_link');
                $this->_objTpl->hideBlock('downloads_download_function_unlink_link');
            }


            $description = $objDownload->getDescription($_LANGID);
            if (strlen($description) > 100) {
                $description = substr($description, 0, 97).'...';
            }

            $this->_objTpl->setVariable(array(
            // parse download id
                'DOWNLOADS_DOWNLOAD_ID'             => $objDownload->getId(),
                'DOWNLOADS_DOWNLOAD_NAME'           => htmlentities($objDownload->getName($_LANGID), ENT_QUOTES, CONTREXX_CHARSET),
                'DOWNLOADS_DOWNLOAD_DESCRIPTION'    => htmlentities($description, ENT_QUOTES, CONTREXX_CHARSET),
                'DOWNLOADS_DOWNLOAD_AUTHOR'         => htmlentities($objDownload->getAuthor(), ENT_QUOTES, CONTREXX_CHARSET),
                'DOWNLOADS_DOWNLOAD_STATUS_LED'     => $objDownload->getActiveStatus() ? 'led_green.gif' : 'led_red.gif',
                'DOWNLOADS_DOWNLOAD_ICON'           => $objDownload->getIcon(),
                'DOWNLOADS_DOWNLOAD_ROW_CLASS'      => $nr++ % 2 ? 'row1' : 'row2'
            ));

            $this->_objTpl->parse('downloads_download_list');

            $objDownload->next();
        }

        $this->_objTpl->setVariable('DOWNLOADS_OF_CATEGORY_TXT', sprintf($_ARRAYLANG['TXT_DOWNLOADS_DOWNLOADS_OF_CATEGORY'], htmlentities($objCategory->getName($_LANGID), ENT_QUOTES, CONTREXX_CHARSET)));

        $this->_objTpl->setVariable(array(
            //'TXT_DOWNLOADS_ID'          => $_ARRAYLANG['TXT_DOWNLOADS_ID'],
            //'TXT_DOWNLOADS_STATUS'      => $_ARRAYLANG['TXT_DOWNLOADS_STATUS'],
            //'TXT_DOWNLOADS_ORDER'       => $_ARRAYLANG['TXT_DOWNLOADS_ORDER'],
            //'TXT_DOWNLOADS_NAME'        => $_ARRAYLANG['TXT_DOWNLOADS_NAME'],
            //'TXT_DOWNLOADS_DESCRIPTION' => $_ARRAYLANG['TXT_DOWNLOADS_DESCRIPTION'],
            //'TXT_DOWNLOADS_AUTHOR'      => $_ARRAYLANG['TXT_DOWNLOADS_AUTHOR'],
            'TXT_DOWNLOADS_FUNCTIONS'   => $_ARRAYLANG['TXT_DOWNLOADS_FUNCTIONS']
        ));
    }

//    /**
//     * add new file
//     *
//     * @global object $objDatabase
//     * @global array $_CONFIG
//     */
//    function InsertFile()
//    {
//        global $objDatabase, $_CONFIG;
//
//        if ($_REQUEST["file_select"]=="1") {
//            if ($_REQUEST["file_source"]!='') {
//                $FileName = addslashes(strip_tags($_REQUEST["file_source"]));
//            }
//        } elseif ($_REQUEST["file_select"]=="2") {
//            if ($_REQUEST["file_url"]!='') {
//                $FileName = addslashes(strip_tags($_REQUEST["file_url"]));
//                if (substr($FileName, 0, 7)!='http://' && substr($FileName, 0, 8)!='https://') {
//                    $FileName = 'http://'.$FileName;
//                }
//            }
//        }
//
//        $FileType = addslashes(strip_tags($_REQUEST["file_type"]));
//        $FileSize = addslashes(strip_tags($_REQUEST["file_size"]));
//        $FileLicense = addslashes(strip_tags($_REQUEST["file_license"]));
//        $FileVersion = addslashes(strip_tags($_REQUEST["file_version"]));
//        $FileAuthor = addslashes(strip_tags($_REQUEST["file_author"]));
//        $FileImage = addslashes(strip_tags($_POST['file_image']));
//        $FileAccessID = $_CONFIG['lastAccessId']+1;
//
//        srand(microtime()*100000000);
//        $zufall = rand(1000000,9999999);
//        $SourceName = md5($zufall.$FileName);
//
//        if ($_REQUEST["protected"]=="1") {
//            $FileProtected = 1;
//        } else {
//            $FileProtected = 0;
//        }
//
//        if ($_REQUEST["file_state"]=="1") {
//            $file_state = 1;
//        } else {
//            $file_state = 0;
//        }
//
//
//        // File
//        // -----------------------------------------------------------------
//        $query = "
//            INSERT INTO ".DBPREFIX."module_downloads_files
//            (`file_name`, `file_type`, `file_size`, `file_img`, `file_autor`, `file_created`, `file_source`, `file_protected`, `file_access_id`, `file_license`, `file_version`, `file_state`) VALUES
//            ('".$FileName."', '".$FileType."', '".$FileSize."', '".$FileImage."', '".$FileAuthor."', now(), '".$SourceName."', '".$FileProtected."', '".$FileAccessID."', '".$FileLicense."', '".$FileVersion."', '".$file_state."')
//        ";
//
//        $objDatabase->Execute($query);
//
//        $objDatabase->Execute("UPDATE ".DBPREFIX."settings SET setvalue=".$FileAccessID." WHERE setname='lastAccessId'");
//        require_once(ASCMS_CORE_PATH.'/settings.class.php');
//        $objSettings = new settingsManager();
//        $objSettings->writeSettingsFile();
//
//        $FileId = $objDatabase->Insert_Id();
//
//        // Categories
//        // -----------------------------------------------------------------
//        for($i=0; $i<=count($_REQUEST["assignedCategories"]); $i++) {
//            if (intval($_REQUEST["assignedCategories"][$i])>0) {
//                $query = "
//                        INSERT INTO ".DBPREFIX."module_downloads_rel_files_cat
//                            (`rel_file`, `rel_category`) VALUES
//                            ('".$FileId."', '".$_REQUEST["assignedCategories"][$i]."')
//                ";
//                $objDatabase->Execute($query);
//            }
//        }
//
//        // Name & Description
//        // -----------------------------------------------------------------
//        //for($i=0; $i<=count($_REQUEST["frmEditEntry_Languages"]); $i++) {
//        foreach (array_keys($this->_arrLang) as $langId) {
//            //$langId = $_REQUEST["frmEditEntry_Languages"][$i];
//            if (intval($langId)>0) {
//                // insert lang
//                // ------------------------------------------------
//                $query = "INSERT INTO ".DBPREFIX."module_downloads_files_lang
//                            SET file='".$FileId."',
//                            language='".$langId."'";
//                $objDatabase->Execute($query);
//
//                // insert loclaes
//                // ------------------------------------------------
//                $query = "INSERT INTO ".DBPREFIX."module_downloads_files_locales
//                            SET loc_lang='".$langId."',
//                            loc_file='".$FileId."',
//                            loc_name='".addslashes(strip_tags($_POST['file_name_'.$langId]))."',
//                            loc_desc='".addslashes(strip_tags($_POST['file_desc_'.$langId]))."'";
//                $objDatabase->Execute($query);
//            }
//        }
//
//        // Access ID
//        // -----------------------------------------------------------------
//        if ($FileProtected==1) {
//            for($i=0; $i<=count($_REQUEST["assignedGroups"]); $i++) {
//                if (intval($_REQUEST["assignedGroups"][$i])>0) {
//                    $objDatabase->Execute("INSERT INTO ".DBPREFIX."access_group_dynamic_ids
//                    (`access_id`,`group_id`) VALUES
//                    (".$FileAccessID.", ".intval($_REQUEST["assignedGroups"][$i]).")");
//                }
//            }
//        }
//
//        // Related downloads
//        // -----------------------------------------------------------------
//        for($i=0; $i<=count($_REQUEST["assignedDownloads"]); $i++) {
//            if (intval($_REQUEST["assignedDownloads"][$i])>0) {
//                $objDatabase->Execute("INSERT INTO ".DBPREFIX."module_downloads_rel_files_files
//                (`rel_file`,`rel_related`) VALUES
//                (".$FileId.", ".intval($_REQUEST["assignedDownloads"][$i]).")");
//            }
//        }
//
//        return true;
//    }

    /**
     * module settings
     *
     * @global object $objDatabase
     * @global array $_ARRAYLANG
     */
    function _settings()
    {
        global $_ARRAYLANG, $objDatabase, $_CONFIG;
        $this->_pageTitle = $_ARRAYLANG['TXT_SETTINGS'];
        $this->_objTpl->loadTemplateFile('settings.html');


        // Save settings
        // ------------------------------------------------------
        if ($_REQUEST["mode"] == "save") {

            $query = "
              UPDATE ".DBPREFIX."module_downloads_settings SET
                  `setting_value`='".intval($_REQUEST["settings_filter"])."'
                where `setting_id`=1
            ";

            $objDatabase->Execute($query);
            $query = "
              UPDATE ".DBPREFIX."module_downloads_settings SET
                  `setting_value`='".$_REQUEST["settings_design"]."'
                where `setting_id`=2
            ";
            $objDatabase->Execute($query);
            header('location:index.php?cmd=downloads&act=settings&msg=1');
            exit();
        }

        if ($_REQUEST["msg"]=="1") {
            $this->arrStatusMsg['ok'][] = $_ARRAYLANG['TXT_DOWNLOADS_UPDATE_SUCCESSFULL'];
        }

        if ($this->_arrConfig["filter"]==1) {
            $filter_checked = 'checked';
        } else {
            $filter_checked = '';
        }

        for($x = 1; $x < 4; $x++) {
            if ($this->_arrConfig["design"]==$x) {
                $SlectedDesign[$x] = 'selected';
            } else {
                $SlectedDesign[$x] = '';
            }
        }

        $this->_objTpl->setVariable(array(
            'TXT_SETTINGS' => $_ARRAYLANG['TXT_SETTINGS'],
            'TXT_DOWNLOADS_FILTER' => $_ARRAYLANG['TXT_DOWNLOADS_FILTER'],
            'TXT_DOWNLOADS_DESIGN' => $_ARRAYLANG['TXT_DOWNLOADS_DESIGN'],
            'TXT_DOWNLOADS_NO_ICONS' => $_ARRAYLANG['TXT_DOWNLOADS_NO_ICONS'],
            'TXT_DOWNLOADS_ICON_SET' => $_ARRAYLANG['TXT_DOWNLOADS_ICON_SET'],
            'TXT_SAVE' => $_ARRAYLANG['TXT_SAVE'],
            'FILTER_CHECKED' => $filter_checked,
            'ICONS_SELECTED_1' => $SlectedDesign[1],
            'ICONS_SELECTED_2' => $SlectedDesign[2],
            'ICONS_SELECTED_3' => $SlectedDesign[3],
            'ICONS_SELECTED_4' => $SlectedDesign[4],
        ));
    }


    /**
     * placeholder
     *
     * @global object $objDatabase
     * @global array $_ARRAYLANG
     */
    function _placeholder()
    {
        global $_ARRAYLANG, $objDatabase;
        $this->_pageTitle = $_ARRAYLANG['TXT_PLACEHOLDER'];
        $this->_objTpl->loadTemplateFile('placeholder.html');


        $this->_objTpl->setVariable(array(
            'TXT_DOWNLOADS_DOWNLOADS' => $_ARRAYLANG['TXT_DOWNLOADS_DOWNLOADS'],
            'TXT_DOWNLOADS_ICONS' => $_ARRAYLANG['TXT_DOWNLOADS_ICONS'],
            'TXT_DOWNLOADS_CATEGORIES' => $_ARRAYLANG['TXT_DOWNLOADS_CATEGORIES'],
            'TXT_PLACEHOLDER_FILE_ID' => $_ARRAYLANG['TXT_PLACEHOLDER_FILE_ID'],
            'TXT_PLACEHOLDER_FILE_NAME' => $_ARRAYLANG['TXT_PLACEHOLDER_FILE_NAME'],
            'TXT_PLACEHOLDER_FILE_DESC' => $_ARRAYLANG['TXT_PLACEHOLDER_FILE_DESC'],
            'TXT_PLACEHOLDER_FILE_TYPE' => $_ARRAYLANG['TXT_PLACEHOLDER_FILE_TYPE'],
            'TXT_PLACEHOLDER_FILE_SIZE' => $_ARRAYLANG['TXT_PLACEHOLDER_FILE_SIZE'],
            'TXT_PLACEHOLDER_FILE_IMG' => $_ARRAYLANG['TXT_PLACEHOLDER_FILE_IMG'],
            'TXT_PLACEHOLDER_FILE_AUTHOR' => $_ARRAYLANG['TXT_PLACEHOLDER_FILE_AUTHOR'],
            'TXT_PLACEHOLDER_FILE_CREATED' => $_ARRAYLANG['TXT_PLACEHOLDER_FILE_CREATED'],
            'TXT_PLACEHOLDER_FILE_LICENSE' => $_ARRAYLANG['TXT_PLACEHOLDER_FILE_LICENSE'],
            'TXT_PLACEHOLDER_FILE_VERSION' => $_ARRAYLANG['TXT_PLACEHOLDER_FILE_VERSION'],
            'TXT_PLACEHOLDER_CATEGORY_ID' => $_ARRAYLANG['TXT_PLACEHOLDER_CATEGORY_ID'],
            'TXT_PLACEHOLDER_CATEGORY_NAME' => $_ARRAYLANG['TXT_PLACEHOLDER_CATEGORY_NAME'],
            'TXT_PLACEHOLDER_CATEGORY_DESC' => $_ARRAYLANG['TXT_PLACEHOLDER_CATEGORY_DESC'],
            'TXT_PLACEHOLDER_ICON_DISPLAY' => $_ARRAYLANG['TXT_PLACEHOLDER_ICON_DISPLAY'],
            'TXT_PLACEHOLDER_ICON_FILTERS' => $_ARRAYLANG['TXT_PLACEHOLDER_ICON_FILTERS'],
            'TXT_PLACEHOLDER_ICON_CATEGORY' => $_ARRAYLANG['TXT_PLACEHOLDER_ICON_CATEGORY'],
            'TXT_PLACEHOLDER_ICON_FILE' => $_ARRAYLANG['TXT_PLACEHOLDER_ICON_FILE'],
            'TXT_PLACEHOLDER_ICON_DOWNLOAD' => $_ARRAYLANG['TXT_PLACEHOLDER_ICON_DOWNLOAD'],
            'TXT_PLACEHOLDER_ICON_INFO' => $_ARRAYLANG['TXT_PLACEHOLDER_ICON_INFO'],
        ));

    }


    /**
     * _CategoryEdit
     *
     * @global object $objDatabase
     * @global array $_ARRAYLANG
    function _CategoryEdit($mode)
    {}
     */


//    /**
//     * _DeleteDownload
//     *
//     * @var int    $id
//     * @global object $objDatabase
//     */
//    function _DeleteDownload($id)
//    {
//        global $objDatabase;
//        if (intval($id)>0) {
//            $query = "DELETE FROM ".DBPREFIX."module_downloads_files WHERE file_id=".$id;
//            $objDatabase->Execute($query);
//            $query = "DELETE FROM ".DBPREFIX."module_downloads_files_lang WHERE file=".$id;
//            $objDatabase->Execute($query);
//            $query = "DELETE FROM ".DBPREFIX."module_downloads_files_locales WHERE loc_file=".$id;
//            $objDatabase->Execute($query);
//            $query = "DELETE FROM ".DBPREFIX."module_downloads_rel_files_cat WHERE rel_file=".$id;
//            $objDatabase->Execute($query);
//            $query = "DELETE FROM ".DBPREFIX."module_downloads_rel_files_files WHERE rel_file=".$id;
//            $objDatabase->Execute($query);
//            return true;
//        } else {
//            return false;
//        }
//    }


//    /**
//     * Update Download
//     *
//     * @global object $objDatabase
//     */
//    function UpdateDownload()
//    {
//        global $objDatabase;
//
//        $FileName = '';
//        $FileUrl = '';
//        $FileId = intval($_REQUEST["id"]);
//        $DownlaodInfo = $this->_FileInfo($FileId);
//
//        if ($_REQUEST["file_select"]=="1") {
//            if ($_REQUEST["file_source"]!='') {
//                $FileName = addslashes(strip_tags($_REQUEST["file_source"]));
//            }
//        } elseif ($_REQUEST["file_select"]=="2") {
//            if ($_REQUEST["file_url"]!='') {
//                $FileUrl = addslashes(strip_tags($_REQUEST["file_url"]));
//                if (substr($FileUrl, 0, 7)!='http://' && substr($FileUrl, 0, 8)!='https://') {
//                    $FileUrl = 'http://'.$FileUrl;
//                }
//            }
//        }
//
//        $FileType = addslashes(strip_tags($_REQUEST["file_type"]));
//        $FileSize = addslashes(strip_tags($_REQUEST["file_size"]));
//        $FileLicense = addslashes(strip_tags($_REQUEST["file_license"]));
//        $FileVersion = addslashes(strip_tags($_REQUEST["file_version"]));
//        $FileAuthor = addslashes(strip_tags($_REQUEST["file_author"]));
//        $FileImage = addslashes(strip_tags($_POST['file_image']));
//
//        if ($_REQUEST["protected"]=="1") {
//            $FileProtected = 1;
//        } else {
//            $FileProtected = 0;
//        }
//
//        if ($_REQUEST["file_state"]=="1") {
//            $file_state = 1;
//        } else {
//            $file_state = 0;
//        }
//
//        // File
//        // -----------------------------------------------------------------
//        $query = "
//              UPDATE ".DBPREFIX."module_downloads_files SET
//                `file_name`='".$FileName."',
//                `file_url`='".$FileUrl."',
//                `file_type`='".$FileType."',
//                `file_size`='".$FileSize."',
//                `file_img`='".$FileImage."',
//                `file_autor`='".$FileAuthor."',
//                `file_protected`='".$FileProtected."',
//                `file_license`='".$FileLicense."',
//                `file_version`='".$FileVersion."',
//                `file_state`='".$file_state."'
//                where file_id=".$FileId."
//        ";
//        $objDatabase->Execute($query);
//
//        // Categories
//        // -----------------------------------------------------------------
//
//        $query = "DELETE FROM ".DBPREFIX."module_downloads_rel_files_cat WHERE `rel_file`=".$FileId." ";
//        $objDatabase->Execute($query);
//
//        for($i=0; $i<=count($_REQUEST["assignedCategories"]); $i++) {
//            if (intval($_REQUEST["assignedCategories"][$i])>0) {
//                $query = "
//                        INSERT INTO ".DBPREFIX."module_downloads_rel_files_cat
//                            (`rel_file`, `rel_category`) VALUES
//                            ('".$FileId."', '".$_REQUEST["assignedCategories"][$i]."')
//                ";
//                $objDatabase->Execute($query);
//            }
//        }
//
//        // Name & Description
//        // -----------------------------------------------------------------
//        foreach (array_keys($this->_arrLang) as $langId) {
//            if (intval($langId)>0) {
////
////                // insert lang
////                // ------------------------------------------------
////                $query = "INSERT INTO ".DBPREFIX."module_downloads_files_lang
////                            SET file='".$FileId."',
////                            language='".$langId."'";
////                $objDatabase->Execute($query);
//
//
//                $query = "UPDATE ".DBPREFIX."module_downloads_files_locales SET
//                            `loc_name`='".addslashes(strip_tags($_POST['file_name_'.$langId]))."',
//                            `loc_desc`='".addslashes(strip_tags($_POST['file_desc_'.$langId]))."'
//                            WHERE `loc_file`=".$FileId." AND `loc_lang`=".$langId."
//                ";
////                echo($query."<br />");
//                $objDatabase->Execute($query);
//            }
//        }
//
//        // Access ID
//        // -----------------------------------------------------------------
//
//        $query = "DELETE FROM ".DBPREFIX."access_group_dynamic_ids WHERE `access_id`=".$DownlaodInfo["file_access_id"]." ";
//        $objDatabase->Execute($query);
//
//        if ($FileProtected==1) {
//            for($i=0; $i<=count($_REQUEST["assignedGroups"]); $i++) {
//                if (intval($_REQUEST["assignedGroups"][$i])>0) {
//                    $objDatabase->Execute("INSERT INTO ".DBPREFIX."access_group_dynamic_ids
//                    (`access_id`,`group_id`) VALUES
//                    (".$DownlaodInfo["file_access_id"].", ".intval($_REQUEST["assignedGroups"][$i]).")");
//                }
//            }
//        }
//
//        // Related downloads
//        // -----------------------------------------------------------------
//
//        $query = "DELETE FROM ".DBPREFIX."module_downloads_rel_files_files WHERE `rel_file`=".$FileId." ";
//        $objDatabase->Execute($query);
//
//        for($i=0; $i<=count($_REQUEST["assignedDownloads"]); $i++) {
//            if (intval($_REQUEST["assignedDownloads"][$i])>0) {
//                $objDatabase->Execute("INSERT INTO ".DBPREFIX."module_downloads_rel_files_files
//                (`rel_file`,`rel_related`) VALUES
//                (".$FileId.", ".intval($_REQUEST["assignedDownloads"][$i]).")");
//            }
//        }
//
//        return true;
//    }

}

?>
