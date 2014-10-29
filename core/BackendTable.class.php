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

    public function __construct($attrs = array(), $options = array()) {
        global $_ARRAYLANG;
        
        if ($attrs instanceof \Cx\Core_Modules\Listing\Model\Entity\DataSet) {
            $first = true;
            $row = 1;
            foreach ($attrs as $rowname=>$rows) {
                $col = 0;
                foreach ($rows as $header=>$data) {
                    if (
                        isset($options['fields']) &&
                        isset($options['fields'][$header]) &&
                        isset($options['fields'][$header]['showOverview']) &&
                        !$options['fields'][$header]['showOverview']
                    ) {
                        continue;
                    }
                    if ($first) {
                        if (isset($_ARRAYLANG[$header])) {
                            $header = $_ARRAYLANG[$header];
                        }
                        $this->setCellContents(0, $col, $header, 'th');
                    }
                    if (is_object($data) && get_class($data) == 'DateTime') {
                        $data = $data->format(ASCMS_DATE_FORMAT);
                    } else if (gettype($data) == 'boolean') {
                        $data = '<i>' . ($data ? 'Yes' : 'No') . '</i>';
                    } else if ($data === null) {
                        $data = '<i>NULL</i>';
                    } else if (empty($data)) {
                        $data = '<i>(empty)</i>';
                    }
                    $this->setCellContents($row, $col, $data);
                    $col++;
                }
                if (is_array($options['functions'])) {
                    if ($first) {
                        $header = 'FUNCTIONS';
                        if (isset($_ARRAYLANG['FUNCTIONS'])) {
                            $header = $_ARRAYLANG['FUNCTIONS'];
                        }
                        $this->setCellContents(0, $col, $header, 'th');
                    }
                    if (!isset($options['functions']['baseUrl'])) {
                        $options['functions']['baseUrl'] = clone \Env::get('cx')->getRequest();
                    }
                    $this->setCellContents($row, $col, $this->getFunctionsCode($rowname, $options['functions']));
                }
                $first = false;
                $row++;
            }
            $attrs = array();
        }
        parent::__construct(array_merge($attrs, array('class' => 'adminlist')));
    }
    
    protected function getFunctionsCode($rowname, $functions) {
        $baseUrl = $functions['baseUrl'];
        $code = '<span class="functions">';
        if (isset($functions['edit']) && $functions['edit']) {
            $editUrl = clone $baseUrl;
            $editUrl->setParam('editid', $rowname);
            $code .= '<a href="' . $editUrl . '" class="edit"></a>';
        }
        if (isset($functions['delete']) && $functions['delete']) {
            $code .= '<a href="#" class="delete"></a>';
        }
        return $code . '</span>';
    }

    public function toHtml() {
        $this->altRowAttributes(1, array('class' => 'row1'), array('class' => 'row2'), true);
        return parent::toHtml();
    }
}
