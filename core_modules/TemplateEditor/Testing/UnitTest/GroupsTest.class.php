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
 * Class GroupsTest
 *
 * @copyright   CLOUDREXX CMS - CLOUDREXX AG
 * @author      Adrian Berger <adrian.berger@cloudrexx.com>
 * @package     contrexx
 * @subpackage  core_module_templateeditor
 */
class GroupsTest extends \Cx\Core\Test\Model\Entity\ContrexxTestCase
{
    /**
     * @var \Cx\Core_Modules\TemplateEditor\Model\Entity\TestStorage
     */
    protected $testStorage;

    /**
     * @var \Cx\Core_Modules\TemplateEditor\Model\Repository\OptionSetRepository
     */
    protected $themeOptionRepository;

    /**
     * @var \Cx\Core_Modules\TemplateEditor\Model\Entity\OptionSet
     */
    protected $optionSet;

    /**
     * Set up the unit test
     *
     * @access protected
     */
    protected function setUp()
    {
        $this->testStorage =
            new \Cx\Core_Modules\TemplateEditor\Model\Entity\TestStorage();
        $this->themeOptionRepository =
            new \Cx\Core_Modules\TemplateEditor\Model\Repository\OptionSetRepository(
                $this->testStorage
            );
        $this->optionSet = $this->themeOptionRepository->get(
            new \Cx\Core\View\Model\Entity\Theme(
                null,
                null,
                '/core_modules/TemplateEditor/Testing/UnitTest/Test_Template'
            )
        );
    }

    /**
     * Check if the function getOptionsOrderedByGroups works properly
     *
     * @access public
     */
    public function testCheckOrderOfOptions()
    {
        $optionGroups = $this->optionSet->getOptionsOrderedByGroups();
        $this->assertTrue(is_array($optionGroups));
        // check one self defined group
       $this->assertTrue(
            count($optionGroups['example_group_1']) == 2 &&
            array_key_exists('main_title', $optionGroups['example_group_1']) &&
            array_key_exists('news_area', $optionGroups['example_group_1'])
        );
        // check standard group
         $this->assertTrue(
            array_key_exists('logo_image', $optionGroups['others_group'])
        );
    }

}