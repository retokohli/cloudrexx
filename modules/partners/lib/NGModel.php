<?PHP

require_once dirname(__FILE__)."/Trigger.php";

class NGModel_Error              extends Exception {};
class NGModel_NoPrimaryKey_Error extends NGModel_Error {};
abstract class NGModel extends NGRecord {

    /**
     * You must implement this function to tell NGModel how to
     * read and write your data.
     * An implementation may look like this:
     *
     *      function typeinfo($mode) {
     *          switch ($mode){
     *              case 'table'  : return 'contrexx_foo_bar';
     *              case 'fields' : return array("id", 'name', 'foo', 'bar');
     *              case 'primary': return 'id';
     *          };
     *      }
     */
    abstract static function typeinfo($mode);

    /**
     * This will automatically be set to true when data changes.
     * If you have changed data in a nested array, it might be
     * neccessary to set this flag to true manually.
     */
    public $dirty = false;

    function __set($key, $value) {
        $this->dirty = true;
        $this->data_storage[$key] = $value;
    }

    /**
     * Returns true if ALL primary keys have a value
     * (ie are not Null).
     */
    function has_primary() {
        foreach ($this->_typeinfo('primary') as $key) {
            if (is_null($this->$key)) {
                return false;
            }
        }
        return true;
    }

    function __get($key) {
        return parent::__get($key);
    }

    /**
     * Returns a data pair ready to be used in an SQL
     * statement.
     * the call
     *      $this->_pair('name', "O'Hara")
     * would return the following (without quotes):
     *      "name, 'O\'Hara'"
     */
    function _pair($key, $value) {
        return $key . " = '" . addslashes($value) . "'";
    }

    /**
     * Wrapper for calling typeinfo(). All this function does
     * is return an array of primaries even if only a single string
     * is given.
     */
    protected function _typeinfo($type) {
        $info = $this->typeinfo($type);
        if ($type == 'primary' and !is_array($info)) {
            return array($info);
        }
        return $info;
    }

    /**
     * Returns a separated list of condition pairs that can
     * then be used in a query for use in WHERE or UPDATE
     * clauses.
     */
    protected function _pk_cond($sep = 'AND') {
        $out = array();
        foreach ($this->_typeinfo('primary') as $key) {
            $out[] = $this->_pair($key, $this->$key);
        }
        return join(" $sep ", $out);
    }

    function _update() {
        $fields = $this->_typeinfo('fields');
        $pk     = $this->_typeinfo('primary');
        $table  = $this->_typeinfo('table');
        $inserts = array();
        DBG::trace();
        DBG::dump($fields);
        foreach ($fields as $field) {
            if (!in_array($field, $pk) and !is_null($this->$field)) {
                $inserts[] = $this->_pair($field, $this->$field);
            }
        }
        $pk_cond = $this->_pk_cond();
        $inserts = join(",\n\t", $inserts);
        $sql = "
            UPDATE {$table}
            SET    {$inserts}
            WHERE  {$pk_cond}
        ";
        NGDb::execute($sql);

        $this->dirty = false;
        return true;
    }
    function _insert() {
        $pk      = $this->_typeinfo('primary');
        $fields  = $this->_typeinfo('fields');
        $table   = $this->_typeinfo('table');
        $inserts = array();
        foreach ($fields as $field) {
            if (in_array($field, $pk) && is_null($this->$field)) {
                continue;
            }
            if (!is_null($this->$field)) {
                // only insert if we have a value. let DEFAULT take
                // care of the rest
                $inserts[] = $this->_pair($field, $this->$field);
            }
        }
        $inserts = join(",\n\t", $inserts);
        $sql = "INSERT INTO {$table} SET $inserts";
        NGDb::execute($sql);
        
        // We can only reliably read the LAST_INSERT_ID() if 
        // there's a single primary key. multiple primary keys
        // must be set manually anyways, so this doesn't make sense.
        if (sizeof($pk) == 1) {
            $key_name = $pk[0];
            // Only read primary key if it's NULL. Otherwise, we would
            // get in trouble pretty easy...
            if (is_null($this->$key_name)) {
                $info = NGDb::execute('SELECT LAST_INSERT_ID() AS id');
                $this->$key_name = $info->fields['id'];
            }
        }
        $this->__is_in_database = true;
        $this->dirty = false;
        return true;
    }
    function save() {
        if (!$this->dirty) return false;
        if ($this->__is_in_database) {
            $res = $this->_update();
            Trigger::event('updated', get_class($this), $this);
            return $res;
        }
        DBG::trace();
        $res = $this->_insert();
        Trigger::event('created', get_class($this), $this);
        return $res;
    }

    /**
     * Deletes this object from the database.
     * This will set the primary key to Null, so when you
     * (accidentally or not) save() the object, a new one will
     * be created.
     */
    function delete() {
        $pk     = $this->typeinfo('primary');
        $table  = $this->typeinfo('table');

        if (!is_null($this->$pk)) {
            $pk_cond = $this->_pk_cond();

            NGDb::execute("DELETE FROM `$table` WHERE $pk_cond");
            $this->$pk = Null;
            return true;
        }
        else {
            throw new NGModel_NoPrimaryKey_Error(
                "You tried to delete this object, but the primary key was not found [{$this->$pk}]"
            );
        }
    }
}

