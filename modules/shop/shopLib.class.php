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
 * Table and field index constants for the core_text table
 */
require_once('lib/keys.php');

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
    /**
     * @todo These class variable *SHOULD* be initialized in the constructor,
     * otherwise it makes no sense to have them as class variables
     * -- unless they are indeed treated as public, which is dangerous.
     * Someone might try to access them before they are set up!
     */
    public $arrConfig = array();

    /**
     * Upload directory for uploaded customer images
     * @var   string
     */
    public $uploadDir = false;


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
     * OBSOLETE
     * Returns HTML code for the payment handler dropdown menu
     * @param   string  $menuName
     * @param   string  $selectedId
     * @return  string
    function _getPaymentHandlerMenu($menuName='paymentHandler', $selectedId=0)
    {
        global $objDatabase;

        $query = "
            SELECT id, name
              FROM ".DBPREFIX."module_shop".MODULE_INDEX."_payment_processors
             WHERE status=1
             ORDER BY name
        ";
        $objResult = $objDatabase->Execute($query);
        if (!$objResult) return '';
        $menu = '<select name="'.$menuName.'">'."\n";
        while (!$objResult->EOF) {
            $menu .=
                '<option value="'.$objResult->fields['id'].'"'.
                ($selectedId == $objResult->fields['id']
                    ? ' selected="selected"' : ''
                ).'>'.$objResult->fields['name'].
                '</option>'."\n";
            $objResult->MoveNext();
        }
        $menu .= '</select>'."\n";
        return $menu;
    }
     */


    /**
     * Initialize the shop configuration array
     *
     * The array created contains all of the common shop settings.
     * @global ADONewConnection
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
/*
  Never used.
        $this->arrConfig['js_cart'] = array(
            'id'     => 9999,
            'value'  => '',
            'status' => '0',
        );
*/
    }


    /**
     * Checks that the email address isn't already used by an other customer
     *
     * @access  private
     * @global  ADONewConnection
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
     * @global  ADONewConnection
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
     * @global  ADONewConnection  $objDatabase  Database connection
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
     *
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
     * Also removes related order items, attributes, the customer, and the
     * user accounts created for the downloads.
     * @param   integer   $orderId        The order ID
     * @return  boolean                   True on success, false otherwise
     * @global  ADONewConnection  $objDatabase    Database connection object
     * @static
     * @author  Reto Kohli <reto.kohli@comvation.com>
     */
    static function deleteOrder($orderId)
    {
        global $objDatabase;

        $query = "
            SELECT customerid, order_date
              FROM ".DBPREFIX."module_shop_orders
             WHERE orderid=$orderId
        ";
        $objResult = $objDatabase->Execute($query);
        if (!$objResult) {
            return false;
        }
        $customerId = $objResult->fields['customerid'];
        $orderDate = $objResult->fields['order_date'];

        $query = "
            DELETE FROM ".DBPREFIX."module_shop_order_items_attributes
             WHERE order_id=$orderId
        ";
        $objResult = $objDatabase->Execute($query);
        if (!$objResult) {
            return false;
        }

        $query = "
            DELETE FROM ".DBPREFIX."module_shop_order_items
             WHERE orderid=$orderId
        ";
        $objResult = $objDatabase->Execute($query);
        if (!$objResult) {
            return false;
        }

        $query = "
            DELETE FROM ".DBPREFIX."module_shop_orders
             WHERE orderid=$orderId
        ";
        $objResult = $objDatabase->Execute($query);
        if (!$objResult) {
            return false;
        }

        $query = "
            DELETE FROM ".DBPREFIX."module_shop_customers
             WHERE customerid=$customerId
        ";
        $objResult = $objDatabase->Execute($query);
        if (!$objResult) {
            return false;
        }

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
        return true;
    }


    /**
     * Deletes the order with the given ID.
     *
     * If no valid ID is specified, looks in the GET and POST request
     * arrays for parameters called orderId and selectedOrderId, respectively.
     * Also removes related order items, attributes, the customer, and the
     * user accounts created for the downloads.
     * @param   integer   $orderId        The optional order ID
     * @return  boolean                   True on success, false otherwise
     * @global  mixed     $objDatabase    Database object
     */
    function deleteOrderOld($orderId=0)
    {
        global $objDatabase, $_ARRAYLANG;

        $arrOrderId = array();
        // prepare the array $arrOrderId with the ids of the orders to delete
        if (empty($orderId)) {
            if (isset($_GET['orderId']) && !empty($_GET['orderId'])) {
                array_push($arrOrderId, $_GET['orderId']);
            } elseif (isset($_POST['selectedOrderId']) && !empty($_POST['selectedOrderId'])) {
                $arrOrderId = $_POST['selectedOrderId'];
            }
        } else {
            array_push($arrOrderId, $orderId);
        }
        if (empty($arrOrderId)) return true;

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
                        //$this->addMessage("Datei $filename gel?scht");
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
        $this->addMessage($_ARRAYLANG['TXT_ORDER_DELETED']);
        return true;
    }


    /**
     * Add a line to the log file
     *
     * Prepends the current date and time to the string,
     * adds a line terminator and appends this to the log file.
     * Silently terminates if the log file cannot be opened for appending.
     * @param   string   $strLine     The entry to be logged
     * @static
     */
    static function addLog($strLine)
    {
        $fp = fopen(ASCMS_DOCUMENT_ROOT.'/shop.log', 'a');
        if (!$fp) return;
        fwrite($fp, date('Ymd His')." $strLine\n");
        fclose($fp);
    }

}

?>
