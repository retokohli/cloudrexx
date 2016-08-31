<?php

namespace Cx\Core\Routing\Model\Entity;

/**
// can be generated from component backend commands
 */
class BackendUrl extends \Cx\Core\Routing\Model\Entity\Url {

    public function getComponent() {
        $resolvePathParts = explode($this->getPathDelimiter(), $this->getPathWithoutOffset());
        array_shift($resolvePathParts);
        array_shift($resolvePathParts);
        if (in_array($resolvePathParts[0], array('', 'index.php'))) {
            return '';
        }
        if (!$this->hasParam('cmd')) {
            $this->setParam('cmd', $resolvePathParts[0]);
            $_REQUEST['cmd'] = $resolvePathParts[0];
            $_GET['cmd'] = $_REQUEST['cmd'];
        }
        return $resolvePathParts[0];
    }
    
    public function getArguments() {
        $resolvePathParts = explode($this->getPathDelimiter(), $this->getPathWithoutOffset());
        array_shift($resolvePathParts);
        array_shift($resolvePathParts);
        if (in_array($resolvePathParts[0], array('', 'index.php'))) {
            return '';
        }
        if (isset($resolvePathParts[1])) {
            if (substr($resolvePathParts[1], -1, 1) == '/') {
                $resolvePathParts[1] = substr($resolvePathParts[1], 0, -1);
            }
            if (!$this->hasParam('act')) {
                $this->setParam('act', $resolvePathParts[1]);
                $_REQUEST['act'] = $resolvePathParts[1];
                $_GET['act'] = $_REQUEST['act'];
            }
            return $resolvePathParts[1];
        }
        return '';
    }
}

