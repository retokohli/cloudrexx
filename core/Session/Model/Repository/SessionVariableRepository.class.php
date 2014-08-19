<?php

/**
 * SessionVariableRepository
 *
 * @copyright   CONTREXX CMS - COMVATION AG
 * @author      COMVATION Development Team <info@comvation.com>
 * @author      ss4u <ss4u.comvation@gmail.com>
 * @package     contrexx
 * @subpackage  core_session
 */

namespace Cx\Core\Session\Model\Repository;

use Doctrine\ORM\EntityManager,
    Doctrine\ORM\Mapping\ClassMetadata,
    Gedmo\Tree\Entity\Repository\NestedTreeRepository;

/**
 * SessionVariableRepository
 *
 * @copyright   CONTREXX CMS - COMVATION AG
 * @author      COMVATION Development Team <info@comvation.com>
 * @author      ss4u <ss4u.comvation@gmail.com>
 * @package     contrexx
 * @subpackage  core_session
 */
class SessionVariableRepository extends NestedTreeRepository {
    protected $em = null;    

    public function __construct(EntityManager $em, ClassMetadata $class)
    {
        parent::__construct($em, $class);
        $this->em = $em;
    }
    
    public function find($id, $lockMode = 0, $lockVersion = NULL) {
        return $this->findOneBy(array('id' => $id));
    }
}
