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
												description
										FROM	'.DBPREFIX.'module_gallery_categories
									');
	if ($objResult->RecordCount() > 0) {
		while (!$objResult->EOF) {
			foreach ($arrLanguages as $intLangId => $strValue) {
				$objDatabase->Execute('	INSERT
										INTO	'.DBPREFIX.'module_gallery_language
										SET		gallery_id='.$objResult->fields['id'].',
												lang_id='.$intLangId.',
												name="name",
												value="'.$objResult->fields['name'].'"
									');
				$objDatabase->Execute('	INSERT
										INTO	'.DBPREFIX.'module_gallery_language
										SET		gallery_id='.$objResult->fields['id'].',
												lang_id='.$intLangId.',
												name="desc",
												value="'.$objResult->fields['description'].'"
									');
			}
			$objResult->MoveNext();
		}
	}
?>