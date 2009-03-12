<?php

/**
 * Livecam Library
 * @copyright   CONTREXX CMS - COMVATION AG
 * @author		Comvation Development Team <info@comvation.com>
 * @version		1.0.0
 * @package     contrexx
 * @subpackage  module_livecam
 * @todo        Edit PHP DocBlocks!
 */

/**
 * Livecam Library
 * @copyright   CONTREXX CMS - COMVATION AG
 * @author		Comvation Development Team <info@comvation.com>
 * @access		private
 * @version		1.0.0
 * @package     contrexx
 * @subpackage  module_livecam
 */
class LivecamLibrary
{
	/**
	* Settings array
	*
	* @access public
	* @var array
	*/
	var $arrSettings = array();

	/**
    * Get settings
    *
    * Initialize the settings
    *
    * @access public
    */
    function getSettings()
    {

    	global $objDatabase;

    	$query = "SELECT setname, setvalue FROM ".DBPREFIX."module_livecam_settings";
        $objResult = $objDatabase->Execute($query);
	    while (!$objResult->EOF) {
		    $this->arrSettings[$objResult->fields['setname']] = $objResult->fields['setvalue'];
		    $objResult->MoveNext();
	    }

    }


    /**
     * Get cam settings
     *
     * @param int $id
     * @return array
     */
    public function getCamSettings($id=0)
    {
        global $objDatabase;

        $id = intval($id);

        $query = "  SELECT  id,
                            currentImagePath,
                            archivePath,
                            thumbnailPath,
                            maxImageWidth,
                            thumbMaxSize,
                            shadowboxActivate,
                            showFrom,
                            showTill
                    FROM contrexx_module_livecam";
        if ($id != 0) {
            // select only one
            $query .= " WHERE id = ".$id;
        }
        $result = $objDatabase->Execute($query);
        if ($result === false) {
            //throw new DatabaseError("error getting the camera setting");
            return;
        }

        $ret = array();
        if ($result->RecordCount()) {
            while (!$result->EOF) {
                $cam = Array(
                    "currentImagePath"          => $result->fields['currentImagePath'],
                    "archivePath"               => $result->fields['archivePath'],
                    "thumbnailPath"             => $result->fields['thumbnailPath'],
                    "maxImageWidth"             => $result->fields['maxImageWidth'],
                    "thumbMaxSize"              => $result->fields['thumbMaxSize'],
                    "shadowboxActivate"          => $result->fields['shadowboxActivate'],
                    "showFrom"                  => $result->fields['showFrom'],
                    "showTill"                  => $result->fields['showTill']
                );
                $ret[$result->fields['id']] = $cam;
                $result->MoveNext();
            }
        }

        if ($id == 0) {
            // return all cams
            return $ret;
        } else {
            // there is only one. return that
            return array_pop($ret);
        }
    }
}

?>
