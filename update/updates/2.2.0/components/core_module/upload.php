<?php

function _uploadUpdate()
{
    try {
        //remove fileuploader module
        UpdateUtil::sql("UPDATE `".DBPREFIX."modules` SET name='upload' WHERE name='fileUploader'");
    }
    catch (UpdateException $e) {
        DBG::trace();
        return UpdateUtil::DefaultActionHandler($e);
    }
}
