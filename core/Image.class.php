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
    const ICON_CLEAR_IMAGE_SRC = 'images/modules/hotelcard/clear_image.gif';

    /**
     * The default "no image" URI
     */
    const PATH_NO_IMAGE = 'images/modules/hotelcard/no_image.gif';

    /**
     * Size limit in bytes for images being uploaded or stored
     */
    const MAXIMUM_UPLOAD_FILE_SIZE = 300000;

    /**
     * Thumbnail suffix
     */
    const THUMBNAIL_SUFFIX = '.thumb';

    /**
     * Array of all file extensions accepted as image files
     * @var   array
     */
    private static $arrAcceptedFiletype = array(
        'gif', 'jpg', 'png',
    );

    /**
     * @var     integer         $id               The object ID, PRIMARY
     * @access  private
     */
    private $id = false;

    /**
     * The ordinal number
     * @var     integer         $ord              The ordinal number, PRIMARY
     * @access  private
     */
    private $ord = 0;

    /**
     * @var     integer   $image_type_keys    The image type key
     * @access  private
     */
    private $image_type_key = false;

    /**
     * @var     integer         $file_type_key    The file type key
     * @access  private
     */
    private $file_type_key = false;

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
    function __construct($ord=0, $image_id=0)
    {
        $this->ord = (empty($ord)      ? 0 : $ord);
        $this->id =  (empty($image_id) ? 0 : $image_id);
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
    function getImageTypeKey()
    {
        return $this->image_type_key;
    }
    /**
     * Set the type ID
     *
     * Any non-positive value or string will be interpreted as NULL
     * @param   integer          $image_type_key  The type ID
     * @author      Reto Kohli <reto.kohli@comvation.com>
     */
    function setImageTypeKey($image_type_key)
    {
        $this->image_type_key = (empty($image_type_key) ? 'NULL' : $image_type_key);
    }

    /**
     * Get the file type key
     * @return  integer                              The file type key
     * @author      Reto Kohli <reto.kohli@comvation.com>
     */
    function getFileTypeKey()
    {
        return $this->file_type_key;
    }
    /**
     * Set the file type key
     *
     * Any non-positive value or string will be interpreted as NULL
     * @param   integer          $file_type_key       The file type key
     * @author      Reto Kohli <reto.kohli@comvation.com>
     */
    function setFileTypeKey($file_type_key)
    {
        $this->file_type_key = (empty($file_type_key) ? 'NULL' : $file_type_key);
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
// Should not be necessary, as the proper File methods are used to get this path
//        File::pathRelativeToRoot($path);
        $path = preg_replace('/\.thumb$/', '', $path);
        if ($path == self::PATH_NO_IMAGE) {
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
        $this->width = (intval($width) > 0 ? $width : 0);
    }

    /**
     * Get the height
     * @return  integer                              The height
     * @author  Reto Kohli <reto.kohli@comvation.com>
     */
    function getHeight()
    {
        return $this->height;
    }
    /**
     * Set the height
     * @param   integer          $height               The height
     * @author  Reto Kohli <reto.kohli@comvation.com>
     */
    function setHeight($height)
    {
        $this->height = (intval($height) > 0 ? $height : 0);
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
     * In that case, the $image_type_key parameter must be non-empty.
     * @param   integer     $image_id       The object ID
     * @param   integer     $ord            The ordinal number
     * @param   string      $path           The path
     * @param   integer     $image_type_key  The image type key
     * @param   integer     $width          The optional width, overrides automatic value
     * @param   integer     $ord            The optional height, overrides automatic value
     * @return  boolean                     True on success, false otherwise
     */
    function replace($image_id, $ord, $path, $image_type_key='', $width=false, $height=false)
    {
//echo("Image::replace($image_id, $ord, $path, $image_type_key, $width, $height): Entered<br />");
        $objImage = Image::getById($image_id, $ord);
        if (!$objImage && empty($image_type_key)) {
//echo("Image::replace(): Image not found and empty key<br />");
            return false;
        }
        if (!$objImage) $objImage = new Image($ord);

        $imageSize = getimagesize(ASCMS_DOCUMENT_ROOT.'/'.$path);
        if ($width === false || $height === false) {
            $width = $imageSize[0];
            $height = $imageSize[1];
//echo("Image::replace(): Image size: $width/$height<br />");
        }
        $path_parts = pathinfo($path);

// TODO:  Debug stuff, remove in release
//        $auto_type = $imageSize[2];
//        if ($auto_type !== strtoupper($path_parts['extension']))
//echo("Image::replace(image_id $image_id, ord $ord, path $path, image_type_key $image_type_key, width $width, height $height): Warning: Image extension (".$path_parts['extension'].") mismatch with type ($auto_type)<br />");
// /TODO

        if ($image_type_key) $objImage->setTypeKey($image_type_key);
        $objImage->setPath($path);
        $objImage->setFileTypeKey(Filetype::getTypeIdForExtension($path_parts['extension']));
        $objImage->setWidth($width);
        $objImage->setHeight($height);
//echo("Image::replace(): Storing Image<br />");
        return $objImage->store();
    }


    /**
     * Delete this object from the database.
     *
     * If the $delete_files parameter is true, the file and thumbnail
     * will be deleted as well
     * @param   boolean       $delete_files   If true, the files are
     *                                        deleted, too
     * @return  boolean                       True on success, false otherwise
     * @global  ADOConnection $objDatabase    Database object
     * @author  Reto Kohli <reto.kohli@comvation.com>
     */
    function delete($delete_files=false)
    {
        global $objDatabase;

        if ($delete_files && $this->path) {
            File::delete_file($this->path);
            File::delete_file(self::getThumbnailFilename($this->path));
        }
        if (!$this->id) return false;
        $objResult = $objDatabase->Execute("
            DELETE FROM ".DBPREFIX."core_image
             WHERE id=$this->id
               AND ord=$this->ord
        ");
        if (!$objResult) return self::errorHandler();
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
     * @param       integer     $image_id       The Image ID
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
               SET `image_type_key`=".
                ($this->image_type_key
                  ? "'".addslashes($this->image_type_key)."'"
                  : 'NULL').",
                   `file_type_key`=".
                ($this->file_type_key
                  ? "'".addslashes($this->file_type_key)."'"
                  : 'NULL').",
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
                "`ord`, `image_type_key`,
                `file_type_key`, `path`,
                `width`, `height`
            ) VALUES (
                ".($this->id ? "$this->id, " : '').
                ($this->ord ? $this->ord : 0).",
                ".($this->image_type_key
                  ? "'".addslashes($this->image_type_key)."'" : 'NULL').",
                ".($this->file_type_key
                  ? "'".addslashes($this->file_type_key)."'" : 'NULL').",
                '".addslashes($this->path)."',
                ".($this->width ? $this->width : 'NULL').",
                ".($this->height ? $this->height : 'NULL')."
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
     * @param       integer     $ord            The optional ordinal number,
     *                                          defaults to zero
     * @return      Image                       The object on success,
     *                                          false otherwise
     * @global      mixed       $objDatabase    Database object
     * @author      Reto Kohli <reto.kohli@comvation.com>
     */
    static function getById($image_id, $ord=0)
    {
        global $objDatabase;

        if (empty($image_id)) return false;
        // This may not be what you want, but it's your fault in that case
        if (empty($ord)) $ord = 0;
        $query = "
            SELECT `image_type_key`, `file_type_key`,
                   `path`, `width`, `height`
              FROM ".DBPREFIX."core_image
             WHERE id=$image_id
               AND ord=$ord";
        $objResult = $objDatabase->Execute($query);
        if (!$objResult) return self::errorHandler();
        if ($objResult->EOF) return false;
        $objImage = new Image($ord, $image_id);
        $objImage->image_type_key = $objResult->fields['image_type_key'];
        $objImage->file_type_key = $objResult->fields['file_type_key'];
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
     *      'image_type_key'     => image type key,
     *      'file_type_key' => file type key,
     *      'path'         => path,
     *      'width'        => width,
     *      'height'       => height,
     *    ),
     *    [...]
     *  )
     * @static
     * @param       integer     $image_id       The Image ID
     * @param       integer     $key            The optional key
     * @param       integer     $ord            The optional ordinal number
     * @return      array                       The fields array on success,
     *                                          false otherwise
     * @author      Reto Kohli <reto.kohli@comvation.com>
     */
    static function getArrayById($image_id, $key=false, $ord=false)
    {
        global $objDatabase;

        if (empty($image_id)) return false;
        // This may not be what you want, but it's your fault in that case
        $query = "
            SELECT `ord`, `image_type_key`, `file_type_key`,
                   `path`, `width`, `height`
              FROM ".DBPREFIX."core_image
             WHERE id=$image_id".
               ($key !== false ? " AND `image_type_key`='".addslashes($key)."'" : '').
               ($ord !== false ? " AND `ord`=$ord" : '');
        $objResult = $objDatabase->Execute($query);
        if (!$objResult) return self::errorHandler();
        $arrImage = array();
        while (!$objResult->EOF) {
            $arrImage[$objResult->fields['ord']] = array(
                'id'             => $image_id,
                'ord'            => $objResult->fields['ord'],
                'image_type_key' => $objResult->fields['image_type_key'],
                'file_type_key'  => $objResult->fields['file_type_key'],
                'path'           => $objResult->fields['path'],
                'width'          => $objResult->fields['width'],
                'height'         => $objResult->fields['height'],
            );
            $objResult->MoveNext();
        }
        return $arrImage;
    }


    /**
     * Create a new thumbnail for the image object
     *
     * This uses this Images' key to determine the Imagetype and its
     * default thumbnail size.
     * @return  boolean         True on success, false otherwise.
     * @author  Reto Kohli <reto.kohli@comvation.com>
     */
    function makeThumbnail()
    {
        global $objDatabase;

        if (!$this->id) return false;
        // Only try to create thumbs from entries that contain a
        // file name with a known extension
        if (   $this->path == ''
            || !preg_match('/\.(?:jpe?g|gif|png)$/i', $this->path)) return false;
        // Get the thumbnail size for the associated type
        $arrOptions =
            Imagetype::getThumbnailOptions($this->image_type_key);
        return self::createThumbnail($this->path,
            $arrOptions['width'], $arrOptions['height'],
            $arrOptions['quality']);
    }



    /**
     * Returns a scaled image size array
     *
     * The $size array is the same as the one returned by {@see getimagesize()}.
     * One of $maxwidth and $maxheight may be zero, in which case it is ignored.
     * The size is then calculated to fit the other while maintaining the
     * ratio.  If both are zero, the original $size is returned unchanged.
     * @param   array       $size         The Image size array
     * @param   integer     $maxwidth     The maximum width
     * @param   integer     $maxheight    The maximum height
     * @return  array                     The scaled size array
     */
    static function getScaledSize($size, $maxwidth, $maxheight)
    {
        if ($maxwidth == 0 && $maxheight == 0) return $size;
        if ($maxwidth == 0) $maxwidth = 1e9;
        if ($maxheight == 0) $maxheight = 1e9;
        $ratio    = $size[0] / $size[1];
        $maxratio = $maxwidth / $maxheight;
        if ($ratio < $maxratio) {
            $size[0] = intval($maxheight*$ratio);
            $size[1] = $maxheight;
            return $size;
        }
        $size[0] = $maxwidth;
        $size[1] = intval($maxwidth/$ratio);
        return $size;
    }


    /**
     * Create a thumbnail of a picture.
     *
     * Both the width and height of the thumbnail may be
     * specified; the picture will still be scaled to fit within the given
     * sizes while keeping the original width/height ratio.
     * In addition to that, this method tries to delete an existing
     * thumbnail before attempting to write the new one.
     * Note that thumbnails are always created as jpeg image files!
     * @param   string  $image_path     The image file path
     * @param   integer $maxWidth       The maximum width of the thumbnail
     * @param   integer $maxHeight      The maximum height of the thumbnail
     * @param   integer $quality        The desired jpeg thumbnail quality
     * @return  bool                    True on success, false otherwise.
     * @static
     */
    static function createThumbnail(
        $image_path, $maxWidth=160, $maxHeight=120, $quality=90
    ) {
        return self::scale(
            $image_path, self::getThumbnailFilename($image_path),
            true, $maxWidth, $maxHeight, $quality);
    }


    /**
     * Create a scaled version of a picture.
     *
     * Both the width and height of the thumbnail may be
     * specified; the picture will still be scaled to fit within the given
     * sizes while keeping the original width/height ratio.
     * In addition to that, this method tries to delete an existing
     * target image before attempting to write the new one.
     * Note that scaled images are always created as jpeg image files!
     * @param   string  $source_path    The source image file path
     * @param   string  $target_path    The target image file path
     * @param   boolean $force          If true, the target image is forced
     *                                  to be overwritten
     * @param   integer $maxWidth       The maximum width of the image
     * @param   integer $maxHeight      The maximum height of the image
     * @param   integer $quality        The desired jpeg thumbnail quality
     * @return  bool                    True on success, false otherwise.
     * @static
     */
    static function scale(
        $source_path, $target_path, $force=false,
        $maxWidth=160, $maxHeight=120, $quality=90
    ) {
        File::pathRelativeToRoot($source_path);
        $original_size = getimagesize(ASCMS_DOCUMENT_ROOT.'/'.$source_path);
        $scaled_size = self::getScaledSize(
            $original_size, $maxWidth, $maxHeight);
        $source_image = self::load();
        if (!$source_image) return false;
        $target_image = false;
        if (function_exists ('imagecreatetruecolor'))
            $target_image = @imagecreatetruecolor($scaled_size[0], $scaled_size[1]);
        if (!$target_image)
            $target_image = ImageCreate($scaled_size[0], $scaled_size[1]);
        imagecopyresized(
            $target_image, $source_image, 0, 0, 0, 0,
            $scaled_size[0] + 1, $scaled_size[1] + 1,
            $original_size[0]  + 1, $original_size[1]  + 1
        );
        return self::saveJpeg($target_image, $target_path, $quality, $force);
    }


    /**
     * Takes an image path and returns the corresponding thumbnail file name
     *
     * If the path belongs to a thumbnail already, it is returned unchanged.
     * Note that any thumbnails are created as jpeg image files!
     * @param   string    $image_path     The original image path
     * @return  string                    The thumbnail image path
     */
    static function getThumbnailFilename($image_path)
    {
        if (preg_match(
            '/'.preg_quote(self::THUMBNAIL_SUFFIX.'.jpg', '/').'$/',
            $image_path)) {
//echo("Image::getThumbnailFilename(): $image_path is a thumbnail already<br />");
            return $image_path;
        }
        // Insert the thumbnail suffix *before* the original extension, if any
        $thumb_path = preg_replace(
            '/(?:\.\w+)?$/', self::THUMBNAIL_SUFFIX.'.jpg', $image_path);
//echo("Image::getThumbnailFilename(): fixed $image_path to $thumb_path<br />");
        return $thumb_path;
    }


    /**
     * Saves the image to the path given as a jpeg file
     * @access  public
     * @param   string    $file   The path for the jpeg image file to be written
     * @param   booelan   $force  Force overwriting existing files if true
     * @return  boolean           True on success, false otherwise
     */
    function saveJpeg($image, $path, $quality=90, $force=false)
    {
        if (File::exists($path) && !$force) return false;
        File::delete_file($path);
        if (imagejpeg($image, $path, $quality)) {
            return File::chmod($path, File::CHMOD_FILE);
        }
        return false;
    }


    /**
     * NOT IMPLEMENTED
     * Output the image with the path given in the browser
     *
     * This method will not return!
     * @access   public
     * @return   void
    static function showImage($path)
    {
    }
     */


    /**
     * Stores any image files from a post request
     *
     * The files may be uploaded or chosen in the file browser.
     * Each file is moved to the given target path with a uniquid()
     * prepended to the file name; the original name is stored temporarily
     * only in the session.
     * If possible, the corresponding Image object is updated.  If not,
     * a new one is created and stored.
     * The original file name, type, Image ID and ordinal value are stored
     * in the session under $_SESSION['image'][<field_name>].
     * @param   string  $target_folder_path   The target folder path for
     *                                        uploaded images only
     * @return  integer             The Image ID if all images have been
     *                              processed successfully,
     *                              false if errors occurred,
     *                              or the empty string if nothing changed
     */
    static function processPostFiles($target_folder_path)
    {
//echo("Image::processPostFiles($target_folder_path): Entered<br />");
        // Cases:
        // If present, pick the path, ID, ord and type from the session,
        // overwrite with those from the post.
        // - Post with a file upload (remember that this requires a multipart
        //   encoded form):
        //    Fields: id, ord, type, file
        //    - For those with valid file upload parameters (no error):
        //      insert or update the file and image.
        //    - For those with invalid parameters (error):
        //      - If there's no ID, ignore it
        //      - If ID and ord are valid, but the src has been posted empty,
        //        delete the file and image.
        // - Post with image selection from the file browser:
        //    Fields: id, ord, type src
        //    - If the src is present, try to get id and ord
        //    - If the src is valid, either update or insert the image
        //    - if the src is empty, but id and ord are valid,
        //      delete the image and file

        // Collect all posted images from file upload and post
        $arrName = array();
        if (is_array($_FILES)) {
            $arrName = array_keys($_FILES);
        }
        if (is_array($_POST)) {
            $match = array();
            foreach (array_keys($_POST) as $name) {
                if (!preg_match('/^(\w+)_src$/', $name, $match)) continue;
                if (!in_array($match[1], $arrName))
                    $arrName[] = $match[1];
            }
        }
//echo("Image::processPostFiles($target_folder_path): Made name array<br />".var_export($arrName, true)."<hr />");

        $result = ''; // No change
//echo("Image::processPostFiles(): Collected image field names: ".var_export($arrName, true)."<hr />"."FILES: ".var_export($_FILES, true)."<hr />"."POST: ".var_export($_POST, true)."<hr />");
        // Process all images found
        foreach ($arrName as $name) {
            $changed = false;
//echo("Image::processPostFiles(): Processing image field name: $name<br />");
            $image_name = false; // The image original name
            $image_src  = false; // The image path
            $image_id   = false; // The image ID
            $image_ord  = false; // The image ordinal value
            $image_type = false; // The image type key
            // Try to get the image object coordinates from the session,
            // in the ['image'][$name] branch, ...
            if (isset($_SESSION['image'][$name]['src']))
                $image_src  = $_SESSION['image'][$name]['src'];
            if (isset($_SESSION['image'][$name]['id']))
                $image_id   = $_SESSION['image'][$name]['id'];
            if (isset($_SESSION['image'][$name]['ord']))
                $image_ord  = $_SESSION['image'][$name]['ord'];
            if (isset($_SESSION['image'][$name]['type']))
                $image_type = $_SESSION['image'][$name]['type'];
            // ...or get them from the post.
            // There may be fields with the name plus suffix
            // These override the session parameters.
            if (isset($_FILES[$name]))        $image_name = $_FILES[$name]['name'];
            if (isset($_POST[$name.'_src']))  $image_src  = $_POST[$name.'_src'];
            if (isset($_POST[$name.'_id']))   $image_id   = $_POST[$name.'_id'];
            if (isset($_POST[$name.'_ord']))  $image_ord  = $_POST[$name.'_ord'];
            if (isset($_POST[$name.'_type'])) $image_type = $_POST[$name.'_type'];
//echo("Image::processPostFiles(): Got parameters for $name: image_name $image_name, image_src $image_src, image_id $image_id, image_ord $image_ord, image_type $image_type<br />");
            // Upload valid images and update the parameters
            $objImage = self::getById($image_id, $image_ord);
            if (!$objImage)
                $objImage = new Image($image_ord, $image_id);
            // The image original name is only set when uploading images
            if ($image_name) {
                // Uploads must go to the target folder
//                $image_src = $target_folder_path.'/'.uniqid().'_'.$image_name;
                $image_src = $target_folder_path.'/'.$image_name;
                if (!$objImage->delete(true)) {
//echo("Image::processPostFiles(): Failed deleting $image_src<br />");
                } else {
//echo("Image::processPostFiles(): Deleted $image_src<br />");

                }
//echo("Image::processPostFiles(): Uploading $image_name to $image_src<br />");
                if (!File::uploadFileHttp(
                    $name, $image_src,
                    self::MAXIMUM_UPLOAD_FILE_SIZE,
                    self::$arrAcceptedFiletype)
                ) {
                    // For failed uploads, do not change anything
//echo("Image::processPostFiles(): Uploading failed<hr />");
                    $result = false;
                    continue;
                }
            }
            // Delete the image if the src has been posted, but is empty
            if ($objImage->getPath() && $image_src === '') {
//echo("Image::processPostFiles(): Deleting ".$objImage->getPath()."<br />");
                unset($_SESSION['image'][$name]);
                // Also delete the files (image and thumb)
                if (!$objImage->delete(true)) {
//echo("Image::processPostFiles(): Failed deleting $image_src<br />");
                }
                continue;
            }
            // The Image is valid
            if ($image_src != $objImage->getPath()) {
                $objImage->setPath($image_src);
                $changed = true;
            }
            if ($image_src && File::exists($image_src)) {
                $size = getimagesize(ASCMS_DOCUMENT_ROOT.'/'.$image_src);
                if (   $size
                    && (   $size[0] != $objImage->getWidth()
                        || $size[1] != $objImage->getHeight())
                ) {
                    $objImage->setWidth($size[0]);
                    $objImage->setHeight($size[1]);
                    $changed = true;
                }
            }
            if (   $image_type !== false
                && $image_type != $objImage->getImageTypeKey()) {
                $objImage->setImageTypeKey($image_type);
                $changed = true;
            }
// TODO: File type
//            $objImage->setFileTypeKey('');
            if ($changed) {
                if ($objImage->store()) {
                    // The original name is never stored with the image, just kept
                    // for reference as long as the session is alive
                    if ($image_name)
                        $_SESSION['image'][$name]['name'] = $image_name;
                    $_SESSION['image'][$name]['src']  = $objImage->path;
                    $_SESSION['image'][$name]['id']   = $objImage->id;
                    $_SESSION['image'][$name]['ord']  = $objImage->ord;
                    $_SESSION['image'][$name]['type'] = $objImage->image_type_key;
//echo("Image::processPostFiles(): Successfully stored image $name, ID ".$objImage->getId()."<hr />");
                    if ($result === '') $result = true;
                } else {
//echo("Image::processPostFiles(): Failed storing $image_src<br />");
                    $result = false;
                }
            }
        }
//echo("Image::processPostFiles(): Result ".var_export($result, true).", image ID $objImage->id<br />");
        return ($result === true ? $objImage->id : $result);
    }


    /**
     * Returns the image data stored in the session for the given key
     *
     * If no such image is present, returns an image created from the
     * default path given, if any.
     * If the given default image does not exist, returns the Image class
     * default Image.
     * If that fails, too, returns false
     * @param   string    $key            The image key
     * @return  Image                     The default Image
     */
    static function getFromSessionByKey($key)
    {
        if (   isset($_SESSION['image'][$key])
            && isset($_SESSION['image'][$key]['id'])) {
//echo("Image::getFromSessionByKey($key): Found ".var_export($_SESSION['image'][$key], true)."<br />");
            $objImage = self::getById(
                $_SESSION['image'][$key]['id'],
                $_SESSION['image'][$key]['ord']
            );
            if ($objImage) return $objImage;
//echo("Image::getFromSessionByKey($key): Could not get the image<br />");
        }
        return false;
    }


    /**
     * Uploads an image file and stores its information in the database
     * @param   string  $upload_field_name  File input field name
     * @param   string  $target_path        Target path, relative to the
     *                                      document root, including the
     *                                      file name
     * @return  integer                     The new image ID on success,
     *                                      false otherwise
     * @author    Reto Kohli <reto.kohli@comvation.com>
     */
    static function uploadAndStore(
        $upload_field_name, &$target_path,
        $image_id=false, $image_type_key=false, $ord=false)
    {
        // $target_path *SHOULD* be like ASCMS_HOTELCARD_IMAGES_FOLDER.'/folder/name.ext'
        // Strip path offset, if any, from the target path
        $target_path = preg_replace('/^'.preg_quote(ASCMS_PATH_OFFSET, '/').'/', '', $target_path);
        if (!File::uploadFileHttp(
            $upload_field_name, $target_path,
            self::MAXIMUM_UPLOAD_FILE_SIZE, self::$arrAcceptedFiletype)
        ) {
//echo("Image::uploadAndStore($upload_field_name, $target_path, $image_id, $image_type_key, $ord): Failed to upload<br />");
            return false;
        }
        if ($image_id && $ord === false)
            $ord = self::getNextOrd($image_id, $image_type_key);
        $objImage = new Image($ord, $image_id);
        $objImage->setPath($target_path);
        $size = getimagesize(ASCMS_DOCUMENT_ROOT.'/'.$target_path);
        $objImage->setWidth($size[0]);
        $objImage->setHeight($size[1]);
        $objImage->setImageTypeKey($image_type_key);
//echo("Image::uploadAndStore(): Made Image:<br />".var_export($objImage, true)."<br />");
        if ($objImage->store()) {
//echo("Image::uploadAndStore(): Successfully stored<br />");
            return $objImage->getId();
        }
//echo("Image::uploadAndStore(): Failed to store<br />");
        if (!File::delete_file($target_path)) {
//echo("Image::uploadAndStore(): Failed to delete file $target_path<br />");
        }
        return false;
    }


    /**
     * Returns the last used ordinal value plus one for the image ID and
     * image type key given
     *
     * If there is no matching one yet, returns 1.
     * @param   integer   $image_id         The optional image ID
     * @param   integer   $image_type_key   The optional image type key
     * @return  integer                     The next ordinal number on success,
     *                                      false otherwise
     */
    static function getNextOrd($image_id, $image_type_key=false)
    {
        global $objDatabase;

        $query = "
            SELECT MAX(`ord`) as `ord`
              FROM ".DBPREFIX."core_image
             WHERE `id`=$image_id".
              ($image_type_key
                  ? " AND `image_type_key`=".addslashes($image_type_key) : '');
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

die("Image::errorHandler(): Disabled!<br />");

        $arrTables = $objDatabase->MetaTables('TABLES');
        if (in_array(DBPREFIX."core_image", $arrTables)) {
            $query = "DROP TABLE `".DBPREFIX."core_image`";
            $objResult = $objDatabase->Execute($query);
            if (!$objResult) return false;
        }
        // The table doesn't exist yet!
        $query = "
            CREATE TABLE `".DBPREFIX."core_image` (
              `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
              `ord` INT UNSIGNED NOT NULL DEFAULT 0 COMMENT 'Ordinal value allowing multiple images to be stored for the same image ID and type.\nUsed for sorting.\nDefaults to zero.',
              `image_type_key` TINYTEXT NULL DEFAULT NULL COMMENT 'Defaults to NULL, which is an untyped image.',
              `file_type_key` TINYTEXT NULL DEFAULT NULL COMMENT 'File type is unknown if NULL.',
              `path` TEXT NOT NULL COMMENT 'Path *SHOULD* be relative to the ASCMS_DOCUMENT_ROOT (document root + path offset).\nOmit leading slashes, these will be cut.',
              `width` INT UNSIGNED NULL COMMENT 'Width is unknown if NULL.',
              `height` INT UNSIGNED NULL COMMENT 'Height is unknown if NULL.',
              PRIMARY KEY (`id`, `ord`),
              KEY `image_type` (`image_type_key`(32)),
              KEY `file_type` (`file_type_key`(32)))
            ENGINE=MyISAM
        ";
        $objResult = $objDatabase->Execute($query);
        if (!$objResult) return false;

        // More to come...

        return false;
    }

}

?>
