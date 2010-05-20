<?php
/**
 * Media  Directory Settings
 * @copyright   CONTREXX CMS - COMVATION AG
 * @author      Comvation Development Team <info@comvation.com>
 * @package     contrexx
 * @subpackage  module_mediadir
 * @todo        Edit PHP DocBlocks!
 */

/**
 * Includes
 */
require_once ASCMS_MODULE_PATH . '/mediadir/lib/lib.class.php';
require_once ASCMS_MODULE_PATH . '/mediadir/lib/inputfield.class.php';
require_once ASCMS_MODULE_PATH . '/mediadir/lib/form.class.php';


class mediaDirectorySettings extends mediaDirectoryLibrary
{

    /**
     * Constructor
     */
    function __construct()
    {
        parent::getSettings();
        parent::getCommunityGroups();
        parent::getFrontendLanguages();
    }



    function settings_classification($objTpl)
    {
        global $_ARRAYLANG, $_CORELANG;

        $objTpl->addBlockfile('MEDIADIR_SETTINGS_CONTENT', 'settings_content', 'module_mediadir_settings_classification.html');

        $objTpl->setVariable(array(
            'TXT_MEDIADIR_CLASSIFICATION' => $_ARRAYLANG['TXT_MEDIADIR_CLASSIFICATION'],
            'TXT_MEDIADIR_SETTINGS_CLASSIFICATION_POINTS' => $_ARRAYLANG['TXT_MEDIADIR_SETTINGS_CLASSIFICATION_POINTS'],
            'TXT_MEDIADIR_SETTINGS_CLASSIFICATION_POINTS_INFO' => $_ARRAYLANG['TXT_MEDIADIR_SETTINGS_CLASSIFICATION_POINTS_INFO'],
            'TXT_MEDIADIR_SETTINGS_CLASSIFICATION_SEARCH' => $_ARRAYLANG['TXT_MEDIADIR_SETTINGS_CLASSIFICATION_SEARCH'],
            'TXT_MEDIADIR_SETTINGS_CLASSIFICATION_SEARCH_INFO' => $_ARRAYLANG['TXT_MEDIADIR_SETTINGS_CLASSIFICATION_SEARCH_INFO'],
            'TXT_MEDIADIR_SETTINGS_CLASSIFICATION_SEARCH_FROM' => $_ARRAYLANG['TXT_MEDIADIR_SETTINGS_CLASSIFICATION_SEARCH_FROM'],
            'TXT_MEDIADIR_SETTINGS_CLASSIFICATION_SEARCH_TO' => $_ARRAYLANG['TXT_MEDIADIR_SETTINGS_CLASSIFICATION_SEARCH_TO'],
            'TXT_MEDIADIR_SETTINGS_CLASSIFICATION_SEARCH_EXACT' => $_ARRAYLANG['TXT_MEDIADIR_SETTINGS_CLASSIFICATION_SEARCH_EXACT'],
        ));

        if($this->arrSettings['settingsClassificationSearch'] == 1) {
            $strClassificationSearchFrom = 'selected="selected"';
            $strClassificationSearchTo = '';
            $strClassificationSearchExact = '';
        } else if ($this->arrSettings['settingsClassificationSearch'] == 2) {
            $strClassificationSearchFrom = '';
            $strClassificationSearchTo = 'selected="selected"';
            $strClassificationSearchExact = '';
        } else {
            $strClassificationSearchFrom = '';
            $strClassificationSearchTo = '';
            $strClassificationSearchExact = 'selected="selected"';
        }


        $objTpl->setVariable(array(
            'MEDIADIR_SETTINGS_CLASSIFICATION_POINTS' => intval($this->arrSettings['settingsClassificationPoints']),
            'MEDIADIR_SETTINGS_CLASSIFICATION_SEARCH_FROM' => $strClassificationSearchFrom,
            'MEDIADIR_SETTINGS_CLASSIFICATION_SEARCH_TO' => $strClassificationSearchTo,
            'MEDIADIR_SETTINGS_CLASSIFICATION_SEARCH_EXACT' => $strClassificationSearchExact,
        ));
    }



    function settings_votes($objTpl)
    {
        global $_ARRAYLANG, $_CORELANG, $objDatabase;

        $objTpl->addBlockfile('MEDIADIR_SETTINGS_CONTENT', 'settings_content', 'module_mediadir_settings_comments_votes.html');

        if(isset($_GET['restore'])){
            if($_GET['restore'] == 'voting') {
                $objDatabase->Execute("TRUNCATE TABLE ".DBPREFIX."module_mediadir_votes");
            }

            if($_GET['restore'] == 'comments') {
                $objDatabase->Execute("TRUNCATE TABLE ".DBPREFIX."module_mediadir_comments");
            }
        }

        $objTpl->setVariable(array(
            'TXT_MEDIADIR_VOTES' => $_ARRAYLANG['TXT_MEDIADIR_VOTES'],
            'TXT_MEDIADIR_SETTINGS_ALLOW_VOTES' => $_ARRAYLANG['TXT_MEDIADIR_SETTINGS_ALLOW_VOTES'],
            'TXT_MEDIADIR_SETTINGS_VOTE_ONLY_COMMUNITY' => $_ARRAYLANG['TXT_MEDIADIR_SETTINGS_VOTE_ONLY_COMMUNITY'],
            'TXT_MEDIADIR_SETTINGS_DELETE_ALL_VOTES' => $_ARRAYLANG['TXT_MEDIADIR_SETTINGS_DELETE_ALL_VOTES'],
            'TXT_MEDIADIR_COMMENTS' => $_ARRAYLANG['TXT_MEDIADIR_COMMENTS'],
            'TXT_MEDIADIR_CONFIRM_DELETE_DATA' => $_ARRAYLANG['TXT_MEDIADIR_CONFIRM_DELETE_DATA'],
            'TXT_MEDIADIR_ACTION_IS_IRREVERSIBLE' => $_ARRAYLANG['TXT_MEDIADIR_ACTION_IS_IRREVERSIBLE'],
            'TXT_MEDIADIR_SETTINGS_ALLOW_COMMENTS' => $_ARRAYLANG['TXT_MEDIADIR_SETTINGS_ALLOW_COMMENTS'],
            'TXT_MEDIADIR_SETTINGS_COMMENT_ONLY_COMMUNITY' => $_ARRAYLANG['TXT_MEDIADIR_SETTINGS_COMMENT_ONLY_COMMUNITY'],
            'TXT_MEDIADIR_SETTINGS_DELETE_ALL_COMMENTS' => $_ARRAYLANG['TXT_MEDIADIR_SETTINGS_DELETE_ALL_COMMENTS'],
        ));

        if($this->arrSettings['settingsAllowVotes'] == 1) {
            $strAllowVotesOn = 'checked="checked"';
            $strAllowVotesOff = '';
        } else {
            $strAllowVotesOn = '';
            $strAllowVotesOff = 'checked="checked"';
        }

        if($this->arrSettings['settingsVoteOnlyCommunity'] == 1) {
            $strVoteOnlyCommunityOn = 'checked="checked"';
            $strVoteOnlyCommunityOff = '';
        } else {
            $strVoteOnlyCommunityOn = '';
            $strVoteOnlyCommunityOff = 'checked="checked"';
        }

        if($this->arrSettings['settingsAllowComments'] == 1) {
            $strAllowCommentsOn = 'checked="checked"';
            $strAllowCommentsOff = '';
        } else {
            $strAllowCommentsOn = '';
            $strAllowCommentsOff = 'checked="checked"';
        }

        if($this->arrSettings['settingsCommentOnlyCommunity'] == 1) {
            $strCommentOnlyCommunityOn = 'checked="checked"';
            $strCommentOnlyCommunityOff = '';
        } else {
            $strCommentOnlyCommunityOn = '';
            $strCommentOnlyCommunityOff = 'checked="checked"';
        }

        $objTpl->setVariable(array(
            'MEDIADIR_SETTINGS_ALLOW_VOTES_ON' => $strAllowVotesOn,
            'MEDIADIR_SETTINGS_ALLOW_VOTES_OFF' => $strAllowVotesOff,
            'MEDIADIR_SETTINGS_VOTE_ONLY_COMMUNITY_ON' => $strVoteOnlyCommunityOn,
            'MEDIADIR_SETTINGS_VOTE_ONLY_COMMUNITY_OFF' => $strVoteOnlyCommunityOff,
            'MEDIADIR_SETTINGS_ALLOW_COMMENTS_ON' => $strAllowCommentsOn,
            'MEDIADIR_SETTINGS_ALLOW_COMMENTS_OFF' => $strAllowCommentsOff,
            'MEDIADIR_SETTINGS_COMMENT_ONLY_COMMUNITY_ON' => $strCommentOnlyCommunityOn,
            'MEDIADIR_SETTINGS_COMMENT_ONLY_COMMUNITY_OFF' => $strCommentOnlyCommunityOff,
        ));
    }


    function settings_map($objTpl)
    {
        global $_ARRAYLANG, $_CORELANG, $_CONFIG, $objDatabase;

        parent::getSettings();

        $objTpl->addBlockfile('MEDIADIR_SETTINGS_CONTENT', 'settings_content', 'module_mediadir_settings_map.html');

        if(empty($_CONFIG['googleMapsAPIKey'])) {
            $objTpl->hideBlock('mediadirGoogleMap');

            $objDatabase->Execute("UPDATE ".DBPREFIX."module_mediadir_inputfield_types SET `active`='0' WHERE `name`='google_map'");

            $objTpl->setVariable(array(
                'TXT_MEDIADIR_SETTINGS_GOOGLE_NO_KEY' => $_ARRAYLANG['TXT_MEDIADIR_SETTINGS_GOOGLE_NO_KEY'],
                'TXT_MEDIADIR_SETTINGS_GOOGLE_PLEASE_ENTER_KEY_IN_GLOBAL_SETTINGS' => $_ARRAYLANG['TXT_MEDIADIR_SETTINGS_GOOGLE_PLEASE_ENTER_KEY_IN_GLOBAL_SETTINGS'],
            ));
        } else {
            $objTpl->hideBlock('mediadirGoogleMapNoKey');

            $objDatabase->Execute("UPDATE ".DBPREFIX."module_mediadir_inputfield_types SET `active`='1' WHERE `name`='google_map'");

            $objTpl->setVariable(array(
                'TXT_MEDIADIR_SETTINGS_GOOGLE_START_POSITION' => $_ARRAYLANG['TXT_MEDIADIR_GOOGLE'],
                'TXT_MEDIADIR_SETTINGS_GOOGLE_START_POSITION' => $_ARRAYLANG['TXT_MEDIADIR_SETTINGS_GOOGLE_START_POSITION'],
            ));

            $strMapId       = 'settingsGoogleMap_map';
            $strLonId       = 'settingsGoogleMap_lon';
            $strLatId       = 'settingsGoogleMap_lat';
            $strZoomId      = 'settingsGoogleMap_zoom';
            $strStreetId    = 'settingsGoogleMap_street';
            $strZipId       = 'settingsGoogleMap_zip';
            $strCityId      = 'settingsGoogleMap_city';
            $strKey         = $_CONFIG['googleMapsAPIKey'];

            $arrValues = explode(',', $this->arrSettings['settingsGoogleMapStartposition']);

            $strValueLon = $arrValues[0];
            $strValueLat = $arrValues[1];
            $strValueZoom = $arrValues[2];

            $strGoogleMap .= '<table cellpadding="0" cellspacing="0" border="0" class="mediadirTableGoogleMap">';
            $strGoogleMap .= '<tr><td style="border: 0px;">'.$_ARRAYLANG['TXT_MEDIADIR_GOOGLE_MAP_STREET'].':&nbsp;&nbsp;</td><td style="border: 0px; padding-bottom: 2px;"><input type="text" name="settingsGoogleMap[street]" id="'.$strStreetId.'" class="mediadirInputfieldGoogleMapLarge" value="" onfocus="this.select();" /></td></tr>';
            $strGoogleMap .= '<tr><td style="border: 0px;">'.$_ARRAYLANG['TXT_MEDIADIR_GOOGLE_MAP_CITY'].':&nbsp;&nbsp;</td><td style="border: 0px; padding-bottom: 2px;"><input type="text" name="settingsGoogleMap[place]" id="'.$strZipId.'" class="mediadirInputfieldGoogleMapLarge" value="" onfocus="this.select();" /></td></tr>';
            $strGoogleMap .= '<tr><td style="border: 0px;">'.$_ARRAYLANG['TXT_MEDIADIR_GOOGLE_MAP_ZIP'].':&nbsp;&nbsp;</td><td style="border: 0px; padding-bottom: 2px;"><input type="text" name="settingsGoogleMap[zip]" id="'.$strCityId.'" class="mediadirInputfieldGoogleMapSmall" value="" onfocus="this.select();" /></td></tr>';
            $strGoogleMap .= '<tr><td style="border: 0px;"><br /></td><td style="border: 0px;"><input type="button" onclick="searchAddress();" name="settingsGoogleMap[search]" id="mediadirInputfield_'.$intId.'_search" value="'.$_CORELANG['TXT_SEARCH'].'" /></td></tr>';
            $strGoogleMap .= '<tr><td style="border: 0px;" coldpan="2"><br /></td></tr>';
            $strGoogleMap .= '<tr><td style="border: 0px;">'.$_ARRAYLANG['TXT_MEDIADIR_GOOGLE_MAP_LON'].':&nbsp;&nbsp;</td><td style="border: 0px; padding-bottom: 2px;"><input type="text" name="settingsGoogleMap[lon]" id="'.$strLonId.'" class="mediadirInputfieldGoogleMapLarge" value="'.$strValueLon.'" onfocus="this.select();" /></td></tr>';
            $strGoogleMap .= '<tr><td style="border: 0px;">'.$_ARRAYLANG['TXT_MEDIADIR_GOOGLE_MAP_LAT'].':&nbsp;&nbsp;</td><td style="border: 0px; padding-bottom: 2px;"><input type="text" name="settingsGoogleMap[lat]" id="'.$strLatId.'" class="mediadirInputfieldGoogleMapLarge" value="'.$strValueLat.'" onfocus="this.select();" /></td></tr>';
            $strGoogleMap .= '<tr><td style="border: 0px;">'.$_ARRAYLANG['TXT_MEDIADIR_GOOGLE_MAP_ZOOM'].':&nbsp;&nbsp;</td><td style="border: 0px; padding-bottom: 2px;"><input type="text" name="settingsGoogleMap[zoom]" id="'.$strZoomId.'" class="mediadirInputfieldGoogleMapSmall" value="'.$strValueZoom.'" onfocus="this.select();" /></td></tr>';
            $strGoogleMap .= '</table><br />';
            $strGoogleMap .= '<div id="'.$strMapId.'" style="border: solid 1px #0A50A1; width: 418px; height: 300px;"></div>';
            $strGoogleMap .= <<<EOF
<script src="http://maps.google.com/maps?file=api&amp;v=2&amp;key=$strKey" type="text/javascript"></script>
<script type="text/javascript">
//<![CDATA[
var elZoom = document.getElementById('$strZoomId');
var elLon = document.getElementById('$strLonId');
var elLat = document.getElementById('$strLatId');

var elStreet = document.getElementById('$strStreetId');
var elZip = document.getElementById('$strZipId');
var elCity = document.getElementById('$strCityId');

var map;
var point;
var geocoder;
var old_marker = null;

function initialize() {
    if (GBrowserIsCompatible()) {
        map = new GMap2(document.getElementById("$strMapId"));

        map.setCenter(new GLatLng($strValueLon, $strValueLat), $strValueZoom);
        map.addControl(new GLargeMapControl());

        if($strValueLon != 0 && $strValueLon != 0) {
            var marker = new GMarker(new GLatLng($strValueLon, $strValueLat), $strValueZoom);
            map.addOverlay(marker);
            old_marker = marker;
        }

        geocoder = new GClientGeocoder();

        GEvent.addListener(map,"click", function(overlay,latlng) {
            if (latlng) {
                if (old_marker != null) {
                    map.removeOverlay(old_marker);
                }

                var marker = new GMarker(latlng);

                point = latlng;
                setAttributes();

                map.addOverlay(marker);

                old_marker = marker;
            }
        });

        GEvent.addListener(map, "moveend", function() {
            elZoom.value = map.getZoom();
        });
    }
}

function searchAddress() {
    var address =  elStreet.value + " " + elZip.value + " " + elCity.value;

    if (geocoder) {
        geocoder.getLatLng(
        address,
        function(latlng) {
            if (!latlng) {
                alert(address + " not found");
            } else {


                map.setCenter(latlng, 15);

                if (old_marker != null) {
                    map.removeOverlay(old_marker);
                }

                var marker = new GMarker(latlng);

                point = latlng;
                setAttributes();

                map.addOverlay(marker);

                old_marker = marker;
            }
        }
    );
    }
}

function setAttributes() {
    var lon = point.y.toString();
    var lat = point.x.toString();

    elZoom.value = map.getZoom();
    elLon.value = lon;
    elLat.value = lat;
}

window.onload = initialize();
//]]>
</script>
EOF;
            if($this->arrSettings['settingsGoogleMapAllowKml'] == 1) {
                $strAllowKmlOn = 'checked="checked"';
                $strAllowKmlOff = '';
            } else {
                $strAllowKmlOn = '';
                $strAllowKmlOff = 'checked="checked"';
            }

            if($this->arrSettings['settingsGoogleMapType'] == 0) {
                $strMapyType0 = 'selected="selected"';
                $strMapyType1 = '';
                $strMapyType2 = '';
            } else if($this->arrSettings['settingsGoogleMapType'] == 1) {
                $strMapyType0 = '';
                $strMapyType1 = 'selected="selected"';
                $strMapyType2 = '';
            } else  {
                $strMapyType0 = '';
                $strMapyType1 = '';
                $strMapyType2 = 'selected="selected"';
            }

            $strSelectMapyType .= '<option value="0" '.$strMapyType0.'>'.$_ARRAYLANG['TXT_MEDIADIR_SETTINGS_GOOGLE_MAP_TYPE_MAP'].'</option>';
            $strSelectMapyType .= '<option value="1" '.$strMapyType1.'>'.$_ARRAYLANG['TXT_MEDIADIR_SETTINGS_GOOGLE_MAP_TYPE_SATELLITE'].'</option>';
            $strSelectMapyType .= '<option value="2" '.$strMapyType2.'>'.$_ARRAYLANG['TXT_MEDIADIR_SETTINGS_GOOGLE_MAP_TYPE_HYBRID'].'</option>';

            $objTpl->setVariable(array(
                'MEDIADIR_SETTINGS_GOOGLE_START_POSITION' => $strGoogleMap,
                'TXT_MEDIADIR_SETTINGS_GOOGLE_ALLOW_KML' => $_ARRAYLANG['TXT_MEDIADIR_SETTINGS_GOOGLE_ALLOW_KML'],
                'TXT_MEDIADIR_SETTINGS_GOOGLE_MAP_TYPE' => $_ARRAYLANG['TXT_MEDIADIR_SETTINGS_GOOGLE_MAP_TYPE'],
                'MEDIADIR_SETTINGS_GOOGLE_ALLOW_KML_ON' => $strAllowKmlOn,
                'MEDIADIR_SETTINGS_GOOGLE_ALLOW_KML_OFF' => $strAllowKmlOff,
                'MEDIADIR_SETTINGS_GOOGLE_MAP_TYPE' => $strSelectMapyType,
            ));
        }
    }


    function settings_save_map($arrData)
    {
        global $_ARRAYLANG, $_CORELANG, $objDatabase;

        $strValueLon = contrexx_addslashes($arrData['settingsGoogleMap']['lon']);
        $strValueLat = contrexx_addslashes($arrData['settingsGoogleMap']['lat']);
        $strValueZoom = contrexx_addslashes($arrData['settingsGoogleMap']['zoom']);

        $objRSSaveGoogle = $objDatabase->Execute("
                UPDATE
                    ".DBPREFIX."module_mediadir_settings
                SET
                    value='".$strValueLon.",".$strValueLat.",".$strValueZoom."'
                WHERE
                    name='settingsGoogleMapStartposition'
                ");
        if ($objRSSaveGoogle === false) {
            return false;
        }

        $objRSSaveGoogle = $objDatabase->Execute("
                UPDATE
                    ".DBPREFIX."module_mediadir_settings
                SET
                    value='".intval($arrData['settingsGoogleMapType'])."'
                WHERE
                    name='settingsGoogleMapType'
                ");
        if ($objRSSaveGoogle === false) {
            return false;
        }

        $objRSSaveGoogle = $objDatabase->Execute("
                UPDATE
                    ".DBPREFIX."module_mediadir_settings
                SET
                    value='".intval($arrData['settingsGoogleMapAllowKml'])."'
                WHERE
                    name='settingsGoogleMapAllowKml'
                ");
        if ($objRSSaveGoogle === false) {
            return false;
        }

        return true;
    }



    function settings_files($objTpl)
    {
        global $_ARRAYLANG, $_CORELANG;

        $objTpl->addBlockfile('MEDIADIR_SETTINGS_CONTENT', 'settings_content', 'module_mediadir_settings_files.html');

        $objTpl->setVariable(array(
            'TXT_MEDIADIR_SETTINGS_PICS_AND_FILES' => $_ARRAYLANG['TXT_MEDIADIR_PICS_AND_FILES'],
            'TXT_MEDIADIR_SETTINGS_THUMB_SIZE' => $_ARRAYLANG['TXT_MEDIADIR_SETTINGS_THUMB_SIZE'],
            'TXT_MEDIADIR_SETTINGS_NUM_PICS_PER_GALLERY' => $_ARRAYLANG['TXT_MEDIADIR_SETTINGS_NUM_PICS_PER_GALLERY'],
            'TXT_MEDIADIR_SETTINGS_ENCRYPT_FILENAME' => $_ARRAYLANG['TXT_MEDIADIR_SETTINGS_ENCRYPT_FILENAME'],
            'TXT_MEDIADIR_SETTINGS_THUMB_SIZE_INFO' => $_ARRAYLANG['TXT_MEDIADIR_SETTINGS_THUMB_SIZE_INFO'],
            'TXT_MEDIADIR_SETTINGS_NUM_PICS_PER_GALLERY_INFO' => $_ARRAYLANG['TXT_MEDIADIR_SETTINGS_NUM_PICS_PER_GALLERY_INFO'],
            'TXT_MEDIADIR_SETTINGS_ENCRYPT_FILENAME_INFO' => $_ARRAYLANG['TXT_MEDIADIR_SETTINGS_ENCRYPT_FILENAME_INFO'],
        ));

        if($this->arrSettings['settingsEncryptFilenames'] == 1) {
            $strEncryptFilenamesOn = 'checked="checked"';
            $strEncryptFilenamesOff = '';
        } else {
            $strEncryptFilenamesOn = '';
            $strEncryptFilenamesOff = 'checked="checked"';
        }


        $objTpl->setVariable(array(
            'MEDIADIR_SETTINGS_THUMB_SIZE' => intval($this->arrSettings['settingsThumbSize']),
            'MEDIADIR_SETTINGS_NUM_PICS_PER_GALLERY' => $this->arrSettings['settingsNumGalleryPics'],
            'MEDIADIR_SETTINGS_ENCRYPT_FILENAMES_ON' => $strEncryptFilenamesOn,
            'MEDIADIR_SETTINGS_ENCRYPT_FILENAMES_OFF' => $strEncryptFilenamesOff,
        ));
    }



    function settings_entries($objTpl)
    {
        global $_ARRAYLANG, $_CORELANG;

        $objTpl->addBlockfile('MEDIADIR_SETTINGS_CONTENT', 'settings_content', 'module_mediadir_settings_entries.html');

        $objTpl->setGlobalVariable(array(
            'TXT_MEDIADIR_SETTINGS_CONFIRM_NEW_ENTRIES' => $_ARRAYLANG['TXT_MEDIADIR_SETTINGS_CONFIRM_NEW_ENTRIES'],
            'TXT_MEDIADIR_SETTINGS_CONFIRM_NEW_ENTRIES_INFO' => $_ARRAYLANG['TXT_MEDIADIR_SETTINGS_CONFIRM_NEW_ENTRIES_INFO'],
            'TXT_MEDIADIR_SETTINGS_NUM_ENTRIES_PER_GROUP' => $_ARRAYLANG['TXT_MEDIADIR_SETTINGS_NUM_ENTRIES_PER_GROUP'],
            'TXT_MEDIADIR_SETTINGS_NUM_ENTRIES_PER_GROUP_INFO' => $_ARRAYLANG['TXT_MEDIADIR_SETTINGS_NUM_ENTRIES_PER_GROUP_INFO'],
            'TXT_MEDIADIR_SETTINGS_ENTRIES' => $_ARRAYLANG['TXT_MEDIADIR_ENTRIES'],
            'TXT_MEDIADIR_SETTINGS_NUM' => $_ARRAYLANG['TXT_MEDIADIR_NUM'],
            'TXT_MEDIADIR_SETTINGS_COMMUNITY_GROUP' => $_ARRAYLANG['TXT_MEDIADIR_COMMUNITY_GROUP'],
            'TXT_MEDIADIR_SETTINGS_NO_COMMUNITY_GROUPS' => $_ARRAYLANG['TXT_MEDIADIR_NO_COMMUNITY_GROUPS'],
            'TXT_MEDIADIR_SETTINGS_CONFIRM_UPDATED_ENTRIES' => $_ARRAYLANG['TXT_MEDIADIR_SETTINGS_CONFIRM_UPDATED_ENTRIES'],
            'TXT_MEDIADIR_SETTINGS_CONFIRM_UPDATED_ENTRIES_INFO' => $_ARRAYLANG['TXT_MEDIADIR_SETTINGS_CONFIRM_UPDATED_ENTRIES_INFO'],
            'TXT_MEDIADIR_SETTINGS_COUNT_ENTRIES' => $_ARRAYLANG['TXT_MEDIADIR_SETTINGS_COUNT_ENTRIES'],
            'TXT_MEDIADIR_SETTINGS_COUNT_ENTRIES_INFO' => $_ARRAYLANG['TXT_MEDIADIR_SETTINGS_COUNT_ENTRIES_INFO'],
            'TXT_MEDIADIR_SETTINGS_ALLOW_ADD_ENTRIES' => $_ARRAYLANG['TXT_MEDIADIR_SETTINGS_ALLOW_ADD_ENTRIES'],
            'TXT_MEDIADIR_SETTINGS_ALLOW_ADD_ENTRIES_INFO' => $_ARRAYLANG['TXT_MEDIADIR_SETTINGS_ALLOW_ADD_ENTRIES_INFO'],
            'TXT_MEDIADIR_SETTINGS_ADD_ONLY_COMMUNITY' => $_ARRAYLANG['TXT_MEDIADIR_SETTINGS_ADD_ONLY_COMMUNITY'],
            'TXT_MEDIADIR_SETTINGS_ADD_ONLY_COMMUNITY_INFO' => $_ARRAYLANG['TXT_MEDIADIR_SETTINGS_ADD_ONLY_COMMUNITY_INFO'],
            'TXT_MEDIADIR_SETTINGS_ALLOW_EDIT_ENTRIES' => $_ARRAYLANG['TXT_MEDIADIR_SETTINGS_ALLOW_EDIT_ENTRIES'],
            'TXT_MEDIADIR_SETTINGS_ALLOW_EDIT_ENTRIES_INFO' => $_ARRAYLANG['TXT_MEDIADIR_SETTINGS_ALLOW_EDIT_ENTRIES_INFO'],
            'TXT_MEDIADIR_SETTINGS_ALLOW_DEL_ENTRIES' => $_ARRAYLANG['TXT_MEDIADIR_SETTINGS_ALLOW_DEL_ENTRIES'],
            'TXT_MEDIADIR_SETTINGS_ALLOW_DEL_ENTRIES_INFO' => $_ARRAYLANG['TXT_MEDIADIR_SETTINGS_ALLOW_DEL_ENTRIES_INFO'],
            'TXT_MEDIADIR_LATEST_ENTRIES' => $_ARRAYLANG['TXT_MEDIADIR_LATEST_ENTRIES'],
            'TXT_MEDIADIR_POPULAR_HITS' => $_ARRAYLANG['TXT_MEDIADIR_POPULAR_HITS'],
            'TXT_MEDIADIR_SETTINGS_LATEST_NUM_XML' => $_ARRAYLANG['TXT_MEDIADIR_SETTINGS_LATEST_NUM_XML'],
            'TXT_MEDIADIR_SETTINGS_LATEST_NUM_OVERVIEW' => $_ARRAYLANG['TXT_MEDIADIR_SETTINGS_LATEST_NUM_OVERVIEW'],
            'TXT_MEDIADIR_SETTINGS_LATEST_NUM_BACKEND' => $_ARRAYLANG['TXT_MEDIADIR_SETTINGS_LATEST_NUM_BACKEND'],
            'TXT_MEDIADIR_SETTINGS_LATEST_NUM_FRONTEND' => $_ARRAYLANG['TXT_MEDIADIR_SETTINGS_LATEST_NUM_FRONTEND'],
            'TXT_MEDIADIR_SETTINGS_POPULAR_NUM_FRONTEND' => $_ARRAYLANG['TXT_MEDIADIR_SETTINGS_POPULAR_NUM_FRONTEND'],
            'TXT_MEDIADIR_SETTINGS_POPULAR_NUM_RESTORE' => $_ARRAYLANG['TXT_MEDIADIR_SETTINGS_POPULAR_NUM_RESTORE'],
            'TXT_MEDIADIR_SETTINGS_LATEST_NUM_HEADLINES' => $_ARRAYLANG['TXT_MEDIADIR_SETTINGS_LATEST_NUM_HEADLINES'],
            'TXT_MEDIADIR_SETTINGS_SHOW_ENTRIES_IN_ALL_LANG' => $_ARRAYLANG['TXT_MEDIADIR_SETTINGS_SHOW_ENTRIES_IN_ALL_LANG'],
            'TXT_MEDIADIR_SETTINGS_SHOW_ENTRIES_IN_ALL_LANG_INFO' => $_ARRAYLANG['TXT_MEDIADIR_SETTINGS_SHOW_ENTRIES_IN_ALL_LANG_INFO'],
            'TXT_MEDIADIR_SETTINGS_PAGING_NUM_ENTRIES' => $_ARRAYLANG['TXT_MEDIADIR_SETTINGS_PAGING_NUM_ENTRIES'],
            'TXT_MEDIADIR_SETTINGS_PAGING_NUM_ENTRIES_INFO' => $_ARRAYLANG['TXT_MEDIADIR_SETTINGS_PAGING_NUM_ENTRIES_INFO'],
            'TXT_MEDIADIR_SETTINGS_DISPLAYDURATION' => $_ARRAYLANG['TXT_MEDIADIR_SETTINGS_DEFAULT_DISPLAYDURATION'],
            'TXT_MEDIADIR_DISPLAY_DURATION_ALWAYS' => $_ARRAYLANG['TXT_MEDIADIR_DISPLAYDURATION_ALWAYS'],
            'TXT_MEDIADIR_DISPLAY_DURATION_PERIOD' => $_ARRAYLANG['TXT_MEDIADIR_DISPLAYDURATION_PERIOD'],
            'TXT_MEDIADIR_DISPLAY_DURATION_FROM' => $_CORELANG['TXT_FROM'],
            'TXT_MEDIADIR_DISPLAY_DURATION_TO' => $_CORELANG['TXT_TO'],
            'TXT_MEDIADIR_SETTINGS_NOTIFICATION_DISPLAYDURATION' => $_ARRAYLANG['TXT_MEDIADIR_SETTINGS_NOTIFICATION_DISPLAYDURATION'],
            'TXT_MEDIADIR_SETTINGS_DISPLAYDURATION_NOTIFICATION_DAYSBEFOR' => $_ARRAYLANG['TXT_MEDIADIR_SETTINGS_NOTIFICATION_DISPLAYDURATION_DAYSBEFOR'],
            'TXT_MEDIADIR_SETTINGS_DISPLAYDURATION_VALUE_TYPE_DAY' => $_ARRAYLANG['TXT_MEDIADIR_SETTINGS_DISPLAYDURATION_VALUE_TYPE_DAY'],
            'TXT_MEDIADIR_SETTINGS_DISPLAYDURATION_VALUE_TYPE_MONTH' => $_ARRAYLANG['TXT_MEDIADIR_SETTINGS_DISPLAYDURATION_VALUE_TYPE_MONTH'],
            'TXT_MEDIADIR_SETTINGS_DISPLAYDURATION_VALUE_TYPE_YEAR' => $_ARRAYLANG['TXT_MEDIADIR_SETTINGS_DISPLAYDURATION_VALUE_TYPE_YEAR'],
            'TXT_MEDIADIR_SETTINGS_TRANSLATION_STATUS' => $_ARRAYLANG['TXT_MEDIADIR_SETTINGS_TRANSLATION_STATUS'],
            'TXT_MEDIADIR_SETTINGS_TRANSLATION_STATUS_INFO' => $_ARRAYLANG['TXT_MEDIADIR_SETTINGS_TRANSLATION_STATUS_INFO'],
        ));

        if($this->arrSettings['settingsConfirmNewEntries'] == 1) {
            $strConfirmEntriesOn = 'checked="checked"';
            $strConfirmEntriesOff = '';
        } else {
            $strConfirmEntriesOn = '';
            $strConfirmEntriesOff = 'checked="checked"';
        }

        if($this->arrSettings['settingsConfirmUpdatedEntries'] == 1) {
            $strConfirmUpdatedEntriesOn = 'checked="checked"';
            $strConfirmUpdatedEntriesOff = '';
        } else {
            $strConfirmUpdatedEntriesOn = '';
            $strConfirmUpdatedEntriesOff = 'checked="checked"';
        }

        if($this->arrSettings['settingsCountEntries'] == 1) {
            $strCountEntriesOn = 'checked="checked"';
            $strCountEntriesOff = '';
        } else {
            $strCountEntriesOn = '';
            $strCountEntriesOff = 'checked="checked"';
        }

        if($this->arrSettings['settingsShowEntriesInAllLang'] == 1) {
            $strShowEntriesInAllLangOn = 'checked="checked"';
            $strShowEntriesInAllLangOff = '';
        } else {
            $strShowEntriesInAllLangOn = '';
            $strShowEntriesInAllLangOff = 'checked="checked"';
        }

        if($this->arrSettings['settingsAllowAddEntries'] == 1) {
            $strAddEntriesOn = 'checked="checked"';
            $strAddEntriesOff = '';
        } else {
            $strAddEntriesOn = '';
            $strAddEntriesOff = 'checked="checked"';
        }

        if($this->arrSettings['settingsAddEntriesOnlyCommunity'] == 1) {
            $strAddCommunityOn = 'checked="checked"';
            $strAddCommunityOff = '';
        } else {
            $strAddCommunityOn = '';
            $strAddCommunityOff = 'checked="checked"';
        }

        if($this->arrSettings['settingsAllowEditEntries'] == 1) {
            $strEditEntriesOn = 'checked="checked"';
            $strEditEntriesOff = '';
        } else {
            $strEditEntriesOn = '';
            $strEditEntriesOff = 'checked="checked"';
        }

        if($this->arrSettings['settingsAllowDelEntries'] == 1) {
            $strDelEntriesOn = 'checked="checked"';
            $strDelEntriesOff = '';
        } else {
            $strDelEntriesOn = '';
            $strDelEntriesOff = 'checked="checked"';
        }
        
        if($this->arrSettings['settingsTranslationStatus'] == 1) {
            $strTransStatusOn = 'checked="checked"';
            $strTransStatusOff = '';
        } else {
            $strTransStatusOn = '';
            $strTransStatusOff = 'checked="checked"';
        }
        
        if(intval($this->arrSettings['settingsEntryDisplaydurationType']) == 1) {
            $strDisplaydurationAlways = 'selected="selected"';
            $strDisplaydurationPeriod = '';
            $strDisplaydurationShowPeriod = 'none';
            $intDisplaydurationValue = 0;
        } else {
            $strDisplaydurationAlways = '';
            $strDisplaydurationPeriod = 'selected="selected"';
            $strDisplaydurationShowPeriod = 'inline';
            $intDisplaydurationValue = intval($this->arrSettings['settingsEntryDisplaydurationValue']);
            
            switch (intval($this->arrSettings['settingsEntryDisplaydurationValueType'])) {
            	case 1:
	                $strDisplaydurationValueTypeDay = 'selected="selected"';
	                $strDisplaydurationValueTypeMonth = '';
	                $strDisplaydurationValueTypeYear = '';
            		break;
                case 2:
                    $strDisplaydurationValueTypeDay = '';
                    $strDisplaydurationValueTypeMonth = 'selected="selected"';
                    $strDisplaydurationValueTypeYear = '';
                    break;
                case 3:
                    $strDisplaydurationValueTypeDay = '';
                    $strDisplaydurationValueTypeMonth = '';
                    $strDisplaydurationValueTypeYear = 'selected="selected"';
                    break;
            }
        }
        
        if(intval($this->arrSettings['settingsEntryDisplaydurationNotification']) == 0) {
            $strDisplaydurationNotificationOff = 'selected="selected"';
            $strDisplaydurationNotificationOn = '';
            $strDisplaydurationNotificationValue = 0;
            $strDisplaydurationNotificationShowDaybefore = 'none';
        } else {
            $strDisplaydurationNotificationOff = '';
            $strDisplaydurationNotificationOn = 'selected="selected"';
            $strDisplaydurationNotificationValue = intval($this->arrSettings['settingsEntryDisplaydurationNotification']);
            $strDisplaydurationNotificationShowDaybefore = 'inline';
        }

        $objTpl->setVariable(array(
            'MEDIADIR_SETTINGS_CONFIRM_NEW_ENTRIES_ON' => $strConfirmEntriesOn,
            'MEDIADIR_SETTINGS_CONFIRM_NEW_ENTRIES_OFF' => $strConfirmEntriesOff,
            'MEDIADIR_SETTINGS_CONFIRM_UPDATED_ENTRIES_ON' => $strConfirmUpdatedEntriesOn,
            'MEDIADIR_SETTINGS_CONFIRM_UPDATED_ENTRIES_OFF' => $strConfirmUpdatedEntriesOff,
            'MEDIADIR_SETTINGS_COUNT_ENTRIES_ON' => $strCountEntriesOn,
            'MEDIADIR_SETTINGS_COUNT_ENTRIES_OFF' => $strCountEntriesOff,
            'MEDIADIR_SETTINGS_ALLOW_ADD_ENTRIES_OFF' => $strAddEntriesOff,
            'MEDIADIR_SETTINGS_ALLOW_ADD_ENTRIES_ON' => $strAddEntriesOn,
            'MEDIADIR_SETTINGS_ADD_ONLY_COMMUNITY_OFF' => $strAddCommunityOff,
            'MEDIADIR_SETTINGS_ADD_ONLY_COMMUNITY_ON' => $strAddCommunityOn,
            'MEDIADIR_SETTINGS_ALLOW_EDIT_ENTRIES_OFF' => $strEditEntriesOff,
            'MEDIADIR_SETTINGS_ALLOW_EDIT_ENTRIES_ON' => $strEditEntriesOn,
            'MEDIADIR_SETTINGS_ALLOW_DEL_ENTRIES_OFF' => $strDelEntriesOff,
            'MEDIADIR_SETTINGS_ALLOW_DEL_ENTRIES_ON' => $strDelEntriesOn,
            'MEDIADIR_SETTINGS_SHOW_ENTRIES_IN_ALL_LANG_OFF' => $strShowEntriesInAllLangOff,
            'MEDIADIR_SETTINGS_SHOW_ENTRIES_IN_ALL_LANG_ON' => $strShowEntriesInAllLangOn,
            'MEDIADIR_SETTINGS_LATEST_NUM_XML' => intval($this->arrSettings['settingsLatestNumXML']),
            'MEDIADIR_SETTINGS_LATEST_NUM_OVERVIEW' => intval($this->arrSettings['settingsLatestNumOverview']),
            'MEDIADIR_SETTINGS_LATEST_NUM_BACKEND' => intval($this->arrSettings['settingsLatestNumBackend']),
            'MEDIADIR_SETTINGS_LATEST_NUM_FRONTEND' => intval($this->arrSettings['settingsLatestNumFrontend']),
            'MEDIADIR_SETTINGS_POPULAR_NUM_FRONTEND' => intval($this->arrSettings['settingsPopularNumFrontend']),
            'MEDIADIR_SETTINGS_POPULAR_NUM_RESTORE' => intval($this->arrSettings['settingsPopularNumRestore']),
            'MEDIADIR_SETTINGS_LATEST_NUM_HEADLINES' => intval($this->arrSettings['settingsLatestNumHeadlines']),
            'MEDIADIR_SETTINGS_PAGING_NUM_ENTRIES' => intval($this->arrSettings['settingsPagingNumEntries']),
            'MEDIADIR_SETTINGS_DISPLAYDURATION_SELECT_ALWAYS' => $strDisplaydurationAlways,
            'MEDIADIR_SETTINGS_DISPLAYDURATION_SELECT_PERIOD' => $strDisplaydurationPeriod,
            'MEDIADIR_SETTINGS_DISPLAYDURATION_SHOW_PERIOD' => $strDisplaydurationShowPeriod,
            'MEDIADIR_SETTINGS_DISPLAYDURATION_VALUE' => $intDisplaydurationValue,
            'MEDIADIR_SETTINGS_DISPLAYDURATION_VALUE_TYPE_DAY' => $strDisplaydurationValueTypeDay,
            'MEDIADIR_SETTINGS_DISPLAYDURATION_VALUE_TYPE_MONTH' => $strDisplaydurationValueTypeMonth,
            'MEDIADIR_SETTINGS_DISPLAYDURATION_VALUE_TYPE_YEAR' => $strDisplaydurationValueTypeYear,
            'MEDIADIR_SETTINGS_DISPLAYDURATION_NOTIFICATION_OFF' => $strDisplaydurationNotificationOff,
            'MEDIADIR_SETTINGS_DISPLAYDURATION_NOTIFICATION_ON' => $strDisplaydurationNotificationOn,
            'MEDIADIR_SETTINGS_DISPLAYDURATION_NOTIFIVATION_SHOW_DAYBEFORE' => $strDisplaydurationNotificationShowDaybefore,
            'MEDIADIR_SETTINGS_DISPLAYDURATION_NOTIFICATION_VALUE' => $strDisplaydurationNotificationValue,
            'MEDIADIR_SETTINGS_TRANSLATION_STATUS_OFF' => $strTransStatusOff,
            'MEDIADIR_SETTINGS_TRANSLATION_STATUS_ON' => $strTransStatusOn,
        ));

        if(empty($this->arrCommunityGroups)) {
            $objTpl->setVariable(array(
                'TXT_MEDIADIR_SETTINGS_NO_COMMUNITY_GROUPS' => $_ARRAYLANG['TXT_MEDIADIR_NO_COMMUNITY_GROUPS'],
            ));
            $objTpl->parse('noCommunityGroupList');
        } else {
            foreach ($this->arrCommunityGroups as $intGroupId => $arrGroup) {
                if($arrGroup['type'] == 'frontend' && $arrGroup['active'] == 1) {
                    $objTpl->setVariable(array(
                        'MEDIADIR_SETTINGS_NUM_ENTRIES_GROUP_NAME' =>$arrGroup['name'],
                        'MEDIADIR_SETTINGS_NUM_ENTRIES' => $arrGroup['num_entries'],
                        'MEDIADIR_SETTINGS_NUM_ENTRIES_GROUP_ID' => $intGroupId,
                    ));
                    $objTpl->parse('communityGroupList');
                }
            }
        }

        $objTpl->parse('settings_content');
    }



    function settings_levels_categories($objTpl)
    {
        global $_ARRAYLANG, $_CORELANG;

        $objTpl->addBlockfile('MEDIADIR_SETTINGS_CONTENT', 'settings_content', 'module_mediadir_settings_levels_categories.html');

        $objTpl->setGlobalVariable(array(
            'TXT_MEDIADIR_SETTINGS_SHOW_CATEGORY_DESC' => $_ARRAYLANG['TXT_MEDIADIR_SETTINGS_SHOW_CATEGORY_DESC'],
            'TXT_MEDIADIR_SETTINGS_SHOW_CATEGORY_IMG' => $_ARRAYLANG['TXT_MEDIADIR_SETTINGS_SHOW_CATEGORY_IMG'],
            'TXT_MEDIADIR_SETTINGS_CATEGORIES' => $_ARRAYLANG['TXT_MEDIADIR_CATEGORIES'],
            'TXT_MEDIADIR_SETTINGS_SHOW_CATEGORY_DESC_INFO' => $_ARRAYLANG['TXT_MEDIADIR_SETTINGS_SHOW_CATEGORY_DESC_INFO'],
            'TXT_MEDIADIR_SETTINGS_SHOW_CATEGORY_IMG_INFO' => $_ARRAYLANG['TXT_MEDIADIR_SETTINGS_SHOW_CATEGORY_IMG_INFO'],
            'TXT_MEDIADIR_SETTINGS_CATEGORY_ORDER' => $_ARRAYLANG['TXT_MEDIADIR_SETTINGS_CATEGORY_ORDER'],
            'TXT_MEDIADIR_SETTINGS_LEVELS' => $_ARRAYLANG['TXT_MEDIADIR_LEVELS'],
            'TXT_MEDIADIR_SETTINGS_SHOW_LEVELS' => $_ARRAYLANG['TXT_MEDIADIR_LEVELS']." ".$_ARRAYLANG['TXT_MEDIADIR_ACTIVATE'],
            'TXT_MEDIADIR_SETTINGS_SHOW_LEVEL_DESC' => $_ARRAYLANG['TXT_MEDIADIR_SETTINGS_SHOW_LEVEL_DESC'],
            'TXT_MEDIADIR_SETTINGS_SHOW_LEVEL_IMG' => $_ARRAYLANG['TXT_MEDIADIR_SETTINGS_SHOW_LEVEL_IMG'],
            'TXT_MEDIADIR_SETTINGS_LEVEL_ORDER' => $_ARRAYLANG['TXT_MEDIADIR_SETTINGS_LEVEL_ORDER'],
            'TXT_MEDIADIR_SETTINGS_SHOW_LEVELS_INFO' => $_ARRAYLANG['TXT_MEDIADIR_SETTINGS_SHOW_LEVELS_INFO'],
            'TXT_MEDIADIR_SETTINGS_SHOW_LEVEL_IMG_INFO' => $_ARRAYLANG['TXT_MEDIADIR_SETTINGS_SHOW_LEVEL_IMG_INFO'],
            'TXT_MEDIADIR_SETTINGS_SHOW_LEVEL_DESC_INFO' => $_ARRAYLANG['TXT_MEDIADIR_SETTINGS_SHOW_LEVEL_DESC_INFO'],
            'TXT_MEDIADIR_SETTINGS_NUM_CATEGORIES_PER_GROUP' => $_ARRAYLANG['TXT_MEDIADIR_SETTINGS_NUM_CATEGORIES_PER_GROUP'],
            'TXT_MEDIADIR_SETTINGS_NUM_CATEGORIES_PER_GROUP_INFO' => $_ARRAYLANG['TXT_MEDIADIR_SETTINGS_NUM_CATEGORIES_PER_GROUP_INFO'],
            'TXT_MEDIADIR_SETTINGS_NUM_LEVELS_PER_GROUP' => $_ARRAYLANG['TXT_MEDIADIR_SETTINGS_NUM_LEVELS_PER_GROUP'],
            'TXT_MEDIADIR_SETTINGS_NUM_LEVELS_PER_GROUP_INFO' => $_ARRAYLANG['TXT_MEDIADIR_SETTINGS_NUM_LEVELS_PER_GROUP_INFO'],
            'TXT_MEDIADIR_SETTINGS_NUM' => $_ARRAYLANG['TXT_MEDIADIR_NUM'],
            'TXT_MEDIADIR_SETTINGS_COMMUNITY_GROUP' => $_ARRAYLANG['TXT_MEDIADIR_COMMUNITY_GROUP'],
            'TXT_MEDIADIR_SETTINGS_NO_COMMUNITY_GROUPS' => $_ARRAYLANG['TXT_MEDIADIR_NO_COMMUNITY_GROUPS'],
        ));

        $arrOrder = array(
            0 => $_ARRAYLANG['TXT_MEDIADIR_SETTINGS_ORDER_USER'],
            1 => $_ARRAYLANG['TXT_MEDIADIR_SETTINGS_ORDER_ABC'],
            2 => $_ARRAYLANG['TXT_MEDIADIR_SETTINGS_ORDER_INDEX'],
        );

        if($this->arrSettings['settingsShowCategoryDescription'] == 1) {
            $strCategoryDescOn = 'checked="checked"';
            $strCategoryDescOff = '';
        } else {
            $strCategoryDescOn = '';
            $strCategoryDescOff = 'checked="checked"';
        }

        if($this->arrSettings['settingsShowCategoryImage'] == 1) {
            $strCategoryImgOn = 'checked="checked"';
            $strCategoryImgOff = '';
        } else {
            $strCategoryImgOn = '';
            $strCategoryImgOff = 'checked="checked"';
        }

        if($this->arrSettings['settingsShowLevels'] == 1) {
            $strLevelsOn = 'checked="checked"';
            $strLevelsOff = '';
        } else {
            $strLevelsOn = '';
            $strLevelsOff = 'checked="checked"';
        }

        if($this->arrSettings['settingsShowLevelDescription'] == 1) {
            $strLevelDescOn = 'checked="checked"';
            $strLevelDescOff = '';
        } else {
            $strLevelDescOn = '';
            $strLevelDescOff = 'checked="checked"';
        }

        if($this->arrSettings['settingsShowLevelImage'] == 1) {
            $strLevelImgOn = 'checked="checked"';
            $strLevelImgOff = '';
        } else {
            $strLevelImgOn = '';
            $strLevelImgOff = 'checked="checked"';
        }

        $objTpl->setVariable(array(
            'MEDIADIR_SETTINGS_CATEGORY_IMG_ON' => $strCategoryImgOn,
            'MEDIADIR_SETTINGS_CATEGORY_IMG_OFF' => $strCategoryImgOff,
            'MEDIADIR_SETTINGS_CATEGORY_DESC_ON' => $strCategoryDescOn,
            'MEDIADIR_SETTINGS_CATEGORY_DESC_OFF' => $strCategoryDescOff,
            'MEDIADIR_SETTINGS_CATEGORY_ORDER' => $this->buildDropdownmenu($arrOrder, $this->arrSettings['settingsCategoryOrder']),
            'MEDIADIR_SETTINGS_LEVEL_IMG_ON' => $strLevelImgOn,
            'MEDIADIR_SETTINGS_LEVEL_IMG_OFF' => $strLevelImgOff,
            'MEDIADIR_SETTINGS_LEVEL_DESC_ON' => $strLevelDescOn,
            'MEDIADIR_SETTINGS_LEVEL_DESC_OFF' => $strLevelDescOff,
            'MEDIADIR_SETTINGS_LEVELS_ON' => $strLevelsOn,
            'MEDIADIR_SETTINGS_LEVELS_OFF' => $strLevelsOff,
            'MEDIADIR_SETTINGS_LEVEL_ORDER' => $this->buildDropdownmenu($arrOrder, $this->arrSettings['settingsLevelOrder']),
        ));

        if(empty($this->arrCommunityGroups)) {
            $objTpl->setVariable(array(
                'TXT_MEDIADIR_SETTINGS_NO_COMMUNITY_GROUPS' => $_ARRAYLANG['TXT_MEDIADIR_NO_COMMUNITY_GROUPS'],
            ));
            $objTpl->parse('noCommunityGroupCategoryList');
        } else {
            foreach ($this->arrCommunityGroups as $intGroupId => $arrGroup) {
                if($arrGroup['type'] == 'frontend' && $arrGroup['active'] == 1) {
                    $objTpl->setVariable(array(
                        'MEDIADIR_SETTINGS_NUM_CATEGORIES_GROUP_NAME' =>$arrGroup['name'],
                        'MEDIADIR_SETTINGS_NUM_CATEGORIES' => $arrGroup['num_categories'],
                        'MEDIADIR_SETTINGS_NUM_CATEGORIES_GROUP_ID' => $intGroupId,
                    ));
                    $objTpl->parse('communityGroupCategoryList');


                    $objTpl->setVariable(array(
                        'MEDIADIR_SETTINGS_NUM_LEVELS_GROUP_NAME' =>$arrGroup['name'],
                        'MEDIADIR_SETTINGS_NUM_LEVELS' => $arrGroup['num_levels'],
                        'MEDIADIR_SETTINGS_NUM_LEVELS_GROUP_ID' => $intGroupId,
                    ));
                    $objTpl->parse('communityGroupLevelList');
                }
            }
        }

        $objTpl->parse('settings_content');
    }



    function settings_mails($objTpl)
    {
        global $_ARRAYLANG, $_CORELANG, $objDatabase;

        $objTpl->addBlockfile('MEDIADIR_SETTINGS_CONTENT', 'settings_content', 'module_mediadir_settings_mails.html');

        switch ($_GET['tpl']) {
            case 'delete_template':
                if(!empty($_GET['id'])) {
                    $objDelete = $objDatabase->Execute("DELETE FROM ".DBPREFIX."module_mediadir_mails WHERE `id`='".intval($_GET['id'])."'");
                    if($objDelete !== false){
                        $this->strOkMessage = $_ARRAYLANG['TXT_MEDIADIR_MAIL_TEMPLATE']." ".$_ARRAYLANG['TXT_MEDIADIR_SUCCESSFULLY_DELETED'];
                    } else {
                        $this->strErrMessage = $_ARRAYLANG['TXT_MEDIADIR_MAIL_TEMPLATE']." ".$_ARRAYLANG['TXT_MEDIADIR_CORRUPT_DELETED'];
                    }
                }
                break;
        }

        if(!empty($_POST) && !isset($_POST['submitSettingsForm'])) {
            $objSetAsDefault = $objDatabase->Execute("UPDATE ".DBPREFIX."module_mediadir_mails SET `is_default`='0'");
            foreach ($_POST as $key => $intTemplateDefaultId) {
                $objSetAsDefault = $objDatabase->Execute("UPDATE ".DBPREFIX."module_mediadir_mails SET `is_default`='1', `active`='1' WHERE `id`='".intval($intTemplateDefaultId)."'");
            }
        }

        $objTpl->setGlobalVariable(array(
            'TXT_MEDIADIR_NEW_MAIL_TEMPLATE' => $_ARRAYLANG['TXT_MEDIADIR_NEW_MAIL_TEMPLATE'],
            'TXT_MEDIADIR_STATUS' => $_CORELANG['TXT_STATUS'],
            'TXT_MEDIADIR_TITLE' => $_ARRAYLANG['TXT_MEDIADIR_TITLE'],
            'TXT_MEDIADIR_ACTION' => $_CORELANG['TXT_HISTORY_ACTION'],
            'TXT_MEDIADIR_LANG' => $_CORELANG['TXT_ACCESS_LANGUAGE'],
            'TXT_MEDIADIR_DEFAULT' => $_CORELANG['TXT_STANDARD'],
            'TXT_MEDIADIR_CONFIRM_DELETE_DATA' => $_ARRAYLANG['TXT_MEDIADIR_CONFIRM_DELETE_DATA'],
            'TXT_MEDIADIR_ACTION_IS_IRREVERSIBLE' => $_ARRAYLANG['TXT_MEDIADIR_ACTION_IS_IRREVERSIBLE'],
            'TXT_EDIT' => $_ARRAYLANG['TXT_MEDIADIR_EDIT'],
            'TXT_DELETE' => $_ARRAYLANG['TXT_MEDIADIR_DELETE'],
        ));

        $objTemplates = $objDatabase->Execute("
            SELECT
                id,title,lang_id,action_id,is_default,active
            FROM
                ".DBPREFIX."module_mediadir_mails
            ORDER BY
                action_id ASC, title ASC
            ");

		if ($objTemplates !== false) {
			while (!$objTemplates->EOF) {
				$strTemplateTitle = htmlspecialchars($objTemplates->fields['title'], ENT_QUOTES, CONTREXX_CHARSET);
				$intTemplateLangId = intval($objTemplates->fields['lang_id']);
				$intTemplateActionId = intval($objTemplates->fields['action_id']);
				$intIsDefault = intval($objTemplates->fields['is_default']);
				$intStatus = intval($objTemplates->fields['active']);
				$intTemplateId = intval($objTemplates->fields['id']);

				//get lang name
				foreach ($this->arrFrontendLanguages as $key => $arrLang) {
				    if($arrLang['id'] == $intTemplateLangId) {
				        $strTemplateLang = $arrLang['name'];
				    }
        		}

        		//get action
        		$objAction = $objDatabase->Execute("SELECT name FROM ".DBPREFIX."module_mediadir_mail_actions WHERE id='".$intTemplateActionId."' LIMIT 1 ");
        		if ($objAction !== false) {
        			$strTemplateAction = $_ARRAYLANG['TXT_MEDIADIR_MAIL_ACTION_'.strtoupper($objAction->fields['name'])];
        		}

        		if($intStatus == 1) {
        		    $strStatus = 'images/icons/status_green.gif';
        		    $intStatus = 0;
        		} else {
        		    $strStatus = 'images/icons/status_red.gif';
        		    $intStatus = 1;
        		}

        		if($intIsDefault == 1) {
        		    $strIsDefault = 'checked="checked"';
        		} else {
        		    $strIsDefault = '';
        		}

				//parse data variables
                $objTpl->setVariable(array(
                    'MEDIADIR_TEMPLATE_ROW_CLASS' => $i%2==0 ? 'row1' : 'row2',
                    'MEDIADIR_TEMPLATE_ID' => $intTemplateId,
                    'MEDIADIR_TEMPLATE_STATUS' => $strStatus,
                    'MEDIADIR_TEMPLATE_SWITCH_STATUS' => $intStatus,
                    'MEDIADIR_TEMPLATE_LANG' => $strTemplateLang,
                    'MEDIADIR_TEMPLATE_TITLE' => $strTemplateTitle,
                    'MEDIADIR_TEMPLATE_ACTION' => $strTemplateAction,
                    'MEDIADIR_TEMPLATE_DEFAULT' => $strIsDefault,
                    'MEDIADIR_TEMPLATE_DEFAULT_NAME' => "templateDefault_".$intTemplateActionId,
                ));

                $i++;

                $objTpl->parse('mediadirMailTemplateList');
                $objTemplates->MoveNext();
			}
		}

		if($objTemplates->RecordCount() == 0) {
    		 $objTpl->setVariable(array(
                'TXT_MEDIADIR_NO_ENTRIES_FOUND' => $_ARRAYLANG['TXT_MEDIADIR_NO_ENTRIES_FOUND']
            ));

            $objTpl->parse('mediadirMailTemplateNoEntries');
		}

        $objTpl->parse('settings_content');
    }



    function settings_modify_mail($objTpl)
    {
        global $_ARRAYLANG, $_CORELANG, $objDatabase;

        $objTpl->addBlockfile('MEDIADIR_SETTINGS_CONTENT', 'settings_content', 'module_mediadir_settings_modify_mail.html');

        //load teplate data
        if(isset($_GET['id']) && $_GET['id'] != 0) {
            $pageTitle = $_ARRAYLANG['TXT_MEDIADIR_EDIT_MAIL_TEMPLATE'];
            $intTemplateId = intval($_GET['id']);

            $objTemplate = $objDatabase->Execute("
                SELECT
                    title,content,recipients,lang_id,action_id
                FROM
                    ".DBPREFIX."module_mediadir_mails
                WHERE
                    id='".$intTemplateId."'
                LIMIT 1
                ");
    		if ($objTemplat !== false) {
    			while (!$objTemplate->EOF) {
    				$strTemplateTitle = htmlspecialchars($objTemplate->fields['title'], ENT_QUOTES, CONTREXX_CHARSET);
    				$strTemplateContent = htmlspecialchars($objTemplate->fields['content'], ENT_QUOTES, CONTREXX_CHARSET);
    				$strTemplateRecipients = htmlspecialchars($objTemplate->fields['recipients'], ENT_QUOTES, CONTREXX_CHARSET);
    				$intTemplateLangId = intval($objTemplate->fields['lang_id']);
                    $intTemplateActionId = intval($objTemplate->fields['action_id']);
    				$intStatus = intval($objTemplate->fields['active']);
    				$objTemplate->MoveNext();
    			}
    		}

            //parse data variables
            $objTpl->setGlobalVariable(array(
                'MEDIADIR_TEMPLATE_ID' => $intTemplateId,
                'MEDIADIR_TEMPLATE_STATUS' => $intStatus,
                'MEDIADIR_TEMPLATE_TITLE' => $strTemplateTitle,
                'MEDIADIR_TEMPLATE_CONTENT' => $strTemplateContent,
                'MEDIADIR_TEMPLATE_RECIPIENTS' => $strTemplateRecipients,
            ));
        } else {
            $pageTitle = $_ARRAYLANG['TXT_MEDIADIR_NEW_MAIL_TEMPLATE'];
        }

        //get actions
        $arrActions = array();
        $objActions = $objDatabase->Execute("SELECT id, name FROM ".DBPREFIX."module_mediadir_mail_actions");
		if ($objActions !== false) {
			while (!$objActions->EOF) {
				$arrActions[$objActions->fields['id']] = $_ARRAYLANG['TXT_MEDIADIR_MAIL_ACTION_'.strtoupper($objActions->fields['name'])];
				$objActions->MoveNext();
			}
		}
		
		//get languages
		$arrLanguages = array();
		foreach ($this->arrFrontendLanguages as $key => $arrLang) {
		    $arrLanguages[$arrLang['id']] = $arrLang['name'];
		}

        $objTpl->setVariable(array(
            'TXT_MEDIADIR_PAGE_TITLE' => $pageTitle,
            'TXT_MEDIADIR_ACTION' => $_CORELANG['TXT_HISTORY_ACTION'],
            'TXT_MEDIADIR_LANG' => $_CORELANG['TXT_BROWSERLANGUAGE'],
            'TXT_MEDIADIR_RECIPIENTS' => $_ARRAYLANG['TXT_MEDIADIR_RECIPIENTS'],
            'TXT_MEDIADIR_TITLE' => $_ARRAYLANG['TXT_MEDIADIR_TITLE'],
            'TXT_MEDIADIR_CONTENT' => $_CORELANG['TXT_CONTENT'],
            'TXT_MEDIADIR_PLACEHOLDER' => $_ARRAYLANG['TXT_MEDIADIR_PLACEHOLDER'],
            'TXT_MEDIADIR_USERNAME' => $_CORELANG['TXT_USER_NAME'],
            'TXT_MEDIADIR_FIRSTNAME' => $_CORELANG['TXT_USER_FIRSTNAME'],
            'TXT_MEDIADIR_LASTNAME' => $_CORELANG['TXT_USER_LASTNAME'],
            'TXT_MEDIADIR_TITLE' => $_ARRAYLANG['TXT_MEDIADIR_TITLE'],
            'TXT_MEDIADIR_LINK' => $_ARRAYLANG['TXT_MEDIADIR_LINK'],
            'TXT_MEDIADIR_URL' => $_CORELANG['TXT_SETTINGS_DOMAIN_URL'],
            'TXT_MEDIADIR_DATE' => $_CORELANG['TXT_DATE'],
            'MEDIADIR_TEMPLATE_ACTION' => $this->buildDropdownmenu($arrActions, $intTemplateActionId),
            'MEDIADIR_TEMPLATE_LANG' => $this->buildDropdownmenu($arrLanguages, $intTemplateLangId),
        ));

        $objTpl->parse('settings_content');
    }



    function settings_save_mail($arrData)
    {
        global $_ARRAYLANG, $_CORELANG, $objDatabase;

        $intTemplateId = intval($arrData['templateId']);
        $intTemplateAction = intval($arrData['templateAction']);
        $intTemplateLang = intval($arrData['templateLang']);
        $intTemplateRecipients = contrexx_addslashes($arrData['templateRecipients']);
        $intTemplateTitle = contrexx_addslashes($arrData['templateTitle']);
        $intTemplateContent = contrexx_addslashes($arrData['templateContent']);

        if(!empty($intTemplateId) && $intTemplateId != 0) {
            $objEditTemplate = $objDatabase->Execute("
                UPDATE
                    ".DBPREFIX."module_mediadir_mails
                SET
                    title='".$intTemplateTitle."',
                    content='".$intTemplateContent."',
                    recipients='".$intTemplateRecipients."',
                    lang_id='".$intTemplateLang."',
                    action_id='".$intTemplateAction."'
                WHERE
                    id='".$intTemplateId."'
                ");
            if ($objEditTemplate === false) {
                return false;
            }
        } else {
            $objAddTemplate = $objDatabase->Execute("
                INSERT INTO
                    ".DBPREFIX."module_mediadir_mails
                SET
                    title='".$intTemplateTitle."',
                    content='".$intTemplateContent."',
                    recipients='".$intTemplateRecipients."',
                    lang_id='".$intTemplateLang."',
                    action_id='".$intTemplateAction."'
                ");
            if ($objAddTemplate === false) {
                return false;
            }
        }

        parent::getSettings();
        parent::getCommunityGroups();

        return true;
    }



    function settings_forms($objTpl)
    {
        global $_ARRAYLANG, $_CORELANG, $objDatabase, $_LANGID;

        switch ($_GET['tpl']) {
            case 'delete_form':
                if(!empty($_GET['id'])) {
                    $objForms = new mediaDirectoryForm();
                    $strStatus = $objForms->deleteForm(intval($_GET['id']));

                    if($strStatus == true){
                        $this->strOkMessage = $_ARRAYLANG['TXT_MEDIADIR_FORM_TEMPLATE']." ".$_ARRAYLANG['TXT_MEDIADIR_SUCCESSFULLY_DELETED'];
                    } else {
                        $this->strErrMessage = $_ARRAYLANG['TXT_MEDIADIR_FORM_TEMPLATE']." ".$_ARRAYLANG['TXT_MEDIADIR_CORRUPT_DELETED'];
                    }
                }
                break;
        }

        $objTpl->addBlockfile('MEDIADIR_SETTINGS_CONTENT', 'settings_content', 'module_mediadir_settings_forms.html');

        $objTpl->setGlobalVariable(array(
            'TXT_MEDIADIR_NEW_FORM_TEMPLATE' => $_ARRAYLANG['TXT_MEDIADIR_NEW_FORM_TEMPLATE'],
            'TXT_MEDIADIR_STATUS' => $_CORELANG['TXT_STATUS'],
            'TXT_MEDIADIR_TITLE' => $_ARRAYLANG['TXT_MEDIADIR_TITLE'],
            'TXT_MEDIADIR_DESCRIPTION' => $_CORELANG['TXT_DESCRIPTION'],
            'TXT_MEDIADIR_ACTION' => $_CORELANG['TXT_HISTORY_ACTION'],
            'TXT_MEDIADIR_ORDER' => $_CORELANG['TXT_CORE_SORTING_ORDER'],
            'TXT_MEDIADIR_CONFIRM_DELETE_DATA' => $_ARRAYLANG['TXT_MEDIADIR_CONFIRM_DELETE_DATA'],
            'TXT_MEDIADIR_FORM_DEL_INFO' => $_ARRAYLANG['TXT_MEDIADIR_FORM_DEL_INFO'],
            'TXT_MEDIADIR_ACTION_IS_IRREVERSIBLE' => $_ARRAYLANG['TXT_MEDIADIR_ACTION_IS_IRREVERSIBLE'],
            'TXT_EDIT' => $_ARRAYLANG['TXT_MEDIADIR_EDIT'],
            'TXT_DELETE' => $_ARRAYLANG['TXT_MEDIADIR_DELETE'],
            'TXT_MEDIADIR_SUBMIT' => $_CORELANG['TXT_SAVE'],
        ));

        $objForms = new mediaDirectoryForm();
        $objForms->listForms($objTpl, 1, null);


        $objTpl->parse('settings_content');
    }



    function settings_modify_form($objTpl)
    {
        global $_ARRAYLANG, $_CORELANG, $_LANGID, $objDatabase;

        $objTpl->addBlockfile('MEDIADIR_SETTINGS_CONTENT', 'settings_content', 'module_mediadir_settings_modify_form.html');

        $objTpl->setGlobalVariable(array(
            'TXT_MEDIADIR_SETTINGS_INPUTFIELDS' => $_ARRAYLANG['TXT_MEDIADIR_INPUTFIELDS'],
            'TXT_MEDIADIR_SETTINGS_FORM' => $_ARRAYLANG['TXT_MEDIADIR_FORM'],
            'TXT_MEDIADIR_SETTINGS_PLACEHOLDER' => $_ARRAYLANG['TXT_MEDIADIR_PLACEHOLDER'],
            'TXT_MEDIADIR_SETTINGS_GLOBAL_PLACEHOLDER_INFO' => $_ARRAYLANG['TXT_MEDIADIR_SETTINGS_GLOBAL_PLACEHOLDER_INFO'],
            'TXT_MEDIADIR_SETTINGS_PLACEHOLDER_INFO' => $_ARRAYLANG['TXT_MEDIADIR_SETTINGS_PLACEHOLDER_INFO'],
            'TXT_MEDIADIR_SETTINGS_FIELD_SHOW_IN' => $_ARRAYLANG['TXT_MEDIADIR_FIELD_SHOW_IN'],
            'TXT_MEDIADIR_SETTINGS_INPUTFIELDS_ADD_NEW' => $_ARRAYLANG['TXT_MEDIADIR_SETTINGS_INPUTFIELDS_ADD_NEW'],
            'TXT_MEDIADIR_SETTINGS_INPUTFIELDS_ID' => $_CORELANG['TXT_GROUP_ID'],
            'TXT_MEDIADIR_SETTINGS_INPUTFIELDS_SORT' => $_CORELANG['TXT_CORE_SORTING_ORDER'],
            'TXT_MEDIADIR_SETTINGS_INPUTFIELDS_NAME' => $_ARRAYLANG['TXT_MEDIADIR_FIELD_NAME'],
            'TXT_MEDIADIR_SETTINGS_INPUTFIELDS_TYPE' => $_ARRAYLANG['TXT_MEDIADIR_FIELD_TYPE'],
            'TXT_MEDIADIR_SETTINGS_INPUTFIELDS_DEFAULTVALUE' => $_ARRAYLANG['TXT_MEDIADIR_DEFAULTVALUE'],
            'TXT_MEDIADIR_SETTINGS_INPUTFIELDS_CHECK' => $_ARRAYLANG['TXT_MEDIADIR_VALUE_CHECK'],
            'TXT_MEDIADIR_SETTINGS_INPUTFIELDS_MUSTFIELD' => $_ARRAYLANG['TXT_MEDIADIR_MUSTFIELD'],
            'TXT_MEDIADIR_SETTINGS_INPUTFIELDS_ACTION' => $_CORELANG['TXT_HISTORY_ACTION'],
            'TXT_MEDIADIR_SETTINGS_INPUTFIELD_SYSTEM_FIELD_CANT_DELETE' => $_ARRAYLANG['TXT_MEDIADIR_SETTINGS_INPUTFIELD_SYSTEM_FIELD_CANT_DELETE'],
            'TXT_MEDIADIR_DELETE' => $_ARRAYLANG['TXT_MEDIADIR_DELETE'],
            'TXT_MEDIADIR_SETTINGS_INPUTFIELDS_EXP_SEARCH' => $_ARRAYLANG['TXT_MEDIADIR_EXP_SEARCH'],
            'MEDIADIR_SETTINGS_INPUTFIELDS_DEFAULT_LANG_ID' => $_LANGID,
            'MEDIADIR_SETTINGS_FORM_DEFAULT_LANG_ID' => $_LANGID,
            'TXT_MEDIADIR_NAME' =>  $_CORELANG['TXT_NAME'],
            'TXT_MEDIADIR_DESCRIPTION' =>  $_CORELANG['TXT_DESCRIPTION'],
            'TXT_MEDIADIR_PICTURE' =>  $_CORELANG['TXT_IMAGE'],
            'TXT_MEDIADIR_BROWSE' =>  $_CORELANG['TXT_BROWSE'],
            'TXT_MEDIADIR_MORE' =>  $_ARRAYLANG['TXT_MEDIADIR_MORE'],
            'TXT_MEDIADIR_SETTINGS_PERMISSIONS' =>  $_CORELANG['TXT_PERMISSIONS'],
            'TXT_MEDIADIR_SETTINGS_PERMISSIONS_INFO' =>  $_ARRAYLANG['TXT_MEDIADIR_SETTINGS_PERMISSIONS_INFO'],
            'TXT_MEDIADIR_SETTINGS_COMMUNITY_GROUP' =>  $_ARRAYLANG['TXT_MEDIADIR_COMMUNITY_GROUP'],
            'TXT_MEDIADIR_SETTINGS_ALLOW_GHROUP_ADD_ENTRIES' =>  $_ARRAYLANG['TXT_MEDIADIR_SETTINGS_ALLOW_GHROUP_ADD_ENTRIES'],
        ));

        if(isset($_GET['ajax'])) {
            $ajax = $_GET['ajax'];
        } else if (isset($_POST['ajax'])) {
            $ajax = $_POST['ajax'];
        } else {
            $ajax = null;
        }

        //ajax functions
        switch ($ajax) {
            case 'add':
                $objInputfields = new mediaDirectoryInputfield(intval($_GET['id']));
                $intInsertId = $objInputfields->addInputfield();

                die();
                break;

            case 'delete':
                $objInputfields = new mediaDirectoryInputfield(intval($_GET['id']));
                $intInsertId = $objInputfields->deleteInputfield($_GET['field']);

                die();
                break;

            case 'save':
                $objInputfields = new mediaDirectoryInputfield(intval($_POST['formId']));
                $strInputfields = $objInputfields->saveInputfields($_POST);

                die();
                break;

            case 'move':
                $objInputfields = new mediaDirectoryInputfield(intval($_GET['id']));
                $strInputfields = $objInputfields->moveInputfield($_GET['field'], $_GET['direction']);

                die();
                break;

            case 'refresh':
                $objInputfields = new mediaDirectoryInputfield(intval($_GET['id']));
                $strInputfields = $objInputfields->refreshInputfields($objTpl);

                //return
                echo $strInputfields;

                die();
                break;
        }

        //load form data
        if(intval($_GET['id']) != 0) {
            $pageTitle = $_ARRAYLANG['TXT_MEDIADIR_EDIT_FORM_TEMPLATE'];
            $intFormId = intval($_GET['id']);

            $objForm = new mediaDirectoryForm($intFormId);

            //parse data variables
            $objTpl->setGlobalVariable(array(
                'MEDIADIR_FORM_ID' => $intFormId,
                'MEDIADIR_FORM_NAME_MASTER' => $objForm->arrForms[$intFormId]['formName'][0],
                'MEDIADIR_FORM_DESCRIPTION_MASTER' => $objForm->arrForms[$intFormId]['formDescription'][0],
                'MEDIADIR_FORM_PICTURE' => $objForm->arrForms[$intFormId]['formPicture'],
            ));

            parent::getCommunityGroups();

            //permissions community groups
            if(empty($this->arrCommunityGroups)) {
                $objTpl->setVariable(array(
                    'TXT_MEDIADIR_SETTINGS_NO_COMMUNITY_GROUPS' => $_ARRAYLANG['TXT_MEDIADIR_NO_COMMUNITY_GROUPS'],
                ));
                $objTpl->parse('mediadirFormNoCommunityGroup');
            } else {
                $i=0;
                foreach ($this->arrCommunityGroups as $intGroupId => $arrGroup) {
                    if($arrGroup['type'] == 'frontend' && $arrGroup['active'] == 1) {
                        if(intval($arrGroup['status_group'][$intFormId]) == 1) {
                            $strGroupStatus = 'checked="checked"';
                        } else {
                            $strGroupStatus = '';
                        }

                        $objTpl->setVariable(array(
                            'MEDIADIR_SETTINGS_COMMUNITY_GROUP_ROW_CLASS' => $i%2==0 ? 'row1' : 'row2',
                            'TXT_MEDIADIR_SETTINGS_COMMUNITY_GROUP_NAME' => $arrGroup['name'],
                            'MEDIADIR_SETTINGS_COMMUNITY_GROUP_ACTIVE' => $strGroupStatus,
                            'MEDIADIR_SETTINGS_COMMUNITY_GROUP_ID' => $intGroupId,
                        ));
                        $i++;
                        $objTpl->parse('mediadirFormCommunityGroupList');
                    }
                }
            }

            //load inputfields data
            $objInputfields = new mediaDirectoryInputfield($intFormId);
            $objInputfields->listInputfields($objTpl, 1);
            $objInputfields->listPlaceholders($objTpl);
        } else {
            $pageTitle = $_ARRAYLANG['TXT_MEDIADIR_NEW_FORM_TEMPLATE'];

            $objTpl->hideBlock('mediadirInputfieldsForm');
        }

        //form name language block
        foreach ($this->arrFrontendLanguages as $key => $arrLang) {
            if(isset($intFormId)){
                $strFormName = empty($objForm->arrForms[$intFormId]['formName'][$arrLang['id']]) ? $objForm->arrForms[$intFormId]['formName'][0] : $objForm->arrForms[$intFormId]['formName'][$arrLang['id']];
            } else {
                $intFormId = '';
            }

            $objTpl->setVariable(array(
                'MEDIADIR_FORM_NAME_LANG_ID' => $arrLang['id'],
                'TXT_MEDIADIR_FORM_NAME_LANG_NAME' => $arrLang['name'],
                'TXT_MEDIADIR_FORM_NAME_LANG_SHORTCUT' => $arrLang['lang'],
                'MEDIADIR_FORM_NAME' => $strFormName,
            ));

            if(($key+1) == count($this->arrFrontendLanguages)) {
                $objTpl->setVariable(array(
                    'MEDIADIR_MINIMIZE' =>  '<a href="javascript:ExpandMinimizeForm(\'formName\');">&laquo;&nbsp;'.$_ARRAYLANG['TXT_MEDIADIR_MINIMIZE'].'</a>',
                ));
            }

            $objTpl->parse('mediadirFormNameList');
        }

        //form decription language block
        foreach ($this->arrFrontendLanguages as $key => $arrLang) {
            if(isset($intFormId)){
                $strFormDescription = empty($objForm->arrForms[$intFormId]['formDescription'][$arrLang['id']]) ? $objForm->arrForms[$intFormId]['formDescription'][0] : $objForm->arrForms[$intFormId]['formDescription'][$arrLang['id']];
            } else {
                $intFormId = '';
            }

            $objTpl->setVariable(array(
                'MEDIADIR_FORM_DESCRIPTION_LANG_ID' => $arrLang['id'],
                'TXT_MEDIADIR_FORM_DESCRIPTION_LANG_NAME' => $arrLang['name'],
                'TXT_MEDIADIR_FORM_DESCRIPTION_LANG_SHORTCUT' => $arrLang['lang'],
                'MEDIADIR_FORM_DESCRIPTION' => $strFormDescription,
            ));

            if(($key+1) == count($this->arrFrontendLanguages)) {
                $objTpl->setVariable(array(
                    'MEDIADIR_MINIMIZE' =>  '<a href="javascript:ExpandMinimizeForm(\'formDescription\');">&laquo;&nbsp;'.$_ARRAYLANG['TXT_MEDIADIR_MINIMIZE'].'</a>',
                ));
            }

            $objTpl->parse('mediadirFormDescriptionList');
        }

        $objTpl->setVariable(array(
            'TXT_MEDIADIR_PAGE_TITLE' => $pageTitle,
        ));

        $objTpl->parse('settings_content');
    }



    function saveSettings($arrSettings)
    {
        global $_ARRAYLANG, $_CORELANG, $objDatabase;

        foreach ($arrSettings as $strName => $varValue) {
            switch ($strName) {
                case 'settingsNumEntries':
                    $objSaveSettings = $objDatabase->Execute("TRUNCATE TABLE ".DBPREFIX."module_mediadir_settings_num_entries");

                    foreach ($varValue as $intGroupId => $strNum) {
                        $objSaveSettings = $objDatabase->Execute("
                            INSERT INTO
                                ".DBPREFIX."module_mediadir_settings_num_entries
                            SET
                                `group_id` = '".intval($intGroupId)."',
                                `num_entries` = '".contrexx_addslashes($strNum)."'
                            ");

                        if ($objSaveSettings === false) {
                            return false;
                        }
                    }
                    break;
                case 'settingsNumCategories':
                    $objSaveSettings = $objDatabase->Execute("TRUNCATE TABLE ".DBPREFIX."module_mediadir_settings_num_categories");

                    foreach ($varValue as $intGroupId => $strNum) {
                        $objSaveSettings = $objDatabase->Execute("
                            INSERT INTO
                                ".DBPREFIX."module_mediadir_settings_num_categories
                            SET
                                `group_id` = '".intval($intGroupId)."',
                                `num_categories` = '".contrexx_addslashes($strNum)."'
                            ");

                        if ($objSaveSettings === false) {
                            return false;
                        }
                    }
                    break;
                case 'settingsNumLevels':
                    $objSaveSettings = $objDatabase->Execute("TRUNCATE TABLE ".DBPREFIX."module_mediadir_settings_num_levels");

                    foreach ($varValue as $intGroupId => $strNum) {
                        $objSaveSettings = $objDatabase->Execute("
                            INSERT INTO
                                ".DBPREFIX."module_mediadir_settings_num_levels
                            SET
                                `group_id` = '".intval($intGroupId)."',
                                `num_levels` = '".contrexx_addslashes($strNum)."'
                            ");

                        if ($objSaveSettings === false) {
                            return false;
                        }
                    }
                    break;
                default:
                    $objSaveSettings = $objDatabase->Execute("
                        UPDATE
                            ".DBPREFIX."module_mediadir_settings
                        SET
                            `value`='".contrexx_addslashes($varValue)."'
                        WHERE
                            `name`='".contrexx_addslashes($strName)."'
                    ");

                    if ($objSaveSettings === false) {
                        return false;
                    }
                    break;
            }
        }

        parent::getSettings();
        parent::getCommunityGroups();

        return true;
    }
}