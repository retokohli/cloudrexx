<?php
if (!@include_once('config/configuration.php')) {
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
		
		$objSpider = $objDatabase->Execute("SELECT id, page, count FROM ".DBPREFIX."stats_spiders");
		
		if ($objSpider !== false) {
			while (!$objSpider->EOF) {
				if (!isset($arrIndexedPages[$objSpider->fields['page']])) {
					$arrIndexedPages[$objSpider->fields['page']] = array();
				}
				array_push($arrIndexedPages[$objSpider->fields['page']], $objSpider->fields['count'] = $objSpider->fields['id']);
				$objSpider->MoveNext();
			}
			
			foreach ($arrIndexedPages as $page => $indexes) {
				if (count($indexes)>1) {
					krsort($indexes);
					reset($indexes);
					$id = current($indexes);
					$objDatabase->Execute("DELETE FROM ".DBPREFIX."stats_spiders WHERE page='".$page."' AND id!=".$id);
					//print "DELETE FROM ".DBPREFIX."stats_spiders WHERE page='".addslashes($page)."' AND id!=".$id."<br />";
				}
			}
			
		}
	}
}

?>
</form>