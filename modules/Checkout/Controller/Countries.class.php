<?php

/**
 * Countries
 * Obsolete since CLX-478
 *
 * @copyright   CONTREXX CMS - COMVATION AG
 * @author      COMVATION Development Team <info@comvation.com>
 * @package     contrexx
 * @subpackage  module_checkout
 */

namespace Cx\Modules\Checkout\Controller;

/**
 * Countries
 *
 * @copyright   CONTREXX CMS - COMVATION AG
 * @author      COMVATION Development Team <info@comvation.com>
 * @package     contrexx
 * @subpackage  module_checkout
 */
class Countries {

    /**
     * Database object.
     *
     * @access      private
     * @var         ADONewConnection
     */
    private $objDatabase;

    /**
     * Initialize the database object.
     *
     * @access      public
     * @param       ADONewConnection    $objDatabase
     */
    public function __construct($objDatabase)
    {
        $this->objDatabase = $objDatabase;
    }

    /**
     * Get all countries.
     *
     * @access      public
     * @return      array       $arrCountries   contains all countries
     * @return      boolean                     contains false if there are no countries
     */
    public function getAll()
    {
        $arrCountries = \Cx\Core\Country\Controller\Country::getNameArray(false);

        if (!empty($arrCountries)) {
            return $arrCountries;
        } else {
            return false;
        }
    }

}
