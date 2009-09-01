<?php

/**
 * Image handling
 * @version     2.2.0
 * @package     contrexx
 * @subpackage  core
 * @copyright   CONTREXX CMS - COMVATION AG
 * @author      Reto Kohli <reto.kohli@comvation.com>
 */

require_once ASCMS_CORE_PATH.'/Text.class.php';

/**
 * Image
 *
 * Includes access methods and data layer.
 * Do not, I repeat, do not access private fields, or even try
 * to access the database directly!
 * @version     2.2.0
 * @package     contrexx
 * @subpackage  core
 * @copyright   CONTREXX CMS - COMVATION AG
 * @author      Reto Kohli <reto.kohli@comvation.com>
 */
class Image
{
    /**
     * The icon URI for the "remove the current image" link
     */
    const ICON_CLEAR_IMAGE_SRC = 'images/content/clear_image.gif';

    /**
     * The default "no image" URI
     */
    const NO_IMAGE_SRC = 'images/content/no_image.gif';

    /**
     * Size limit in bytes for images being uploaded or stored
     */
    const MAXIMUM_UPLOAD_FILE_SIZE = 300000;

    /**
     * Array of all file extensions accepted as image files
     * @var   array
     */
    private static $arrAcceptedFiletype = array(
        'gif', 'jpg', 'png',
    );

    /**
     * @var     integer         $id              The object ID, PRIMARY
     * @access  private
     */
    private $id = false;

    /**
     * The ordinal number
     * @var     integer         $ord             The ordinal number, PRIMARY
     * @access  private
     */
    private $ord = 0;

    /**
     * @var     integer         $image_type_id    The image type ID
     * @access  private
     */
    private $image_type_id = false;

    /**
     * @var     integer         $file_type_id    The file type ID
     * @access  private
     */
    private $file_type_id = false;

    /**
     * @var     string          $path            The image file path
     * @access  private
     */
    private $path = false;

    /**
     * The image width
     * @var     integer         $width            The image width
     * @access  private
     */
    private $width = false;

    /**
     * The image height
     * @var     integer         $height          The image height
     * @access  private
     */
    private $height = false;


    /**
     * Create an Image
     *
     * Note that the optional $image_id argument *SHOULD NOT* be used when
     * adding the first Image to another object, but only to ensure that
     * additional Images with different ordinals are added to the same ID.
     * @access  public
     * @param   integer       $ord              The ordinal number
     * @param   integer       $image_id          The optional Image ID
     * @author  Reto Kohli <reto.kohli@comvation.com>
     */
    function Image($ord, $image_id=0)
    {
        $this->ord = $ord;
        $this->id = $image_id;
    }


    /**
     * Get the ID
     * @return  integer                             The object ID
     * @author      Reto Kohli <reto.kohli@comvation.com>
     */
    function getId()
    {
        return $this->id;
    }
    /**
     * Set the ID -- NOT ALLOWED
     * See {@link Image::makeClone()}
     */

    /**
     * Get the ordinal number
     * @return  integer                              The ordinal number
     * @author      Reto Kohli <reto.kohli@comvation.com>
     */
    function getOrd()
    {
        return $this->ord;
    }
    /**
     * Set the ordinal number
     *
     * Note that this value is non-negative,
     * negative numbers have their sign stripped here.
     * @param   integer          $ord               The ordinal number
     * @author      Reto Kohli <reto.kohli@comvation.com>
     */
    function setOrd($ord)
    {
        $this->ord = abs($ord);
    }

    /**
     * Get the type ID
     * @return  integer                           The type ID
     * @author      Reto Kohli <reto.kohli@comvation.com>
     */
    function getImageTypeId()
    {
        return $this->image_type_id;
    }
    /**
     * Set the type ID
     *
     * Any non-positive value or string will be interpreted as NULL
     * @param   integer          $image_type_id  The type ID
     * @author      Reto Kohli <reto.kohli@comvation.com>
     */
    function setImageTypeId($image_type_id)
    {
        if (intval($image_type_id) <= 0) $image_type_id = 'NULL';
        $this->image_type_id = $image_type_id;
    }

    /**
     * Get the file type ID
     * @return  integer                              The file type ID
     * @author      Reto Kohli <reto.kohli@comvation.com>
     */
    function getFileTypeId()
    {
        return $this->text_id;
    }
    /**
     * Set the file type ID
     *
     * Any non-positive value or string will be interpreted as NULL
     * @param   integer          $file_type_id       The file type ID
     * @author      Reto Kohli <reto.kohli@comvation.com>
     */
    function setFileTypeId($file_type_id)
    {
        if (intval($file_type_id) <= 0) $file_type_id = 'NULL';
        $this->file_type_id = $file_type_id;
    }

    /**
     * Get the path
     *
     * Note that the path is stored relative to ASCMS_DOCUMENT_ROOT,
     * with ASCMS_PATH_OFFSET, any path separator following it,
     * and everything before that cut off!
     * @return  string                           The path
     * @author      Reto Kohli <reto.kohli@comvation.com>
     */
    function getPath()
    {
        return $this->path;
    }
    /**
     * Set the path
     *
     * Note that the path is stored relative to ASCMS_DOCUMENT_ROOT,
     * with ASCMS_PATH_OFFSET, any path separator following it,
     * and everything before that cut off!
     * @param   string          $path         The path
     * @author      Reto Kohli <reto.kohli@comvation.com>
     */
    function setPath($path)
    {
        $reRoot = '/.*?'.preg_quote(ASCMS_PATH_OFFSET, '/').'\/?/';
        $path = preg_replace($reRoot, '', $path);
        $reThumb = '/\.thumb$/';
        $path = preg_replace($reThumb, '', $path);
        if ($path == self::NO_IMAGE_SRC) {
            $this->path = '';
        } else {
            $this->path = strip_tags($path);
        }
    }

    /**
     * Get the width
     * @return  integer                              The width
     * @author      Reto Kohli <reto.kohli@comvation.com>
     */
    function getWidth()
    {
        return $this->width;
    }
    /**
     * Set the width
     * @param   integer          $width               The width
     * @author      Reto Kohli <reto.kohli@comvation.com>
     */
    function setWidth($width)
    {
        $this->width = $width;
    }

    /**
     * Get the height
     * @return  integer                              The height
     * @author      Reto Kohli <reto.kohli@comvation.com>
     */
    function getHeight()
    {
        return $this->height;
    }
    /**
     * Set the height
     * @param   integer          $height               The height
     * @author      Reto Kohli <reto.kohli@comvation.com>
     */
    function setHeight($height)
    {
        $this->height = $height;
    }


    /**
     * Clone the object
     *
     * Note that this does NOT create a copy in any way, but simply clears
     * the object ID.  Upon storing this object, a new ID is created.
     * @author      Reto Kohli <reto.kohli@comvation.com>
     */
    function makeClone()
    {
        $this->id = 0;
    }


    /**
     * Replace the image path for the given object ID and ordinal number.
     *
     * If no object with that ID and ordinal can be found, creates a new one.
     * In that case, the $image_type_id parameter must be non-empty.
     * @param   integer     $image_id       The object ID
     * @param   integer     $ord            The ordinal number
     * @param   string      $path           The path
     * @param   integer     $image_type_id  The image type ID
     * @param   integer     $width          The optional width, overrides automatic value
     * @param   integer     $ord            The optional height, overrides automatic value
     * @return  boolean                     True on success, false otherwise
     */
    function replace($image_id, $ord, $path, $image_type_id='', $width=false, $height=false)
    {
        $objImage = Image::getById($image_id, $ord);
        if (!$objImage && empty($image_type_id)) return false;
        if (!$objImage) $objImage = new Image($ord);
        $imageSize = getimagesize(ASCMS_DOCUMENT_ROOT.'/'.$path);
        if ($width === false || $height === false) {
            $width = $imageSize[0];
            $height = $imageSize[1];
        }
        $path_parts = pathinfo($path);

// TODO:  Debug stuff, remove in release
        $auto_type = $imageSize[2];
        if ($auto_type !== strtoupper($path_parts['extension']))
//echo("Image::replace(image_id $image_id, ord $ord, path $path, image_type_id $image_type_id, width $width, height $height): Warning: Image extension (".$path_parts['extension'].") mismatch with type ($auto_type)<br />");
// /TODO

        if ($image_type_id) $objImage->setTypeKey($image_type_id);
        $objImage->setPath($path);
        $objImage->setFileTypeId(Filetype::getTypeIdForExtension($path_parts['extension']));
        $objImage->setWidth($width);
        $objImage->setHeight($height);
        return $objImage->store();
    }


    /**
     * Delete this object from the database.
     * @todo        Delete existing files and thumbnails along with it.
     * @global      mixed       $objDatabase    Database object
     * @return      boolean                     True on success, false otherwise
     * @author      Reto Kohli <reto.kohli@comvation.com>
     */
    function delete()
    {
        global $objDatabase;

        if (!$this->id) return false;

        $objResult = $objDatabase->Execute("
            DELETE FROM ".DBPREFIX."core_image
             WHERE id=$this->id
               AND ord=$this->ord
        ");
        if (!$objResult) return self::errorHandler();
/*
        if (file_exists(self::$imageFolder.$this->path)) {
            if (!@unlink(self::$imageFolder.$this->path)) {
                return false;
            }
        }
        if (file_exists(self::$imageFolder.$this->path.'.thumb')) {
            if (!@unlink(self::$imageFolder.$this->path.'.thumb')) {
                return false;
            }
        }
*/
        return true;
    }


    /**
     * Delete the Image objects selected by their ID and optional
     * ordinal number from the database.
     *
     * If you don't specify an ordinal number, this method will delete
     * any Image records with that ID.  Otherwise, only the selected
     * Image will be removed.
     * @todo        Existing thumbnails are deleted along with them.
     * @static
     * @global      mixed       $objDatabase    Database object
     * @param       integer     $image_id        The Image ID
     * @param       mixed       $ord            The optional ordinal number
     * @return      boolean                     True on success, false otherwise
     * @author      Reto Kohli <reto.kohli@comvation.com>
     */
    static function deleteById($image_id, $ord=false)
    {
        global $objDatabase;

        $arrImages = self::getArrayById($image_id, $ord);
        if (!is_array($arrImages)) return false;

        foreach (array_keys($arrImages) as $ord) {
            $objImage = self::getById($image_id, $ord);
            if (!$objImage) return false;
            if (!$objImage->delete()) return false;
        }
        return true;
    }


    /**
     * Test whether a record with the ID and ordinal number of this object
     * is already present in the database.
     * @return  boolean                     True if the record exists,
     *                                      false otherwise
     * @global  mixed       $objDatabase    Database object
     * @author      Reto Kohli <reto.kohli@comvation.com>
     */
    function recordExists()
    {
        global $objDatabase;

        if (!$this->id) return false;

        $query = "
            SELECT 1
              FROM ".DBPREFIX."core_image
             WHERE id=$this->id
               AND ord=$this->ord";
        $objResult = $objDatabase->Execute($query);
        if (!$objResult) return self::errorHandler();
        if ($objResult->EOF) return false;
        return true;
    }


    /**
     * Stores the object in the database.
     *
     * Either updates or inserts the object, depending on the outcome
     * of the call to {@link recordExists()}.
     * @return      boolean     True on success, false otherwise
     * @author      Reto Kohli <reto.kohli@comvation.com>
     */
    function store()
    {
        $result = false;
        if ($this->id && $this->recordExists()) {
            $result = $this->update();
        } else {
            $result = $this->insert();
        }
/*
        if ($result) {
            $result = self::makeThumbnail();
        }
*/
        return $result;
    }


    /**
     * Update this object in the database.
     * @return      integer                     The Image ID on success,
     *                                          false otherwise
     * @global      mixed       $objDatabase    Database object
     * @author      Reto Kohli <reto.kohli@comvation.com>
     */
    function update()
    {
        global $objDatabase;

        $query = "
            UPDATE ".DBPREFIX."core_image
               SET `id`=$this->id,
                   `image_type_id`=$this->image_type_id,
                   `file_type_id`=".($this->file_type_id
                      ? $this->file_type_id : 'NULL').",
                   `path`='".addslashes($this->path)."',
                   `width`=".($this->width ? $this->width : 'NULL').",
                   `height`=".($this->height ? $this->height : 'NULL')."
             WHERE `id`=$this->id
               AND `ord`=$this->ord
        ";
        $objResult = $objDatabase->Execute($query);
        if (!$objResult) return self::errorHandler();
        return $this->id;
    }


    /**
     * Insert this object into the database.
     * @return      integer                     The Image ID on success,
     *                                          false otherwise
     * @global      mixed       $objDatabase    Database object
     * @author      Reto Kohli <reto.kohli@comvation.com>
     */
    function insert()
    {
        global $objDatabase;

        $query = "
            INSERT INTO ".DBPREFIX."core_image (
                ".($this->id ? '`id`, ' : '').
                "`ord`, `image_type_id`,
                `file_type_id`, `path`,
                `width`, `height`
            ) VALUES (
                ".($this->id ? "$this->id, " : '').
                "$this->ord, $this->image_type_id,
                ".($this->file_type_id ? $this->file_type_id : 'NULL').",
                '".addslashes($this->path)."',
                `width`=".($this->width ? $this->width : 'NULL').",
                `height`=".($this->height ? $this->height : 'NULL')."
            )
        ";
        $objResult = $objDatabase->Execute($query);
        if (!$objResult) return self::errorHandler();
        if ($this->id == 0) $this->id = $objDatabase->Insert_ID();
//echo("Image::insert(): Inserted ID $this->id<br />");
        return $this->id;
    }


    /**
     * Select an object by ID from the database.
     * @static
     * @param       integer     $id             The object ID
     * @param       integer     $ord            The ordinal number
     * @return      Image                       The object on success,
     *                                          false otherwise
     * @global      mixed       $objDatabase    Database object
     * @author      Reto Kohli <reto.kohli@comvation.com>
     */
    static function getById($image_id, $ord)
    {
        global $objDatabase;

        if (empty($image_id)) return false;
        $query = "
            SELECT `image_type_id`, `file_type_id`,
                   `path`, `width`, `height`
              FROM ".DBPREFIX."core_image
             WHERE id=$image_id
               AND ord=$ord";
        $objResult = $objDatabase->Execute($query);
        if (!$objResult) return self::errorHandler();
        if ($objResult->EOF) return false;
        $objImage = new Image($ord, $image_id);
        $objImage->image_type_id = $objResult->fields['image_type_id'];
        $objImage->file_type_id = $objResult->fields['file_type_id'];
        $objImage->path = $objResult->fields['path'];
        $objImage->width = $objResult->fields['width'];
        $objImage->height = $objResult->fields['height'];
        return $objImage;
    }


    /**
     * Returns an array with all fields from the Image records
     * for the Image ID given.
     *
     * The array is indexed by the ordinal numbers.  If more than one image
     * is found, the array is sorted by those in ascending order.
     * The returned array looks like
     *  array(
     *    ord => array(
     *      'id'           => image ID,
     *      'ord'          => ord,
     *      'image_type_id'     => image type ID,
     *      'file_type_id' => file type ID,
     *      'path'         => path,
     *      'width'        => width,
     *      'height'       => height,
     *    ),
     *    [...]
     *  )
     * @static
     * @param       integer     $image_id        The Image ID
     * @param       integer     $ord            The optional ordinal number
     * @return      array                       The fields array on success,
     *                                          false otherwise
     * @author      Reto Kohli <reto.kohli@comvation.com>
     */
    static function getArrayById($image_id, $ord=false)
    {
        global $objDatabase;

        $query = "
            SELECT `ord`, `image_type_id`, `file_type_id`,
                   `path`, `width`, `height`
              FROM ".DBPREFIX."core_image
             WHERE id=$image_id
               ".($ord === false ? 'ORDER BY ord ASC' : "AND ord=$ord")
        ;
        $objResult = $objDatabase->Execute($query);
        if (!$objResult) {
            return self::errorHandler();
        }
        $arrImage = array();
        while (!$objResult->EOF) {
            $arrImage[$objResult->fields['ord']] = array(
                'id'           => $objResult->fields['id'],
                'ord'          => $objResult->fields['ord'],
                'image_type_id'     => $objResult->fields['image_type_id'],
                'file_type_id' => $objResult->fields['file_type_id'],
                'path'         => $objResult->fields['path'],
                'width'        => $objResult->fields['width'],
                'height'       => $objResult->fields['height'],
            );
            $objResult->MoveNext();
        }
        return $arrImage;
    }


    /**
     * Create a new thumbnail for the image object.
     *
     * Removes the old thumbnail file first, if present
     * ({@see ImageManager::_createThumbWhq()}).
     * Note that this should not be used for bulk thumbnail creation.
     * It creates a new instance of the {@see ImageManager) class on each call
     * and thus is rather slow.
     * @return  boolean         True on success, false otherwise.
     * @author  Reto Kohli <reto.kohli@comvation.com>
     */
    function makeThumbnail()
    {
        global $objDatabase;
// TODO:  Actually, this file should be called "ImageManager.class.php"...
        require_once ASCMS_FRAMEWORK_PATH."/Image.class.php";

        if (!$this->id) return false;
        $objImageManager = new ImageManager();
        // only try to create thumbs from entries that contain a
        // plain text file name with a known extension
        if (   $this->path == ''
            || !preg_match('/\.(?:jpe?g|gif|png)$/', $this->path)) return false;
        // Get the thumbnail size for the associated type
        $arrThumbnailSize = Imagetype::getThumbnailSizeByTypeKey($this->image_type_id);
        if (!is_array($arrThumbnailSize)) {
            $arrThumbnailSize = array(
                Config::getByName('hotelcard_default_thumbnail_width'),
                Config::getByName('hotelcard_default_thumbnail_width'),
            );
        }
        // Reset the ImageManager
        $objImageManager->imageCheck = 1;
        // Create thumbnail

        if (!$objImageManager->_createThumbWhq(
            ASCMS_DOCUMENT_ROOT.'/',
            '',
            $this->path,
            $arrThumbnailSize[0],
            $arrThumbnailSize[1],
            90
        )) {
            return false;
        }
        return true;
    }


    /**
     * Uploads an image file and stores its information in the database
     * @param   string  $upload_field_name  File input field name
     * @param   string  $target_path        Target path, relative to the document
     *                                      root, including the file name
     * @return  integer                     The new image ID on success, false otherwise
     * @author    Reto Kohli <reto.kohli@comvation.com>
     */
    function uploadAndStore(
        $upload_field_name, $target_path,
        $image_id=false, $image_type_id=false, $ord=false)
    {
        // $target_path *SHOULD* be like ASCMS_HOTELCARD_IMAGES_FOLDER.'/folder/name.ext'
        // Strip path offset, if any, from the target path
        $target_path = preg_replace('/^'.preg_quote(ASCMS_PATH_OFFSET, '/').'/', '', $target_path);
        if (!File::uploadFileHttp(
            $upload_field_name, $target_path,
            self::MAXIMUM_UPLOAD_FILE_SIZE, self::$arrAcceptedFiletype)
        ) {
//echo("Image::uploadAndStore($upload_field_name, $target_path, $image_id, $image_type_id, $ord): Failed to upload<br />");
            return false;
        }
        if ($image_id) {
            if ($ord === false) $ord = self::getNextOrd($image_id, $image_type_id);
        }
        $objImage = new Image($ord, $image_id);
        $objImage->setPath($target_path);
        $size = getimagesize(ASCMS_DOCUMENT_ROOT.'/'.$target_path);
        $objImage->setWidth($size[0]);
        $objImage->setHeight($size[1]);
        $objImage->setImageTypeId($image_type_id);
        if ($objImage->store()) {
//echo("Image::uploadAndStore(): Successfully stored<br />");
            return $objImage->getId();
        }
//echo("Image::uploadAndStore(): Failed to store<br />");
        return false;
    }


    /**
     * Returns the last used ordinal value plus one for the image ID and
     * image type ID given
     *
     * If there is no matching one yet, returns 1.
     * @param   integer   $image_id       The optional image ID
     * @param   integer   $image_type_id  The optional image type ID
     * @return  integer                   The next ordinal number on success,
     *                                    false otherwise
     */
    static function getNextOrd($image_id=0, $image_type_id=0)
    {
        global $objDatabase;

        $query = "
            SELECT MAX(`ord`) as `ord`
              FROM ".DBPREFIX."core_image
             WHERE 1".
              ($image_id      ? " AND `id`=$image_id"                 : '').
              ($image_type_id ? " AND `image_type_id`=$image_type_id" : '');
        $objResult = $objDatabase->Execute($query);
        if (!$objResult || $objResult->EOF) return self::errorHandler();
        return 1 + $objResult->fields['ord'];
    }


    /**
     * Handle any error occurring in this class.
     *
     * Tries to fix known problems with the database table.
     * @global  mixed     $objDatabase    Database object
     * @return  boolean                   False.  Always.
     * @author  Reto Kohli <reto.kohli@comvation.com>
     */
    function errorHandler()
    {
        global $objDatabase;

        $arrTables = $objDatabase->MetaTables('TABLES');
        if (!in_array(DBPREFIX."core_image", $arrTables)) {
            // The table doesn't exist yet!
            $query = "
                CREATE TABLE `".DBPREFIX."core_image` (
                  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
                  `ord` INT UNSIGNED NOT NULL DEFAULT 0 COMMENT 'Ordinal value allowing multiple images to be stored for the same image ID and type.\nUsed for sorting.\nDefaults to zero.',
                  `image_type_id` INT(10) UNSIGNED NULL DEFAULT NULL COMMENT 'Type ID allowing multiple images to be stored for the same image ID.\nRelates to core_image_type.type_text_id, which in turn is a core_text ID.\nDefaults to NULL, which is an untyped image.',
                  `file_type_id` INT UNSIGNED NULL COMMENT 'Image type is unknown if NULL.',
                  `path` TEXT NOT NULL COMMENT 'Path *SHOULD* be relative to the ASCMS_DOCUMENT_ROOT (document root + path offset).\nOmit leading slashes, these will be cut.',
                  `width` INT UNSIGNED NULL COMMENT 'Width is unknown if NULL.',
                  `height` INT UNSIGNED NULL COMMENT 'Height is unknown if NULL.',
                  PRIMARY KEY (`id`, `ord`),
                  INDEX `file_type_id` (`file_type_id` ASC),
                  INDEX `image_type_id` (`image_type_id` ASC),
                  CONSTRAINT `file_type_id`
                    FOREIGN KEY (`file_type_id`)
                    REFERENCES `contrexx_core_file_type` (`id`)
                    ON DELETE NO ACTION
                    ON UPDATE NO ACTION,
                  CONSTRAINT `image_type_id`
                    FOREIGN KEY (`image_type_id`)
                    REFERENCES `core_image_type` (`key`)
                    ON DELETE NO ACTION
                    ON UPDATE NO ACTION)
                ENGINE=MyISAM
            ";
            $objResult = $objDatabase->Execute($query);
            if (!$objResult) return false;
        }

        // More to come...

        return false;
    }


    /**
     * Scales the given Image width and height to fit both maximum values.
     *
     * Note that both the $width and $height parameters are passed by
     * reference and are modified.
     * @param   integer     $width        The Image width
     * @param   integer     $height       The Image height
     * @param   integer     $maxwidth     The maximum width
     * @param   integer     $maxheight    The maximum height
     */
    static function scaleDimensions(&$width, &$height, $maxwidth, $maxheight)
    {
        $ratio = $width / $height;
        $maxratio = $maxwidth / $maxheight;
        if ($ratio > $maxratio) {
            $width = $maxwidth;
            $height *= intval($maxwidth/$width);
        } else {
            $width *= intval($maxheight/$height);
            $height = $maxheight;
        }
    }
}

?>
