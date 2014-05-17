<?php
/**
 * Meta info about the component
 *
 * @copyright   Comvation AG
 * @author      Michael Ritter <michael.ritter@comvation.com>
 * @author      ss4u <ss4u.comvation@gmail.com>
 * @package     contrexx
 * @subpackage  core_core
 * @version     3.1.0
 */

namespace Cx\Core\Core\Model\Entity;
use \Symfony\Component\Yaml\Yaml as Yaml;

/**
 * MetaComponentException
 *
 * @copyright   Comvation AG
 * @author      Michael Ritter <michael.ritter@comvation.com>
 * @author      ss4u <ss4u.comvation@gmail.com>
 * @package     contrexx
 * @subpackage  core_core
 * @version     3.1.0
 */
class MetaComponentException extends \Exception {}

/**
 * Meta info about the component load or get
 *
 * @copyright   Comvation AG
 * @author      Michael Ritter <michael.ritter@comvation.com>
 * @author      ss4u <ss4u.comvation@gmail.com>
 * @package     contrexx
 * @subpackage  core_core
 * @version     3.1.0
 */
class MetaComponent {
    
    /**
     * Component name
     * 
     * @var string
     */
    private $componentName;
    
    /**
     * Component Type
     * 
     * @var string
     */
    private $componentType;
    
    /**
     * Component Publisher
     * 
     * @var string
     */
    private $componentPublisher;
    
    /**
     * Component dependencies
     * 
     * @var array
     */
    private $dependencies = array();
    
    /**
     * Component Versions
     *
     * @var array
     */
    private $versions = array();
    
    /**
     * Component Rating
     *
     * @var integer
     */
    private $rating;
    
    /**
     * Downloads count
     *
     * @var integer
     */
    private $downloads;
    
    /**
     * Price
     *
     * @var mixed
     */
    private $price;
    
    /**
     *
     * @var mixed
     */
    private $pricePer;

    /**
     * create Meta info object of Component
     * 
     * @param string $componentName Component Name
     * @param string $componentType Component Type
     */
    function __construct($componentName, $componentType) {
        $this->componentName = $componentName;
        $this->componentType = $componentType;
    }
    
    /**
     * Write the current component info to a specified file
     * 
     * @param string $path Path to the file 
     */
    public function writeToFile($path) {
        
        $content = array(
            'DlcInfo' => array(
                 'name' => $this->getComponentName(),
                 'type' => $this->getComponentType(),
                 'publisher' => $this->getComponentPublisher(),
                 'dependencies' => $this->getDependencies(),
                 'versions' => $this->getVersions(),              
                 'rating' => $this->getRating(),
                 'downloads' => $this->getDownloads(),
                 'price' => $this->getPrice(),
                 'pricePer' => $this->getPricePer(),
            )
        );
        
        try {
            $file = new \Cx\Lib\FileSystem\File($path);
            $file->touch();
            $file->write(
                    Yaml::dump($content, 3)
            );
        } catch (\Cx\Lib\FileSystem\FileSystemException $e) {
            \DBG::msg($e->getMessage());
        }        
    }
    
    /********************************/
    /*   Get/Set Implementation    */
    /********************************/
    
    public function getComponentName() {
        return $this->componentName;
    }

    public function setComponentName($componentName) {
        $this->componentName = $componentName;
    }

    public function getComponentType() {
        return $this->componentType;
    }

    public function setComponentType($componentType) {
        $this->componentType = $componentType;
    }

    public function getComponentPublisher() {
        return $this->componentPublisher;
    }

    public function setComponentPublisher($componentPublisher) {
        $this->componentPublisher = $componentPublisher;
    }

    public function getDependencies() {
        if (!empty($this->dependencies)) {
            return $this->dependencies;
        } else {
            return null;
        }        
    }

    public function setDependencies($dependency) {
        $this->dependencies[] = $dependency;
    }

    public function getVersions() {
        if (!empty($this->versions)) {
            return $this->versions;
        } else {
            return null;
        }
        
    }

    public function setVersions($version) {
        $this->versions[] = $version;
    }

    public function getRating() {
        return $this->rating;
    }

    public function setRating($rating) {
        $this->rating = $rating;
    }

    public function getDownloads() {
        return $this->downloads;
    }

    public function setDownloads($downloads) {
        $this->downloads = $downloads;
    }

    public function getPrice() {
        return $this->price;
    }

    public function setPrice($price) {
        $this->price = $price;
    }

    public function getPricePer() {
        return $this->pricePer;
    }

    public function setPricePer($pricePer) {
        $this->pricePer = $pricePer;
    }

}