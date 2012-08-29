<?php

namespace Cx\Model\ContentManager\Repository;

use Doctrine\Common\Util\Debug as DoctrineDebug;
use Doctrine\ORM\EntityRepository,
    Doctrine\ORM\EntityManager,
    Doctrine\ORM\Mapping\ClassMetadata,
    Doctrine\ORM\Query\Expr;
use Gedmo\Tree\Entity\Repository\NestedTreeRepository;

class NodeRepository extends NestedTreeRepository {
    protected $em = null;
    const DataProperty = '__data';

    public function __construct(EntityManager $em, ClassMetadata $class)
    {
        parent::__construct($em, $class);
        $this->em = $em;
    }

    /**
     * Returns the root node.
     * @todo DO NOT use NestedTreeRepository->getRootNodes(), it needs a lot of RAM, implement own query to get all root nodes
     * @return \Cx\Model\ContentManager\Node
     */
    public function getRoot() {
        return $this->findOneBy(array('id'=>1));
    }
}

