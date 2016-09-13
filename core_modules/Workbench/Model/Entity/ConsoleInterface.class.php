<?php
/**
 * Contrexx
 *
 * @link      http://www.contrexx.com
 * @copyright Comvation AG 2007-2014
 * @version   Contrexx 4.0
 * 
 * According to our dual licensing model, this program can be used either
 * under the terms of the GNU Affero General Public License, version 3,
 * or under a proprietary license.
 *
 * The texts of the GNU Affero General Public License with an additional
 * permission and of our proprietary license can be found at and
 * in the LICENSE file you have received along with this program.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU Affero General Public License for more details.
 *
 * "Contrexx" is a registered trademark of Comvation AG.
 * The licensing of the program under the AGPLv3 does not imply a
 * trademark license. Therefore any rights, title and interest in
 * our trademarks remain entirely with us.
 */

/**
 * User interface for console usage
 * @author Michael Ritter <michael.ritter@comvation.com>
 */

namespace Cx\Core_Modules\Workbench\Model\Entity;

/**
 * User interface for console usage
 * @author Michael Ritter <michael.ritter@comvation.com>
 */
class ConsoleInterface extends UserInterface {

    /**
     * Initialize this interface
     * @param array $arguments Commandline arguments
     * @param \Cx\Core\Core\Controller\Cx $cx Contrexx main class
     */
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
    
    /**
     * Shows help for workbench
     */
    private function showHelp() {
        echo 'Contrexx Workbench command line utility

Synopsis: workbench(.bat) <subcommand> [options] [parameter]

Use »workbench(.bat) help <subcommand>« for more info about a subcommand

Available subcommands:' . "\r\n";
        foreach ($this->getCommands() as $command) {
            echo "\t" . $command->getName() . ' - ' . $command->getDescription() . "\r\n";
        }
    }
    
    /**
     * Gives commands access to database object
     * @return \AdoNewConnection Database connection
     */
    public function getDb() {
        return $this->cx->getDb();
    }
    
    /**
     * Get a user input
     * @param string $description Description to display the user
     * @param string $defaultValue (optional) Value to return if user does not enter anything, default ''
     * @return string User input or default value
     */
    public function input($description, $defaultValue = '') {
        echo $description . ' [' . $defaultValue . ']: ';
        $handle = fopen('php://stdin', 'r');
        $line = strtolower(trim(fgets($handle)));
        if (trim($line) == '') {
            $line = $defaultValue;
        }
        return $line;
    }
    
    /**
     * Ask the user a yes/no question, default answer is no
     * @param string $question Question for the user
     * @return boolean True for yes, false otherwise
     */
    public function yesNo($question) {
        echo $question . ' [N,y] ';
        $handle = fopen('php://stdin', 'r');
        $line = strtolower(trim(fgets($handle)));
        return ($line == 'yes' || $line == 'y');
    }
    
    /**
     * Display a message for the user
     * @param string $message Message to display
     */
    public function show($message) {
        if ($this->silent) {
            return;
        }
        echo $message . "\r\n";
    }
    
    /**
     * Recursively show an array to the user (BETA)
     * 
     * Accepts an array in the form array({something}=>{title}, 'children'=>{recursion})
     * @todo Tested for 2 dimensions only
     * @todo $childrenCount must be an array in order to handle more than 2 dimensions
     * @param array $tree Array to show
     * @param mixed $displayindex Index to display of an entry
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
