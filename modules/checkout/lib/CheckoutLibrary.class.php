<?php

/**
 * CheckoutLibrary
 *
 * @copyright   CONTREXX CMS - COMVATION AG
 * @author      COMVATION Development Team <info@comvation.com>
 * @package     contrexx
 * @subpackage  module_checkout
 */
class CheckoutLibrary {

    /**
     * Transaction status confirmed.
     *
     * @access      protected
     */
    const CONFIRMED = "confirmed";

    /**
     * Transaction status waiting.
     *
     * @access      protected
     */
    const WAITING = "waiting";

    /**
     * Transaction status cancelled.
     *
     * @access      protected
     */
    const CANCELLED = "cancelled";


    /**
     * Transaction title mister.
     *
     * @access      protected
     */
    const MISTER = "mister";

    /**
     * Transaction title miss.
     *
     * @access      protected
     */
    const MISS = "miss";


    /**
     * Allowed currencies.
     *
     * @access      protected
     * @var         array
     */
    protected $arrCurrencies = array(1 => 'CHF', 2 => 'EUR', 3 => 'USD');

}
