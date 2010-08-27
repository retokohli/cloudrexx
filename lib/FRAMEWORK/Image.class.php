<?php

/**
 * Image manager
 * @copyright   CONTREXX CMS - COMVATION AG
 * @author        Paulo M. Santos <pmsantos@astalavista.net>
 * @version       1.0
 * @package     contrexx
 * @subpackage  lib_framework
 * @todo        Edit PHP DocBlocks!
 */

/**
 * Includes
 */
require_once(ASCMS_FRAMEWORK_PATH."/File.class.php");

/**
 * Image manager
 * @copyright   CONTREXX CMS - COMVATION AG
 * @author        Paulo M. Santos <pmsantos@astalavista.net>
 * @version       1.0
 * @access        public
 * @package     contrexx
 * @subpackage  lib_framework
 */
class ImageManager
{
    public $orgImage;
    public $orgImageWidth;
    public $orgImageHeight;
    public $orgImageType;
    public $orgImageFile;

    public $newImage;
    public $newImageWidth;
    public $newImageHeight;
    public $newImageQuality;
    public $newImageType;
    public $newImageFile;

    public $imageCheck = 1;

    const IMG_TYPE_GIF = 1;
    const IMG_TYPE_JPEG = 2;
    const IMG_TYPE_PNG = 3;


    /**
     * Constructor
     *
     * @access   public
     * @return   void
     */
    function __construct()
    {
        $this->_resetVariables();
    }


    /**
     * Load Image
     *
     * Loads an existing image into a variable
     *
     * @access   public
     * @param    string [$file] path and filename of the existing image
     * @return   bool
     */
    function loadImage($file)
    {
        $this->_resetVariables();
        $this->orgImageFile = $file;
        $this->orgImageType = $this->_isImage($this->orgImageFile);
        if ($this->orgImageType) {
            $getImage             = $this->_getImageSize($this->orgImageFile);
            $this->orgImageWidth  = $getImage[0];
            $this->orgImageHeight = $getImage[1];
            $this->orgImage       = $this->_imageCreateFromFile($this->orgImageFile);
            if ($this->orgImage) return true;
            $this->imageCheck = 0;
            $this->_resetVariables();
            return false;
        }
        $this->imageCheck = 0;
        $this->_resetVariables();
        return false;
    }


    /**
     * Add Background Layer
     *
     * This scales the image to a size that it fits into the rectangle defined by width $width and height $height.
     * Spaces at the edges will be padded with the color $bgColor.
     *
     * @param   array   $bgColor is an array containing 3 values, representing the red, green and blue portion (0-255) of the desired color.
     * @param   integer The width of the rectangle
     * @param   integer The height of the rectangle
     */
    function addBackgroundLayer($bgColor, $width =  null, $height = null)
    {
        if (function_exists ("imagecreatetruecolor")) {
            $this->newImage = imagecreatetruecolor($width, $height);
            // GD > 2 check
            if (!$this->newImage) {
                $this->newImage = ImageCreate($this->newImageWidth, $this->newImageHeight);
            }
        } else {
            $this->newImage = ImageCreate($width, $height);
        }

        imagefill($this->newImage, 0, 0, imagecolorallocate($this->newImage, $bgColor[0], $bgColor[1], $bgColor[2]));
        $ratio =  max($this->orgImageWidth / $width, $this->orgImageHeight / $height);
        $scaledWidth = $this->orgImageWidth / $ratio;
        $scaledHeight = $this->orgImageHeight / $ratio;
        $offsetWidth = ($width - $scaledWidth) / 2;
        $offsetHeight = ($height - $scaledHeight) / 2;
        imagecopyresized($this->newImage, $this->orgImage, $offsetWidth, $offsetHeight, 0, 0, $scaledWidth, $scaledHeight, $this->orgImageWidth, $this->orgImageHeight);
        $this->imageCheck = 1;
        $this->newImageQuality=100;
        $this->newImageType    = $this->orgImageType;
    }


    /**
     * Creates a thumbnail of a picture
     * @param   string  $strPath
     * @param   string  $strWebPath
     * @param   string  $file
     * @param   int     $maxSize      The maximum width or height of the image
     * @param   int     $quality
     * @return  boolean
     */
    function _createThumb($strPath, $strWebPath, $file, $maxSize=80, $quality=90)
    {
        $objFile   = new File();
        $_objImage = new ImageManager();
        $file      = basename($file);
        $tmpSize   = getimagesize($strPath.'/'.$file);

        if ($tmpSize[0] > $tmpSize[1]) {
           $factor = $maxSize / $tmpSize[0];
        } else {
           $factor = $maxSize / $tmpSize[1] ;
        }
        $thumbWidth  = $tmpSize[0] * $factor;
        $thumbHeight = $tmpSize[1] * $factor;
        if (!$_objImage->loadImage($strPath.'/'.$file)) return false;
        if (!$_objImage->resizeImage($thumbWidth, $thumbHeight, $quality)) return false;
        $thumb_name = self::getThumbnailFilename($file);
        if (!$_objImage->saveNewImage($strPath .'/'.$thumb_name)) return false;
        if (!$objFile->setChmod($strPath, $strWebPath, $thumb_name)) return false;
        return true;
    }


    /**
     * Create a thumbnail of a picture.
     *
     * This is very much like {@link _createThumb()}, but provides more
     * parameters.  Both the width and height of the thumbnail may be
     * specified; the picture will still be scaled to fit within the given
     * sizes while keeping the original width/height ratio.
     * In addition to that, this method tries to delete an existing
     * thumbnail before attempting to write the new one.
     * @param   string  $strPath        The image file folder
     * @param   string  $strWebPath     The image file web folder
     * @param   string  $file           The image file name
     * @param   integer $maxWidth       The maximum width of the image
     * @param   integer $maxHeight      The maximum height of the image
     * @param   integer $quality        The desired jpeg thumbnail quality
     * @param   string  $thumbNailSuffix  Suffix of the thumbnail. Default is 'thumb'
     * @param   string  $strPathNew     Image file store folder. Default is $strPath
     * @param   string  $strWebPathNew  Image file web store folder. Default is $strWebPath
     * @param   string  $fileNew        Image file store name. Default is $file
     * @return  bool                    True on success, false otherwise.
     */
    function _createThumbWhq(
        $strPath, $strWebPath, $file, $maxWidth=80, $maxHeight=80, $quality=90,
        $thumbNailSuffix='.thumb', $strPathNew=null, $strWebPathNew=null, $fileNew=null
    ) {
        // Do *NOT* strip subfolders from the file name!
        // This would break correct operation in some places (shop)
        // Fix your own code to provide the file name only if you need to.
        //$file      = basename($file);
        if (empty($fileNew))       $fileNew       = $file;
        if (empty($strPathNew))    $strPathNew    = $strPath;
        if (empty($strWebPathNew)) $strWebPathNew = $strWebPath;
        $tmpSize = getimagesize($strPath.'/'.$file);

        // reset the ImageManager
        $this->imageCheck = 1;

        $width       = $tmpSize[0];
        $height      = $tmpSize[1];
        $widthRatio  = $width/$maxWidth;
        $heightRatio = $height/$maxHeight;
        $thumbWidth  = 0;
        $thumbHeight = 0;

        if ($widthRatio < $heightRatio) {
            $thumbHeight = $maxHeight;
            $thumbWidth  = $width*$maxHeight/$height;
        } else {
            $thumbWidth  = $maxWidth;
            $thumbHeight = $height*$maxWidth/$width;
        }

        if (!$this->loadImage($strPath.'/'.$file)) return false;
        if (!$this->resizeImage($thumbWidth, $thumbHeight, $quality)) return false;
        if (is_file($strPathNew.'/'.$fileNew.$thumbNailSuffix)) {
            if (!unlink($strPathNew.'/'.$fileNew.$thumbNailSuffix)) return false;
        }
        if (!$this->saveNewImage($strPathNew.'/'.$fileNew.$thumbNailSuffix)) return false;
        $objFile = new File();
        if (!$objFile->setChmod($strPathNew, $strWebPathNew, $fileNew.$thumbNailSuffix)) return false;
        return true;
    }


    /**
     * Resizes the original Image to the given dimensions and stores it as the new
     * @access    public
     * @param     string    $width    The width of the new image
     * @param     string    $height   The height of the new image
     * @param     string    $quality  The quality for the new image
     * @return    booelan             True on success, false otherwise
     */
    function resizeImage($width, $height, $quality)
    {
        if ($this->imageCheck == 1) {
            $this->newImageWidth   = $width;
            $this->newImageHeight  = $height;
            $this->newImageQuality = $quality;
            $this->newImageType    = $this->orgImageType;

            if (function_exists ("imagecreatetruecolor")) {
                $this->newImage = @imagecreatetruecolor($this->newImageWidth, $this->newImageHeight);
                // GD > 2 check
                if ($this->newImage) {
                    $this->setTransparency();
                } else {
                    $this->newImage = ImageCreate($this->newImageWidth, $this->newImageHeight);
                }
            } else {
                $this->newImage = ImageCreate($this->newImageWidth, $this->newImageHeight);
            }
            imagecopyresized($this->newImage, $this->orgImage, 0, 0, 0, 0, $this->newImageWidth + 1, $this->newImageHeight + 1, $this->orgImageWidth, $this->orgImageHeight);
            return true;
        }
        return false;
    }

    /**
     * Add transparency to new image
     *
     * Define a color as transparent or add alpha channel,
     * depending on the image file type.
     *
     * @access private
     * @return void
     */
    private function setTransparency()
    {
        switch ($this->orgImageType) {
            case self::IMG_TYPE_GIF:
                $transparentColorIdx = imagecolortransparent($this->orgImage);
                if ($transparentColorIdx >= 0) {
                    //its transparent
                    $transparentColor = imagecolorsforindex($this->orgImage, $transparentColorIdx);
                    $transparentColorIdx = imagecolorallocate($this->newImage, $transparentColor['red'], $transparentColor['green'], $transparentColor['blue']);
                    imagefill($this->newImage, 0, 0, $transparentColorIdx);
                    imagecolortransparent($this->newImage, $transparentColorIdx);
                }
                break;
            case self::IMG_TYPE_PNG:
                imagealphablending($this->newImage, false);
                $colorTransparent = imagecolorallocatealpha($this->newImage, 0, 0, 0, 127);
                imagefill($this->newImage, 0, 0, $colorTransparent);
                imagesavealpha($this->newImage, true);
                break;
            default:
                break;
        }
    }


    /**
     * Resize and save image
     *
     * Resizes the loaded Image as you wish and saves the new image
     *
     * @access  public
     * @param   string  $path         Absolute path to the directory where the image is in
     * @param   string  $webPath      Absolute webpath (in the directory root of the webserver)
     * @param   string  $fileName     FileName of the image that needs to be resized
     * @param   string  $width        Width of the new image
     * @param   string  $height       Height of the new image
     * @param   string  $quality      Quality for the new image
     * @param   string  $newPath      Absolute path to the directory where the image is in (leave empty if same as original)
     * @param   string  $newWebPath   Absolute webpath (in the directory root of the webserver, leave empty if same as original)
     * @param   string  $newFileName  FileName of the image that needs to be resized (leave empty to create default $file.'.thumb')
     * @return  bool
     */
    function resizeImageSave(
        $path, $webPath, $fileName, $width, $height, $quality=70,
        $newPath='', $newWebPath='', $newFileName='', $thumbNailSuffix='.thumb'
    ) {
        // If empty, use the original image path for the new image
        if ($newPath == '') $newPath = $path;
        if ($newWebPath == '') $newWebPath = $webPath;
        if ($newFileName == '') $newFileName = $fileName.$thumbNailSuffix;

        $this->_checkTrailingSlash($path);
        $this->_checkTrailingSlash($webPath);
        $this->_checkTrailingSlash($newPath);
        $this->_checkTrailingSlash($newWebPath);

        $this->loadImage($path.$fileName);
        if ($this->imageCheck == 1) { // if file is a valid image
            $this->newImageWidth   = $width;
            $this->newImageHeight  = $height;
            $this->newImageQuality = $quality;
            $this->newImageType    = $this->orgImageType;

            if (function_exists ('imagecreatetruecolor')) {
                $this->newImage = @imagecreatetruecolor($this->newImageWidth, $this->newImageHeight);
                // GD > 2 check
                if ($this->newImage) {
                    $this->setTransparency();
                } else {
                    $this->newImage = ImageCreate($this->newImageWidth, $this->newImageHeight);
                }
            } else {
                $this->newImage = ImageCreate($this->newImageWidth, $this->newImageHeight);
            }
            imagecopyresized(
                $this->newImage, $this->orgImage,
                0, 0, 0, 0,
                $this->newImageWidth+1, $this->newImageHeight+1,
                $this->orgImageWidth, $this->orgImageHeight
            );
            if ($this->saveNewImage($newPath.$newFileName)) return true;
        }
        return false;
    }


    /**
     * Add a trailing slash to the given path if there's none already
     *
     * The parameter is given by reference.
     * @param   string  $path   The directory path
     * @return  void
     */
    function _checkTrailingSlash(&$path) {
        // add directory separator if not already provided
        if (substr($path, -1) != DIRECTORY_SEPARATOR)  $path .= DIRECTORY_SEPARATOR;
    }


    /**
     * Saves the new image wherever you want
     * @access  public
     * @param   string    $file             The path for the image file to be written
     * @param   booelan   $forceOverwrite   Force overwriting existing files if true
     * @return  boolean                     True on success, false otherwise
     */
    function saveNewImage($file, $forceOverwrite=false)
    {
        if (   $this->imageCheck == 1
            && !empty($this->newImage)
            && (!file_exists($file) || $forceOverwrite)) {
            $this->newImageFile = $file;
            switch($this->newImageType) {
                case self::IMG_TYPE_GIF:
                    $function = 'imagegif';
                    if (!function_exists($function)) {
                        $function = 'imagejpeg';
                    }
                    break;
                case self::IMG_TYPE_JPEG:
                    $function = 'imagejpeg';
                    break;
                case self::IMG_TYPE_PNG:  // make a jpeg thumbnail, too
//                    $function = 'imagepng';
                    $function = 'imagejpeg';
                    break;
                default:
                    return false;
            }

            $function($this->newImage, $this->newImageFile, $this->newImageQuality);
            return true;
        }
        return false;
    }


    /**
     * Outputs the new image in the browser
     * @access   public
     * @return   boolean      True on success, false otherwise
     */
    function showNewImage()
    {
        if ($this->imageCheck == 1 && !empty($this->newImage)) {
            switch($this->newImageType) {
                case self::IMG_TYPE_GIF:
                    header("Content-type: image/gif");
                    $function = 'imagegif';
                    if (!function_exists($function)) {
                        $function = 'imagejpeg';
                    }
                    break;
                case self::IMG_TYPE_JPEG:
                    header("Content-type: image/jpeg");
                    $function = 'imagejpeg';
                    break;
                case self::IMG_TYPE_PNG:
                    header("Content-type: image/png");
                    $function = 'imagepng';
                    break;
                default:
                    return false;
            }
            $function($this->newImage, '', $this->newImageQuality);
            return true;
        }
        return false;
    }


    /**
     * Resets all object variables
     * @access   private
     * @return   void
     */
    function _resetVariables()
    {
        $this->orgImage        = '';
        $this->orgImageWidth   = '';
        $this->orgImageHeight  = '';
        $this->orgImageType    = '';
        $this->orgImageFile    = '';

        $this->newImage        = '';
        $this->newImageWidth   = '';
        $this->newImageHeight  = '';
        $this->newImageQuality = '';
        $this->newImageType    = '';
        $this->newImageFile    = '';
    }


    /**
     * Scale down the original image dimensions to a given maximum pixel size
     *
     * Proportionally scales the width and height, so that both fit within
     * the maximum size.
     * The array returned looks like
     *  array(
     *    'style'   => 'style="height: <scaled_height>px; width: <scaled_width>px;"',
     *    'width'   => original width plus one in pixels,
     *    'height'  => original height pluis one in pixels,
     *  )
     * @todo    Reto asks:  Who wrote this?
     * @todo    Reto asks:  Why is 1 added to the original size?
     * @todo    The $webPath argument is not used.  Remove.
     * @todo    It looks like this method isn't used at all.  Remove.
     * @param   string  $path       The absolute file path
     * @param   string  $webPath    The file path relative to the document root
     * @param   string  $fileName   The file name
     * @param   integer $max        The maximum size for both width and height
     * @return  mixed               An array on success, false otherwise
     * @author  reto.kohli@comvation.com (Added proper documentation, fixed)
     */
    function getImageDim($path, $webPath, $fileName, $max=60)
    {
        $this->_checkTrailingSlash($path);
        if (is_file($path.$fileName)) {
            $size   = getimagesize($path.$fileName);
            $height = $size[1];
            $width  = $size[0];
            if ($height > $max && $height > $width) {
                $height = $max;
                $ratio  = ($size[1] / $height);
                $width  = ($size[0] / $ratio);
            } else if ($width > $max) {
                $width  = $max;
                $ratio  = ($size[0] / $width);
                $height = ($size[1] / $ratio);
            }
            if ($width > 0 && $height > 0) {
                $imgdim['style']  = 'style="height: '.$height.'px; width:'.$width.'px;"';
                $imgdim['width']  = $size[0]+1;
                $imgdim['height'] = $size[1]+1;
            }
            return $imgdim;
        }
        return false;
    }


    /**
     * Creates an image from an image file
     * @access   private
     * @param    string   $file   The path of the image
     * @return   resource         The image on success, the empty string otherwise
     */
    function _imageCreateFromFile($file)
    {
        $arrSizeInfo = getimagesize($file);
        if (!is_array($arrSizeInfo)) return false;
        $type = $this->_isImage($file);
        $potentialRequiredMemory = $arrSizeInfo[0] * $arrSizeInfo[1] * 1.8;
        switch($type) {
            case self::IMG_TYPE_GIF:
                $function = 'imagecreatefromgif';
                break;
            case self::IMG_TYPE_JPEG:
                $function = 'imagecreatefromjpeg';
                $potentialRequiredMemory *=
                    ($arrSizeInfo['bits']/8) * ($arrSizeInfo['channels'] < 3 ? 3 : $arrSizeInfo['channels']);
                break;
            case self::IMG_TYPE_PNG:
                $function = 'imagecreatefrompng';
                $potentialRequiredMemory *= 4;
                break;
            default:
                return '';
        }

        @include_once(ASCMS_FRAMEWORK_PATH.'/System.class.php');

        $memoryLimit = FWSystem::getBytesOfLiteralSizeFormat(@ini_get('memory_limit'));
        if (empty($memoryLimit)) {
            // set default php memory limit of 8MBytes
            $memoryLimit = 8*pow(1024, 2);
        }

        if (function_exists('memory_get_usage')) {
            $potentialRequiredMemory += memory_get_usage();
        } else {
            // add a default of 3MBytes
            $potentialRequiredMemory += 3*pow(1024, 2);
        }

        if ($potentialRequiredMemory > $memoryLimit) {
            // try to set a higher memory_limit
            if (!ini_set('memory_limit', $potentialRequiredMemory) || $memoryLimit == FWSystem::getBytesOfLiteralSizeFormat(ini_get('memory_limit'))) return '';
        }
        return $function($file);
    }


    /**
     * Returns the image type as determined by getimagesize() for the given file.
     *
     * Only accepts web image types (GIF, JPG, or PNG).
     * If the function imagecreatefromgif() is not available, GIF images aren't
     * accepted.
     * False is returned for images/files that are not supported.
     * @access    private
     * @param     string    $file   The file path of the image
     * @return    integer           The file type on success, false otherwise
     */
    function _isImage($file)
    {
        if (file_exists($file)) {
            $imageSize = @getimagesize($file);
            // 1 = GIF,  2 = JPG,  3 = PNG,  4 = SWF,  5 = PSD, 6 = BMP,
            // 7 = TIFF(intel byte order), 8 = TIFF(motorola byte order,
            // 9 = JPC, 10 = JP2, 11 = JPX, 12 = JB2, 13 = SWC$
            // Only accept GIF, JPG, or PNG
            if ($imageSize[2] == 1 && function_exists('imagecreatefromgif'))
                return $imageSize[2];
            if ($imageSize[2] == 2 || $imageSize[2] == 3)
                return $imageSize[2];
        }
        return false;
    }


    /**
     * Gets the size of the image
     * @access    private
     * @param     string    $file   The path of the image
     * @return    array             The array as returned by @getimagesize($file)
     *                              on success, false otherwise
     * @todo      This method is completely redundant.  It does exactly the same
     *            as calling @getimagesize($file) directly! - Remove.
     */
    function _getImageSize($file)
    {
        $getImageSize = @getimagesize($file);
        if ($getImageSize) return $getImageSize;
        return false;
    }


    /**
     * Returns the file name for the thumbnail image
     *
     * Replaces the .png extension with .jpg, if found.
     * This works around the PNG bug in PHP.
     * Does append the .thumb extension if not already present.
     * @param   string    $file_name        The image file name
     * @return  string                      The thumbnail file name
     */
    static function getThumbnailFilename($file_name)
    {
        if (preg_match('/\.thumb$/', $file_name)) return $file_name;
        $thumb_name = preg_replace('/\.png$/', '.jpg', $file_name);
        return $thumb_name.'.thumb';
    }

}

?>
