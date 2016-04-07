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
        $cx = \Cx\Core\Core\Controller\Cx::instanciate();
        if (!in_array($cx->getMode(), array(\Cx\Core_Modules\MultiSite\Controller\ComponentController::MODE_HYBRID, \Cx\Core_Modules\MultiSite\Controller\ComponentController::MODE_SERVICE))) {
            return;
        }

        $filePath   = $cx->getWebsiteDocumentRootPath() . '/core_modules/MultiSite/Data';
        $objDomains = $this->findAll();
        $websiteDomainContent = $codeBaseRepositoryContent = $websiteDomainServerMapContent = array();
        \Cx\Core\Setting\Controller\Setting::init('MultiSite', '','FileSystem');
        $websitePath = \Cx\Core\Setting\Controller\Setting::getValue('websitePath','MultiSite');
        $websiteOffsetPath = substr($websitePath, strlen($cx->getWebsiteDocumentRootPath()));
        $codeBaseRepositoryPath = \Cx\Core\Setting\Controller\Setting::getValue('codeBaseRepository','MultiSite');
        $codeBaseRepositoryOffsetPath = substr($codeBaseRepositoryPath, strlen($cx->getCodeBaseDocumentRootPath()));
        foreach ($objDomains As $objDomain) {
            if ($objDomain->getWebsite() instanceof \Cx\Core_Modules\MultiSite\Model\Entity\Website) {
                $domainName                     = $objDomain->getName();
                $websiteName                    = $objDomain->getWebsite()->getName();
                $codeBaseName                   = $objDomain->getWebsite()->getCodeBase();
                $websiteMode                    = $objDomain->getWebsite()->getWebsiteMode();
                $websiteDomainContent[]         = "$domainName\t$websiteOffsetPath/$websiteName";
                if (!empty($codeBaseName)) {
                    $codeBaseRepositoryContent[] = "$domainName\t$codeBaseRepositoryOffsetPath/".$codeBaseName;
                }
                if (   $websiteMode == \Cx\Core_Modules\MultiSite\Controller\ComponentController::WEBSITE_MODE_CLIENT
                    && $objDomain->getWebsite()->getServerWebsite()
                ) {
                    $websiteDomainServerMapContent[] = "$domainName\t$websiteOffsetPath/{$objDomain->getWebsite()->getServerWebsite()->getName()}";
                }
            }
        }
        // In case the MultiSite system is running in hybrid-mode, then the FQDN and BaseDN
        // are the same. Therefore, we shall remove those duplicates.
        
        $websiteDomainContent      = array_unique($websiteDomainContent);
        $codeBaseRepositoryContent = array_unique($codeBaseRepositoryContent);
        $websiteDomainServerMapContent = array_unique($websiteDomainServerMapContent);
        $websiteDomainMap          = array(
                                        'WebsiteDomainContentMap.txt'  => $websiteDomainContent,
                                        'WebsiteDomainCodeBaseMap.txt' => $codeBaseRepositoryContent,
                                        'WebsiteDomainServerMap.txt'   => $websiteDomainServerMapContent,
                                    );
            
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

