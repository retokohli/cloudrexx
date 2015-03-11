<?php

namespace Cx\Core\Wysiwyg\Model\Entity;

/**
 * Cx\Core\Wysiwyg\Model\Entity\WysiwygTemplate
 */
class WysiwygTemplate extends \Cx\Model\Base\EntityBase {
    /**
     * @var integer $id
     */
    protected $id;

    /**
     * @var string $title
     */
    protected $title;

    /**
     * @var text $description
     */
    protected $description;

    /**
     * @var string $imagePath
     */
    protected $imagePath;

    /**
     * @var text $htmlContent
     */
    protected $htmlContent;

    /**
     * @var boolean $active
     */
    protected $active = true;


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
     * Set title
     *
     * @param string $title
     */
    public function setTitle($title)
    {
        $this->title = $title;
    }

    /**
     * Get title
     *
     * @return string $title
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * Set description
     *
     * @param text $description
     */
    public function setDescription($description)
    {
        $this->description = $description;
    }

    /**
     * Get description
     *
     * @return text $description
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * Set imagePath
     *
     * @param string $imagePath
     */
    public function setImagePath($imagePath)
    {
        $this->imagePath = $imagePath;
    }

    /**
     * Get imagePath
     *
     * @return string $imagePath
     */
    public function getImagePath()
    {
        return $this->imagePath;
    }

    /**
     * Set htmlContent
     *
     * @param text $htmlContent
     */
    public function setHtmlContent($htmlContent)
    {
        $this->htmlContent = $htmlContent;
    }

    /**
     * Get htmlContent
     *
     * @return text $htmlContent
     */
    public function getHtmlContent()
    {
        return $this->htmlContent;
    }

    /**
     * Set active
     *
     * @param boolean $active
     */
    public function setActive($active)
    {
        $this->active = $active;
    }

    /**
     * Get active
     *
     * @return boolean $active
     */
    public function getActive()
    {
        return $this->active;
    }
}