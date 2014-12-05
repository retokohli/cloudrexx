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
     * Find the lastest entry
     * 
     * @return object
     */
    public function findLastEntry(){
        $qb = $this->getEntityManager()->createQueryBuilder();
        
        $qb->select('WebTemplate')
                ->from('\Cx\Core_Modules\MultiSite\Model\Entity\WebsiteTemplate', 'WebTemplate')
                ->orderBy('WebTemplate.id', 'DESC')
                ->getDql();
        $qb->setFirstResult(0)->setMaxResults(1);
        return current($qb->getQuery()->getResult());
    }
}
