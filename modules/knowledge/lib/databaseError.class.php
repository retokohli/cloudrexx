<?php

/**
 * Contains database error class
 *
 * @author Stefan Heinemann <sh@comvation.com>
 * @copyright Comvation AG <info@comvation.com>
 */

/**
 * Database Error
 *
 * This class is thrown as a exception. Contains the
 * adodb error message and some kind of stacktrace that can be
 * return either plainly or formatted for the red alertbox.
 * @author Stefan Heinemann <sh@comvation.com>
 * @copyright Comvation AG <info@comvation.com>
 */
class DatabaseError extends Exception
{
    /**
     * Construct the Exception class
     *
     * @param string $message
     */
    public function __construct($message)
    {
        parent::__construct($message);
    }

    /**
     * Return a formated error message
     *
     * This message will be used within the red box
     * @global $objDatabase
     * @return string
     */
    public function formatted()
    {
        global $objDatabase;

        $txt_details = "Details";

        return "<a style=\"margin-left: 1em;\" href=\"javascript:void(0);\" onclick=\"showErrDetails(this);\">$txt_details&gt;&gt;</a>
        <div style=\"display:none;\" id=\"errDetails\">
        ".$this->getMessage()."<br />
        ".$objDatabase->ErrorMsg()."<br />
        ".$this->getTraceAsString()."
        </div>
        <script type=\"text/javascript\">
            /* <![CDATA[ */
                var showErrDetails = function(obj)
                {
                    var childs = obj.childNodes;
                    for (var i = 0; i < childs.length; ++i) {
                        obj.removeChild(childs[i]);
                    }
                    if ($('errDetails').visible()) {
                        $('errDetails').style.display = \"none\";
                        obj.appendChild(document.createTextNode(\"$txt_details >>\"));
                    } else {
                        $('errDetails').style.display = \"block\";
                        obj.appendChild(document.createTextNode(\"$txt_details <<\"));
                    }
                }
            /* ]]> */
        </script>";
    }

    /**
     * Return a plain error message
     *
     * Just return some error text. This is for example
     * for ajax requests
     * @global $objDatabase
     * @return string
     */
    public function plain()
    {
        global $objDatabase;

        return  $this->getMessage()."\n".
                strip_tags($objDatabase->ErrorMsg())."\n".
                $this->getTraceAsString();
    }

}

?>
