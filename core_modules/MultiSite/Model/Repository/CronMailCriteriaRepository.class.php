<?php

/**
 * Class CronMailCriteriaRepository
 *
 * @copyright   CONTREXX CMS - COMVATION AG
 * @author      ss4u <ss4ugroup@gmail.com>
 * @package     contrexx
 * @subpackage  coremodule_multisite
 */

namespace Cx\Core_Modules\MultiSite\Model\Repository;

/**
 * Class CronMailCriteriaRepository
 * 
 * @copyright   CONTREXX CMS - COMVATION AG
 * @author      ss4u <ss4ugroup@gmail.com>
 * @package     contrexx
 * @subpackage  coremodule_multisite
 */
class CronMailCriteriaRepository extends \Doctrine\ORM\EntityRepository {
    /**
     * Check the website criteria exists or not
     * 
     * @return array
     */
    public function isWebsiteCriteriaExists($id) {
        if (empty($id)) {
            return false;
        }
        
        $qb = $this->getEntityManager()->createQueryBuilder();
        $qb->select('count(c.id)')
           ->from('\Cx\Core_Modules\MultiSite\Model\Entity\CronMailCriteria', 'c')
           ->leftJoin('c.cronMail', 'cm')
           ->where('cm.id = ' . $id)     
           ->andWhere('c.attribute LIKE ?1')->setParameter(1, 'Website%');
        
        return $qb->getQuery()->getSingleScalarResult();
    }
}
