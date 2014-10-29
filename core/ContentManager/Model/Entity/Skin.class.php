<?php

/**
 * Skin
 *
 * @copyright   CONTREXX CMS - COMVATION AG
 * @author      COMVATION Development Team <info@comvation.com>
 * @package     contrexx
 * @subpackage  model_contentmanager
 */

namespace Cx\Core\ContentManager\Model\Doctrine\Entity;

/**
 * Skin
 *
 * @copyright   CONTREXX CMS - COMVATION AG
 * @author      COMVATION Development Team <info@comvation.com>
 * @package     contrexx
 * @subpackage  model_contentmanager
 */
class Skin
{
    /**
     * @var integer $id
     */
    private $id;

    /**
     * @var string $themesname
     */
    private $themesname;

    /**
     * @var string $foldername
     */
    private $foldername;

    /**
     * @var boolean $expert
     */
    private $expert;

    /**
     * @var Cx\Core\ContentManager\Model\Entity\Page
     */
    private $pages;

    public function __construct()
    {
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
     * Set themesname
     *
     * @param string $themesname
     */
    public function setThemesname($themesname)
    {
        $this->themesname = $themesname;
    }

    /**
     * Get themesname
     *
     * @return string $themesname
     */
    public function getThemesname()
    {
        return $this->themesname;
    }

    /**
     * Set foldername
     *
     * @param string $foldername
     */
    public function setFoldername($foldername)
    {
        $this->foldername = $foldername;
    }

    /**
     * Get foldername
     *
     * @return string $foldername
     */
    public function getFoldername()
    {
        return $this->foldername;
    }

    /**
     * Set expert
     *
     * @param boolean $expert
     */
    public function setExpert($expert)
    {
        $this->expert = $expert;
    }

    /**
     * Get expert
     *
     * @return boolean $expert
     */
    public function getExpert()
    {
        return $this->expert;
    }

    /**
     * Add pages
     *
     * @param Cx\Core\ContentManager\Model\Entity\Page $pages
     */
    public function addPages(\Cx\Core\ContentManager\Model\Entity\Page $pages)
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
}