<?php

/**
 * Class ProductRepository
 *
 * @copyright   CONTREXX CMS - COMVATION AG
 * @author      Comvation Development Team <info@comvation.com>
 * @package     contrexx
 * @subpackage  module_pim
 */

namespace Cx\Modules\Pim\Model\Repository;

/**
 * Class ProductRepository
 * 
 * @copyright   CONTREXX CMS - COMVATION AG
 * @author      Comvation Development Team <info@comvation.com>
 * @package     contrexx
 * @subpackage  module_pim
 */
class ProductRepository extends \Doctrine\ORM\EntityRepository {
    
    /**
    * Get MultisiteProducts
    * 
    * @return mixed \Cx\Modules\Pim\Model\Entity\Product or null
    */
    public function getMultisiteProducts() 
    {
        $qb = $this->getEntityManager()->createQueryBuilder();
        $qb->select('p')
                ->from('\Cx\Modules\Pim\Model\Entity\Product', 'p')
                ->where("p.entityClass = 'Cx\Core_Modules\MultiSite\Model\Entity\Website'")
                ->orWhere("p.entityClass = 'Cx\Core_Modules\MultiSite\Model\Entity\WebsiteCollection'")
                ->orderBy('p.id');
        $result =  $qb->getQuery()->getResult();
        return $result;
    }
}
