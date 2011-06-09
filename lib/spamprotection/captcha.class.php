<?PHP
/**
 * Captcha
 * @copyright   CONTREXX CMS - COMVATION AG
 * @author      Thomas Kaelin <thomas.kaelin@astalvista.ch>
 * @access      public
 * @version     1.2.0
 * @package     contrexx
 * @subpackage  lib_spamprotection
 */
class Captcha {
    var $boolFreetypeInstalled = false;
    var $boolGDInstalled = false;
    
    var $strRandomString;
    var $strSalt;
    
    var $strFontDir;
    var $strBackgroundDir;
    var $strAbsolutePath;
    var $strWebPath;
    
    var $intRandomLength = 5;
    var $intSaltLength = 20;
    var $intMaximumCharacters = 20;
    
    var $intImageWidth = 120;
    var $intNumberOfBackgrounds = 7;

    var $image = null; //the GD image
    
    /**
    * Constructor: Initializes base-state of object
    *
    */
    function __construct() {
        global $sessionObj;

        if (!isset($sessionObj)) $sessionObj = new cmsSession();

        srand ((double)microtime()*1000000);
                
        $this->strRandomString     = $this->createRandomString();
        $this->strSalt             = $this->createRandomString($this->intSaltLength);
        
        $this->strFontDir         = ASCMS_DOCUMENT_ROOT.'/lib/spamprotection/fonts/';
        $this->strBackgroundDir = ASCMS_DOCUMENT_ROOT.'/lib/spamprotection/backgrounds/';
        $this->strAbsolutePath     = ASCMS_DOCUMENT_ROOT.'/images/spamprotection/';
        $this->strWebPath         = ASCMS_PATH_OFFSET.'/images/spamprotection/';
  
        $this->isGDInstalled();
        $this->isFreetypeInstalled();
    }

    /**
     * Determines whether the GD is installed
     */
    function isGdInstalled() {
        $arrExtensions = get_loaded_extensions();
        
        if (in_array('gd', $arrExtensions)) {
            $this->boolGDInstalled = true;
        }
    }
    
    /**
     * Figures out if the Freetype-Extension (part of GD) is installed.
     */
    function isFreetypeInstalled() {
        $arrExtensions = get_loaded_extensions();
        
        if (in_array('gd', $arrExtensions)) {
            $arrGdFunctions = get_extension_funcs('gd');       
                
            if (in_array('imagettftext', $arrGdFunctions)) {
                $this->boolFreetypeInstalled = true;
            }
        }
    }
   
    /**
     * Creates an random string with $intDigits digits.
     *
     * @param    integer        $intDigits: How many digits should the created string have?
     * @return    string        A new random string
     */
    function createRandomString($intDigits=0) {
        if ($intDigits > $this->intMaximumCharacters || $intDigits == 0) {
            $intDigits = $this->intRandomLength;
        }
        
        $strReturn = '';            
        for ($i=1; $i <= $intDigits; ++$i) {
            switch (rand(0,1)) {
                case 0:
                    $char = chr(rand(65,90));
                    while($char == 'O') //no O's pleace
                        $char = chr(rand(65,90));                    
                    $strReturn .= $char;
                    break;
                case 1:
                    $strReturn .= sprintf("%d", rand(2,9));
                    break;
                default:
            }
        }
        
        return  $strReturn;
    }
     
    /**
     * Creates a captcha image.
     *
     */
    function createImage() {
        $intWidth             = $this->intImageWidth;
        $intHeight             = $intWidth / 3;
        $intFontSize         = floor($intWidth / strlen($this->strRandomString)) - 2;
        $intAngel             = 15;
        $intVerticalMove     = floor($intHeight/7);
        
        $image = imagecreatetruecolor($intWidth, $intHeight);
        
        $arrFontColors     = array(imagecolorallocate($image, 0, 0, 0),        //black
                                imagecolorallocate($image, 255, 0, 0),         //red
                                imagecolorallocate($image, 0, 180, 0),         //darkgreen
                                imagecolorallocate($image, 0, 105, 172),    //blue
                                imagecolorallocate($image, 145, 19, 120)    //purple
                            );
                                                        
        $arrFonts    = array(    $this->strFontDir.'coprgtb.ttf',
                                $this->strFontDir.'ltypeb.ttf',
                        );
                        
        //Draw background
        $imagebg = imagecreatefromjpeg($this->strBackgroundDir.rand(1, $this->intNumberOfBackgrounds).'.jpg');
        imagesettile($image, $imagebg);
        imagefilledrectangle($image, 0, 0, $intWidth, $intHeight, IMG_COLOR_TILED);
        
        //Draw string
        for ($i = 0; $i < strlen($this->strRandomString); ++$i) {
            $intColor     = rand(0, count($arrFontColors)-1);
            $intFont    = rand(0, count($arrFonts)-1);
            $intAngel     = rand(-$intAngel, $intAngel);
            $intYMove     = rand(-$intVerticalMove, $intVerticalMove);
            
            if ($this->boolFreetypeInstalled) {
                imagettftext(    $image, 
                                $intFontSize, 
                                $intAngel, 
                                (6+$intFontSize*$i), 
                                ($intHeight/2+$intFontSize/2+$intYMove), 
                                $arrFontColors[$intColor], 
                                $arrFonts[$intFont], 
                                substr($this->strRandomString,$i,1)
                            );
            } else {
                imagestring($image, 
                            5, 
                            (6+25*$i),
                            12+$intYMove,
                            substr($this->strRandomString,$i,1),
                            $arrFontColors[$intColor]
                        );
            }
        }

        //save the image for further processing
        $this->image = $image;
    }

    /**
     * Creates a new image and sends it to the Browser
     */
    function printNewImage() {
        //create a new image...
        $this->createImage();

        //...write the new secret to the session...
        $this->updateSession();

        //...and print it.
        header('Content-type: image/jpeg');
        imagejpeg($this->image, NULL, 90);
        imagedestroy($this->image);
    }

    /**
     * writes the secret to the session
     */
    function updateSession() {
        $_SESSION['captchaSecret'] = $this->strRandomString;
    }
    
    /**
     * Because of security-problems: returns just an "spamprotection" string.
     *
     * @return    string        Alt tag for the image
     */
    function getAlt() {        
       return 'Spamprotection';
    }
   
    /**
     * checks whether the entered string matches the captcha
     *
     * @return boolean
     */
    function check($strEnteredString) {
        // in case there was a session initialization problem, $_SESSION['captchaSecret'] might be NULL
        if (empty($strEnteredString)) return false;

        $valid = strtoupper($strEnteredString) == strtoupper($_SESSION['captchaSecret']);
        unset($_SESSION['captchaSecret']); //remove secret to improve security
        return $valid;
    }       

    /**
     * gets the url for a new captcha
     *
     * @return string
     */
    function getUrl() {
        global $objInit;
        $isBackend = $objInit->mode == "backend";
        $url = ASCMS_PATH_OFFSET;
        if($isBackend) {
            $url .= ASCMS_BACKEND_PATH.'/index.php?cmd=captcha&act=new';
        }
        else {
            $url .= '/index.php?section=captcha&cmd=new';
        }
        
        //add no cache param
        $url .= '&nc='.md5(''.time());
        return $url;
    }
}
?>
