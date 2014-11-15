<?php
/**
 * Contrexx
 *
 * @link      http://www.contrexx.com
 * @copyright Comvation AG 2007-2014
 * @version   Contrexx 4.0
 * 
 * According to our dual licensing model, this program can be used either
 * under the terms of the GNU Affero General Public License, version 3,
 * or under a proprietary license.
 *
 * The texts of the GNU Affero General Public License with an additional
 * permission and of our proprietary license can be found at and
 * in the LICENSE file you have received along with this program.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU Affero General Public License for more details.
 *
 * "Contrexx" is a registered trademark of Comvation AG.
 * The licensing of the program under the AGPLv3 does not imply a
 * trademark license. Therefore any rights, title and interest in
 * our trademarks remain entirely with us.
 */

/**
 * Data Set
 *
 * @copyright   CONTREXX CMS - COMVATION AG
 * @author      COMVATION Development Team <info@comvation.com>
 * @package     contrexx
 * @subpackage  core_module_listing
 */

namespace Cx\Core_Modules\Listing\Model\Entity;

/**
 * Data Set Exception
 *
 * @copyright   CONTREXX CMS - COMVATION AG
 * @author      COMVATION Development Team <info@comvation.com>
 * @package     contrexx
 * @subpackage  core_module_listing
 */

class DataSetException extends \Exception {}

/**
 * Data Set
 *
 * @copyright   CONTREXX CMS - COMVATION AG
 * @author      COMVATION Development Team <info@comvation.com>
 * @package     contrexx
 * @subpackage  core_module_listing
 */

class DataSet implements \Iterator {
    protected static $yamlInterface = null;
    protected $data = array();
    protected $dataType = 'array';

    public function __construct($data, callable $converter = null) {
        if (is_callable($converter)) {
            $this->data = $converter($data);
        } else {
            $this->data = $this->convert($data);
        }
    }

    protected function convert($data) {
        $convertedData = array();
        if (is_array($data)) {
            foreach ($data as $key=>$value) {
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
            foreach ($em->getClassMetadata(get_class($object))->getColumnNames() as $field) {
                $value = $em->getClassMetadata(get_class($object))->getFieldValue($object, $field);
                if ($value instanceof \DateTime) {
                    $value = $value->format('d.M.Y H:i:s');
                }
                $data[$field] = $value;
            }
            return $data;
        }
        foreach ($object as $attribute=>$property) {
            $data[$attribute] = $property;
        }
        return $data;
    }

    protected static function getYamlInterface() {
        if (self::$yamlInterface) {
            self::$yamlInterface = new \Cx\Core_Modules\Listing\Model\YamlInterface();
        }
        return self::$yamlInterface;
    }

    public function toYaml() {
        return $this->export(self::getYamlInterface());
    }

    public static function import(Cx\Core_Modules\Listing\Model\Importable $importInterface, $content) {
        return new static($importInterface->import($content));
    }

    /**
     *
     * @param Cx\Core_Modules\Listing\Model\ImportInterface $importInterface
     * @param type $filename
     * @throws \Cx\Lib\FileSystem\FileSystemException
     * @return type 
     */
    public static function importFromFile(Cx\Core_Modules\Listing\Model\Importable $importInterface, $filename) {
        $objFile = new \Cx\Lib\FileSystem\File($filename);
        return self::import($importInterface, $objFile->getData());
    }

    public function export(Cx\Core_Modules\Listing\Model\Exportable $exportInterface) {
        return $exportInterface->export($this);
    }

    /**
     *
     * @param Cx\Core_Modules\Listing\Model\ExportInterface $exportInterface
     * @param type $filename 
     * @throws \Cx\Lib\FileSystem\FileSystemException
     */
    public function exportToFile(Cx\Core_Modules\Listing\Model\Exportable $exportInterface, $filename) {
        $objFile = new \Cx\Lib\FileSystem\File($filename);
        $objFile->write($this->export($exportInterface));
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
    
    public function length() {
        return count($this->data);
    }
    
    public function count() {
        return $this->length();
    }
    
    public function size() {
        return $this->length();
    }
}
