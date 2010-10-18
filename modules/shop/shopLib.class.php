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
    const thumbnailSuffix = '.thumb';
    const usernamePrefix = 'user';


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
     * @param   integer $lang_id             The language ID
     * @global  ADONewConnection  $objDatabase    Database connection object
     * @return  mixed                       The mail template array on success,
     *                                      false otherwise
     * @author  Reto Kohli <reto.kohli@comvation.com>
     */
    static function shopSetMailtemplate($shopTemplateId, $lang_id)
    {
        global $objDatabase;

        $query = "
            SELECT from_mail, xsender, subject, message
              FROM ".DBPREFIX."module_shop".MODULE_INDEX."_mail_content
             WHERE tpl_id=$shopTemplateId
               AND lang_id=$lang_id
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
        $thumbWidthMax = SettingDb::getValue('thumbnail_max_width');
        $thumbHeightMax = SettingDb::getValue('thumbnail_max_height');
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
     * @param   string  $selected       Optional selected status
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


    /**
     * Send a confirmation e-mail with the order data
     * @static
     * @param   integer   $order_id   The order ID
     * @return  boolean               True on success, false otherwise
     * @access  private
     */
    static function sendConfirmationMail($order_id, $create_accounts=true)
    {
        global $objDatabase;

        $return = true;
        $arrSubstitution =
            ShopLibrary::getOrderSubstitutionArray($order_id, $create_accounts);
        $customer_id = $arrSubstitution['CUSTOMER_ID'];
        $objCustomer = Customer::getById($customer_id);
        if (!$objCustomer) {
//die("Failed to get Customer for ID $customer_id");
            $return = false;
        } else {
            $arrSubstitution += $objCustomer->getSubstitutionArray();
//die("sendConfirmationMail($order_id, $create_accounts): Subs: ".var_export($arrSubstitution, true));
        }
        if (empty($arrSubstitution)) $return = false;
        // Prepared template for order confirmation
        $arrMailtemplate = array(
            'key'     => 1,
            'lang_id' => $arrSubstitution['LANG_ID'],
            'to'      =>
                $arrSubstitution['CUSTOMER_EMAIL'].','.
                SettingDb::getValue('email_confirmation'),
            'substitution' => &$arrSubstitution,
        );
//DBG::log("sendConfirmationMail($order_id, $create_accounts): Template: ".var_export($arrMailtemplate, true));
        if (!MailTemplate::send($arrMailtemplate)) $return = false;
        return $return;
    }


    /**
     * Returns an array with all placeholders and their values to be
     * replaced in any shop mailtemplate for the given order ID.
     *
     * You only have to set the 'substitution' index value of your Mailtemplate
     * array to the array returned.
     * Customer data is not included here.  See {@see Customer::getSubstitutionArray()}.
     * Note that this method is now mostly independent of the current session.
     * The language of the mail template is determined by the browser
     * language range stored with the order.
     * The password is no longer available in the session if the confirmation
     * is sent after paying with some external PSP that uses some form of
     * instant payment notification (i.e. PayPal)!
     * In that case, it is replaced by asterisks in the confirmation mail.
     * @access  private
     * @static
     * @param   integer $order_id     The order ID
     * @param   boolean $create_accounts  If true, creates User accounts
     *                                    and Coupon codes.  Defaults to true
     * @return  array                 The array with placeholders as keys
     *                                and values from the order on success,
     *                                false otherwise
     */
    static function getOrderSubstitutionArray($order_id, $create_accounts=true)
    {
        global $objDatabase, $_ARRAYLANG;

/*
            $_ARRAYLANG['TXT_SHOP_URI_FOR_DOWNLOAD'].":\r\n".
            'http://'.$_SERVER['SERVER_NAME'].
            "/index.php?section=download\r\n";
*/

        // Pick the order from the database
        // Note: order_sum is left out, as this is currently not set!
        $query = "
            SELECT customerid, customer_lang,
                   selected_currency_id,
                   currency_order_sum,
                   order_date, order_status,
                   ship_prefix, ship_company, ship_firstname, ship_lastname,
                   ship_address, ship_city, ship_zip, ship_country_id, ship_phone,
                   tax_price,
                   shipping_id, currency_ship_price,
                   payment_id, currency_payment_price,
                   customer_note, last_modified
              FROM ".DBPREFIX."module_shop".MODULE_INDEX."_orders
             WHERE orderid=$order_id";
        $objResult = $objDatabase->Execute($query);
        if (!$objResult || $objResult->RecordCount() == 0) {
            // Order not found
            return false;
        }
        $lang_id = $objResult->fields['customer_lang'];
        if (!intval($lang_id))
            $lang_id = FWLanguage::getLangIdByIso639_1($lang_id);
        $order_status = $objResult->fields['order_status'];
        $customer_id = $objResult->fields['customerid'];
        $payment_id = $objResult->fields['payment_id'];

        $arrSubstitution = array (
            'LANG_ID'             => $lang_id,
            'NOW'                 => date(ASCMS_DATE_FORMAT),
            'TODAY'               => date(ASCMS_DATE_SHORT_FORMAT),
//            'DATE'                => date(ASCMS_DATE_SHORT_FORMAT, strtotime($objResult->fields['order_date'])),
            'ORDER_ID'            => $order_id,
            'ORDER_ID_CUSTOM'     => ShopLibrary::getCustomOrderId($order_id),
            'ORDER_DATE'          =>
                date(ASCMS_DATE_SHORT_FORMAT,
                    strtotime($objResult->fields['order_date'])),
            'ORDER_TIME'          =>
                date(ASCMS_DATE_FORMAT,
                    strtotime($objResult->fields['order_date'])),
            'ORDER_STATUS_ID'     => $order_status,
            'ORDER_STATUS'        => $_ARRAYLANG['TXT_SHOP_ORDER_STATUS_'.$objResult->fields['order_status']],
            'MODIFIED'            =>
                date(ASCMS_DATE_FORMAT,
                    strtotime($objResult->fields['last_modified'])),
            'REMARKS'             => $objResult->fields['customer_note'],
            // Must be present in the Order, so the Customer can be found
            'CUSTOMER_ID'         => $customer_id,
            'SHIPPING_COMPANY'    => $objResult->fields['ship_company'],
            'SHIPPING_TITLE'      => $objResult->fields['ship_title'],
            'SHIPPING_FIRSTNAME'  => $objResult->fields['ship_firstname'],
            'SHIPPING_LASTNAME'   => $objResult->fields['ship_lastname'],
            'SHIPPING_ADDRESS'    => $objResult->fields['ship_address'],
            'SHIPPING_ZIP'        => $objResult->fields['ship_zip'],
            'SHIPPING_CITY'       => $objResult->fields['ship_city'],
            'SHIPPING_COUNTRY_ID' => $objResult->fields['ship_country_id'],
            'SHIPPING_COUNTRY'    => Country::getNameById(
                $objResult->fields['ship_country_id']),
            'SHIPPING_PHONE'      => $objResult->fields['ship_phone'],
            'SHIPPING_ID'         => $objResult->fields['shipping_id'],
            'SHIPPING_NAME'       => sprintf('%-40s', Shipment::getShipperName($objResult->fields['shipping_id'])),
            'SHIPPING_PRICE'      => sprintf('% 9.2f', $objResult->fields['currency_ship_price']),
//            'SHIP_PRICE'          => sprintf('% 9.2f', $objResult->fields['currency_ship_price']),
//            'CURRENCY_SHIP_PRICE' => sprintf('% 9.2f', $objResult->fields['currency_ship_price']),
            'PAYMENT_ID'          => $payment_id,
            'PAYMENT_NAME'        => sprintf('%-40s', Payment::getProperty($objResult->fields['payment_id'], 'name')),
            'PAYMENT_PRICE'       => sprintf('% 9.2f', $objResult->fields['currency_payment_price']),
//            'CURRENCY_PAYMENT_PRICE' => sprintf('% 9.2f', $objResult->fields['currency_payment_price']),
            'TAX_PRICE'           => sprintf('% 9.2f', $objResult->fields['tax_price']),
            'ORDER_SUM'           => sprintf('% 9.2f', $objResult->fields['currency_order_sum']),
//            'CURRENCY_ORDER_SUM'  => sprintf('% 9.2f', $objResult->fields['currency_order_sum']),
            'CURRENCY'            => Currency::getCodeById($objResult->fields['selected_currency_id']),
        );

// yantramatte
        $coupon_code = '';
        $objCoupon = null;
        // Pick the Coupon code Attribute, if any
        $query = "
            SELECT product_option_value, product_option_values_price
              FROM ".DBPREFIX."module_shop".MODULE_INDEX."_order_items_attributes
             WHERE order_id=$order_id
               AND product_option_name='".addslashes(Coupon::COUPON_ATTRIBUTE_NAME)."'";
        $objResult = $objDatabase->Execute($query);
        if ($objResult && !$objResult->EOF) {
            $coupon_code = $objResult->fields['product_option_value'];
            $arrSubstitution['DISCOUNT_COUPON'][] = array(
                'DISCOUNT_COUPON_CODE' => sprintf('%-40s',
                    $coupon_code),
                // Note that the price is stored with a negative sign already
                'DISCOUNT_COUPON_AMOUNT' => sprintf('% 9.2f',
                    $objResult->fields['product_option_values_price']),
            );
        }

        // Pick the order items from the database
        // order items: order_items_id, orderid, productid, product_name,
        //              price, quantity, vat_percent, weight
        $query = "
            SELECT order_items_id, productid, product_name, price, quantity
              FROM ".DBPREFIX."module_shop".MODULE_INDEX."_order_items
             WHERE orderid=$order_id";
        $objResultItem = $objDatabase->Execute($query);
        if (!$objResultItem || $objResultItem->EOF) {
            // Order not found
die("Order not found");
//            return false;
        }

// yantramatte
        // Deduct Order discounts for Coupons from each Product price
        $objCoupon = Coupon::get(
            $coupon_code, $arrSubstitution['ORDER_SUM'], $customer_id,
            0, $payment_id);
//        $discount_amount = 0;
        $discount_rate   = 0;
        if ($objCoupon) {
            $coupon_code = $objCoupon->code();
            if ($objCoupon->discount_rate())
                $discount_rate = $objCoupon->discount_rate();
//            if ($objCoupon->discount_amount())
//                $discount_amount = $objCoupon->discount_amount();
//DBG::log("ShopLibrary::getOrderSubstitutionArray(): Found Coupon; rate $discount_rate, amount $discount_amount");
        }

        $orderItemCount = 0;
        $priceTotalItems = 0;
        while (!$objResultItem->EOF) {
            $orderItemId = $objResultItem->fields['order_items_id'];
            $product_id = $objResultItem->fields['productid'];
//echo("Item: Product ID $product_id");
            $product_name = substr($objResultItem->fields['product_name'], 0, 40);
            $item_price = $objResultItem->fields['price'];

// yantramatte
            // Deduct Product discounts for Coupons from this Product price,
            // if applicable and no global Coupon is already in use
            if (!$coupon_code) {
                $objCoupon = Coupon::get(
                    $coupon_code, $arrSubstitution['ORDER_SUM'], $customer_id,
                    $product_id, $payment_id);
                if ($objCoupon) {
                    $coupon_code = $objCoupon->code();
                    if ($objCoupon->discount_rate())
                        $item_price -= $item_price * ($objCoupon->discount_rate() / 100);
//                    if ($objCoupon->discount_amount())
//                        $discount_amount = $objCoupon->discount_amount();
//DBG::log("ShopLibrary::getOrderSubstitutionArray(): Found Coupon; rate $discount_rate, amount $discount_amount");
                }
            }
// yantramatte
            // Deduct global discount, by rate in percent *only*
            if ($discount_rate) {
                $item_price -= $item_price * ($discount_rate / 100);
//DBG::log("ShopLibrary::getOrderSubstitutionArray(): Deducted $discount_rate%: $item_price");
            }
            $item_price = Currency::getCurrencyPrice($item_price);

            $quantity = $objResultItem->fields['quantity'];
// Add individual VAT rates for Products
//            $orderItemVatPercent = $objResultItem->fields['vat_percent'];

            $objProduct = Product::getById($product_id);
            if (!$objProduct) {
                $objResultItem->MoveNext();
die("Product ID $product_id not found");
                continue;
            }
            // Decrease the Product stock count,
            // applies to "real", shipped goods only
            $objProduct->decreaseStock($quantity);
            $product_code = $objProduct->code();

            // Pick the order items attributes from the database
            $query = "
                SELECT product_option_name, product_option_value
                  FROM ".DBPREFIX."module_shop".MODULE_INDEX."_order_items_attributes
                 WHERE order_items_id=$orderItemId
                 ORDER BY product_option_name ASC";
            $objResultAttribute = $objDatabase->Execute($query);
            $str_options = '';
            // Any attributes?
            if ($objResultAttribute && $objResultAttribute->RecordCount() > 0) {
                $str_options = '  '; // '[';
                $optionNamePrevious = '';
                while (!$objResultAttribute->EOF) {
                    $optionName = $objResultAttribute->fields['product_option_name'];
                    $optionValue = $objResultAttribute->fields['product_option_value'];
                    // Recognize the names of uploaded files,
                    // verify their presence and use the original name
                    $optionValueStripped = ShopLibrary::stripUniqidFromFilename($optionValue);
                    if (   $optionValue != $optionValueStripped
                        && file_exists(ASCMS_PATH.'/'.self::$uploadDir.'/'.$optionValue)) {
                            $optionValue = $optionValueStripped;
                    }
                    if ($optionName != $optionNamePrevious) {
                        if ($optionNamePrevious) {
                            $str_options .= '; ';
                        }
                        $str_options .= $optionName.': '.$optionValue;
                        $optionNamePrevious = $optionName;
                    } else {
                        $str_options .= ', '.$optionValue;
                    }
                    $objResultAttribute->MoveNext();
                }
//                $str_options .= ']';
            }

            // Product details
            $arrProduct = array(
                'PRODUCT_ID' => $product_id,
                'PRODUCT_CODE' => $product_code,
                'PRODUCT_QUANTITY' => $quantity,
                'PRODUCT_TITLE' => $product_name,
                'PRODUCT_OPTIONS' => $str_options,
                'PRODUCT_ITEM_PRICE' => sprintf('% 9.2f', $item_price),
                'PRODUCT_TOTAL_PRICE' => sprintf('% 9.2f', $item_price*$quantity),
            );
            $orderItemCount += $quantity;
            $priceTotalItems += $item_price*$quantity;

            if ($create_accounts) {
                // Add an account for every single instance of every Product
                for ($instance = 1; $instance <= $quantity; ++$instance) {
                    $validity = 0; // Default to unlimited validity
                    // In case there are protected downloads in the cart,
                    // collect the group IDs
                    $arrUsergroupId = array();
                    if ($objProduct->distribution() == 'download') {
                        $usergroupIds = $objProduct->usergroup_ids();
                        if ($usergroupIds != '') {
                            $arrUsergroupId = explode(',', $usergroupIds);
                            $validity = $objProduct->weight();
                        }
                    }
                    // create an account that belongs to all collected
                    // user groups, if any.
                    if (count($arrUsergroupId) > 0) {
                        // The login names are created separately for
                        // each product instance
                        $username = self::usernamePrefix."-$order_id-$product_id-$instance";
                        $userpass = uniqid();
                        $userEmail =
                            "shop_customer_${order_id}_${product_id}_${instance}-".
                            $arrSubstitution['CUSTOMER_EMAIL'];

                        $objUser = new User();
                        $objUser->setUsername($username);
                        $objUser->setPassword($userpass);
                        $objUser->setEmail($userEmail);
                        $objUser->setAdminStatus(false);
                        $objUser->setActiveStatus(true);
                        $objUser->setGroups($arrUsergroupId);
                        $objUser->setValidityTimePeriod($validity);
                        $objUser->setFrontendLanguage(FRONTEND_LANG_ID);
                        $objUser->setBackendLanguage(FRONTEND_LANG_ID);
                        $objUser->setProfile(array(
                            'firstname'    => array(0 => $arrSubstitution['CUSTOMER_FIRSTNAME']),
                            'lastname'     => array(0 => $arrSubstitution['CUSTOMER_LASTNAME']),
                            'company'      => array(0 => $arrSubstitution['CUSTOMER_COMPANY']),
                            'address'      => array(0 => $arrSubstitution['CUSTOMER_ADDRESS']),
                            'zip'          => array(0 => $arrSubstitution['CUSTOMER_ZIP']),
                            'city'         => array(0 => $arrSubstitution['CUSTOMER_CITY']),
                            'country'      => array(0 => $arrSubstitution['CUSTOMER_COUNTRY_ID']),
                            'phone_office' => array(0 => $arrSubstitution['CUSTOMER_PHONE']),
                            'phone_fax'    => array(0 => $arrSubstitution['CUSTOMER_FAX']),
                        ));
                        if (!$objUser->store()) {
                            // TODO: $this can't be used here due that this is a static function. There is so far no way to report an error in case one had occured.
                            // $this->statusMessage .= implode('<br />', $objUser->getErrorMsg());
                            return false;
                        }
                        if (empty($arrProduct['USER_DATA']))
                            $arrProduct['USER_DATA'] = array();
                        $arrProduct['USER_DATA'][] = array(
                            'USER_NAME' => $username,
                            'USER_PASS' => $userpass,
                        );
                    }
//echo("Instance $instance");
                    if ($objProduct->distribution() == 'coupon') {
                        if (empty($arrProduct['COUPON_DATA']))
                            $arrProduct['COUPON_DATA'] = array();
//DBG::log("Getting code");
                        $code = Coupon::getNewCode();
//DBG::log("Got code: $code, calling Coupon::addCode($code, 0, 0, 0, $item_price)");
                        Coupon::addCode($code, 0, 0, 0, $item_price, 0, 0, 1e10);
                        $arrProduct['COUPON_DATA'][] = array(
                            'COUPON_CODE' => $code
                        );
                    }
                }
                // "Use" the Coupon, if possible for the Product
                if ($coupon_code) {
                    $objCoupon = Coupon::useCoupon(
                        $coupon_code, $arrSubstitution['ORDER_SUM'],
                        $customer_id, $product_id, $payment_id
                    );
                    if ($objCoupon) $coupon_code = '';
                }

            }
            if (empty($arrSubstitution['ORDER_ITEM']))
                $arrSubstitution['ORDER_ITEM'] = array();
            $arrSubstitution['ORDER_ITEM'][] = $arrProduct;
            $objResultItem->MoveNext();
        }
        $arrSubstitution['ORDER_ITEM_SUM'] =
            sprintf('% 9.2f', $priceTotalItems);
        $arrSubstitution['ORDER_ITEM_COUNT'] = sprintf('% 4u', $orderItemCount);

        // "Use" the Coupon, if possible for the Order
        if ($coupon_code) {
            $objCoupon = Coupon::useCoupon(
                $coupon_code, $arrSubstitution['ORDER_ITEM_SUM'],
                $customer_id, 0, $payment_id
            );
        }

        if (Vat::isEnabled()) {
            $arrSubstitution['TAX_TEXT'] =
                sprintf('%-40s',
                    (Vat::isIncluded()
                        ? $_ARRAYLANG['TXT_SHOP_VAT_PREFIX_INCL']
                        : $_ARRAYLANG['TXT_SHOP_VAT_PREFIX_EXCL']
                    ));
        }
        return $arrSubstitution;
    }

}

?>
