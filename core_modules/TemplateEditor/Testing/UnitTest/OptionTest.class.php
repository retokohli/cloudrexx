<?php


namespace Cx\Core_Modules\TemplateEditor\Testing\UnitTest;
use Cx\Core\Test\Model\Entity\ContrexxTestCase;
use Cx\Core_Modules\TemplateEditor\Model\Entity\TextOption;

/**
 * Class OptionTest
 *
 * @copyright   CONTREXX CMS - COMVATION AG
 * @author      Robin Glauser <robin.glauser@comvation.com>
 * @package     contrexx
 * @subpackage  core_module_templateeditor
 */
class OptionTest extends ContrexxTestCase {

    public function testTextOption()
    {
        $textOption = new TextOption('test','test', array('value' => 'test', 'regex' => '/test/i'));
    }
}