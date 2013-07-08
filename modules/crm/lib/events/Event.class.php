<?php
/**
 * Event Class CRM
 *
 * @category   Event
 * @package    Contrexx
 * @subpackage Module_Crm
 * @author     SoftSolutions4U Development Team <info@softsolutions4u.com>
 * @copyright  2012 and CONTREXX CMS - COMVATION AG
 * @license    trial license
 * @link       www.contrexx.com
 */

/**
 * Event Class CRM
 *
 * @category   Event
 * @package    Contrexx
 * @subpackage Module_Crm
 * @author     SoftSolutions4U Development Team <info@softsolutions4u.com>
 * @copyright  2012 and CONTREXX CMS - COMVATION AG
 * @license    trial license
 * @link       www.contrexx.com
 */
class Event
{
    /**
    * Event name
    *
    * @access protected
    * @var string
    */
    protected $name;

    /**
    * Event context
    *
    * @access protected
    * @var string
    */
    protected $context;

    /**
    * Event cancel status
    *
    * @access protected
    * @var boolean
    */
    protected $cancelled = false;

    /**
    * Event information
    *
    * @access protected
    * @var string
    */
    protected $info;

    /**
     * Constructor
     *
     * @param String $name
     * @param String $context
     * @param String $info
     *
     * @return null
     */
    function __construct($name, $context = null, $info = null)
    {
        $this->setName($name);
        $this->setContext($context);
        $this->setInfo($info);
    }

    /**
     * Set Context
     * 
     * @param String $context
     */
    public function setContext($context)
    {
        $this->context = $context;
    }

    /**
     * Get Context
     * 
     * @return String
     */
    public function getContext()
    {
        return $this->context;
    }

    /**
     * Set information
     *
     * @param String $info
     */
    public function setInfo($info)
    {
        $this->info = $info;
    }

    /**
     * Get Information
     * 
     * @return String
     */
    public function getInfo()
    {
        return $this->info;
    }

    /**
     * Cancel status
     */
    function cancel()
    {
        $this->cancelled = true;
    }

    /**
     * return cancel status
     *
     * @return Boolean
     */
    public function isCancelled()
    {
        return $this->cancelled;
    }

    /**
     * Set name
     * 
     * @param String $name
     */
    public function setName($name)
    {
        $this->name = $name;
    }

    /**
     * Get name
     * 
     * @return String
     */
    public function getName()
    {
        return $this->name;
    }
}
 
