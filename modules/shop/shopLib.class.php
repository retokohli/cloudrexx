<?php

/**
 * Shop library
 * @copyright   CONTREXX CMS - COMVATION AG
 * @author      Ivan Schmid <ivan.schmid@comvation.com>
 * @version     1.0.0
 * @package     contrexx
 * @subpackage  module_shop
 * @todo        Edit PHP DocBlocks!
 */

// Order status constant values
define('SHOP_ORDER_STATUS_PENDING',   0);
define('SHOP_ORDER_STATUS_CONFIRMED', 1);
define('SHOP_ORDER_STATUS_DELETED',   2);
define('SHOP_ORDER_STATUS_CANCELLED', 3);
define('SHOP_ORDER_STATUS_COMPLETED', 4);
define('SHOP_ORDER_STATUS_PAID',      5);
define('SHOP_ORDER_STATUS_SHIPPED',   6);
// Total number.  Keep this up to date!
define('SHOP_ORDER_STATUS_COUNT',     7);

// Payment result constant values
define('SHOP_PAYMENT_RESULT_SUCCESS_SILENT', -1);
define('SHOP_PAYMENT_RESULT_FAIL',            0);
define('SHOP_PAYMENT_RESULT_SUCCESS',         1);
define('SHOP_PAYMENT_RESULT_CANCEL',          2);
// Total number.  Keep this up to date!
define('SHOP_PAYMENT_RESULT_COUNT',           3);

/**
 * All the helping hands needed to run the shop
 * @copyright   CONTREXX CMS - COMVATION AG
 * @author      Ivan Schmid <ivan.schmid@comvation.com>
 * @access      public
 * @version     1.0.0
 * @package     contrexx
 * @subpackage  module_shop
 * @todo        Add a proper constructor that initializes the class with its
 *              various variables, and/or move the appropriate parts to
 *              a pure Shop class.
 */
class ShopLibrary
{
    /**
     * @todo These class variable *SHOULD* be initialized in the constructor,
     * otherwise it makes no sense to have them as class variables
     * -- unless they are indeed treated as public, which is dangerous.
     * Someone might try to access them before they are set up!
     */
    var $arrConfig = array();
    var $arrCurrencies = array();
    var $arrShipment = array();
    var $arrPayment = array();
    var $arrShopMailTemplate = array();

    /**
     * Array of all countries
     *
     * @var     array [$arrCountries] array of all countries
     * @access  public
     * @see     _initCountries()
     */
    var $arrCountries = array();


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


    /**
     * Returns an array of image names, widths and heights from
     * the base64 encoded string taken from the Database
     *
     * @param   string  $base64Str  The base64 encoded image string
     * @return  array               The decoded images, like:
     * array(1 => array('img' => <image1>, 'width' => <image1.width>, 'height' => <image1.height>),
     *       ... (three images in total)
     * )
     */
    function _getShopImagesFromBase64String($base64Str)
    {
        // Pre-init array to avoid "undefined index" notices
        $arrPictures = array(
            1 => array('img' => '', 'width' => 0, 'height' => 0),
            2 => array('img' => '', 'width' => 0, 'height' => 0),
            3 => array('img' => '', 'width' => 0, 'height' => 0)
        );
        if (strpos($base64Str, ':') === false) {
            // have to return an array with the desired number of elements
            // and an empty file name in order to show the "dummy" picture(s)
            return $arrPictures;
        }
        $i = 0;
        foreach (explode(':', $base64Str) as $imageData) {
            list($shopImage, $shopImage_width, $shopImage_height) = explode('?', $imageData);
            $shopImage        = base64_decode($shopImage);
            $shopImage_width  = base64_decode($shopImage_width);
            $shopImage_height = base64_decode($shopImage_height);
            $arrPictures[++$i] = array(
                'img'    => $shopImage,
                'width'  => $shopImage_width,
                'height' => $shopImage_height,
            );
        }
        return $arrPictures;
    }


    /**
     * In_array replacement for multi dim. arrays
     * NOT USED ANYMORE!
     *
     * @return boolean
     */
    /*
    function ______in_array_multi($needle, $haystack)
    {
       $found = false;
       foreach($haystack as $value) if((is_array($value) && in_array_multi($needle, $value)) || $value == $needle) $found = true;
       return $found;
    }
    */


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

        $onchange = !empty($onchange) ? "onchange=\"".$onchange."\"" : "";
        $menu = "\n<select name=\"".$menuName."\" ".$onchange.">\n";

        $query = "SELECT countries_id, countries_name ".
            "FROM ".DBPREFIX."module_shop".MODULE_INDEX."_countries ".
            "WHERE activation_status=1";
        $objResult = $objDatabase->Execute($query);

        if ($objResult->RecordCount() > 1) {
            while (!$objResult->EOF) {
                $selected = (intval($selectedId)==$objResult->fields['countries_id']) ? "selected=\"selected\"" : "";
                $menu .="<option value=\"".$objResult->fields['countries_id']."\" ".$selected.">".$objResult->fields['countries_name']."</option>\n";
                $objResult->MoveNext();
            }
            $menu .= "</select>\n";
        } else {
            $menu = "\n<input name=\"".$menuName."\" type=\"hidden\" value=\"".$objResult->fields['countries_id']."\">".$objResult->fields['countries_name']."\n";
        }
        return $menu;
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
        // init object from the framework
        $objShopLanguage = new FWLanguage();
        // gets indexed language array
        $arrLanguage = $objShopLanguage->getLanguageArray();

        $menu = "\n<select name=\"".$menuName."\">\n";
        $menu .= ($selectedId==0) ? "<option value=\"0\" selected=\"selected\">All</option>\n" : "<option value=\"0\">All</option>\n";

        foreach($arrLanguage AS $id => $data)
        {
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


    function _initPayment()
    {
        global $objDatabase;

         $query = "SELECT id, name, processor_id, costs, costs_free_sum, sort_order, status ".
                  "FROM ".DBPREFIX."module_shop".MODULE_INDEX."_payment ".
                  "ORDER BY sort_order";
         $objResult = $objDatabase->Execute($query);
         while (!$objResult->EOF) {
            $this->arrPayment[$objResult->fields['id']] = array(
                'id' => $objResult->fields['id'],
                'name' => $objResult->fields['name'],
                'processor_id' => $objResult->fields['processor_id'],
                'costs' => $objResult->fields['costs'],
                'costs_free_sum' => $objResult->fields['costs_free_sum'],
                'sort_order' => $objResult->fields['sort_order'],
                'status' => $objResult->fields['status']
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
     * @global $objDatabase Database object
     */
    function _initConfiguration()
    {
        global $objDatabase;

        $query = "
            SELECT id, name, value, status
              FROM ".DBPREFIX."module_shop".MODULE_INDEX."_config
        ";
        $objResult = $objDatabase->Execute($query);
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
     */
    //static
    function shopSendmail($shopMailTo, $shopMailFrom, $shopMailFromText, $shopMailSubject, $shopMailBody)
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
     * @global  mixed   $objDatabase        Database object
     * @return  mixed                       The mail template array on success,
     *                                      false otherwise
     */
    //static
    function shopSetMailtemplate($shopTemplateId, $langId)
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
     * @global  mixed   $objDatabase    Database object
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
     * @global  mixed     $objDatabase    Database object
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
}

?>
