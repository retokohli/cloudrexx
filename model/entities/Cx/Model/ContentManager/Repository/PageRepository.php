<?php

namespace Cx\Model\ContentManager\Repository;

use Doctrine\Common\Util\Debug as DoctrineDebug;
use Doctrine\ORM\EntityRepository,
    Doctrine\ORM\EntityManager,
    Doctrine\ORM\Mapping\ClassMetadata,
    Doctrine\ORM\Query\Expr;

class PageRepository extends EntityRepository {
    protected $em = null;

    public function __construct(EntityManager $em, ClassMetadata $class)
    {
        parent::__construct($em, $class);
        $this->em = $em;
    }


    public function getTree($lang = null) {
        //TODO: join pages w/ nodes.
        //      then assign each page it's node, each node it's children
        //      navigation w/ page->getNode()->getParent()->getPages(lang)
        $repo = $this->em->getRepository('Cx\Model\ContentManager\Node');
        $qb = $this->em->createQueryBuilder();

        $joinConditionType = null;
        $joinCondition = null;

        //language filtering
        if($lang) {
            $joinConditionType = Expr\Join::WITH;
            $joinCondition = $qb->expr()->eq('p.lang',$lang);
        }
        $qb->addSelect('p');

        //join the pages
        $qb->leftJoin('node.pages', 'p', $joinConditionType, $joinCondition);

        //get all nodes
        $tree = $repo->children(null, false, 'lft', 'ASC', $qb);

        $cur = 1;
        //set up children node arrays
        /*
        foreach($tree as $node) {
            $parent = $node->getParent();
            while($parent) {
                echo "node " . $node->getId() ." at lvl " .$node->getLvl(). ": adding $cur\n";
                $parent->addParsedChild($node);
                $parent = $parent->getParent();
            }
            $cur++;
        }
        */
        
        //return
        return $tree;
    }
}