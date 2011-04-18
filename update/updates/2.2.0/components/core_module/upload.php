<?php

function _uploadUpdate()
{
    try { //update/create settings
        //remove fileuploader setting
        UpdateUtil::sql('DELETE FROM '.DBPREFIX.'_settings WHERE setid=70 AND setname="fileUploaderStatus"');
        //see if we have already inserted the new settings
        UpdateUtil::sql('SELECT id FROM '.DBPREFIX.'_settings WHERE id=73 OR id=74');
        $res = UpdateUtil::sql('DELETE FROM '.DBPREFIX.'_settings WHERE setid=70 AND setname="fileUploaderStatus"');

        $id73found = false;
        $id74found = false;
       
        while(!$res->EOF) {
            $id = $res->fields['id'];
            if($id == 73) $id73found = true;
            if($id == 74) $id74found = true;

            $res->MoveNext();
        }

        if(!$id73found) //insert if not found
            UpdateUtil::sql('INSERT INTO '.DBPREFIX.'_settings VALUES(73,"advancedUploadFrontend","on",52)');
        if(!$id74found) //insert if not found
            UpdateUtil::sql('INSERT INTO '.DBPREFIX.'_settings VALUES(74,"advancedUploadBackend","on",52)');
    }
    catch (UpdateException $e) {
        DBG::trace();
        return UpdateUtil::DefaultActionHandler($e);
    }
}