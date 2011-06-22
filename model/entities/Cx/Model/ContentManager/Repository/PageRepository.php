<?php

namespace Cx\Model\ContentManager\Repository;

use Doctrine\Common\Util\Debug as DoctrineDebug;
use Doctrine\ORM\EntityRepository,
    Doctrine\ORM\EntityManager,
    Doctrine\ORM\Mapping\ClassMetadata;

class PageRepository extends EntityRepository {
    protected $em = null;

    public function __construct(EntityManager $em, ClassMetadata $class)
    {
        parent::__construct($em, $class);
        $this->em = $em;
    }


    public function getTreeByLanguage() {
        //TODO: join pages w/ nodes.
        //      then assign each page it's node, each node it's children
        //      navigation w/ page->getNode()->getParent()->getPages(lang)
        $repo = $this->em->getRepository('Cx\Model\ContentManager\Node');

        //get all nodes
        $tree = $repo->children(null, false, 'lft');

        //set up children node arrays
        foreach($tree as $node) {
            $parent = $node->getParent();
            while($parent) {
                $parent->addChildren($node);
                $parent = $parent->getParent();
            }
        }
        
        //return
        return $tree;
    }
}