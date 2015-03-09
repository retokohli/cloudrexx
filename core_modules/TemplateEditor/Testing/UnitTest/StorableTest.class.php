<?php

/**
 * FileSystemTest
 * 
 * @copyright   CONTREXX CMS - COMVATION AG
 * @author      Comvation Development Team <info@comvation.com>
 * @author      SS4U <ss4u.comvation@gmail.com>
 * @version     1.0.0
 * @package     contrexx
 * @subpackage  coremodule_mediabrowser
 */

namespace Cx\Core_Modules\TemplateEditor\Testing\UnitTest;

use Core_Modules\TemplateEditor\Model\Entity\ColorOption;
use Core_Modules\TemplateEditor\Model\Entity\ThemeOptions;
use Core_Modules\TemplateEditor\Model\Repository\ThemeOptionsRepository;
use Core_Modules\TemplateEditor\Model\Storable;
use Cx\Core\Test\Model\Entity\ContrexxTestCase;

/**
 * FileSystemTest
 * 
 * @copyright   CONTREXX CMS - COMVATION AG
 * @author      Robin Glauser <robin.glauser@comvation.com>
 * @version     1.0.0
 * @package     contrexx
 * @subpackage  coremodule_mediabrowser
 */
class StorableTest extends ContrexxTestCase
{
    /**
     * @var Storable
     */
    protected $testStorage;

    /**
     * @var ThemeOptionsRepository
     */
    protected $themeOptionRepository;

    protected function setUp()
    {
        $this->testStorage = new TestStorage();
        $this->themeOptionRepository = new ThemeOptionsRepository($this->testStorage);
    }

    public function testLoadOption()
    {
        $themeOption = $this->themeOptionRepository->getByName('standard_3_0');
        $this->assertTrue($themeOption instanceof ThemeOptions);
        if ($themeOption instanceof ThemeOptions){
            $this->assertTrue($themeOption->getOption('main_color') instanceof ColorOption);
        }
    }

    public function testSaveOption()
    {
        $themeOption = $this->themeOptionRepository->getByName('standard_3_0');
        $newColor = 'dddddd';
        $this->assertTrue($themeOption instanceof ThemeOptions);
        if ($themeOption instanceof ThemeOptions) {
            $themeOption->setOption('main_color', $newColor);
            $this->assertTrue(
                $themeOption->getOption('main_color') == $newColor
            );
        }
    }

}