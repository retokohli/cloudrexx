<?php
/**
 * WebsiteRepository
 *
 * @copyright   Comvation AG
 * @author      Project Team SS4U <info@comvation.com>
 * @package     contrexx
 * @subpackage  coremodule_multisite
 */

namespace Cx\Core_Modules\MultiSite\Model\Repository;

/**
 * WebsiteRepositoryException
 *
 * @copyright   Comvation AG
 * @author      COMVATION Development Team <info@comvation.com>
 * @package     contrexx
 * @subpackage  coremodule_multisite
 */
class WebsiteRepositoryException extends \Exception {}

/**
 * WebsiteRepository
 *
 * @copyright   Comvation AG
 * @author      COMVATION Development Team <info@comvation.com>
 * @package     contrexx
 * @subpackage  coremodule_multisite
 */
class WebsiteRepository extends \Doctrine\ORM\EntityRepository {
    protected $websites = array();
    
    public function findByCreatedDateRange($startTime, $endTime) {
        
    }
    
    public function findByMail($mail) {
        if (empty($mail)) {
            return null;
        }
        foreach ($this->findAll() as $website) {
            if ($website->getOwner()->getEmail() == $mail) {
                return $website;
            }
        }
        return null;
    }
    
    public function findByName($name) {
        if (empty($name)) {
            return null;
        }
        
        $website = $this->findBy(array('name' => $name));
        if (count($website)>0) {
            return $website;
        } else {
            return null;
        }
    }
    
    public function findWebsitesBetween($startTime, $endTime) {
        
    }
    
    public function findOneForSale($productOptions, $saleOptions) {
        
        $baseSubscription  = isset($saleOptions['baseSubscription']) ? $saleOptions['baseSubscription'] : '';
        if ($baseSubscription instanceof \Cx\Modules\Order\Model\Entity\Subscription) {
            $productEntity = $baseSubscription->getProductEntity();
            if ($productEntity instanceof \Cx\Core_Modules\MultiSite\Model\Entity\Website) {
                \Env::get('em')->remove($baseSubscription);
                return $productEntity;
            }
            throw new WebsiteRepositoryException('There is no product entity exists in the base subscription.');
        }
        
        $websiteThemeId = isset($saleOptions['themeId']) ? $saleOptions['themeId'] : null;
        $website = $this->initWebsite($saleOptions['websiteName'], $saleOptions['customer'], $websiteThemeId);
        
        \Env::get('em')->persist($website);
        // flush $website to database -> subscription will need the ID of $website
        // to properly work
        \Env::get('em')->flush();
        return $website;
    }
    
    public function findWebsitesByCriteria($criteria = array()) {
        if (empty($criteria)) {
            return;
        }
        
        $qb = $this->getEntityManager()->createQueryBuilder();
        $qb->select('website')
                ->from('\Cx\Core_Modules\MultiSite\Model\Entity\Website', 'website')
                ->leftJoin('website.owner', 'user');
        
        $filterPos = 1;
        foreach ($criteria as $fieldName => $fieldValue) {
            if (empty($fieldValue)) {
                continue;
            }
            $method = ($filterPos == 1) ? 'where' : 'andWhere';
            $qb->$method($fieldName . ' = ?' . $filterPos)->setParameter($filterPos, $fieldValue);
            $filterPos++;
        }
        
        return $qb->getQuery()->getResult();
    }
    
    public function findWebsitesBySearchTerms($term) {
        $qb = $this->getEntityManager()->createQueryBuilder();
        $qb->select('website')
                ->from('\Cx\Core_Modules\MultiSite\Model\Entity\Website', 'website')
                ->leftJoin('website.owner', 'user')
                ->leftJoin('website.domains', 'domain')
                ->where('website.id = :id')->setParameter('id', $term)
                ->orWhere('user.email LIKE ?1')
                ->orWhere('website.name LIKE ?1')
                ->orWhere('website.ftpUser LIKE ?1')
                ->orWhere('domain.name LIKE ?1')
                ->setParameter(1, '%' . $term . '%')
                ->groupBy('website.id');
        
        return $qb->getQuery()->getResult();
    }
    
    /**
     * Initializing the website
     * 
     * @param string $websiteName
     * @param \User $objUser
     * @param integer $websiteThemeId
     * @return \Cx\Core_Modules\MultiSite\Model\Entity\Website
     */
    public function initWebsite($websiteName = '', \User $objUser = null, $websiteThemeId = 0) {
        if (empty($websiteName)) {
            return;
        }
        
        $basepath = \Cx\Core\Setting\Controller\Setting::getValue('websitePath','MultiSite');
        $websiteServiceServer = null;
        if (\Cx\Core\Setting\Controller\Setting::getValue('mode','MultiSite') == \Cx\Core_Modules\MultiSite\Controller\ComponentController::MODE_MANAGER) {
            //get default service server
            $defaultWebsiteServiceServer = \Env::get('em')->getRepository('Cx\Core_Modules\MultiSite\Model\Entity\WebsiteServiceServer')
            ->findBy(array('id' => \Cx\Core\Setting\Controller\Setting::getValue('defaultWebsiteServiceServer','MultiSite')));
            $websiteServiceServer = $defaultWebsiteServiceServer[0];
        }
        
        $website = new \Cx\Core_Modules\MultiSite\Model\Entity\Website($basepath, $websiteName, $websiteServiceServer, $objUser, false, $websiteThemeId);
        return $website;
    }
    
    /**
     * Find websites by the search term
     * 
     * @param string $term
     * 
     * @return array
     */
    public function findByTerm($term) {
        if (empty($term)) {
            return array();
        }
        $qb = $this->getEntityManager()->createQueryBuilder();
        $qb ->select('website')
            ->from('\Cx\Core_Modules\MultiSite\Model\Entity\Website', 'website')
            ->where('website.name LIKE ?1')
            ->setParameter(1, '%' . contrexx_raw2db($term) . '%');
        
        $websites = $qb->getQuery()->getResult();
        
        return !empty($websites) ? $websites : array();
    }
}


