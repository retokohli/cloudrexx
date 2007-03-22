<?php
/**
 * Database wrapper
 *
 * Will be removed in new versions!
 * @copyright   CONTREXX CMS - ASTALAVISTA IT AG
 * @author		Astalavista Development Team <thun@astalvista.ch>
 * @version		1.0.0
 * @package     contrexx
 * @subpackage  core
 * @todo        Edit PHP DocBlocks!
 */

/**
 * Database wrapper class
 *
 * Will be removed in new versions!
 * @copyright   CONTREXX CMS - ASTALAVISTA IT AG
 * @author		Astalavista Development Team <thun@astalvista.ch>
 * @access		public
 * @version		1.0.0
 * @package     contrexx
 * @subpackage  core
 */
class astalavistaDB extends DB_Sql {

   var $classname = "astalavistaDB";
   var $Host;
   var $Database;
   var $User;
   var $Password;



    /**
    * Constructor
    *
    * @global    array     Database Configuration
    */
   function astalavistaDB()
   {
      global $_DBCONFIG;
      $this->Host    	= $_DBCONFIG['host'];
      $this->Database 	= $_DBCONFIG['database'];
      $this->User     	= $_DBCONFIG['user'];
      $this->Password 	= $_DBCONFIG['password'];
      $this->debug      = false; // true for on, false for off
      $this->debug_type = 'now'; // 'now' for output to browser, 'log' for output to the admin log
      $this->script_uri = ASCMS_PROTOCOL.'://'.$_SERVER['SERVER_NAME'].dirname($_SERVER['SCRIPT_NAME'])."/";
      //$this->dbconfig = $_DBCONFIG;
   }


    /**
    * Return halt-message on error
    *
    * @param     string    $msg
    */
   function halt($msg)
   {
      if($this->debug_type==now)
	  {
         printf("<b>Database error: </b><PRE>%s</PRE><br>\n", $msg);
         printf("<b>MySQL error number</b>: %s (%s)<br><br>\n", $this->Errno, $this->Error);
         printf("Please send this error message to your System Administrator.<br>\n");
         printf("Error URL: <small>%s</small><br>\n", $this->script_uri);
         die("Session halted");
      }
	  else
	  {
         error(sprintf("Database error: MySQL error number (%s) Please send this error message to your System Administrator.", $this->Errno));
         printf("Error URL: %s\n", $this->script_uri);
	     die("Session halted");
      }
   }
}
?>