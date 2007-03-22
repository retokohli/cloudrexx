<?php

	$objResult = $objDatabase->Execute('SELECT	id
										FROM	'.DBPREFIX.'languages
									');
	$arrLanguages = array();
	while (!$objResult->EOF) {
		array_push($arrLanguages, $objResult->fields['id']);
		$objResult->MoveNext();
	}
	
	$objResult = $objDatabase->Execute('SELECT	id,
												name,
												linkname
										FROM	'.DBPREFIX.'module_gallery_pictures
									');
	if ($objResult->RecordCount() > 0) {
		while (!$objResult->EOF) {
			foreach ($arrLanguages as $intLangId => $strValue) {
				$objDatabase->Execute('	INSERT
										INTO	'.DBPREFIX.'module_gallery_language_pics
										SET		picture_id='.$objResult->fields['id'].',
												lang_id='.$intLangId.',
												name="'.$objResult->fields['name'].'",
												`desc`="'.$objResult->fields['linkname'].'"
									');
			}
			$objResult->MoveNext();
		}
	}
?>