<?php

/**
 * Dummy class for simulating an external payment provider.
 *
 * Creates a dummy form for testing the payment process in the shop.
 * @copyright   CONTREXX CMS - COMVATION AG
 * @author Reto Kohli <reto.kohli@comvation.com>
 * @package     contrexx
 * @subpackage  module_shop
 * @todo        Edit PHP DocBlocks!
 */


/**
 * Lets you choose successful, failed, and aborted payments.
 *
 * This also demonstrates the requirements of the current specification
 * for any external payment service provider class.
 * See {@link PaymentProcessing.class} for details.
 * @copyright   CONTREXX CMS - COMVATION AG
 * @author      Reto Kohli <reto.kohli@comvation.com>
 * @package     contrexx
 * @subpackage  module_shop
 * @todo        Edit PHP DocBlocks!
 */

class Dummy
{
    /**
     * Constructor (PHP 4)
     *
     * Note that this is neither needed nor used.
     * @author Reto Kohli <reto.kohli@comvation.com>
     */
    function Dummy()
    {
        $this->__construct();
    }

    /**
     * Constructor (PHP 5)
     *
     * Note that this is neither needed nor used.
     * @author Reto Kohli <reto.kohli@comvation.com>
     */
    function __construct()
    {
        // Nothing to do here.
    }


    /**
     * Returns the dummy payment form
     * @author Reto Kohli <reto.kohli@comvation.com>
     * @static
     * @return string  HTML code for the dummy payment form
     */
    //static
    function getForm()
    {
        $orderid    = $_SESSION['shop']['orderid'];
        $confirmURI = "index.php?section=shop".MODULE_INDEX."&amp;cmd=success&amp;handler=dummy&amp;orderid=$orderid&amp;result=-1";
        $failureURI = "index.php?section=shop".MODULE_INDEX."&amp;cmd=success&amp;handler=dummy&amp;orderid=$orderid&amp;result=0";
        $successURI = "index.php?section=shop".MODULE_INDEX."&amp;cmd=success&amp;handler=dummy&amp;orderid=$orderid&amp;result=1";
        $cancelURI  = "index.php?section=shop".MODULE_INDEX."&amp;cmd=success&amp;handler=dummy&amp;orderid=$orderid&amp;result=2";
        return <<<_
Please choose one:
<hr />
<a href='$confirmURI'>Confirm payment (silent)</a>
<br />
<a href='$successURI'>Successful payment (show success page)</a>
<br />
<a href='$failureURI'>Failed payment</a>
<br />
<a href='$cancelURI'>Cancelled payment</a>
<hr />

_;
    }


    /**
     * Commit the payment process result (dummy operation).
     *
     * After the user submitted the payment form, a result according to her
     * choices is created here.
     * The order ID *MUST* be provided in the 'orderid' request argument.
     * Otherwise, the payment is assumed to have failed.
     * The result of the payment process *SHOULD* be provided in the 'result'
     * request argument.  It *SHOULD* be one of the following:
     * 0 (zero): The payment was unsuccessful.
     * 1 (one): The payment was successful.
     * 2 (two): The payment has been cancelled.
     * Values other than these are considered to be equal to 0, however.
     * @author  Reto Kohli <reto.kohli@comvation.com>
     * @static
     * @return  mixed   The integer order ID after a successful payment,
     *                  Boolean false after a failed payment or a general
     *                  error.
     */
    //static
    function commit()
    {
        $result = intval(isset($_GET['result']) ? $_GET['result'] : 0);
        if ($result < 1 || $result > 2) {
            // Result is as good as zero. fail.
            return false;
        }
        // only cases 1 and 2 remaining.
        if (isset($_GET['orderid'])) {
            $orderid = intval($_GET['orderid']);
        } else {
            // No order ID. fail.
            return false;
        }
        // Returning order ID or false.
        return ($result == 1 ? $orderid : false);
    }
}

?>