<?php

namespace Cx\Model\ContentManager\Repository;

use Doctrine\Common\Util\Debug as DoctrineDebug;
use Doctrine\ORM\EntityRepository,
    Doctrine\ORM\EntityManager,
    Doctrine\ORM\Mapping\ClassMetadata,
    Doctrine\ORM\Query\Expr;

class PageRepository extends EntityRepository {
    protected $em = null;
    const DataProperty = '__data';

    public function __construct(EntityManager $em, ClassMetadata $class)
    {
        parent::__construct($em, $class);
        $this->em = $em;
    }

    /**
     * An array of pages sorted by their langID for specified module and cmd.
     * 
     * @param string $module
     * @param string $cmd optional
     *
     * @return array ( langId => Page )
     */
    public function getFromModuleCmdByLang($module, $cmd = null) {
        $crit = array( 'module' => $module );
        if($cmd)
            $crit['cmd'] = $cmd;
        
        $pages = $this->findBy($crit);
        $ret = array();
        
        foreach($pages as $page) {
            $ret[$page->getLang()] = $page;
        }

        return $ret;
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
        $qb->andWhere($qb->expr()->gt('node.lvl', 0)); //exclude root node

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

        $isRootQuery = !$rootNode || ( isset($rootNode) && $rootNode->getLvl() == 0); 

        if($isRootQuery) {
            /*
              special case: there are several childs of rootNode, but the rootNode itself is not in the resultset - we need to build the tree with all direct childs in mind
             */
            foreach($tree as $node) {
                $lang2arr = null;
                if($node->getLvl() == 1) {
                    $this->treeByTitle($node, $result);
                }
            }
        }
        else { //the root node is at $tree[0]
            $this->treeByTitle($tree[0], $result);
        }

        return $result;
    }

    protected function treeByTitle($root, &$result, &$lang2arr = null) {
        $myLang2arr = array();
        //(I) get titles of all Pages linked to this Node
        $pages = $root->getPages();
        foreach($pages as $page) {
            $title = $page->getTitle();
            $lang = $page->getLang();
            
            if($lang2arr) //this won't be set for the first node
                $target = &$lang2arr[$lang];
            else
                $target = &$result;
            
            if(isset($target[$title])) { //another language's Page has the same title
                //add the language
                $target[$title]['__data']['lang'][] = $lang;
            }
            else {
                $target[$title] = array();
                $target[$title]['__data'] = array(
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

    /**
     * Tries to find the path's Page.
     *
     * @param string $path e.g. Hello/APage/AModuleObject
     * @param Node $root
     * @param int $lang
     * @param boolean $exact if true, returns null on partially matched path
     * @return array ( 
     *     matchedPath => string (e.g. 'Hello/APage/'),
     *     unmatchedPath => string (e.g. 'AModuleObject') | null,
     *     node => Node,
     *     lang => array (the langIds where this matches)
     * )
     */
    public function getPagesAtPath($path, $root = null, $lang = null, $exact = false) {
        $tree = $this->getTreeByTitle($root, $lang, true);
        
        //this is a mock strategy. if we use this method, it should be rewritten to use bottom up
        $pathParts = explode('/', $path);
        $matchedLen = 0;
        $treePointer = &$tree;
        
        foreach($pathParts as $part) {
            if(isset($treePointer[$part])) {
                $treePointer = &$treePointer[$part];
                $matchedLen += strlen($part);
                if('/' == substr($path,$matchedLen,1))
                    $matchedLen++;
            }
            else {
                if($exact)
                    return null;
                break;
            }
        }

        //no level matched
        if($matchedLen == 0)
            return null;

        $unmatchedPath = substr($path, $matchedLen);
        if(!$unmatchedPath) { //beautify the FALSE to null
            $unmatchedPath = null;
        }

        $result = array(
            'matchedPath' => substr($path, 0, $matchedLen),
            'unmatchedPath' => $unmatchedPath
        );
        if(!$lang) {
            $result['pages'] = $treePointer['__data']['node']->getPagesByLang();
            $result['lang'] = $treePointer['__data']['lang'];
        }
        else {
            $page = $treePointer['__data']['node']->getPagesByLang();
            $page = $page[$lang];
            $result['page'] = $page;
        }

        return $result;
    }

    /**
     * Get a pages' path. Quite costly
     * @todo should be rewritten to use a custom query on heavy usage
     *     
     * @param \Cx\Model\ContentManager\Page $page
     * @return string path, e.g. '/This/Is/It'
     */
    public function getPath($page) {
        $lang = $page->getLang();
        $node = $page->getNode();
        $nodeRepo = $this->em->getRepository('Cx\Model\ContentManager\Node');
        $pathNodes = $nodeRepo->getPath($node);
        
        $path = '';
        foreach($pathNodes as $node) {
            $pages = $node->getPagesByLang();
            $thePageInOurLang = $pages[$page->getLang()];
            
//TODO: what happens if $thePageInOurLang is still null?
//      This should be restricted by the content manager.
            $path .= '/'.$thePageInOurLang->getTitle();
        }

        return $path;
    }
}