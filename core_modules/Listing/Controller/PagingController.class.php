<?php

namespace Cx\Core_Modules\Listing\Controller;

class PagingController extends ListingHandler {
    protected $countPerPage = 0;
    protected $currentPage = 0;
    
    public function __construct() {
        global $_CONFIG;
        
        $this->countPerPage = $_CONFIG['corePagingLimit'];
    }
    
    public function handle($params, $config) {
        $params['offset'] = 0;
        if (isset($config['pos'])) {
            $params['offset'] = $config['pos'];
        }
        $params['count'] = $this->countPerPage;
        return $params;
    }
}
