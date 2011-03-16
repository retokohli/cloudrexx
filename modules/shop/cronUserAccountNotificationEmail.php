<?php
/**
 * The Shop
 * @copyright   CONTREXX CMS - COMVATION AG
 * @package     contrexx
 * @subpackage  module_shop
 * @todo        Edit PHP DocBlocks!
 */

/**
 * Pull generated user accounts from the access_users database table
 * and send notification emails to those customers whose expiration date
 * is only a week or less away.
 * Note: If you put this script in a different folder, you need to change the
 * include path of the configuration file below!
 * Note: The "interests" field is used to mark those accounts which have
 * been processed successfully.  This field must not be used otherwise
 * for autocreated user accounts.  Leave it untouched at all times!
 *
 */
require_once '../../config/configuration.php';
require_once ASCMS_LIBRARY_PATH.'/adodb/adodb.inc.php';
require_once ASCMS_CORE_PATH.'/database.php';

$objDatabase = getDatabaseObject(&$errorMsg, $newInstance = false);
if (!$objDatabase) {
    die("Error: Failed to connect to database");
}

/*  Look for generated accounts that are still active, have a limited
    validity, and haven't been notified yet.
    The interests field will be updated (filled in) upon successful
    notification!   */
$query = "
    SELECT * FROM ".DBPREFIX."access_users
     WHERE username LIKE 'A-%'
       AND active=1
       AND expiration!=0
       AND interests=''
";
$objResult = $objDatabase->Execute($query);
if (!$objResult) {
    die("Error: Query failed, code gfdhsrevws\n$query\n");
}

$todayTimestamp = time();
// Notify customers seven days in advance
$notifyLimitTimestamp = $todayTimestamp + 7 * 24 * 60 * 60;

while (!$objResult->EOF) {
    $id = $objResult->fields['id'];
    $endTimestamp = $objResult->fields['expiration'];
    $endDate = date('d.m.Y', $endTimestamp);

    // Skip accounts that are valid for longer than the limit
    if ($endTimestamp > $notifyLimitTimestamp) {
        $objResult->MoveNext();
        continue;
    }

    // Send a notification e-mail
    $username = $objResult->fields['username'];
    $email = $objResult->fields['email'];
    // START: COMPATIBELITY MODE FOR SHOP ACCOUNT SELLING
    if (preg_match('#^shop_customer_[0-9]+\-(.*)$#', $email, $compatibleEmail)) {
        $email = $compatibleEmail[1];
    }
    // END: COMPATIBELITY MODE FOR SHOP ACCOUNT SELLING
    
    $firstname = $objResult->fields['firstname'];
    $lastname = $objResult->fields['lastname'];
    $subject = "Your account on www.noser.com will expire in seven days";
    $mailbody =
"Dear $firstname $lastname,

This mail has been sent to inform you that your account on www.mydomain.com
will expire on $endDate.
Your account user name: $username
Please visit our website www.mydomain.com if you would like to extend your
account.

Kind regards,

The mydomain Team";
    $headers =
"From: info@mydomain.com\r\n".
"Reply-To: info@mydomain.com\r\n".
"X-Mailer: PHP/".phpversion();

    // Send mail to customer
    // Test/debug with: $result = true;
    $result = @mail($email, $subject, $mailbody, $headers);

    // Update user account record.
    // Mark the account if the mail could be sent only.
    if ($result) {
        $query = "
            UPDATE ".DBPREFIX."access_users
               SET interests='notified on ".date('Y-m-d H:i:s')."'
             WHERE id=$id
        ";
        $objResult2 = $objDatabase->Execute($query);
        if (!$objResult2) {
            die("Error: Query failed, code iuhlmvgfhk\n$query\n");
        }
    }
    $objResult->MoveNext();
}

?>
