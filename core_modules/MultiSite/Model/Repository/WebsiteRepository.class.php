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
        $websiteThemeId = isset($saleOptions['themeId']) ? $saleOptions['themeId'] : null;
        $website = $this->initWebsite($saleOptions['websiteName'], $saleOptions['customer'], $websiteThemeId);
        \Env::get('em')->persist($website);
        // flush $website to database -> subscription will need the ID of $website
        // to properly work
        \Env::get('em')->flush();
        return $website;
    }
    
    public function findWebsitesByOwnerId($ownerId) {
        $qb = $this->getEntityManager()->createQueryBuilder();
        $qb->select('website')
                ->from('\Cx\Core_Modules\MultiSite\Model\Entity\Website', 'website')
                ->where('website.ownerId = :ownerId')
                ->groupBy('website.websiteServiceServerId')
                ->getDql();
        $qb->setParameter('ownerId', $ownerId);

        return $qb->getQuery()->getResult();
    }
    
    public function findWebsitesBySearchTerms($term) {
        $filter = array(
                    'email' => '%' . $term . '%'
                  );
        $ids = array();
        if ($objUser = \FWUser::getFWUserObject()->objUser->getUsers($filter)) {
            while (!$objUser->EOF) {
                $ids[] = $objUser->getId();
                $objUser->next();
            }
        }
        $matchedUsersId = !empty($ids) ? 'website.ownerId IN (' . implode(',', $ids) . ') OR' : '';
        $query = \Env::get('em')->createQuery("SELECT 
                                                    website 
                                                FROM 
                                                    Cx\Core_Modules\MultiSite\Model\Entity\Website website 
                                                JOIN 
                                                    website.domains domain 
                                                WHERE 
                                                    " . $matchedUsersId . "
                                                    website.name LIKE '%" . $term . "%'
                                                OR 
                                                    website.ftpUser LIKE '%" . $term . "%'
                                                OR 
                                                    domain.name LIKE '%" . $term . "%' 
                                                GROUP BY 
                                                    website.id"
        );
        return $query->getResult();
    }
    
    /**
     * Get the Website Owners by criteria
     * 
     * @param array $criteria
     * 
     * @return array
     */
    public function getWebsitesByCriteria(array $criteria) {
        try {
            $qb = $this->getEntityManager()->createQueryBuilder();
            $qb->select('website')
                ->from('\Cx\Core_Modules\MultiSite\Model\Entity\Website', 'website');
            
            if (!empty($criteria['creationDate'])) {
                switch (true) {
                    case preg_match('#^ON\ #i', $criteria['creationDate']):
                        $date = new \DateTime(preg_replace('#^ON\ #i', '', $criteria['creationDate']));
                        $qb->where('website.creationDate > ?1')->setParameter(1, $date->format('Y-m-d 00:00:01'));
                        $qb->andWhere('website.creationDate < ?2')->setParameter(2, $date->format('Y-m-d 23:59:59'));
                        break;
                    case preg_match('#^BEFORE\ #i', $criteria['creationDate']):
                        $date = new \DateTime(preg_replace('#^BEFORE\ #i', '', $criteria['creationDate']));
                        $qb->where('website.creationDate < ?3')->setParameter(3, $date->format('Y-m-d 00:00:01'));
                        break;
                    case preg_match('#^AFTER\ #i', $criteria['creationDate']):
                        $date = new \DateTime(preg_replace('#^AFTER\ #i', '', $criteria['creationDate']));
                        $qb->where('website.creationDate > ?4')->setParameter(4, $date->format('Y-m-d 23:59:59'));
                        break;
                }
            }
            
            $i = 5;
            foreach ($criteria as $key => $value) {
                if (empty($value) || $key === 'creationDate') {
                    continue;
                }
                if ($i == 5) {
                    $method = !empty($criteria['creationDate']) ? 'andWhere' : 'where';
                    $qb->$method('website.' . $key . ' = ?' . $i)->setParameter($i, $value);
                } else {
                    $qb->andWhere('website.' . $key . ' = ?' . $i)->setParameter($i, $value);
                }
                $i++;
            }
            
            $qb->getDql();
            $websites = $qb->getQuery()->getResult();
        } catch (\Doctrine\ORM\NoResultException $e) {
            $websites = array();
        }
        return $websites;
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
        
        $basepath = \Cx\Core\Setting\Controller\Setting::getValue('websitePath');
        $websiteServiceServer = null;
        if (\Cx\Core\Setting\Controller\Setting::getValue('mode') == \Cx\Core_Modules\MultiSite\Controller\ComponentController::MODE_MANAGER) {
            //get default service server
            $defaultWebsiteServiceServer = \Env::get('em')->getRepository('Cx\Core_Modules\MultiSite\Model\Entity\WebsiteServiceServer')
            ->findBy(array('id' => \Cx\Core\Setting\Controller\Setting::getValue('defaultWebsiteServiceServer')));
            $websiteServiceServer = $defaultWebsiteServiceServer[0];
        }
        
        $website = new \Cx\Core_Modules\MultiSite\Model\Entity\Website($basepath, $websiteName, $websiteServiceServer, $objUser, false, $websiteThemeId);
        return $website;
    }
}


