<?php
/**
 * Media  Directory Inputfield Google Map
 * @copyright   CONTREXX CMS - COMVATION AG
 * @author      Comvation Development Team <info@comvation.com>
 * @package     contrexx
 * @subpackage  module_marketplace
 * @todo        Edit PHP DocBlocks!
 */

/**
 * Includes
 */
require_once ASCMS_MODULE_PATH . '/mediadir/lib/lib.class.php';
require_once(ASCMS_FRAMEWORK_PATH. '/Image.class.php');
require_once ASCMS_LIBRARY_PATH. '/googleServices/googleMap.class.php';
require_once ASCMS_MODULE_PATH . '/mediadir/lib/inputfields/inputfield.interface.php';

class mediaDirectoryInputfieldGoogle_map extends mediaDirectoryLibrary implements inputfield
{
    public $arrPlaceholders = array('TXT_MEDIADIR_INPUTFIELD_NAME','MEDIADIR_INPUTFIELD_VALUE','MEDIADIR_INPUTFIELD_LINK', 'MEDIADIR_INPUTFIELD_LINK_HREF');

    private $imagePath;
    private $imageWebPath;

    /**
     * Constructor
     */
    function __construct()
    {
        $this->imagePath = constant('ASCMS_'.$this->moduleConstVar.'_IMAGES_PATH').'/';
        $this->imageWebPath = constant('ASCMS_'.$this->moduleConstVar.'_IMAGES_WEB_PATH') .'/';
    }

    function getInputfield($intView, $arrInputfield, $intEntryId=null)
    {
        global $objDatabase,$_CORELANG, $_ARRAYLANG, $_LANGID, $objInit, $_CONFIG;

        switch ($intView) {
            default:
            case 1:
                //modify (add/edit) View
                $intId = intval($arrInputfield['id']);
                parent::getSettings();

                if(isset($intEntryId) && $intEntryId != 0) {
                    $objInputfieldValue = $objDatabase->Execute("
                        SELECT
                            `value`
                        FROM
                            ".DBPREFIX."module_".$this->moduleTablePrefix."_rel_entry_inputfields
                        WHERE
                            field_id=".$intId."
                        AND
                            entry_id=".$intEntryId."
                        LIMIT 1
                    ");
                    $strValue  = htmlspecialchars($objInputfieldValue->fields['value'], ENT_QUOTES, CONTREXX_CHARSET);
                    $arrValues = explode(',', $strValue);

                    $strValueLon = $arrValues[0];
                    $strValueLat = $arrValues[1];
                    $strValueZoom = $arrValues[2];
                    $strValueKml = $arrValues[3];

                    if($strValueKml != null) {
                        $arrKml = explode("/", $strValueKml);
                        $intArrKmlLenght = count($arrKml)-1;
                        $strKmlFilename = $arrKml[$intArrKmlLenght];

                        $strKmlPreview = '<a href="'.$arrValues[3].'" target="_blank">'.$strKmlFilename.'</a>&nbsp;&nbsp;<input type="checkbox" value="1" name="deleteMedia['.$intId.']" />'.$_ARRAYLANG['TXT_MEDIADIR_DELETE'].'<br />';
                    } else {
                        $strKmlPreview = '';
                    }
                } else {
                    $objSettingsRS = $objDatabase->Execute("SELECT value FROM ".DBPREFIX."module_".$this->moduleTablePrefix."_settings WHERE name='settingsGoogleMapStartposition'");
                    if ($objSettingsRS !== false) {
                        $strValue = htmlspecialchars($objSettingsRS->fields['value'], ENT_QUOTES, CONTREXX_CHARSET);
                    }
                    $arrValues = explode(',', $strValue);

                    $strValueLon = $arrValues[0];
                    $strValueLat = $arrValues[1];
                    $strValueZoom = $arrValues[2];
                    $strKmlPreview = '';
                }

                $strMapId       = $this->moduleName.'Inputfield_'.$intId.'_map';
                $strLonId       = $this->moduleName.'Inputfield_'.$intId.'_lon';
                $strLatId       = $this->moduleName.'Inputfield_'.$intId.'_lat';
                $strZoomId      = $this->moduleName.'Inputfield_'.$intId.'_zoom';
                $strStreetId    = $this->moduleName.'Inputfield_'.$intId.'_street';
                $strZipId       = $this->moduleName.'Inputfield_'.$intId.'_zip';
                $strCityId      = $this->moduleName.'Inputfield_'.$intId.'_city';
                $strKmlId       = $this->moduleName.'Inputfield_'.$intId.'_kml';
                $strKey         = $_CONFIG['googleMapsAPIKey'];

                if($objInit->mode == 'backend') {
                    $strInputfield .= '<table cellpadding="0" cellspacing="0" border="0" class="'.$this->moduleName.'TableGoogleMap">';
                    $strInputfield .= '<tr><td style="border: 0px;">'.$_ARRAYLANG['TXT_MEDIADIR_GOOGLE_MAP_STREET'].':&nbsp;&nbsp;</td><td style="border: 0px; padding-bottom: 2px;"><input type="text" name="'.$this->moduleName.'Inputfield['.$intId.'][street]" id="'.$strStreetId.'" value="" onfocus="this.select();" /></td></tr>';
                    $strInputfield .= '<tr><td style="border: 0px;">'.$_ARRAYLANG['TXT_MEDIADIR_GOOGLE_MAP_CITY'].':&nbsp;&nbsp;</td><td style="border: 0px; padding-bottom: 2px;"><input type="text" name="'.$this->moduleName.'Inputfield['.$intId.'][place]" id="'.$strZipId.'"  value="" onfocus="this.select();" /></td></tr>';
                    $strInputfield .= '<tr><td style="border: 0px;">'.$_ARRAYLANG['TXT_MEDIADIR_GOOGLE_MAP_ZIP'].':&nbsp;&nbsp;</td><td style="border: 0px; padding-bottom: 2px;"><input type="text" name="'.$this->moduleName.'Inputfield['.$intId.'][zip]" id="'.$strCityId.'" value="" onfocus="this.select();" /></td></tr>';
                    $strInputfield .= '<tr><td style="border: 0px;"><br /></td><td style="border: 0px;"><input type="button" onclick="searchAddress();" name="'.$this->moduleName.'Inputfield['.$intId.'][search]" id="'.$this->moduleName.'Inputfield_'.$intId.'_search" value="'.$_CORELANG['TXT_SEARCH'].'" /></td></tr>';
                    $strInputfield .= '<tr><td style="border: 0px;" coldpan="2"><br /></td></tr>';
                    $strInputfield .= '<tr><td style="border: 0px;">'.$_ARRAYLANG['TXT_MEDIADIR_GOOGLE_MAP_LON'].':&nbsp;&nbsp;</td><td style="border: 0px; padding-bottom: 2px;"><input type="text" name="'.$this->moduleName.'Inputfield['.$intId.'][lon]" id="'.$strLonId.'"  value="'.$strValueLon.'" onfocus="this.select();" /></td></tr>';
                    $strInputfield .= '<tr><td style="border: 0px;">'.$_ARRAYLANG['TXT_MEDIADIR_GOOGLE_MAP_LAT'].':&nbsp;&nbsp;</td><td style="border: 0px; padding-bottom: 2px;"><input type="text" name="'.$this->moduleName.'Inputfield['.$intId.'][lat]" id="'.$strLatId.'" value="'.$strValueLat.'" onfocus="this.select();" /></td></tr>';
                    $strInputfield .= '<tr><td style="border: 0px;">'.$_ARRAYLANG['TXT_MEDIADIR_GOOGLE_MAP_ZOOM'].':&nbsp;&nbsp;</td><td style="border: 0px; padding-bottom: 2px;"><input type="text" name="'.$this->moduleName.'Inputfield['.$intId.'][zoom]" id="'.$strZoomId.'" value="'.$strValueZoom.'" onfocus="this.select();" /></td></tr>';
                    $strInputfield .= '</table><br />';
                    $strInputfield .= '<div id="'.$strMapId.'" style="border: solid 1px #0A50A1; width: 418px; height: 300px;"></div>';
                    if($this->arrSettings['settingsGoogleMapAllowKml'] == 1) {
                        $strInputfield .= '<br /><table cellpadding="0" cellspacing="0" border="0" class="'.$this->moduleName.'TableGoogleMap">';
                        $strInputfield .= '<tr><td style="border: 0px;">Routedatei (*.kml):&nbsp;&nbsp;</td><td style="border: 0px; padding-bottom: 2px;">'.$strKmlPreview.'<input type="text" name="'.$this->moduleName.'Inputfield['.$intId.'][kml]" value="'.$strValueKml.'" id="'.$strKmlId.'"  style="width: 238px;" onfocus="this.select();" />&nbsp;<input type="button" value="Durchsuchen" onClick="getFileBrowser(\''.$strKmlId.'\', \''.$this->moduleName.'\', \'/uploads\')" /></td></tr>';
                        $strInputfield .= '</table>';
                    }

                } else {
                    $strInputfield  = '<div class="'.$this->moduleName.'GoogleMap" style="float: left; height: auto ! important;">';
                    $strInputfield .= '<fieldset class="'.$this->moduleName.'FieldsetGoogleMap">';
                    $strInputfield .= '<legend>'.$_ARRAYLANG['TXT_MEDIADIR_GOOGLE_MAP_SEARCH_ADDRESS'].'</legend>';
                    $strInputfield .= '<table cellpadding="0" cellspacing="0" border="0" class="'.$this->moduleName.'TableGoogleMap">';
                    $strInputfield .= '<tr><td>'.$_ARRAYLANG['TXT_MEDIADIR_GOOGLE_MAP_STREET'].':&nbsp;&nbsp;</td><td><input type="text" name="'.$this->moduleName.'Inputfield['.$intId.'][street]" id="'.$strStreetId.'" class="'.$this->moduleName.'InputfieldGoogleMapLarge" value="" onfocus="this.select();" /></td></tr>';
                    $strInputfield .= '<tr><td>'.$_ARRAYLANG['TXT_MEDIADIR_GOOGLE_MAP_CITY'].':&nbsp;&nbsp;</td><td><input type="text" name="'.$this->moduleName.'Inputfield['.$intId.'][place]" id="'.$strZipId.'" class="'.$this->moduleName.'InputfieldGoogleMapLarge" value="" onfocus="this.select();" /></td></tr>';
                    $strInputfield .= '<tr><td>'.$_ARRAYLANG['TXT_MEDIADIR_GOOGLE_MAP_ZIP'].':&nbsp;&nbsp;</td><td><input type="text" name="'.$this->moduleName.'Inputfield['.$intId.'][zip]" id="'.$strCityId.'" class="'.$this->moduleName.'InputfieldGoogleMapSmall" value="" onfocus="this.select();" /><input type="button" onclick="searchAddress();" name="'.$this->moduleName.'Inputfield['.$intId.'][search]" id="'.$this->moduleName.'Inputfield_'.$intId.'_search" value="'.$_CORELANG['TXT_SEARCH'].'" /></td></tr>';
                    $strInputfield .= '<tr><td coldpan="2"><br /></td></tr>';
                    $strInputfield .= '<tr><td>'.$_ARRAYLANG['TXT_MEDIADIR_GOOGLE_MAP_LON'].':&nbsp;&nbsp;</td><td><input type="text" name="'.$this->moduleName.'Inputfield['.$intId.'][lon]" id="'.$strLonId.'" class="'.$this->moduleName.'InputfieldGoogleMapLarge" value="'.$strValueLon.'" onfocus="this.select();" /></td></tr>';
                    $strInputfield .= '<tr><td>'.$_ARRAYLANG['TXT_MEDIADIR_GOOGLE_MAP_LAT'].':&nbsp;&nbsp;</td><td><input type="text" name="'.$this->moduleName.'Inputfield['.$intId.'][lat]" id="'.$strLatId.'" class="'.$this->moduleName.'InputfieldGoogleMapLarge" value="'.$strValueLat.'" onfocus="this.select();" /></td></tr>';
                    $strInputfield .= '<tr><td>'.$_ARRAYLANG['TXT_MEDIADIR_GOOGLE_MAP_ZOOM'].':&nbsp;&nbsp;</td><td><input type="text" name="'.$this->moduleName.'Inputfield['.$intId.'][zoom]" id="'.$strZoomId.'" class="'.$this->moduleName.'InputfieldGoogleMapSmall" value="'.$strValueZoom.'" onfocus="this.select();" /></td></tr>';
                    $strInputfield .= '</table>';
                    $strInputfield .= '</fieldset>';
                    $strInputfield .= '</div>';
                    $strInputfield .= '<div class="'.$this->moduleName.'GoogleMap" style="float: left; height: auto ! important;">';
                    $strInputfield .= '<div id="'.$strMapId.'" class="map"></div>';
                    $strInputfield .= '</div>';
                    if($this->arrSettings['settingsGoogleMapAllowKml'] == 1) {
                        $strInputfield .= '<div class="'.$this->moduleName.'GoogleMap" style="float: left; height: auto ! important;">';
                        $strInputfield .= '<br /><fieldset class="'.$this->moduleName.'FieldsetGoogleMap">';
                        $strInputfield .= '<legend>Routedatei beifügen</legend>';
                        $strInputfield .= '<table cellpadding="0" cellspacing="0" border="0" class="'.$this->moduleName.'TableGoogleMap">';
                        $strInputfield .= '<tr><td>Datei (*.kml):&nbsp;&nbsp;</td><td>'.$strKmlPreview.'<input type="file" name="kmlUpload_'.$intId.'" value="'.$strValueKml.'" id="'.$strKmlId.'" class="'.$this->moduleName.'InputfieldGoogleMapFile" /><input name="'.$this->moduleName.'Inputfield['.$intId.'][kml]" value="'.$strValueKml.'" type="hidden"></td></tr>';
                        $strInputfield .= '</table>';
                        $strInputfield .= '</fieldset>';
                        $strInputfield .= '</div>';
                    }
                }


                $strInputfield .= <<<EOF
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
        map.addControl(new GMapTypeControl());
        map.addMapType(G_PHYSICAL_MAP);


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

var tmpGoogleMapOnLoad = window.onload; window.onload = function() { if(tmpGoogleMapOnLoad){tmpGoogleMapOnLoad();} initialize(); }
//]]>
</script>
EOF;

                return $strInputfield;

                break;
        }
    }



    function saveInputfield($intInputfieldId, $arrValue)
    {
        global $objInit;

        $lat  = floatval($arrValue['lat']);
        $lon  = floatval($arrValue['lon']);
        $zoom = floatval($arrValue['zoom']);

        if($objInit->mode == 'backend') {
            if ($_POST["deleteMedia"][$intInputfieldId] != 1) {
                $strGeoXml = contrexx_addslashes($_POST[$this->moduleName.'Inputfield'][$intInputfieldId]['kml']);
            } else {
                $strGeoXml = null;
            }
        } else {
            if (!empty($_FILES['kmlUpload_'.$intInputfieldId]['name']) || $_POST["deleteMedia"][$intInputfieldId] == 1) {
                $this->deleteKml($_POST[$this->moduleName.'Inputfield'][$intInputfieldId]['kml']);

                if ($_POST["deleteMedia"][$intInputfieldId] != 1) {
                    $strGeoXml = $this->uploadMedia($intInputfieldId);
                } else {
                    $strGeoXml = null;
                }
            } else {
                $strGeoXml = contrexx_addslashes($_POST[$this->moduleName.'Inputfield'][$intInputfieldId]['kml']);
            }
        }

        $strValue = contrexx_addslashes($lon.','.$lat.','.$zoom.','.$strGeoXml);

        return $strValue;
    }


    function deleteKml($strPathKml)
    {
        if(!empty($strPathKml)) {
            $objFile = new File();
            $arrFileInfo = pathinfo($strPathKml);
            $kmlName    = $arrFileInfo['basename'];

            //delete kml
            if (file_exists(ASCMS_PATH.$strPathKml)) {
                $objFile->delFile($this->imagePath, $this->imageWebPath, 'uploads/'.$kmlName);
            }
        }
    }



    function uploadMedia($intInputfieldId)
    {
        global $objDatabase;

        if (isset($_FILES)) {
            $tmpKml   = $_FILES['kmlUpload_'.$intInputfieldId]['tmp_name'];
            $kmlName  = $_FILES['kmlUpload_'.$intInputfieldId]['name'];

            if ($kmlName != "") {
                //get extension
                $arrKmlInfo   = pathinfo($kmlName);
                $kmlExtension = !empty($arrKmlInfo['extension']) ? '.'.$arrKmlInfo['extension'] : '';
                $kmlBasename  = $arrKmlInfo['filename'];
                $randomSum    = rand(10, 99);


                //check filename
                if (file_exists($this->imagePath.'uploads/'.$kmlName)) {
                    $kmlName = $kmlBasename.'_'.time().$kmlExtension;
                }

                //upload file
                if (move_uploaded_file($tmpKml, $this->imagePath.'uploads/'.$kmlName)) {
                    $objFile = new File();
                    $objFile->setChmod($this->imagePath, $this->imageWebPath, 'uploads/'.$kmlName);
                    return contrexx_addslashes($this->imageWebPath.'uploads/'.$kmlName);
                } else {
                    return false;
                }
            } else {
                return false;
            }
        } else {
            return false;
        }
    }



    function deleteContent($intEntryId, $intIputfieldId)
    {
        global $objDatabase;

        $objDeleteKmlFile = $objDatabase->Execute("SELECT value FROM ".DBPREFIX."module_".$this->moduleTablePrefix."_rel_entry_inputfields WHERE `entry_id`='".intval($intEntryId)."' AND  `field_id`='".intval($intIputfieldId)."'");
        if($objDeleteKmlFile !== false) {
            $strValue  = $objDeleteKmlFile->fields['value'];
            $arrValues = explode(',', $strValue);
            $strKmlPath = $arrValues[3];
            if(!empty($strKmlPath)) {
                $this->deleteKml($strKmlPath);
            }
        }

        $objDeleteInputfield = $objDatabase->Execute("DELETE FROM ".DBPREFIX."module_".$this->moduleTablePrefix."_rel_entry_inputfields WHERE `entry_id`='".intval($intEntryId)."' AND  `field_id`='".intval($intIputfieldId)."'");

        if($objDeleteEntry !== false) {
            return true;
        } else {
            return false;
        }
    }



    function getContent($intEntryId, $arrInputfield, $arrTranslationStatus)
    {
         global $objDatabase, $_CONFIG, $_ARRAYLANG;

        $intId = intval($arrInputfield['id']);

        $objInputfieldValue = $objDatabase->Execute("
            SELECT
                `value`
            FROM
                ".DBPREFIX."module_".$this->moduleTablePrefix."_rel_entry_inputfields
            WHERE
                field_id=".$intId."
            AND
                entry_id=".$intEntryId."
            LIMIT 1
        ");

        $strValue  = htmlspecialchars($objInputfieldValue->fields['value'], ENT_QUOTES, CONTREXX_CHARSET);
        $arrValues = explode(',', $strValue);

        $strValueLon = $arrValues[0];
        $strValueLat = $arrValues[1];
        $strValueZoom = $arrValues[2];
        $strValueGeoXml = $arrValues[3];
        $strValueLink = '<a href="http://maps.google.com/maps?q='.$arrValues[0].','.$arrValues[1].'" target="_blank">'.$_ARRAYLANG['TXT_MEDIADIR_GOOGLEMAPS_LINK'].'</a>';
        $strValueLinkHref = 'http://maps.google.com/maps?q='.$arrValues[0].','.$arrValues[1];

         if(!empty($strValueGeoXml) || $strValueGeoXml != 0){
            $strServerProtocol = ASCMS_PROTOCOL."://";
            $strServerName = $_SERVER['SERVER_NAME'];
            $strServerKmlWebPath = constant('ASCMS_'.$this->moduleConstVar.'_IMAGES_WEB_PATH').'/uploads';

            $strGeoXmlPath = $strServerProtocol.$strServerName.$strServerKmlWebPath.$strValueGeoXml;
            //test only
            //$strGeoXmlPath = 'http://mapgadgets.googlepages.com/cta.kml';
            $strHideGeoXml = false;
            $strMouseover = 'loadGeoXml(kml'.$intEntryId.');';
            $strMouseout = 'hideGeoXml(kml'.$intEntryId.');';
        } else {
            $strGeoXmlPath = null;
            $strHideGeoXml = true;
            $strMouseover = null;
            $strMouseout = null;
        }

        if(!empty($strValue)) {
            $objGoogleMap = new googleMap();
            $objGoogleMap->setMapId($this->moduleName.'Inputfield_'.$intId.'_map');
            $objGoogleMap->setMapStyleClass('map');
            $objGoogleMap->setMapType(0);
            $objGoogleMap->setMapZoom($arrValues[2]);
            $objGoogleMap->setMapCenter($arrValues[0], $arrValues[1]);

            $objGoogleMap->addMapMarker($intId, $strValueLon, $strValueLat, null, true, $strGeoXmlPath, $strHideGeoXml);

            $arrContent['TXT_'.$this->moduleLangVar.'_INPUTFIELD_NAME'] = htmlspecialchars($arrInputfield['name'][0], ENT_QUOTES, CONTREXX_CHARSET);
            $arrContent[$this->moduleLangVar.'_INPUTFIELD_VALUE'] = $objGoogleMap->getMap();
            $arrContent[$this->moduleLangVar.'_INPUTFIELD_LINK'] = $strValueLink;
            $arrContent[$this->moduleLangVar.'_INPUTFIELD_LINK_HREF'] = $strValueLinkHref;
        } else {
            $arrContent = null;
        }

        return $arrContent;
    }



    function getJavascriptCheck()
    {
        parent::getSettings();

        $fieldName = $this->moduleName."Inputfield_";
        
        if($this->arrSettings['settingsGoogleMapAllowKml'] == 1) {
            $strKmlCheck  = <<<EOF
                value_kml = document.getElementById('$fieldName' + field + '_kml').value;

                if (value_kml != "") {
                	ending = value_kml.substr(-4);
                    if(ending != '.kml') {
                        isOk = false;
                    	document.getElementById('$fieldName' + field + '_kml').style.border = "#ff0000 1px solid";
                    } else {
                	   document.getElementById('$fieldName' + field + '_kml').style.borderColor = '';
                    }
                }  else {
                    document.getElementById('$fieldName' + field + '_kml').style.borderColor = '';
                }
EOF;
        } else {
            $strKmlCheck  = '';
        }

        $strJavascriptCheck = <<<EOF

            case 'google_map':
                value_lon = document.getElementById('$fieldName' + field + '_lon').value;
                value_lat = document.getElementById('$fieldName' + field + '_lat').value;
                value_zoom = document.getElementById('$fieldName' + field + '_zoom').value;

                if ((value_lon == "" || value_lat == "" || value_zoom == "") && isRequiredGlobal(inputFields[field][1], value)) {
                    isOk = false;
                	if (value_lon == "" && isRequiredGlobal(inputFields[field][1], value)) {
                    	document.getElementById('$fieldName' + field + '_lon').style.border = "#ff0000 1px solid";
                    }

                    if (value_lat == "" && isRequiredGlobal(inputFields[field][1], value)) {
                    	document.getElementById('$fieldName' + field + '_lat').style.border = "#ff0000 1px solid";
                    }

                    if (value_zoom == "" && isRequiredGlobal(inputFields[field][1], value)) {
                    	document.getElementById('$fieldName' + field + '_zoom').style.border = "#ff0000 1px solid";
                    }
                }  else {
                	document.getElementById('$fieldName' + field + '_lon').style.borderColor = '';
                	document.getElementById('$fieldName' + field + '_lat').style.borderColor = '';
                	document.getElementById('$fieldName' + field + '_zoom').style.borderColor = '';
                }

                $strKmlCheck

                break;

EOF;
        return $strJavascriptCheck;
    }
    
    
    function getFormOnSubmit($intInputfieldId)
    {
        return null;
    }
}
