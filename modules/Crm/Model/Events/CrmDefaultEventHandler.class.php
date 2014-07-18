<?php
/**
 * CrmDefaultEventHandler Class CRM
 *
 * @category   CrmDefaultEventHandler
 * @package    contrexx
 * @subpackage module_crm
 * @author     SoftSolutions4U Development Team <info@softsolutions4u.com>
 * @copyright  2012 and CONTREXX CMS - COMVATION AG
 * @license    trial license
 * @link       www.contrexx.com
 */

namespace Cx\Modules\Crm\Model\Events;

/**
 * CrmDefaultEventHandler Class CRM
 *
 * @category   CrmDefaultEventHandler
 * @package    contrexx
 * @subpackage module_crm
 * @author     SoftSolutions4U Development Team <info@softsolutions4u.com>
 * @copyright  2012 and CONTREXX CMS - COMVATION AG
 * @license    trial license
 * @link       www.contrexx.com
 */
class CrmDefaultEventHandler implements \Cx\Modules\Crm\Model\Events\CrmEventHandler
{
    /**
     * Default Information
     *
     * @access protected
     * @var array
     */
    protected $default_info = array(
        'section'   => 'Crm'
    );

    /**
     * Event handler
     * 
     * @param Event $event calling event
     *
     * @return boolean
     */
    function handleEvent(\Cx\Modules\Crm\Model\Entity\CrmEvent $event)
    {
        $info          = $event->getInfo();
        $substitutions = isset($info['substitution']) ? $info['substitution'] : array();
        $lang_id       = isset($info['lang_id']) ? $info['lang_id'] : FRONTEND_LANG_ID;
        $arrMailTemplate = array_merge(
            $this->default_info,
            array(
                'key'          => $event->getName(),
                'lang_id'      => $lang_id, 
                'substitution' => $substitutions,
        ));

        if (false === \Cx\Core\MailTemplate\Controller\MailTemplate::send($arrMailTemplate)) {
            $event->cancel();
            return false;
        };
        return true;
    }
}

