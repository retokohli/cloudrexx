<?php

/**
 * Cloudrexx
 *
 * @link      http://www.cloudrexx.com
 * @copyright Cloudrexx AG 2007-2015
 *
 * According to our dual licensing model, this program can be used either
 * under the terms of the GNU Affero General Public License, version 3,
 * or under a proprietary license.
 *
 * The texts of the GNU Affero General Public License with an additional
 * permission and of our proprietary license can be found at and
 * in the LICENSE file you have received along with this program.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU Affero General Public License for more details.
 *
 * "Cloudrexx" is a registered trademark of Cloudrexx AG.
 * The licensing of the program under the AGPLv3 does not imply a
 * trademark license. Therefore any rights, title and interest in
 * our trademarks remain entirely with us.
 */

/**
 * @copyright   CLOUDREXX CMS - CLOUDREXX AG
 * @author      CLOUDREXX Development Team <info@cloudrexx.com>
 * @package     cloudrexx
 * @subpackage  core_html
 */

namespace Cx\Core\Html\Controller;

/**
 * Creates a table view
 *
 * @copyright   CLOUDREXX CMS - CLOUDREXX AG
 * @author      CLOUDREXX Development Team <info@cloudrexx.com>
 * @package     cloudrexx
 * @subpackage  core_html
 */
class TableGenerator extends \BackendTable
{
    /**
     * Overwrites the constructor to convert the $attr array into a dataset to
     * flip it and show the attributes besides the values.
     *
     * @param $attrs   array attributes and values
     * @param $options array options for view generator
     */
    public function __construct($attrs = array(), $options = array(), $readOnly)
    {
        global $_ARRAYLANG;

        // Rename Key Fields
        foreach ($attrs as $rowname=>$row) {
            $newRowName = $rowname;
            if (isset($_ARRAYLANG[$rowname])) {
                $newRowName = $_ARRAYLANG[$rowname];
            }

            if (
                $readOnly &&
                isset($options['fields']) &&
                isset($options['fields'][$rowname]) &&
                isset($options['fields'][$rowname]['showReadOnly']) &&
                !$options['fields'][$rowname]['showReadOnly']
            ) {
                continue;
            }

            if (
                $readOnly &&
                isset($options['fields']) &&
                isset($options['fields'][$rowname]) &&
                isset($options['fields'][$rowname]['showfield'])
            ) {
                $callback = $options['fields'][$rowname]['showfield'];
                $vgId = null;
                if (
                    isset($options['functions']) &&
                    isset($options['functions']['vg_increment_number'])
                ) {
                    $vgId = $options['functions']['vg_increment_number'];
                }
                if (
                    is_array($callback) &&
                    isset($callback['adapter']) &&
                    isset($callback['method'])
                ) {
                    $json = new \Cx\Core\Json\JsonData();
                    $jsonResult = $json->data(
                        $callback['adapter'],
                        $callback['method'],
                        array(
                            'data' => $row,
                            'rows' => $attrs,
                            'options' => $options['fields'][$rowname],
                            'vgId' => $vgId,
                        )
                    );
                    if ($jsonResult['status'] == 'success') {
                        $data = $jsonResult["data"];
                    }
                } else if(is_callable($callback)){
                    $data = $callback(
                        $row,
                        $attrs,
                        $options['fields'][$rowname],
                        $vgId
                    );
                }
            } else {
                $data = $row;
            }
            $rows[$newRowName] = $data;
        }

        $data = new \Cx\Core_Modules\Listing\Model\Entity\DataSet(
            array('key' => array_keys($rows), 'value' => array_values($rows))
        );
        $options['fields']['key']['sorting'] = false;
        $options['fields']['value']['sorting'] = false;
        $options['functions']['add'] = false;
        $options['functions']['edit'] = false;
        $options['functions']['delete'] = false;
        $options['functions']['show'] = false;
        unset($options['multiActions']);
        unset($options['tabs']);

        $data = $data->flip();

        parent::__construct($data, $options, true);
    }
}
