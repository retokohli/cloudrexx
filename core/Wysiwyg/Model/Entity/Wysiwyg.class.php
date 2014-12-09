<?php

namespace Cx\Core\Wysiwyg\Model\Entity;

/**
 * Cx\Core\Wysiwyg\Model\Entity\Wysiwyg
 */
class Wysiwyg extends \Cx\Model\Base\EntityBase {
    /**
     * @var integer $id
     */
    private $id;

    /**
     * @var string $title
     */
    private $title;

    /**
     * @var text $description
     */
    private $description;

    /**
     * @var string $imagePath
     */
    private $imagePath;

    /**
     * @var text $htmlContent
     */
    private $htmlContent;

    /**
     * @var boolean $inactive
     */
    private $inactive;


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
     * Set inactive
     *
     * @param boolean $inactive
     */
    public function setInactive($inactive)
    {
        $this->inactive = $inactive;
    }

    /**
     * Get inactive
     *
     * @return boolean $inactive
     */
    public function getInactive()
    {
        return $this->inactive;
    }
}