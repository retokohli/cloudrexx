<?php

/**
 * Class WebsiteTemplateRepository
 *
 * @copyright   CONTREXX CMS - COMVATION AG
 * @author      ss4u <ss4ugroup@gmail.com>
 * @package     contrexx
 * @subpackage  coremodule_multisite
 */

namespace Cx\Core_Modules\MultiSite\Model\Repository;

/**
 * Class WebsiteTemplateRepository
 * 
 * @copyright   CONTREXX CMS - COMVATION AG
 * @author      ss4u <ss4ugroup@gmail.com>
 * @package     contrexx
 * @subpackage  coremodule_multisite
 */
class WebsiteTemplateRepository extends \Doctrine\ORM\EntityRepository {
    
   /**
    * Get First Entity
    * 
    * @return mixed \Cx\Core_Modules\MultiSite\Model\Entity\WebsiteTemplate or null
    */
   public function getFirstEntity() {
        $qb = $this->getEntityManager()->createQueryBuilder();
        $qb->select('t')
                ->from('\Cx\Core_Modules\MultiSite\Model\Entity\WebsiteTemplate', 't')
                ->orderBy('t.id')
                ->setMaxResults(1);
        $result = $qb->getQuery()->getResult();
        return $result ? current($result) : null;
    }
    
}
