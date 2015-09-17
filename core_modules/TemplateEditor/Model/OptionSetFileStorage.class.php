<?php

namespace Cx\Core_Modules\TemplateEditor\Model;

use Symfony\Component\Yaml\Exception;
use Symfony\Component\Yaml\Parser;
use Symfony\Component\Yaml\ParserException;
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
     * @throws ParserException
     */
    public function retrieve($name)
    {
        $file = file_get_contents(
            \Cx\Core\Core\Controller\Cx::instanciate()->getClassLoader()->getFilePath($this->path
                . '/' . $name . '/options/options.yml')
        );
        if ($file) {
            try {
                $yaml = new Parser();
                return $yaml->parse($file);
            }
            catch (ParserException $e){
                preg_match("/line (?P<line>[0-9]+)/",$e->getMessage(),$matches);
                throw new ParserException($e->getMessage(), $matches['line']);
            }
        } else {
            throw new ParserException("File".       $this->path
                . '/' . $name . '/options/options.yml not found');
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
        mkdir($this->path . '/' . $name );
        mkdir($this->path . '/' . $name .'/options');
        return file_put_contents(
            $this->path
            . '/' . $name . '/options/options.yml',
            Yaml::dump($data->yamlSerialize(), 5));
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