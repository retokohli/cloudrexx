<?php

/**
 * BackendTable
 *
 * @copyright   CONTREXX CMS - COMVATION AG
 * @author      COMVATION Development Team <info@comvation.com>
 * @package     contrexx
 * @subpackage  core
 */

/**
 * BackendTable
 *
 * @copyright   CONTREXX CMS - COMVATION AG
 * @author      COMVATION Development Team <info@comvation.com>
 * @package     contrexx
 * @subpackage  core
 */
class BackendTable extends HTML_Table {

    public function __construct($attrs = array()) {
    	if ($attrs instanceof \Cx\Core_Modules\Listing\Model\Entity\DataSet) {
    		$first = true;
    		$row = 1;
    		foreach ($attrs as $colname=>$rows) {
    			$col = 0;
    			foreach ($rows as $header=>$data) {
    				if ($first) {
    					$this->setCellContents(0, $col, $header, 'th');
    				}
    				$this->setCellContents($row, $col, $data);
    				$col++;
    			}
    			$first = false;
    			$row++;
    		}
    		$attrs = array();
    	}
        parent::__construct(array_merge($attrs, array('class' => 'adminlist')));
    }

    public function toHtml() {
        $this->altRowAttributes(1, array('class' => 'row1'), array('class' => 'row2'), true);
        return parent::toHtml();
    }

}
