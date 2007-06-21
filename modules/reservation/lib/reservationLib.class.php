<?php
/**
 * Reservations module library
 *
 * Library of the reservation module
 *
 * @copyright   CONTREXX CMS - COMVATION AG
 * @author Comvation Development Team <info@comvation.com>
 * @access public
 * @version 1.0.0
 * @package     contrexx
 * @subpackage  module_reservation
 * @todo        Edit PHP DocBlocks!
 */

class reservationLib
{
    var $options;
    var $langId;

    /**
     * Constructor
     *
     */
    function __construct()
    {
        $this->tidy();

        /* FIXME */
	    setlocale(LC_ALL, 'de_DE@euro', 'de_DE', 'deu_deu');
    }

    /**
     * Constructor for php 4
     *
     * @return reservationLib
     */
    function reservationLib()
    {
        $this->reservationLib();
    }

    /**
     * Sets the options
     *
     * @global $objDatabase;
     */
    function setOptions()
    {
        global $objDatabase;

        $query = "SELECT setname, setvalue FROM ".DBPREFIX."module_reservation_settings
                  WHERE lang_id = '".$this->langId."'";
        $objResult = $objDatabase->Execute($query);

        if ($objResult) {
            while (!$objResult->EOF) {
                $this->options[$objResult->fields['setname']] = $objResult->fields['setvalue'];

                $objResult->MoveNext();
            }
        }
    }


    /**
     * Tidies the database
     *
     * Delete old entries
     * @global $objDatabase
     */
    function tidy()
    {
        global $objDatabase;

        /**
         * Clean reservations with status = 0
         */
        $time = time() - (60 * 60);
        $query = "SELECT id FROM ".DBPREFIX."module_reservation
                  WHERE status = '0' AND time < '".$time."'";
        $objResult = $objDatabase->Execute($query);
        if ($objResult) {
            while (!$objResult->EOF) {
                $id = $objResult->fields['id'];
                $query = "DELETE FROM ".DBPREFIX."module_reservation
                          WHERE id = '".$id."'";
                $objDatabase->Execute($query);

                $objResult->MoveNext();
            }
        }
    }

}


?>