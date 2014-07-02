<?php

/**
 * PageTree
 *
 * @copyright   CONTREXX CMS - COMVATION AG
 * @author      COMVATION Development Team <info@comvation.com>
 * @package     contrexx
 * @subpackage  core_pagetree
 */

namespace Cx\Core\PageTree;

/**
 * Base class for all kinds of trees such as Sitemaps and Navigation.
 *
 * @copyright   CONTREXX CMS - COMVATION AG
 * @author Michael Ritter <michael.ritter@comvation.com>
 * @package     contrexx
 * @subpackage  core_pagetree
 */
abstract class PageTree {
    protected static $virtualPagesAdded = false;
    protected $lang = null;
    protected $rootNode = null;
    protected $depth = null;
    protected $em = null;
    protected $license = null;
    protected $currentPage = null;
    protected $pageIdsAtCurrentPath = array();    
    protected $currentPagePath = null;
    protected $pageRepo = null;
    protected $skipInvisible = true;
    
    /**
     * @param $entityManager the doctrine em
     * @param \Cx\Core_Modules\License\License $license License used to check if a module is allowed in frontend
     * @param int $maxDepth maximum depth to fetch, 0 means everything
     * @param \Cx\Core\ContentManager\Model\Entity\Node $rootNode node to use as root
     * @param int $lang the language
     * @param \Cx\Core\ContentManager\Model\Entity\Page $currentPage if set, renderElement() will receive a correctly set $current flag.
     */
    public function __construct($entityManager, $license, $maxDepth = 0, $rootNode = null, $lang = null, $currentPage = null, $skipInvisible = true) {
        $this->lang = $lang;
        $this->depth = $maxDepth;
        $this->em = $entityManager;
        $this->license = $license;
        $this->rootNode = $rootNode;
        $this->currentPage = $currentPage;
        $this->skipInvisible = $skipInvisible;
        $pageI = $currentPage;
        while ($pageI) {
            $this->pageIdsAtCurrentPath[] = $pageI->getId();
            try {
                $pageI = $pageI->getParent();
            } catch (\Cx\Core\ContentManager\Model\Entity\PageException $e) {
                $pageI = null;
            }
        }
        $this->startLevel = 1;
        $this->startPath = '';
        $this->pageRepo = $this->em->getRepository('Cx\Core\ContentManager\Model\Entity\Page');
        $this->nodeRepo = $this->em->getRepository('Cx\Core\ContentManager\Model\Entity\Node');
        if (!$this->rootNode) {
            $this->rootNode = $this->nodeRepo->getRoot();
        }
        $this->init();
    }

    /**
     * returns the string representation of the tree.
     *
     * @return string
     */
    public function render() {
        $content = $this->preRender($this->lang);
        $content .= $this->renderHeader($this->lang);
$this->bytes = memory_get_peak_usage();
        $content .= $this->internalRender($this->rootNode);
//echo 'PageTree2(' . get_class($this) . '): ' . formatBytes(memory_get_peak_usage()-$this->bytes) . '<br />';
        $content .= $this->renderFooter($this->lang);
        $content .= $this->postRender($this->lang);
        return $content;
    }
    
    /**
     * @todo Virtual pages!
     * @param type $nodes
     * @param type $level
     * @param type $dontDescend 
     */
    private function internalRender($node) {
        global $_CONFIG;
        $content = '';

        $qb  = $this->em->createQueryBuilder();
        $qb1 = clone $qb;
        
        $qb1->select('COUNT(p1.id)')
            ->from('\Cx\Core\ContentManager\Model\Entity\Page', 'p1')
            ->join('p1.node', 'n1')
            ->where("p1.type != 'alias'")
            ->andWhere('n1.parent = n.id')
            ->andWhere('p1.lang = :lang')
            ->andWhere('p1.active = 1');
        if ($this->skipInvisible) {
            $qb1->andWhere('p1.display = 1');
        }
        
        $qb ->select('p','n.lvl AS level', 'n.lft AS leftIndex', 'n.rgt AS rightIndex')
            ->addSelect('('. $qb1->getDQL() .') AS hasChild')            
            ->from('\Cx\Core\ContentManager\Model\Entity\Page', 'p')
            ->join('p.node', 'n')
            ->where("p.type != 'alias'")
            ->andWhere('n.lft >= :left')
            ->andWhere('n.rgt <= :right')
            ->andWhere('p.lang = :lang')
            ->orderBy('n.lft', 'ASC')
            ->setParameters(array(
                'left'  => $node->getLft(),
                'right' => $node->getRgt(),
                'lang'  => $this->lang
            ));
        $lastLevel = $this->getLastLevel();
        if ($lastLevel) {
            $qb->andWhere('n.lvl <= :level');
            $qb->setParameter('level', $lastLevel);
        }
        
        $pages = $qb->getQuery()->getResult();
        
        $invisibleIndex = 0;
        foreach ($pages as $page) {
            
            $currentPage = $page[0];
            $level       = $page['level'];
            $left        = $page['leftIndex'];
            $right       = $page['rightIndex'];
            $hasChilds   = (boolean) $page['hasChild'];
            
            if ($left <= $invisibleIndex) {
                continue;
            }
            
            if (!$currentPage || !$currentPage->isActive() || !$currentPage->isVisible()) {
                $invisibleIndex = $right;                
                continue;
            }
            
            // if page is protected, user has not sufficent permissions and protected pages are hidden
            if ($currentPage->isFrontendProtected() && $_CONFIG['coreListProtectedPages'] != 'on' &&
                    !\Permission::checkAccess($currentPage->getFrontendAccessId(), 'dynamic', true)
                ) {
                $invisibleIndex = $right;
                continue;
            }
            
            if ($currentPage->getModule() != '' && !$this->license->isInLegalFrontendComponents($currentPage->getModule())) {
                $invisibleIndex = $right;
                continue;
            }
            // prepare data for element
            $current = in_array($currentPage->getId(), $this->pageIdsAtCurrentPath);
            
            $href = $currentPage->getPath();
            if (isset($_GET['pagePreview']) && $_GET['pagePreview'] == 1) {
                $href .= '?pagePreview=1';
            }
            
            $bytes = memory_get_peak_usage();
            $content .= $this->preRenderElement($level, $hasChilds, $this->lang, $currentPage);
            $content .= $this->renderElement($currentPage->getTitle(), $level, $hasChilds, $this->lang, $href, $current, $currentPage);
            $content .= $this->postRenderElement($level, $hasChilds, $this->lang, $currentPage);
            $bytes = memory_get_peak_usage()-$bytes;
            $this->bytes = $this->bytes + $bytes;
        }
        
        return $content;
    }
    
    /**
     * Tells wheter $pathToPage is in the active branch
     * @param String $pathToPage
     * @return boolean True if active, false otherwise
     */
    public function isPagePathActive($pathToPage) {
        if ($pathToPage == '') {
            return false;
        }
        
        $pathToPage = str_replace('//', '/', $pathToPage . '/');
        return substr($this->currentPagePath . '/', 0, strlen($pathToPage)) == $pathToPage;
    }

    public function setVirtualLanguageDirectory($dir) {
        $this->virtualLanguageDirectory = $dir;
    }
    
    protected abstract function preRenderElement($level, $hasChilds, $lang, $page);
    /**
     * Override this to do your representation of the tree.
     *
     * @param string $title
     * @param int $level 0-based level of the element
     * @param boolean $hasChilds are there children of this element? if yes, they will be processed in the subsequent calls.
     * @param int $lang language id
     * @param string $path path to this element, e.g. '/CatA/CatB'
     * @param boolean $current if a $currentPage has been specified, this will be set to true if either a parent element of the current element or the current element itself is rendered.
     *
     * @return string your string representation of the element.
     */
    protected abstract function renderElement($title, $level, $hasChilds, $lang, $path, $current, $page);

    protected abstract function postRenderElement($level, $hasChilds, $lang, $page);
    
    public abstract function preRenderLevel($level, $lang, $parentNode);
    
    public abstract function postRenderLevel($level, $lang, $parentNode);
    
    protected abstract function renderHeader($lang);
    
    protected abstract function renderFooter($lang);
    
    protected abstract function preRender($lang);
    
    protected abstract function postRender($lang);
    
    /**
     * Called on construction. Override if you do not want to override the ctor.
     */
    protected abstract function init();
    
    protected function getFirstLevel() {
        return 1; // show from first level
    }
    
    protected function getLastLevel() {
        return 0; // show all levels
    }

    protected function getFullNavigation() {
        return false;
    }
}
