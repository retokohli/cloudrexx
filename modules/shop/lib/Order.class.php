<?php

/**
 * Shop Order
 * @copyright   CONTREXX CMS - COMVATION AG
 * @author      Reto Kohli <reto.kohli@comvation.com>
 * @version     3.0.0
 * @package     contrexx
 * @subpackage  module_shop
 * @todo        Test!
 */

/**
 * Shop Order
 * @copyright   CONTREXX CMS - COMVATION AG
 * @author      Reto Kohli <reto.kohli@comvation.com>
 * @version     3.0.0
 * @package     contrexx
 * @subpackage  module_shop
 */
class Order
{
    /**
     * Order status constant values
     * @author  Reto Kohli <reto.kohli@comvation.com>
     */
    const STATUS_PENDING   = 0;
    const STATUS_CONFIRMED = 1;
    const STATUS_DELETED   = 2;
    const STATUS_CANCELLED = 3;
    const STATUS_COMPLETED = 4;
    const STATUS_PAID      = 5;
    const STATUS_SHIPPED   = 6;
    /**
     * Total number of states.
     * @internal Keep this up to date!
     * @author  Reto Kohli <reto.kohli@comvation.com>
     */
    const STATUS_MAX = 7;
    /**
     * Folder name for (image) file uploads in the Shop
     *
     * Note that this is prepended with the document root when necessary.
     */
    const UPLOAD_FOLDER = 'media/shop/upload/';

    protected $id = null;
    protected $customer_id = null;
    protected $currency_id = null;
    protected $shipment_id = null;
    protected $payment_id = null;
    protected $lang_id = 0;
    protected $status = 0;
    protected $sum = 0.00;
    protected $vat_amount = 0.00;
    protected $shipment_amount = 0.00;
    protected $payment_amount = 0.00;
    protected $gender = '';
    protected $company = '';
    protected $firstname = '';
    protected $lastname = '';
    protected $address = '';
    protected $city = '';
    protected $zip = '';
    protected $country_id = 0;
    protected $phone = '';
    protected $ip = '';
    protected $host = '';
    protected $browser = '';
    protected $note = '';
    protected $date_time = '0000-00-00 00:00:00';
    protected $modified_on = '0000-00-00 00:00:00';
    protected $modified_by = '';

/*  OBSOLETE
    ccNumber
    ccDate
    ccName
    ccCode
*/


    function id()
    {
        return $this->id;
    }

    function customer_id($customer_id=null)
    {
        if (isset($customer_id)) {
            $customer_id = intval($customer_id);
            if ($customer_id > 0) {
                $this->customer_id = $customer_id;
            }
        }
        return $this->customer_id;
    }

    function currency_id($currency_id=null)
    {
        if (isset($currency_id)) {
            $currency_id = intval($currency_id);
            if ($currency_id > 0) {
                $this->currency_id = $currency_id;
            }
        }
        return $this->currency_id;
    }

    function shipment_id($shipment_id=null)
    {
        if (isset($shipment_id)) {
            $shipment_id = intval($shipment_id);
            // May be empty (no shipment)!
            if ($shipment_id >= 0) {
                $this->shipment_id = $shipment_id;
            }
        }
        return $this->shipment_id;
    }

    function payment_id($payment_id=null)
    {
        if (isset($payment_id)) {
            $payment_id = intval($payment_id);
            if ($payment_id > 0) {
                $this->payment_id = $payment_id;
            }
        }
        return $this->payment_id;
    }

    function lang_id($lang_id=null)
    {
        if (isset($lang_id)) {
            $lang_id = intval($lang_id);
            if ($lang_id > 0) {
                $this->lang_id = $lang_id;
            }
        }
        return $this->lang_id;
    }

    function status($status=null)
    {
        if (isset($status)) {
            $status = intval($status);
            if ($status >= 0) {
                $this->status = $status;
            }
        }
        return $this->status;
    }

    function sum($sum=null)
    {
        if (isset($sum)) {
            $sum = floatval($sum);
            if ($sum >= 0) {
                $this->sum = number_format($sum, 2, '.', '');
            }
        }
        return $this->sum;
    }

    function vat_amount($vat_amount=null)
    {
        if (isset($vat_amount)) {
            $vat_amount = floatval($vat_amount);
            if ($vat_amount >= 0) {
                $this->vat_amount = number_format($vat_amount, 2, '.', '');
            }
        }
        return $this->vat_amount;
    }

    function shipment_amount($shipment_amount=null)
    {
        if (isset($shipment_amount)) {
            $shipment_amount = floatval($shipment_amount);
            if ($shipment_amount >= 0) {
                $this->shipment_amount = number_format($shipment_amount, 2, '.', '');
            }
        }
        return $this->shipment_amount;
    }

    function payment_amount($payment_amount=null)
    {
        if (isset($payment_amount)) {
            $payment_amount = floatval($payment_amount);
            if ($payment_amount >= 0) {
                $this->payment_amount = number_format($payment_amount, 2, '.', '');
            }
        }
        return $this->payment_amount;
    }

    function gender($gender=null)
    {
        if (isset($gender)) {
            $gender = trim(strip_tags($gender));
            if ($gender != '') {
                $this->gender = $gender;
            }
        }
        return $this->gender;
    }

    function company($company=null)
    {
        if (isset($company)) {
            $this->company = trim(strip_tags($company));
        }
        return $this->company;
    }

    function firstname($firstname=null)
    {
        if (isset($firstname)) {
            $firstname = trim(strip_tags($firstname));
            if ($firstname != '') {
                $this->firstname = $firstname;
            }
        }
        return $this->firstname;
    }

    function lastname($lastname=null)
    {
        if (isset($lastname)) {
            $lastname = trim(strip_tags($lastname));
            if ($lastname != '') {
                $this->lastname = $lastname;
            }
        }
        return $this->lastname;
    }

    function address($address=null)
    {
        if (isset($address)) {
            $address = trim(strip_tags($address));
            if ($address != '') {
                $this->address = $address;
            }
        }
        return $this->address;
    }

    function city($city=null)
    {
        if (isset($city)) {
            $city = trim(strip_tags($city));
            if ($city != '') {
                $this->city = $city;
            }
        }
        return $this->city;
    }

    function zip($zip=null)
    {
        if (isset($zip)) {
            $zip = trim(strip_tags($zip));
            if ($zip != '') {
                $this->zip = $zip;
            }
        }
        return $this->zip;
    }

    function country_id($country_id=null)
    {
        if (isset($country_id)) {
            $country_id = intval($country_id);
            if ($country_id > 0) {
                $this->country_id = $country_id;
            }
        }
        return $this->country_id;
    }

    function phone($phone=null)
    {
        if (isset($phone)) {
            $phone = trim(strip_tags($phone));
            if ($phone != '') {
                $this->phone = $phone;
            }
        }
        return $this->phone;
    }

    function ip($ip=null)
    {
        if (isset($ip)) {
            $ip = trim(strip_tags($ip));
            if ($ip != '') {
                $this->ip = $ip;
            }
        }
        return $this->ip;
    }

    function host($host=null)
    {
        if (isset($host)) {
            $host = trim(strip_tags($host));
            if ($host != '') {
                $this->host = $host;
            }
        }
        return $this->host;
    }

    function browser($browser=null)
    {
        if (isset($browser)) {
            $browser = trim(strip_tags($browser));
            if ($browser != '') {
                $this->browser = $browser;
            }
        }
        return $this->browser;
    }

    function note($note=null)
    {
        if (isset($note)) {
            $note = trim(strip_tags($note));
            if ($note != '') {
                $this->note = $note;
            }
        }
        return $this->note;
    }

    function date_time($date_time=null)
    {
        if (isset($date_time)) {
            $date_time = strtotime(trim(strip_tags($date_time)));
            if ($date_time > 0) {
                $this->date_time =
                    date(ASCMS_DATE_FORMAT_DATETIME, $date_time);
            }
        }
        return $this->date_time;
    }

    function modified_on($modified_on=null)
    {
        if (isset($modified_on)) {
            $modified_on = strtotime(trim(strip_tags($modified_on)));
            if ($modified_on > 0) {
                $this->modified_on =
                    date(ASCMS_DATE_FORMAT_DATETIME, $modified_on);
            }
        }
        return $this->modified_on;
    }

    function modified_by($modified_by=null)
    {
        if (isset($modified_by)) {
            $modified_by = trim(strip_tags($modified_by));
            if ($modified_by != '') {
                $this->modified_by = $modified_by;
            }
        }
        return $this->modified_by;
    }


    /**
     * Returns the Order for the ID given
     *
     * If the ID is invalid or no record is found for it, returns null.
     * @param   integer   $id       The Order ID
     * @return  Order               The object on success, null otherwise
     */
    static function getById($id)
    {
        global $objDatabase;

//DBG::activate(DBG_PHP|DBG_ADODB|DBG_LOG_FIREPHP);

        $query = "
            SELECT `id`, `customer_id`, `lang_id`, `currency_id`,
                   `shipment_id`, `payment_id`,
                   `status`,
                   `sum`,
                   `vat_amount`, `shipment_amount`, `payment_amount`,
                   `gender`, `company`, `firstname`, `lastname`,
                   `address`, `city`, `zip`, `country_id`, `phone`,
                   `ip`, `host`, `browser`,
                   `note`,
                   `date_time`, `modified_on`, `modified_by`
              FROM `".DBPREFIX."module_shop".MODULE_INDEX."_orders`
             WHERE `id`=".intval($id);
//DBG::activate(DBG_ADODB);
        $objResult = $objDatabase->Execute($query);
//DBG::deactivate(DBG_ADODB);
        if (!$objResult) return self::errorHandler();
        if ($objResult->EOF) {
//DBG::log("Order::getById(): Failed to get Order ID $id");
            return null;
        }
        $objOrder = new Order();
        $objOrder->id = $objResult->fields['id'];
        $objOrder->customer_id($objResult->fields['customer_id']);
        $objOrder->currency_id($objResult->fields['currency_id']);
        $objOrder->shipment_id($objResult->fields['shipment_id']);
        $objOrder->payment_id($objResult->fields['payment_id']);
        $objOrder->lang_id($objResult->fields['lang_id']);
        $objOrder->status($objResult->fields['status']);
        $objOrder->sum($objResult->fields['sum']);
        $objOrder->vat_amount($objResult->fields['vat_amount']);
        $objOrder->shipment_amount($objResult->fields['shipment_amount']);
        $objOrder->payment_amount($objResult->fields['payment_amount']);
        $objOrder->gender($objResult->fields['gender']);
        $objOrder->company($objResult->fields['company']);
        $objOrder->firstname($objResult->fields['firstname']);
        $objOrder->lastname($objResult->fields['lastname']);
        $objOrder->address($objResult->fields['address']);
        $objOrder->city($objResult->fields['city']);
        $objOrder->zip($objResult->fields['zip']);
        $objOrder->country_id($objResult->fields['country_id']);
        $objOrder->phone($objResult->fields['phone']);
        $objOrder->ip($objResult->fields['ip']);
        $objOrder->host($objResult->fields['host']);
        $objOrder->browser($objResult->fields['browser']);
        $objOrder->note($objResult->fields['note']);
        $objOrder->date_time($objResult->fields['date_time']);
        $objOrder->modified_on($objResult->fields['modified_on']);
        $objOrder->modified_by($objResult->fields['modified_by']);
        return $objOrder;
    }


    /**
     * Inserts a new Order into the database table
     *
     * Does not handle items nor attributes, see {@see insertItem()} and
     * {@see insertAttribute()} for that.
     * Fails if the ID is non-empty, or if the record cannot be inserted
     * for any reason.
     * Does not insert the shipment related properties if the shipment ID
     * is empty.  Those fields *SHOULD* default to NULL.
     * @return  integer             The ID of the record inserted on success,
     *                              false otherwise
     */
    function insert()
    {
        global $objDatabase, $_ARRAYLANG;

        if ($this->id) {
            return false;
        }
        // Ignores the shipment if not applicable
        $query = "
            INSERT INTO `".DBPREFIX."module_shop".MODULE_INDEX."_orders` (
                `customer_id`, `currency_id`, `sum`,
                `date_time`, `status`,
                `payment_id`, `payment_amount`,
                `vat_amount`,
                `ip`, `host`, `lang_id`,
                `browser`, `note`".
            ($this->shipment_id ? ',
                `company`, `gender`,
                `firstname`, `lastname`,
                `address`, `city`,
                `zip`, `country_id`, `phone`,
                `shipment_id`, `shipment_amount`' : '')."
            ) VALUES (
                $this->customer_id, $this->currency_id, $this->sum,
                ".($this->date_time ? "'$this->date_time'" : 'NOW()').",
                $this->status,
                $this->payment_id, $this->payment_amount,
                $this->vat_amount,
                '".addslashes($this->ip)."',
                '".addslashes($this->host)."',
                $this->lang_id,
                '".addslashes($this->browser)."',
                '".addslashes($this->note)."'".
            ($this->shipment_id ? ",
                '".addslashes($this->company)."',
                '".addslashes($this->gender)."',
                '".addslashes($this->firstname)."',
                '".addslashes($this->lastname)."',
                '".addslashes($this->address)."',
                '".addslashes($this->city)."',
                '".addslashes($this->zip)."',
                $this->country_id,
                '".addslashes($this->phone)."',
                $this->shipment_id,
                $this->shipment_amount" : '')."
            )";
        $objResult = $objDatabase->Execute($query);
        if (!$objResult) {
            Message::error($_ARRAYLANG['TXT_SHOP_ERROR_STORING_ORDER']);
            return false;
        }
        $this->id = $objDatabase->Insert_ID();
        return $this->id;
    }


    /**
     * Returns an array of Attributes and chosen options for this Order
     *
     * Options for uploads are linked to their respective files
     * The array looks like this:
     *  array(
     *    item ID => array(
     *      'attribute' => "Attribute name",
     *      'options' => array(
     *        order attribute id => array(
     *          'name' => "option name",
     *          'price' => "price",
     *         ),
     *       [... more ...]
     *      ),
     *    ),
     *    [... more ...]
     *  )
     * Note that the array may be empty.
     * @return  array           The Attribute/option array on success,
     *                          null otherwise
     */
    function getOptionArray()
    {
        global $objDatabase;

        $query = "
            SELECT `attribute`.`id`, `attribute`.`item_id`, `attribute`.`attribute_name`,
                   `attribute`.`option_name`, `attribute`.`price`
              FROM `".DBPREFIX."module_shop".MODULE_INDEX."_order_attributes` AS `attribute`
              JOIN `".DBPREFIX."module_shop".MODULE_INDEX."_order_items` AS `item`
                ON `attribute`.`item_id`=`item`.`id`
             WHERE `item`.`order_id`=".$this->id()."
             ORDER BY `attribute`.`attribute_name` ASC, `attribute`.`option_name` ASC";
        $objResult = $objDatabase->Execute($query);
        if (!$objResult) return self::errorHandler();
        $arrProductOptions = array();
        while (!$objResult->EOF) {
            $option_full = $objResult->fields['option_name'];
            $option = ShopLibrary::stripUniqidFromFilename($option_full);
            $path = Order::UPLOAD_FOLDER.$option_full;
            // Link option names to uploaded files
            if (   $option != $option_full
                && File::exists($path)) {
                $option =
                    '<a href="'.$path.'" target="uploadimage">'.$option.'</a>';
            }
            $id = $objResult->fields['id'];
            $price = $objResult->fields['price'];
            $arrProductOptions[$objResult->fields['item_id']]
                    [$objResult->fields['attribute_name']][$id] = array(
                'name' => $option,
                'price' => $price,
            );
            $objResult->MoveNext();
        }
        return $arrProductOptions;
    }


// TODO
    /**
     * Stores the Order as Posted
     * @global  array             $_ARRAYLANG     Language array
     * @global  ADONewConnection  $objDatabase    Database connection object
     * @static
     */
    static function storeFromPost()
    {
        global $objDatabase, $_ARRAYLANG;

        $order_id = (isset($_POST['order_id'])
            ? intval($_POST['order_id']) : null);
        if (empty($order_id)) return null;
        // calculate the total order sum in the selected currency of the customer
        $totalOrderSum =
            floatval($_POST['shippingPrice'])
          + floatval($_POST['paymentPrice']);
        // the tax amount will be set, even if it's included in the price already.
        // thus, we have to check the setting.
        if (!Vat::isIncluded()) {
            $totalOrderSum += floatval($_POST['taxPrice']);
        }
        // store the product details and add the price of each product
        // to the total order sum $totalOrderSum
        foreach ($_REQUEST['product_list'] as $orderItemId => $product_id) {
            if ($orderItemId != 0 && $product_id == 0) {
                // delete the product from the list
                $query = "
                    DELETE FROM ".DBPREFIX."module_shop".MODULE_INDEX."_order_items
                     WHERE id=$orderItemId";
                $objResult = $objDatabase->Execute($query);
                if (!$objResult) {
                    return self::errorHandler();
                }
                $query = "
                    DELETE FROM ".DBPREFIX."module_shop".MODULE_INDEX."_order_attributes
                     WHERE id=$orderItemId";
                $objResult = $objDatabase->Execute($query);
                if (!$objResult) {
                    return self::errorHandler();
                }
            } elseif ($product_id != 0) {
                $objProduct = Product::getById($product_id);
                if (!$objProduct) {
                    Message::error(sprintf(
                        $_ARRAYLANG['TXT_SHOP_PRODUCT_NOT_FOUND'],
                        $product_id));
                    continue;
                }
                $product_name = $objProduct->name();
                $price = Currency::formatPrice(
                    $_REQUEST['productPrice'][$orderItemId]);
                $quantity = max(1,
                    intval($_REQUEST['productQuantity'][$orderItemId]));
                $totalOrderSum += $price * $quantity;
                $vat_rate = Vat::format(
                    $_REQUEST['productTaxPercent'][$orderItemId]);
                $weight = Weight::getWeight(
                    $_REQUEST['productWeight'][$orderItemId]);
                if ($orderItemId == 0) {
                    // Add a new product to the list
                    if (!self::insertItem($order_id, $product_id, $product_name,
                        $price, $quantity, $vat_rate, $weight, array())) {
                        return false;
                    }
                } else {
                    // Update the order item
                    if (!self::updateItem($orderItemId, $product_id,
                        $product_name, $price, $quantity, $vat_rate, $weight, array())) {
                        return false;
                    }
                }
            }
        }
        $objUser = FWUser::getFWUserObject()->objUser;
        // store the order details
        $query = "
            UPDATE ".DBPREFIX."module_shop".MODULE_INDEX."_orders
               SET sum=".floatval($totalOrderSum).",
                   shipment_amount=".floatval($_POST['shippingPrice']).",
                   payment_amount=".floatval($_POST['paymentPrice']).",
                   status ='".intval($_POST['order_status'])."',
                   gender='".contrexx_input2db($_POST['shipPrefix'])."',
                   company='".contrexx_input2db($_POST['shipCompany'])."',
                   firstname='".contrexx_input2db($_POST['shipFirstname'])."',
                   lastname='".contrexx_input2db($_POST['shipLastname'])."',
                   address='".contrexx_input2db($_POST['shipAddress'])."',
                   city='".contrexx_input2db($_POST['shipCity'])."',
                   zip='".contrexx_input2db($_POST['shipZip'])."',
                   country_id=".intval($_POST['shipCountry']).",
                   phone='".contrexx_input2db($_POST['shipPhone'])."',
                   vat_amount=".floatval($_POST['taxPrice']).",
                   shipment_id=".intval($_POST['shipperId']).",
                   modified_by='".$objUser->getUsername()."',
                   modified_on=now()
             WHERE id=$order_id";
        // should not be changed, see above
        // ", payment_id = ".intval($_POST['paymentId']).
        if (!$objDatabase->Execute($query)) {
            Message::error($_ARRAYLANG['TXT_SHOP_ORDER_ERROR_STORING']);
            return self::errorHandler();
        }
        Message::ok($_ARRAYLANG['TXT_DATA_RECORD_UPDATED_SUCCESSFUL']);
        // Send an email to the customer, if requested
        if (!empty($_POST['sendMail'])) {
            $result = ShopLibrary::sendConfirmationMail($order_id);
            if (!$result) {
                return Message::error($_ARRAYLANG['TXT_MESSAGE_SEND_ERROR']);
            }
            Message::ok(sprintf($_ARRAYLANG['TXT_EMAIL_SEND_SUCCESSFULLY'], $result));
        }
        return true;
    }


    /**
     * Clear all shipment related properties
     *
     * Called by insert() when there is no shipment ID
     */
    function clearShipment()
    {
        $this->address = null;
        $this->city = null;
        $this->company = null;
        $this->country_id = null;
        $this->firstname = null;
        $this->lastname = null;
        $this->phone = null;
        $this->gender = null;
        $this->shipment_amount = 0;
        $this->shipment_id = null;
        $this->zip = null;
    }


    /**
     * Deletes this Order
     * @return  boolean                 True on success, false otherwise
     */
    function delete()
    {
        return self::deleteById($this->id);
    }


    /**
     * Deletes the Order with the given ID
     * @param   integer   $order_id     The Order ID
     * @return  boolean                 True on success, false otherwise
     */
    static function deleteById($order_id)
    {
        global $objDatabase, $_ARRAYLANG;

        $order_id = intval($order_id);
        if (empty($order_id)) return false;
        $arrItemId = self::getItemIdArray($order_id);
        if (!empty($arrItemId)) {
            foreach ($arrItemId as $item_id) {
                // Delete files uploaded with the order
                $query = "
                    SELECT `option_name`
                      FROM `".DBPREFIX."module_shop".MODULE_INDEX."_order_attributes`
                     WHERE `item_id`=$item_id";
                $objResult = $objDatabase->Execute($query);
                if (!$objResult) {
                    return self::errorHandler();
                }
                while (!$objResult->EOF) {
                    $path =
                        Order::UPLOAD_FOLDER.
                        $objResult->fields['option_name'];
                    if (File::exists($path)) {
                        if (!File::delete_file($path)) {
                            Message::error(sprintf(
                                $_ARRAYLANG['TXT_SHOP_ERROR_DELETING_FILE'], $path));
                        }
                    }
                    $objResult->MoveNext();
                }
                $query = "
                    DELETE FROM `".DBPREFIX."module_shop".MODULE_INDEX."_order_attributes`
                     WHERE `item_id`=$item_id";
                if (!$objDatabase->Execute($query)) {
                    return Message::error(
                        $_ARRAYLANG['TXT_SHOP_ERROR_DELETING_ORDER_ATTRIBUTES']);
                }
            }
        }
        $query = "
            DELETE FROM `".DBPREFIX."module_shop".MODULE_INDEX."_order_items`
             WHERE `order_id`=$order_id";
        if (!$objDatabase->Execute($query)) {
            return Message::error(
                $_ARRAYLANG['TXT_SHOP_ERROR_DELETING_ORDER_ITEMS']);
        }
        $query = "
            DELETE FROM `".DBPREFIX."module_shop".MODULE_INDEX."_lsv`
             WHERE `order_id`=$order_id";
        if (!$objDatabase->Execute($query)) {
            return Message::error(
                $_ARRAYLANG['TXT_SHOP_ERROR_DELETING_ORDER_LSV']);
        }
        // Remove accounts autocreated for downloads
// TODO: TEST!
        $objOrder = self::getById($order_id);
        if ($objOrder) {
            $customer_id = $objOrder->customer_id();
            $objCustomer = Customer::getById($customer_id);
            if ($objCustomer) {
                $customer_email =
                    Orders::usernamePrefix."_${order_id}_%-".
                    $objCustomer->email();
                $objUser = FWUser::getFWUserObject()->objUser->getUsers(
                    array('email' => $customer_email));
                if ($objUser) {
                    while (!$objUser->EOF) {
                        if (!$objUser->delete()) {
                            return false;
                        }
                        $objUser->next();
                    }
                }
            }
        }
        $query = "
            DELETE FROM `".DBPREFIX."module_shop".MODULE_INDEX."_orders`
             WHERE `id`=$order_id";
        if (!$objDatabase->Execute($query)) {
            return Message::error(
                $_ARRAYLANG['TXT_SHOP_ERROR_DELETING_ORDER']);
        }
        return true;
    }


    /**
     * Returns an array of item IDs for the given Order ID
     *
     * Mind that the returned array may be empty.
     * On failure, returns null.
     * @param   integer   $order_id   The Order ID
     * @return  array                 The array of item IDs on success,
     *                                null otherwise
     */
    static function getItemIdArray($order_id)
    {
        global $objDatabase, $_ARRAYLANG;

        $order_id = intval($order_id);
        $query = "
            SELECT `id`
              FROM `".DBPREFIX."module_shop".MODULE_INDEX."_order_items`
             WHERE `order_id`=$order_id";
        $objResult = $objDatabase->Execute($query);
        if (!$objResult) {
            Message::error(
                $_ARRAYLANG['TXT_SHOP_ERROR_QUERYING_ORDER_ITEMS']);
            return null;
        }
        $arrItemId = array();
        while (!$objResult->EOF) {
            $arrItemId[] = $objResult->fields['id'];
            $objResult->MoveNext();
        }
        return $arrItemId;
    }


    /**
     * Returns the time() value representing the date and time of the first
     * Order present in the database
     *
     * Returns null if there is no Order, or on error.
     * @return  integer               The first Order time, or null
     */
    static function getFirstOrderTime()
    {
        $count = 0;
        $arrOrder = Orders::getArray($count, 'date_time ASC', null, null, 1);
        if (empty($arrOrder)) return null;
        $objOrder = current($arrOrder);
        return strtotime($objOrder->date_time());
    }


    function insertItem($order_id, $product_id, $name, $price, $quantity,
        $vat_rate, $weight, $arrOptions
    ) {
        global $objDatabase, $_ARRAYLANG;

        $product_id = intval($product_id);
        if ($product_id <= 0) {
            return Message::error($_ARRAYLANG['TXT_SHOP_ORDER_ITEM_ERROR_INVALID_PRODUCT_ID']);
        }
        $quantity = intval($quantity);
        if ($quantity <= 0) {
            return Message::error($_ARRAYLANG['TXT_SHOP_ORDER_ITEM_ERROR_INVALID_QUANTITY']);
        }
        $weight = intval($weight);
        if ($weight < 0) {
            return Message::error($_ARRAYLANG['TXT_SHOP_ORDER_ITEM_ERROR_INVALID_WEIGHT']);
        }
        $query = "
            INSERT INTO ".DBPREFIX."module_shop".MODULE_INDEX."_order_items (
                order_id, product_id, product_name,
                price, quantity, vat_rate, weight
            ) VALUES (
                $order_id, $product_id, '".addslashes($name)."',
                '".Currency::formatPrice($price)."', $quantity,
                '".Vat::format($vat_rate)."', $weight
            )";
        $objResult = $objDatabase->Execute($query);
        if (!$objResult) {
            return Message::error($_ARRAYLANG['TXT_SHOP_ORDER_ITEM_ERROR_INSERTING']);
        }
        $item_id = $objDatabase->Insert_ID();
        foreach ($arrOptions as $attribute_id => $arrOptionIds) {
            if (!$this->insertAttribute($item_id, $attribute_id, $arrOptionIds)) {
                return false;
            }
        }
        return true;
    }


    function updateItem($item_id, $product_id, $name, $price, $quantity,
        $vat_rate, $weight, $arrOptions
    ) {
        global $objDatabase, $_ARRAYLANG;

        $item_id = intval($item_id);
        if ($item_id <= 0) {
            return Message::error($_ARRAYLANG['TXT_SHOP_ORDER_ITEM_ERROR_INVALID_ITEM_ID']);
        }
        $product_id = intval($product_id);
        if ($product_id <= 0) {
            return Message::error($_ARRAYLANG['TXT_SHOP_ORDER_ITEM_ERROR_INVALID_PRODUCT_ID']);
        }
        $quantity = intval($quantity);
        if ($quantity <= 0) {
            return Message::error($_ARRAYLANG['TXT_SHOP_ORDER_ITEM_ERROR_INVALID_QUANTITY']);
        }
        $weight = intval($weight);
        if ($weight < 0) {
            return Message::error($_ARRAYLANG['TXT_SHOP_ORDER_ITEM_ERROR_INVALID_WEIGHT']);
        }
        $query = "
            UPDATE ".DBPREFIX."module_shop".MODULE_INDEX."_order_items
               SET `product_id`=$product_id,
                   `product_name`='".addslashes($name)."',
                   `price`='".Currency::formatPrice($price)."',
                   `quantity`=$quantity,
                   `vat_rate`='".Vat::format($vat_rate)."',
                   `weight`=$weight
             WHERE `id`=$item_id";
        $objResult = $objDatabase->Execute($query);
        if (!$objResult) {
            return Message::error($_ARRAYLANG['TXT_SHOP_ORDER_ITEM_ERROR_UPDATING']);
        }
        if (!self::deleteOptions($item_id)) return false;
        foreach ($arrOptions as $attribute_id => $arrOptionIds) {
            if (!$this->insertAttribute($item_id, $attribute_id, $arrOptionIds)) {
                return false;
            }
        }
        return true;
    }


    /**
     * Add the chosen option(s) of the given Attribute ID to the Order item
     *
     * Will add error messages using {@see Message::error()}, if any.
     * The $arrOptionIds array has the form
     *  array(attribute_id => array(option_id, ...))
     * @param   integer   $item_id        The Order item ID
     * @param   integer   $attribute_id   The Attribute ID
     * @param   array     $arrOptionIds   The array of option IDs
     * @return  boolean                   True on success, false otherwise
     */
    function insertAttribute($item_id, $attribute_id, $arrOptionIds)
    {
        global $objDatabase, $_ARRAYLANG;

        $objAttribute = Attribute::getById($attribute_id);
        if (!$objAttribute) {
            return Message::error($_ARRAYLANG['TXT_SHOP_ERROR_INVALID_ATTRIBUTE_ID']);
        }
        $name = $objAttribute->getName();
        $_arrOptions = Attributes::getOptionArrayByAttributeId($attribute_id);
        foreach ($arrOptionIds as $option_id) {
            $arrOption = null;
            if ($objAttribute->getType() >= Attribute::TYPE_TEXT_OPTIONAL) {
                // There is exactly one option record for these
                // types.  Use that and overwrite the empty name with
                // the text or file name.
                $arrOption = current($_arrOptions);
                $arrOption['value'] = $option_id;
            } else {
                // Use the option record for the option ID given
                $arrOption = $_arrOptions[$option_id];
            }
            if (!is_array($arrOption)) {
                Message::error($_ARRAYLANG['TXT_SHOP_ERROR_INVALID_OPTION_ID']);
                continue;
            }
            $query = "
                INSERT INTO `".DBPREFIX."module_shop".MODULE_INDEX."_order_attributes`
                   SET `item_id`=$item_id,
                       `attribute_name`='".addslashes($name)."',
                       `option_name`='".addslashes($arrOption['value'])."',
                       `price`='".$arrOption['price']."'";
            $objResult = $objDatabase->Execute($query);
            if (!$objResult) {
                return Message::error($_ARRAYLANG['TXT_ERROR_INSERTING_ORDER_ITEM_ATTRIBUTE']);
            }
        }
        return true;
    }


    /**
     * Deleted the options associated with the Order item with the given ID
     *
     * Will add error messages using {@see Message::error()}, if any.
     * @param   integer   $item_id        The Order item ID
     * @return  boolean                   True on success, false otherwise
     */
    static function deleteOptions($item_id)
    {
        global $objDatabase, $_ARRAYLANG;

        $item_id = intval($item_id);
        if ($item_id > 0) {
            $query = "
                DELETE FROM `".DBPREFIX."module_shop".MODULE_INDEX."_order_attributes`
                 WHERE `item_id`=$item_id";
            if ($objDatabase->Execute($query)) {
                return true;
            }
        }
        return Message::error(
            $_ARRAYLANG['TXT_SHOP_ORDER_ITEM_ERROR_DELETING_ATTRIBUTES']);
    }


// TODO -- From admin.class
    /**
     * Set up details of the selected order
     * @access  public
     * @param   HTML_Template_Sigma $objTemplate    The Template, by reference
     * @param   boolean             $edit           Edit if true, view otherwise
     * @global  ADONewConnection    $objDatabase    Database connection object
     * @global  array               $_ARRAYLANG     Language array
     * @static
     * @author  Reto Kohli <reto.kohli@comvation.com> (parts)
     */
    static function view_detail(&$objTemplate=null, $edit=false)
    {
        global $objDatabase, $_ARRAYLANG;

        // The order total -- in the currency chosen by the customer
//        $order_sum = 0;
        // recalculated VAT total
        $total_vat_amount = 0;
        $order_id = intval($_REQUEST['order_id']);
        if (!$order_id) {
            return Message::error(
                $_ARRAYLANG['TXT_SHOP_ORDER_ERROR_INVALID_ORDER_ID']);
        }
        if (!$objTemplate) {
            $template_name = ($edit
              ? 'module_shop_order_edit.html'
              : 'module_shop_order_details.html');
            $objTemplate = new HTML_Template_Sigma(
                ASCMS_MODULE_PATH.'/shop/template');
//DBG::log("Orders::view_list(): new Template: ".$objTemplate->get());
            $objTemplate->loadTemplateFile($template_name);
//DBG::log("Orders::view_list(): loaded Template: ".$objTemplate->get());
        }
        $objOrder = Order::getById($order_id);
        if (!$objOrder) {
//DBG::log("Shop::shopShowOrderdetails(): Failed to find Order ID $order_id");
            return Message::error(sprintf(
                $_ARRAYLANG['TXT_SHOP_ORDER_NOT_FOUND'], $order_id));
        }

// TODO FROM HERE

        // lsv data
        $query = "
            SELECT `holder`, `bank`, `blz`
              FROM ".DBPREFIX."module_shop".MODULE_INDEX."_lsv
             WHERE order_id=$order_id";
        $objResult = $objDatabase->Execute($query);
        if (!$objResult) {
            return self::errorHandler();
        }
        if ($objResult->RecordCount() == 1) {
            $objTemplate->setVariable(array(
                'SHOP_ACCOUNT_HOLDER' => contrexx_raw2xhtml(
                    $objResult->fields['holder']),
                'SHOP_ACCOUNT_BANK' => contrexx_raw2xhtml(
                    $objResult->fields['bank']),
                'SHOP_ACCOUNT_BLZ' => contrexx_raw2xhtml(
                    $objResult->fields['blz']),
            ));
        }

        $customer_id = $objOrder->customer_id();
        if (!$customer_id) {
//DBG::log("Shop::shopShowOrderdetails(): Invalid Customer ID $customer_id");
            return Message::error(sprintf(
                $_ARRAYLANG['TXT_SHOP_INVALID_CUSTOMER_ID'], $customer_id));
        }
        $objCustomer = Customer::getById($customer_id);
        if (!$objCustomer) {
//DBG::log("Shop::shopShowOrderdetails(): Failed to find Customer ID $customer_id");
            return Message::error(sprintf(
                $_ARRAYLANG['TXT_SHOP_CUSTOMER_NOT_FOUND'], $customer_id));
        }
        Vat::is_reseller($objCustomer->is_reseller());
        Vat::is_home_country(
            SettingDb::getValue('country_id') == $objOrder->country_id());
        $objTemplate->setGlobalVariable($_ARRAYLANG
          + array(
            'SHOP_CURRENCY' =>
                Currency::getCurrencySymbolById($objOrder->currency_id())));
//DBG::log("Order sum: ".Currency::formatPrice($objOrder->sum()));
        $objTemplate->setVariable(array(
            'SHOP_CUSTOMER_ID' => $customer_id,
            'SHOP_ORDERID' => $order_id,
            'SHOP_DATE' => date(ASCMS_DATE_FORMAT_DATETIME,
                strtotime($objOrder->date_time())),
            'SHOP_ORDER_STATUS' => ($edit
                ? Orders::getStatusMenu(
                    $objOrder->status(), false, null,
                    'swapSendToStatus(this.value)')
                : $_ARRAYLANG['TXT_SHOP_ORDER_STATUS_'.$objOrder->status()]),
            'SHOP_SEND_MAIL_STYLE' =>
                ($objOrder->status() == Order::STATUS_CONFIRMED
                    ? 'display: inline;' : 'display: none;'),
            'SHOP_SEND_MAIL_STATUS' => ($edit
                ? ($objOrder->status() != Order::STATUS_CONFIRMED
                    ? HTML_ATTRIBUTE_CHECKED : '')
                : ''),
            'SHOP_ORDER_SUM' => Currency::formatPrice($objOrder->sum()),
            'SHOP_DEFAULT_CURRENCY' => Currency::getDefaultCurrencySymbol(),
            'SHOP_GENDER' =>
                $_ARRAYLANG['TXT_SHOP_'.strtoupper($objCustomer->gender())],
            'SHOP_COMPANY' => $objCustomer->company(),
            'SHOP_FIRSTNAME' => $objCustomer->firstname(),
            'SHOP_LASTNAME' => $objCustomer->lastname(),
            'SHOP_ADDRESS' => $objCustomer->address(),
            'SHOP_ZIP' => $objCustomer->zip(),
            'SHOP_CITY' => $objCustomer->city(),
            'SHOP_COUNTRY' => Country::getNameById($objCustomer->country_id()),
            'SHOP_SHIP_GENDER' => ($edit
                ? Customer::getGenderMenu($objOrder->gender(), 'shipPrefix')
                : $_ARRAYLANG['TXT_SHOP_'.strtoupper($objOrder->gender())]),
            $_ARRAYLANG['TXT_SHOP_'.strtoupper($objOrder->gender())],
            'SHOP_SHIP_COMPANY' => $objOrder->company(),
            'SHOP_SHIP_FIRSTNAME' => $objOrder->firstname(),
            'SHOP_SHIP_LASTNAME' => $objOrder->lastname(),
            'SHOP_SHIP_ADDRESS' => $objOrder->address(),
            'SHOP_SHIP_ZIP' => $objOrder->zip(),
            'SHOP_SHIP_CITY' => $objOrder->city(),
            'SHOP_SHIP_COUNTRY' => ($edit
                ? Country::getMenu('shipCountry', $objOrder->country_id())
                : Country::getNameById($objOrder->country_id())),
            'SHOP_SHIP_PHONE' => $objOrder->phone(),
            'SHOP_PHONE' => $objCustomer->phone(),
            'SHOP_FAX' => $objCustomer->fax(),
            'SHOP_EMAIL' => $objCustomer->email(),
            'SHOP_PAYMENTTYPE' => Payment::getProperty($objOrder->payment_id(), 'name'),
// OBSOLETE
//            'SHOP_CCNUMBER' => $objResult->fields['ccnumber'],
//            'SHOP_CCDATE' => $objResult->fields['ccdate'],
//            'SHOP_CCNAME' => $objResult->fields['ccname'],
//            'SHOP_CVC_CODE' => $objResult->fields['cvc_code'],
            'SHOP_CUSTOMER_NOTE' => $objOrder->note(),
            'SHOP_CUSTOMER_IP' => ($objOrder->ip()
                ? '<a href="index.php?cmd=nettools&amp;tpl=whois&amp;address='.
                  $objOrder->ip().'" title="'.$_ARRAYLANG['TXT_SHOW_DETAILS'].'">'.
                  $objOrder->ip().'</a>'
                : '&nbsp;'),
            'SHOP_CUSTOMER_HOST' => ($objOrder->host()
                ? '<a href="index.php?cmd=nettools&amp;tpl=whois&amp;address='.
                  $objOrder->host().'" title="'.$_ARRAYLANG['TXT_SHOW_DETAILS'].'">'.
                  $objOrder->host().'</a>'
                : '&nbsp;'),
            'SHOP_CUSTOMER_LANG' => FWLanguage::getLanguageParameter(
                $objOrder->lang_id(), 'name'),
            'SHOP_CUSTOMER_BROWSER' => ($objOrder->browser()
                ? $objOrder->browser() : '&nbsp;'),
            'SHOP_COMPANY_NOTE' => $objCustomer->companynote(),
            'SHOP_LAST_MODIFIED' =>
                (   $objOrder->modified_on()
                 && $objOrder->modified_on() != '0000-00-00 00:00:00'
                  ? $objOrder->modified_on().'&nbsp;'.
                    $_ARRAYLANG['TXT_EDITED_BY'].'&nbsp;'.
                    $objOrder->modified_by()
                  : $_ARRAYLANG['TXT_ORDER_WASNT_YET_EDITED']),
            'SHOP_SHIPPING_TYPE' => ($objOrder->shipment_id()
                ? Shipment::getShipperName($objOrder->shipment_id())
                : '&nbsp;'),
        ));
        $ppName = '';
        $psp_id = Payment::getPaymentProcessorId($objOrder->payment_id());
        if ($psp_id) {
            $ppName = PaymentProcessing::getPaymentProcessorName($psp_id);
        }
        $objTemplate->setVariable(array(
            'SHOP_SHIPPING_PRICE' => $objOrder->shipment_amount(),
            'SHOP_PAYMENT_PRICE' => $objOrder->payment_amount(),
            'SHOP_PAYMENT_HANDLER' => $ppName,
            'SHOP_LAST_MODIFIED_DATE' => $objOrder->modified_on(),
        ));
        if ($edit) {
            // edit order
            $strJsArrShipment = Shipment::getJSArrays();
            $objTemplate->setVariable(array(
                'SHOP_SEND_TEMPLATE_TO_CUSTOMER' =>
                    sprintf(
                        $_ARRAYLANG['TXT_SEND_TEMPLATE_TO_CUSTOMER'],
                        $_ARRAYLANG['TXT_ORDER_COMPLETE']),
                'SHOP_SHIPPING_TYP_MENU' => Shipment::getShipperMenu(
                    $objOrder->country_id(),
                    $objOrder->shipment_id(),
                    "calcPrice(0);"),
                'SHOP_JS_ARR_SHIPMENT' => $strJsArrShipment,
                'SHOP_PRODUCT_IDS_MENU_NEW' => Products::getMenuoptions(
                    null, null,
                    $_ARRAYLANG['TXT_SHOP_PRODUCT_MENU_FORMAT']),
                'SHOP_JS_ARR_PRODUCT' => Products::getJavascriptArray(
                    $objCustomer->group_id(), $objCustomer->is_reseller()),
            ));
        }
        // Order items
        $query = "
            SELECT `id`, `product_id`, `product_name`,
                   `price`, `quantity`, `vat_rate`, `weight`
              FROM `".DBPREFIX."module_shop".MODULE_INDEX."_order_items`
             WHERE `order_id`=$order_id";
        $objResult = $objDatabase->Execute($query);
        if (!$objResult) {
            return self::errorHandler();
        }
        $arrProductOptions = $objOrder->getOptionArray();
        $i = 0;
        $total_weight = 0;
        $total_vat_amount = 0;
        $total_net_price = 0;
        // Orders with Attributes cannot currently be edited
        // (this would spoil all the options!)
        $have_option = false;
        while (!$objResult->EOF) {
            $item_id = $objResult->fields['id'];
            $name = $objResult->fields['product_name'];
            if (isset($arrProductOptions[$item_id])) {
                if ($edit) {
// TODO: Edit options
                } elseif (isset($arrProductOptions[$item_id])) {
//DBG::log("Order::view_detail(): Item ID $item_id, Attributes: ".var_export($arrProductOptions[$item_id], true));
                    foreach ($arrProductOptions[$item_id] as $attribute_name => $arrAttribute) {
                        $have_option = true;
                        $options = '';
                        // Note: $arrOption is indexed by item_attribute_id
                        foreach ($arrAttribute as $arrOption) {
                            $options .= ($options ? ', ' : '').
                                $arrOption['name'].' ('.$arrOption['price'].')';
                        }
                        $name .=
                            '<i><br />- '.$attribute_name.': '.
                            $options.'</i>';
                    }
                }
            }
            $product_id = $objResult->fields['product_id'];
            // Get missing product details
            $objProduct = Product::getById($product_id);
            if (!$objProduct) {
// TODO: Add error message
                return false;
            }
            $code = $objProduct->code();
            $distribution = $objProduct->distribution();

            $price = $objResult->fields['price'];
            $quantity = $objResult->fields['quantity'];
            $vat_rate = $objResult->fields['vat_rate'];
            $row_net_price = $price * $quantity;
            $row_price = $row_net_price; // VAT added later, if applicable
            $total_net_price += $row_net_price;

            // Here, the VAT has to be recalculated before setting up the
            // fields.  If the VAT is excluded, it must be added here.
            // Note: the old Order.vat_amount field is no longer valid,
            // individual shop_order_items *MUST* have been UPDATEd by the
            // time PHP parses this line.
            // Also note that this implies that the vat_id and
            // country_id can be ignored, as they are considered when the
            // order is placed and the VAT is applied to the order
            // accordingly.

            // calculate the VAT amount per row, included or excluded
            $row_vat_amount = Vat::amount($vat_rate, $row_net_price);
            // and add it to the total VAT amount
            $total_vat_amount += $row_vat_amount;

            if (!Vat::isIncluded()) {
                // Add tax to price
                $row_price += $row_vat_amount;
            }
            //else {
                // VAT is disabled.
                // There shouldn't be any non-zero percentages in the order_items!
                // but if there are, there probably has been a change and we *SHOULD*
                // still treat them as if VAT had been enabled at the time the order
                // was placed!
                // That's why the else {} block is commented out.
            //}
            $weight = '-';
            if ($distribution != 'download') {
                $weight = $objResult->fields['weight'];
                if (intval($weight) > 0) {
                    $total_weight += $weight * $quantity;
                }
            }
            $objTemplate->setVariable(array(
                'SHOP_PRODUCT_ID' => $product_id,
                'SHOP_ROWCLASS' => 'row'.(++$i % 2 + 1),
                'SHOP_QUANTITY' => $quantity,
                'SHOP_PRODUCT_NAME' => $name,
                'SHOP_PRODUCT_PRICE' => Currency::formatPrice($price),
                'SHOP_PRODUCT_SUM' => Currency::formatPrice($row_net_price),
                'SHOP_P_ID' => ($edit
                    ? $objResult->fields['id'] // Item ID
                    // If we're just showing the order details, the
                    // product ID is only used in the product ID column
                    : $objResult->fields['product_id']), // Product ID
                'SHOP_PRODUCT_CODE' => $code,
                // fill VAT field
                'SHOP_PRODUCT_TAX_RATE' => ($edit
                    ? $vat_rate : Vat::format($vat_rate)),
                'SHOP_PRODUCT_TAX_AMOUNT' => Currency::formatPrice($row_vat_amount),
                'SHOP_PRODUCT_WEIGHT' => Weight::getWeightString($weight),
                'SHOP_ACCOUNT_VALIDITY' => FWUser::getValidityString($weight),
            ));
            // Get a product menu for each Product if $edit-ing.
            // Preselect the current Product ID.
            if ($edit) {
                $objTemplate->setVariable(
                    'SHOP_PRODUCT_IDS_MENU', Products::getMenuoptions(
                        $product_id, null,
                        $_ARRAYLANG['TXT_SHOP_PRODUCT_MENU_FORMAT']));
            }
            $objTemplate->parse('orderdetailsRow');
            $objResult->MoveNext();
        }

        // Show VAT with the individual products:
        // If VAT is enabled, and we're both in the same country
        // ($total_vat_amount has been set above if both conditions are met)
        // show the VAT rate.
        // If there is no VAT, the amount is 0 (zero).
        //if ($total_vat_amount) {
            // distinguish between included VAT, and additional VAT added to sum
            $tax_part_percentaged = (Vat::isIncluded()
                ? $_ARRAYLANG['TXT_TAX_PREFIX_INCL']
                : $_ARRAYLANG['TXT_TAX_PREFIX_EXCL']);
            $objTemplate->setVariable(array(
                'SHOP_TAX_PRICE' => Currency::formatPrice($total_vat_amount),
                'SHOP_PART_TAX_PROCENTUAL' => $tax_part_percentaged,
            ));
        //} else {
            // No VAT otherwise
            // remove it from the details overview if empty
            //$objTemplate->hideBlock('taxprice');
            //$tax_part_percentaged = $_ARRAYLANG['TXT_NO_TAX'];
        //}
        $objTemplate->setVariable(array(
            'SHOP_ROWCLASS_NEW' => 'row'.(++$i % 2 + 1),
            'SHOP_TOTAL_WEIGHT' => Weight::getWeightString($total_weight),
            'SHOP_NET_PRICE' => Currency::formatPrice($total_net_price),
// See above
//            'SHOP_ORDER_SUM' => Currency::formatPrice($order_sum),
        ));
        // Coupon
        $objCoupon = Coupon::getByOrderId($order_id);
        if ($objCoupon) {
            $objTemplate->setVariable(array(
                'SHOP_COUPON_CODE' => $objCoupon->code(),
                'SHOP_COUPON_DISCOUNT_AMOUNT' => $objCoupon->discount_amount(),
            ));
        }
        $objTemplate->setVariable(array(
            'TXT_PRODUCT_ID' => $_ARRAYLANG['TXT_ID'],
            // inserted VAT, weight here
            // change header depending on whether the tax is included or excluded
            'TXT_TAX_RATE' => (Vat::isIncluded()
                ? $_ARRAYLANG['TXT_TAX_PREFIX_INCL']
                : $_ARRAYLANG['TXT_TAX_PREFIX_EXCL']),
            'TXT_SHOP_ACCOUNT_VALIDITY' => $_ARRAYLANG['TXT_SHOP_VALIDITY'],
        ));
        // Disable the "edit" button when there are Attributes
        if (!$edit) {
            if ($have_option) {
                $objTemplate->touchBlock('order_no_edit');
            } else {
                $objTemplate->touchBlock('order_edit');
            }
        }
        return true;
    }


// Frontend-only methods from here
    /**
     * Returns the most recently used language ID found in the order table
     * for the given Customer ID
     *
     * Note that this method must be used for migrating old Shop Customers ONLY.
     * It returns null if no order is found, or on error.
     * @param   integer   $customer_id      The Customer ID
     * @return  integer                     The language ID on success,
     *                                      null otherwise
     */
    static function getLanguageIdByCustomerId($customer_id)
    {
        global $objDatabase;

        $query = "
            SELECT `lang_id`
              FROM `".DBPREFIX."module_shop".MODULE_INDEX."_orders`
             WHERE `customer_id`=$customer_id
             ORDER BY `id` DESC";
        $objResult = $objDatabase->Execute($query);
        if (!$objResult || $objResult->EOF) return null;
        return $objResult->fields['lang_id'];
    }


    /**
     * Handles database errors
     *
     * Also migrates the old database structure to the new one
     * @return  boolean             False.  Always.
     */
    static function errorHandler()
    {
        require_once(ASCMS_DOCUMENT_ROOT.'/update/UpdateUtil.php');
        require_once(ASCMS_CORE_PATH.'/SettingDb.class.php');
        require_once(ASCMS_CORE_PATH.'/Country.class.php');

//DBG::activate(DBG_DB_FIREPHP);
        ShopSettings::errorHandler();
        Country::errorHandler();

        $table_name = DBPREFIX.'module_shop'.MODULE_INDEX.'_orders';
        $table_structure = array(
            'id' => array('type' => 'INT(10)', 'unsigned' => true, 'auto_increment' => true, 'primary' => true, 'renamefrom' => 'orderid'),
            'customer_id' => array('type' => 'INT(10)', 'unsigned' => true, 'default' => '0', 'renamefrom' => 'customerid'),
            'currency_id' => array('type' => 'INT(10)', 'unsigned' => true, 'default' => '0', 'renamefrom' => 'selected_currency_id'),
            'shipment_id' => array('type' => 'INT(10)', 'unsigned' => true, 'notnull' => false, 'default' => null, 'renamefrom' => 'shipping_id'),
            'payment_id' => array('type' => 'INT(10)', 'unsigned' => true, 'default' => '0'),
            'lang_id' => array('type' => 'INT(10)', 'unsigned' => true, 'default' => '0', 'renamefrom' => 'customer_lang'),
            'status' => array('type' => 'TINYINT(1)', 'unsigned' => true, 'default' => '0', 'renamefrom' => 'order_status'),
            'sum' => array('type' => 'DECIMAL(9,2)', 'unsigned' => true, 'default' => '0.00', 'renamefrom' => 'currency_order_sum'),
            'vat_amount' => array('type' => 'DECIMAL(9,2)', 'unsigned' => true, 'default' => '0.00', 'renamefrom' => 'tax_price'),
            'shipment_amount' => array('type' => 'DECIMAL(9,2)', 'unsigned' => true, 'default' => '0.00', 'renamefrom' => 'currency_ship_price'),
            'payment_amount' => array('type' => 'DECIMAL(9,2)', 'unsigned' => true, 'default' => '0.00', 'renamefrom' => 'currency_payment_price'),
            'gender' => array('type' => 'VARCHAR(50)', 'notnull' => false, 'default' => null, 'renamefrom' => 'ship_prefix'),
            'company' => array('type' => 'VARCHAR(100)', 'notnull' => false, 'default' => null, 'renamefrom' => 'ship_company'),
            'firstname' => array('type' => 'VARCHAR(40)', 'notnull' => false, 'default' => null, 'renamefrom' => 'ship_firstname'),
            'lastname' => array('type' => 'VARCHAR(100)', 'notnull' => false, 'default' => null, 'renamefrom' => 'ship_lastname'),
            'address' => array('type' => 'VARCHAR(40)', 'notnull' => false, 'default' => null, 'renamefrom' => 'ship_address'),
            'city' => array('type' => 'VARCHAR(50)', 'notnull' => false, 'default' => null, 'renamefrom' => 'ship_city'),
            'zip' => array('type' => 'VARCHAR(10)', 'notnull' => false, 'default' => null, 'renamefrom' => 'ship_zip'),
            'country_id' => array('type' => 'INT(10)', 'unsigned' => true, 'notnull' => false, 'default' => null, 'renamefrom' => 'ship_country_id'),
            'phone' => array('type' => 'VARCHAR(20)', 'notnull' => false, 'default' => null, 'renamefrom' => 'ship_phone'),
            'ip' => array('type' => 'VARCHAR(50)', 'default' => '', 'renamefrom' => 'customer_ip'),
            'host' => array('type' => 'VARCHAR(100)', 'default' => '', 'renamefrom' => 'customer_host'),
            'browser' => array('type' => 'VARCHAR(255)', 'default' => '', 'renamefrom' => 'customer_browser'),
            'note' => array('type' => 'TEXT', 'default' => '', 'renamefrom' => 'customer_note'),
            'date_time' => array('type' => 'DATETIME', 'default' => '0000-00-00 00:00:00', 'renamefrom' => 'order_date'),
            'modified_on' => array('type' => 'DATETIME', 'default' => null, 'notnull' => false, 'renamefrom' => 'last_modified'),
            'modified_by' => array('type' => 'VARCHAR(50)', 'notnull' => false, 'default' => null),
        );
        $table_index = array(
            'status' => array('fields' => array('status')));
        UpdateUtil::table($table_name, $table_structure, $table_index);

        $table_name = DBPREFIX.'module_shop'.MODULE_INDEX.'_order_items';
        $table_structure = array(
            'id' => array('type' => 'INT(10)', 'unsigned' => true, 'auto_increment' => true, 'primary' => true, 'renamefrom' => 'order_items_id'),
            'order_id' => array('type' => 'INT(10)', 'unsigned' => true, 'default' => '0', 'renamefrom' => 'orderid'),
            'product_id' => array('type' => 'INT(10)', 'unsigned' => true, 'default' => '0', 'renamefrom' => 'productid'),
            'product_name' => array('type' => 'VARCHAR(255)', 'default' => ''),
            'price' => array('type' => 'DECIMAL(9,2)', 'unsigned' => true, 'default' => '0.00'),
            'quantity' => array('type' => 'INT(10)', 'unsigned' => true, 'default' => '0'),
            'vat_rate'  => array('type' => 'DECIMAL(5,2)', 'unsigned' => true, 'notnull' => false, 'default' => null, 'renamefrom' => 'vat_percent'),
            'weight' => array('type' => 'INT(10)', 'unsigned' => true, 'default' => '0'),
        );
        $table_index = array(
            'order' => array('fields' => array('order_id')));
        UpdateUtil::table($table_name, $table_structure, $table_index);

        $table_name = DBPREFIX.'module_shop'.MODULE_INDEX.'_order_attributes';
        $table_structure = array(
            'id' => array('type' => 'INT(10)', 'unsigned' => true, 'auto_increment' => true, 'primary' => true, 'renamefrom' => 'orders_items_attributes_id'),
            'item_id' => array('type' => 'INT(10)', 'unsigned' => true, 'default' => '0', 'renamefrom' => 'order_items_id'),
            'attribute_name' => array('type' => 'VARCHAR(255)', 'default' => '', 'renamefrom' => 'product_option_name'),
            'option_name' => array('type' => 'VARCHAR(255)', 'default' => '', 'renamefrom' => 'product_option_value'),
            'price' => array('type' => 'DECIMAL(9,2)', 'unsigned' => true, 'default' => '0.00', 'renamefrom' => 'product_option_values_price'),
        );
        $table_index = array(
            'item_id' => array('fields' => array('item_id')));
        UpdateUtil::table($table_name, $table_structure, $table_index);

        // LSV
        $table_name = DBPREFIX.'module_shop'.MODULE_INDEX.'_lsv';
        $table_structure = array(
            'order_id' => array('type' => 'INT(10)', 'unsigned' => true, 'primary' => true, 'renamefrom' => 'id'),
            'holder' => array('type' => 'tinytext', 'default' => ''),
            'bank' => array('type' => 'tinytext', 'default' => ''),
            'blz' => array('type' => 'tinytext', 'default' => ''),
        );
        $table_index = array();
        UpdateUtil::table($table_name, $table_structure, $table_index);

        // Always
        return false;
    }

}
