<?php
/**
 * Media  Directory Map Class
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
require_once ASCMS_MODULE_PATH . '/mediadir/lib/entry.class.php';


require_once ASCMS_LIBRARY_PATH.'/googleServices/googleMap.class.php';

class mediaDirectoryMap extends mediaDirectoryLibrary
{
    var $strKey;
    var $testMap;

    /**
     * Constructor
     */
    function __construct()
    {
        global $_CONFIG;

        $this->strKey = $_CONFIG['googleMapsAPIKey'];

        parent::getSettings();

        $this->testView();
    }

    function showMap($objTpl)
    {
        $objEntries = new mediaDirectoryEntry();
        $objEntries->getEntries();
        $srtMarkers = $objEntries->listEntries($objTpl, 4);



        $arrValues = explode(',', $this->arrSettings['settingsGoogleMapStartposition']);

        $strValueLon = $arrValues[0];
        $strValueLat = $arrValues[1];
        $strValueZoom = $arrValues[2];

        $strJavascript = <<<EOF
<script src="http://maps.google.com/maps?file=api&amp;v=2&amp;key=$this->strKey" type="text/javascript"></script>
<script type="text/javascript">

var map;
var geoXml;


function initialize() {
  if (GBrowserIsCompatible()) {
    map = new GMap2(document.getElementById("mediadirMapOverview"));
    var center = new GLatLng($strValueLon, $strValueLat);

    map.setCenter(center, $strValueZoom);


    map.addControl(new GLargeMapControl());
    map.addControl(new GMapTypeControl());

    map.addMapType(G_PHYSICAL_MAP);

    $srtMarkers
  }
}

function loadGeoXml(geoXml) {
    map.addOverlay(geoXml);
}

function hideGeoXml(geoXml) {
    map.removeOverlay(geoXml);
}

var tmpGoogleMapOnLoad = window.onload; window.onload = function() { if(tmpGoogleMapOnLoad){tmpGoogleMapOnLoad();} initialize(); }
</script>
EOF;

        $strMap = '<div id="mediadirMapOverview" class="mapLarge"></div>';

        $objTpl->setVariable(array(
            'MEDIADIR_GOOGLE_MAP' => $strMap.$strJavascript,
            'MEDIADIR_GOOGLE_MAP_TEST' => $this->testMap,
        ));
    }

    function testView()
    {
        $objGoggleMap = new googleMap();
        $objGoggleMap->setMapId('googleMap2');
        $objGoggleMap->setMapStyleClass('mapLarge');
        $objGoggleMap->setMapType(2);

        $arrValues = explode(',', $this->arrSettings['settingsGoogleMapStartposition']);
        $objGoggleMap->setMapZoom($arrValues[2]);
        $objGoggleMap->setMapCenter($arrValues[0], $arrValues[1]);

        $objEntries = new mediaDirectoryEntry();
        $objEntries->getEntries();

        print_r("<pre>");
        print_r($objEntries->arrEntries);
        print_r("</pre>");

        foreach ("")

        $objGoggleMap->addMarkers($objEntries->listEntries($objTpl, 4));

        $this->testMap = $objGoggleMap->getMap();
    }
}