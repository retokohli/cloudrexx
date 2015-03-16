<?php

namespace Cx\Core_Modules\TemplateEditor\Model;


use Cx\Core\Core\Controller\Cx;
use Symfony\Component\Yaml\Yaml;

/**
 * Class TestStorage
 *
 * @copyright   CONTREXX CMS - COMVATION AG
 * @author      Robin Glauser <robin.glauser@comvation.com>
 * @package     contrexx
 * @subpackage  core_module_templateeditor
 */
class TestStorage implements Storable
{

    /**
     * @param String $name
     *
     * @return array
     */
    public function retrieve($name) {
        return require_once Cx::instanciate()->getCodeBaseCoreModulePath()
            . '/TemplateEditor/Testing/UnitTest/Component.php';
    }

    /**
     * @param                  $name
     * @param YamlSerializable $data
     *
     * @return bool
     */
    public function persist($name, YamlSerializable $data) {
        return true;
    }

}