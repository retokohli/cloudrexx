<?php

function _auctionUpdate()
{
    try{
        // delete obsolete table  contrexx_module_auction_access
        UpdateUtil::drop_table(DBPREFIX.'module_auction_access');
    }
    catch (UpdateException $e) {
        // we COULD do something else here..
        DBG::trace();
        return UpdateUtil::DefaultActionHandler($e);
    }

    return true;
}
?>
