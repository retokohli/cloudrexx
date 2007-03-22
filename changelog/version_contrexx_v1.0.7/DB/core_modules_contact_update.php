<?php
if (!@include_once('../../config/configuration.php')) {
	print "Plazieren Sie diese Datei in das Hauptverzeichniss Ihrer Contrexx Installation.";
} else {
?>
<form action="<?php echo $_SERVER['PHP_SELF'];?>" method="post">
<?php

	if (!isset($_POST['doUpdate'])) {
		print "<input type=\"submit\" name=\"doUpdate\" value=\"Update ausführen\" />";
		
	} else {
		require_once ASCMS_CORE_PATH.'/API.php';
		
		
		$errorMsg = '';
		$objDatabase = getDatabaseObject($errorMsg);
		$objDatabase->debug=true;
		
		$objResult = $objDatabase->Execute("SELECT setid, setname, setvalue FROM ".DBPREFIX."settings WHERE setname LIKE 'contactFormEmail%'");
		if ($objResult !== false) {
			while (!$objResult->EOF) {
				if ($objResult->fields['setname'] == 'contactFormEmail') {
					$key = 0;
				} else {
					$key = $objResult->fields['setid'];
				}
					
				$id = substr($objResult->fields['setname'],16);
				
				$arrEmails[$id] = array(
					'settings_key'	=> $key,
					'email'			=> $objResult->fields['setvalue']
				);
				$objResult->MoveNext();
			}
			
			if (is_array($arrEmails)) {
				foreach ($arrEmails as $id => $arrEmail) {
					if ($arrEmail['settings_key'] == 0) {
						$objDatabase->Execute("UPDATE ".DBPREFIX."module_contact_form SET mails='".$arrEmail['email']."' WHERE id=1");
					} else {
						if (empty($arrEmail['email'])) {
							$objDatabase->Execute("DELETE FROM ".DBPREFIX."settings WHERE setid=".$arrEmail['settings_key']);
						} elseif ($objDatabase->Execute("UPDATE ".DBPREFIX."module_contact_form SET mails='".$arrEmail['email']."' WHERE id=".$id) !== false) {
							$objDatabase->Execute("DELETE FROM ".DBPREFIX."settings WHERE setid=".$arrEmail['settings_key']);
						}
					}
				}
			}
		}
	}
}

?>
</form>