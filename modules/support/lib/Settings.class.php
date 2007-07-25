<?php

/**
 * Support system settings
 * @copyright   CONTREXX CMS - COMVATION AG
 * @author      Reto Kohli <reto.kohli@comvation.com>
 * @version     0.0.1
 * @package     contrexx
 * @subpackage  module_shop
 * @todo        Edit PHP DocBlocks!
 */

/**
 * Support system settings
 * @copyright   CONTREXX CMS - COMVATION AG
 * @author      Reto Kohli <reto.kohli@comvation.com>
 * @version     0.0.1
 * @package     contrexx
 * @subpackage  module_shop
 * @todo        Edit PHP DocBlocks!
 */
class Settings
{
    /**
     * This flag is set to true as soon as any changed setting is
     * detected and stored.  Only used by new methods that support it.
     * @access  private
     * @var     boolean     $flagChanged
     */
    var $flagChanged = false;


    /**
     * Constructor (PHP4)
     */
    function Settings()
    {
        $this->__construct();
    }

    /**
     * Constructor (PHP5)
     */
    function __construct()
    {
    }


    /**
     * Runs all the methods to store the various settings from the shop admin zone.
     *
     * Note that not all of the methods report their success or failure back here (yet),
     * so you should not rely on the result of this method.
     * @return  boolean                     True on success, false otherwise.
     */
    function storeSettings()
    {
        $success = true;
        if ($this->flagChanged === true) {
            $success &= false;
        }
        return $success;
    }


    /**
     * Store general settings
     *
     * @return  boolean     true on success, false otherwise.
     */
    function _storeGeneral()
    {
        global $objDatabase;
        if(isset($_POST['general']) && !empty($_POST['general'])) {
            $objDatabase->Execute("UPDATE ".DBPREFIX."module_shop_config
                                    SET status=".intval($_POST['payment_lsv_status'])."
                                    WHERE name='payment_lsv_status'"
                                );
            if ($objDatabase->Affected_Rows()) { $this->flagChanged = true; }
            $objDatabase->Execute("OPTIMIZE TABLE ".DBPREFIX."module_shop_config");
            return true;
        }
        return false;
    }


}

?>
