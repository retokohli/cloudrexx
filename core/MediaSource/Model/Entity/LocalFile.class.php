<?php
/**
 * @copyright   Comvation AG
 * @author      Robin Glauser <robin.glauser@comvation.com>
 * @package     contrexx
 */

namespace Cx\Core\MediaSource\Model\Entity;


class LocalFile implements File
{
    /**
     * @var string
     */
    private $file;

    function __construct($file) {
        $this->file = $file;
    }

    public function getPath() {
        return pathinfo($this->file, PATHINFO_DIRNAME);
    }

    public function getName() {
        return pathinfo($this->file, PATHINFO_FILENAME);
    }

    public function getExtension() {
        return pathinfo($this->file, PATHINFO_EXTENSION);
    }

    public function getMimeType() {
        return \Mime::getMimeTypeForExtension(pathinfo($this->file, PATHINFO_EXTENSION));
    }

    /**
     * @return string
     */
    public function __toString() {
        return $this->file;
    }

    public function getFullName() {
        return pathinfo($this->file, PATHINFO_BASENAME);
    }
}