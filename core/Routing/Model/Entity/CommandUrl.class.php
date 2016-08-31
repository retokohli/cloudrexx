<?php

namespace Cx\Core\Routing\Model\Entity;

/**
// can be generated from datasource/-access and component commands
 */
class CommandUrl extends \Cx\Core\Routing\Model\Entity\Url {
    
    public function getCommandName() {
        switch ($this->getScheme()) {
            case 'file':
                global $argv;
                $resolvePathParts = $argv;
                array_shift($resolvePathParts);
                break;
            default:
                $resolvePathParts = explode($this->getPathDelimiter(), $this->getPathWithoutOffset());
                array_shift($resolvePathParts);
                array_shift($resolvePathParts);
                break;
        }
        return (string) current($resolvePathParts);
    }
    
    public function getCommandArguments() {
        switch ($this->getScheme()) {
            case 'file':
                global $argv;
                array_shift($argv);
                array_shift($argv);
                return $argv;
                break;
            default:
                $arguments = explode($this->getPathDelimiter(), $this->getPathWithoutOffset());
                array_shift($arguments);
                array_shift($arguments);
                array_shift($arguments);
                $arguments += $_GET;
                return $arguments;
                break;
        }
    }
}

