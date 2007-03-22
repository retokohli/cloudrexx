<?php
/**
 * Captcha (old version)
 * @copyright   CONTREXX CMS - ASTALAVISTA IT AG
 * @author      Astalavista Development Team <thun@astalvista.ch>
 * @access      public
 * @version     1.0.0
 * @package     contrexx
 * @subpackage  lib_spamprotection_old
 * @todo        Edit PHP DocBlocks!
 */

/**
 * Includes
 */
require_once "captcha.class.php";

$captcha = new Captcha(true);
$captcha->generateImage();
	
?>
