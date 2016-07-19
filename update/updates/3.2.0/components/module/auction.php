<?php

function _auctionUpdate()
{
    try{
        // delete obsolete table  contrexx_module_auction_access
        \Cx\Lib\UpdateUtil::drop_table(DBPREFIX.'module_auction_access');
    }
    catch (\Cx\Lib\UpdateException $e) {
        // we COULD do something else here..
        DBG::trace();
        return \Cx\Lib\UpdateUtil::DefaultActionHandler($e);
    }

    return true;
}
