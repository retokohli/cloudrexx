<?php
/**
 * EventHandler Interface CRM
 *
 * @category   EventHandler
 * @package    contrexx
 * @subpackage Module_Crm
 * @author     SoftSolutions4U Development Team <info@softsolutions4u.com>
 * @copyright  2012 and CONTREXX CMS - COMVATION AG
 * @license    trial license
 * @link       www.contrexx.com
 */

/**
 * EventHandler Interface CRM
 *
 * @category   EventHandler
 * @package    contrexx
 * @subpackage Module_Crm
 * @author     SoftSolutions4U Development Team <info@softsolutions4u.com>
 * @copyright  2012 and CONTREXX CMS - COMVATION AG
 * @license    trial license
 * @link       www.contrexx.com
 */
interface EventHandler
{
    /**
     * Event handler
     * 
     * @param Event $event event name
     *
     * @return null
     */
    function handleEvent(Event $event);
}
 
