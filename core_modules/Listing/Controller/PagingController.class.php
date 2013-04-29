<?php

namespace Cx\Core_Modules\Listing\Controller;

class PagingController extends ListingHandler {
    protected $countPerPage = 0;
    protected $currentPage = 0;
    
    public function __construct() {
        global $_CONFIG;
        
        $this->countPerPage = $_CONFIG['corePagingLimit'];
    }
    
    public function handle(&$offset, &$count, &$criteria, &$order, &$args) {
        $offset = 0;
        if (isset($args['pos'])) {
            $offset = $args['pos'];
        }
        $count = $this->countPerPage;
    }
}
