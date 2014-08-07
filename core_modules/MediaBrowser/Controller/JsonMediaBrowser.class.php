<?php

/**
 * JSON Adapter for Uploader
 * @copyright   Comvation AG
 * @author      Tobias Schmoker <tobias.schmoker@comvation.com>
 * @package     contrexx
 * @subpackage  core_json
 */

namespace Cx\Core_Modules\MediaBrowser\Controller;

use \Cx\Core\Json\JsonAdapter;

/**
 * JSON Adapter for Uploader
 * @copyright   Comvation AG
 * @author      Tobias Schmoker <tobias.schmoker@comvation.com>
 * @package     contrexx
 * @subpackage coremodule_mediabrowser
 */
class JsonMediaBrowser implements JsonAdapter {

    protected $_path = "";
    protected $_mediaType = "";

    /**
     * Returns the internal name used as identifier for this adapter
     * @return String Name of this adapter
     */
    public function getName() {
        return 'MediaBrowser';
    }

    /**
     * Returns an array of method names accessable from a JSON request
     * @return array List of method names
     */
    public function getAccessableMethods() {
        return array('getFiles', 'getSites', 'getSources');
    }

    /**
     * Returns all messages as string
     * @return String HTML encoded error messages
     */
    public function getMessagesAsString() {
        return '';
    }

    public function getSources() {
        global $_ARRAYLANG, $_CORELANG;

        // standard
        $return[] = array(
            'name' => 'Dateien',
            'value' => 'files',
            'path' => array_values(array_filter(explode('/', MediaBrowserConfiguration::get()->mediaTypePaths['files'][1])))
        );

        foreach (MediaBrowserConfiguration::get()->mediaTypes as $type => $name) {
            if (!$this->_checkForModule($type)) {
                continue;
            }
            $name = $_ARRAYLANG[$name];
            if (empty($name)) {
                $name = $_CORELANG[$name];
            }
            $return[] = array(
                'name' => $name,
                'value' => $type,
                'path' => array_values(array_filter(explode('/', MediaBrowserConfiguration::get()->mediaTypePaths[$type][1])))
            );
        }
        return $return;
    }

    public function getFiles($params) {
        $this->_path = (strlen($params['get']['path']) > 0) ? $params['get']['path'] : '/';
        $this->_mediaType = (strlen($params['get']['mediatype']) > 0) ? $params['get']['mediatype'] : 'files';

        $retFiles = array();
        $retDirectories = array();
        /* paramas
          current $path
          current $strPath

         */

        if (array_key_exists($this->_mediaType, MediaBrowserConfiguration::get()->mediaTypePaths)) {
            $strPath = MediaBrowserConfiguration::get()->mediaTypePaths[$this->_mediaType][0] . $this->_path;
        } else {
            $strPath = ASCMS_CONTENT_IMAGE_PATH . $this->_path;
        }

        $ritit = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($strPath), \RecursiveIteratorIterator::SELF_FIRST);
        $r = array();
        $i = 0;
        foreach ($ritit as $splFileInfo) {
            $extension = 'Dir';
            if (!$splFileInfo->isDir()) {
                $extension = ucfirst(pathinfo($splFileInfo->getFilename(), PATHINFO_EXTENSION));
            }

            // set preview if image
            $preview = 'none';
            if (preg_match("/(jpg|jpeg|gif|png)/i", ucfirst($extension) )){
                $preview = ASCMS_PATH_OFFSET . str_replace(ASCMS_DOCUMENT_ROOT, '', $splFileInfo->getRealPath());
                $preview = str_replace('.' . lcfirst($extension), \Cx\Core_Modules\Uploader\Controller\UploaderConfiguration::get()->thumbnails[0]['value'] . '.' . lcfirst($extension), $preview);
            }

            $fileInfos = array(
                'name' => $splFileInfo->getFilename(),
                'size' => $this->formatBytes($splFileInfo->getSize()),
                'cleansize' => $splFileInfo->getSize(),
                'extension' => ucfirst(mb_strtolower($extension)),
                'preview' => $preview,
                'active' => false // preselect in mediabrowser or mark a folder
            );

            // filters
            if (
                    $fileInfos['name'] == '.' ||
                    $fileInfos['name'] == '..' ||
                    preg_match('/\.thumb$/', $fileInfos['name']) ||
                    $fileInfos['name'] == 'index.php' ||
                    (0 === strpos($fileInfos['name'], '.'))
            ) {
                continue;
            }
            
            // filter thumbnail images
            $thumbFilter = false;
            foreach (\Cx\Core_Modules\Uploader\Controller\UploaderConfiguration::get()->thumbnails as $thumbnail) {
                if (false !== strpos($fileInfos['name'], $thumbnail['value'].'.')) {
                        $thumbFilter = true;
                }
            }
            if ($thumbFilter) {
                continue;
            }

            $path = array($splFileInfo->getFilename() => array('datainfo' => $fileInfos));


            for ($depth = $ritit->getDepth() - 1; $depth >= 0; $depth--) {
                $path = array($ritit->getSubIterator($depth)->current()->getFilename() => $path);
            }
            $r = array_merge_recursive($r, $path);
        }
        return ($r);
    }

    public function getSites($params) {
        $jd = new \Cx\Core\Json\JsonData();
        $data = $jd->data('node', 'getTree', array('get' => array('recursive' => 'true')));
        $pageStack = array();
        $ref = 0;
        $data['data']['tree'] = array_reverse($data['data']['tree']);
        foreach ($data['data']['tree'] as &$entry) {
            $entry['attr']['level'] = 0;
            array_push($pageStack, $entry);
        }
        $return = array();
        while (count($pageStack)) {
            $entry = array_pop($pageStack);
            $page = $entry['data'][0];
            $arrPage['level'] = $entry['attr']['level'];
            $arrPage['node_id'] = $entry['attr']['rel_id'];
            $children = $entry['children'];
            $children = array_reverse($children);
            foreach ($children as &$entry) {
                $entry['attr']['level'] = $arrPage['level'] + 1;
                array_push($pageStack, $entry);
            }
            $arrPage['catname'] = $page['title'];
            $arrPage['catid'] = $page['attr']['id'];
            $arrPage['lang'] = BACKEND_LANG_ID;
            $arrPage['protected'] = $page['attr']['protected'];
            $arrPage['type'] = \Cx\Core\ContentManager\Model\Entity\Page::TYPE_CONTENT;
            $arrPage['alias'] = $page['title'];
            $arrPage['frontend_access_id'] = $page['attr']['frontend_access_id'];
            $arrPage['backend_access_id'] = $page['attr']['backend_access_id'];

            // JsonNode does not provide those
            //$arrPage['level'] = ;
            //$arrPage['type'] = ;
            //$arrPage['parcat'] = ;
            //$arrPage['displaystatus'] = ;
            //$arrPage['moduleid'] = ;
            //$arrPage['startdate'] = ;
            //$arrPage['enddate'] = ;
            // But we can simulate level and type for our purposes: (level above)
            $jsondata = json_decode($page['attr']['data-href']);
            $path = $jsondata->path;
            if (trim($jsondata->module) != '') {
                $arrPage['type'] = \Cx\Core\ContentManager\Model\Entity\Page::TYPE_APPLICATION;
                $module = explode(' ', $jsondata->module, 2);
                $arrPage['modulename'] = $module[0];
                if (count($module) > 1) {
                    $arrPage['cmd'] = $module[1];
                }
            }

            $url = "'" . '[[' . \Cx\Core\ContentManager\Model\Entity\Page::PLACEHOLDER_PREFIX;

// TODO: This only works for regular application pages. Pages of type fallback that are linked to an application
//       will be parsed using their node-id ({NODE_<ID>})
            if (($arrPage['type'] == \Cx\Core\ContentManager\Model\Entity\Page::TYPE_APPLICATION) && ($this->_mediaMode !== 'alias')) {
                $url .= $arrPage['modulename'];
                if (!empty($arrPage['cmd'])) {
                    $url .= '_' . $arrPage['cmd'];
                }

                $url = strtoupper($url);
            } else {
                $url .= $arrPage['node_id'];
            }

            // if language != current language or $alwaysReturnLanguage
            if ($this->_frontendLanguageId != FRONTEND_LANG_ID ||
                    (isset($_GET['alwaysReturnLanguage']) &&
                    $_GET['alwaysReturnLanguage'] == 'true')) {
                $url .= '_' . $this->_frontendLanguageId;
            }
            $url .= "]]'";

            $return[] = array(
                'click' => "javascript:{setUrl($url,null,null,'" . \FWLanguage::getLanguageCodeById($this->_frontendLanguageId) . $path . "','page')}",
                'name' => $arrPage['catname'],
                'extension' => 'Html',
                'level' => $arrPage['level']
            );
        }
        return $return;
    }

    protected function formatBytes($bytes, $unit = "", $decimals = 2) {
        $units = array('B' => 0, 'KB' => 1, 'MB' => 2, 'GB' => 3, 'TB' => 4,
            'PB' => 5, 'EB' => 6, 'ZB' => 7, 'YB' => 8);

        $value = 0;
        if ($bytes > 0) {
            // Generate automatic prefix by bytes 
            // If wrong prefix given
            if (!array_key_exists($unit, $units)) {
                $pow = floor(log($bytes) / log(1024));
                $unit = array_search($pow, $units);
            }

            // Calculate byte value by prefix
            $value = ($bytes / pow(1024, floor($units[$unit])));
        }

        // If decimals is not numeric or decimals is less than 0 
        // then set default value
        if (!is_numeric($decimals) || $decimals < 0) {
            $decimals = 2;
        }

        // Format output
        return sprintf('%.' . $decimals . 'f ' . $unit, $value);
    }

    /**
     * checks whether a module is available and active
     * @param $strModuleName
     * @return bool
     */
    function _checkForModule($strModuleName) {
        global $objDatabase;
        if (($objRS = $objDatabase->SelectLimit("SELECT `status` FROM " . DBPREFIX . "modules WHERE name = '" . $strModuleName . "' AND `is_active` = '1' AND `is_licensed` = '1'", 1)) != false) {
            if ($objRS->RecordCount() > 0) {
                if ($objRS->fields['status'] == 'n') {
                    return false;
                }
                return true;
            }
            return false;
        }
        return true;
    }

    /**
     * Returns default permission as object
     * @return Object
     */
    public function getDefaultPermissions()
    {
        // TODO: Implement getDefaultPermissions() method.
    }
}
