<?php

/**
 * Database Tools
 * @version     3.0.0
 * @package     contrexx
 * @subpackage  core
 * @todo        Test!
 * @copyright   CONTREXX CMS - COMVATION AG
 * @author      Reto Kohli <reto.kohli@comvation.com>
 * @internal    Adapted from update/UpdateUtil.php
 */

/**
 * Database Tools
 *
 * Some helpers that make working with and manipulating the database easier
 * @version     3.0.0
 * @package     contrexx
 * @subpackage  core
 * @todo        Test!
 * @copyright   CONTREXX CMS - COMVATION AG
 * @author      Reto Kohli <reto.kohli@comvation.com>
 * @internal    Adapted from update/UpdateUtil.php
 */
class DbTool
{
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
     * @return  boolean                 True on success, false otherwise
     */
    static function table($name, $struc, $idx=array(), $engine='MyISAM')
    {
        if (self::table_exists($name)) {
            return self::check_indexes($name, array(), $struc, true)
                && self::check_columns($name, $struc)
                && self::check_indexes($name, $idx, $struc)
                && self::check_dbtype($name, $engine);
        }
        return self::create_table($name, $struc, $idx, $engine);
    }


    static function drop_table($name)
    {
        global $objDatabase;

        if (!self::table_exists($name)) return true;
        $table_stmt = "DROP TABLE `$name`";
        return (boolean)$objDatabase->Execute($table_stmt);
    }


    static function column_exists($name, $col)
    {
        global $objDatabase;

        $col_info = $objDatabase->MetaColumns($name);
        if ($col_info === false) return false;
        return isset($col_info[strtoupper($col)]);
    }


    static function table_exists($name)
    {
        global $objDatabase;

        $tableinfo = $objDatabase->MetaTables();
        if ($tableinfo === false) return false;
        return in_array($name, $tableinfo);
    }


    static function check_dbtype($name, $engine)
    {
        global $objDatabase;

        $tableinfo   = self::sql("SHOW CREATE TABLE $name");
        $create_stmt = $tableinfo->fields['Create Table'];
        $match = array();
        preg_match('#ENGINE=(\w+)#i', $create_stmt, $match);
        $current_engine = strtoupper($match[1]);
        if (strtoupper($engine) == $current_engine) {
DBG::log("check_dbtype($name, $engine): Current engine $current_engine is okay");
            return true;
        }
        // need to change the engine type.
// TODO: I guess this may bluntly fail.  Should always check the return value!
        $return = self::sql("ALTER TABLE `$name` ENGINE=$engine");
DBG::log("check_dbtype($name, $engine): Altering the engine type ".($return ? "failed" : "successful"));
        return $return;
    }


    static function create_table($name, $struc, $idx=array(), $engine='MyISAM')
    {
        global $objDatabase;

        // create table statement
        $cols = array();
        foreach ($struc as $col => $spec) {
            $cols[] = "`$col` ".self::colspec($spec, true);
        }
        $colspec    = join(",\n", $cols);
        $primaries  = join(",\n", self::getprimaries($struc));
        $table_stmt = "CREATE TABLE `$name`(
            $colspec".(!empty($primaries) ? ",
            PRIMARY KEY ($primaries)" : '')."
        ) ENGINE=$engine";
        if (!self::sql($table_stmt)) {
die("DbTool::create_table($name, $struc, $idx, $engine): Error: failed to create table");
        }

        // index statements. as we just created the table
        // we can now just do check_indexes() to take care
        // of the "problem".
        if (!self::check_indexes($name, $idx)) {
die("DbTool::create_table($name, $struc, $idx, $engine): Error: failed to create indices");
        }
        return true;
    }


    /**
     * Rename the table $table_name_old to $table_name_new
     * @param   string  $table_name_old   The current table name
     * @param   string  $table_name_new   The new table name
     * @return  boolean                   True on success, false otherwise
     */
    static function table_rename($table_name_old, $table_name_new)
    {
        return (boolean)self::sql(
            "RENAME TABLE `$table_name_old` TO `$table_name_new`");
    }


    /**
     * Returns true if the table is empty
     *
     * If the table cannot be accessed, returns null.
     * Hint: call {@see table_exists} first.
     * @param   string      $table_name     The table name
     * @return  boolean                     True if the table is empty,
     *                                      null on error, or false
     */
    static function table_empty($table_name)
    {
        $count = self::record_count($table_name);
        if (is_null($count)) return null;
        return (boolean)$count;
    }


    /**
     * Returns the record count for the given table name
     *
     * If the table cannot be accessed, returns null.
     * @param   string    $table_name     The table name
     * @return  integer                   The record count on success,
     *                                    null otherwise
     */
    static function record_count($table_name)
    {
        $objResult = self::sql("
            SELECT COUNT(*) AS `record_count`
              FROM $table_name");
        if (!$objResult) return null;
        return $objResult->fields['record_count'];
    }


    /**
     * Execute the query on the current database connection and return
     * the resulting recordset
     * @param   string        $statement    The query
     * @return  ADORecordSet                Some type of ADODB recordset on
     *                                      success, false(?) otherwise
     * @global  ADOConnection               The database connection
     */
    static function sql($statement)
    {
        global $objDatabase;

        return $objDatabase->Execute($statement);
    }


    /**
     * Execute the INSERT query on the current database connection and return
     * the ID of the record created
     * @param   string        $statement    The query
     * @return  integer(?)                  The ID of the record created on
     *                                      success, null otherwise
     * @global  ADOConnection               The database connection
     */
    static function insert($statement)
    {
        global $objDatabase;

        if (self::sql($statement)) return $objDatabase->Insert_ID();
        return null;
    }


    static function check_columns($name, $struc, $add_only=false)
    {
        global $objDatabase;

        $col_info = $objDatabase->MetaColumns($name);
        if ($col_info === false) return false;
        // Create missing columns
        foreach ($struc as $col => $spec) {
            if (self::check_column($name, $col_info, $col, $spec)) {
                // col_info NEEDS to be reloaded, as check_column()
                // has changed the table
                $col_info = $objDatabase->MetaColumns($name);
                if ($col_info === false) return false;
            }
        }
        if ($add_only) return true;
        // Drop obsolete columns
        return self::drop_unspecified_columns($name, $struc, $col_info);
    }


    private static function drop_unspecified_columns($name, $struc, $col_info)
    {
        global $objDatabase;

        foreach (array_keys($col_info) as $col) {
            // Loop because the case in $struc is arbitrary
            $exists = false;
            foreach (array_keys($struc) as $col_exists) {
                if (strtolower($col) == strtolower($col_exists)) {
                    $exists = true;
                    break;
                }
            }
            if (!$exists) {
                $col_name = $col_info[$col]->name;
                if (!self::drop_column($name, $col_name)) return false;
            }
        }
        return true;
    }


    /**
     * Drop the given $column_name from $table_name
     * @param   string  $table_name   The table name
     * @param   string  $column_name  The column name
     * @return  boolean               True on success, false otherwise
     * @author  Reto Kohli <reto.kohli@comvation.com>
     */
    static function drop_column($table_name, $column_name)
    {
        return self::sql(
            "ALTER TABLE `$table_name` DROP COLUMN `$column_name`");
    }


    /**
     * Checks the given column and ALTERS what's needed.
     *
     * @return  boolean             True if a change has been made.
     */
    private static function check_column($name, $col_info, $col, $spec)
    {
        global $objDatabase;

        if (!isset($col_info[strtoupper($col)])) {
            $colspec = self::colspec($spec);
            // check if we need to rename the column
            if (isset($spec['renamefrom']) and isset($col_info[strtoupper($spec['renamefrom'])])) {
                // rename requested and possible.
                $from = $spec['renamefrom'];
                $query = "ALTER TABLE `$name` CHANGE `$from` `$col` $colspec";
            } else {
                // rename not possible or not requested. create the new column!
                // TODO: maybe we should somehow notify the caller if
                //       rename was requested but not possible?
                $query = "ALTER TABLE `$name` ADD `$col` $colspec";
            }
            return self::sql($query);
        }
        $col_spec = $col_info[strtoupper($col)];
        $type =
            $col_spec->type.
            (   preg_match('@[a-z]+\([0-9]+\)@i', $spec['type'])
             && $col_spec->max_length > 0
              ? "($col_spec->max_length)"
              : ($col_spec->type == 'enum'
                  ? "(".implode(",", $col_spec->enums).")" : ''));
        $default = (isset($spec['default'])
            ? $spec['default']
            : (isset($spec['default_expr']) ? $spec['default_expr'] : ''));
        if (   $type != strtolower($spec['type'])
// TODO: Please verify that these comparisons do what you expect.
            || $col_spec->unsigned != (isset($spec['unsigned']) && $spec['unsigned'])
            || $col_spec->not_null != (!isset($spec['notnull']) || $spec['notnull'])
            || $col_spec->has_default != (isset($spec['default']) || isset($spec['default_expr']))
            || $col_spec->has_default && ($col_spec->default_value != $default)
            || $col_spec->auto_increment != (isset($spec['auto_increment']) && $spec['auto_increment'])
        ) {
            $colspec = self::colspec($spec);
            $query = "ALTER TABLE `$name` CHANGE `$col` `$col` $colspec";
            self::sql($query);
            return true;
        }
        return false;
        // TODO: maybe we should check for the type of the
        // existing column here and adjust it too?
    }


    private static function check_indexes(
        $name, $idx=array(), $struc=null, $droponly=null
    ) {
        global $objDatabase;

        # mysql> show index from contrexx_access_user_mail;
        $keyinfo = $objDatabase->Execute("SHOW INDEX FROM `$name`");
DBG::log("DbTool::check_indexes($name, \$idx, \$struc): Keyinfo: ".var_export($keyinfo, true));
        if ($keyinfo === false) return false;

        // Find existing keys, drop unused keys
        $arr_keys_to_drop = array();
        $arr_primaries = array();
        while (!$keyinfo->EOF) {
            if (isset($idx[$keyinfo->fields['Key_name']])) {
                $idx[$keyinfo->fields['Key_name']]['exists'] = true;
DBG::log("DbTool::check_indexes($name, \$idx, \$struc): Have key: ".$keyinfo->fields['Key_name']);
                $keyinfo->MoveNext();
                continue;
            }
            if ($keyinfo->fields['Key_name'] == 'PRIMARY') {
                $arr_primaries[] = $keyinfo->fields['Column_name'];
DBG::log("DbTool::check_indexes($name, \$idx, \$struc): Have primary: ".$keyinfo->fields['Column_name']);
                // primary keys should NOT be dropped :P
                $keyinfo->MoveNext();
                continue;
            }
            $arr_keys_to_drop[] = $keyinfo->fields['Key_name'];
DBG::log("DbTool::check_indexes($name, \$idx, \$struc): Obsolete key: ".$keyinfo->fields['Key_name']);
            $keyinfo->MoveNext();
        }

        if ($struc) {
            $new_primaries = self::getprimaries($struc);
            // recreate the primary key in case it changed
            if (   count(array_diff($new_primaries, $arr_primaries))
                || count(array_diff($arr_primaries, $new_primaries))) {
                // delete current primary key, in case there is one
                if (count($arr_primaries)) {
// TODO: This won't work for auto_increment fields!
// Need to remove this flag first and add it again afterwards.
//DBG::log("Struc: ".var_export($struc, true));
//DBG::log("Primary OLD: ".var_export($arr_primaries, true));
//DBG::log("Primary NEW: ".var_export($new_primaries, true));
foreach ($arr_primaries as $key) {
    if (!empty($struc[$key]['auto_increment'])) {
        $struc_temp = $struc[$key];
        unset($struc_temp['auto_increment']);
        self::sql("ALTER TABLE $name CHANGE $key $key ".self::colspec($struc_temp));
    }
}
                    $drop_st = "ALTER TABLE `$name` DROP PRIMARY KEY";
DBG::log("DbTool::check_indexes($name, \$idx, \$struc): Dropping obsolete primary: ".$drop_st);
                    self::sql($drop_st);
                }
                // add new primary key, in case one is defined
                if (count($new_primaries) && empty($droponly)) {
                    $new_st = "ALTER TABLE `$name` ADD PRIMARY KEY (`".join("`, `", $new_primaries)."`)";
DBG::log("DbTool::check_indexes($name, \$idx, \$struc): Adding new primary: ".$new_st);
                    self::sql($new_st);
                }
            }
        }
        // drop obsolete keys
        if (count($arr_keys_to_drop)) {
            foreach ($arr_keys_to_drop as $key) {
                $drop_st = self::dropkey($name, $key);
DBG::log("DbTool::check_indexes($name, \$idx, \$struc): Dropping obsolete key: ".$drop_st);
                self::sql($drop_st);
            }
        }
        if (empty($droponly)) return true;
        // create new keys
        foreach ($idx as $keyname => $spec) {
DBG::log("DbTool::check_indexes($name, \$idx, \$struc): Checking new key: ".$keyname);
            if (!array_key_exists('exists', $spec)) {
                $new_st = self::keyspec($name, $keyname, $spec);
DBG::log("DbTool::check_indexes($name, \$idx, \$struc): Adding new key: ".$new_st);
                self::sql($new_st);
            }
        }
        return true;
    }


    private static function dropkey($table, $name)
    {
        return "DROP INDEX `$name` ON `$table`";
    }


    private static function keyspec($table, $name, $spec)
    {
        foreach ($spec['fields'] as $fieldInfo1 => $fieldInfo2) {
            if (is_integer($fieldInfo2)) {
                $arrFields[] = "`$fieldInfo1`($fieldInfo2)";
            } else {
                $arrFields[] = "`$fieldInfo2`";
            }
        }
        $fields = join(',', $arrFields);
        $type = array_key_exists('type', $spec) ? $spec['type'] : '';
        if (isset($spec['force']) && $spec['force']) {
            $descr = "ALTER IGNORE TABLE `$table` ADD $type INDEX `$name` ($fields)";
        } else {
            $descr = "CREATE $type INDEX `$name` ON $table ($fields)";
        }
        return $descr;
    }


    private static function colspec($spec, $create_tbl_operation=false)
    {
        $unsigned = (array_key_exists('unsigned', $spec)
            ? $spec['unsigned'] : false);
        $notnull = (array_key_exists('notnull', $spec)
            ? $spec['notnull'] : true);
        $autoinc = (array_key_exists('auto_increment', $spec)
            ? $spec['auto_increment'] : false);
        $default_expr = (array_key_exists('default_expr', $spec)
            ? $spec['default_expr'] : '');
        $default = (array_key_exists('default', $spec)
            ? (is_null($spec['default'])
                ? 'NULL' : "'".addslashes($spec['default'])."'")
            : null);
        $binary = (array_key_exists('binary', $spec))
            ? $spec['binary'] : null;
        $after = false;
        if (   !$create_tbl_operation
            && array_key_exists('after', $spec)
        ) {
            $after = $spec['after'];
        }
        $default_st = '';
        if (   strtoupper($spec['type']) != 'BLOB'
            && strtoupper($spec['type']) != 'TEXT'
        ) {
            // BLOB and TEXT types can't have a default value(?)
            if ($default) {
                $default_st = " DEFAULT $default";
            } elseif($default_expr != '') {
                $default_st = " DEFAULT $default_expr";
            }
        }
        $descr =
            $spec['type'].
            ($binary ? ' BINARY' : '').
            ($unsigned ? ' unsigned' : '').
            ($notnull ? ' NOT NULL' : '').
            ($autoinc ? ' auto_increment' : '').
            $default_st.
            ($after ? " AFTER `$after`" : '');
        return $descr;
    }


    private static function getprimaries($struc)
    {
        $primaries = array();
        foreach ($struc as $name => $spec) {
            $is_primary = (array_key_exists('primary', $spec)) ? $spec['primary'] : false;
            if ($is_primary) {
                $primaries[] = $name;
            }
        }
        return $primaries;
    }

}

?>
