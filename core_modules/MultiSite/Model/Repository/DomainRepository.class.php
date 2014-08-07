<?php

/**
 * Class DomainRepository
 *
 * @copyright   CONTREXX CMS - COMVATION AG
 * @author      ss4u <ss4ugroup@gmail.com>
 * @package     contrexx
 * @subpackage  coremodule_multisite
 */

namespace Cx\Core_Modules\MultiSite\Model\Repository;

/**
 * Class DomainRepository
 * 
 * @copyright   CONTREXX CMS - COMVATION AG
 * @author      ss4u <ss4ugroup@gmail.com>
 * @package     contrexx
 * @subpackage  coremodule_multisite
 */
class DomainRepository extends \Doctrine\ORM\EntityRepository {
    
    /**
     * Export Domain and Website ContentMap
     * 
     */
    public function exportDomainAndWebsite() {
        $filePath = \Env::get('cx')->getWebsiteDocumentRootPath() . '/core_modules/MultiSite/Data';
        $objDomains = $this->findAll();
        $websiteDomainContent = $codeBaseRepositoryContent = array();
        \Cx\Core\Setting\Controller\Setting::init('MultiSite', '','FileSystem');
        $websitePath = \Cx\Core\Setting\Controller\Setting::getValue('websitePath');
        $websiteOffsetPath = substr($websitePath, strlen(\Env::get('cx')->getWebsiteDocumentRootPath()));
        $codeBaseRepositoryPath = \Cx\Core\Setting\Controller\Setting::getValue('codeBaseRepository');
        $codeBaseRepositoryOffsetPath = substr($codeBaseRepositoryPath, strlen(\Env::get('cx')->getCodeBaseDocumentRootPath()));
        foreach ($objDomains As $objDomain) {
            $domainName                     = $objDomain->getName();
            $websiteName                    = $objDomain->getWebsite()->getName();
            $codeBaseName                   = $objDomain->getWebsite()->getCodeBase();
            $websiteDomainContent[]         = "$domainName\t$websiteOffsetPath/$websiteName";
            if (!empty($codeBaseName)) {
               $codeBaseRepositoryContent[] = "$domainName\t$codeBaseRepositoryOffsetPath/".$codeBaseName;
            }                             
        }
        // In case the MultiSite system is running in hybrid-mode, then the FQDN and BaseDN
        // are the same. Therefore, we shall remove those duplicates.
        
        $websiteDomainContent      = array_unique($websiteDomainContent);
        $codeBaseRepositoryContent = array_unique($codeBaseRepositoryContent);
        $websiteDomainMap['WebsiteDomainContentMap.txt'] = $websiteDomainContent;
        
        if(!empty($codeBaseRepositoryContent)){
              $websiteDomainMap['WebsiteDomainCodeBaseMap.txt'] = $codeBaseRepositoryContent;
        }    

        foreach ($websiteDomainMap as $key => $value) {
            try {
                $content = join("\n", $value);
                $objFile = new \Cx\Lib\FileSystem\File($filePath.'/'.$key);
                $objFile->write($content);
            } catch (\Cx\Lib\FileSystem\FileSystemException $e) {
                \DBG::msg($e->getMessage());
            }
        }
    }
}

