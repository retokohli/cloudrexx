<?PHP
/**
 * Shop library
 *
 * @copyright   CONTREXX CMS - ASTALAVISTA IT AG
 * @author      Ivan Schmid <ivan.schmid@astalavista.ch>
 * @version     1.0.0
 * @package     contrexx
 * @subpackage  module_shop
 * @todo        Edit PHP DocBlocks!
 */

/**
 * All the helping hands needed to run the shop
 *
 * @copyright   CONTREXX CMS - ASTALAVISTA IT AG
 * @author      Ivan Schmid <ivan.schmid@astalavista.ch>
 * @access      public
 * @version     1.0.0
 * @package     contrexx
 * @subpackage  module_shop
 * @todo        Add a proper constructor that initializes the class with its
 *              various variables, and/or move the appropriate parts to
 *              a pure Shop class.
 */
class ShopLibrary {
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
        $query = "SELECT zones_id, zones_name, activation_status FROM ".DBPREFIX."module_shop_zones ORDER BY zones_name";
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
        if(strpos($base64Str, ':') === false) {
            // have to return an array with the desired number of elements
            // and an empty file name in order to show the "dummy" picture(s)
            return array(
                1 => array('img' => ''),
            );
        }
        $arrPictures = array();
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
        /*
        list($shopImage2, $shopImage2_width, $shopImage2_height) = explode('?', $tmp2);
        list($shopImage3, $shopImage3_width, $shopImage3_height) = explode('?', $tmp3);


        $shopImage2        = base64_decode($shopImage2);
        $shopImage2_width  = base64_decode($shopImage2_width);
        $shopImage2_height = base64_decode($shopImage2_height);

        $shopImage3        = base64_decode($shopImage3);
        $shopImage3_width  = base64_decode($shopImage3_width);
        $shopImage3_height = base64_decode($shopImage3_height);

        $arrPictures = array();

        return array(   1 => array,
                        2 => array('img' => $shopImage2, 'width' => $shopImage2_width, 'height' => $shopImage2_height),
                        3 => array('img' => $shopImage3, 'width' => $shopImage3_width, 'height' => $shopImage3_height),
        );
        */
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
        $query("SELECT zones_id, countries_id FROM ".DBPREFIX."module_shop_rel_countries ORDER BY id");
        $objResult = $objDatabase->Execute($query);
        while (!$objResult->EOF) {
            $arrRelCountries[]=array($objResult->fields['zones_id'],$objResult->fields['countries_id']);
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
        $query = "SELECT zones_id, zones_name FROM ".DBPREFIX."module_shop_zones WHERE activation_status=1";
        $objResult = $objDatabase->Execute($query);
        while(!$objResult->EOF) {
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
    function _getCountriesMenu($menuName="countryId", $selectedId="", $onchange="")
    {
        global $objDatabase;

        $onchange = !empty($onchange) ? "onchange=\"".$onchange."\"" : "";
        $menu = "\n<select name=\"".$menuName."\" ".$onchange.">\n";

        $query = "SELECT countries_id, countries_name ".
            "FROM ".DBPREFIX."module_shop_countries ".
            "WHERE activation_status=1";
        $objResult = $objDatabase->Execute($query);

        if($objResult->RecordCount()>1) {
            while (!$objResult->EOF) {
                $selected = (intval($selectedId)==$objResult->fields['countries_id']) ? "selected=\"selected\"" : "";
                $menu .="<option value=\"".$objResult->fields['countries_id']."\" ".$selected.">".$objResult->fields['countries_name']."</option>\n";
                $objResult->MoveNext();
            }
            $menu .= "</select>\n";
        } else {
            $menu = $menu = "\n<input name=\"".$menuName."\" type=\"hidden\" value=\"".$objResult->fields['countries_id']."\">".$objResult->fields['countries_name']."\n";
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


    /**
     * Returns a dropdown menu string with all available order status.
     *
     * @param   string  $menuName   Optional menu name
     * @param   string  $selectedId Optional preselected status ID
     * @param   string  $onchange   Optional onchange callback function
     * @return  string  $menu       The dropdown menu string
     */
    function _getOrderStatusMenu($menuName="orderStatusId", $selectedId="", $onchange="")
    {
        $menu = "<select name=\"".$menuName."\" id=\"".$menuName."\" ".($onchange != '' ? "onchange='".$onchange."'" : '').">\n";

        foreach ($this->arrOrderStatus as $statusId => $strStatus) {
            $menu .= "<option value='".$statusId."' ".($selectedId == $statusId ? "selected='selected'" : '').'>'.$strStatus."</option>\n";
        }
        $menu .= "</select>\n";
        return $menu;
    }


/*  replaced by Shipment.class!

    function _initShipment()
    {
        global $objDatabase;

         $query = "SELECT id, name, costs, costs_free_sum, status ".
            "FROM ".DBPREFIX."module_shop_shipment ".
            "ORDER BY id";
         $objResult = $objDatabase->Execute($query);
         while(!$objResult->EOF) {
            $this->arrShipment[$objResult->fields['id']]= array(
                'id' => $objResult->fields['id'],
                'name' => $objResult->fields['name'],
                'costs' => $objResult->fields['costs'],
                'costs_free_sum' => $objResult->fields['costs_free_sum'],
                'status' => $objResult->fields['status']
            );
            $objResult->MoveNext();
        }
    }
*/


    function _initCountries()
    {
        global $objDatabase;

         $query = "SELECT countries_id,
                            countries_name,
                           countries_iso_code_2,
                           countries_iso_code_3,
                            activation_status
                      FROM ".DBPREFIX."module_shop_countries
                      ORDER BY countries_id";

         $objResult = $objDatabase->Execute($query);

         while(!$objResult->EOF) {
            $this->arrCountries[$objResult->fields['countries_id']]= array(
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
                  "FROM ".DBPREFIX."module_shop_payment ".
                  "ORDER BY sort_order";
         $objResult = $objDatabase->Execute($query);
         while(!$objResult->EOF) {
            $this->arrPayment[$objResult->fields['id']]= array(
                'id' => $objResult->fields['id'],
                'name' => $objResult->fields['name'],
                'processor_id' => $objResult->fields['processor_id'],
                'costs'    => $objResult->fields['costs'],
                'costs_free_sum'    => $objResult->fields['costs_free_sum'],
                'sort_order' => $objResult->fields['sort_order'],
                'status' => $objResult->fields['status']
            );
            $objResult->MoveNext();
        }
    }


    /**
     * gets a select box with all the payment handlers
     *
     * @param  string  optional $menuName
     * @param  string  optional $selectedhandlerName
     * @return string $menu
     */
    function _getPaymentHandlerMenu($menuName="paymentHandler", $selectedhandlerName="Internal")
    {
        global $objDatabase;
        $menu = "\n<select name=\"".$menuName."\">\n";
        // paymentHandlers array from the shopmanager class

        $query = "SELECT id, name FROM ".DBPREFIX."module_shop_payment_processors WHERE status=1 ORDER BY name";
        $objResult = $objDatabase->Execute($query);
        while (!$objResult->EOF) {
            $selected = ($selectedhandlerName==$objResult->fields['id']) ? "selected=\"selected\"" : "";
            $menu .= "<option value=\"".$objResult->fields['id']."\" ".$selected.">".$objResult->fields['name']."</option>\n";
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

        $query = "SELECT id, name, value, status FROM ".DBPREFIX."module_shop_config ORDER BY id";
        $objResult = $objDatabase->Execute($query);
        while(!$objResult->EOF) {
            $this->arrConfig[$objResult->fields['name']]= array(
                'id'     => $objResult->fields['id'],
                'value'  => $objResult->fields['value'],
                'status' => $objResult->fields['status']
            );
            $objResult->MoveNext();
        }

        $this->arrConfig['js_cart'] = array(
            'id'     => 9999,
            'value'  => '',
            'status' => '0'
        );
    }


    /**
     * Set up and send an email from the shop.
     *
     * @return boolean  The return value of the {@link phpmailer::Send()} function.
     */
    function shopSendmail($shopMailTo,$shopMailFrom,$shopMailFromText,$shopMailSubject,$shopMailBody,$shopMailBcc="" )
    {
    	global $_CONFIG;

        // replace cr/lf by lf only
        $shopMailBody = preg_replace('/\015\012/', "\012", $shopMailBody);

        if (@include_once ASCMS_LIBRARY_PATH.'/phpmailer/class.phpmailer.php') {
			$objMail = new phpmailer();

			if ($_CONFIG['coreSmtpServer'] > 0 && @include_once ASCMS_CORE_PATH.'/SmtpSettings.class.php') {
				$objSmtpSettings = new SmtpSettings();
				if (($arrSmtp = $objSmtpSettings->getSmtpAccount($_CONFIG['coreSmtpServer'])) !== false) {
					$objMail->IsSMTP();
					$objMail->Host = $arrSmtp['hostname'];
					$objMail->Port = $arrSmtp['port'];
					$objMail->SMTPAuth = true;
					$objMail->Username = $arrSmtp['username'];
					$objMail->Password = $arrSmtp['password'];
				}
			}

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
     * Set up and fill in a mail template
     *
     * Get the selected mail template and associated fields from the database
     * and fill in the elements of the arrShopMailTemplate class variable.
     * Considers the language ID stored in the {@link langId} class variable.
     *
     * @param   integer $shopTemplateId     The mail template ID
     * @global          $objDatabase        Database object
     * @return  boolean                     True on success, false otherwise
     */
    function shopSetMailtemplate($shopTemplateId)
    {
        global $objDatabase;

        $query = "SELECT content.from_mail AS from_mail,
                         content.xsender AS xsender,
                         content.subject AS subject,
                         content.message AS message
                    FROM ".DBPREFIX."module_shop_mail AS mail,
                         ".DBPREFIX."module_shop_mail_content AS content
                   WHERE mail.id=content.tpl_id
                     AND content.lang_id=".intval($this->langId)."
                     AND mail.id =".intval($shopTemplateId);
       if (($objResult = $objDatabase->Execute($query)) !== false) {
           if (!$objResult->EOF) {
                $this->arrShopMailTemplate['mail_from']=$objResult->fields['from_mail'];
                $this->arrShopMailTemplate['mail_x_sender'] = $objResult->fields['xsender'];
                $this->arrShopMailTemplate['mail_subject'] = $objResult->fields['subject'];
                $this->arrShopMailTemplate['mail_body'] = $objResult->fields['message'];
           }
           return true;
       } else {
           return false;
       }
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
     * @version 1.0
     * @param  string  $string
     * @return boolean result
     */
    function shopCheckEmail($string)
    {
        if( eregi( "^" . "[a-z0-9]+([_\\.-][a-z0-9]+)*" .    //user
            "@" . "([a-z0-9]+([\.-][a-z0-9]+)*)+" .            //domain
            "\\.[a-z]{2,4}" .                                 //sld, tld
            "$", $string)
        ) {
            return true;
        } else {
            return false;
        }
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
    function _checkEmailIntegrity($email, $customerId = 0)
    {
        global $objDatabase;

        if ($customerId != 0) {
            $objResult = $objDatabase->SelectLimit("SELECT customerid FROM ".DBPREFIX."module_shop_customers WHERE email='".$email."' AND customerid !=".$customerId, 1);
        } else {
            $objResult = $objDatabase->SelectLimit("SELECT customerid FROM ".DBPREFIX."module_shop_customers WHERE email='".$email."'", 1);
        }
        if ($objResult !== false && $objResult->RecordCount() == 0) {
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
    function _checkUsernameIntegrity($username, $customerId = 0)
    {
        global $objDatabase;

        if ($customerId != 0) {
            $objResult = $objDatabase->SelectLimit("SELECT customerid FROM ".DBPREFIX."module_shop_customers WHERE username='".$username."' AND customerid !=".$customerId, 1);
        } else {
            $objResult = $objDatabase->SelectLimit("SELECT customerid FROM ".DBPREFIX."module_shop_customers WHERE username='".$username."'", 1);
        }
        if ($objResult !== false && $objResult->RecordCount() == 0) {
            return true;
        }
        return false;
    }
}

?>
