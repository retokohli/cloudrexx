<?php

/* Yellowpay crashtest dummy */

// Please choose:
$output = 'form';//$output = 'post';
$formResult = -1;// one of (-1, 0, 1, 2)

// Append result to this, add other arguments to POST
$returnUri = 'http://localhost/c_trunk/index.php?section=shop&handler=yellowpay';

/*
Wird in den Stammdaten als primäre bzw. sekundäre Zieladresse für den Empfang
der Autorisierungsquittung eine URL hinterlegt, werden die Rückgabeparameter
per http oder https POST geschickt. Die Zustellung der Autorisierungsquittung ist
aus Sicht yellowpay erfolgreich, wenn der Server des Shops einen gültigen http
Status zurückgibt. Im Moment wird weder der Inhalt der Antwort noch die Bedeutung
des http Status Codes ausgewertet, d.h. ein http/1.1 404 Not Found wird ebenso
als erfolgreiche Zustellung interpretiert wie ein http/1.1 200 OK.
PostFinance behält es sich allerdings vor, in Zukunft die erfolgreiche Zustellung der
Autorisierungsquittung von einem http/1.1 200 OK abhängig zu machen und
empfiehlt deshalb, Autorisierungsquittungen immer mit einem http/1.1 200 OK
und einer leeren Antwortseite an PostFinance zu beantworten (keine HTML Tags).
Die Autorisierungsquittung enthält:
· Alle vom Merchant übergebenen Parameter ausser deliveryPaymentType (deferred/
immediate), welcher nur im Zahlungsdetail des Merchant-GUI yellowpay
sichtbar ist.
*/

$strPost = '';
$strForm = '';
foreach ($_POST as $name => $value) {
    if (   $name == 'deliveryPaymentType'
        || $name == 'txtShopPara') {
        continue;
    }
    addParam($name, $value);
}

/*
// Immer hinzugefügt:
· txtTransactionID (Payment-ID, von PostFinance generiert).
// 6 digits in the example
· txtPayMet (vom Shopper gewählte Zahlungsart, von PostFinance generiert).
*/
addParam('txtTransactionID', '2684');
addParam('txtPayMet', 'whatever');

/*
// Nur bei methode PostFinanceCard:
· txtEp2TrxID (Transaction-ID, ep2-Transaktionsreferenz bei Zahlungsart PostFinance
Card von PostFinance generiert; die ersten 8 alphanumerischen Stellen
repräsentieren die ep2-Terminal-ID, die folgenden 8 numerischen Stellen entsprechen
der ep2-Transaktions-Laufnummer)
*/
addParam('txtEp2TrxID', mt_rand(10000000, 99999999).mt_rand(10000000, 99999999));

/*
// Nur bei methode PostFinance E-Rechnung:
· txtESR_Member wenn die Zahlungsart PostFinance E-Rechnung gewählt wurde.
· txtESR_Ref wenn die Zahlungsart PostFinance E-Rechnung gewählt wurde.
· txtHashBack6
*/
addParam('txtESR_Member',
  (!empty($_POST['txtESR_Member'])
      ? $_POST['txtESR_Member']
      : mt_rand(10, 99).mt_rand(10, 999999).mt_rand(0, 9)
  )
);
addParam('txtESR_Ref',
  (!empty($_POST['txtESR_Ref'])
      ? $_POST['txtESR_Ref']
      : '1234567890123456'
  )
);

/*
Der beim Zahlungsmaskenaufruf übergebene, optionale, Parameter txtShopPara
wird im http oder https POST nicht als einzelner Parameter zurückgegeben sondern
in einzelne Key/Value Pairs aufgespalten. Aus „key1=value1&key2=value2“
wird im http oder https POST:
key1=value1
key2=value2
*/
if (!empty($_POST['txtShopPara'])) {
    $arrPara = explode('&', $_POST['txtShopPara']);
    foreach ($arrPara as $value) {
        $arrArg = explode('=', $value);
        addParam($arrArg[0], $arrArg[1]);
    }
}

function addParam($name, $value)
{
    global $strPost, $strForm;

    $strForm .= "        <tr><td>$name</td><td><input type=\"text\" name=\"$name\" value=\"$value\" /></td></tr>\n";
    $strPost .= "$name=".urlencode($value)."\r\n";
}

if ($output == 'post') {
    $fp = fopen("$returnUri&result=$formResult", 'w+');
    if (!$fp) {
        die("Failed to connect back");
    }
    fwrite($fp, $strPost);
    fclose($fp);
    die();
}

die(
'<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
  <head>
    <title>Yellowpay Dummy</title>
    <script type="text/javascript">
    // <![CDATA[
    function submitResult(result) {
      document.yellowpay.action="'.$returnUri.'&result="+result;
      document.yellowpay.submit();
    }
    // ]]>
    </script>
  </head>
  <body>
    <form name="yellowpay" method="post" action="'.htmlspecialchars($returnUri).'">
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
