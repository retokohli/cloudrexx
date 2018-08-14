<?php

namespace Cx\Core\MediaSource\Model\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * IndexerEntry
 */
class IndexerEntry extends \Cx\Model\Base\EntityBase {
    /**
     * @var integer
     */
    protected $id;

    /**
     * @var string
     */
    protected $path;

    /**
     * @var string
     */
    protected $indexer;

    /**
     * @var string
     */
    protected $content;

    /**
     * @var \DateTime
     */
    protected $lastUpdate;


    /**
     * Get id
     *
     * @return integer 
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set path
     *
     * @param string $path
     * @return IndexerEntry
     */
    public function setPath($path)
    {
        $this->path = $path;

        return $this;
    }

    /**
     * Get path
     *
     * @return string 
     */
    public function getPath()
    {
        return $this->path;
    }

    /**
     * Set indexer
     *
     * @param string $indexer
     * @return IndexerEntry
     */
    public function setIndexer($indexer)
    {
        $this->indexer = $indexer;

        return $this;
    }

    /**
     * Get indexer
     *
     * @return string 
     */
    public function getIndexer()
    {
        return $this->indexer;
    }

    /**
     * Set content
     *
     * @param string $content
     * @return IndexerEntry
     */
    public function setContent($content)
    {
        $this->content = $content;

        return $this;
    }

    /**
     * Get content
     *
     * @return string 
     */
    public function getContent()
    {
        return $this->content;
    }

    /**
     * Set lastUpdate
     *
     * @param \DateTime $lastUpdate
     * @return IndexerEntry
     */
    public function setLastUpdate($lastUpdate)
    {
        $this->lastUpdate = $lastUpdate;

        return $this;
    }

    /**
     * Get lastUpdate
     *
     * @return \DateTime 
     */
    public function getLastUpdate()
    {
        return $this->lastUpdate;
    }
}
