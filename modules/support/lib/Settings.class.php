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
     *
     * @author  Reto Kohli <reto.kohli@comvation.com>
     */
    function Settings()
    {
        $this->__construct();
    }

    /**
     * Constructor (PHP5)
     *
     * @author  Reto Kohli <reto.kohli@comvation.com>
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
     * @author  Reto Kohli <reto.kohli@comvation.com>
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
     * @author  Reto Kohli <reto.kohli@comvation.com>
     */
    function _storeGeneral()
    {
        global $objDatabase;
        if (!empty($_POST['general'])) {
            $query = "
                UPDATE ".DBPREFIX."module_support_config
                   SET status=".intval($_POST['xy'])."
                 WHERE name='xy'
            ";
            $objResult = $objDatabase->Execute($query);
            if (!$objResult) {
                return false;
            }
            if ($objDatabase->Affected_Rows()) {
                $this->flagChanged = true;
                $objDatabase->Execute("OPTIMIZE TABLE ".DBPREFIX."module_support_config");
            }
        }
        return true;
    }


}

?>
