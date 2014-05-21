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
 * @subpackage  core_json
 */
class JsonMediaBrowser implements JsonAdapter {

    private $_path = "";
    private $_mediaType = "";

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
        return array('getFiles');
    }

    /**
     * Returns all messages as string
     * @return String HTML encoded error messages
     */
    public function getMessagesAsString() {
        return '';
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
            if ($splFileInfo->isDir()) {
                $extension = 'dir';
            } else {
                $extension = pathinfo($splFileInfo->getFilename(), PATHINFO_EXTENSION);
            }

            $fileInfos = array(
                'name' => $splFileInfo->getFilename(),
                'size' => $splFileInfo->getSize(),
                'extension' => ucfirst($extension),
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

            $path = array($splFileInfo->getFilename() => array('datainfo' => $fileInfos));


            for ($depth = $ritit->getDepth() - 1; $depth >= 0; $depth--) {
                $path = array($ritit->getSubIterator($depth)->current()->getFilename() => $path);
            }
            $r = array_merge_recursive($r, $path);
        }

        return ($r);
    }

    private
            function directoryToArray($directory, $recursive) {
        $array_items = array();
        if ($handle = opendir($directory)) {
            while (false !== ($file = readdir($handle))) {
                if ($file != "." && $file != "..") {



                    if (is_dir($directory . "/" . $file)) {
                        if ($recursive) {
                            $array_items = array_merge($array_items, $this->directoryToArray($directory . "/" . $file, $recursive));
                        }
                        $file = $directory . "/" . $file;
                        $array_items[] = preg_replace("/\/\//si", "/", $file);
                        echo 'voila: ' . $file;
                    } else {
                        $file = $directory . "/" . $file;
                        $array_items[] = preg_replace("/\/\//si", "/", $file);
                    }
                }
            }
            closedir($handle);
        }
        return $array_items;
    }

}
