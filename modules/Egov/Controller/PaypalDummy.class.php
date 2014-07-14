<?php

/* PayPal crashtest dummy */

// Please choose:

$paypalUriNok = '';
$paypalUriOk  = '';
$paypalUriIpn = '';

$strForm = '';
$ipn = false;

foreach ($_POST as $name => $value) {
    if ($name == 'cancel_return') {
        $paypalUriNok = $value;
        continue;
    } elseif ($name == 'return') {
        $paypalUriOk  = $value;
        continue;
    } elseif ($name == 'notify_url') {
        $paypalUriIpn = $value;
        continue;
    } elseif ($name == 'cmd') {
        if ($value == '_notify-validate') {
            die("VERIFIED");
        }
        continue;
    }
    addParam($name, $value);
}

function addParam($name, $value)
{
    global $strForm;
    $strForm .= "        <tr><td>$name</td><td><input type=\"text\" name=\"$name\" value=\"".urlencode($value)."\" /></td></tr>\n";
}

die(
'<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
  <head>
    <title>PayPal Dummy</title>
    <script type="text/javascript">
    // <![CDATA[
    function submitResult(result) {
      targetUri = "";
      if (result == -1) {
        targetUri = "'.$paypalUriIpn.'";
      }
      if (result == 0) {
        targetUri = "'.$paypalUriNok.'";
      }
      if (result == 1) {
        targetUri = "'.$paypalUriOk.'";
      }
      if (result == 2) {
        targetUri = "'.$paypalUriNok.'";
      }
      document.paypal.action=targetUri;
      document.paypal.submit();
    }
    // ]]>
    </script>
  </head>
  <body>
    <form name="paypal" method="post" action="'.htmlspecialchars($paypalUriNok).'">
      <table summary="">
'.$strForm.
'      </table>
      <input type="button" value="Notification"
        onclick="submitResult(-1);" />&nbsp;
      <input type="button" value="Abort"
        onclick="submitResult(0);" />&nbsp;
      <input type="button" value="Success"
        onclick="submitResult(1);" />&nbsp;
      <input type="button" value="Cancel"
        onclick="submitResult(2);" />&nbsp;
    </form>
  </body>
</html>
');

?>
