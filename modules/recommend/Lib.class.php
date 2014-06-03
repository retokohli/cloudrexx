<?php
/**
 * Recommend module
 *
 * Library for the recommend module
 *
 * @copyright   CONTREXX CMS - COMVATION AG
 * @author Comvation Development Team <info@comvation.com>
 * @access public
 * @version 1.0.0
 * @package     contrexx
 * @subpackage  module_recommend
 * @todo        Edit PHP DocBlocks!
 */

/**
 * Recommend module
 *
 * Library for the recommend module
 *
 * @copyright   CONTREXX CMS - COMVATION AG
 * @author Comvation Development Team <info@comvation.com>
 * @access public
 * @version 1.0.0
 * @package     contrexx
 * @subpackage  module_recommend
 * @todo        Edit PHP DocBlocks!
 */
class RecommendLibrary
{
    /**
     * Get message body
     *
     * Reads the message body out of the database
     *
     * @return string body
     */
    function getMessageBody($lang)
    {
        global $objDatabase;

        $query = "SELECT value FROM ".DBPREFIX."module_recommend WHERE name = 'body' AND lang_id = $lang";
        $objResult = $objDatabase->Execute($query);
        if (!$objResult->EOF) {
            return stripslashes($objResult->fields['value']);
        } else {
            return $this->getStandardBody();
        }
    }

    /**
     * Get subject
     *
     * @return string subject
     */
    function getMessageSubject($lang)
    {
        global $objDatabase, $_FRONTEND_LANGID;

        $query = "SELECT value FROM ".DBPREFIX."module_recommend WHERE name = 'subject' AND lang_id = $lang";
        $objResult = $objDatabase->Execute($query);
        if (!$objResult->EOF) {
            return stripslashes($objResult->fields['value']);
        } else {
            return $this->getStandardSubject();
        }
    }

    /**
     * Get female salutation
     *
     * Returns the saved salutation for females
     */
    function getFemaleSalutation($lang)
    {
        global $objDatabase, $_FRONTEND_LANGID;

        $query = "SELECT value FROM ".DBPREFIX."module_recommend WHERE name = 'salutation_female' AND lang_id = $lang";
        $objResult = $objDatabase->Execute($query);
        if (!$objResult->EOF) {
            return stripslashes($objResult->fields['value']);
        } else {
            return "Dear";
        }
    }


    /**
     * Get female salutation
     *
     * Returns the saved salutation for females
     */
    function getMaleSalutation($lang)
    {
        global $objDatabase, $_FRONTEND_LANGID;

        $query = "SELECT value FROM ".DBPREFIX."module_recommend WHERE name = 'salutation_male' AND lang_id = $lang";
        $objResult = $objDatabase->Execute($query);
        if (!$objResult->EOF) {
            return stripslashes($objResult->fields['value']);
        } else {
            return "Dear";
        }
    }


    /**
     * Get standard body
     *
     * Returns the standard (english) body
     */
    function getStandardBody()
    {
        return "<SALUTATION> <RECEIVER_NAME>

<SENDER_NAME> (<SENDER_MAIL>) recommends following webpage:

<URL>

Comment of <SENDER_NAME>:

<COMMENT>";
    }


    /**
     * Get standard subject
     *
     * Returns the standard (english) subject
     */
    function getStandardSubject()
    {
        return  "Webpage recommendation from <SENDER_NAME>";
    }
}

?>
