<?php

/**
 * Backup
 *
 * Functions to create and restore backups of the contrexx cms
 *
 * @copyright    CONTREXX CMS - COMVATION AG
 * @author        Comvation Development Team <info@comvation.com>
 * @package     contrexx
 * @subpackage  core
 * @version        1.0.0
 * @todo        Make this an actual class (as the file name suggests)
 * @todo        Document Backup structure, management, schedule(?)
 */

//Security-Check
if (eregi("backup.class.php",$_SERVER['PHP_SELF'])) {
    Header("Location: index.php");
    die();
}


/**
 * Create a database backup
 *
 * There are three mutually exclusive backup sets, controlled by the
 * content of the $_POST array:
 * - If the element 'backup_default' is set, the tables to be backed up are
 *   determined by the list of table names listed in the string
 *   'default_tables'. The list entries are separated by semicolons (';').
 * - If the element 'backup_shop' is set, the tables to be backed up are
 *   determined by the list of table names listed in the string
 *   'shop_tables'. The list entries are separated by semicolons (';').
 * - If none of the above applies, the tables to be backed up are
 *   determined by the list of table names listed in the array 'tables'.
 * The data is dumped as a SQL text file named '$timestamp.sql',
 * where $timestamp represents the current date/time as returned by
 * the {@link mktime()} function with no arguments.
 * A new row is added to the 'backups' table, containing the ID, date,
 * description, tables, and backup file size.
 * Also see {@link backup_getDBTables()}, and {@link backup_getDBContent()}.
 * @global    mixed      Database
 * @global    array      Core language
 * @return    string     Result string
 */
function backup_create()
{
    global $objDatabase,$_CORELANG;

    $timestamp=mktime();
    //$cur_time=date("YmdHis",$timestamp);
    $timestampReadable=date(ASCMS_DATE_FORMAT, $timestamp);


    $newfile="# Contrexx CMS database backup file created on ".$timestampReadable."\r\n";
    $description=$_POST['description'];

    if (isset($_POST["backup_default"])) {
        $table_array=explode(";",$_POST["default_tables"]);
        if (empty($_POST['description'])) {
            $description=$_CORELANG['TXT_CONTENT_BACKUP'];
        }
    } elseif (isset($_POST["backup_shop"])) {
        $table_array=explode(";",$_POST["shop_tables"]);
        if (empty($_POST['description'])) {
            $description=$_CORELANG['TXT_SHOP_BACKUP'];
        }
    } else {
        $table_array=$_POST["tables"];
    }

    if (!empty($table_array)) {
        $implode_tables=implode(";",$table_array);
        foreach ($table_array as $table_name) {
            $table_name = get_magic_quotes_gpc() ? $table_name : addslashes($table_name);
            $newfile.= "\n# ----------------------------------------------------------\n#\n";
            $newfile.= "# structur for table '$table_name'\n#\n";
            $newfile.= backup_getDBTables($table_name);
            $newfile.= "\n\n";
            $newfile.= "#\n# data for table '$table_name'\n#\n";
            $newfile.= backup_getDBContent($table_name);
            $newfile.= "\n\n";
        }

        $dumbfilename=$timestamp.".sql";
        $fp = fopen(ASCMS_BACKUP_PATH.'/'.$dumbfilename, "w");
        if (!$fp) die ("Could not create the database backup file.\n (Check the file permission chmod 777)");
        fwrite($fp, $newfile);
        fclose($fp);

        $filesize = filesize (ASCMS_BACKUP_PATH. '/' .$dumbfilename);

        $query = "INSERT INTO ".DBPREFIX."backups
                          SET date='".$timestamp."',
                              description=".$objDatabase->qstr($description, get_magic_quotes_gpc()).",
                              usedtables=".$objDatabase->qstr($implode_tables, get_magic_quotes_gpc()).",
                              size=$filesize";

        if ($objDatabase->Execute($query)) {
            return $_CORELANG['TXT_DATA_RECORD_ADDED_SUCCESSFUL'];
        } else {
            return $_CORELANG['TXT_DATABASE_QUERY_ERROR'];
        }
    } else {
        return $_CORELANG['TXT_FILL_OUT_ALL_REQUIRED_FIELDS'];
    }
}


/**
 * Restore database backup specified by its backup ID.
 *
 * The ID is provided by the 'backupid' element in the $_REQUEST array.
 * If there is a corresponding entry in the 'backups' table, the respective
 * backup is restored into the database from the dump file.
 * @see     backup_create()
 * @global  mixed   Database
 * @global  array   Core languange
 * @return  string  Result string
 */
function backup_restore()
{
    global $objDatabase,$_CORELANG;

    $id=intval($_REQUEST["backupid"]);
    if (empty($id)) return $_CORELANG['TXT_DATABASE_QUERY_ERROR'];

    $objResult = $objDatabase->Execute("SELECT date FROM ".DBPREFIX."backups WHERE id=".$id);
    if ($objResult !== false) {
        $filename = $objResult->fields['date'].".sql";
        ob_start();
        @ob_implicit_flush(0);
        readfile(ASCMS_BACKUP_PATH.'/'.$filename);
        $contents = ob_get_contents();
        ob_end_clean();

        $error_log = array();
        if (!empty($contents)) {
            $contents = preg_replace("/\r/s", "\n", $contents);
            $contents = preg_replace("/[\n]{2,}/s", "\n", $contents);
            $lines = explode("\n", $contents);
            $in_query = 0;
            $i = 0;
            foreach ($lines as $line) {
                $line = trim($line);
                if (!$in_query) {
                    if (preg_match('/^CREATE/i', $line)) {
                        $in_query = 1;
                        $split_file[$i] = $line;
                    } elseif (!empty($line) && $line[0] != '#') {
                        $split_file[$i] = preg_replace('/;$/i', '', $line);
                        $i++;
                    }
                } elseif ($in_query) {
                    if (preg_match('/^[\)]/', $line)) {
                        $in_query = 0;
                        $split_file[$i] .= preg_replace('/;$/i', '', $line);
                        $i++;
                    } elseif (!empty($line) && $line[0] != '#') {
                        $split_file[$i] .= $line;
                    }
                }
            }
            foreach ($split_file as $sql) {
                $sql = trim($sql);
                if (!empty($sql) && $sql[0]!= "#") {
                    @set_time_limit(1200);
                    if ($objDatabase->Execute($sql) === false) {
                        $error_log[]= $sql;
                    }
                }
            }
        }
        if (!empty($error_log)) {
            $msg = $_CORELANG['TXT_DATABASE_QUERY_ERROR'];
            $msg .= "<ol>";
            foreach ($error_log as $val) {
                $msg.= sprintf("<li>%s</li>", $val);
            }
            $msg .= "</ol>";
        } else {
            $msg = $_CORELANG['TXT_DATA_RECORD_RESTORE_SUCCESSFUL'];
        }
    }
    return $msg;
}


/**
 * Delete a database backup specified by its backup ID.
 * The ID is provided by the 'backupid' element in the $_REQUEST array.
 * If there is a corresponding entry in the 'backups' table, the respective
 * backup file, along with the table entry, is deleted.
 * @see     backup_create()
 * @global  mixed   Database
 * @global  array   Core language
 * @return  string  Result string
 */
function backup_delete()
{
    global $objDatabase,$_CORELANG;

    $id = intval($_REQUEST["backupid"]);
    if (empty($id)) return $_CORELANG['TXT_DATABASE_QUERY_ERROR'];

    $objResult = $objDatabase->Execute("SELECT date FROM ".DBPREFIX."backups WHERE id=".$id);
    if (!$objResult) {
        return false;
    }
    $filename = $objResult->fields['date'].".sql";
    if (file_exists(ASCMS_BACKUP_PATH.'/'.$filename)) {
        if (@unlink(ASCMS_BACKUP_PATH.'/'.$filename)) {
            if ($objDatabase->Execute("DELETE FROM ".DBPREFIX."backups WHERE id=".$id) !== false) {
                return $_CORELANG['TXT_DATA_RECORD_DELETED_SUCCESSFUL'];
            }
        }
    } else {
        if ($objDatabase->Execute("DELETE FROM ".DBPREFIX."backups WHERE id=".$id) !== false) {
           return $_CORELANG['TXT_DATA_RECORD_DELETED_SUCCESSFUL'];
        }
    }
    return $_CORELANG['TXT_DATABASE_QUERY_ERROR'];
}


/**
 * View single backup table entry specified by its backup ID.
 *
 * The ID is provided by the 'backupid' element in the $_REQUEST array.
 * If there is a corresponding entry in the 'backups' table, the content
 * of the respective backup file is returned. If either the 'backupid' is
 * invalid, or the entry cannot be found in the 'backups' table, or the
 * backup file cannot be read, an empty string is returned.
 * @see     backup_create()
 * @version 1.0     initial version
 * @global  mixed   Database
 * @return  string  Contents
 * @todo    Improve error handling, i.e.: What if the backup file cannot
 *          be opened?
 */
function backup_view()
{
    global $objDatabase;

    $id = intval($_REQUEST['backupid']);
    if (empty($id)) return '';
    $objResult = $objDatabase->Execute("SELECT date FROM ".DBPREFIX."backups WHERE id=".$id);
    if (!$objResult) {
        return '';
    }
    $filename = $objResult->fields['date'].'.sql';
    ob_start();
    @ob_implicit_flush(0);
    readfile(ASCMS_BACKUP_PATH.'/'.$filename);
    $contents = ob_get_contents();
    ob_end_clean();
    return '<pre>'.preg_replace('/\{([A-Z0-9_]*?)\}/','[[\\1]]',htmlspecialchars($contents, ENT_QUOTES, CONTREXX_CHARSET)).'</pre>';
}


/**
 * View a list of tables contained in a backup specified by its backup ID.
 *
 * The ID is provided by the 'backupid' element in the $_REQUEST array.
 * If there is a corresponding entry in the 'backups' table, the content
 * of the 'usedtables' field is returned. If either the 'backupid' is
 * invalid, or the entry cannot be found in the 'backups' table, an empty
 * string is returned.
 * @version 1.0     initial version
 * @global  mixed   Database
 * @global  array   Core language
 * @return  string  output
 * @todo    Improve error handling, i.e.: What if the backup record does not
 *          exist?
 */
function backup_viewTables()
{
    global $objDatabase,$_CORELANG;

    $id = intval($_REQUEST['backupid']);
    if (empty($id)) {
        return '';
    }
    $objResult = $objDatabase->Execute("SELECT usedtables FROM ".DBPREFIX."backups WHERE id=".$id);
    if (!$objResult) {
        return '';
    }
    $tables = explode(';',$objResult->fields['usedtables']);
    $output = $_CORELANG['TXT_TABLES'].':<br>';
    foreach ($tables as $value) {
        $output .= '<br>'.$value;
    }
    return $output;
}


/**
 * Download a backup file specified by its backup ID.
 *
 * The ID is provided by the 'backupid' element in the $_REQUEST array.
 * If there is a corresponding entry in the 'backups' table, the respective
 * file is printed. If either the 'backupid' is invalid, or the entry cannot
 * be found in the 'backups' table, an empty string is returned.
 * @version 1.0     initial version
 * @global  mixed   Database
 * @global  array   Core language
 * @todo    Improve error handling, i.e.: What if the backup record does not
 *          exist?
 * @return  bool/void   exits on success, returns false otherwise
 */
function backup_download()
{
    global $objDatabase;

    $id = intval($_REQUEST['backupid']);
    if (empty($id)) {
        return false;
    }
    $objResult = $objDatabase->Execute("SELECT date FROM ".DBPREFIX."backups WHERE id=".$id);
    if (!$objResult) {
        return false;
    }
    $filename = $objResult->fields['date'].".sql";
    $size = @filesize(ASCMS_BACKUP_PATH.'/'.$filename);
    header("Content-type: application/x-unknown");
    header("Content-length: $size\n");
    header("Content-Disposition: attachment; filename=$filename\n");
    readfile(ASCMS_BACKUP_PATH.'/'.$filename);
    exit;
}


/**
 * Show a list of present backups
 *
 * @todo Document: What exactly does it do, and how?
 * @global    mixed      Template
 * @global    array      Core language
 * @global    mixed      Database
 * @global    mixed      Database Configuration
 * @return    void
 */
function backup_showList()
{
    global $objTemplate, $_CORELANG, $objDatabase, $_DBCONFIG;

    $database_size_prefix = 0;
    $database_number_prefix = 0;
    $tables = '';
    $table_array = $objDatabase->MetaTables('TABLES');
    $defaulttables = array();
    $shoptables = array();

    foreach ($table_array as $table) {
        if (    preg_match('/\b'.DBPREFIX.'/i', $table)
            && !preg_match('/\b'.DBPREFIX.'backups\b/i', $table)) {
            $tables .= '<option value="'.$table.'">'.$table.'</option>';
        }
        if (   preg_match('/'.DBPREFIX.'content_navigation\b/i', $table)
            || preg_match('/'.DBPREFIX.'content\b/i', $table)) {
            $defaulttables[] = $table;
        } elseif (preg_match('/'.DBPREFIX.'module_shop/', $table)) {
            $shoptables[] = $table;
        }
    }

    $objTemplate->addBlockfile('ADMIN_CONTENT', 'backup', "backup.html");
    $objTemplate->setVariable(array(
        'TXT_BACKUP_LIST'            => $_CORELANG['TXT_BACKUP_LIST'],
        'TXT_DATE'                   => $_CORELANG['TXT_DATE'],
        'TXT_NAME'                   => $_CORELANG['TXT_NAME'],
        'TXT_DESCRIPTION'            => $_CORELANG['TXT_DESCRIPTION'],
        'TXT_TABLES'                 => $_CORELANG['TXT_TABLES'],
        'TXT_SIZE'                   => $_CORELANG['TXT_SIZE'],
        'TXT_ACTION'                 => $_CORELANG['TXT_ACTION'],
        'TXT_BACKUP_CREATE'          => $_CORELANG['TXT_BACKUP_CREATE'],
        'TXT_BACKUP_SUBMIT'          => $_CORELANG['TXT_BACKUP_SUBMIT'],
        'TXT_SELECT_ALL'             => $_CORELANG['TXT_SELECT_ALL'],
        'TXT_DESELECT_ALL'           => $_CORELANG['TXT_DESELECT_ALL'],
        'TXT_CONFIRM_RESTORE_DATA'   => $_CORELANG['TXT_CONFIRM_RESTORE_DATA'],
        'TXT_CONFIRM_DELETE_DATA'    => $_CORELANG['TXT_CONFIRM_DELETE_DATA'],
        'TXT_ACTION_IS_IRREVERSIBLE' => $_CORELANG['TXT_ACTION_IS_IRREVERSIBLE'],
        'TXT_DATABASE'               => $_CORELANG['TXT_DATABASE'],
        'TXT_DEFAULT_BACKUP'         => $_CORELANG['TXT_CONTENT_BACKUP'],
        'TXT_RESTORE'                => $_CORELANG['TXT_RESTORE'],
        'TXT_DOWNLOAD'               => $_CORELANG['TXT_DOWNLOAD'],
        'TXT_DELETE'                 => $_CORELANG['TXT_DELETE'],
        'TXT_VIEW'                   => $_CORELANG['TXT_VIEW'],
        'TXT_SHOP_BACKUP'            => $_CORELANG['TXT_SHOP_BACKUP']
    ));

    $objResult = $objDatabase->Execute("
        SELECT id, date, description, usedtables, size
          FROM ".DBPREFIX."backups
         ORDER BY date DESC
    ");
    $j = 0;
    if ($objResult) {
        while (!$objResult->EOF) {
            $description = ($objResult->fields['description']=='') ? '-' : stripslashes($objResult->fields['description']);
            $filesize = size_format($objResult->fields['size']);
            $tablecount = count(explode(';',$objResult->fields['usedtables']));
            $objTemplate->setVariable(array(
                'BACKUP_ID'          => $objResult->fields['id'],
                'BACKUP_DATE'        => date(ASCMS_DATE_FORMAT , $objResult->fields['date']),
                'BACKUP_DESCRIPTION' => $description,
                'BACKUP_TABLES'      => $tablecount,
                'BACKUP_SIZE'        => $filesize,
                'BACKUP_ROWCLASS'    => (++$j % 2 ? 'row2' : 'row1'),
            ));
            $objTemplate->parse('backupRow');
            $objResult->MoveNext();
        }
    }
    if ($j == 0) {
        $objTemplate->hideBlock('backupRow');
    }
    if ($_DBCONFIG['dbType'] == 'mysql') {
        $objResult = $objDatabase->Execute("SHOW TABLE STATUS");
        if ($objResult !== false) {
            $type = isset($objResult->fields['Type']) ? 'Type' : 'Engine';
            while (!$objResult->EOF) {
                if (eregi('^(MyISAM|ISAM|HEAP|InnoDB)$', $objResult->fields[$type])) {
                    if (   preg_match('/\b'.DBPREFIX.'/', $objResult->fields['Name'])
                        && !preg_match('/\b'.DBPREFIX.'backups\b/i', $objResult->fields['Name'])) {
                        $database_size_prefix+= $objResult->fields['Data_length']+$objResult->fields['Index_length'];
                        $database_number_prefix++;
                    }
                }
                $objResult->MoveNext();
            }
        }
    }

    $objTemplate->setVariable(array(
        'DB_NAME'              => $_DBCONFIG['database'],
        'CMS_SIZE'             => size_format($database_size_prefix)."( $database_number_prefix ".$_CORELANG['TXT_TABLES'].")",
        'BACKUP_TABLE_OPTIONS' => $tables,
        'DEFAULT_TABLES'       => implode(";",$defaulttables),
        'SHOP_TABLES'          => implode(";",$shoptables),
    ));
}


/**
 * Extract table information for the given table from the database.
 *
 * Collects information about the given database table and returns
 * an SQL string. This string can in turn be used to drop and recreate
 * an empty instance of that table.
 * @see     backup_create()
 * @see     backup_restore()
 * @param   string  Table name
 * @global  mixed   Database
 * @global  mixed   Database Configuration
 * @return  string  SQL string
 */
function backup_getDBTables($table)
{
    global $objDatabase,$_DBCONFIG;

    $alltablesstructure = '';
    $fieldnames     = array();
    $structurelines = array();
    $tablekeys    = array();
    $uniquekeys   = array();
    $fulltextkeys = array();

//    block to get the fields informations from the table with the new database
//    $objResult = $objDatabase->MetaColumns($table);
//    if ($objResult !== false) {
//        print_r($objResult);
//        foreach ($objResult as $objColumn) {
//            $structureline = $objColumn->name;
//            $structureline.= ' '.$objColumn->type;
//            $structureline.= ' '.($objColumn->not_null ? 'NOT ' : '').'NULL';
//
//            if ($objColumn->has_default) {
//                $structureline.= ' default \''.$objColumn->default_value.'\'';
//            }
//            $structureline .= ($objColumn->auto_increment ? ' auto_increment' : '');
//            $structurelines[] = $structureline;
//
//            $fieldnames[] = $objColumn->name;
//        }
//    }


    $objResult = $objDatabase->Execute("SHOW FIELDS FROM $table");
    if ($objResult !== false) {
        while (!$objResult->EOF) {
            $structureline = '`'.$objResult->fields['Field'].'`';
            $structureline.= ' '.$objResult->fields['Type'];
            $structureline.= ' '.($objResult->fields['Null'] ? '' : 'NOT ').'NULL';
            $default = $objResult->fields['Default'];
            if (isset($default)) {
                switch ($objResult->fields['Type']) {
                    case 'tinytext':
                    case 'tinyblob':
                    case 'text':
                    case 'blob':
                    case 'mediumtext':
                    case 'mediumblob':
                    case 'longtext':
                    case 'longblob':
                        // no default values
                        break;
                    default:
                        $structureline.= ' default \''.$objResult->fields['Default'].'\'';
                        break;
                }
            }
            $structureline .= ($objResult->fields['Extra'] ? ' '.$objResult->fields['Extra'] : '');
            $structurelines[] = $structureline;

            $fieldnames[] = $objResult->fields['Field'];
            $objResult->MoveNext();
        }
    }

    $objResult = $objDatabase->Execute("SHOW KEYS FROM $table");
    if ($objResult !== false) {
        while (!$objResult->EOF) {
            $key_name = $objResult->fields['Key_name'];
            $seqindex = $objResult->fields['Seq_in_index'];
            $uniquekeys[$key_name] = false;
            if ($objResult->fields['Non_unique'] == 0) {
                $uniquekeys[$key_name] = true;
            }
            $fulltextkeys[$key_name] = false;
            if ($objResult->fields['Comment'] == 'FULLTEXT' || $objResult->fields['Index_type'] == 'FULLTEXT') {
                $fulltextkeys[$key_name] = true;
            }
            $tablekeys[$key_name][$seqindex] = '`'.$objResult->fields['Column_name'].'`';
            ksort($tablekeys[$key_name]);
            $objResult->MoveNext();
        }
    }

    foreach ($tablekeys as $keyname => $keyfieldnames) {
        $structureline  = '';
        if ($keyname == 'PRIMARY') {
            $structureline .= 'PRIMARY ';
        } else {
            $structureline .= ($fulltextkeys[$keyname] ? 'FULLTEXT ' : '');
            $structureline .= ($uniquekeys[$keyname]   ? 'UNIQUE '   : '');
        }
        $structureline .= 'KEY'.(($keyname == 'PRIMARY') ? '' : ' '.$keyname);
        $structureline .= ' ('.implode(',', $keyfieldnames).')';
        $structurelines[] = $structureline;
    }

    $tablestructure  = "DROP TABLE IF EXISTS $table;\n";
    $tablestructure .= "CREATE TABLE ".$table." (";
    $tablestructure .= "\n".implode(", \n", $structurelines);
    $tablestructure .= "\n);";

    $alltablesstructure .= str_replace(' ,', ',', $tablestructure);
    return $alltablesstructure;
}


/**
 * Extract table content for the given table from the database.
 *
 * Collects the full content of the given database table and returns
 * an SQL string. This string can in turn be used to restore the table
 * into an empty instance of that table.
 * @see     backup_getDBTables()
 * @version 1.0     initial version
 * @param   string  Table name
 * @global  mixed   Database
 * @return  string  SQL string
 */
function backup_getDBContent($table)
{
    global $objDatabase;
    $output= "";
    $search = array ("\x00", "\x0a", "\x0d", "\x1a");
    $replace= array ('\0', '\n', '\r', '\Z');

    $field_array = $objDatabase->MetaColumnNames($table, true);
    $objResult = $objDatabase->Execute("SELECT * FROM ".$table);
    if ($objResult !== false) {
        while (!$objResult->EOF) {
            $content = "INSERT INTO ".$table." VALUES (";
            for ($j=0; $j<count($field_array);$j++) {
                $field_curr = $field_array[$j];
                $aux = $objResult->fields[$field_curr];
                if (!isset($aux)) {
                    $content .= "NULL,";
                } elseif ($objResult->fields[$field_curr] != "") {
                    $content .= "'".addslashes($objResult->fields[$field_curr])."',";
                } else {
                    $content .= "'',";
                }
            }
            $content = ereg_replace(",$","",$content);
            $content = str_replace($search, $replace, $content);
            $content .= ");\n";
            $output .= $content;
            $objResult->MoveNext();
        }
    }
    return $output;
}


/**
 * Format the integer file size value using appropriate units.
 *
 * Returns a size string using one of the units
 * 'bytes', 'Kbytes', 'Mbytes', or 'Gbytes,
 * so that the numerical value does not exceed 1024, if possible.
 *
 * @version 1.0     initial version
 * @param   integer File size in bytes
 * @return  string  File size string with appropriate unit
 * @todo    Fix potential bug for filesizes >= 1 Petabyte.
 *          Should determine the exponent first, then check against
 *          the array upper boundary.
 * @todo    Add singular form for 1 KB/MG/GB (or use short forms...)!
 */
function size_format($filesize)
{
    //$type = Array ('Byte', 'KByte', 'MByte', 'GByte', TByte);
    $type = array ('B', 'KB', 'MB', 'GB', 'TB');
    for ($i = 0; $filesize > 1024; ++$i) {
        $filesize/= 1024;
    }
    return (round ($filesize, 2)." $type[$i]");
}

?>
