<?php

/**
 * Dummy class for simulating an external payment provider
 *
 * Creates a dummy form for testing the payment process in the shop.
 *
 * @copyright   CONTREXX CMS - COMVATION AG
 * @author Reto Kohli <reto.kohli@comvation.com>
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
        $successURI = "index.php?section=shop&amp;cmd=success&amp;handler=dummy&amp;orderid=$orderid&amp;result=1";
        $failureURI = "index.php?section=shop&amp;cmd=success&amp;handler=dummy&amp;orderid=$orderid&amp;result=0";
        $cancelURI  = "index.php?section=shop&amp;cmd=cancel&amp;orderid=$orderid";
        return <<<_
Please choose one:
<hr />
<a href='$successURI'>Successful payment</a>
<br />
<a href='$failureURI'>Failed payment</a>
<br />
<a href='$cancelURI'>Cancelled payment</a>
<hr />

_;
    }


    /**
     * Commit the payment process result to the order database.
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
     *                  The 'NULL' string value (not the null value of the
     *                  null type!) after a failed payment or a general
     *                  error.
     */
    //static
    function commit()
    {
        global $objDatabase;

        $result = intval(isset($_GET['result']) ? $_GET['result'] : 0);
//echo("getPaymentStatus(): result is '$result'<br />");

        if ($result < 1 || $result > 2) {
//echo("getPaymentStatus(): result is as good as zero. fail.<br />");
            return 'NULL';
        }
        // only cases 1 and 2 remain
        if (isset($_GET['orderid'])) {
            $orderid = intval($_GET['orderid']);
        } else {
//echo("getPaymentStatus(): no order ID. fail.<br />");
            return 'NULL';
        }
//echo("getPaymentStatus(): Setting result '$result', order ID is '$orderid'<br />");
        $query = "
            UPDATE ".DBPREFIX."module_shop_orders
               SET order_status='$result'
             WHERE orderid=$orderid
        ";
        $objResult = $objDatabase->Execute($query);
        if (!$objResult) {
//echo("getPaymentStatus(): query $query failed<br />");
            return 'NULL';
        }
        if ($objDatabase->Affected_Rows() != 1) {
//echo("getPaymentStatus(): row miscount: ".$objDatabase->Affected_Rows()." fail.<br />");
            return 'NULL';
        }
//echo("getPaymentStatus(): Returning ".($result == 1 ? $orderid : 'NULL')."<br />");
        return ($result == 1 ? $orderid : 'NULL');
    }
}

?>