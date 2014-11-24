<?php

/**
 * Class Job
 *
 * @copyright   CONTREXX CMS - COMVATION AG
 * @author      Project Team SS4U <info@comvation.com>
 * @package     contrexx
 * @subpackage  coremodule_cron
 */

namespace Cx\Core_Modules\Cron\Model\Entity;

/**
 * Class Job
 * 
 * @copyright   CONTREXX CMS - COMVATION AG
 * @author      Project Team SS4U <info@comvation.com>
 * @package     contrexx
 * @subpackage  coremodule_cron
 */
class Job extends \Cx\Model\Base\EntityBase {
    /**
     * @var integer $id
     */
    protected $id;
    /**
     *
     * @var boolean $active 
     */
    protected $active;
    /**
     *
     * @var string $expression 
     */
    protected $expression;
    /**
     *
     * @var string $command 
     */
    protected $command;
    /**
     *
     * @var datetime $lastRan 
     */
    protected $lastRan;
    
    /**
     * Get the id
     * 
     * @return integer $id
     */
    public function getId() {
        return $this->id;
    }

    /**
     * Set the id
     * 
     * @param integer $id
     */
    public function setId($id) {
        $this->id = $id;
    }
    
    /**
     * Get the active
     * 
     * @return boolean $active
     */
    public function getActive() {
        return $this->active;
    }
    /**
     * Set the active
     * 
     * @param boolean $active
     */
    public function setActive($active) {
        $this->active = $active;
    }
    /**
     * Get the expression
     * 
     * @return string expression
     */
    public function getExpression() {
        return $this->expression;
    }
    /**
     * Set the expression
     * 
     * @param string $expression
     */
    public function setExpression($expression) {
        $this->expression = $expression;
    }
    /**
     * Get the command
     * 
     * @return string command
     */
    public function getCommand() {
        return $this->command;
    }
    /**
     * Set the command
     * 
     * @param string $command
     */
    public function setCommand($command) {
        $this->command = $command;
    }
    /**
     * Get the lastRan
     * 
     * @return type lastRan
     */
    public function getLastRan() {
        return $this->lastRan;
    }
    /**
     * Set the lastRan
     * 
     * @param type $lastRan
     */
    public function setLastRan($lastRan) {
        $this->lastRan = $lastRan;
    }
    /**
     * check and execute cron job
     * 
     * @return boolean
     */
    public function execute() {
            
        try {
            // check if cron job needs to be executed
            $cron = \Cron\CronExpression::factory($this->expression);
            if ($cron->getNextRunDate($this->lastRan, 0)->getTimestamp() > time()) {
                return false;
            }
            // execute cron job
            if (call_user_func($this->command)) {
                // update last ran time to now if cron job has successfully been executed
                $this->lastRan = new \DateTime();
            }
            return true;
        } catch (\Exception $e) {
            \DBG::msg($e->getMessage());
            return $e->getMessage();
        }
    }
}
