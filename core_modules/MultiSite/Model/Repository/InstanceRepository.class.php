<?php
namespace Cx\Modules\MultiSite\Model\Repository;

class InstanceRepository {
	
	public function findAll($basepath) {
		$instances = array();
		$dh = opendir($basepath);
		while ($file = readdir($dh)) {
			if (!is_dir($basepath . '/' . $file)) {
				continue;
			}
			try {
				$instances[] = new \Cx\Modules\MultiSite\Model\Entity\Instance($basepath, $file);
			} catch (\Cx\Modules\MultiSite\Model\Entity\InstanceException $e) {
				continue;
			}
		}
		closedir($dh);
		return $instances;
	}
}
