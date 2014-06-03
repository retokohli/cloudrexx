<?php
/**
 * Membership Class CRM
 *
 * @category   Membership
 * @package    contrexx
 * @subpackage module_crm
 * @author     SoftSolutions4U Development Team <info@softsolutions4u.com>
 * @copyright  2012 and CONTREXX CMS - COMVATION AG
 * @license    trial license
 * @link       www.contrexx.com
 */

/**
 * Membership Class CRM
 *
 * @category   Membership
 * @package    contrexx
 * @subpackage module_crm
 * @author     SoftSolutions4U Development Team <info@softsolutions4u.com>
 * @copyright  2012 and CONTREXX CMS - COMVATION AG
 * @license    trial license
 * @link       www.contrexx.com
 */

class membership
{

    /**
    * Module Name
    *
    * @access private
    * @var string
    */
    private $moduleName = 'crm';

    /**
    * Table Name
    *
    * @access private
    * @var string
    */
    private $table_name;

    /**
     * find all the membership by language
     *
     * @param array $data conditions
     * 
     * @return array
     */
    function findAllByLang($data = array())
    {
        global $objDatabase, $_LANGID;

        $condition = '';
        if (!empty($data)) {
            $condition = "AND ".implode("AND ", $data);
        }
        $objResult = $objDatabase->Execute("SELECT membership.*,
                                                   memberLoc.value
                                             FROM `".DBPREFIX."module_{$this->moduleName}_memberships` AS membership
                                             LEFT JOIN `".DBPREFIX."module_{$this->moduleName}_membership_local` AS memberLoc
                                                ON membership.id = memberLoc.entry_id
                                             WHERE memberLoc.lang_id = ".$_LANGID." $condition ORDER BY sorting ASC");

        return $objResult;
    }
}
?>
