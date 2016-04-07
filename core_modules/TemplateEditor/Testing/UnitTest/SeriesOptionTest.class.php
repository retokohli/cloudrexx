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



namespace Cx\Core_Modules\TemplateEditor\Testing\UnitTest;

use Cx\Core\Html\Sigma;
use Cx\Core\Test\Model\Entity\ContrexxTestCase;
use Cx\Core_Modules\TemplateEditor\Model\Entity\SeriesOption;

/**
 * Class SeriesOptionTest
 *
 * @copyright   CLOUDREXX CMS - CLOUDREXX AG
 * @author      Adrian Berger <adrian.berger@comvation.com>
 * @package     contrexx
 * @subpackage  core_module_templateeditor
 */
class SeriesOptionTest extends ContrexxTestCase
{

    protected $template = '<!-- BEGIN option -->
        <div class="option {TEMPLATEEDITOR_OPTION_TYPE}">
            {TEMPLATEEDITOR_OPTION}
        </div>
        <!-- END option -->';

    protected function setUp() {
        global $_LANGID;
        $_LANGID = 1;
        \Env::get('init')->loadLanguageData('TemplateEditor');
    }

    public function testSeriesOption() {
        $elements = array(
            1 => array('url' => 'https://placekitten.com/1500/300'),
            2 => array('url' => 'https://placekitten.com/1000/500')
        );
        $imageOption = new SeriesOption(
            'test',
            array(1 => 'Unit-Test'),
            array('elements' => $elements)
        );
        $backendTemplate = new Sigma();
        $backendTemplate->setTemplate($this->template);
        $imageOption->renderOptionField($backendTemplate);
        $renderedTemplate = $backendTemplate->get();
        foreach ($elements['elements'] as $element) {
            foreach($element as $value) {
                $this->assertTrue((strpos($renderedTemplate, $value) !== 0));
            }
        }
    }

}