<?php
/**
 * @copyright   Comvation AG
 * @author      Robin Glauser <robin.glauser@comvation.com>
 * @package     contrexx
 */

namespace Cx\Core_Modules\TemplateEditor\Model;

use Symfony\Component\Yaml\Yaml;

class PresetFileStorage implements Storable
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
     * @throws PresetRepositoryException
     */
    public function retrieve($name)
    {
        if (!file_exists($this->path . '/options/presets/' . $name . '.yml')){
            throw new PresetRepositoryException('Preset '.$name.' not found.');
        }
        $file = file_get_contents(
            $this->path . '/options/presets/' . $name . '.yml'
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
            $this->path . '/options/presets/' . $name . '.yml',
            Yaml::dump($data->yamlSerialize(), 5)
        );
    }

    /**
     * @return array
     */
    public function getList()
    {
        $list=  array_filter(
            array_filter(glob($this->path . '/options/presets/*'), 'is_file'),
            function (&$name) {
                return $name = pathinfo($name, PATHINFO_FILENAME);
            }
        );
        // Move Default to first place
        $key = array_search('Default', $list);
        $new_value = $list[$key];
        unset($list[$key]);
        array_unshift($list, $new_value);
        return $list;
    }

    /**
     * @param $name
     */
    public function remove($name)
    {
        \Cx\Lib\FileSystem\FileSystem::delete_file($this->path . '/options/presets/' . $name . '.yml');
    }
}

class PresetRepositoryException extends \Exception {

}