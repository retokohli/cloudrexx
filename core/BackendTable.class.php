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
            $hasMasterTableHeader = !empty($options['header']);
            // add master table-header-row
            if ($hasMasterTableHeader) {
                $this->addRow(array(0 => $options['header']), null, 'th');
            }
            $first = true;
            $row = 1 + $hasMasterTableHeader;
            foreach ($attrs as $rowname=>$rows) {
                $col = 0;
                $virtual = $rows['virtual'];
                unset($rows['virtual']);
                if (isset($options['multiActions'])) {
                    $this->setCellContents($row, $col, '<input name="select-' . $rowname . '" value="' . $rowname . '" type="checkbox" />', 'TD', '0', false);
                    $col++;
                }
                foreach ($rows as $header=>$data) {
                    $encode = true;
                    if (
                        isset($options['fields']) &&
                        isset($options['fields'][$header]) &&
                        isset($options['fields'][$header]['showOverview']) &&
                        !$options['fields'][$header]['showOverview']
                    ) {
                        continue;
                    }
                    
                    $origHeader = $header;
                    if(isset($options['fields'][$header]['sorting'])) {
                        $sorting = $options['fields'][$header]['sorting'];
                    } else if(isset($options['functions']['sorting'])) {
                        $sorting = $options['functions']['sorting'];
                    }
                    if ($first) {
                        if (isset($options['fields'][$header]['header'])) {
                            $header = $options['fields'][$header]['header'];
                        }
                        if (isset($_ARRAYLANG[$header])) {
                            $header = $_ARRAYLANG[$header];
                        }
                        if (
                            is_array($options['functions']) &&
                            isset($options['functions']['sorting']) &&
                            $options['functions']['sorting'] &&
                            $sorting!==false
                        ) {
                            $order = '';
                            $img = '&uarr;&darr;';
                            if (isset($_GET['order'])) {
                                $supOrder = explode('/', $_GET['order']);
                                if (current($supOrder) == $origHeader) {
                                    $order = '/DESC';
                                    $img = '&darr;';
                                    if (count($supOrder) > 1 && $supOrder[1] == 'DESC') {
                                        $order = '';
                                        $img = '&uarr;';
                                    }
                                }
                            }
                            $header = '<a href="' .  \Env::get('cx')->getRequest()->getUrl() . '&order=' . $origHeader . $order . '" style="white-space: nowrap;">' . $header . ' ' . $img . '</a>';
                        }
                        if ($hasMasterTableHeader) {
                            $this->setCellContents(1, $col, $header, 'td', 0);
                        } else {
                            $this->setCellContents(0, $col, $header, 'th', 0);
                        }
                    }
                    if (
                        isset($options['fields']) &&
                        isset($options['fields'][$origHeader]) &&
                        isset($options['fields'][$origHeader]['table']) &&
                        isset($options['fields'][$origHeader]['table']['parse']) &&
                        is_callable($options['fields'][$origHeader]['table']['parse'])
                    ) {
                        $callback = $options['fields'][$origHeader]['table']['parse'];
                        $data = $callback($data, $rows);
                        $encode = false; // todo: this should be set by callback
                    } else if (is_object($data) && get_class($data) == 'DateTime') {
                        $data = $data->format(ASCMS_DATE_FORMAT);
                    } else if (isset($options['fields'][$origHeader]) && isset($options['fields'][$origHeader]['type']) && $options['fields'][$origHeader]['type'] == '\Country') {
                        $data = \Cx\Core\Country\Controller\Country::getNameById($data);
                        if (empty($data)) {
                            $data = \Cx\Core\Country\Controller\Country::getNameById(204);
                        }
                    } else if (gettype($data) == 'boolean') {
                        $data = '<i>' . ($data ? $_ARRAYLANG['TXT_YES'] : $_ARRAYLANG['TXT_NO']) . '</i>';
                        $encode = false;
                    } else if ($data === null) {
                        $data = '<i>NULL</i>';
                        $encode = false;
                    } else if (empty($data)) {
                        $data = '<i>(empty)</i>';
                        $encode = false;
                    }
                    $this->setCellContents($row, $col, $data, 'TD', 0, $encode);
                    $col++;
                }
                if (is_array($options['functions'])) {
                    if ($first) {
                        $header = 'Functions';
                        if (isset($_ARRAYLANG['TXT_FUNCTIONS'])) {
                            $header = $_ARRAYLANG['TXT_FUNCTIONS'];
                        }
                        if ($hasMasterTableHeader) {
                            $this->setCellContents(1, $col, $header, 'td', 0, true);
                        } else {
                            $this->setCellContents(0, $col, $header, 'th', 0, true);
                        }
                        $this->updateColAttributes($col, array('style' => 'text-align:right;'));
                    }
                    if (!isset($options['functions']['baseUrl'])) {
                        $options['functions']['baseUrl'] = clone \Env::get('cx')->getRequest()->getUrl();
                    }
                    $this->setCellContents($row, $col, $this->getFunctionsCode($rowname, $rows, $options['functions'], $virtual), 'TD', 0);
                }
                $first = false;
                $row++;
            }
            // adjust colspan of master-table-header-row
            if ($hasMasterTableHeader) {
                $this->setCellAttributes(0, 0, array('colspan' => $col + is_array($options['functions'])));
                $this->updateRowAttributes(1, array('class' => 'row3'), true);
            }
            // add multi-actions
            if (isset($options['multiActions'])) {
                $multiActionsCode = '
                    <img src="images/icons/arrow.gif" width="38" height="22" alt="^" title="^">
                    <a href="#" onclick="jQuery(\'input[type=checkbox]\').prop(\'checked\', true);return false;">' . $_ARRAYLANG['TXT_SELECT_ALL'] . '</a> /
                    <a href="#" onclick="jQuery(\'input[type=checkbox]\').prop(\'checked\', false);return false;">' . $_ARRAYLANG['TXT_DESELECT_ALL'] . '</a>
                    <img alt="-" title="-" src="images/icons/strike.gif">
                ';
                $multiActions = array(''=>$_ARRAYLANG['TXT_SUBMIT_SELECT']);
                foreach ($options['multiActions'] as $actionName=>$actionProperties) {
                    $actionTitle = $actionName;
                    if (isset($actionProperties['title'])) {
                        $actionTitle = $actionProperties['title'];
                    } else if (isset($_ARRAYLANG[$value])) {
                        $actionTitle = $_ARRAYLANG[$value];
                    }
                    if (isset($actionProperties['jsEvent'])) {
                        $actionName = $actionProperties['jsEvent'];
                    }
                    $multiActions[$actionName] = $actionTitle;
                }
                $select = new \Cx\Core\Html\Model\Entity\DataElement(
                    'cxMultiAction',
                    \Html::getOptions($multiActions),
                    \Cx\Core\Html\Model\Entity\DataElement::TYPE_SELECT
                );
                // this is not a nice place for this code
                // but we should cleanup this complete class and make
                // it base on templates
                $select->setAttribute(
                    'onchange',
                    '
                        var regex = /([a-zA-Z\/]+):([a-zA-Z\/]+)/;
                        var matches = jQuery(this).val().match(regex);
                        if (!matches) {
                            return false;
                        }
                        var checkboxes = jQuery(this).closest("table").find("input[type=checkbox]");
                        var activeRows = [];
                        checkboxes.filter(":checked").each(function(el) {
                            activeRows.push(jQuery(this).val());
                        });
                        cx.trigger(matches[1], matches[2], activeRows);
                        checkboxes.prop("checked", false);
                        jQuery(this).val("");
                    '
                );
                $this->setCellContents($row, 0, $multiActionsCode . $select, 'TD', 0);
                $this->setCellAttributes($row, 0, array('colspan' => $col + is_array($options['functions'])));
            }
            $attrs = array();
        }
        parent::__construct(array_merge($attrs, array('class' => 'adminlist', 'width' => '100%')));
    }

    /**
     * Override from parent. Added contrexx_raw2xhtml support
     * @param type $row
     * @param type $col
     * @param type $contents
     * @param type $type
     * @param type $body
     * @param type $encode
     * @return type 
     */
    function setCellContents($row, $col, $contents, $type = 'TD', $body = 0, $encode = false)
    {
        if ($encode) {
            //replaces curly brackets, so they get not parsed with the sigma engine
            $contents = preg_replace(array("/{/","/}/"), array("&#123;","&#125;"), contrexx_raw2xhtml($contents), -1);
        }
        $ret = $this->_adjustTbodyCount($body, 'setCellContents');
        if (PEAR::isError($ret)) {
            return $ret;
        }
        $ret = $this->_tbodies[$body]->setCellContents($row, $col, $contents, $type);
        if (PEAR::isError($ret)) {
            return $ret;
        }
    }
    
    protected function getFunctionsCode($rowname, $rowData, $functions, $virtual = false ) {
        global $_ARRAYLANG;
        
        $baseUrl = $functions['baseUrl'];
        $code = '<span class="functions">';
        if(!$virtual){
            if (isset($functions['actions']) && is_callable($functions['actions'])) {
                $code .= $functions['actions']($rowData);                
            }
            
            if (isset($functions['edit']) && $functions['edit']) {
                $editUrl = clone $baseUrl;
                $editUrl->setParam('editid', $rowname);
                $code .= '<a href="' . $editUrl . '" class="edit" title="'.$_ARRAYLANG['TXT_CORE_RECORD_EDIT_TITLE'].'"></a>';
            }
            if (isset($functions['delete']) && $functions['delete']) {
                $deleteUrl = clone $baseUrl;
                $deleteUrl->setParam('deleteid', $rowname);
                $deleteUrl.='&csrf='.\Cx\Core\Csrf\Controller\Csrf::code();
                $onclick ='if (confirm(\''.$_ARRAYLANG['TXT_CORE_RECORD_DELETE_CONFIRM'].'\'))'.
                        'window.location.replace(\''.$deleteUrl.'\');';
                $_uri = 'javascript:void(0);';
                $code .= '<a onclick="'.$onclick.'" href="'.$_uri.'" class="delete" title="'.$_ARRAYLANG['TXT_CORE_RECORD_DELETE_TITLE'].'"></a>';
            }
        }
        return $code . '</span>';
    }

    public function toHtml() {
        $this->altRowAttributes(2, array('class' => 'row1'), array('class' => 'row2'), true);
        return parent::toHtml();
    }

}
