<?php

/**
 * Cloudrexx
 *
 * @link      http://www.cloudrexx.com
 * @copyright Cloudrexx AG 2007-2015
 * 
 * According to our dual licensing model, this program can be used either
 * under the terms of the GNU Affero General Public License, version 3,
 * or under a proprietary license.
 *
 * The texts of the GNU Affero General Public License with an additional
 * permission and of our proprietary license can be found at and
 * in the LICENSE file you have received along with this program.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU Affero General Public License for more details.
 *
 * "Cloudrexx" is a registered trademark of Cloudrexx AG.
 * The licensing of the program under the AGPLv3 does not imply a
 * trademark license. Therefore any rights, title and interest in
 * our trademarks remain entirely with us.
 */
 
/**
 * Wysiwyg
 *
 * @copyright   CLOUDREXX CMS - CLOUDREXX AG
 * @author      CLOUDREXX Development Team <info@cloudrexx.com>
 * @package     cloudrexx
 * @subpackage  core_wysiwyg
 */

namespace Cx\Core\Wysiwyg;
use Cx\Core_Modules\MediaBrowser\Model\Entity\MediaBrowser;

/**
 * Class WysiwygException
 *
 * @copyright   CLOUDREXX CMS - CLOUDREXX AG
 * @author      Project Team SS4U <info@comvation.com>
 * @version     3.0.0
 * @package     cloudrexx
 * @subpackage  core_wysiwyg
 */
class WysiwygException extends \Exception {}

/**
 * Wysiqyg class
 * 
 * @copyright   CLOUDREXX CMS - CLOUDREXX AG
 * @author      Thomas Daeppen <thomas.daeppen@comvation.com>
 * @author      Michael Räss <michael.raess@comvation.com>
 * @author      Ueli Kramer <ueli.kramer@comvation.com>
 * @version     3.0.0
 * @package     cloudrexx
 * @subpackage  core_wysiwyg
 */

class Wysiwyg
{
    /**
     * options for the different types of wysiwyg editors
     * @var array the types which are available for cloudrexx wysiwyg editors
     */
    private $types = array(
        'small' => array(
            'toolbar' => 'Small',
            'width' => '100%',
            'height' => 200,
            'fullPage' => 'false',
            'extraPlugins' => array(),
        ),
        'full' => array(
            'toolbar' => 'Full',
            'width' => '100%',
            'height' => 450,
            'fullPage' => 'false',
            'extraPlugins' => array(),
        ),
        'fullpage' => array(
            'toolbar' => 'Full',
            'width' => '100%',
            'height' => 450,
            'fullPage' => 'true',
            'extraPlugins' => array(),
        ),
        'bbcode' => array(
            'toolbar' => 'BBCode',
            'width' => '100%',
            'height' => 200,
            'fullPage' => 'false',
            'extraPlugins' => array('bbcode'),
        ),
    );

    /**
     * @var string the value for the textarea html attribute "name"
     */
    private $name;
    /**
     * @var string the value for the textarea html attribute "value"
     */
    private $value;
    /**
     * @var string the type of wysiwyg editor
     */
    private $type;
    /**
     * @var int the language id of current language
     */
    private $langId;
    /**
     * @var array array of extra plugins added for the wysiwyg editor
     */
    private $extraPlugins;

    /**
     * @var int
     */
    protected $memoryLimit;

    /**
     * @var int
     */
    protected $timeLimit;

    /**
     * @var array
     */
    protected $argsForDataUriExtraction = array();

    /**
     * @var array
     */
    protected $extractedImgFiles = array();

    /**
     * constant MiB2 2megabytes
     */
    const MiB2 = 2097152;

    /**
     * Initialize WYSIWYG editor
     *
     * @param string $name the name content for name attribute
     * @param string $value content for value attribute
     * @param string $type the type of editor to use: possible types are small, full, bbcode
     * @param null|int $langId the language id
     * @param array $extraPlugins extra plugins to activate
     */
    public function __construct($name, $value = '', $type = 'small', $langId = null, $extraPlugins = array())
    {
        $this->name = $name;
        $this->value = $value;
        $this->type = strtolower($type);
        $this->langId = $langId ? intval($langId) : FRONTEND_LANG_ID;
        $this->extraPlugins = $extraPlugins;
    }

    /**
     * Get the html source code for the wysiwyg editor
     *
     * @return string
     */
    public function getSourceCode()
    {
        $mediaBrowserCkeditor = new MediaBrowser();
        $mediaBrowserCkeditor->setOptions(array('type' => 'button', 'style' => 'display:none'));
        $mediaBrowserCkeditor->setCallback('ckeditor_image_callback');
        $mediaBrowserCkeditor->setOptions(array(
                'id' => 'ckeditor_image_button'
            ));

        \JS::activate('ckeditor');
        \JS::activate('jquery');

        $configPath = ASCMS_PATH_OFFSET.substr(\Env::get('ClassLoader')->getFilePath(ASCMS_CORE_PATH.'/Wysiwyg/ckeditor.config.js.php'), strlen(ASCMS_DOCUMENT_ROOT));
        $options = array(
            "customConfig: CKEDITOR.getUrl('".$configPath."?langId=".$this->langId."')",
            "width: '" . $this->types[$this->type]['width'] . "'",
            "height: '" . $this->types[$this->type]['height'] . "'",
            "toolbar: '" . $this->types[$this->type]['toolbar'] . "'",
            "fullPage: " . $this->types[$this->type]['fullPage']
        );

        $extraPlugins = array_merge($this->extraPlugins, $this->types[$this->type]['extraPlugins']);
        if (!empty($extraPlugins)) {
            $options[] = "extraPlugins: '" . implode(',', $extraPlugins) . "'";
        }

        $onReady = "CKEDITOR.replace('".$this->name."', { %s });";
        \JS::registerCode('
            $J(function(){
                '.sprintf($onReady, implode(",\r\n", $options)).'
            });
        ');

        return $mediaBrowserCkeditor->getXHtml('mediabrowser').'<textarea name="'.$this->name.'" style="width: 100%; height: ' . $this->types[$this->type]['height'] . 'px">'.$this->value.'</textarea>';
    }

    /**
     * Get safe BBCode
     *
     * @param string $bbcode the unsafe BBCode
     * @param bool $html return as html code
     * @return string
     */
    public static function prepareBBCodeForDb($bbcode, $html = false)
    {
        $bbcode = strip_tags($bbcode);
        if ($html) {
            $bbcode = self::prepareBBCodeForOutput($bbcode);
        }
        return contrexx_input2db($bbcode);
    }

    /**
     * Convert BBCode to HTML
     *
     * This code comes from the forum module, feel free to rewrite
     *
     * @param string $bbcode the BBCode which should be a html output
     * @return string the xhtml output
     */
    public static function prepareBBCodeForOutput($bbcode)
    {
        $BBCodeHandler = new \Cx\Core\Wysiwyg\BBCodeHandler();
        return $BBCodeHandler->parse($bbcode);
    }

    /**
     * Alias for the method getSourceCode()
     *
     * @return string
     */
    public function __toString()
    {
        return $this->getSourceCode();
    }

    /**
     * @param int $langId
     */
    public function setLangId($langId)
    {
        $this->langId = $langId;
    }

    /**
     * @return int
     */
    public function getLangId()
    {
        return $this->langId;
    }

    /**
     * @param string $name
     */
    public function setName($name)
    {
        $this->name = $name;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param string $type
     */
    public function setType($type)
    {
        $this->type = strtolower($type);
    }

    /**
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @param string $value
     */
    public function setValue($value)
    {
        $this->value = $value;
    }

    /**
     * @return string
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * @param array $extraPlugins
     */
    public function setExtraPlugins($extraPlugins)
    {
        $this->extraPlugins = $extraPlugins;
    }

    /**
     * @return array
     */
    public function getExtraPlugins()
    {
        return $this->extraPlugins;
    }

    /**
     * Extracting the Data urls into the filesystem
     * 
     * @param string $content     Html content to extract data urls.
     * @param mixed  $path        Abosulte path to store images or callable function which returns path to store the images
     * @param mixed  $namePrefix  Prefix for image or callable function which returns complete filename of image
     * 
     * @return array $movedFiles Array of moved files
     * @throws \Exception
     */
    public function extractDataUrlsToFileSystem(&$content, $path, $namePrefix = 'html_inline_image')
    {
        if (empty($content) || empty($path)) {
            return array();
        }

        try {
            //Store the process started time
            $processTime = \Cx\Core\Core\Controller\Cx::instanciate()->getStartTime();

            //Pattern to extract the image data-urls from the given content
            $pattern = '/<img\s+[^>]*src=[\'\"](data\:(\s|)image\/(\w{3,4})\;base64\,(\s|)([a-z0-9\!\$\&\,\(\)\*\+\,\;\=\-\.\_\~\:\@\/\?\%\s]*)\s*)[\'\"][^>]*>/si';

            //Get the file path and filename prefix
            $filePath   = is_callable($path) ? call_user_func($path) : $path;
            $filePrefix = is_callable($namePrefix) ? call_user_func($namePrefix) : $namePrefix;

            //If the filePath not exists, return empty array
            if (!\Cx\Lib\FileSystem\FileSystem::exists($filePath)) {
                return array();
            }

            //Reset the variable $extractedImgFiles if it's not empty
            if (!empty($this->extractedImgFiles)) {
                $this->extractedImgFiles = array();
            }

            //Find the relative path for setting it as img src instead of data url
            \Cx\Lib\FileSystem\FileSystem::path_absolute_to_os_root($filePath);
            $documentPath = \Env::get('cx')->getWebsiteDocumentRootPath();
            $relativePath = \Env::get('cx')->getWebsiteOffsetPath() . str_replace($documentPath, '', $filePath);

            //Find all the occurrence of img src and 
            //store it into the location given in $filePath
            $this->argsForDataUriExtraction = array(
                                                'filePath'     => $filePath, 
                                                'filePrefix'   => $filePrefix, 
                                                'relativePath' => $relativePath, 
                                                'processTime'  => $processTime[1]);
            $content = preg_replace_callback(
                        $pattern, 
                        array($this, 'moveDataUrlsToFileSystem'), 
                        $content);
            return $this->extractedImgFiles;
        } catch(WysiwygException $e){
            \DBG::log($e->getMessage());
            throw new \Exception('Wysiwyg::extractDataUrlsToFileSystem(): Failed to extract the data urls into filesystem.');
        }
    }

    /**
     * Move the extracted data urls into the filesystem
     * 
     * @param array $matches array of image data-url matches 
     *                       found through the preg_replace_callback
     * 
     * @return string replace string
     */
    protected function moveDataUrlsToFileSystem($matches)
    {
        //If the variable $argsForDataUriExtraction is empty 
        //then return empty string
        if (empty($this->argsForDataUriExtraction)) {
            return '';
        }

        //Convert the image data-url as image file and 
        //store it into the location given in $filePath
        $imgTag       = '';
        $filePath     = $this->argsForDataUriExtraction['filePath'];
        $filePrefix   = $this->argsForDataUriExtraction['filePrefix'];
        $relativePath = $this->argsForDataUriExtraction['relativePath'];
        $fileName     = $this->checkFileAvailability(
                            $filePath, 
                            $filePrefix . '.' . $matches[3]);
        try {
            $file = new \Cx\Lib\FileSystem\File($filePath . '/' . $fileName);
            $file->touch();
            $file->write(base64_decode($matches[5]));
            $this->extractedImgFiles[] = $filePath . '/' . $fileName;
            $imgTag = '<img src="'. $relativePath . '/' . $fileName 
                    . '" title="' . $fileName 
                    . '" alt="' . $fileName . '" />';
        } catch (\Exception $e) {
            \DBG::log($e->getMessage());
            return '';
        }

        //Check the memory overflow and timeout limit
        $this->checkMemoryLimit(self::MiB2);
        $this->checkTimeoutLimit($this->argsForDataUriExtraction['processTime']);

        return $imgTag;
    }

    /**
     * To check the filename is available, if not rename the filename
     * 
     * @param string $filePath file absolute path
     * @param string $fileName name of the file
     * 
     * @return string $filePath return the available filename
     */
    public function checkFileAvailability($filePath, $fileName) 
    {
        if (empty($filePath) || empty($fileName)) {
            return '';
        }

        //check the file availability
        $i = 1;
        $fileInfo = pathinfo($fileName);
        while (\Cx\Lib\FileSystem\FileSystem::exists($filePath . '/' . $fileName)) {
            $fileName = $fileInfo['filename'] . '_' . $i++ . '.' . $fileInfo['extension'];
        }

        return $fileName;
    }

    /**
     * Checking memory limit
     * 
     * @param integer $requiredMemoryLimit required memory limit
     * 
     * @return boolean
     * @throws WysiwygException
     */
    function checkMemoryLimit($requiredMemoryLimit)
    {
        if (empty($this->memoryLimit)) {
            $memoryLimit = \FWSystem::getBytesOfLiteralSizeFormat(@ini_get('memory_limit'));
            //if memory limit is empty then set default php memory limit of 8MiBytes
            $this->memoryLimit = !empty($memoryLimit) ? $memoryLimit : self::MiB2 * 4;
        }

        $potentialRequiredMemory = memory_get_usage() + $requiredMemoryLimit;
        if ($potentialRequiredMemory > $this->memoryLimit) {
            // try to set a higher memory_limit
            if (!@ini_set('memory_limit', $potentialRequiredMemory)) {
                throw new WysiwygException('Memory limit could not allocated to required memory.');
            }
        }

        return true;
    }

    /**
     * Checking the timeout limit
     * 
     * @param integer $processStartTime process started time
     * 
     * @return boolean
     * @throws WysiwygException
     */
    function checkTimeoutLimit($processStartTime)
    {
        if (empty($this->timeLimit)) {
            $this->timeLimit = ini_get('max_execution_time');
        }

        $timeoutTime = $processStartTime + $this->timeLimit;

        if ($timeoutTime > time()) {
            return true;
        }

        throw new WysiwygException('Timeout limit exceeded.');
    }
}