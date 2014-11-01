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
        $basepath = \Cx\Core\Setting\Controller\Setting::getValue('websitePath');
        $websiteName = $saleOptions['websiteName'];
        if (\Cx\Core\Setting\Controller\Setting::getValue('mode') == \Cx\Core_Modules\MultiSite\Controller\ComponentController::MODE_MANAGER) {
            //get default service server
            $defaultWebsiteServiceServer = \Env::get('em')->getRepository('Cx\Core_Modules\MultiSite\Model\Entity\WebsiteServiceServer')
            ->findBy(array('id' => \Cx\Core\Setting\Controller\Setting::getValue('defaultWebsiteServiceServer')));
            $websiteServiceServer = $defaultWebsiteServiceServer[0];
        }
        $objUser = $saleOptions['customer'];
        $websiteThemeId = isset($saleOptions['themeId']) ? $saleOptions['themeId'] : null;
        $website = new \Cx\Core_Modules\MultiSite\Model\Entity\Website($basepath, $websiteName, $websiteServiceServer, $objUser, false, $websiteThemeId);
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
}


