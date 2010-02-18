<?php

/**
 * Language Database Interface
 * @copyright   CONTREXX CMS - COMVATION AG
 * @author      Reto Kohli <reto.kohli@comvation.com>
 * @version     2.2.0
 * @package     contrexx
 * @subpackage  core
 */

/**
 * Language Database Interface
 * @copyright   CONTREXX CMS - COMVATION AG
 * @author      Reto Kohli <reto.kohli@comvation.com>
 * @version     2.2.0
 * @package     contrexx
 * @subpackage  core
 */
class LanguageDb
{
    /**
     * Array of error messages
     *
     * You should check this whenever you get a false return value
     * @var   array
     */
    private static $arrErrors = array();


    /**
     * Returns a language variable array by its ID
     *
     * The array looks like
     *  array(
     *    'id'        => The variable ID,
     *    'name'      => The variable name,
     *    'backend'   => True if valid for the backend,
     *    'frontend'  => True if valid for the frontend,
     *    'module_id' => The module ID,
     *    Language ID => array(
     *      'content' => Content,
     *      'status'  => True if verfied,
     *    ),
     *    ... more ... for each language ID available
     *  ),
     * @param   integer   $variable_id    The variable ID
     * @return  array                     The variable array on success,
     *                                    false otherwise
     */
    static function getById($variable_id)
    {
        global $objDatabase;

        $objResult = $objDatabase->Execute("
            SELECT id, name, module_id, backend, frontend
              FROM ".DBPREFIX."language_variable_names
             WHERE id=$variable_id");
        if (!$objResult || $objResult->EOF) return false;

        $arrVariable = array(
            'id' => $objResult->fields['id'],
            'name' => $objResult->fields['name'],
            'backend' => $objResult->fields['backend'],
            'frontend' => $objResult->fields['frontend'],
            'module_id' => $objResult->fields['module_id'],
        );

        $objResult = $objDatabase->Execute("
            SELECT content, lang_id, status
              FROM ".DBPREFIX."language_variable_content
             WHERE varid=$variable_id
             ORDER BY lang_id ASC");
        if (!$objResult) return false;
        while (!$objResult->EOF) {
            $arrVariable[$objResult->fields['lang_id']] = array(
                'content' => $objResult->fields['content'],
                'status' => $objResult->fields['status'],
            );
            $objResult->MoveNext();
        }
        return $arrVariable;

    }


    /**
     * Returns an empty language variable array
     *
     * Note that the language specific parts (content, status) are not
     * present in that array.
     * The array looks like
     *  array(
     *    'id'        => 0,
     *    'name'      => '',
     *    'backend'   => false,
     *    'frontend'  => false,
     *    'module_id' => false,
     *  ),
     * @param   integer   $variable_id    The variable ID
     * @return  array                     The variable array on success,
     *                                    false otherwise
     */
    static function getNew()
    {
        global $objDatabase;

        return array(
            'id'        => 0,
            'name'      => '',
            'backend'   => false,
            'frontend'  => false,
            'module_id' => false,
        );
    }


    /**
     * Deletes the selected language and all content
     *
     * This also removes the language entries from the global
     * core_text table.
     * However, it does not affect obsolete tables like
     * module_gallery_language or module_gallery_language_pics.
     * Also, does not affect page content.  Clean this up in the
     * appropriate code.
     * @global    ADONewConnection
     * @return    boolean    True on success, false otherwise
     */
    function deleteLanguage($lang_id)
    {
        global $objDatabase;

        if (!$objDatabase->Execute("
            DELETE FROM ".DBPREFIX."languages
             WHERE id=".intval($lang_id)))
            return false;
        if (!$objDatabase->Execute("
            DELETE FROM ".DBPREFIX."language_variable_content
             WHERE lang_id=".intval($lang_id)))
            return false;
        return true;
    }


    /**
     * Deletes the language variables with the given ID
     *
     * Deletes all languages if $lang_id is empty.
     * @global  ADONewConnection
     * @return  boolean                     True on success, false otherwise
     */
    function deleteVariable($variable_id, $lang_id=null)
    {
        global $objDatabase;

        if (empty($variable_id)) return false;

        if (!$objDatabase->Execute("
            DELETE FROM ".DBPREFIX."language_variable_content
             WHERE varid=".intval($variable_id).
             (empty($lang_id) ? '' : " AND lang_id=".intval($lang_id))))
            return false;
        if (empty($lang_id)) {
            if (!$objDatabase->Execute("
                DELETE FROM ".DBPREFIX."language_variable_names
                 WHERE id=".intval($variable_id)))
                return false;
        }
        return true;
    }


    /**
     * Adds a new language and copies all language variables from the
     * default language
     *
     * Returns true if the language already exists.
     * @global  ADONewConnection
     * @return  boolean                       True on success, false otherwise
     */
    function addLanguage($short_name, $name, $charset)
    {
        global $objDatabase;

        if (empty($name) || empty($short_name) || empty($charset)) return false;

        $objResult = $objDatabase->Execute("
            SELECT lang
              FROM ".DBPREFIX."languages
             WHERE lang='$short_name'");
        if (!$objResult) return false;
        // If the language exists already, that's fine
        if ($objResult->RecordCount()) return true;

        if (!$objDatabase->Execute("
            INSERT INTO ".DBPREFIX."languages
                lang, name, charset, is_default
            ) VALUES (
                '".addslashes($short_name)."',
                '".addslashes($name)."',
                '".addslashes($charset)."',
                'false'
            )"))
            return false;
        $lang_id = $objDatabase->Insert_ID();

// TODO: Move this task to the Content class!
        // Add lost & found
        $objDatabase->Execute("
            INSERT INTO ".DBPREFIX."content_navigation VALUES (
                '', '1', 0, 'Lost & Found', '', 9999, 'off', '0', '1',
                'system', 1132500836, 'lost_and_found', ".$lang_id.",
                1, '0000-00-00', '0000-00-00', 0, 0, 0, 0
            )");
        $page_id = $objDatabase->Insert_ID();
        $objDatabase->Execute("
            INSERT INTO ".DBPREFIX."content VALUES (
                $page_id, 'Restored categories will be added here.',
                'Lost &amp; Found', 'Lost &amp; Found', 'Lost & Found',
                'Lost & Found', 'index', '', '', 'y'
            )");

        // Copy the content from the default language
        $lang_id_default = FWLanguage::getDefaultLangId();
        return self::copy($lang_id, $lang_id_default);
    }


    /**
     * Copy content from one language to another
     *
     * Clears the target language first if $force is true
     * @param   integer   $lang_id_source   The language ID to copy from
     * @param   integer   $lang_id_target   The language ID to copy to
     * @param   boolean   $force            Remove target entries first if true
     *
     * @return unknown
     */
    static function copy($lang_id_source, $lang_id_target, $force=false)
    {
        global $objDatabase;

        if ($force) {
            if (!$objDatabase->Execute("
                DELETE FROM ".DBPREFIX."language_variable_content (
                 WHERE lang_id=$lang_id_target"))
                return false;
        }
        return (bool)$objDatabase->Execute("
            INSERT INTO ".DBPREFIX."language_variable_content (
                varid, content, lang_id, status
            ) VALUES (
                SELECT varid, content, $lang_id_target, 0
                  FROM ".DBPREFIX."language_variable_content
                 WHERE lang_id=$lang_id_source
            ");
    }


    /**
     * Write language variable to the database in the given language only
     * @param   integer   $module_id    The module ID
     * @param   integer   $lang_id      The language ID
     * @param   string    $name         The variable name
     * @param   string    $content      The variable content
     * @param   boolean   $backend      True if valid for the backend
     * @param   boolean   $frontend     True if valid for the frontend
     * @param   integer   $status       True if the variable is verified
     * @return  boolean                 True on success, false otherwise
     */
    function storeVariable(
        $module_id, $lang_id, $name, $content, $backend, $frontend, $status
    ) {
        $variable_id = self::storeName($module_id, $name, $backend, $frontend);
        if (!$variable_id) return false;
        return self::storeContent($variable_id, $lang_id, $content, $status);
    }


    /**
     * Stores the variable name in the database
     *
     * Returns the variable ID on success, false otherwise.
     * @param   integer   $module_id    The module ID
     * @param   string    $name         The variable name
     * @param   boolean   $backend      True if valid for the backend
     * @param   boolean   $frontend     True if valid for the frontend
     * @return  integer                 The variable ID on success,
     *                                  false otherwise
     */
    static function storeName($module_id, $name, $backend, $frontend)
    {
        global $objDatabase;

        $name = addslashes(trim(strip_tags($name)));
        // Look for the variable, store name and scope
        $objResult = $objDatabase->Execute("
            SELECT `id`
              FROM `".DBPREFIX."language_variable_names`
             WHERE `module_id`=$module_id
               AND `name`='$name'");
        if (!$objResult) return false;
        if ($objResult->EOF) {
            // The variable doesn't exist yet, insert it
            $objResult = $objDatabase->Execute("
                INSERT INTO ".DBPREFIX."language_variable_names (
                    name, module_id, backend, frontend
                ) VALUES (
                    '$name',
                    $module_id,
                    ".($backend  ? 1 : 0).",
                    ".($frontend ? 1 : 0)."
                )");
            if (!$objResult) return false;
            return $objDatabase->Insert_ID();
        }
        // The variable already exists, update it
        $variable_id = $objResult->fields['id'];
        $objResult = $objDatabase->Execute("
            UPDATE ".DBPREFIX."language_variable_names
               SET backend=".($backend ? 1 : 0).",
                   frontend=".($frontend ? 1 : 0)."
             WHERE id=$variable_id");
        if ($objResult) return $variable_id;
        return false;
    }


    /**
     * Store variable content for the given language ID
     * @param   integer   $variable_id  The variable ID
     * @param   integer   $lang_id      The language ID
     * @param   string    $content      The variable content
     * @param   integer   $status       True if the variable is verified
     * @return  boolean                 True on success, false otherwise
     */
    static function storeContent($variable_id, $lang_id, $content, $status)
    {
        global $objDatabase;

        $content = addslashes(trim(strip_tags($content)));
        // Look for the variable content, store it
        $objResult = $objDatabase->Execute("
            SELECT 1
              FROM ".DBPREFIX."language_variable_content
             WHERE varid=$variable_id
               AND lang_id=$lang_id");
        if (!$objResult) return false;
        if ($objResult->EOF) {
            // The variable content doesn't exist yet, insert it
            $objResult = $objDatabase->Execute("
                INSERT INTO ".DBPREFIX."language_variable_content (
                    varid, content, status, lang_id
                ) VALUES (
                    $variable_id,
                    '$content',
                    ".($status ? 1 : 0).",
                    $lang_id
                )");
        } else {
            $objResult = $objDatabase->Execute("
                UPDATE ".DBPREFIX."language_variable_content
                   SET content='$content',
                       status=".intval($status)."
                 WHERE varid=$variable_id
                   AND lang_id=$lang_id");
        }
        if (!$objResult) return false;
        return true;
    }


    /**
     * Returns an array of language variables according to the parameters
     *
     * Any optional parameters left out or set to null are ignored.
     * The resulting array is sorted by ascending IDs.
     * Module IDs zero (0) and one (1) are considered equal, meaning that
     * if $module_id is either of them, both are queried and included
     * in the result.
     * Note that the array may be empty.
     * The array looks like
     *  array(
     *    variable ID => array(
     *      'backend'  => True if valid for the backend,
     *      'frontend' => True if valid for the frontend,
     *      Language ID => array(
     *        'content' => Content,
     *        'status'  => True if verfied,
     *      ),
     *      ... more ... for each language ID available
     *    ),
     *    ... more ...
     *  )
     * @global    array
     * @global    ADONewConnection
     * @global    HTML_Template_Sigma
     * @param     integer   $module_id    The module ID, or zero for core
     * @param     integer   $lang_id      The optional language ID, or null
     * @param     boolean   $status       The optional status, or null
     * @param     boolean   $backend      The optional backend flag, or null
     * @param     boolean   $frontend     The optional frontend flag, or null
     * @param     string    $term         The optional search term, or null
     * @return    array                   The variable array on success,
     *                                    false otherwise
     */
    function getArrayBySearch(
        $module_id, $lang_id=null, $status=null,
        $backend=null, $frontend=null, $term=null
    ) {
        global $objDatabase;

//echo("getArrayBySearch(module_id $module_id, lang_id $lang_id, status $status, backend $backend, frontend $frontend, term $term): Entered<br />");

        $query = "
            SELECT `id`, `name`, `backend`, `frontend`,
                   `lang_id`, `content`, `status`
              FROM `".DBPREFIX."language_variable_names`
             INNER JOIN `".DBPREFIX."language_variable_content`
                   ON `id`=`varid`
             WHERE `module_id`=".
              ($module_id > 1 ? intval($module_id) : "0 OR `module_id`=1").
              ($lang_id  === null ? '' : " AND `lang_id`=".intval($lang_id)).
              ($status   === null ? '' : " AND `status`=".intval($status)).
              ($backend  === null ? '' : " AND `backend`=".intval($backend)).
              ($frontend === null ? '' : " AND `frontend`=".intval($frontend)).
              (empty($term)
                  ? ''
                  : " AND (   `name` LIKE '%".$term."%'
                           OR `content` LIKE '%".$term."%')")."
             ORDER BY `id` ASC";
        $objResult = $objDatabase->Execute($query);
        if (!$objResult) return false;
        $arrVariables = array();
        while (!$objResult->EOF) {
            $id = $objResult->fields['id'];
            if (empty($arrVariables[$id])) {
                $arrVariables[$id] = array(
                    'name'     => $objResult->fields['name'],
                    'backend'  => $objResult->fields['backend'],
                    'frontend' => $objResult->fields['frontend'],
                );
            }
            $lang_id = $objResult->fields['lang_id'];
            $arrVariables[$id][$lang_id] = array(
                'content' => $objResult->fields['content'],
                'status' => $objResult->fields['status'],
            );
            $objResult->MoveNext();
        }
        return $arrVariables;
    }


    /**
     * Returns true if the language directory is valid and writeable
     *
     * @return  boolean     True on success, false otherwise
     */
    function checkPermissions()
    {
        if (is_writeable($this->filePath) AND
           is_dir($this->filePath)) {
            return true;
        } else {
            return false;
        }
    }


    /**
     * Writes language files
     *
     * Note that this uses the newly proposed language file structure
     * that keeps all language files for a single module in the same
     * folder, named like "frontend-de.php", or "backend-it.php"
     * If $module_id is left out or null, writes all components' language files.
     * If $lang_id is left out or null, writes all language files in all
     * available languages.
     * @global  ADONewConnection
     * @param   integer   $module_id      The optional module ID
     * @param   integer   $lang_id        The optional language ID
     * @return  boolean                   True on success, false otherwise
     */
    function writeFiles($module_id=null, $lang_id=null)
    {
        global $objDatabase, $_CORELANG;

        if (!include_once(ASCMS_FRAMEWORK_PATH.'/File.class.php'))
            return false;

        $arrModules = array();
        $arrLanguages = array();

        self::$arrErrors = array();

        $arrModules = array($module_id);
        if (empty($module_id)) {
            $arrModules = array_keys(modulemanager::getNameArray());
            if (empty($arrModules)) {
                self::$arrErrors[] = $_CORELANG['TXT_MODULE_MISSING_ALL'];
                return false;
            }
        }

        $arrLanguages = array($lang_id);
        if (empty($lang_id)) {
            $arrLanguages = array_keys(FWLanguage::getNameArray(true));
            if (empty($arrLanguages)) {
                self::$arrErrors[] = $_CORELANG['TXT_LANGUAGE_MISSING_ALL'];
                return false;
            }
        }
//die("LanguageDb::writeFiles($module_id, $lang_id):<br />Modules:<br />".var_export($arrModules, true)."<br />Languages:<br />".var_export($arrLanguages, true)."<hr />");

        $arrBackFront = array(true, false);
        foreach ($arrModules as $module_id) {
            foreach ($arrLanguages as $lang_id) {
                foreach ($arrBackFront as $backend) {
                    self::writeFile($module_id, $lang_id, $backend);
                }
            }
        }
        return true;
    }


    static function writeFile($module_id, $lang_id, $backend)
    {
        global $objDatabase, $_CORELANG;

//echo("LanguageDb::writeFile($module_id, $lang_id, $backend): writing<hr />");

        $query = "
            SELECT name, is_core
              FROM ".DBPREFIX."modules
             WHERE id=$module_id";
        $objResult = $objDatabase->Execute($query);
        if (!$objResult) {
            self::$arrErrors[] = $_CORELANG['TXT_DATABASE_QUERY_ERROR'];
            return false;
        }
        if ($objResult->EOF) {
            self::$arrErrors[] = sprintf(
                $_CORELANG['TXT_MODULE_MISSING'], $module_id);
            return false;
        }
        $name = $objResult->fields['name'];
        $is_core = $objResult->fields['is_core'];
        if (empty($name)) {
            self::$arrErrors[] = $_CORELANG['TXT_DATABASE_QUERY_ERROR'];
            return false;
        }

        // Note that folders are relative to ASCMS_DOCUMENT_ROOT
        switch($name) {
            case 'core':
                $folder_path = '';
                break;
            case 'media1':
                $name = 'media';
                $folder_path = '/media';
                break;
            case 'media2':
            case 'media3':
            case 'media4':
                // Skip those, media1 does all
                return true;
            default:
                // Regular and other core modules
                $folder_path =
                    ($is_core ? ASCMS_CORE_MODULE_FOLDER : ASCMS_MODULE_FOLDER).
                    '/'.$name;
        }

        $query = "
            SELECT lang FROM ".DBPREFIX."languages
             WHERE id=$lang_id";
        $objResult = $objDatabase->Execute($query);
        if (!$objResult) {
            self::$arrErrors[] = $_CORELANG['TXT_DATABASE_QUERY_ERROR'];
            return false;
        }
        if ($objResult->EOF) {
            self::$arrErrors[] = $_CORELANG['TXT_LANGUAGE_MISSING'];
            return false;
        }
        $lang_code = $objResult->fields['lang'];

        // Query the variables
        $arrVariables = self::getArrayBySearch(
            $module_id, $lang_id, null,
            ($backend ? true : null),
            ($backend ? null : true)
        );

        $folder_path = $folder_path.'/lang/';
        if (!File::exists($folder_path)) File::make_folder($folder_path);

        $file_path =
            $folder_path.
            ($backend ? 'backend' : 'frontend').'-'.$lang_code.'.php';
        if (File::exists($file_path)) File::delete_file($file_path);

        File::chmod($folder_path, 0777);

        $fileHandle = fopen(ASCMS_DOCUMENT_ROOT.'/'.$file_path, 'w');
        if (!$fileHandle) {
            self::$arrErrors[] = sprintf(
                $_CORELANG['TXT_LANGUAGE_ERROR_OPENING_FILE_FOR_WRITING'], $file_path);
            return false;
        }

        @fwrite($fileHandle,
            "<?php\n\n".
            "/**\n * Contrexx CMS\n * generated date ".
            date('r',time())."\n */\n\n");
//echo("Writing variables to $file_path:<br />".var_export($arrVariables, true)."<hr />");
        foreach ($arrVariables as $arrVariable) {
            $name = $arrVariable['name'];
            //$content = str_replace('"', '\\"', $arrVariable[$lang_id]['content']);
            $content = $arrVariable[$lang_id]['content'];
            @fwrite($fileHandle,
                "\$_ARRAYLANG['$name'] = \"$content\";\n");
        }
        @fwrite($fileHandle, "\n?>\n");
        @fclose($fileHandle);
        File::chmod($folder_path, 0755);
//        File::chmod($file_path, 0755);
//die();
        return true;
    }


    /**
     * Returns true if editing variables is enabled
     *
     * This is the case iff the variable tables exist in the database
     * @return    boolean               True if variables can be edited,
     *                                  false otherwise
     * @static
     */
    static function variablesEnabled()
    {
        global $objDatabase;

        $arrTables = $objDatabase->MetaTables('TABLES');
        if (   $arrTables
            && in_array(DBPREFIX."language_variable_names", $arrTables)
            && in_array(DBPREFIX."language_variable_content", $arrTables))
            return true;
        return false;
    }


    static function getErrors()
    {
        return self::$arrErrors;
    }

/*
CREATE TABLE `contrexx_2_1`.`contrexx_language_variable_names` (
`id` INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY ,
`module_id` INT UNSIGNED NOT NULL DEFAULT '0',
`name` VARCHAR( 255 ) NOT NULL DEFAULT '',
`backend` TINYINT( 1 ) UNSIGNED NOT NULL DEFAULT '0',
`frontend` TINYINT( 1 ) UNSIGNED NOT NULL DEFAULT '0'
) ENGINE = MYISAM ;

CREATE TABLE `contrexx_2_1`.`contrexx_language_variable_content` (
`varid` INT UNSIGNED NOT NULL DEFAULT '0',
`lang_id` INT UNSIGNED NOT NULL DEFAULT '0',
`content` TEXT NOT NULL DEFAULT '',
`status` TINYINT( 1 ) UNSIGNED NOT NULL DEFAULT '0'
) ENGINE = MYISAM ;

*/

}

?>
