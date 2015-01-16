<?php
/**
 * Data Set
 *
 * @copyright   CONTREXX CMS - COMVATION AG
 * @author      COMVATION Development Team <info@comvation.com>
 * @package     contrexx
 * @subpackage  coremodule_listing
 */

namespace Cx\Core_Modules\Listing\Model\Entity;

/**
 * Data Set Exception
 *
 * @copyright   CONTREXX CMS - COMVATION AG
 * @author      COMVATION Development Team <info@comvation.com>
 * @package     contrexx
 * @subpackage  coremodule_listing
 */
class DataSetException extends \Exception {}

/**
 * Data Set
 *
 * @copyright   CONTREXX CMS - COMVATION AG
 * @author      COMVATION Development Team <info@comvation.com>
 * @package     contrexx
 * @subpackage  coremodule_listing
 */
class DataSet implements \Iterator {
    protected static $yamlInterface = null;
    protected $data = array();
    protected $dataType = 'array';

// TODO: DataSet must be extended, that it can handle objects
    public function __construct($data = array(), callable $converter = null) {
        if (!count($data)) {
            return;
        }
        if (is_callable($converter)) {
            $this->data = $converter($data);
        } else {
            $this->data = $this->convert($data);
        }
    }
    
    /**
     * Set data-attribute $key to $value
     */
    public function add($key, $value, &$convertedData = null) {
        if ($convertedData === null) {
            $convertedData = &$this->data;
        }
        if (is_array($value)) {
            foreach ($value as $attribute=>$property) {
                $convertedData[$key][$attribute] = $property;
            }
        } else if (is_object($value)) {
            if ($this->dataType == 'array') {
                $this->dataType = get_class($value);
            }
            $convertedObject = $this->convertObject($value, $key);
            $convertedData[$key] = $convertedObject;
        } else {
             throw new DataSetException('Supplied argument could not be converted to DataSet');
        }
    }

    /**
     * Appends the data of passed $dataSet to the current object and rewinds it
     * @param \Cx\Core_Modules\Listing\Model\Entity\DataSet $dataSet DataSet of which its data should be appended
     */
    public function join(\Cx\Core_Modules\Listing\Model\Entity\DataSet $dataSet) {
        foreach ($dataSet as $data) {
            $this->data[] = $data;
        }
        $this->rewind();
    }

    protected function convert($data) {
        $convertedData = array();
        if (is_array($data)) {
            foreach ($data as $key=>$value) {
                $this->add($key, $value, $convertedData);
            }
        } else if (is_object($data)) {
            $this->dataType = get_class($data);
            $convertedData = $this->convertObject($data);
        } else {
            throw new DataSetException('Supplied argument could not be converted to DataSet');
        }
        return $convertedData;
    }

    protected function convertObject($object, &$key) {
        $data = array();
        if ($object instanceof \Cx\Model\Base\EntityBase) {
            $em = \Env::get('em');
            $identifiers = $em->getClassMetadata(get_class($object))->getIdentifierValues($object);
            if (is_array($identifiers)) {
                $identifiers = implode('/', $identifiers);
            }
            $key = $identifiers;
            foreach ($em->getClassMetadata(get_class($object))->getColumnNames() as $column) {
                $field = $em->getClassMetadata(get_class($object))->getFieldName($column);
                $value = $em->getClassMetadata(get_class($object))->getFieldValue($object, $field);
                if ($value instanceof \DateTime) {
                    $value = $value->format('d.M.Y H:i:s');
                } elseif (is_array($value)) {
                    $value = serialize($value);
                }
                $data[$field] = $value;
            }
            $associationMappings = $em->getClassMetadata(get_class($object))->getAssociationMappings();
            foreach ($associationMappings as $field => $associationMapping) {
                $classMethods = get_class_methods($object);
                $methodNameToFetchAssociation = 'get'.ucfirst($field);
                if (in_array($methodNameToFetchAssociation, $classMethods)) {
                    $data[$field] = $object->$methodNameToFetchAssociation();
                }
            }
            $data['virtual'] = $object->isVirtual();
            return $data;
        }
       foreach ($object as $attribute => $property) {
            if (is_object($property)) {
                $data[$attribute] = $this->convertObject($property, $key);
            } else {
                $data[$attribute] = $property;
            }
        } 
        return $data;
    }
    
    protected static function getYamlInterface() {
        if (empty(self::$yamlInterface)) {
            self::$yamlInterface = new \Cx\Core_Modules\Listing\Model\Entity\YamlInterface();
        }
        return self::$yamlInterface;
    }

    public function toYaml() {
        return $this->export(self::getYamlInterface());
    }

    public static function import(\Cx\Core_Modules\Listing\Model\Entity\Importable $importInterface, $content) {
        try {
            return new static($importInterface->import($content));
        } catch (\Exception $e) {
            \DBG::msg($e->getMessage());
            throw new DataSetException('Importing data through supplied interface failed!');
        }
    }

    /**
     *
     * @param Cx\Core_Modules\Listing\Model\ImportInterface $importInterface
     * @param type $filename
     * @throws \Cx\Lib\FileSystem\FileSystemException
     * @return type 
     */
    public static function importFromFile(\Cx\Core_Modules\Listing\Model\Entity\Importable $importInterface, $filename) {
        try {
            $objFile = new \Cx\Lib\FileSystem\File($filename);
            return self::import($importInterface, $objFile->getData());
        } catch (\Cx\Lib\FileSystem\FileSystemException $e) {
            \DBG::msg($e->getMessage());
            throw new DataSetException("Failed to load data from file $filename!");
        }
    }

    public function export(\Cx\Core_Modules\Listing\Model\Entity\Exportable $exportInterface) {
        try {
            return $exportInterface->export($this->data);
        } catch (\Exception $e) {
            \DBG::msg($e->getMessage());
            throw new DataSetException('Exporting data to selected interface failed!');
        }
    }

    /**
     *
     * @param Cx\Core_Modules\Listing\Model\ExportInterface $exportInterface
     * @param type $filename 
     * @throws \Cx\Lib\FileSystem\FileSystemException
     */
    public function exportToFile(\Cx\Core_Modules\Listing\Model\Entity\Exportable $exportInterface, $filename) {
        try {
            $objFile = new \Cx\Lib\FileSystem\File($filename);
            $objFile->touch();
            $objFile->write($this->export($exportInterface));
        } catch (\Cx\Lib\FileSystem\FileSystemException $e) {
            \DBG::msg($e->getMessage());
            throw new DataSetException("Failed to export data to file $filename!");
        }
    }

    /**
     *
     * @param type $filename 
     * @throws \Cx\Lib\FileSystem\FileSystemException
     */
    public function save($filename) {
        $this->exportToFile($this->getYamlInterface(), $filename);
    }

    public static function fromYaml($data) {
        return self::import(self::getYamlInterface(), $data);
    }

    /**
     *
     * @param type $filename
     * @throws \Cx\Lib\FileSystem\FileSystemException
     * @return type 
     */
    public static function load($filename) {
        return self::importFromFile(self::getYamlInterface(), $filename);
    }
    
    public function getDataType() {
        return $this->dataType;
    }
    
    public function entryExists($key) {
        return isset($this->data[$key]);
    }
    
    public function getEntry($key) {
        if (!$this->entryExists($key)) {
            throw new DataSetException('No such entry');
        }
        return $this->data[$key];
    }
    
    public function toArray() {
        if (count($this->data) == 1) {
            return current($this->data);
        }
        return $this->data;
    }

    public function current() {
        return current($this->data);
    }

    public function next() {
        return next($this->data);
    }

    public function key() {
        return key($this->data);
    }

    public function valid() {
        return current($this->data);
    }

    public function rewind() {
        return reset($this->data);
    }
    
    public function count() {
        return $this->size();
    }
    
    public function length() {
        return $this->size();
    }
    
    public function size() {
        return count($this->data);
    }
    
    public function limit($length, $offset) {
        $i = 0;
        $result = new static();
        foreach ($this as $key=>$value) {
            if ($i < $offset || $i >= ($offset + $length)) {
                $i++;
                continue;
            }
            $result->add($key, $value);
            $i++;
        }
        return $result;
    }
    
    /**
     * Sort this DataSet by the fields and in the order specified
     * 
     * $order has the following syntax:
     * array(
     *     {fieldname} => SORT_ASC|SORT_DESC,
     *     [{fieldname2} => SORT_ASC|SORT_DESC,
     *     [...]]
     * )
     * @todo Cleanup and efficiency
     * @todo Allow sorting by multiple fields (not possible yet)
     * @param array $order Fields and order to sort
     * @return DataSet Sorted DataSet (new instance)
     */
    public function sort($order) {
        $sortField = key($order);
        $sortOrder = current($order);
        //echo 'Sorting ' . $sortField . ' by ' . $sortOrder;
        
        $data = $this->toArray();
        
        // Obtain a list of columns (this could be done more efficient)
        $colData = $this->flip($data);
        if (!isset($colData[$sortField])) {
            return clone $this;
        }
        // Sort the data with volume descending, edition ascending
        // Add $data as the last parameter, to sort by the common key
        array_multisort($colData[$sortField], $sortOrder, $data);
        
        return new static($data);
    }
    
    /**
     * Tell if the supplied argument is iterable
     * @todo Rethink this method, DataSet is always iterable, this is a general helper method
     * @param mixed $var Variable to test iterability
     * @return boolean True if $var is iterable, false otherwise
     */
    private function is_iterable($var) {
        return (is_array($var) || $var instanceof Traversable || $var instanceof stdClass);
    }
    
    /**
     * Flips an 2 dimensional array
     * @todo Rethink this method, may return a new DataSet instance and remove param
     * @todo Does not work with only one entry
     * @param array $arr Array to flip
     * @return array Flipped array
     */
    private function flip($arr) {
        $out = array();
        foreach ($arr as $key => $subarr) {
        if (!$this->is_iterable($subarr)) {
            $out[0][$key] = $subarr;
            continue;
        }
            foreach ($subarr as $subkey => $subvalue) {
                 $out[$subkey][$key] = $subvalue;
            }
        }

        return $out;
    }
}
