<?php

namespace Cx\Core_Modules\TemplateEditor\Model;

use Symfony\Component\Yaml\Exception;
use Symfony\Component\Yaml\Yaml;

/**
 * Class FileStorage
 *
 * @copyright   CONTREXX CMS - COMVATION AG
 * @author      Robin Glauser <robin.glauser@comvation.com>
 * @package     contrexx
 * @subpackage  core_module_templateeditor
 */
class OptionSetFileStorage implements Storable
{
    /**
     * @var String
     */
    protected $path;


    /**
     * @param String $path
     */
    public function __construct($path)
    {
        $this->path = $path;
    }

    /**
     * @param String $name
     *
     * @return array
     */
    public function retrieve($name)
    {
        $file = file_get_contents(
            $this->path
            . '/' . $name . '/options/options.yml'
        );
        if ($file) {
            return Yaml::load($file);
        } else {
            return [];
        }
    }

    /**
     * @param                  $name
     * @param YamlSerializable $data
     *
     * @return bool
     */
    public function persist($name, YamlSerializable $data)
    {
        return file_put_contents(
            $this->path
            . '/' . $name . '/options/options.yml',
            Yaml::dump($data->yamlSerialize(), 5)
        );
    }

    /**
     * @return array
     */
    public function getList()
    {
        return array_filter(glob($this->path . '/'), 'is_dir');
    }

    /**
     * @param $name
     */
    public function remove($name){}
}