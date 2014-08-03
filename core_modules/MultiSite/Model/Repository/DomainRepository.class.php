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
    public function exportDomainAndWebsite() {
        $filePath = \Env::get('cx')->getWebsiteDocumentRootPath() . '/core_modules/MultiSite/Data';
        $objDomains = $this->findAll();
        $websiteDomainContent = array();

        \Cx\Core\Setting\Controller\Setting::init('MultiSite', '','FileSystem');
        $websitePath = \Cx\Core\Setting\Controller\Setting::getValue('websitePath');
        $websiteOffsetPath = substr($websitePath, strlen(\Env::get('cx')->getWebsiteDocumentRootPath()));
        foreach ($objDomains As $objDomain) {
            $domainName = $objDomain->getName();
            $websiteName = $objDomain->getWebsite()->getName();
            $websiteDomainContent[] = "$domainName\t$websiteOffsetPath/$websiteName";
        }

        // In case the MultiSite system is running in hybrid-mode, then the FQDN and BaseDN
        // are the same. Therefore, we shall remove those duplicates.
        $websiteDomainContent = array_unique($websiteDomainContent);

        try {
            $data = join("\n", $websiteDomainContent);
            $objFile = new \Cx\Lib\FileSystem\File($filePath.'/WebsiteDomainContentMap.txt');
            $objFile->write($data);
        } catch (\Cx\Lib\FileSystem\FileSystemException $e) {
            \DBG::msg($e->getMessage());
        }
    }
}

