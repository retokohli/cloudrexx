<?php

namespace Cx\Core_Modules\Listing\Model\Entity;

class DataSetException extends \Exception {}

class DataSet implements \Iterator {
	protected $data = array();
	
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
					$convertedData[$key] = $this->convertObject($value);
				} else {
					throw new DataSetException('Supplied argument could not be converted to DataSet');
				}
			}
		} else if (is_object($data)) {
			$convertedData[0] = $this->convertObject($data);
		} else {
			throw new DataSetException('Supplied argument could not be converted to DataSet');
		}
		return $convertedData;
	}
	
	protected function convertObject($object) {
		$data = array();
		if ($object instanceof \Cx\Model\Base\EntityBase) {
			$em = \Env::get('em');
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
}
