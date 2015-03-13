<?php

namespace Cx\Core_Modules\TemplateEditor\Model;

use Cx\Core\Core\Controller\Cx;
use Symfony\Component\Yaml\Exception;
use Symfony\Component\Yaml\Yaml;

/**
 *
 */
class FileStorage implements Storable
{
    /**
     * @var String
     */
    private $path;


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
     * @throws ComponentsFileNotFound
     */
    public function retrieve($name)
    {
        $file = file_get_contents(
            $this->path
            . '/' . $name . '/component.yml'
        );
        if ($file){
            return Yaml::load($file);
        }
        else {
            throw new ComponentsFileNotFound('Can\'t open file '. $this->path
                . '/' . $name . '/component.yml');
        }
    }

    /**
     * @param                  $name
     * @param YamlSerializable $data
     *
     * @return int
     */
    public function persist($name, YamlSerializable $data)
    {
        return file_put_contents($this->path
            . '/' . $name . '/component.yml',Yaml::dump($data->yamlSerialize(),5));
    }
}

class ComponentsFileNotFound extends Exception {}