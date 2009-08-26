<?php echo "<pre>";
// in einzelne Parameter splitten
$param = "DATA=%3CIDP+MSGTYPE%3D%22AuthenticationConfirm%22+RESULT%3D%220%22+AUTHMESSAGE%3D%22Authentication+Succeeded%22+KEYID%3D%221%2D0%22+MPI%5FSESSIONID%3D%2210d5IxA6GI44tAC8b5f5A6WUUt5A%22+XID%3D%2210d5IxA6GI44tAC8b5f5A6WUUt5A%22+CAVV%3D%22AAABBIIFmAAAAAAAAAAAAAAAAAA%3D%22+ECI%3D%221%22%2F%3E&SIGNATURE=0d6a6b0a39397db0ca336a163ce6053e616f7640ce0e994fe58e90b83e671b1e3d6e95827d0135832bff3ef238ba28365e2213992d82b166ce534ac9a2f6801f";
$param = urldecode(stripslashes($param));
$pattern = "/([A-Z]+)\=([^&]+)/";
$matches = array();
preg_match_all($pattern, $param, $matches);
print_r($matches);

// parameter in einzelne Variablen verteilen
$data = $matches[2][0];
$signature = $matches[2][1];

// Attribute aus XML-Tag lesen
$pattern = '/([a-z0-9]+)\="([^"]+)"/i';
$matches = array();
preg_match_all($pattern, $data, $matches);
print_r($matches);

// source ausgeben echo "<br /><hr>";
highlight_file($_SERVER['SCRIPT_FILENAME']);
?>
