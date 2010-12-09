<?PHP
class UpdateException extends Exception {};

class Update_DatabaseException extends UpdateException {
    public $sql;
    function __construct($message, $sql = null) {
        parent::__construct($message);
        $this->sql = $sql;
    }
}

class UpdateUtil {
    /**
     * Creates or modifies a table to the given specification.
     *
     * @param string name - the name of the table. do not forget DBPREFIX!
     * @param array struc - the structure of the columns. This is an associative
     *     array with the keys being the column names and the values being an array
     *     with the following keys:
     *       array(
     *           'type'            => 'INT', # or VARCHAR(30) or whatever
     *           'notnull'         => true/false, # optional, defaults to true
     *           'auto_increment'  => true/false, # optional, defaults to false
     *           'default'         => 'value',    # optional, defaults to '' (or 0 if type is INT)
     *           'default_expr'    => expression, # use this instead of 'default' to use NOW(), CURRENT_TIMESTAMP etc
     *           'primary'         => true/false, # optional, defaults to false
     *           'renamefrom'      => 'a_name'    # optional. Use this if the column existed previously with another name
     *           'on_update'       => value for ON UPDATE #optional, defaults to none
     *           'on_delete'       => value for ON DELETE #optional, defaults to none
     *       )
     * @param array idx - optional. Additional index specification. This is an associative array
     *     where the keys are index names and the values are arrays with the following
     *     keys:
     *        array(
     *            'fields' => array('field1', 'field2', ..), # field names to be indexed
     *            'type'   => 'UNIQUE/FULLTEXT', # optional. If left out, a normal search index is created
     *            'force'  => true/false,  # optional. forces creation of unique indexes, even if there
     *                                     # are duplicates (which will be dropped). use with care.
     *        )
     */
    public static function table($name, array $struc, array $idx = array(), $engine = 'MyISAM') {
        if (self::table_exist($name)) {
            self::check_columns($name, $struc);
            self::check_indexes($name, $idx, $struc);
            self::check_dbtype($name, $engine);
        }
        else {
            self::create_table($name, $struc, $idx, $engine);
        }
    }

    public static function drop_table($name) {
        global $objDatabase;
        if (self::table_exist($name)) {
            $table_stmt = "DROP TABLE `$name`";
            if ($objDatabase->Execute($table_stmt) === false) {
                self::cry($objDatabase->ErrorMsg(), $table_stmt);
            }
        }
    }

    public static function column_exist($name, $col) {
        global $objDatabase;
        $col_info = $objDatabase->MetaColumns($name);
        if ($col_info === false) {
            throw new UpdateException(sprintf($_ARRAYLANG['TXT_UNABLE_GETTING_DATABASE_TABLE_STRUCTURE'], $name));
        }
        return isset($col_info[strtoupper($col)]);
    }

    public static function table_exist($name) {
        global $objDatabase, $_ARRAYLANG;
        $tableinfo = $objDatabase->MetaTables();
        if ($tableinfo === false) {
            throw new UpdateException(sprintf($_ARRAYLANG['TXT_UNABLE_GETTING_DATABASE_TABLE_STRUCTURE'], $name));
        }
        return in_array($name, $tableinfo);
    }

    private static function check_dbtype($name, $engine) {
        global $objDatabase;

        $tableinfo   = self::sql("SHOW CREATE TABLE $name");
        $create_stmt = $tableinfo->fields['Create Table'];

        preg_match('#ENGINE=(\w+)#i', $create_stmt, $result);
        $current_engine = strtoupper($result[1]);

        if (strtoupper($engine) == $current_engine) {
            return;
        }
        // need to change the engine type.
        self::sql("ALTER TABLE `$name` ENGINE=$engine");
    }

    private static function create_table($name, array $struc, $idx, $engine) {
        global $objDatabase, $_ARRAYLANG;

        // create table statement
        $cols = array();
        foreach ($struc as $col => $spec) {
            $cols[] = "`$col` ". self::_colspec($spec, true);
        }
        $colspec    = join(",\n", $cols);
        $primaries  = join(",\n", self::_getprimaries($struc));

        $table_stmt = "CREATE TABLE `$name`(
            $colspec".(!empty($primaries) ? ",
            PRIMARY KEY ($primaries)" : '')."
        ) ENGINE=$engine";

        self::sql($table_stmt);
        // index statements. as we just created the table
        // we can now just do check_indexes() to take care
        // of the "problem".
        self::check_indexes($name, $idx);
    }
    private static function cry($msg, $sql) {
        throw new Update_DatabaseException($msg, $sql);
    }


    public static function sql($statement) {
        global $objDatabase;
        # ugly, ugly hack so it does not return Insert_ID when we didn't insert
        $objResult = $objDatabase->Execute($statement);
        if ($objResult === false) {
            self::cry($objDatabase->ErrorMsg(), $statement);
        }
        return $objResult;
    }

    public static function insert($statement) {
        global $objDatabase;
        self::sql($statement);
        return $objDatabase->Insert_ID();
    }


    private function check_columns($name, $struc) {
        global $objDatabase;
        $col_info = $objDatabase->MetaColumns($name);
        if ($col_info === false) {
            self::cry(sprintf($_ARRAYLANG['TXT_UNABLE_GETTING_DATABASE_TABLE_STRUCTURE'], $name));
        }

        // Create columns that don't exist yet
        foreach ($struc as $col => $spec) {
            if (self::_check_column($name, $col_info, $col, $spec)) {
                // col_info NEEDS to be reloaded, as _check_column() has changed the table
                $col_info = $objDatabase->MetaColumns($name);
                if ($col_info === false) {
                    self::cry(sprintf($_ARRAYLANG['TXT_UNABLE_GETTING_DATABASE_TABLE_STRUCTURE'], $name));
                }
            }
        }

        // Drop columns that are not specified
        self::_drop_unspecified_columns($name, $struc, $col_info);
    }

    private function _drop_unspecified_columns($name, $struc, $col_info) {
        global $objDatabase;

        foreach (array_keys($col_info) as $col) {
            // we have to do a stupid loop here as we don't know
            // the exact case of the name in $spec ;(
            $exists = false;
            foreach (array_keys($struc) as $col_exists) {
                if (strtolower($col) == strtolower($col_exists)) {
                    $exists = true;
                    break;
                }
            }
            if (!$exists) {
                $col_name = $col_info[$col]->name;
                $query = "ALTER TABLE `$name` DROP COLUMN `$col_name`";
                self::sql($query);
            }
        }
    }

    /**
     * Checks the given column and ALTERS what's needed. Returns true
     * if a change has been done.
     */
    private function _check_column($name, $col_info, $col, $spec) {
        global $objDatabase;

        if (!isset($col_info[strtoupper($col)])) {
            $colspec = self::_colspec($spec);
            // check if we need to rename the column
            if (isset($spec['renamefrom']) and isset($col_info[strtoupper($spec['renamefrom'])])) {
                // rename requested and possible.
                $from = $spec['renamefrom'];
                $query = "ALTER TABLE `$name` CHANGE `$from` `$col` $colspec";
            }
            else {
                // rename not possible or not requested. create the new column!
                // TODO: maybe we should somehow notify the caller if
                //       rename was requested but not possible?
                $query = "ALTER TABLE `$name` ADD `$col` $colspec";
            }

            self::sql($query);
            return true;
		}
        else {
            $col_spec = $col_info[strtoupper($col)];
            $type = $col_spec->type . (preg_match('@[a-z]+\([0-9]+\)@i', $spec['type']) && $col_spec->max_length > 0 ? "($col_spec->max_length)" : ($col_spec->type == 'enum' ? "(".implode(",", $col_spec->enums).")" : ''));
            $default = isset($spec['default']) ? $spec['default'] : (isset($spec['default_expr']) ? $spec['default_expr'] : '');
            if ($type <> strtolower($spec['type'])
                || $col_spec->unsigned <> (isset($spec['unsigned']) && $spec['unsigned'])
                || $col_spec->not_null <> (!isset($spec['notnull']) || $spec['notnull'])
                || $col_spec->has_default <> (isset($spec['default']) || isset($spec['default_expr']))
                || $col_spec->has_default && ($col_spec->default_value <> $default)
                || $col_spec->auto_increment <> (isset($spec['auto_increment']) && $spec['auto_increment'])
                || isset($spec['on_update'])
                || isset($spec['on_delete'])
            ) {
                $colspec = self::_colspec($spec);
                $query = "ALTER TABLE `$name` CHANGE `$col` `$col` $colspec";
                self::sql($query);
                return true;
            }

        }
        return false;

        // TODO: maybe we should check for the type of the
        // existing column here and adjust it too?
    }

    private function check_indexes($name, $idx, $struc = null) {
        global $objDatabase;
        # mysql> show index from contrexx_access_user_mail;
        $keyinfo = $objDatabase->Execute("SHOW INDEX FROM `$name`");
        if ($keyinfo === false) {
            self::cry($objDatabase->ErrorMsg(), "SHOW INDEX FROM `$name`");
        }

        // Find already existing keys, drop unused keys
        $arr_keys_to_drop = array();
        $arr_primaries = array();
        while (!$keyinfo->EOF) {
            if (isset($idx[ $keyinfo->fields['Key_name'] ])) {
                $idx[$keyinfo->fields['Key_name']]['exists'] = true;
                $keyinfo->MoveNext();
                continue;
            }
            if ($keyinfo->fields['Key_name'] == 'PRIMARY') {
                $arr_primaries[] = $keyinfo->fields['Column_name'];
                // primary keys should NOT be dropped :P
                $keyinfo->MoveNext();
                continue;
            }
            $arr_keys_to_drop[] = $keyinfo->fields['Key_name'];
            $keyinfo->MoveNext();
        }

        if ($struc) {
            $new_primaries = self::_getprimaries($struc);
            // recreate the primary key in case it changed
            if (count(array_diff($new_primaries, $arr_primaries)) || count(array_diff($arr_primaries, $new_primaries))) {
                // delete current primary key, in case there is one
                if (count($arr_primaries)) {
                    $drop_st = "ALTER TABLE `$name` DROP PRIMARY KEY";
                    self::sql($drop_st);
                }

                // add new primary key, in case one is defined
                if (count($new_primaries)) {
                    $new_st = "ALTER TABLE `$name` ADD PRIMARY KEY (`".join("`, `", $new_primaries)."`)";
                    self::sql($new_st);
                }
            }
        }

        // drop obsolete keys
        if (count($arr_keys_to_drop)) {
            foreach ($arr_keys_to_drop as $key) {
                $drop_st = self::_dropkey($name, $key);
                self::sql($drop_st);
            }
        }

        // create new keys
        foreach ($idx as $keyname => $spec) {
            if (!array_key_exists('exists', $spec)) {
                $new_st = self::_keyspec($name, $keyname, $spec);
                self::sql($new_st);
            }
        }
        // okay, that's it, have a nice day!
    }

    private function _dropkey($table, $name) {
        return "DROP INDEX `$name` ON `$table`";
    }

    private function _keyspec($table, $name, $spec) {
        foreach ($spec['fields'] as $fieldInfo1 => $fieldInfo2) {
            if (intval($fieldInfo1) !== $fieldInfo1) {
                $arrFields[] = '`'.$fieldInfo1.'`('.$fieldInfo2.')';
            } else {
                $arrFields[] = '`'.$fieldInfo2.'`';
            }
        }
        $fields = join(',', $arrFields);
        $type   = array_key_exists('type', $spec) ? $spec['type'] : '';

        if (isset($spec['force']) && $spec['force']) {
            $descr = "ALTER IGNORE TABLE `$table` ADD $type INDEX `$name` ($fields)";
        } else {
            $descr  = "CREATE $type INDEX `$name` ON $table ($fields)";
        }

        return $descr;
    }
    private function _colspec($spec, $create_tbl_operation = false) {
        $unsigned     = (array_key_exists('unsigned',       $spec)) ? $spec['unsigned']       : false;
        $notnull      = (array_key_exists('notnull',        $spec)) ? $spec['notnull']        : true;
        $autoinc      = (array_key_exists('auto_increment', $spec)) ? $spec['auto_increment'] : false;
        $default_expr = (array_key_exists('default_expr',   $spec)) ? $spec['default_expr']   : '';
        $default      = (array_key_exists('default',        $spec)) ? $spec['default']        : null;
        $binary       = (array_key_exists('binary',         $spec)) ? $spec['binary']         : null;
        $on_update    = (array_key_exists('on_update',      $spec)) ? $spec['on_update']      : null;
        $on_delete    = (array_key_exists('on_delete',      $spec)) ? $spec['on_delete']      : null;

        $after = false;
        if (!$create_tbl_operation) {
            $after    = (array_key_exists('after',          $spec)) ? $spec['after']          : false;
        }

        $default_st = '';
        if (strtoupper($spec['type']) != 'BLOB' and strtoupper($spec['type']) != 'TEXT') {
            // BLOB/TEXT can't have a default value... sez MySQL
            if (!is_null($default)) {
                $default_st = " DEFAULT '".addslashes($default)."'";
            }
            elseif($default_expr != '') {
                $default_st = " DEFAULT $default_expr";
            }
        }

        $descr  = $spec['type'];
        $descr .= $binary ? " BINARY" : '';
        $descr .= $unsigned ? " unsigned"      : '';
        $descr .= $notnull ? " NOT NULL"       : '';
        $descr .= $autoinc ? " auto_increment" : '';
        $descr .= $default_st;
        $descr .= $on_update ? " ON UPDATE ".$on_update : '';
        $descr .= $on_delete ? " ON DELETE ".$on_delete : '';
        $descr .= $after ? " AFTER `".$after."`" : '';
        return $descr;
    }
    private function _getprimaries($struc) {
        $primaries = array();
        foreach ($struc as $name => $spec) {
            $is_primary = (array_key_exists('primary', $spec)) ? $spec['primary'] : false;
            if ($is_primary) {
                $primaries[] = $name;
            }
        }
        return $primaries;
    }

    /**
     * Replace certain strings in a content page
     *
     * This method will replace $search with $replace in the content page(s) specified by the module ID $moduleId and CMD $cmd.
     * If $cmd is set to NULL, the replacement will be done on every content page of the specified module.
     * $search and $replace can either be a single string or an array of strings.
     * $changeVersion specifies the Contrexx version in which the replaced should take place. Latter means that the replace will only be done if the installed Contrexx version is older than the one specified by $changeVersion.
     *
     * @global Contrexx_Update
     * @global Array
     * @param integer Module ID
     * @param string CMD
     * @param mixed Search string(s)
     * @param mixed Replacement string(s)
     * @param string Contrexx version
     * of the content page
     */
    public static function migrateContentPage($moduleId, $cmd, $search, $replace, $changeVersion)
    {
        global $objUpdate, $_CONFIG;

        if ($objUpdate->_isNewerVersion($_CONFIG['coreCmsVersion'], $changeVersion)) {
            $query = "
                SELECT
                    c.`id`,
                    c.`content`,
                    c.`title`,
                    c.`metatitle`,
                    c.`metadesc`,
                    c.`metakeys`,
                    c.`metarobots`,
                    c.`css_name`,
                    c.`redirect`,
                    c.`expertmode`,
                    n.`catid`,
                    n.`is_validated`,
                    n.`parcat`,
                    n.`catname`,
                    n.`target`,
                    n.`displayorder`,
                    n.`displaystatus`,
                    n.`activestatus`,
                    n.`cachingstatus`,
                    n.`username`,
                    n.`cmd`,
                    n.`lang`,
                    n.`startdate`,
                    n.`enddate`,
                    n.`protected`,
                    n.`frontend_access_id`,
                    n.`backend_access_id`,
                    n.`themes_id`,
                    n.`css_name`
                FROM `".DBPREFIX."content` AS c
                INNER JOIN `".DBPREFIX."content_navigation` AS n ON n.`catid` = c.`id`
                WHERE n.`module` = $moduleId ".($cmd === null ? '' : "AND n.`cmd` = '$cmd'")." AND n.`username` != 'contrexx_update_$changeVersion'";
            $objContent = self::sql($query);
            $orig_loopy_query = $query;
            $arrFailedPages = array();
            while (!$objContent->EOF) {
                $newContent = str_replace(
                    $search,
                    $replace,
                    $objContent->fields['content']
                );
                $query = "UPDATE `".DBPREFIX."content` AS c INNER JOIN `".DBPREFIX."content_navigation` AS n on n.`catid` = c.`id` SET `content` = '".addslashes($newContent)."', `username` = 'contrexx_update_$changeVersion' WHERE c.`id` = ".$objContent->fields['id'];
                self::sql($query);

                $query = "UPDATE `".DBPREFIX."content_navigation_history` SET `is_active` = '0' WHERE `catid` = ".$objContent->fields['id'];
                self::sql($query);

                $query = "
                    INSERT INTO `".DBPREFIX."content_navigation_history`
                    SET
                        `is_active` = '1',
                        `catid` = ".$objContent->fields['id'].",
                        `parcat` = ".$objContent->fields['parcat'].",
                        `catname` = '".addslashes($objContent->fields['catname'])."',
                        `target` = '".$objContent->fields['target']."',
                        `displayorder` = ".$objContent->fields['displayorder'].",
                        `displaystatus` = '".$objContent->fields['displaystatus']."',
                        `activestatus` = '".$objContent->fields['activestatus']."',
                        `cachingstatus` = '".$objContent->fields['cachingstatus']."',
                        `username` = 'contrexx_update_$changeVersion',
                        `changelog` = ".time().",
                        `cmd` = '".$objContent->fields['cmd']."',
                        `lang` = ".$objContent->fields['lang'].",
                        `module` = $moduleId,
                        `startdate` = '".$objContent->fields['startdate']."',
                        `enddate` = '".$objContent->fields['enddate']."',
                        `protected` = ".$objContent->fields['protected'].",
                        `frontend_access_id` = ".$objContent->fields['frontend_access_id'].",
                        `backend_access_id` = ".$objContent->fields['backend_access_id'].",
                        `themes_id` = ".$objContent->fields['themes_id'].",
                        `css_name` = '".$objContent->fields['css_name']."'";
                $historyId = self::insert($query);

                $query = "
                    INSERT INTO `".DBPREFIX."content_history`
                    SET
                        `id` = ".$historyId.",
                        `page_id` = ".$objContent->fields['id'].",
                        `content` = '".addslashes($newContent)."',
                        `title` = '".addslashes($objContent->fields['title'])."',
                        `metatitle` = '".addslashes($objContent->fields['metatitle'])."',
                        `metadesc` = '".addslashes($objContent->fields['metadesc'])."',
                        `metakeys` = '".addslashes($objContent->fields['metakeys'])."',
                        `metarobots` = '".addslashes($objContent->fields['metarobots'])."',
                        `css_name` = '".addslashes($objContent->fields['css_name'])."',
                        `redirect` = '".addslashes($objContent->fields['redirect'])."',
                        `expertmode` = '".$objContent->fields['expertmode']."'";
                self::sql($query);

                $query = "
                    INSERT INTO	`".DBPREFIX."content_logfile`
                    SET
                        `action` = 'update',
                        `history_id` = ".$historyId.",
                        `is_validated` = '1'";
                self::sql($query);

                $objContent->MoveNext();
            }
        }
    }

    public static function DefaultActionHandler($e) {
        if ($e instanceof Update_DatabaseException) {
            return _databaseError($e->sql, $e->getMessage());
        }
		setUpdateMsg($e->getMessage());
		return false;
    }
}

