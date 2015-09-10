<?php

namespace Cx\Core_Modules\TemplateEditor\Testing\UnitTest;

use Cx\Core\Test\Model\Entity\ContrexxTestCase;
use Cx\Core\View\Model\Entity\Theme;
use Cx\Core_Modules\TemplateEditor\Model\Entity\ColorOption;
use Cx\Core_Modules\TemplateEditor\Model\Entity\OptionSet;
use Cx\Core_Modules\TemplateEditor\Model\Repository\OptionSetRepository;
use Cx\Core_Modules\TemplateEditor\Model\Storable;
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
     * @var OptionSetRepository
     */
    protected $themeOptionRepository;

    protected function setUp()
    {
        $this->testStorage = new TestStorage();
        $this->themeOptionRepository = new OptionSetRepository($this->testStorage);
    }

    public function testLoadOption()
    {
        $themeOption = $this->themeOptionRepository->get(new Theme(null, null, 'standard_3_0'));
        $this->assertTrue($themeOption instanceof OptionSet);
        if ($themeOption instanceof OptionSet){
            $this->assertTrue($themeOption->getOption('main_color') instanceof ColorOption);
        }
    }

    public function testSaveOption()
    {
        $themeOption = $this->themeOptionRepository->get(new Theme(null, null, 'standard_3_0'));
        $newColor = 'dddddd';
        $this->assertTrue($themeOption instanceof OptionSet);
        if ($themeOption instanceof OptionSet) {
            /**
             * @var $color ColorOption
             */
            $color = $themeOption->getOption('main_color');
            $color->handleChange($newColor);
            $this->assertTrue($color->getColor() == $newColor);
        }

        $this->assertTrue($this->themeOptionRepository->save($themeOption));
    }

}