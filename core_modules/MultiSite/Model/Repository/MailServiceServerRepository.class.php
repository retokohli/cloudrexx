<?php

/**
 * Class MailServiceServerRepository
 *
 * @copyright   CONTREXX CMS - COMVATION AG
 * @author      ss4u <ss4ugroup@gmail.com>
 * @package     contrexx
 * @subpackage  coremodule_multisite
 */

namespace Cx\Core_Modules\MultiSite\Model\Repository;

/**
 * Class MailServiceServerRepository
 * 
 * @copyright   CONTREXX CMS - COMVATION AG
 * @author      ss4u <ss4ugroup@gmail.com>
 * @package     contrexx
 * @subpackage  coremodule_multisite
 */
class MailServiceServerRepository extends \Doctrine\ORM\EntityRepository {
    
    /**
    * Get First Entity
    * 
    * @return mixed \Cx\Core_Modules\MultiSite\Model\Entity\MailServiceServer or null
    */
   public function getFirstEntity() {
        $qb = $this->getEntityManager()->createQueryBuilder();
        $qb->select('m')
                ->from('\Cx\Core_Modules\MultiSite\Model\Entity\MailServiceServer', 'm')
                ->orderBy('m.id')
                ->setMaxResults(1);
        $result = $qb->getQuery()->getResult();
        return $result ? current($result) : null;
    }
    
}
