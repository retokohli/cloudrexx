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
 * Class OptionTest
 *
 * @copyright   CLOUDREXX CMS - CLOUDREXX AG
 * @author      Robin Glauser <robin.glauser@cloudrexx.com>
 * @package     contrexx
 * @subpackage  core_module_templateeditor
 */
class OptionTest extends \Cx\Core\Test\Model\Entity\ContrexxTestCase
{
    protected function setUp() {
        global $_LANGID;
        $_LANGID = 1;
        \Env::get('init')->loadLanguageData('TemplateEditor');
    }

    public function testTextOption() {
        $type = 'Cx\Core_Modules\TemplateEditor\Model\Entity\TextOption';
        $testValue = 'TestString';
        $textOption = new $type(
                'test',
                array(1 => 'Unit-Test'),
                array(
                    'textvalue' => $testValue,
                    'regex' => '/^[a-z]+$/i',
                    'regexError' => array(
                        1 => 'Darf nur Buchstaben enthalten: %s',
                    ),
                ),
                $type
        );
        $invalidValue = 'hello1';
        try {
            $caught = false;
            $textOption->handleChange($invalidValue);
        } catch (
            \Cx\Core_Modules\TemplateEditor\Model\Entity\OptionValueNotValidException $e) {
            $caught = true;
            $this->assertTrue((strpos($e->getMessage(), $invalidValue) !== false));
        }
        if (!$caught) {
            $this->assertTrue(false);
        }
        $this->renderOption($textOption, $testValue);
    }

    public function testTextareaOption() {
        $type = 'Cx\Core_Modules\TemplateEditor\Model\Entity\TextareaOption';
        $testValue = 'TestString \n TestString2';
        $textareaOption = new $type(
            'test',
            array(1 => 'Unit-Test'),
            array(
                'textvalue' => $testValue,
                'regex' => '/^[A-Za-z0-9 \\\]+$/i',
                'regexError' => array(
                    1 => 'Darf Buchstaben, Zahlen, Zeilenumbrüche und '.
                        'Leerzeichen enthalten: %s',
                ),
            ),
            $type
        );
        $invalidValue = 'invalid$';
        try {
            $caught = false;
            $textareaOption->handleChange($invalidValue);
        } catch (
        \Cx\Core_Modules\TemplateEditor\Model\Entity\OptionValueNotValidException $e) {
            $caught = true;
            $this->assertTrue((strpos($e->getMessage(), $invalidValue) !== false));
        }
        if (!$caught) {
            $this->assertTrue(false);
        }
        $this->renderOption($textareaOption, $testValue);
    }

    public function testAreaOption() {
        $type = 'Cx\Core_Modules\TemplateEditor\Model\Entity\AreaOption';
        $areaOption = new $type(
            'test',
            array(1 => 'Unit-Test'),
            array('active' => true),
            $type
        );
        $this->renderOption($areaOption, 'checked');
    }

    public function testColorOption() {
        $type = 'Cx\Core_Modules\TemplateEditor\Model\Entity\ColorOption';
        $color = '#efefef';
        $choice = array('#ededed', '#fefefe');
        $colorOption = new $type(
            'test',
            array(1 => 'Unit-Test'),
            array(
                'color' => $color,
                'choice' => $choice,
            ),
            $type
        );
        $backendTemplate = $colorOption->renderOptionField();
        $renderedTemplate = $backendTemplate->get();
        foreach ($choice as $colorChoice) {
            $this->assertTrue((strpos($renderedTemplate, $colorChoice) !== false));
        }
        $this->assertTrue((strpos($renderedTemplate, $color) !== false));
    }

    public function testImageOption() {
        $type = 'Cx\Core_Modules\TemplateEditor\Model\Entity\ImageOption';
        $url = 'https://placekitten.com/1500/300';
        $imageOption = new $type(
            'test',
            array(1 => 'Unit-Test'),
            array('url' => $url),
            $type
        );
        $this->renderOption($imageOption, $url);
    }

    /**
     * Render the option and try to find the searchValue
     *
     * @param object    $option         the option to render
     * @param string    $searchValue    the value which should be find
     */
    public function renderOption($option, $searchValue){

        $backendTemplate = $option->renderOptionField();
        $renderedTemplate = $backendTemplate->get();
        $this->assertTrue((strpos($renderedTemplate, $searchValue) !== false));
    }
}