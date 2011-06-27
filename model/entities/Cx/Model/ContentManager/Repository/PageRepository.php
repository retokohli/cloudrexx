<?php

namespace Cx\Model\ContentManager\Repository;

use Doctrine\Common\Util\Debug as DoctrineDebug;
use Doctrine\ORM\EntityRepository,
    Doctrine\ORM\EntityManager,
    Doctrine\ORM\Mapping\ClassMetadata,
    Doctrine\ORM\Query\Expr;

class PageRepository extends EntityRepository {
    protected $em = null;
    const ChildProperty = '__childs';
    const DataProperty = '__data';
    const NodeProperty = '__node';

    public function __construct(EntityManager $em, ClassMetadata $class)
    {
        parent::__construct($em, $class);
        $this->em = $em;
    }

    /**
     * Get a tree of all Nodes with their Pages assigned.
     *
     * @param Node $rootNode limit query to subtree.
     * @param int $lang limit query to language.
     * @param boolean $titlesOnly fetch titles only. You may want to use @link getTreeByTitle()
     * @return array
     */
    public function getTree($rootNode = null, $lang = null, $titlesOnly = false) {
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
        $tree = $repo->children($rootNode, false, 'lft', 'ASC', $qb);

        return $tree;
    }

    /**
     * Get a tree mapping titles to Page, Node and language.
     *
     * @see getTree()
     * @return array ( title => array( lang => langId, page => Page, __childs => array ) recursively array-mapped tree.
     */
    public function getTreeByTitle($rootNode = null, $lang = null, $titlesOnly = false) {
        $tree = $this->getTree($rootNode, $lang, true);

        $result = array();

        $this->treeByTitle($tree[0], $result);

        return $result;
    }

    protected function treeByTitle($root, &$result, &$lang2arr = null) {
        if(!$lang2arr)
            $lang2arr = $result;

        $myLang2arr = array();

        //(I) get titles of all Pages linked to this Node
        $pages = $root->getPages();
        foreach($pages as $page) {
            $title = $page->getTitle();
            $lang = $page->getLang();

            if($lang2arr) //this won't be set for the root node
                $target = &$lang2arr[$lang];
            else
                $target = &$result;

            if(isset($target[$title])) { //another language's Page has the same title
                //add the language
                $target[$title][self::DataProperty]['lang'][] = $lang;
            }
            else {
                $target[$title] = array();
                $target[$title][self::DataProperty] = array(
                    'lang' => array($lang),
                    'page' => $page,
                    'node' => $root,
                );
            }
            //remember mapping for recursion
            $myLang2arr[$lang] = &$target[$title];
        }

        //(II) recursion for child Nodes
        foreach($root->getChildren() as $child) {
            $this->treeByTitle($child, $result, $myLang2arr);
        }
    }
}