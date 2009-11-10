<?php
/**
* Class Upload
*
* Class to upload an image and create a thumbnail
*
* @copyright   CONTREXX CMS - COMVATION AG
* @author      Christian Wehrli <schristian.wehrli@astalavista.ch>
* @package     contrexx
* @subpackage  module_shop
* @version       $Id: index.inc.php,v 1.00 $
*/

//Security-Check
if (eregi("shop_image.class.php",$_SERVER['PHP_SELF']))
{
    Header("Location: index.php");
    die();
}

/**
* Class Upload
*
* Class to upload an image and create a thumbnail
*
* @copyright   CONTREXX CMS - COMVATION AG
* @author      Christian Wehrli <schristian.wehrli@astalavista.ch>
* @package     contrexx
* @subpackage  module_shop
* @version       $Id: index.inc.php,v 1.00 $
*/
class upload
{
    // class variable
    var $file_permitted;    // array MIME type of the permited file
    var $archive_dir;        // upload directory
    var $max_filesize;        // max size of file upload
    var $imageQuality;
    var $imageSize;
    var $imageWidth;
    var $imageHeight;
    var $thumbnailSize;
    var $thumbnailWidth;
    var $thumbnailHeight;
    var $fileName;
    var $thumbnailName;
    var $randomNumber;


    /**
     * Constructor
     * @version 1.0    initial version
     * @param  string  $max_file_size
     * @param  array   $file_perm
     * @param  string  $arc_dir
     * @param  int  $quality
     * @access public
     */
    function __construct($file_perm, $max_file_size=300000, $arc_dir='..', $quality)
    {
        if (empty ($file_perm)) {
            $file_perm = array ("image/pjpeg","image/x-png","image/jpeg","image/png","image/gif");
        }
        $this->file_permitted = $file_perm; // set mime/type of files to upload
        $this->imageQuality =$quality;
        $this->max_filesize = $max_file_size; // set max size of file upload
        $this->archive_dir = $arc_dir;  // set destination dir of the upload file
    }


    /**
     * putFile
     *
     * @version 1.0    initial version
     * @param  string  $file
     * @param  integer $pictureWidth
     * @param  integer $overwriteMode
     * @access public
     */
    function putFile($file,$pictureWidth,$overwriteMode)
    {
        $userfile_type = strtok($_FILES[$file]['type'], ";"); // clear file type:  MIME TYPE
        $userfile_name = $_FILES[$file]['name']; // set the original file name
        $userfile_size = $_FILES[$file]['size']; // set the file uploaded dimension
        $userfile = $_FILES[$file]['tmp_name'];  // file uploaded in the temp dir
        $this->imageSize = $userfile_size;
        $userfile_name = $this->removeBadChars($userfile_name);
        $error = "This file type is not permitted: $userfile_type<br />"; // set the error message
        // if the file is in the list of MIME TYPE permitted
        // set the error to empty string
        foreach ($this->file_permitted as $permitted) {
            if ($userfile_type == $permitted) $error = "";
        }
        // if filesize is <= 0 or  > $max_filesize
        // set the error message
        if (($userfile_size <= 0) || ($userfile_size > $this->max_filesize)) {
            $error = "File size error: $userfile_size Kb.<br />";
        }
        // if no error occured, start coping file
        if ($error == "") {
            $filename = basename($userfile_name); // extract file name
            if (!empty ($this->archive_dir)) {
                $destination = $this->archive_dir."/".$this->removeBadChars(basename($filename));
            } else {
                $destination = $this->removeBadChars(basename($filename));
            }
            // if exist, add a random number to the file name
            if (file_exists($destination) AND $overwriteMode==0) {
                srand((double)microtime()*1000000); // random number initialization
                $randNumber = rand(0,20000);
                $this->randomNumber = $randNumber;
                $destination = $this->archive_dir.$this->randomNumber.$filename; // add number to file name
                $this->fileName = $this->randomNumber.$filename;
            } else {
                 $this->fileName = $filename;
            }
            // Do some additional checks
            if (!is_uploaded_file($userfile)) {
                die ("File $userfile_name is not uploaded correctly.");
            }
            // copy file to temp dir to destination fir and removee the temp file
            if (!@move_uploaded_file($userfile,$destination)) {
                die ("Impossible to copy $userfile_name from $userfile to destination directory.");
            }
            // resize the image bevor copying in the image directory
            if (!empty($pictureWidth)) {
                $this->resize_jpeg(
                    $destination, $destination, $pictureWidth, 1600, '');

            }
            return $destination; // return the full path of the file on file system of the server
        } else {
            echo $error;
            return false;
        }
    }


    /**
     * chkgd2
     *
     * Checks the version of the GD-Library (if installed) to use the right function to create picture
     *
     * @version 1.0    initial version
     * @access public
     */
    function chkgd2()
    {
        $testGD = get_extension_funcs('gd'); // Grab function list
        if (!$testGD) {
            echo 'ERROR: GD library is not installed.<br />';
            return false;
        } else {
            ob_start(); // Turn on output buffering
            phpinfo(8); // Output in the output buffer the content of phpinfo
            $grab = ob_get_contents(); // Grab the buffer
            ob_end_clean(); // Clean (erase) the output buffer and turn off output buffering
            $version = strpos($grab, '2.0 or higher'); // search for string '2.0 or higher'
            if ($version) return 'gd2'; // if find the string return gd2
            else return 'gd'; // else return 'gd'
        }
    }


    /**
     * Function to resize pictures
     * @param string  $image_file_path
     * @param string  $new_image_file_path
     * @param integer $percent
     * @version 1.0   initial version
     * @access public
     */
    function resize_jpeg($image_file_path, $new_image_file_path, $percent="")
    {
        $return_val = 1;
        // create new image
        if (eregi('\.png$', $image_file_path)) {
            $return_val = (($img = ImageCreateFromPNG ($image_file_path)) && $return_val == 1) ? '1' : '0';
        }

        if (eregi('\.(jpg|jpeg)$', $image_file_path)) {
            $return_val = (($img = ImageCreateFromJPEG ($image_file_path)) && $return_val == 1) ? '1' : '0';
        }

        if (eregi('\.gif$', $image_file_path)) {
            $return_val = (($img = ImageCreateFromGIF ($image_file_path)) && $return_val == 1) ? '1' : '0';
        }

        //get the original image size and store it in an array
        $arrImageInfo = getimagesize($image_file_path);
        $this->imageWidth= $arrImageInfo[0];
        $this->imageHeight= $arrImageInfo[1];
        if ($percent != '') {
            $new_width = round($this->imageWidth * $percent/100,0);
            $new_height = round($this->imageHeight * $percent/100,0);
        }
        $this->thumbnailWidth=$new_width;
        $this->thumbnailHeight=$new_height;
        // check to see if gd2+ libraries are compiled with php
        $gd_version = ($this->chkgd2());
        if ($gd_version == 'gd2') {
            $full_id =  ImageCreateTrueColor($this->thumbnailWidth-1 , $this->thumbnailHeight-1);
            ImageCopyResampled($full_id,$img, 0,0,0,0, $this->thumbnailWidth, $this->thumbnailHeight, $this->imageWidth, $this->imageHeight);
        } elseif ($gd_version == 'gd') {
            $full_id = ImageCreateTrueColor($this->thumbnailWidth-1 , $this->thumbnailHeight-1);
            ImageCopyResized ($full_id, $img, 0,0,0,0, $this->thumbnailWidth, $this->thumbnailHeight, $this->imageWidth, $this->imageHeight);
        } else {
            'GD Image Library is not installed.';
        }

        if (eregi('\.(jpg|jpeg)$',$image_file_path)) {
            $return_val = (ImageJPEG($full_id, $new_image_file_path, $this->imageQuality) && $return_val == 1) ? '1' : '0';
        }
        if (eregi('\.png$',$image_file_path)) {
            $return_val = (ImagePNG($full_id, $new_image_file_path) && $return_val == 1) ? '1' : '0';
        }
        if (eregi('\.gif$',$image_file_path)) {
            $return_val = (ImageGif ($full_id, $new_image_file_path) && $return_val == 1) ? '1' : '0';
        }

        //set thumbnail size
        if (file_exists($new_image_file_path))
             $this->thumbnailSize= filesize($new_image_file_path);
        //set original size
        if (file_exists($new_image_file_path))
           $this->imageSize= filesize($image_file_path);
        ImageDestroy($full_id);
        // --End Creation, Copying--
        return ($return_val ? true : false);
    }


    /**
     * Create Thumbnail
     *
     * Function to create a thumbnail
     *
     * @param string  $image_path
     * @param string  $path
     * @param string $pre_name
     * @param integer $percent
     * @version 1.0   initial version
     * @access public
     */
    function createThumbnail($image_path, $path, $pre_name='thumb_' ,$percent='')
    {
        if ($image_path != '') {
            $arrMatch = array();
            if (!preg_match('/\.(?:png|jpg|jpeg|gif)$/', $image_path, $arrMatch)) {
                return "Unknown file extension: $image_path.  Cannot create the thumbnail.<br />";
            }
        }
        $fileExtension = $arrMatch[1];
        $image_name =
            basename($image_path, $fileExtension).$pre_name.$fileExtension;
        $this->thumbnailName = $image_name;

        if (!empty($path)) {
            $thumb_path = $path.'/'.$image_name; // complete path to thumbnail dir
        } else {
            $thumb_path = $pre_name.$image_name; // complete path to thumbnail dir
        }

        if ($this->resize_jpeg($image_path, $thumb_path, $percent)) {
            return $image_name;
        }
        return 'Error while creating thumbnail<br />';
    }


    /**
     * removeBadChars(
     *
     * @version 1.0    initial version
     * @param  string  $filename
     * @access public
     * @todo    The process of removing and replacing "bad" characters from the
     *          filename is potentially dangerous, as it may lead to
     *          unwanted/unexpected behavior due to duplicate filenames!
     */
    function removeBadChars($filename)
    {
        if (eregi('\.png$', $filename))
            $fileExtension = '.png';
        if (eregi('\.jpg$', $filename))
            $fileExtension = '.jpg';
        if (eregi('\.jpeg$', $filename))
             $fileExtension = '.jpeg$';
        if (eregi('\.gif$', $filename))
             $fileExtension = '.gif';

        $image_name = basename($filename,$fileExtension);
        $image_name = strtolower($image_name);
        $image_name = str_replace(' ','',$image_name);
        $image_name = str_replace('\\','',$image_name);
        $image_name = str_replace('/','',$image_name);
        $image_name = str_replace(':','',$image_name);
        $image_name = str_replace('.','_',$image_name);
        $image_name = str_replace(',','_',$image_name);
        $image_name = str_replace('(','_',$image_name);
        $image_name = str_replace(')','_',$image_name);
        $image_name = str_replace('*','',$image_name);
        $image_name = str_replace('?','',$image_name);
        $image_name = str_replace('!','',$image_name);
        $image_name = str_replace('\'','',$image_name);
        $image_name = str_replace('\'','',$image_name);
        $image_name = str_replace('<','',$image_name);
        $image_name = str_replace('>','',$image_name);
        $image_name = str_replace('|','',$image_name);
        $image_name = str_replace('ä','ae',$image_name);
        $image_name = str_replace('ö','oe',$image_name);
        $image_name = str_replace('ü','ue',$image_name);
        $image_name = str_replace('é','e',$image_name);
        $image_name = str_replace('è','e',$image_name);
        $image_name = str_replace('à','a',$image_name);
        $filename = $image_name.$fileExtension;
        return $filename;
    }
}

?>
