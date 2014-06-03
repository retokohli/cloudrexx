<?php

/**
 * Class Document System
 *
 * @copyright   CONTREXX CMS - COMVATION AG
 * @author Comvation Development Team <info@comvation.com>
 * @access public
 * @version 1.0.0
 * @package     contrexx
 * @subpackage  module_jobs
 * @todo        Edit PHP DocBlocks!
 */

/**
 * Class Document System
 *
 * @copyright   CONTREXX CMS - COMVATION AG
 * @author Comvation Development Team <info@comvation.com>
 * @access public
 * @version 1.0.0
 * @package     contrexx
 * @subpackage  module_jobs
 * @todo        Edit PHP DocBlocks!
 */
class jobsLibrary
{
    /**
    * Gets the categorie option menu string
    *
    * @global    object     $objDatabase
    * @global    string     $_LANGID
    * @param     string     $lang
    * @param     string     $selectedOption
    * @return    string     $modulesMenu
    */
    function getCategoryMenu($langId, $selectedCatId="")
    {
        global $objDatabase;

        $strMenu = "";
        $query="SELECT catid,
                       name
                  FROM ".DBPREFIX."module_jobs_categories
                 WHERE lang=".$langId."
              ORDER BY catid";

        $objResult = $objDatabase->Execute($query);
        while (!$objResult->EOF) {
            $selected = "";
            if($selectedCatId==$objResult->fields['catid']){
                $selected = "selected";
            }
            $strMenu .="<option value=\"".$objResult->fields['catid']."\" $selected>".stripslashes($objResult->fields['name'])."</option>\n";
            $objResult->MoveNext();
        }
        return $strMenu;
    }
    
    function getLocationMenu($selectedLocId="")
    {
        global $objDatabase;

        $strMenu = "";
        $query="SELECT id,
                       name
                  FROM ".DBPREFIX."module_jobs_location
                  WHERE 1 
              ORDER BY id";

        $objResult = $objDatabase->Execute($query);
        while (!$objResult->EOF) {
            $selected = "";
            if($selectedLocId==$objResult->fields['id']){
                $selected = "selected";
            }
            $strMenu .="<option value=\"".$objResult->fields['id']."\" $selected>".stripslashes($objResult->fields['name'])."</option>\n";
            $objResult->MoveNext();
        }
        return $strMenu;
    }
}

?>
