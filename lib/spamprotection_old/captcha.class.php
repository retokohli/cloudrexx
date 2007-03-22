<?PHP
/**
 * Captcha 
 * @copyright   CONTREXX CMS - ASTALAVISTA IT AG
 * @author      Astalavista Development Team <thun@astalvista.ch>
 * @version     1.0.0
 * @package     contrexx
 * @subpackage  lib_spamprotection_old
 * @todo        Edit PHP DocBlocks!
 */

/**
 * Captcha
 * @copyright   CONTREXX CMS - ASTALAVISTA IT AG
 * @author      Astalavista Development Team <thun@astalvista.ch>
 * @access      public
 * @version     1.0.0
 * @package     contrexx
 * @subpackage  lib_spamprotection_old
 */
class Captcha
{
	/**
	 * Font directory
	 */
	var $fontDir;

	/**
	 * Font list
	 */
	var $fontList;

	/**
	 * Image width
	 */
	var $width = 155;

	/**
	 * Offset number
	 */
	var $offset;

	/**
	 * Constructor
	 */
	function __construct($standalone = false)
	{
		if (!$standalone) {
			// called by the index.php file
			$this->fontDir = ASCMS_DOCUMENT_ROOT . "/lib/spamprotection/fonts";
			$this->generateOffset();
		} else {
			// script runs standalone
			$this->fontDir = "fonts";
		}
	}

	/**
	 * PHP4 Constructor
	 */
	function Captcha($standalone = false)
	{
		$this->__construct($standalone);
	}

	/**
	 * Generates an Image
	 */
	function generateImage()
	{
		$width = $this->width;

		// Font size
		$size = 20;

		if (!empty($_GET['offset'])) {
			$this->offset = intval($_GET['offset']);

			$number = $this->getNumber($this->offset);
		} else {
			$number = "Invalid";
			$width= 155;
			$size = 12;
		}
		
		// choose one of four background images
		$bgNum = rand(1, 3);
		
		$image = imagecreatefromjpeg("backgrounds/$bgNum.jpg");
		
		$textColor = imagecolorallocate ($image, 0, 0, 0); 

		// write the code on the background image
		imagestring ($image, 5, 5, 8, $number, $textColor); 

		// Return the image and release the memory
		header("Content-type: image/jpeg", true);
		imagejpeg($image);
		imagedestroy($image);
	}

	/**
	 * Generates an offset
	 */
	function generateOffset()
	{
		for ($i = 0; $i < 4; $i++) {
			$this->offset .= sprintf("%d", rand(2,9));
		}

		$this->number = $this->getNumber($this->offset);
	}

	/**
	 * Gets the offset
	 */
	function getOffset()
	{
		if (empty($this->offset)) {
			$this->generateOffset();
		}

		return $this->offset;
	}

	/**
	 * Gets the alternative string
	 */
	function getAlt()
	{
		global $_ARRAYLANG;

		if (empty($this->offset)) {
			$this->generateOffset();
		}

		$number = $this->number[0]." ".$this->number[1]." " .$this->number[2]." ".$this->number[3];
		return preg_replace("%\{NUMBER\}%", $number, $_ARRAYLANG['TXT_ALT_STRING']);
	}

	/**
	 * Gets the URL
	 */
	function getUrl()
	{
		if (empty($this->offset)) {
			$this->generateOffset();
		}

		return "lib/spamprotection/captcha.php?offset=" . $this->offset;
	}

	/**
	 * Compares two values
	 */
	function compare($number, $offset)
	{
		$raw_offset = $this->getNumber($offset);

		if ($raw_offset == $number) {
			return true;
		} else {
			return false;
		}
	}

	/**
	 * Gets the captcha number
	 */
	function getNumber($offset)
	{
		$number = sprintf("%04d", intval($offset / 3 + 1571));
		/*
		 * Turn 1 into 2, because it's hard to differ between
		 * 1 and 7
		 */
		return preg_replace("%1%", "2", $number);
	}
}

?>
