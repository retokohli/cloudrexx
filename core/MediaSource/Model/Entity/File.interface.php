<?php
/**
 * @copyright   Comvation AG 
 * @author Robin Glauser <robin.glauser@comvation.com>
 * @package     contrexx
 */

namespace Cx\Core\MediaSource\Model\Entity;


interface File {
    public function getPath();
    public function getName();
    public function getFullName();
    public function getExtension();
    public function getMimeType();
    public function __toString();
}