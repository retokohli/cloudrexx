<?php

namespace Cx\Core_Modules\Workbench\Model\Entity;

class ConsoleInterface extends UserInterface {

    public function __construct($arguments, $cx) {
        parent::__construct($cx);

        $command = 'help';
        if (isset($arguments[1])) {
            $command = $arguments[1];
        }

        if ($command == 'help') {
            if (isset($arguments[2])) {
                if ($this->commandExists($arguments[2])) {
                    $command = $this->getCommand($arguments[2]);    
                    echo 'Command `' . $command->getName() . "`\r\n" .
                        $command->getDescription() . "\r\n\r\n" .
                        $command->getSynopsis() . "\r\n\r\n" .
                        $command->getHelp() . "\r\n";
                    exit;
                } else {
                    echo 'No such subcommand, read the list:' . "\r\n\r\n";
                }
            }
            $this->showHelp();
        } else if ($this->commandExists($command)) {
            try {
                $this->getCommand($command)->execute($arguments);
            } catch (\Cx\Core_Modules\Workbench\Model\Entity\CommandException $e) {
                echo 'Command failed: ' . $e->getMessage();
            } catch (\Exception $e) {
                echo 'FATAL: ' . $e->getMessage();
            }
        } else {
            $this->showHelp();
        }
        echo "\r\n";
    }
    
    private function showHelp() {
        echo 'Contrexx Workbench command line utility

Synopsis: workbench(.bat) <subcommand> [options] [parameter]

Use »workbench(.bat) help <subcommand>« for more info about a subcommand

Available subcommands:' . "\r\n";
        foreach ($this->getCommands() as $command) {
            echo "\t" . $command->getName() . ' - ' . $command->getDescription() . "\r\n";
        }
    }
    
    public function getDb() {
        return $this->cx->getDb();
    }
    
    public function input($description, $defaultValue = '') {
        echo $description . ' [' . $defaultValue . ']: ';
        $handle = fopen('php://stdin', 'r');
        $line = strtolower(trim(fgets($handle)));
        if (trim($line) == '') {
            $line = $defaultValue;
        }
        return $line;
    }
    
    public function yesNo($question) {
        echo $question . ' [N,y] ';
        $handle = fopen('php://stdin', 'r');
        $line = strtolower(trim(fgets($handle)));
        return ($line == 'yes' || $line == 'y');
    }
    
    public function show($message) {
        if ($this->silent) {
            return;
        }
        echo $message . "\r\n";
    }
    
    /**
     * Tested for 2 dimensions only
     * @todo $childrenCount must be an array in order to handle more than 2 dimensions
     * @param array $tree
     * @param type $displayindex 
     */
    public function tree(array $tree, $displayindex = 0) {
        $output = '';
        $levelOffset = '──';
        $level = 1;
        $childrenCount = 0;
        $tree = array_reverse($tree);
        while (count($tree)) {
            $currentItem = array_pop($tree);
            if ($childrenCount == 0 && $level > 1) {
                $level--;
            }
            if ($childrenCount) {
                $childrenCount--;
            }
            $entryLevelOffset = '';
            for ($i = 0; $i < $level; $i++) {
                $entryLevelOffset .= $levelOffset;
            }
            $output .= '├' . $entryLevelOffset . $currentItem[$displayindex] . "\r\n";
            if (isset($currentItem['children'])) {
                $level++;
                $children = array_reverse($currentItem['children']);
                $childrenCount = count($children);
                foreach ($children as $child) {
                    array_push($tree, $child);
                }
            }
        }
        echo $output;
    }
}
