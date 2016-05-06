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

/**
 * Class SeriesOptionTest
 *
 * @copyright   CLOUDREXX CMS - CLOUDREXX AG
 * @author      Adrian Berger <adrian.berger@comvation.com>
 * @package     contrexx
 * @subpackage  core_module_templateeditor
 */
class SeriesOptionTest extends \Cx\Core\Test\Model\Entity\ContrexxTestCase
{

    protected function setUp() {
        \Env::get('init')->loadLanguageData('TemplateEditor');
    }

    public function testImageSeriesOption() {
        $type = 'Cx\Core_Modules\TemplateEditor\Model\Entity\ImageOption';
        $elements = array(
            1 => array('url' => 'https://placekitten.com/1500/300'),
            2 => array('url' => 'https://placekitten.com/1000/500'),
        );
        $this->renderSeriesOption($type, $elements);
    }

    public function testTextSeriesOption() {
        $type = 'Cx\Core_Modules\TemplateEditor\Model\Entity\TextOption';
        $elements = array(
            1 => array('textvalue' => 'testTextSeriesOption'),
            2 => array('textvalue' => 'testTextSeriesOption'),
        );
        $this->renderSeriesOption($type, $elements);
    }
    public function testTextareaSeriesOption() {
        $type = 'Cx\Core_Modules\TemplateEditor\Model\Entity\TextareaOption';
        $elements = array(
            1 => array('textvalue' => 'testTextareaSeriesOption'),
            2 => array('textvalue' => 'testTextareaSeriesOption'),
        );
        $this->renderSeriesOption($type, $elements);
    }

    public function testColorSeriesOption() {
        $type = 'Cx\Core_Modules\TemplateEditor\Model\Entity\ColorOption';
        $elements = array(
            1 => array('color' => '#8b45a3'),
            2 => array('color' => '#1f556a'),
        );
        $this->renderSeriesOption($type, $elements);
    }

    /**
     * Test the option 'combinedOption' as series
     */
    public function testCombinedSeriesOption() {
        $type = 'Cx\Core_Modules\TemplateEditor\Model\Entity\CombinedOption';
        $elements = array(
            'options' => array(
                'Cx\Core_Modules\TemplateEditor\Model\Entity\TextOption',
                'Cx\Core_Modules\TemplateEditor\Model\Entity\ImageOption',
            ),
            'elements' => array(
                array(
                    array('textvalue' => 'Funny Cat'),
                    array('url' => 'https://placekitten.com/1500/300'),
                ),
                array(
                    array('textvalue' => 'Small Cat'),
                    array('url' => 'https://placekitten.com/500/300'),
                ),
            )
        );
        $this->renderSeriesOption($type, $elements);
    }

    /**
     * Render the template for series option and try to find the elements
     *
     * @param string    $type       the type of the option
     * @param array     $elements   the elements to render
     */
    private function renderSeriesOption($type, $elements){
        $option = new \Cx\Core_Modules\TemplateEditor\Model\Entity\SeriesOption(
            'test',
            array(1 => 'Unit-Test'),
            array('elements' => $elements),
            $type,
            true
        );
        $backendTemplate = $option->renderOptionField();
        $renderedTemplate = $backendTemplate->get();
        // CombinedOption has one level more than the other series options
        if (
            $type == 'Cx\Core_Modules\TemplateEditor\Model\Entity\CombinedOption'
        ) {
            foreach ($elements['elements'] as $elements) {
                $this->checkElementsExists($elements, $renderedTemplate);
            }
        } else {
            $this->checkElementsExists($elements, $renderedTemplate);
        }
    }

    /**
     * Checks if the given elements exists in the renderer template
     *
     * @param array     $elements           the elements which should be found
     * @param string    $renderedTemplate   the rendered html template
     */
    private function checkElementsExists($elements, $renderedTemplate) {
        foreach($elements as $element) {
            foreach($element as $value) {
                $this->assertTrue((strpos($renderedTemplate, $value) !== false));
            }
        }
    }

}