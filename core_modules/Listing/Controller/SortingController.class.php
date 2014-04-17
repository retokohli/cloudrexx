<?php

namespace Cx\Core_Modules\Listing\Controller;

class SortingController {
	
	public function handle($params, $config) {
	    if (!isset($config['order'])) {
	        return $params;
	    }
	    $order = explode('/', $config['order']);
	    $sortField = current($order);
	    $sortOrder = SORT_ASC;
	    if (count($order) > 1) {
	        if ($order[1] == 'DESC') {
	            $sortOrder = SORT_DESC;
	        }
	    }
	    $params['order'] = array($sortField => $sortOrder);
	    return $params;
	}
}
