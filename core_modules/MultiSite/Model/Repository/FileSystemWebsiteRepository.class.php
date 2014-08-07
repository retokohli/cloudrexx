<?php
namespace Cx\Core_Modules\MultiSite\Model\Repository;

class FileSystemWebsiteRepository {
    protected $websites = array();
    
    public function findAll($basepath) {
        
        if (isset($this->websites[$basepath])) {
            return $this->websites[$basepath];
        }
        
        $websites = array();
        $dh = opendir($basepath);
        while ($file = readdir($dh)) {
            if (substr($file, 0, 1) == '.') {
                continue;
            }
            if (!is_dir($basepath . '/' . $file)) {
                continue;
            }
            try {
                $websites[$file] = new \Cx\Core_Modules\MultiSite\Model\Entity\Website($basepath, $file);
                $websites[$file]->setName($file);
            } catch (\Cx\Core_Modules\MultiSite\Model\Entity\WebsiteException $e) {
                //echo $e->getMessage() . '<br />';
                continue;
            }
        }
        closedir($dh);
        $this->websites[$basepath] = $websites;
        return $websites;
    }
    
    public function findByCreatedDateRange($basepath, $startTime, $endTime) {
        $websites = $this->findAll($basepath);
        
        // flip start and end if start is bigger than end
        if ($startTime > $endTime) {
            list($startTime, $endTime) = array($endTime, $startTime);
        }
        
        if (is_int($startTime)) {
            $startTime = new \DateTime('@' . $startTime);
        }
        if (is_int($endTime)) {
            $endTime = new \DateTime('@' . $endTime);
        }
        
        $matchingWebsites = array();
        foreach ($websites as $website) {
            if (
                $website->createdAt >= $startTime &&
                $website->createdAt <= $endTime
            ) {
                $matchingWebsites[$website->getName()] = $website;
            }
        }
        return $matchingWebsites;
    }
    
    public function findByMail($basepath, $mail) {
        foreach ($this->findAll($basepath) as $website) {
            if ($website->getEmail() == $mail) {
                return $website;
            }
        }
        return null;
    }

    public function findByName($basePath, $name) {
        if (empty($name)) {
            return null;
        }

        if (isset($this->websites[$basePath][$name])) {
            return $this->websites[$basePath][$name];
        }
        
        try {
            $this->websites[$basePath][$name] = \Cx\Core_Modules\MultiSite\Model\Entity\Website::loadFromFileSystem($basePath, $name);
            return $this->websites[$basePath][$name];
        } catch (\Cx\Core_Modules\MultiSite\Model\Entity\WebsiteException $e) {
            //echo $e->getMessage() . '<br />';
            return null;
        }

        return null;
    }
    
    public function findWebsitesBetween($basepath, $startTime, $endTime) {
        return count($this->findByCreatedDateRange($basepath, $startTime, $endTime));
    }
    
    public function findByDomain($basePath, $name) {
        
        $domainContent = file_get_contents(\Env::get('cx')->getWebsiteDocumentRootPath() . '/core_modules/MultiSite/Data/WebsiteDomainContentMap.txt');
        
        try{
            $domainNameValues = explode("\n", $domainContent);
            foreach ($domainNameValues as $domainValue) {
                $domainName = explode("\t", $domainValue);
                if ($name == $domainName[0]) {
                    $websitePath = explode("/", $domainName[1]);
                    return $this->findByName($basePath, end($websitePath));
                }
            }
        } catch (\Cx\Core_Modules\MultiSite\Model\Entity\WebsiteException $e) {
            return null;
        }
    }
}
