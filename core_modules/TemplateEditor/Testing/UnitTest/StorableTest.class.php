<?php

namespace Cx\Core_Modules\TemplateEditor\Testing\UnitTest;

use Cx\Core\Test\Model\Entity\ContrexxTestCase;
use Cx\Core\View\Model\Entity\Theme;
use Cx\Core_Modules\TemplateEditor\Model\Entity\ColorOption;
use Cx\Core_Modules\TemplateEditor\Model\Entity\ThemeOptions;
use Cx\Core_Modules\TemplateEditor\Model\Repository\ThemeOptionsRepository;
use Cx\Core_Modules\TemplateEditor\Model\TestStorage;

/**
 * Class StorableTest
 *
 * @copyright   CONTREXX CMS - COMVATION AG
 * @author      Robin Glauser <robin.glauser@comvation.com>
 * @package     contrexx
 * @subpackage  core_module_templateeditor
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
        $themeOption = $this->themeOptionRepository->get(new Theme(null, null, 'standard_3_0'));
        $this->assertTrue($themeOption instanceof ThemeOptions);
        if ($themeOption instanceof ThemeOptions){
            $this->assertTrue($themeOption->getOption('main_color') instanceof ColorOption);
        }
    }

    public function testSaveOption()
    {
        $themeOption = $this->themeOptionRepository->get(new Theme(null, null, 'standard_3_0'));
        $newColor = 'dddddd';
        $this->assertTrue($themeOption instanceof ThemeOptions);
        if ($themeOption instanceof ThemeOptions) {
            $color = $themeOption->getOption('main_color');
            $color->handleChange($newColor);
        }
    }

}