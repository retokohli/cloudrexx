<?php

namespace Cx\Model\ContentManager;

/**
 * Cx\Model\ContentManager\Node
 */
class Node extends \Cx\Model\Base\EntityBase
{
    /**
     * @var integer $id
     */
    private $id;

    /**
     * @var integer $lft
     */
    private $lft;

    /**
     * @var integer $rgt
     */
    private $rgt;

    /**
     * @var integer $lvl
     */
    private $lvl;

    /**
     * @var Cx\Model\ContentManager\Node
     */
    private $children;

    /**
     * @var Cx\Model\ContentManager\Page
     */
    private $pages;

    /**
     * @var Cx\Model\ContentManager\Node
     */
    private $parent;

    public function __construct()
    {
        $this->children = new \Doctrine\Common\Collections\ArrayCollection();
        $this->pages = new \Doctrine\Common\Collections\ArrayCollection();      
    }
    
    /**
     * Get id
     *
     * @return integer $id
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set lft
     *
     * @param integer $lft
     */
    public function setLft($lft)
    {
        $this->lft = $lft;
    }

    /**
     * Get lft
     *
     * @return integer $lft
     */
    public function getLft()
    {
        return $this->lft;
    }

    /**
     * Set rgt
     *
     * @param integer $rgt
     */
    public function setRgt($rgt)
    {
        $this->rgt = $rgt;
    }

    /**
     * Get rgt
     *
     * @return integer $rgt
     */
    public function getRgt()
    {
        return $this->rgt;
    }

    /**
     * Set lvl
     *
     * @param integer $lvl
     */
    public function setLvl($lvl)
    {
        $this->lvl = $lvl;
    }

    /**
     * Get lvl
     *
     * @return integer $lvl
     */
    public function getLvl()
    {
        return $this->lvl;
    }

    /**
     * Add children
     *
     * @param Cx\Model\ContentManager\Node $children
     */
    public function addChildren(\Cx\Model\ContentManager\Node $children)
    {
        $this->children[] = $children;
    }

    public function addParsedChild(\Cx\Model\ContentManager\Node $child)
    {
        $this->children[] = $child;
    }
    

    /**
     * Get children
     *
     * @return Doctrine\Common\Collections\Collection $children
     */
    public function getChildren()
    {
        return $this->children;
    }

    /**
     * Add pages
     *
     * @param Cx\Model\ContentManager\Page $pages
     */
    public function addPages(\Cx\Model\ContentManager\Page $pages)
    {
        $this->pages[] = $pages;
    }

    /**
     * Get pages
     *
     * @return Doctrine\Common\Collections\Collection $pages
     */
    public function getPages()
    {
        return $this->pages;
    }

    public function getPagesByLang()
    {
        $pages = $this->getPages();
        $result = array();

        foreach($pages as $page){
            $result[$page->getLang()] = $page;
        }

        return $result;
    }

    /**
     * Set parent
     *
     * @param Cx\Model\ContentManager\Node $parent
     */
    public function setParent(\Cx\Model\ContentManager\Node $parent)
    {
        $this->parent = $parent;
    }

    /**
     * Get parent
     *
     * @return Cx\Model\ContentManager\Node $parent
     */
    public function getParent()
    {
        return $this->parent;
    }

    public function addAssociatedPage($page) {
        $this->pages[] = $page;
    }
    /**
     * @var string $type
     */
    private $type;


    /**
     * Set type
     *
     * @param string $type
     */
    public function setType($type)
    {
        $this->type = $type;
    }

    /**
     * Get type
     *
     * @return string $type
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @prePersist
     */
    public function validate()
    {
        // Add your code here
    }
}