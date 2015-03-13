<?php
/**
 * @copyright   Comvation AG
 * @author      Robin Glauser <robin.glauser@comvation.com>
 * @package     contrexx
 */

namespace Cx\Core_Modules\TemplateEditor\Testing\UnitTest;


use Cx\Core\Core\Controller\Cx;
use Cx\Core_Modules\TemplateEditor\Model\Storable;
use Cx\Core_Modules\TemplateEditor\Model\YamlSerializable;
use Symfony\Component\Yaml\Yaml;

class TestStorage implements Storable
{

    /**
     * @param String $name
     *
     * @return array
     */
    public function retrieve($name)
    {
        return require_once Cx::instanciate()->getCodeBaseCoreModulePath().'/TemplateEditor/Testing/UnitTest/Component.php';
    }

    /**
     * @param                  $name
     * @param YamlSerializable $data
     *
     * @return bool
     */
    public function persist($name,YamlSerializable $data)
    {
        return true;
    }

}