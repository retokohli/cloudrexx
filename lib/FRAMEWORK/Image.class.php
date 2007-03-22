<?php
/**
 * Image manager
 * @copyright   CONTREXX CMS - ASTALAVISTA IT AG
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
 * @copyright   CONTREXX CMS - ASTALAVISTA IT AG
 * @author        Paulo M. Santos <pmsantos@astalavista.net>
 * @version       1.0
 * @access        public
 * @package     contrexx
 * @subpackage  lib_framework
 */
class ImageManager
{
    var $orgImage;
    var $orgImageWidth;
    var $orgImageHeight;
    var $orgImageType;
    var $orgImageFile;

    var $newImage;
    var $newImageWidth;
    var $newImageHeight;
    var $newImageQuality;
    var $newImageType;
    var $newImageFile;

    var $imageCheck       = 1;



    /**
     * Constructor
     *
     * @access   public
     * @return   void
     */
    function ImageManager()
    {
        $this->__construct();
    }



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

        if($this->orgImageType = $this->_isImage($this->orgImageFile))
        {
            $getImage             = $this->_getImageSize($this->orgImageFile);
            $this->orgImageWidth  = $getImage[0];
            $this->orgImageHeight = $getImage[1];

            if($this->orgImage = $this->_imageCreateFromFile($this->orgImageFile))
            {
                return true;
            }
            else
            {
                $this->imageCheck = 0;
                $this->_resetVariables();
                return false;
            }

            return true;
        }
        else
        {
            $this->imageCheck = 0;
            $this->_resetVariables();
            return false;
        }
    }


    /**
     * creates a thumbnail of a picture
     *
     * @param string $strPath
     * @param string $strWebPath
     * @param string $file
     * @param int $maxSize the maximum width or height of the image
     * @param int $quality
     * @return bool
     */
    function _createThumb($strPath, $strWebPath, $file, $maxSize = 80, $quality = 90){
        $objFile = &new File();
	    $_objImage = &new ImageManager();
	    $file = basename($file);
        $tmpSize    = getimagesize($strPath.$file);

        if($tmpSize[0] > $tmpSize[1]){
           $factor = $maxSize / $tmpSize[0];
        }else{
           $factor = $maxSize / $tmpSize[1] ;
        }
        $thumbWidth  = $tmpSize[0] * $factor;
        $thumbHeight = $tmpSize[1] * $factor;

        $_objImage->loadImage($strPath.$file);
        $_objImage->resizeImage($thumbWidth, $thumbHeight, $quality);
        $_objImage->saveNewImage($strPath.$file . '.thumb');
	    if($objFile->setChmod($strPath, $strWebPath, $file . '.thumb')){
	       return true;
	    }
	    return false;
	}


    /**
     * Resize Image
     *
     * Resizes the loaded Image as you wish and creates a variable with
     * the new image
     *
     * @access   public
     * @param    string [$width]   width of the new image
     * @param    string [$height]  height of the new image
     * @param    string [$quality] quality for the new image
     * @return   bool
     */
    function resizeImage($width, $height, $quality)
    {
        if($this->imageCheck == 1)
        {
            $this->newImageWidth   = $width;
            $this->newImageHeight  = $height;
            $this->newImageQuality = $quality;
            $this->newImageType    = $this->orgImageType;

            if(function_exists ("imagecreatetruecolor")) {
            	$this->newImage = @imagecreatetruecolor($this->newImageWidth, $this->newImageHeight);
            	// GD > 2 check
            	if (!$this->newImage) { $this->newImage = ImageCreate($this->newImageWidth, $this->newImageHeight); }
            } else {
            	$this->newImage = ImageCreate($this->newImageWidth, $this->newImageHeight);
            }
            imagecopyresized($this->newImage, $this->orgImage, 0, 0, 0, 0, $this->newImageWidth + 1, $this->newImageHeight + 1, $this->orgImageWidth, $this->orgImageHeight);
            return true;
        }
        else
        {
            return false;
        }
    }


    /**
     * Resize and save image
     *
     * Resizes the loaded Image as you wish and saves the new image
     *
     * @access   public
     * @param    string [$path]         absolute path to the directory where the image is in
     * @param    string [$webPath]      absolute webpath (in the directory root of the webserver)
     * @param    string [$fileName]     fileName of the image that needs to be resized
     * @param    string [$width]        width of the new image
     * @param    string [$height]       height of the new image
     * @param    string [$quality]      quality for the new image
     * @param    string [$newPath]      absolute path to the directory where the image is in (leave empty if same as original)
     * @param    string [$newWebPath]   absolute webpath (in the directory root of the webserver, leave empty if same as original)
     * @param    string [$newFileName]  fileName of the image that needs to be resized (leave empty to create default $file.'.thumb')
     * @return   bool
     */
    function resizeImageSave($path, $webPath, $fileName, $width, $height, $quality = 70, $newPath = '', $newWebPath = '', $newFileName = '', $thumbNailSuffix = '.thumb')
    {
        if($newPath = ''){ // use same path as the original image for new image, if not set
            $newPath = $path;
        }

        if($newWebPath = ''){
            $newWebPath = $webPath;
        }

        if($newFileName = ''){
            $newFileName = $fileName.$thumbNailSuffix;
        }

        $this->_checkTrailingSlash($path);
        $this->_checkTrailingSlash($webPath);
        $this->_checkTrailingSlash($newPath);
        $this->_checkTrailingSlash($newWebPath);

        $this->loadImage($path.$fileName);
        if($this->imageCheck == 1){ // if file is a valid image
            $this->newImageWidth   = $width;
            $this->newImageHeight  = $height;
            $this->newImageQuality = $quality;
            $this->newImageType    = $this->orgImageType;

            if(function_exists ("imagecreatetruecolor")) {
            	$this->newImage = @imagecreatetruecolor($this->newImageWidth, $this->newImageHeight);
            	// GD > 2 check
            	if (!$this->newImage) {
            	    $this->newImage = ImageCreate($this->newImageWidth, $this->newImageHeight);
            	}
            } else {
            	$this->newImage = ImageCreate($this->newImageWidth, $this->newImageHeight);
            }
            imagecopyresized($this->newImage, $this->orgImage, 0, 0, 0, 0, $this->newImageWidth + 1, $this->newImageHeight + 1, $this->orgImageWidth, $this->orgImageHeight);

            if($this->saveNewImage($newPath.$newFileName)){
                return true;
            }
        } else {
            return false;
        }
        return false;
    }

    /**
     * check whether a path has a trailing slash or not and adjust if necessary
     *
     * @param string $path
     */
    function _checkTrailingSlash(&$path){
        if(substr($path, -1) != DIRECTORY_SEPARATOR){ // add directory separator if not already provided
            $path .= DIRECTORY_SEPARATOR;
        }
    }


    /**
     * Saves new image
     *
     * Saves the new image wherever you want
     *
     * @access   public
     * @param    string [$file] path and filename where to save the new image
     * @return   bool
     */
    function saveNewImage($file)
    {
        if($this->imageCheck == 1 && !empty($this->newImage) && !file_exists($file))
        {
            $this->newImageFile = $file;

            switch($this->newImageType)
            {
                case 1:  // gif
                    $function = 'imagegif';
                    if(!function_exists($function)){
                        $function = 'imagejpeg';
                    }
                    break;
                case 2:  // jpg
                    $function = 'imagejpeg';
                    break;
                case 3:  // png
                    $function = 'imagepng';
                    break;
                default:
                    return false;
            }

            $function($this->newImage, $this->newImageFile, $this->newImageQuality);
            return true;
        }
        else
        {
            return false;
        }
    }





    /**
     * Show new image
     *
     * Outputs the new image into the browser
     * or inserts it in a <img .. > tag
     *
     * @access   public
     * @return   bool
     */
    function showNewImage()
    {
        if($this->imageCheck == 1 && !empty($this->newImage))
        {
            switch($this->newImageType)
            {
                case 1:  // gif
                    header("Content-type: image/gif");
                    $function = 'imagegif';
                    if(!function_exists($function)){
                        $function = 'imagejpeg';
                    }
                    break;
                case 2:  // jpg
                    header("Content-type: image/jpeg");
                    $function = 'imagejpeg';
                    break;
                case 3:  // png
                    header("Content-type: image/png");
                    $function = 'imagepng';
                    break;
                default:
                    return false;
            }
            $function($this->newImage, '', $this->newImageQuality);
            return true;
        }
        else
        {
            return false;
        }
    }





    /**
     * Reset variables
     *
     * Resets all variales
     *
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
     * get percentual values of an image dimension
     *
     * @param string $img
     * @param integer $max  maximum size (either width or height, depending on the larger value)
     * @return array || false on failure
     */
    function getImageDim($path, $webPath, $fileName, $max = 60){
        $this->_checkTrailingSlash($path);
		if(is_file($path.$fileName)){
			$size   = getimagesize($path.$fileName);
   			$height = $size[1];
		    $width  = $size[0];
		    if ($height > $max && $height > $width)
		    {
				$height = $max;
		        $percent = ($size[1] / $height);
		        $width = ($size[0] / $percent);
		    }
		    else if ($width > $max)
		    {
		    	$width = $max;
		        $percent = ($size[0] / $width);
		        $height = ($size[1] / $percent);
		   	}
   			if($width > 0 && $height > 0){
				$imgdim['style'] = 'style="height: '.$height.'px; width:'.$width.'px;"';
				$imgdim['width'] = $size[0]+1;
				$imgdim['height'] = $size[1]+1;
   			}
			return $imgdim;
		}
		return false;
	}



    /**
     * Create Image
     *
     * Creates a new image from an old image
     *
     * @access   private
     * @param    string [$file] path and filename of the old image
     * @return   string [$image] image variable
     */
    function _imageCreateFromFile($file)
    {
        if($type = $this->_isImage($file))
        {
            switch($type)
            {
                case 1:  // gif
                    $function = 'imagecreatefromgif';
                    break;
                case 2:  // jpg
                    $function = 'imagecreatefromjpeg';
                    break;
                case 3:  // png
                    $function = 'imagecreatefrompng';
                    break;
                default:
                    return false;
            }

            $image = $function($file);
            return $image;
        }
        else
        {
            return false;
        }
    }





    /**
     * Image Check
     *
     * Checks if the file is an image or not
     *
     * @access   private
     * @param    string [$file] path and filename of the old image
     * @return   string [$imageSize[2]] type of the image
     */
    function _isImage($file)
    {
        if(file_exists($file))
        {
            $imageSize = $this->_getImageSize($file);

            // 1 = GIF,  2 = JPG,  3 = PNG,  4 = SWF,  5 = PSD, 6 = BMP, 7 = TIFF(intel byte order), 8 = TIFF(motorola byte order,
            // 9 = JPC, 10 = JP2, 11 = JPX, 12 = JB2, 13 = SWC

            if($imageSize[2] == 1 || $imageSize[2] == 2 || $imageSize[2] == 3)  // gif, jpg, png
            {
                if($imageSize[2] == 1)
                {
                    if(function_exists('imagecreatefromgif'))  // for test add an !
                    {
                        return $imageSize[2];
                    }
                    else
                    {
                        return false;
                    }
                }
                else
                {
                    return $imageSize[2];
                }
            }
            else
            {
                return false;
            }
        }
        else
        {
            return false;
        }
    }





    /**
     * Image Size
     *
     * Gets the size of the image
     *
     * @access   private
     * @param    string [$file] path and filename of the old image
     * @return   array [$getImageSize] function getimagesize() of the image
     */
    function _getImageSize($file)
    {
        if($getImageSize = @getimagesize($file))
        {
            return $getImageSize;
        }
        else
        {
            return false;
        }
    }
}
?>