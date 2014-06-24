<?php
/**
 * Paging controller
 *
 * @copyright   CONTREXX CMS - COMVATION AG
 * @author      COMVATION Development Team <info@comvation.com>
 * @package     contrexx
 * @subpackage  coremodule_listing
 */

namespace Cx\Core_Modules\Listing\Controller;

/**
 * Paging controller
 *
 * @copyright   CONTREXX CMS - COMVATION AG
 * @author      COMVATION Development Team <info@comvation.com>
 * @package     contrexx
 * @subpackage  coremodule_listing
 */
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
