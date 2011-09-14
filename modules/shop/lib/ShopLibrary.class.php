<?php

/**
 * Shop library
 * @copyright   CONTREXX CMS - COMVATION AG
 * @author      Ivan Schmid <ivan.schmid@comvation.com>
 * @package     contrexx
 * @subpackage  module_shop
 * @version     2.1.0
 */

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
     * Payment result constant values
     * @author  Reto Kohli <reto.kohli@comvation.com>
     */
    const PAYMENT_RESULT_SUCCESS_SILENT = -1;
    const PAYMENT_RESULT_FAIL           =  0;
    const PAYMENT_RESULT_SUCCESS        =  1;
    const PAYMENT_RESULT_CANCEL         =  2;


    const REGISTER_MANDATORY = 'mandatory';
    const REGISTER_OPTIONAL = 'optional';
    const REGISTER_NONE = 'none';

    /**
     * Returns HTML dropdown menu code for all active frontend languages.
     * See {@link /lib/FRAMEWORK/Language.class.php}.
     * @param   string  $menuName   Optional name of the menu
     * @param   string  $selectedId Optional preselected language ID
     * @return  string  $menu       The dropdown menu string
     */
    function _getLanguageMenu($menuName='language', $selectedId=null)
    {
        return Html::getSelectCustom(
            $menuName, FWLanguage::getMenuoptions($selectedId));
    }


    /**
     * gets a select box with all the payment handlers
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
     * OBSOLETE -- see {@see MailTemplate}
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
    static function shopSetMailTemplate($shopTemplateId, $lang_id)
    {
        global $objDatabase;

        $query = "
            SELECT from_mail, xsender, subject, message
              FROM ".DBPREFIX."module_shop".MODULE_INDEX."_mail_content
             WHERE tpl_id=$shopTemplateId
               AND lang_id=$lang_id";
        $objResult = $objDatabase->Execute($query);
        if (!$objResult || $objResult->EOF) {
            return false;
        }
        $arrShopMailTemplate = array();
        $arrShopMailTemplate['mail_from'] = $objResult->fields['from_mail'];
        $arrShopMailTemplate['mail_x_sender'] = $objResult->fields['xsender'];
        $arrShopMailTemplate['mail_subject'] = $objResult->fields['subject'];
        $arrShopMailTemplate['mail_body'] = $objResult->fields['message'];
        return $arrShopMailTemplate;
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
     * OBSOLETE
     *
     * Checks that the email address isn't already used by an other customer
     * @access  private
     * @global          $objDatabase    Database object
     * @param   string  $email          The users' email address
     * @param   integer $customer_id    The customers' ID
     * @return  boolean                 True if the email address is unique, false otherwise
     */
    function _checkEmailIntegrity($email, $customer_id=0)
    {
        ++$email;
        ++$customer_id;
die("ShopLibrary::_checkEmailIntegrity(): Obsolete method called!");
/*
        global $objDatabase;

        $objResult = $objDatabase->Execute("
            SELECT customerid
              FROM ".DBPREFIX."module_shop".MODULE_INDEX."_customers
             WHERE email='$email'
               ".($customer_id > 0 ? "AND customerid!=$customer_id" : '')
        );
        if ($objResult && $objResult->RecordCount() == 0) {
            return true;
        }
        return false;
*/
    }


    /**
     * OBSOLETE
     *
     * Checks that the username isn't already used by an other customer
     * @access  private
     * @global          $objDatabase    Database object
     * @param   string  $username       The user name
     * @param   integer $customer_id    The customers' ID
     * @return  boolean                 True if the user name is unique, false otherwise
     */
    function _checkUsernameIntegrity($username, $customer_id=0)
    {
        ++$username;
        ++$customer_id;
die("ShopLibrary::_checkUsernameIntegrity(): Obsolete method called!");
/*
        global $objDatabase;

        $objResult = $objDatabase->Execute("
            SELECT customerid
              FROM ".DBPREFIX."module_shop".MODULE_INDEX."_customers
             WHERE username='$username'
               ".($customer_id > 0 ? "AND customerid!=$customer_id" : '')
        );
        if ($objResult && $objResult->RecordCount() == 0) {
            return true;
        }
        return false;
*/
    }


    /**
     * Convert the order ID and date to a custom order ID of the form
     * "lastnameYYY", where YYY is the order ID.
     *
     * This method may be customized to meet the needs of any shop owner.
     * The custom order ID may be used for creating user accounts for
     * protected downloads, for example.
     * @param   integer   $order_id       The order ID
     * @return  string                    The custom order ID
     * @global  ADONewConnection  $objDatabase    Database connection object
     * @author  Reto Kohli <reto.kohli@comvation.com>
     */
    function getCustomOrderId($order_id)
    {
        global $objDatabase;

        $query = "
            SELECT `customer_id`
              FROM `".DBPREFIX."module_shop".MODULE_INDEX."_orders`
             WHERE `id`=$order_id";
        $objResult = $objDatabase->Execute($query);
        if (!$objResult || $objResult->EOF) {
            return false;
        }
        $objCustomer = Customer::getById($objResult->fields['customer_id']);
        if (!$objCustomer) return false;
        return $objCustomer->lastname().$order_id;
        // Or something along the lines
        //$year = preg_replace('/^\d\d(\d\d).+$/', '$1', $orderDateTime);
        //return "$year-$order_id";
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
     * OBSOLETE -- REMOVE
     * Deletes the order with the given ID.
     *
     * If no valid ID is specified, looks in the GET and POST request
     * arrays for parameters called order_id and selectedOrderId, respectively.
     * Also removes related order items, attributes, uploaded files, and the
     * user accounts created for the downloads.
     * @todo    Fix user account deletion
     * @param   integer   $order_id       The optional order ID
     * @return  boolean                   True on success, false otherwise
     * @global  mixed     $objDatabase    Database object
     */
    function deleteOrder($order_id=0)
    {
        ++$order_id;
die("OBSOLETE: ShopLibrary::deleteOrder()");
/*
        global $objDatabase, $_ARRAYLANG;

        $arrOrderId = array();
        // Prepare the array with the IDs of the orders to delete
        if (empty($order_id)) {
            if (isset($_GET['order_id']) && !empty($_GET['order_id'])) {
                array_push($arrOrderId, $_GET['order_id']);
            } elseif (isset($_POST['selectedOrderId']) && !empty($_POST['selectedOrderId'])) {
                $arrOrderId = $_POST['selectedOrderId'];
            }
        } else {
            array_push($arrOrderId, $order_id);
        }
        if (empty($arrOrderId)) {
            return true;
        }

        // Delete selected orders
        foreach ($arrOrderId as $order_id) {
            // Delete files uploaded with the order
            $query = "
                SELECT product_option_value
                  FROM ".DBPREFIX."module_shop_order_items_attributes
                 WHERE order_id=$order_id";
            $objResult = $objDatabase->Execute($query);
            if (!$objResult) {
                self::errorHandling();
                return false;
            }
            while (!$objResult->EOF) {
                $filename =
                    ASCMS_PATH.'/'.$this->uploadDir.'/'.
                    $objResult->fields['product_option_value'];
                if (file_exists($filename)) {
                    if (@unlink($filename)) {
                        //self::addMessage("Datei $filename geloescht");
                    } else {
                        self::addError(sprintf($_ARRAYLANG['TXT_SHOP_ERROR_DELETING_FILE'], $filename));
                    }
                }
                $objResult->MoveNext();
            }

// Nope... see below.
//            $customer_id = $objResult->fields['customer_id'];
//            $orderDate = $objResult->fields['date_time'];

            $query = "
                DELETE FROM ".DBPREFIX."module_shop_order_items_attributes
                 WHERE order_id=$order_id";
            $objResult = $objDatabase->Execute($query);
            if (!$objResult) {
                self::errorHandling();
                return false;
            }

            $query = "
                DELETE FROM ".DBPREFIX."module_shop_order_items
                 WHERE order_id=$order_id";
            $objResult = $objDatabase->Execute($query);
            if (!$objResult) {
                self::errorHandling();
                return false;
            }

            $query = "
                DELETE FROM ".DBPREFIX."module_shop_orders
                 WHERE id=$order_id";
            $objResult = $objDatabase->Execute($query);
            if (!$objResult) {
                self::errorHandling();
                return false;
            }

// This needs a fix for the new account name format
//            // Remove automatically created accounts for downloads
//            $orderIdCustom = ShopLibrary::getCustomOrderId($order_id, $orderDate);
//            $objFWUser = FWUser::getFWUserObject();
//            $objUser = $objFWUser->objUser->getUsers(array('username' => $orderIdCustom.'-%'));
//            if ($objUser) {
//                while (!$objUser->EOF) {
//                    if (!$objUser->delete()) {
//                        return false;
//                    }
//                    $objUser->next();
//                }
//            }
        }
//        self::addMessage($_ARRAYLANG['TXT_ORDER_DELETED']);
        return true;
*/
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
     * Send a confirmation e-mail with the order data
     *
     * Calls {@see Orders::getSubstitutionArray()}, which en route
     * creates User accounts for individual electronic Products by default.
     * Set $create_accounts to false when sending a copy.
     * @static
     * @param   integer   $order_id         The order ID
     * @param   boolean   $create_accounts  Create User accounts for electronic
     *                                      Products it true
     * @return  boolean                     The Customers' e-mail address
     *                                      on success, false otherwise
     * @access  private
     */
    static function sendConfirmationMail($order_id, $create_accounts=true)
    {
        global $objDatabase;

        $arrSubstitution =
            Orders::getSubstitutionArray($order_id, $create_accounts);
        $customer_id = $arrSubstitution['CUSTOMER_ID'];
        $objCustomer = Customer::getById($customer_id);
        if (!$objCustomer) {
//die("Failed to get Customer for ID $customer_id");
            return false;
        }

// TODO: Test/fix mail with login data!

        $arrSubstitution += $objCustomer->getSubstitutionArray();
//die("sendConfirmationMail($order_id, $create_accounts): Subs: ".var_export($arrSubstitution, true));
        if (empty($arrSubstitution)) return false;
        // Prepared template for order confirmation
        $arrMailTemplate = array(
            'key'     => 'order_confirmation',
            'lang_id' => $arrSubstitution['LANG_ID'],
            'to'      =>
                $arrSubstitution['CUSTOMER_EMAIL'].','.
                SettingDb::getValue('email_confirmation'),
            'substitution' => &$arrSubstitution,
        );
//DBG::log("sendConfirmationMail($order_id, $create_accounts): Template: ".var_export($arrMailTemplate, true));
        if (!MailTemplate::send($arrMailTemplate)) return false;
        return $arrSubstitution['CUSTOMER_EMAIL'];
    }


    /**
     * Returns an array with all register options
     *
     * Keys are the respective class constant values, and the element values
     * are the language entries.
     * @see     getRegisterMenuoptions()
     * @return  array               The array of register options
     */
    static function getRegisterArray()
    {
        global $_ARRAYLANG;

        return array(
            self::REGISTER_MANDATORY => $_ARRAYLANG['TXT_SHOP_REGISTER_MANDATORY'],
            self::REGISTER_OPTIONAL => $_ARRAYLANG['TXT_SHOP_REGISTER_OPTIONAL'],
            self::REGISTER_NONE => $_ARRAYLANG['TXT_SHOP_REGISTER_NONE'],
        );
    }


    /**
     * Returns HTML code for the register menu options
     * @see     getRegisterArray()
     * @param   string    $selected     The optional selected option
     * @return  string                  The HTML options string
     */
    static function getRegisterMenuoptions($selected=null)
    {
        return Html::getOptions(self::getRegisterArray(), $selected);
    }

}

?>
