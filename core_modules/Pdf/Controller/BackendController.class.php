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
 * Specific BackendController for this Component.
 * Use this to easily create a backend view
 *
 * @copyright   Cloudrexx AG
 * @author      Project Team SS4U <info@cloudrexx.com>
 * @package     cloudrexx
 * @subpackage  coremodule_pdf
 */

namespace Cx\Core_Modules\Pdf\Controller;

/**
 * Specific BackendController for this Component.
 * Use this to easily create a backend view
 *
 * @copyright   Cloudrexx AG
 * @author      Project Team SS4U <info@cloudrexx.com>
 * @package     cloudrexx
 * @subpackage  coremodule_pdf
 */
class BackendController extends \Cx\Core\Core\Model\Entity\SystemComponentBackendController {

    /**
     * Returns a list of available commands (?act=XY)
     * @return array List of acts
     */
    public function getCommands()
    {
        return array();
    }

    /**
     * Use this to parse your backend page
     *
     * You will get the template located in /View/Template/{CMD}.html
     * You can access Cx class using $this->cx
     * To show messages, use \Message class
     * @param \Cx\Core\Html\Sigma $template Template for current CMD
     * @param array $cmd CMD separated by slashes
     */
    public function parsePage(\Cx\Core\Html\Sigma $template, array $cmd)
    {

        // Not an entity, parse overview or settings
        switch (current($cmd)) {
            case '':
            default:
                // Parse entity view generation pages
                $entityClassName = $this->getNamespace() .
                    '\\Model\\Entity\\PdfTemplate';
                $this->parseEntityClassPage(
                    $template,
                    $entityClassName,
                    'PdfTemplate'
                );
                if ($template->blockExists('overview')) {
                    $template->touchBlock('overview');
                }
                break;
        }
    }

    /**
     * This method defines the option to generate the backend view (list and form)
     *
     * @global array $_ARRAYLANG Language data
     * @param string $entityClassName contains the FQCN from entity
     * @return array array containing the options
     */
    protected function getViewGeneratorOptions($entityClassName)
    {
        global $_ARRAYLANG;

        $classNameParts    = explode('\\', $entityClassName);
        $classIdentifier   = end($classNameParts);
        $placeholderPrefix = 'TXT_' . strtoupper(
            $this->getType() . '_' . $this->getName() .
            '_ACT_' . $classIdentifier
        );

        return array(
            'header'     => $_ARRAYLANG[$placeholderPrefix],
            'entityName' => $_ARRAYLANG[$placeholderPrefix . '_ENTITY'],
            'order' => array(
                'overview' => array(
                    'active',
                    'title'
                ),
            ),
            'functions' => array(
                'add'       => true,
                'edit'      => true,
                'delete'    => true,
                'sorting'   => true,
                'paging'    => true,
                'filtering' => false,
            ),
            'fields' => array(
                'id' => array(
                    'showOverview' => false,
                ),
                'title' => array(
                    'header' => $_ARRAYLANG[$placeholderPrefix . '_TITLE'],
                    'table'  => array(
                        'parse' => function($data, $rows, $options) {
                            $editUrl = clone $this->cx->getRequest()->getUrl();
                            $editUrl->setParam(
                                'editid',
                                '{' .
                                $options['functions']['vg_increment_number'] .
                                ',' . $rows['id'] . '}'
                            );
                            $data = '<a href="' . $editUrl . '"'
                                    . ' title="'.$data.'">'.$data.'</a>';
                            return $data;
                        },
                    ),
                ),
                'active' => array(
                    'header'   => $_ARRAYLANG[$placeholderPrefix . '_STATE'],
                    'formtext' => $_ARRAYLANG[$placeholderPrefix . '_ACTIVE'],
                    'sorting'  => false,
                    'table'    => array(
                        'parse' => function($data, $rows) {
                            $img = 'led_red.gif';
                            if ($data) {
                                $img = 'led_green.gif';
                            }
                            $data = '<img src="core/Core/View/Media/icons/'.
                                $img.'" />';
                            return $data;
                        },
                    ),
                ),
                'htmlContent' => array(
                    'header'       => $_ARRAYLANG[
                        $placeholderPrefix . '_HTML_CONTENT'
                    ] . '&nbsp;&nbsp;<span class="tooltip-trigger icon-info">'
                    . '</span><span class="tooltip-message">' .
                    $_ARRAYLANG[$placeholderPrefix . '_HTML_CONTENT_TOOLTIP'] .
                    '</span>',
                    'showOverview' => false,
                    'formfield'    => function (
                        $name,
                        $type,
                        $length,
                        $value,
                        $options
                    ) {
                        $editor = new \Cx\Core\Wysiwyg\Wysiwyg(
                            $name,
                            $value,
                            'fullpage'
                        );
                        $span   = new \Cx\Core\Html\Model\Entity\HtmlElement(
                            'span'
                        );
                        $span->addChild(
                            new \Cx\Core\Html\Model\Entity\TextElement($editor)
                        );
                        return $span;
                    }
                ),
            ),
        );
    }
}
