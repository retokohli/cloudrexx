<?php
/**
 * File Manager
 * @copyright   CONTREXX CMS - ASTALAVISTA IT AG
 * @author      Janik Tschanz <janik.tschanz@astalavista.net>
 * @version     $Id:  Exp $
 * @package     contrexx
 * @subpackage  lib_framework
 * @todo        Edit PHP DocBlocks!
 */

/**
 * File Manager
 *
 * LibClass to manage fils and folders
 * @copyright   CONTREXX CMS - ASTALAVISTA IT AG
 * @author      Janik Tschanz <janik.tschanz@astalavista.net>
 * @version     $Id:  Exp $
 * @package     contrexx
 * @subpackage  lib_framework
 */
class File
{
	var $conn_id;						   // current Connections ID
	var $login_result;					   // current Login

	var $ftp_is_activated;                 // FTP is activated ( true/false )
	var $ftpHost;                          // FTP Host
	var $ftpUserName;                      // FTP User Name
	var $ftpUserPass;                      // FTP Password
	var $ftpDirectory;                     // FTP start directory (htdocs)

	var $chmodFolder       = 0777;         // chmod for folder 0777
	var $chmodFile         = 0666;         // chmod for files  0644,0766
	var $chmodFolderPHP4	= '0777';
	var $chmodFilePHP4		= '0666';

	var $saveMode;						   // save_mode is true/false
	var $is_php5;



	function File(){
		global  $_FTPCONFIG;

		$this->ftp_is_activated = $_FTPCONFIG['is_activated'];
		$this->ftpHost = $_FTPCONFIG['host'];
		$this->ftpUserName = $_FTPCONFIG['username'];
		$this->ftpUserPass = $_FTPCONFIG['password'];
		$this->ftpDirectory = $_FTPCONFIG['path'];
		$this->saveMode = @ini_get('safe_mode');
		$this->is_php5 = (phpversion()<5) ? false : true;

		if($this->ftp_is_activated == true){
			//crate baseconnection
			$this->conn_id = @ftp_connect($this->ftpHost);

			//logon with user and password
			$this->login_result = @ftp_login($this->conn_id, $this->ftpUserName, $this->ftpUserPass);
		}

		$this->checkConnection();
	}



	function checkConnection(){
		//check connection
		if ((!$this->conn_id) || (!$this->login_result)) {
			$status = "FTP: disabled - ";
			$this->ftp_is_activated = false;
		} else {
			$status = "FTP: enabled - ";
		}

		if($this->saveMode == true){
			$status .= "SAVEMODE: on";
		}else{
			$status .= "SAVEMODE: off";
		}

		return $status;
	}



	function copyDir($orgPath, $orgWebPath, $orgDirName, $newPath, $newWebPath, $newDirName, $ignoreExists = false){
		$orgWebPath=$this->checkWebPath($orgWebPath);
		$newWebPath=$this->checkWebPath($newWebPath);

		if(file_exists($newPath.$newDirName) && !$ignoreExists){
			$newDirName = $newDirName.'_'.time();
		}

		$status = $this->mkDir($newPath, $newWebPath, $newDirName);

		if($status!= "error"){
			$openDir=@opendir($orgPath.$orgDirName);
			while ($file=@readdir($openDir)) {
				if($file!="." && $file!="..") {
					if(!is_dir($orgPath.$orgDirName."/".$file)) {
							$this->copyFile($orgPath, $orgDirName."/".$file, $newPath, $newDirName."/".$file);
					} else {
						$this->copyDir($orgPath, $orgWebPath, $orgDirName."/".$file, $newPath, $newWebPath, $newDirName."/".$file);
					}
				}
			}
		    closedir($openDir);
		}
	    return $status;
	}



	function copyFile($orgPath, $orgFileName, $newPath, $newFileName, $ignoreExists = false){
		if(file_exists($newPath.$newFileName)){
			$info   = pathinfo($newFileName);
	        $exte   = $info['extension'];
	        $exte   = (!empty($exte)) ? '.' . $exte : '';
	        $part   = substr($newFileName, 0, strlen($newFileName) - strlen($exte));
	        if(!$ignoreExists){
		        $newFileName  = $part . '_' . (time()) . $exte;
	        }
		}
		if(!copy($orgPath.$orgFileName, $newPath.$newFileName)){
			$status = "error";
		}else{
			$this->setChmod($newPath, str_replace(ASCMS_PATH,'', $newPath), $newFileName);
			$status = $newFileName;
		}

		return $status;
	}



	function mkDir($path, $webPath, $dirName){
		$webPath=$this->checkWebPath($webPath);

		if(file_exists($path.$dirName)){
			$dirName = $dirName.'_'.time();
		}

		$newDir = $this->ftpDirectory.$webPath.$dirName;

		if($this->ftp_is_activated == true){
			ftp_mkdir($this->conn_id, $newDir);

			if($this->is_php5 == false) {
				$chmod_cmd="CHMOD 0777 ".$newDir;
				$chmod=@ftp_site($this->conn_id, $chmod_cmd);
			} else {
				ftp_chmod($this->conn_id, $this->chmodFolder, $newDir);
			}
			$status = $dirName;
		}else{
			if(@mkdir($path.$dirName)){
				@chmod ($path.$dirName, $this->chmodFolder);
				$status = $dirName;
			}else{
				$status = "error";
			}
		}
		return $status;
	}




	function delDir($path, $webPath, $dirName){
		$webPath=$this->checkWebPath($webPath);
		$status = "";
		$openDir=@opendir($path.$dirName);
		while ($file=@readdir($openDir)) {
			if($file!="." && $file!="..") {
				if($this->ftp_is_activated == true){
					if(!is_dir($path.$dirName."/".$file)) {
	   					@ftp_delete($this->conn_id, $this->ftpDirectory.$webPath.$dirName."/".$file);
					} else {
	   					$this->delDir($path, $webPath, $dirName."/".$file);
					}
				}else{
					if(!is_dir($path.$dirName."/".$file)) {
	   					$this->delFile($path, $webPath, $file);
					} else {
	   					$this->delDir($path, $webPath, $dirName."/".$file);
					}
				}
			}
		}
	    closedir($openDir);

	    if($this->ftp_is_activated == true){
		    if(!@ftp_rmdir($this->conn_id,  $this->ftpDirectory.$webPath.$dirName)){
				$status = "error";
			}
	    }else{
	    	if(!@rmdir($path.$dirName."/".$file)){
				$status = "error";
			}
	    }

	    return $status;
	}



	function delFile($path, $webPath, $fileName){
		$webPath=$this->checkWebPath($webPath);

		$delFile = $this->ftpDirectory.$webPath.$fileName;
		if($this->ftp_is_activated == true){
			if(@ftp_delete($this->conn_id, $delFile)){
				$status = $delFile;
			}else{
				$status = "error";
			}
		}else{
			//@unlink($path.$fileName);
			$delete = @unlink($path.$fileName);
			clearstatcache();
			if (@file_exists($path.$fileName)) {
			  $filesys = eregi_replace("/","\\",$path.$fileName);
			  $delete = @system("del $filesys");
			  clearstatcache();

			  // don't work in safemode
			  if (@file_exists($path.$fileName)) {
			     $delete = @chmod ($path.$fileName, 0775);
			     $delete = @unlink($path.$fileName);
			     $delete = @system("del $filesys");
			  }
			}
			clearstatcache();
			if (@file_exists($path.$fileName)){
			  	$status = "error";
			}else{
			  	$status = $fileName;
			}
		}

		return $status;
	}



	function checkWebPath($webPath){
		if($this->ftpDirectory ==""){
			if(substr($webPath, 0, 1)=="/"){
				$webPath = substr($webPath, 1);
			}else{
				$webPath = $webPath;
			}
		}else{
			$webPath = $webPath;
		}

		return $webPath;
	}



	// replaces some characters
    function replaceCharacters($string){
        // replace $change with ''
        $change = array('+', '¦', '"', '@', '*', '#', '°', '%', '§', '&', '¬', '/', '|', '(', '¢', ')', '=', '?', '\'', '´', '`', '^', '~', '!', '¨', '[', ']', '{', '}', '£', '$', '-', '<', '>', '\\', ';', ',', ':');
        // replace $signs1 with $signs
        $signs1 = array(' ', 'ä', 'ö', 'ü', 'ç');
        $signs2 = array('_', 'ae', 'oe', 'ue', 'c');

        $string = strtolower($string);
        foreach($change as $str){
		    $string = str_replace($str, '', $string);
        }
        for($x = 0; $x < count($signs1); $x++){
            $string = str_replace($signs1[$x], $signs2[$x], $string);
        }
        $string = str_replace('__', '_', $string);

        if(strlen($string) > 40){
            $info       = pathinfo($string);
            $stringExt  = $info['extension'];

            $stringName = substr($string, 0, strlen($string) - (strlen($stringExt) + 1));
            $stringName = substr($stringName, 0, 40 - (strlen($stringExt) + 1));
            $string     = $stringName . '.' . $stringExt;
        }

        return $string;
    }




    function renameFile($path, $webPath, $oldFileName, $newFileName){
		$webPath=$this->checkWebPath($webPath);

		if($oldFileName != $newFileName){
			if(file_exists($path.$newFileName)){
				$info   = pathinfo($newFileName);
		        $exte   = $info['extension'];
		        $exte   = (!empty($exte)) ? '.' . $exte : '';
		        $part   = substr($newFileName, 0, strlen($newFileName) - strlen($exte));
		        $newFileName  = $part . '_' . (time()) . $exte;
			}

			if($this->ftp_is_activated == true){
			    if(!ftp_rename($this->conn_id, $this->ftpDirectory.$webPath.$oldFileName, $this->ftpDirectory.$webPath.$newFileName)){
					$status = "error";
				}else{
				  	$status = $newFileName;
				}
		    }else{
		    	if(!rename($path.$oldFileName, $path.$newFileName)){
					$status = "error";
		    	}else{
				  	$status = $newFileName;
				}
		    }
		}else{
			$status = $oldFileName;
		}

		return $status;
	}



	function renameDir($path, $webPath, $oldDirName, $newDirName){
		$webPath=$this->checkWebPath($webPath);

		if($oldDirName != $newDirName){
			if(file_exists($path.$newDirName)){
				$newDirName = $newDirName;
			}

			if($this->ftp_is_activated == true){
			    if(!ftp_rename($this->conn_id, $this->ftpDirectory.$webPath.$oldDirName, $this->ftpDirectory.$webPath.$newDirName)){
					$status = "error";
				}else{
				  	$status = $newDirName;
				}
		    }else{
		    	if(!rename($path.$oldDirName, $path.$newDirName)){
					$status = "error";
				}else{
				  	$status = $newDirName;
				}
		    }
		}else{
			$status = $oldDirName;
		}

		return $status;
	}



	function setChmod($path, $webPath, $fileName)
	{
		global $_FTPCONFIG;

		if (file_exists($path.$fileName)) {
			if (is_dir($path.$fileName)) {
				if (@chmod($path.$fileName, $this->chmodFolder)) {
					return true;
				} elseif ($this->ftp_is_activated == true) {
					if ($this->is_php5 == false) {
						@chown($path.$fileName, $_FTPCONFIG['username']);
						@chmod($path.$fileName, $this->chmodFolder);
						$chmod_cmd="CHMOD ".$this->chmodFolderPHP4." ".$this->ftpDirectory.$webPath.$fileName;
						return @ftp_site($this->conn_id, $chmod_cmd);
					} else {
						return @ftp_chmod($this->conn_id, $this->chmodFolder, $this->ftpDirectory.$webPath.$fileName);
					}
				}
				return false;
			} else {
				if (@chmod($path.$fileName, $this->chmodFile)) {
					return true;
				} elseif ($this->ftp_is_activated == true) {
					if ($this->is_php5 == false) {
						@chown($path.$fileName, $_FTPCONFIG['username']);
						@chmod($path.$fileName, $this->chmodFile);
						$chmod_cmd="CHMOD ".$this->chmodFilePHP4." ".$this->ftpDirectory.$webPath.$fileName;
						return @ftp_site($this->conn_id, $chmod_cmd);
					} else {
						return @ftp_chmod($this->conn_id, $this->chmodFile, $this->ftpDirectory.$webPath.$fileName);
					}
				}
				return false;
			}
		}
        return false;
	}


	function uploadFile($path, $webPath, $fileName, $sourceFile){
		// upload the file
		echo $fileName;
		echo $sourceFile;

		$upload = ftp_put($this->conn_id, "test.jpg", $sourceFile, FTP_BINARY);

		// check upload status
		if (!$upload) {
			echo "FTP upload has failed!";
		} else {
			echo "Uploaded $sourceFile!";
		}
	}
}
?>