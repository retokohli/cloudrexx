<?php

define('_HOTELCARD_DEBUG', 3);

/**
 * Class Hotelcard
 *
 * Administration of the Hotelcard module
 * @copyright   CONTREXX CMS - COMVATION AG
 * @author      Reto Kohli <reto.kohli@comvation.com>
 * @version     2.1.1
 * @package     contrexx
 * @subpackage  module_hotelcard
 */

/** @ignore */
require_once ASCMS_CORE_PATH.'/Filetype.class.php';
require_once ASCMS_CORE_PATH.'/Imagetype.class.php';
require_once ASCMS_CORE_PATH.'/Image.class.php';
require_once ASCMS_CORE_PATH.'/SettingDb.class.php';
require_once ASCMS_CORE_PATH.'/Sorting.class.php';
require_once ASCMS_CORE_PATH.'/Text.class.php';
require_once ASCMS_FRAMEWORK_PATH.'/Language.class.php';
require_once ASCMS_MODULE_PATH.'/hotelcard/lib/Settings.class.php';
//require_once ASCMS_MODULE_PATH.'/hotelcard/lib/Designer.class.php';
//require_once ASCMS_MODULE_PATH.'/hotelcard/lib/Download.class.php';
//require_once ASCMS_MODULE_PATH.'/hotelcard/lib/Product.class.php';
//require_once ASCMS_MODULE_PATH.'/hotelcard/lib/Property.class.php';
//require_once ASCMS_MODULE_PATH.'/hotelcard/lib/Reference.class.php';
//require_once ASCMS_MODULE_PATH.'/hotelcard/lib/Image.class.php';
//require_once ASCMS_MODULE_PATH.'/hotelcard/lib/Text.class.php';
//require_once ASCMS_MODULE_PATH.'/hotelcard/lib/RelProductCategory.class.php';
//require_once ASCMS_MODULE_PATH.'/hotelcard/lib/RelProductProperty.class.php';
//require_once ASCMS_MODULE_PATH.'/hotelcard/lib/RelProductReference.class.php';
//require_once ASCMS_MODULE_PATH.'/hotelcard/lib/RelUserContact.class.php';
//require_once ASCMS_MODULE_PATH.'/hotelcard/lib/constants.php';
//require_once ASCMS_MODULE_PATH.'/hotelcard/lib/sorting.php';
//require_once ASCMS_MODULE_PATH.'/hotelcard/lib/Material.class.php';
//require_once ASCMS_MODULE_PATH.'/hotelcard/lib/Manufacturer.class.php';
//require_once ASCMS_MODULE_PATH.'/hotelcard/lib/Line.class.php';

/**
 * Class Hotelcard
 *
 * Administration of the Hotelcard module
 * @copyright   CONTREXX CMS - COMVATION AG
 * @author      Reto Kohli <reto.kohli@comvation.com>
 * @version     2.1.1
 * @package     contrexx
 * @subpackage  module_hotelcard
 */
class Hotelcard
{
    /**
     * Page title
     * @var     string
     * @static
     * @access  private
     */
    private static $pageTitle = '';
    /**
     * Success notice
     * @var     string
     * @static
     * @access  private
     */
    private static $strOkMessage = '';
    /**
     * Failure notice
     * @var     string
     * @static
     * @access  private
     */
    private static $strErrMessage = '';
    /**
     * Sigma Template
     * @var     HTML_Template_Sigma
     * @static
     * @access  private
     */
    private static $objTemplate = null;
    /**
     * Base page URI with cmd, act, and id parameters
     * @var     string
     * @static
     * @access  private
     */
    private static $baseUri;


    /**
     * Initialize
     * @access  public
     */
    function init()
    {
        if (self::$objTemplate) return;

        if (_HOTELCARD_DEBUG & 1) DBG::enable_error_reporting();
        if (_HOTELCARD_DEBUG & 2) DBG::enable_adodb();

        // Sigma template
        self::$objTemplate = new HTML_Template_Sigma(ASCMS_MODULE_PATH.'/hotelcard/template');
        self::$objTemplate->setErrorHandling(PEAR_ERROR_DIE);

        // Set up whatever needed here.
/*
require_once('../customizing/Import/createRegions.php');
createRegions();
*/
/*
require_once('../customizing/Import/import.php');
importHotelcard();
*/
    }


    /**
     * Set up the selected administration page
     */
    function getPage()
    {
        global $objTemplate, $_ARRAYLANG, $_CORELANG;

        $cmd = (isset($_GET['cmd']) ? $_GET['cmd'] : '');
        $act = (isset($_GET['act']) ? $_GET['act'] : '');
//        $tpl = (isset($_GET['tpl']) ? $_GET['tpl'] : '');
        $category_id = (isset($_REQUEST['category_id']) ? $_REQUEST['category_id'] : 0);
        $contact_id = (isset($_REQUEST['contact_id']) ? $_REQUEST['contact_id'] : 0);
        $designer_id = (isset($_REQUEST['designer_id']) ? $_REQUEST['designer_id'] : 0);
        $download_id = (isset($_REQUEST['download_id']) ? $_REQUEST['download_id'] : 0);
        $product_id = (isset($_REQUEST['product_id']) ? $_REQUEST['product_id'] : 0);
        $property_id = (isset($_REQUEST['property_id']) ? $_REQUEST['property_id'] : 0);
        $reference_id = (isset($_REQUEST['reference_id']) ? $_REQUEST['reference_id'] : 0);
        $idName =
            ($category_id ? 'category_id' :
              ($contact_id ? 'contact_id' :
                ($designer_id ? 'designer_id' :
                  ($download_id ? 'download_id' :
                    ($product_id ? 'product_id' :
                      ($property_id ? 'property_id' :
                        ($reference_id ? 'reference_id' : 'id')))))));
        $id = (isset($_REQUEST[$idName]) ? $_REQUEST[$idName] : 0);
        // Used for setting up the Sorting headers
        self::$baseUri = 'index.php?cmd='.$cmd.'&amp;act='.$act;

        $result = true;
        switch ($act) {
            case 'settings':
            default:
                $result &= self::settings();
                break;
        }

        $result &= (self::$strErrMessage == '');
        if (!$result) {
            self::errorHandler();
            self::addError('An error has occurred.  Please try reloading the page.');
die("ERROR building page, code dfsgljbew<br />");
        }

        $objTemplate->setVariable(array(
            'CONTENT_TITLE'          => self::$pageTitle,
            'CONTENT_OK_MESSAGE'     => self::$strOkMessage,
            'CONTENT_STATUS_MESSAGE' => self::$strErrMessage,
            'ADMIN_CONTENT'          => self::$objTemplate->get(),
            'CONTENT_NAVIGATION'     =>
                '<a href="index.php?cmd=hotelcard&amp;act=settings">'.$_CORELANG['TXT_CORE_SETTINGS'].'</a>',
        ));
// Subnavigation
//        switch ($act) {
//            case 'product':
//                $objTemplate->setVariable(
//                    'CONTENT_SUBNAVIGATION',
//                        '<a href="index.php?cmd=hotelcard&amp;act=product">'.$_ARRAYLANG['TXT_HOTELCARD_PRODUCT_NEW'].'</a>'
//                );
//                break;
//        }
        $objTemplate->setGlobalVariable(array(
            'MODULE_BASE_URI'        => $baseUri,
        ));

    }


    /**
     * Set up the page with a list of all Settings
     *
     * Stores the settings if requested to.
     * @return  boolean             True on success, false otherwise
     * @author  Reto Kohli <reto.kohli@comvation.com>
     */
    function settings()
    {
        global $_ARRAYLANG;

        if (isset($_POST['store'])) self::storeSettings();

        self::$pageTitle = $_ARRAYLANG['TXT_HOTELCARD_SETTINGS'];
        if (!self::$objTemplate->loadTemplateFile('settings.html', true, true))
            die("Failed to load template settings.html");

        // *MUST* reinitialis
        SettingDb::init(MODULE_ID);
        return SettingDb::show(self::$objTemplate);
    }


    /**
     * Store changes to the list of Settings in the database.
     *
     * Returns the empty string if there is nothing to be stored
     * (no or an empty array has been posted).
     * If no setting has changed, true is returned.
     * @return  mixed       True on success, false on failure,
     *                      the empty string otherwise
     * @author  Reto Kohli <reto.kohli@comvation.com>
     */
    function storeSettings()
    {
        global $_ARRAYLANG;

        if (empty($_POST['hotelcard']) || !is_array($_POST['hotelcard'])) return '';

        // Compare POST with current settings.
        // Only store what was changed.
        SettingDb::init(MODULE_ID);
        $flagError = false;
        $flagChanged = false;
        foreach ($_POST['hotelcard'] as $name => $value) {
            $value = contrexx_stripslashes($value);
            if ($value == SettingDb::get($name)) continue;
            $flagChanged = true;
            $result = SettingDb::store($name, $value);
            if ($result === false) $flagError = true;
        }
        // No changes detected
        if (!$flagChanged) return true;
        if ($flagError) {
            self::addError($_ARRAYLANG['TXT_HOTELCARD_ERROR_SETTING_NOT_STORED']);
            return false;
        }
        self::addMessage($_ARRAYLANG['TXT_HOTELCARD_SETTING_STORED_SUCCESSFULLY']);
        return true;
    }


    /**
     * Set up the page with a list of all Properties
     * @return    boolean             True on success, false otherwise
     * @author  Reto Kohli <reto.kohli@comvation.com>
     */
    function showProperties()
    {
        global $_ARRAYLANG;

        if (isset($_POST['store1'])) {
            self::storeProperty();
        }
        if (isset($_POST['store2']) || !empty($_POST['multiAction'])) {
            self::storeProperties();
        }

        $propertyId = (isset($_REQUEST['id']) ? $_REQUEST['id'] : 0);

        if (isset($_GET['delete'])) {
            if (!Property::deleteById($propertyId)) {
                self::addError($_ARRAYLANG['TXT_HOTELCARD_ERROR_PROPERTY_NOT_DELETED']);
            }
            $propertyId = 0;
        }

        self::$pageTitle = $_ARRAYLANG['TXT_HOTELCARD_PROPERTIES'];
        self::$objTemplate->loadTemplateFile('properties.html', true, true);

        $objSorting = new Sorting(
            self::$baseUri,
            array(
                'ord', 'property_id', 'name',
            ),
            array(
                $_ARRAYLANG['TXT_HOTELCARD_ORD'],
                $_ARRAYLANG['TXT_HOTELCARD_ID'],
                $_ARRAYLANG['TXT_HOTELCARD_NAME'],
            )
        );
        self::$objTemplate->setGlobalVariable(array(
            'TXT_HOTELCARD_ACTION_IS_IRREVERSIBLE' => $_ARRAYLANG['TXT_HOTELCARD_ACTION_IS_IRREVERSIBLE'],
            'TXT_HOTELCARD_ACTIVATE_SELECTED' => $_ARRAYLANG['TXT_HOTELCARD_ACTIVATE_SELECTED'],
            'TXT_HOTELCARD_COPY' => $_ARRAYLANG['TXT_HOTELCARD_COPY'],
            'TXT_HOTELCARD_DEACTIVATE_SELECTED' => $_ARRAYLANG['TXT_HOTELCARD_DEACTIVATE_SELECTED'],
            'TXT_HOTELCARD_DELETE' => $_ARRAYLANG['TXT_HOTELCARD_DELETE'],
            'TXT_HOTELCARD_DELETE_SELECTED' => $_ARRAYLANG['TXT_HOTELCARD_DELETE_SELECTED'],
            'TXT_HOTELCARD_DESELECT_ALL' => $_ARRAYLANG['TXT_HOTELCARD_DESELECT_ALL'],
            'TXT_HOTELCARD_EDIT' => $_ARRAYLANG['TXT_HOTELCARD_EDIT'],
            'TXT_HOTELCARD_FUNCTIONS' => $_ARRAYLANG['TXT_HOTELCARD_FUNCTIONS'],
            'TXT_HOTELCARD_ORD' => $objSorting->getHeaderForField('ord'), //$_ARRAYLANG['TXT_HOTELCARD_ORD'],
            'TXT_HOTELCARD_ID' => $objSorting->getHeaderForField('property_id'), //$_ARRAYLANG['TXT_HOTELCARD_ID'],
            'TXT_HOTELCARD_NAME' => $objSorting->getHeaderForField('name'), //$_ARRAYLANG['TXT_HOTELCARD_NAME'],
            'TXT_HOTELCARD_PROPERTIES_DELETE_CONFIRM' => $_ARRAYLANG['TXT_HOTELCARD_PROPERTIES_DELETE_CONFIRM'],
            'TXT_HOTELCARD_PROPERTY_DELETE_CONFIRM' => $_ARRAYLANG['TXT_HOTELCARD_PROPERTY_DELETE_CONFIRM'],
            'TXT_HOTELCARD_SELECT_ACTION' => $_ARRAYLANG['TXT_HOTELCARD_SELECT_ACTION'],
            'TXT_HOTELCARD_SELECT_ALL' => $_ARRAYLANG['TXT_HOTELCARD_SELECT_ALL'],
            'TXT_HOTELCARD_STORE' => $_ARRAYLANG['TXT_HOTELCARD_STORE'],
            'TXT_HOTELCARD_ACTIVE' => $_ARRAYLANG['TXT_HOTELCARD_ACTIVE'],
            'TXT_HOTELCARD_PROPERTY_NAME' => $_ARRAYLANG['TXT_HOTELCARD_NAME'],
            'TXT_HOTELCARD_PROPERTY_DESC' => $_ARRAYLANG['TXT_HOTELCARD_DESC'],
            'TXT_HOTELCARD_PROPERTY_EDIT' => $_ARRAYLANG['TXT_HOTELCARD_PROPERTY_EDIT'],
            'TXT_HOTELCARD_ORDER' => $objSorting->getOrderUriEncoded(),
        ));
        // Edit Property
        $objProperty = Property::getById($propertyId);
        if (!$objProperty) {
            $objProperty = new Property($propertyId);
        }
        $objText = Text::getById($objProperty->getTextId(), FRONTEND_LANG_ID);
        if (!$objText) {
            $objText = new Text(FRONTEND_LANG_ID, $objProperty->getTextId());
        }
        if (isset($_GET['clone'])) {
            $propertyId = 0;
        }
        self::$objTemplate->setCurrentBlock('propertyEdit');
        self::$objTemplate->setVariable(array(
            'HOTELCARD_PROPERTY_ID' => $propertyId,
            'HOTELCARD_PROPERTY_NAME' => htmlentities($objText->getName(), ENT_QUOTES, CONTREXX_CHARSET),
            'HOTELCARD_PROPERTY_DESC' => htmlentities($objText->getDesc(), ENT_QUOTES, CONTREXX_CHARSET),
        ));
        self::$objTemplate->parseCurrentBlock();

        // List Properties
        $arrProperties = Property::getArrayByLanguageId(FRONTEND_LANG_ID, false, true);
        usort($arrProperties, 'cmp_'.$objSorting->getOrderField());
        if ($objSorting->getOrderDirection() == 'DESC') {
            $arrProperties = array_reverse($arrProperties);
        }
        $i = 0;
        self::$objTemplate->setCurrentBlock('property');
        foreach ($arrProperties as $arrProperty) {
            self::$objTemplate->setVariable(array(
                'HOTELCARD_PROPERTY_ID' => $arrProperty['property_id'],
                'HOTELCARD_PROPERTY_NAME' => $arrProperty['name'],
                'HOTELCARD_PROPERTY_ORD' => $arrProperty['ord'],
                'HOTELCARD_ROW_CLASS' => (++$i % 2) + 1,
            ));
            self::$objTemplate->parseCurrentBlock();
        }
        return true;
    }


    /**
     * Store the edited Property in the database.
     *
     * Covers both new and updated Properties.
     * @return  integer             The Property ID on success, zero otherwise
     * @author  Reto Kohli <reto.kohli@comvation.com>
     */
    function storeProperty()
    {
        global $_ARRAYLANG;

        $propertyId = $_GET['id'];
        $propertyName = contrexx_stripslashes($_POST['propertyName']);
        $propertyDesc = contrexx_stripslashes($_POST['propertyDesc']);

        $objProperty = Property::getById($propertyId);
        if (!$objProperty) {
            $objProperty = new Property($propertyId);
        }

        $objText = Text::getById($objProperty->getTextId(), FRONTEND_LANG_ID);
        if (!$objText) {
            $objText = new Text(FRONTEND_LANG_ID);
        }
        $objText->setLanguageId(FRONTEND_LANG_ID);
        $objText->setName($propertyName);
        $objText->setDesc($propertyDesc);
        if (!$objText->store()) {
            self::addError($_ARRAYLANG['TXT_HOTELCARD_ERROR_TEXT_NOT_STORED']);
            return 0;
        }
        $objProperty->setTextId($objText->getId());
        if (!$objProperty->store()) {
            self::addError($_ARRAYLANG['TXT_HOTELCARD_ERROR_PROPERTY_NOT_STORED']);
            return 0;
        }
        self::addMessage($_ARRAYLANG['TXT_HOTELCARD_DATA_UPDATED_SUCCESSFULLY']);
        return $objProperty->getId();
    }


    /**
     * Store changes to the list of Properties in the database.
     * @return  boolean                 True on success, false otherwise
     * @author  Reto Kohli <reto.kohli@comvation.com>
     */
    function storeProperties()
    {
        global $_ARRAYLANG;

        if (isset($_POST['store2'])) {
            foreach ($_POST['ord'] as $propertyId => $ord) {
                if ($ord != $_POST['ord_old'][$propertyId]) {
                    $objProperty = Property::getById($propertyId);
                    if (!$objProperty) {
                        self::addError($_ARRAYLANG['TXT_HOTELCARD_ERROR_PROPERTY_NOT_FOUND']);
                        // Cannot store nothing.
                        return false;
                    }
                    $objProperty->setOrd($ord);
                    if (!$objProperty->store()) {
                        self::addError($_ARRAYLANG['TXT_HOTELCARD_ERROR_PROPERTY_NOT_STORED']);
                        return false;
                    }
                }
            }
        } elseif (!empty($_POST['multiAction'])) {
            $multiAction = $_POST['multiAction'];
            switch ($multiAction) {
              case 'delete':
                if (Property::deleteById(implode(',', $_POST['isSelected']))) {
                    self::addMessage($_ARRAYLANG['TXT_HOTELCARD_PROPERTIES_DELETED']);
                } else {
                    self::addError($_ARRAYLANG['TXT_HOTELCARD_ERROR_PROPERTIES_NOT_DELETED']);
                    return false;
                }
            }
        }
        return true;
    }


    /**
     * Handle any error occurring in this class.
     *
     * Tries to fix known problems with the database.
     * @global  mixed     $objDatabase    Database object
     * @return  boolean                   False.  Always.
     * @author  Reto Kohli <reto.kohli@comvation.com>
     */
    function errorHandler()
    {
        global $objDatabase;

        // Verify that the module is installed
        $query = "
            SELECT 1
              FROM ".DBPREFIX."modules
             WHERE name='hotelcard'
        ";
        $objResult = $objDatabase->Execute($query);
        if (!$objResult) return false;
        if ($objResult->RecordCount() == 0) {
            $query = "
                INSERT INTO ".DBPREFIX."modules (
                  `id`, `name`, `description_variable`,
                  `status`, `is_required`, `is_core`
                ) VALUES (
                  '101', 'hotelcard', 'TXT_HOTELCARD_MODULE_DESCRIPTION',
                  'y', '0', '0'
                )
            ";
            $objResult = $objDatabase->Execute($query);
            if (!$objResult) return false;
        }

        // Verify that the backend area is present
        $query = "
            SELECT 1
              FROM ".DBPREFIX."backend_areas
             WHERE uri='index.php?cmd=hotelcard'
        ";
        $objResult = $objDatabase->Execute($query);
        if (!$objResult) return false;
        if ($objResult->RecordCount() == 0) {
            $query = "
                INSERT INTO ".DBPREFIX."backend_areas (
                  `area_id`, `parent_area_id`, `type`, `area_name`,
                  `is_active`, `uri`, `target`, `module_id`, `order_id`, `access_id`
                ) VALUES (
                  126, '2', 'navigation', 'TXT_HOTELCARD',
                  '1', 'index.php?cmd=hotelcard', '_self', '101', '0', '126'
                );
            ";
            $objResult = $objDatabase->Execute($query);
            if (!$objResult) return false;
        }
        // Always!
        return false;
    }


    /**
     * Adds the string $strErrorMessage to the error messages.
     *
     * If necessary, inserts a line break tag (<br />) between
     * error messages.
     * @param   string  $strErrorMessage    The error message to add
     * @author  Reto Kohli <reto.kohli@comvation.com>
     */
    function addError($strErrorMessage)
    {
        self::$strErrMessage .=
            (self::$strErrMessage != '' && $strErrorMessage != ''
                ? '<br />' : ''
            ).$strErrorMessage;
    }


    /**
     * Adds the string $strOkMessage to the success messages.
     *
     * If necessary, inserts a line break tag (<br />) between
     * messages.
     * @param   string  $strOkMessage       The message to add
     * @author  Reto Kohli <reto.kohli@comvation.com>
     */
    function addMessage($strOkMessage)
    {
        self::$strOkMessage .=
            (self::$strOkMessage != '' && $strOkMessage != ''
                ? '<br />' : ''
            ).$strOkMessage;
    }


    function updateImageText()
    {
        // Get active frontend language IDs
        $objLang = new FWLanguage();
        $arrLanguages = $objLang->getLanguageArray();
        $arrLanguageId = array();
        foreach ($arrLanguages as $id => $arrLanguage) {
            if ($arrLanguage['frontend'])
                $arrLanguageId[] = $id;
        }
        // Product variant images
        $arrProducts = Product::getArrayByLanguageId(FRONTEND_LANG_ID, false);
        foreach ($arrProducts as $arrProduct) {
            $imageId = $arrProduct['image_id'];
            self::updateImageTextSingle($imageId, $arrLanguageId);
        }

        // Reference images
        // Product variant images
        $arrReferences = Reference::getArrayByLanguageId(FRONTEND_LANG_ID, false);
        foreach ($arrReferences as $arrReference) {
            $imageId = $arrReference['image_id'];
            self::updateImageTextSingle($imageId, $arrLanguageId);
        }
die("Finished.");
    }


    static function updateImageTextSingle($imageId, &$arrLanguageId)
    {
        $arrImages = Image::getArrayById($imageId);
        foreach ($arrImages as $arrImage) {
            $textId = $arrImage['text_id'];
            $objImage = false;
            $objText = Text::getById($textId, FRONTEND_LANG_ID);
            if (!$objText) {
                $objText = new Text(FRONTEND_LANG_ID);
                $objImage = Image::getById($imageId, $arrImage['ord']);
            }
            $strText = $objText->getName();
            if (!empty($strText)) {
                continue;
            }
            $filePath = $arrImage['path'];
            $fileName = preg_replace('/^.*\/([^\/]+)$/', "$1", $filePath);
            $objText->setName($fileName);
            foreach ($arrLanguageId as $langId) {
                $objText->setLanguageId($langId);
                $objText->store();
            }
            if ($objImage) {
                $objImage->setTextId($objText->getId());
                $objImage->store();
            }
        }

    }

}

Hotelcard::init();

?>
