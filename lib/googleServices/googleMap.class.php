<?php

/**
 * Google Map
 * @copyright   CONTREXX CMS - COMVATION AG
 * @author      Comvation Development Team <info@comvation.com>
 * @version     1.0.0
 */

/**
 * Includes
 */


class googleMap
{
    private $apiKey;

    private $mapModus = 'overview';
    private $mapDimensions;
    private $mapZoom = 1;
    private $mapControls = true;
    private $mapCenter = 'var center = new GLatLng(0, 0)';
    private $mapId = 'googleMap';
    private $mapType = 0;
    private $mapClass;
    private $mapMarkers = array();
    private $mapIndex = 1;

    /**
     * Constructor
     */
    function __construct($modus)
    {
        global $_CONFIG;

        $this->apiKey = $_CONFIG['googleMapsAPIKey'];
    }


    function setMapModus($modus)
    {
        $this->mapModus = $modus;
    }
    
    function setMapIndex($index)
    {
        $this->mapIndex = $index;
    }
    
    
    function setMapControls($controls)
    {
        $this->mapControls = $controls;
    }
    
    function getMapControls()
    {
    	if ($this->mapControls) {
    		$controls = 'map_'.$this->mapIndex.'.addControl(new GLargeMapControl());';
    		$controls .= 'map_'.$this->mapIndex.'.addControl(new GMapTypeControl());';
    	} else {
    		$controls = '';
    	}
        return $controls;
    }


    function setMapId($id)
    {
        $this->mapId = $id;
    }



    function setMapDimensions($width, $height)
    {
        $this->mapDimensions = 'style="width: '.intval($width).'px; height: '.intval($height).'px;"';
    }



    function setMapStyleClass($class)
    {
        $this->mapClass = 'class="'.$class.'"';
    }



    function setMapZoom($zoom)
    {
        $this->mapZoom = intval($zoom);
    }



    function setMapCenter($lon, $lat)
    {
        $this->mapCenter = 'var center = new GLatLng('.$lon.', '.$lat.')';
    }



    function setMapType($type)
    {
        /*
        Types:
        [0] map
        [1] satellite
        [2] hybrid
        */

        $this->mapType = intval($type);
    }



    function addMapMarker($id, $lon, $lat, $info, $hideInfo=true, $kml=null, $hideKml=true, $click=null, $mouseover=null, $mouseout=null, $icon=null)
    {
        $this->mapMarkers[$id]['lon'] = $lon;
        $this->mapMarkers[$id]['lat'] = $lat;
        $this->mapMarkers[$id]['info'] = $info;
        $this->mapMarkers[$id]['hideInfo'] = $hideInfo;
        $this->mapMarkers[$id]['kml'] = $kml;
        $this->mapMarkers[$id]['click'] = $click;
        $this->mapMarkers[$id]['mouseover'] = $mouseover;
        $this->mapMarkers[$id]['mouseout'] = $mouseout;
        $this->mapMarkers[$id]['hideKml'] = $hideKml;
        $this->mapMarkers[$id]['icon'] = $icon;
    }



    private function getMapMarkers()
    {
        foreach ($this->mapMarkers as $id => $marker) {
            if(intval($marker['lon']) != 0 && intval($marker['lat']) != 0) {
                if ($marker['kml'] != null) {
                    $kml = "var kml$id = new GGeoXml('".$marker['kml']."');";
                } else {
                    $kml = '';
                }

                if ($marker['click'] != null) {
                    $click = "GEvent.addListener(marker$id, \"click\", function() {
                        ".$marker['click']."
                    });";
                } else {
                    $mouseover = '';
                }

                if ($marker['mouseover'] != null) {
                    $mouseover = "GEvent.addListener(marker$id, \"mouseover\", function() {
                        ".$marker['mouseover']."
                    });";
                } else {
                    $mouseover = '';
                }

                if ($marker['mouseout'] != null) {
                    $mouseout = "GEvent.addListener(marker$id, \"mouseout\", function() {
                        ".$marker['mouseout']."
                    });";
                } else {
                    $mouseout = '';
                }

                if (!$marker['hideKml']) {
                    $showKml = "map_".$this->mapIndex.".addOverlay(kml$id);";
                } else {
                    $showKml = '';
                }

                if (!$marker['hideInfo']) {
                    $showInfo = "marker$id.openInfoWindowHtml(info$id');";
                } else {
                    $showInfo = '';
                }

                $km = 10;
                $factor = 0.009009009009009;
                $dist = $km*$factor;

                $divLatPlus = $marker['lat']+$dist;
                $divLatMinus = $marker['lat']-$dist;
                $divLonPlus = $marker['lon']+$dist;
                $divLonMinus = $marker['lon']-$dist;

                $markers .= "
                var point$id = new GLatLng(".$marker['lon'].", ".$marker['lat'].");
                var marker$id = new GMarker(point$id);
                var info$id = '".$marker['info']."';

                ".$kml."
                ".$click."
                ".$mouseover."
                ".$mouseout."
                ".$showKml."
                ".$showInfo."

                map_".$this->mapIndex.".addOverlay(marker$id);

                ";
            }
        }

        return $markers;
    }



    function getMap()
    {
        if($this->mapModus == 'search') {
            $map = self::getSearchMap();
        } else {
            $map = self::getOverviewMap();
        }

        return $map;
    }


    private function getOverviewMap()
    {
        $layer = '<div id="'.$this->mapId.'" '.$this->mapClass.' '.$this->mapDimensions.'></div>';

        $markers = $this->getMapMarkers();
        $controls = $this->getMapControls();
        
        $map = "map_".$this->mapIndex;
        $geoXml = "geoXml_".$this->mapIndex;
        $initialize = "initialize_".$this->mapIndex;
        $tmpGoogleMapOnLoad = "tmpGoogleMapOnLoad_".$this->mapIndex;

        $javascript = <<<EOF
<script src="http://maps.google.com/maps?file=api&amp;v=2&amp;key=$this->apiKey" type="text/javascript"></script>
<script type="text/javascript">

var $map;
var $geoXml;

function $initialize() {
  if (GBrowserIsCompatible()) {
    $map = new GMap2(document.getElementById('$this->mapId'));
    $this->mapCenter

    $map.setCenter(center, $this->mapZoom);

    $controls

    types = $map.getMapTypes();
    $map.setMapType(types[$this->mapType]);

    $markers
  }
}

function loadGeoXml(geoXml) {
    $map.addOverlay(geoXml);
}

function hideGeoXml(geoXml) {
    $map.removeOverlay(geoXml);
}

var $tmpGoogleMapOnLoad = window.onload; 
window.onload = function() { 
	if($tmpGoogleMapOnLoad){
		$tmpGoogleMapOnLoad();
    } 
    $initialize(); 
}
</script>
EOF;

        return $layer.$javascript;
    }



    private function getSearchMap()
    {
        return "not yet implemented";
    }
}

?>