<?php
/**
 * CrmEvent Class CRM
 *
 * @category   CrmEvent
 * @package    contrexx
 * @subpackage module_crm
 * @author     SoftSolutions4U Development Team <info@softsolutions4u.com>
 * @copyright  2012 and CONTREXX CMS - COMVATION AG
 * @license    trial license
 * @link       www.contrexx.com
 */

/**
 * CrmEvent Class CRM
 *
 * @category   CrmEvent
 * @package    contrexx
 * @subpackage module_crm
 * @author     SoftSolutions4U Development Team <info@softsolutions4u.com>
 * @copyright  2012 and CONTREXX CMS - COMVATION AG
 * @license    trial license
 * @link       www.contrexx.com
 */
class CrmEvent
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
     * @param String $name    event name
     * @param String $context event context
     * @param String $info    event info
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
     * @param String $context event context
     *
     * @return null
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
     * @param String $info event info
     *
     * @return null
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
     *
     * @return null
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
     * @param String $name set event name
     *
     * @return null
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
 
