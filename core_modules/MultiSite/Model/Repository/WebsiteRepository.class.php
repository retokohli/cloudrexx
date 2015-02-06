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
    public function getWebsitesByCriteria($criteria, $userIds) {
        try {
            $qb = $this->getEntityManager()->createQueryBuilder();
            $qb->select('Website')
                ->from('\Cx\Core_Modules\MultiSite\Model\Entity\Website', 'Website');

            $filterPos = 1;            
            foreach ($criteria as $fieldName => $fieldValue) {
                if (empty($fieldValue)) {
                    continue;
                }
                //for date field
                if ($fieldName == 'Website.creationDate') {
                    $this->addDateFilterToQueryBuilder($qb, $fieldName, $fieldValue, $filterPos, false);
                } else {
                    $method = ($filterPos == 1) ? 'where' : 'andWhere';
                    $qb->$method($fieldName . ' = ?' . $filterPos)->setParameter($filterPos, $fieldValue);
                    $filterPos++;
                }                
            }
            
            if (!empty($userIds)) {
                $method = ($filterPos == 1) ? 'where' : 'andWhere';
                $qb->$method($qb->expr()->in('Website.ownerId', $userIds));
            }
            
            $qb->getDql();
            $websites = $qb->getQuery()->getResult();
        } catch (\Doctrine\ORM\NoResultException $e) {
            $websites = array();
        }
        
        return $websites;
    }

    /**
     * Get the userids by criteria
     * 
     * @param array $criteria
     * 
     * @return boolean|array
     */
    public function getUsersByCriteria(array $criteria) {
        if (empty($criteria)) {
            return;
        }
        try {
            $qb = $this->getEntityManager()->createQueryBuilder();
            $qb
                ->select('User')
                ->from('\Cx\Core\User\Model\Entity\User', 'User');

            $filterPos = 1;            
            foreach ($criteria as $fieldName => $fieldValue) {
                if (empty($fieldValue)) {
                    continue;
                }
                //for date field
                if ($fieldName == 'User.regdate') {
                    $this->addDateFilterToQueryBuilder($qb, $fieldName, $fieldValue, $filterPos, true);
                } else {
                    $method = ($filterPos == 1) ? 'where' : 'andWhere';
                    $qb->$method($fieldName . ' = ?' . $filterPos)->setParameter($filterPos, $fieldValue);
                    $filterPos++;
                }
            }

            $users = $qb->getQuery()->getResult();
            if (!$users) {
                return;
            }
            $userIds = array();
            foreach ($users as $user) {
                if (   isset($criteria['User.regdate'])
                    && !preg_match('#^[ON\ | BEFORE\ | AFTER\ ]#i', $criteria['User.regdate'])
                ) {
                    $regDate = new \DateTime();
                    $regDate->setTimestamp($user->getRegdate());
                    if (!\Cx\Core_Modules\MultiSite\Controller\CronController::validateDateByCriteria($regDate, $criteria['User.regdate'])) {
                        continue;
                    }
                }
                $userIds[] = $user->getId();
            }
        } catch (\Doctrine\ORM\NoResultException $e) {
            $userIds = array();
        }
        return $userIds;
    }
        
    /**
     * Add the date filter to the query builder
     * 
     * @param \Doctrine\ORM\QueryBuilder $qb            Query builder object
     * @param string                     $fieldName     filter field name
     * @param string                     $criteria      filter criteria    
     * @param int                        $filterPos     current postion of filter query
     * @param boolean                    $useTimeStamp  use datetime or timestamp in the query
     * 
     * @return null
     */
    public function addDateFilterToQueryBuilder(\Doctrine\ORM\QueryBuilder & $qb, $fieldName, $criteria, & $filterPos, $useTimeStamp = false)
    {
        if (empty($fieldName) || empty($criteria)) {
            return;
        }
        
        // return if format not in ON|BEFORE|AFTER
        if (!preg_match('#^[ON\ | BEFORE\ | AFTER\ ]#i', $criteria)) {
            return;
        }
        
        $startDate = new \DateTime(preg_replace('/\b(ON|BEFORE|AFTER) \b/i', '', $criteria));
        $startDate->setTime(0, 0, 1);
        
        $method = ($filterPos == 1) ? 'where' : 'andWhere';
        switch (true) {
            case preg_match('#^ON\ #i', $criteria):
                $qb
                    ->$method($fieldName . ' > ?'. $filterPos)
                    ->setParameter($filterPos, self::parseTimeForFilter($startDate, $useTimeStamp));                
                $startDate->setTime(23, 59, 59);
                $filterPos++;
                
                $qb
                    ->andWhere($fieldName . ' < ?'. $filterPos)
                    ->setParameter($filterPos,  self::parseTimeForFilter($startDate, $useTimeStamp));
                break;
            case preg_match('#^BEFORE\ #i', $criteria):
                $qb
                    ->$method($fieldName . '< ?'. $filterPos)
                    ->setParameter($filterPos, self::parseTimeForFilter($startDate, $useTimeStamp));
                break;
            case preg_match('#^AFTER\ #i', $criteria):
                $startDate->setTime(23, 59, 59);
                $qb
                    ->$method($fieldName . ' > ?'. $filterPos)
                    ->setParameter($filterPos, self::parseTimeForFilter($startDate, $useTimeStamp));
                break;
        }
        $filterPos++;        
    }

    /**
     * Get the timestamp or date value based on the date time object
     * 
     * @param \DateTime $date
     * @param boolean   $timeStamp
     * 
     * @return array
     */
    public static function parseTimeForFilter(\DateTime $date, $timeStamp = false) {        
        return $timeStamp ? $date->getTimestamp() : $date->format('Y-m-d H:i:s');
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


