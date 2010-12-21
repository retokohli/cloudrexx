<?php

/**
 * Shop library
 * @copyright   CONTREXX CMS - COMVATION AG
 * @author      Ivan Schmid <ivan.schmid@comvation.com>
 * @package     contrexx
 * @subpackage  module_shop
 * @todo        Edit PHP DocBlocks!
 * @version     2.1.0
 */

/**
 * Order status constant values
 * @author  Reto Kohli <reto.kohli@comvation.com>
 */
define('SHOP_ORDER_STATUS_PENDING',   0);
define('SHOP_ORDER_STATUS_CONFIRMED', 1);
define('SHOP_ORDER_STATUS_DELETED',   2);
define('SHOP_ORDER_STATUS_CANCELLED', 3);
define('SHOP_ORDER_STATUS_COMPLETED', 4);
define('SHOP_ORDER_STATUS_PAID',      5);
define('SHOP_ORDER_STATUS_SHIPPED',   6);
/**
 * Total number of states.
 * @internal Keep this up to date!
 * @author  Reto Kohli <reto.kohli@comvation.com>
 */
define('SHOP_ORDER_STATUS_COUNT',     7);

/**
 * Payment result constant values
 * @author  Reto Kohli <reto.kohli@comvation.com>
 */
define('SHOP_PAYMENT_RESULT_SUCCESS_SILENT', -1);
define('SHOP_PAYMENT_RESULT_FAIL',            0);
define('SHOP_PAYMENT_RESULT_SUCCESS',         1);
define('SHOP_PAYMENT_RESULT_CANCEL',          2);
/**
 * Total number of possible results (-1 does count as 1)
 * @internal Keep this up to date!
 * @author  Reto Kohli <reto.kohli@comvation.com>
 */
define('SHOP_PAYMENT_RESULT_COUNT',           3);

/**
 * All the helping hands needed to run the shop
 * @copyright   CONTREXX CMS - COMVATION AG
 * @author      Ivan Schmid <ivan.schmid@comvation.com>
 * @access      public
 * @package     contrexx
 * @subpackage  module_shop
 * @todo        Add a proper constructor that initializes the class with its
 *              various variables, and/or move the appropriate parts to
 *              a pure Shop class.
 * @version     2.1.0
 */
class ShopLibrary
{
    const noPictureName = 'no_picture.gif';

    /**
     * @todo These class variable *SHOULD* be initialized in the constructor,
     * otherwise it makes no sense to have them as class variables
     * -- unless they are indeed treated as public, which is dangerous.
     * Someone might try to access them before they are set up!
     */
    public $arrConfig = array();

    /**
     * Array of all countries
     * @var     array [$arrCountries] array of all countries
     * @access  public
     * @see     _initCountries()
     */
    public $arrCountries = array();

    /**
     * Sorting order strings according to the corresponding setting
     *
     * Order 1: By order field value ascending, ID descending
     * Order 2: By title ascending, Product ID ascending
     * Order 3: By Product ID ascending, title ascending
     * @var     array
     * @see     Products::getByShopParam()
     * @author  Reto Kohli <reto.kohli@comvation.com>
     */
    public static $arrProductOrder = array(
        1 => 'p.sort_order ASC, p.id DESC',
        2 => 'p.title ASC, p.product_id ASC',
        3 => 'p.product_id ASC, p.title ASC',
    );


    /**
     * Returns an array of the zones
     * @return  array   The zones array
     */
    function _getZones()
    {
        global $objDatabase;
        $query = "SELECT zones_id, zones_name, activation_status FROM ".DBPREFIX."module_shop".MODULE_INDEX."_zones ORDER BY zones_name";
        $objResult = $objDatabase->Execute($query);
        while (!$objResult->EOF) {
            $arrZones[$objResult->fields['zones_id']] = array(
                    'zones_id'          => $objResult->fields['zones_id'],
                    'zones_name'        => $objResult->fields['zones_name'],
                    'activation_status' => $objResult->fields['activation_status']
                    );
            $objResult->MoveNext();
        }
        return $arrZones;
    }


    function _getRelCountries()
    {
        global $objDatabase;

        $query = "SELECT zones_id, countries_id FROM ".DBPREFIX."module_shop".MODULE_INDEX."_rel_countries ORDER BY id";
        $objResult = $objDatabase->Execute($query);
        while ($objResult && !$objResult->EOF) {
            $arrRelCountries[]=array($objResult->fields['zones_id'], $objResult->fields['countries_id']);
            $objResult->MoveNext();
        }
        return $arrRelCountries;
    }


    function _getZonesMenu($menuName="zone_id", $selectedId="", $onchange="")
    {
        global $objDatabase;

        $menu = "<input type='hidden' name='old_$menuName' value='$selectedId' />\n".
                "\n<select name='$menuName'".
                (!empty($onchange) ? " onchange='$onchange'" : '') .">\n";
        $query = "SELECT zones_id, zones_name FROM ".DBPREFIX."module_shop".MODULE_INDEX."_zones WHERE activation_status=1";
        $objResult = $objDatabase->Execute($query);
        while (!$objResult->EOF) {
            $menu .=
                "<option value='".$objResult->fields['zones_id']."'".
                (intval($selectedId)==intval($objResult->fields['zones_id'])
                    ? " selected='selected'"
                    : '').
                '>'.$objResult->fields['zones_name']."</option>\n";
            $objResult->MoveNext();
        }
        $menu .= "</select>\n";
        return $menu;
    }


    /**
     * Returns a dropdown menu or hidden input field (plus name) string
     * for the active country/-ies.
     *
     * If there is just one active country, returns a hidden <input> tag with the
     * countries' name appended.  If there are more, returns a dropdown menu with
     * the optional ID preselected and optional onchange method added.
     * @param   string  $menuName   Optional name of the menu
     * @param   string  $selectedId Optional pre-selected country ID
     * @param   string  $onchange   Optional onchange callback function
     * @return  string              The dropdown menu string
     */
    function _getCountriesMenu($menuName='countryId', $selectedId='', $onchange='')
    {
        global $objDatabase;

        $query = "
            SELECT countries_id, countries_name
              FROM ".DBPREFIX."module_shop".MODULE_INDEX."_countries
             WHERE activation_status=1";
        $objResult = $objDatabase->Execute($query);
        if ($objResult->RecordCount() > 1) {
            $menu =
                '<select name="'.$menuName.'"'.
                (empty($onchange)
                  ? ''
                  : ' onchange="'.$onchange.'"').">\n";
            while (!$objResult->EOF) {
                $menu .=
                    '<option value="'.$objResult->fields['countries_id'].'"'.
                    (intval($selectedId) == $objResult->fields['countries_id']
                      ? ' selected="selected"' : '').
                    '>'.$objResult->fields['countries_name']."</option>\n";
                $objResult->MoveNext();
            }
            return $menu."</select>\n";
        }
        return
            '<input name="'.$menuName.'" type="hidden" value="'.
            $objResult->fields['countries_id'].'" />'.
            $objResult->fields['countries_name']."\n";
    }


    /**
     * Returns a dropdown menu string with all available languages.
     * See {@link /lib/FRAMEWORK/Language.class.php}.
     *
     * @param   string  $menuName   Optional name of the menu
     * @param   string  $selectedId Optional preselected language ID
     * @return  string  $menu       The dropdown menu string
     */
    function _getLanguageMenu($menuName="language", $selectedId="")
    {
        // gets indexed language array
        $arrLanguage = FWLanguage::getLanguageArray();

        $menu = "\n<select name=\"".$menuName."\">\n";
        $menu .= ($selectedId==0) ? "<option value=\"0\" selected=\"selected\">All</option>\n" : "<option value=\"0\">All</option>\n";

        foreach ($arrLanguage as $id => $data) {
            $selected = (intval($selectedId)==$id) ? "selected=\"selected\"" : "";
            $menu .="<option value=\"".$id."\" $selected>".$data['name']."</option>\n";
        }
        $menu .= "</select>\n";
        return $menu;
    }


    function _initCountries()
    {
        global $objDatabase;

        $query = "
            SELECT countries_id, countries_name,
                   countries_iso_code_2, countries_iso_code_3,
                   activation_status
              FROM ".DBPREFIX."module_shop".MODULE_INDEX."_countries
             ORDER BY countries_id
         ";
         $objResult = $objDatabase->Execute($query);
         while (!$objResult->EOF) {
            $this->arrCountries[$objResult->fields['countries_id']] = array(
                'countries_id' => $objResult->fields['countries_id'],
                'countries_name' => $objResult->fields['countries_name'],
                'countries_iso_code_2' => $objResult->fields['countries_iso_code_2'],
                'countries_iso_code_3' => $objResult->fields['countries_iso_code_3'],
                'activation_status' => $objResult->fields['activation_status']
            );
            $objResult->MoveNext();
        }
    }


    /**
     * gets a select box with all the payment handlers
     *
     * @param   string  $menuName
     * @param   string  $selectedId
     * @return  string  $menu
     */
    function _getPaymentHandlerMenu($menuName='paymentHandler', $selectedId=0)
    {
        global $objDatabase;

        $menu = "\n<select name=\"".$menuName."\">\n";
        $query = "
            SELECT id, name
              FROM ".DBPREFIX."module_shop".MODULE_INDEX."_payment_processors
             WHERE status=1
             ORDER BY name
        ";
        $objResult = $objDatabase->Execute($query);
        while (!$objResult->EOF) {

            $menu .=
                '<option value="'.$objResult->fields['id'].'"'.
                ($selectedId == $objResult->fields['id']
                    ? ' selected="selected"' : ''
                ).'>'.$objResult->fields['name']."</option>\n";
            $objResult->MoveNext();
        }
        $menu .= "</select>\n";
        return $menu;
    }


    /**
     * Initialize the shop configuration array
     *
     * The array created contains all of the common shop settings.
     * @global  $objDatabase    Database object
     * @return                  True on success, false otherwise
     */
    function _initConfiguration()
    {
        global $objDatabase;

        $query = "
            SELECT id, name, value, status
              FROM ".DBPREFIX."module_shop".MODULE_INDEX."_config
        ";
        $objResult = $objDatabase->Execute($query);
        $this->arrConfig = array();
        if (!$objResult) return false;
        while (!$objResult->EOF) {
            $this->arrConfig[$objResult->fields['name']] = array(
                'id'     => $objResult->fields['id'],
                'value'  => $objResult->fields['value'],
                'status' => $objResult->fields['status'],
            );
            $objResult->MoveNext();
        }
        $this->arrConfig['js_cart'] = array(
            'id'     => 9999,
            'value'  => '',
            'status' => '0',
        );
        return true;
    }


    /**
     * Set up and send an email from the shop.
     * @static
     * @param   string    $shopMailTo           Recipient mail address
     * @param   string    $shopMailFrom         Sender mail address
     * @param   string    $shopMailFromText     Sender name
     * @param   string    $shopMailSubject      Message subject
     * @param   string    $shopMailBody         Message body
     * @return  boolean                         True if the mail could be sent,
     *                                          false otherwise
     * @author  Reto Kohli <reto.kohli@comvation.com>
     */
    static function shopSendmail($shopMailTo, $shopMailFrom, $shopMailFromText, $shopMailSubject, $shopMailBody)
    {
        global $_CONFIG;

        // replace cr/lf by lf only
        $shopMailBody = preg_replace('/\015\012/', "\012", $shopMailBody);

        if (@include_once ASCMS_LIBRARY_PATH.'/phpmailer/class.phpmailer.php') {
            $objMail = new phpmailer();
            if (   isset($_CONFIG['coreSmtpServer'])
                && $_CONFIG['coreSmtpServer'] > 0
                && @include_once ASCMS_CORE_PATH.'/SmtpSettings.class.php') {
                if (($arrSmtp = SmtpSettings::getSmtpAccount($_CONFIG['coreSmtpServer'])) !== false) {
                    $objMail->IsSMTP();
                    $objMail->Host = $arrSmtp['hostname'];
                    $objMail->Port = $arrSmtp['port'];
                    $objMail->SMTPAuth = true;
                    $objMail->Username = $arrSmtp['username'];
                    $objMail->Password = $arrSmtp['password'];
                }
            }
            $objMail->CharSet = CONTREXX_CHARSET;
            $objMail->From = preg_replace('/\015\012/', "\012", $shopMailFrom);
            $objMail->FromName = preg_replace('/\015\012/', "\012", $shopMailFromText);
            $objMail->AddReplyTo($_CONFIG['coreAdminEmail']);
            $objMail->Subject = $shopMailSubject;
            $objMail->IsHTML(false);
            $objMail->Body = $shopMailBody;
            $objMail->AddAddress($shopMailTo);
            if ($objMail->Send()) {
                return true;
            }
        }
        return false;
    }


    /**
     * Pick a mail template from the database
     *
     * Get the selected mail template and associated fields from the database.
     * @static
     * @param   integer $shopTemplateId     The mail template ID
     * @param   integer $langId             The language ID
     * @global  ADONewConnection  $objDatabase    Database connection object
     * @return  mixed                       The mail template array on success,
     *                                      false otherwise
     * @author  Reto Kohli <reto.kohli@comvation.com>
     */
    static function shopSetMailtemplate($shopTemplateId, $langId)
    {
        global $objDatabase;

        $query = "
            SELECT from_mail, xsender, subject, message
              FROM ".DBPREFIX."module_shop".MODULE_INDEX."_mail_content
             WHERE tpl_id=$shopTemplateId
               AND lang_id=$langId
        ";
        $objResult = $objDatabase->Execute($query);
        if ($objResult && !$objResult->EOF) {
            $arrShopMailTemplate['mail_from'] = $objResult->fields['from_mail'];
            $arrShopMailTemplate['mail_x_sender'] = $objResult->fields['xsender'];
            $arrShopMailTemplate['mail_subject'] = $objResult->fields['subject'];
            $arrShopMailTemplate['mail_body'] = $objResult->fields['message'];
            return $arrShopMailTemplate;
        }
        return false;
    }


    /**
     * Validate the email address
     *
     * Does an extensive syntax check to determine whether the string argument
     * is a real email address.
     * Note that this doesn't mean that the address is necessarily valid,
     * but only that it isn't just an arbitrary character sequence.
     * @todo    Some valid addresses are rejected by this method,
     * such as *%+@mymail.com.
     * Valid (atom) characters are: "!#$%&'*+-/=?^_`{|}~" (without the double quotes),
     * see {@link http://rfc.net/rfc2822.html RFC 2822} for details.
     * @todo    The rules applied to host names are not correct either, see
     * {@link http://rfc.net/rfc1738.html RFC 1738} and {@link http://rfc.net/rfc3986.html}.
     * Excerpt from RFC 1738:
     * - hostport       = host [ ":" port ]
     * - host           = hostname | hostnumber
     * - hostname       = *[ domainlabel "." ] toplabel
     * - domainlabel    = alphadigit | alphadigit *[ alphadigit | "-" ] alphadigit
     * - toplabel       = alpha | alpha *[ alphadigit | "-" ] alphadigit
     * - alphadigit     = alpha | digit
     * Excerpt from RFC 3986:
     * "Non-ASCII characters must first be encoded according to UTF-8 [STD63],
     * and then each octet of the corresponding UTF-8 sequence must be percent-
     * encoded to be represented as URI characters".
     * @todo    This doesn't really belong here.  Should be placed into a
     *          proper core e-mail class as a static method.
     * @version 1.0
     * @param   string  $string
     * @return  boolean
     */
    function shopCheckEmail($string)
    {
        if (preg_match(
            '/^[a-z0-9]+([-_\.a-z0-9]+)*'.  // user
            '@([a-z0-9]+([-\.a-z0-9]+)*)+'. // domain
            '\.[a-z]{2,4}$/',               // sld, tld
            $string
        )) {
            return true;
        }
        return false;
    }


    /**
     * Checks that the email address isn't already used by an other customer
     *
     * @access  private
     * @global          $objDatabase    Database object
     * @param   string  $email          The users' email address
     * @param   integer $customerId     The customers' ID
     * @return  boolean                 True if the email address is unique, false otherwise
     */
    function _checkEmailIntegrity($email, $customerId=0)
    {
        global $objDatabase;

        $objResult = $objDatabase->Execute("
            SELECT customerid
              FROM ".DBPREFIX."module_shop".MODULE_INDEX."_customers
             WHERE email='$email'
               ".($customerId > 0 ? "AND customerid!=$customerId" : '')
        );
        if ($objResult && $objResult->RecordCount() == 0) {
            return true;
        }
        return false;
    }


    /**
     * Checks that the username isn't already used by an other customer
     *
     * @access  private
     * @global          $objDatabase    Database object
     * @param   string  $username       The user name
     * @param   integer $customerId     The customers' ID
     * @return  boolean                 True if the user name is unique, false otherwise
     */
    function _checkUsernameIntegrity($username, $customerId=0)
    {
        global $objDatabase;

        $objResult = $objDatabase->Execute("
            SELECT customerid
              FROM ".DBPREFIX."module_shop".MODULE_INDEX."_customers
             WHERE username='$username'
               ".($customerId > 0 ? "AND customerid!=$customerId" : '')
        );
        if ($objResult && $objResult->RecordCount() == 0) {
            return true;
        }
        return false;
    }


    /**
     * Convert the order ID and date to a custom order ID of the form
     * "lastnameYYY", where YYY is the order ID.
     *
     * This method may be customized to meet the needs of any shop owner.
     * The custom order ID may be used for creating user accounts for
     * protected downloads, for example.
     * @param   integer   $orderId        The order ID
     * @return  string                    The custom order ID
     * @global  ADONewConnection  $objDatabase    Database connection object
     * @author  Reto Kohli <reto.kohli@comvation.com>
     */
    function getCustomOrderId($orderId)
    {
        global $objDatabase;

        $query = "
            SELECT lastname
              FROM ".DBPREFIX."module_shop".MODULE_INDEX."_orders
             INNER JOIN ".DBPREFIX."module_shop".MODULE_INDEX."_customers
             USING (customerid)
             WHERE orderid=$orderId
        ";
        $objResultOrder = $objDatabase->Execute($query);
        if (!$objResultOrder || $objResultOrder->RecordCount() == 0) {
            return false;
        }
        $lastname = $objResultOrder->fields['lastname'];
        return "$lastname$orderId";
        // Or something along the lines
        //$year = preg_replace('/^\d\d(\d\d).+$/', '$1', $orderDateTime);
        //return "$year-$orderId";
    }


    /**
     * Scale the given image size down to thumbnail size
     *
     * The target thumbnail size is taken from the configuration.
     * The argument and returned arrays use the indices as follows:
     *  array(0 => width, 1 => height)
     * In addition, index 3 of the array returned contains a
     * string with the width and height attribute string, very much like
     * the result of getimagesize().
     * Note that the array argument is passed by reference and its
     * values overwritten for the indices mentioned!
     * @param   array   $arrSize      The original image size array, by reference
     * @return  array                 The scaled down (thumbnail) image size array
     */
    function scaleImageSizeToThumbnail(&$arrSize)
    {
        $thumbWidthMax = $this->arrConfig['shop_thumbnail_max_width']['value'];
        $thumbHeightMax = $this->arrConfig['shop_thumbnail_max_height']['value'];
        $ratioWidth = $thumbWidthMax/$arrSize[0];
        $ratioHeight = $thumbHeightMax/$arrSize[1];
        if ($ratioWidth > $ratioHeight) {
            $arrSize[0] = intval($arrSize[0]*$ratioHeight);
            $arrSize[1] = $thumbHeightMax;
        } else {
            $arrSize[0] = $thumbWidthMax;
            $arrSize[1] = intval($arrSize[1]*$ratioWidth);
        }
        $arrSize[3] = 'width="'.$arrSize[0].'" height="'.$arrSize[1].'"';
        return $arrSize;
    }


    /**
     * Remove the uniqid part from a file name that was added after
     * uploading the file
     *
     * The file name to be matched should look something like
     *  filename[uniqid].ext
     * Where uniqid is a 13 digit hexadecimal value created by uniqid().
     * This method will then return
     *  filename.ext
     * @param   string    $strFilename    The file name with the uniqid
     * @return  string                    The original file name
     */
    function stripUniqidFromFilename($strFilename)
    {
        return preg_replace('/\[[0-9a-f]{13}\]/', '', $strFilename);
    }


    /**
     * Deletes the order with the given ID.
     *
     * If no valid ID is specified, looks in the GET and POST request
     * arrays for parameters called orderId and selectedOrderId, respectively.
     * Also removes related order items, attributes, uploaded files, and the
     * user accounts created for the downloads.
     * @todo    Fix user account deletion
     * @param   integer   $orderId        The optional order ID
     * @return  boolean                   True on success, false otherwise
     * @global  mixed     $objDatabase    Database object
     */
    function deleteOrder($orderId=0)
    {
        global $objDatabase, $_ARRAYLANG;

        $arrOrderId = array();
        // Prepare the array with the IDs of the orders to delete
        if (empty($orderId)) {
            if (isset($_GET['orderId']) && !empty($_GET['orderId'])) {
                array_push($arrOrderId, $_GET['orderId']);
            } elseif (isset($_POST['selectedOrderId']) && !empty($_POST['selectedOrderId'])) {
                $arrOrderId = $_POST['selectedOrderId'];
            }
        } else {
            array_push($arrOrderId, $orderId);
        }
        if (empty($arrOrderId)) {
            return true;
        }

        // Delete selected orders
        foreach ($arrOrderId as $orderId) {
            // Delete files uploaded with the order
            $query = "
                SELECT product_option_value
                  FROM ".DBPREFIX."module_shop_order_items_attributes
                 WHERE order_id=$orderId
            ";
            $objResult = $objDatabase->Execute($query);
            if (!$objResult) {
                $this->errorHandling();
                return false;
            }
            while (!$objResult->EOF) {
                $filename =
                    ASCMS_PATH.'/'.$this->uploadDir.'/'.
                    $objResult->fields['product_option_value'];
                if (file_exists($filename)) {
                    if (@unlink($filename)) {
                        //$this->addMessage("Datei $filename geloescht");
                    } else {
                        $this->addError(sprintf($_ARRAYLANG['TXT_SHOP_ERROR_DELETING_FILE'], $filename));
                    }
                }
                $objResult->MoveNext();
            }

// Nope... see below.
//            $customerId = $objResult->fields['customerid'];
//            $orderDate = $objResult->fields['order_date'];

            $query = "
                DELETE FROM ".DBPREFIX."module_shop_order_items_attributes
                 WHERE order_id=$orderId
            ";
            $objResult = $objDatabase->Execute($query);
            if (!$objResult) {
                $this->errorHandling();
                return false;
            }

            $query = "
                DELETE FROM ".DBPREFIX."module_shop_order_items
                 WHERE orderid=$orderId
            ";
            $objResult = $objDatabase->Execute($query);
            if (!$objResult) {
                $this->errorHandling();
                return false;
            }

            $query = "
                DELETE FROM ".DBPREFIX."module_shop_orders
                 WHERE orderid=$orderId
            ";
            $objResult = $objDatabase->Execute($query);
            if (!$objResult) {
                $this->errorHandling();
                return false;
            }

/*  Whoah...  You cannot possibly do that!
            $query = "
                DELETE FROM ".DBPREFIX."module_shop_customers
                 WHERE customerid=$customerId
            ";
            $objResult = $objDatabase->Execute($query);
            if (!$objResult) {
                $this->errorHandling();
                return false;
            }
*/

/*  This needs a fix for the new account name format
            // Remove automatically created accounts for downloads
            $orderIdCustom = ShopLibrary::getCustomOrderId($orderId, $orderDate);
            $objFWUser = FWUser::getFWUserObject();
            $objUser = $objFWUser->objUser->getUsers(array('username' => $orderIdCustom.'-%'));
            if ($objUser) {
                while (!$objUser->EOF) {
                    if (!$objUser->delete()) {
                        return false;
                    }
                    $objUser->next();
                }
            }
*/
        }
//        $this->addMessage($_ARRAYLANG['TXT_ORDER_DELETED']);
        return true;
    }


    /**
     * Returns a dropdown menu string with all available order status.
     *
     * The enclosing <select> tag is only added if the $menuName argument
     * is non-empty.  If that is empty, however, an additional header
     * option is added.  See {@link getOrderStatusMenuoptions()} for details.
     * @param   string  $selectedId     Optional preselected status ID
     * @param   string  $menuName       Optional menu name
     * @param   string  $onchange       Optional onchange callback function
     * @return  string  $menu           The dropdown menu string
     * @global  array
     */
    static function getOrderStatusMenu($selected='', $menuName='', $onchange='')
    {
        if ($menuName != '') {
            $menu =
                '<select name="'.$menuName.'" id="'.$menuName.'" '.
                ($onchange != '' ? 'onchange="'.$onchange.'"' : '').
                ">\n".
                self::getOrderStatusMenuoptions(
                    $selected, empty($menuName)
                ).
                "</select>\n";
        }
        return $menu;
    }


    /**
     * Returns the HTML menu options for selecting an order status
     *
     * Adds a "-- Status --" header option with empty string value
     * if the $flagFilter parameter is true.
     * @param   string      $selected       The value of the preselected status
     * @param   boolean     $flagFilter     If true, the header option is added
     * @return  string                      The HTML menu options string
     */
    static function getOrderStatusMenuoptions($selected='', $flagFilter=false)
    {
           global $_ARRAYLANG;

        $strMenuoptions =
            ($flagFilter
                ? '<option value="">-- '.
                  $_ARRAYLANG['TXT_STATUS'].
                  " --</option>\n"
                : ''
            );
        for ($i = SHOP_ORDER_STATUS_PENDING; $i < SHOP_ORDER_STATUS_COUNT; ++$i) {
            $strMenuoptions .=
                '<option value="'.$i.'"'.
                ($i === $selected ? ' selected="selected"' : '').'>'.
                $_ARRAYLANG['TXT_SHOP_ORDER_STATUS_'.$i]."</option>\n";
        }
        return $strMenuoptions;
    }


    /**
     * Moves Product or Category images to the shop image folder if necessary
     * and changes the given file path from absolute to relative to the
     * shop image folder
     *
     * Images outside the shop image folder are copied there and all folder
     * parts are stripped.
     * Images inside the shop image folder are left where they are.
     * The path is changed to represent the new location, relative to the
     * shop image folder.
     * Leading folder separators are removed.
     * The changed path *SHOULD* be stored in the picture field as-is.
     * Examples (suppose the shop image folder ASCMS_SHOP_IMAGES_WEB_PATH
     * is 'images/shop'):
     * /var/www/mydomain/upload/test.jpg becomes images/shop/test.jpg
     * /var/www/mydomain/images/shop/test.jpg becomes images/shop/test.jpg
     * /var/www/mydomain/images/shop/folder/test.jpg becomes images/shop/folder/test.jpg
     * @param   string    $imageFileSource    The absolute image path, by reference
     * @return  boolean                       True on success, false otherwise
     * @todo    The message on successful renaming cannot be displayed yet
     */
    static function moveImage(&$imageFileSource)
    {
        global $_ARRAYLANG;

        $arrMatch = array();
        $shopImageFolderRe = '/^'.preg_quote(ASCMS_SHOP_IMAGES_WEB_PATH.'/', '/').'/';
        $imageFileTarget = $imageFileSource;
        if (!preg_match($shopImageFolderRe, $imageFileSource))
            $imageFileTarget = ASCMS_SHOP_IMAGES_WEB_PATH.'/'.basename($imageFileSource);
        // If the image is situated in or below the shop image folder,
        // don't bother to copy it.
        if (!preg_match($shopImageFolderRe, $imageFileSource)) {
            if (   file_exists(ASCMS_PATH.$imageFileTarget)
                && preg_match('/(\.\w+)$/', $imageFileSource, $arrMatch)) {
                $imageFileTarget = preg_replace('/\.\w+$/', uniqid().$arrMatch[1], $imageFileTarget);
//                self::addMessage(
//                    sprintf(
//                        $_ARRAYLANG['TXT_SHOP_IMAGE_RENAMED_FROM_TO'],
//                        basename($imageFileSource), basename($imageFileTarget)
//                    )
//                );
            }
            if (!copy(ASCMS_PATH.$imageFileSource, ASCMS_PATH.$imageFileTarget)) {
                self::addError(
                    $imageFileSource.': '.
                    $_ARRAYLANG['TXT_SHOP_COULD_NOT_COPY_FILE']
                );
                $imageFileSource = false;
                return false;
            }
        }
        // Fix the original, absolute path to relative to the document root
        $imageFileSource = preg_replace($shopImageFolderRe, '', $imageFileTarget);
        return true;
    }


    /**
     * Returns a string with HTML code for the letter
     * dropdown menu options
     * @param   integer     $selected   The optional preselected letter
     * @return  string                  The Menuoptions HTML code
     */
    static function getListletterMenuoptions($selected)
    {
        global $_ARRAYLANG;

        $strMenuoptions =
            '<option value="">'.$_ARRAYLANG['TXT_SHOP_ALL'].'</option>';
        for ($i = 65; $i < 92; ++$i) {
            $letter = chr($i);
            $strMenuoptions .=
                '<option value="'.$letter.'"'.
                ($selected == $letter ? ' selected="selected"' : '').
                '>'.$letter.'</option>';
        }
        return $strMenuoptions;

    }

}

?>
