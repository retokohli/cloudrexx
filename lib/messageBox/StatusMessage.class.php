<?php

/**
 * StatusMessage
 *
 * @copyright   CONTREXX CMS - COMVATION AG
 * @author      COMVATION Development Team <info@comvation.com>
 * @package     contrexx
 * @subpackage  lib_framework
 */

/**
 * StatusMessage
 *
 * @copyright   CONTREXX CMS - COMVATION AG
 * @author      COMVATION Development Team <info@comvation.com>
 * @package     contrexx
 * @subpackage  lib_framework
 */
class StatusMessage {
	var $iconFile;
	var $width;
	var $height;
	var $mode;
	var $type;
	var $message;
	var $title;
	var $background;

    /**
     * Constructor
     */
    function __construct()
    {
    }


	// $type = error | ok | info
	function SetBox($message= '', $type = 'error') {
		switch ($type){
			case "info":
			$this->setIconFile("info.png");
			$this->setColorScheme("blue");
			break;
			case "ok":
			$this->setIconFile("ok.png");
			$this->setColorScheme("green");
			break;
            default:
            $this->setIconFile("error.png");
            $this->setColorScheme("red");
		}

		# The message itself
		$msg->setMsg($message);

		# Box title
		// $msg->setTitle("Authentication problems");
	}


	function setIconFile($icon) {
		$this->iconFile = $icon;
	}

	function setCSS($w) {
		$this->cssName = $w;
	}


	function setMsg($msg) {
		$this->message = $msg;
	}

	function setTitle($t) {
		$this->title = $t;
	}


	function generateCssStyle() {

		$css = "\n
		<style type=\"text/css\">
		.messageBox {
			margin: 3px 0px 3px 0px;
			padding: 5px;
			border: 1px dotted #ccc;
			background: #f6f9ff;
			color: #4d7097;
			font-weight: bold;
			font-family: Tahoma;
			font-size: 100%;
			}
		</style>\n";
	}


	function generateBox() {

		$html ="\n<div class=\"".$this->cssName."\">".$this->message."</div>\n";
		$result = $css . $html;
		return $result;
	}
}
