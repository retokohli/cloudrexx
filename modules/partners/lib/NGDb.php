<?PHP

require_once ASCMS_MODULE_PATH.'/partners/lib/NGRecordSet.php';

class NGDb_Error     extends Exception  {};
class NGDb_Error_SQL extends NGDb_Error {};

class NGDb {
    
    static function execute($sql) {
        global $objDatabase;
        $res = $objDatabase->Execute($sql);
        if ($res === false) {
            // TODO: give more information about the fail!
            throw new NGDb_Error_SQL("SQL: --------\n$sql\n--------\nERROR: ".$objDatabase->ErrorMsg());
        }
        // TODO: return something more meaningful
        return $res;
    }

    /**
     * Executes the given SQL and returns a NGRecordSet object.
     * If specified, each element in the NGRecordSet will be converted
     * to a given target class object.
     */
    static function query($sql, $targetclass = Null) {
        $res = self::execute($sql);
        return new NGRecordSet($res, $targetclass);
    }

    /**
     * Executes the given SQL and returns the first object from that
     * query. If $targetclass is specified, the result will be of that
     * class instead of NGRecord.
     */
    static function query1($sql, $targetclass = Null) {
        $records = self::query($sql, $targetclass);
        return $records->current();
    }

    /**
     * Creates an SQL statement that is safe.
     * Pass the SQL string as the first parameter, and the arguments to
     * be parsed (and escaped) into it as additional parameters.
     * Example:
     *     NGDb::parse('SELECT * FROM tbl WHERE id = %0', "a'x")
     * would return
     *     'SELECT * FROM tbl WHERE id = 'a\'x'
     *
     * Note that numbering starts at 0.
     */
    static function parse($sql, $fragments) {
        $fragments = func_get_args();
        array_shift($fragments);
        foreach ($fragments as $key => $value) {
            $sql = str_replace("%$key", "'".addslashes($value)."'", $sql);
        }
        return $sql;
    }

    static function parse_execute($sql, $fragments) {
        $args = func_get_args();
        $sql = call_user_func_array('NGDb::parse', $args);
        return self::execute(self::parse($sql));
    }
}

/**
 * This class represents a simple SQL query. It can be used to create
 * pagination, to get information from a query and to postpone the 
 * execution of it until actually needed.
 */
class NGDb_Query {
    private $inner_sql;
    private $target_class;

    function __construct($inner_sql, $target_class = 'NGRecord') {
        // automatically "clone" other NGDb_Query object if we didn't
        // get a string
        if (is_object($inner_sql) && $inner_sql instanceof NGDb_Query) {
            $this->inner_sql    = $inner_sql->inner_sql;
            $this->target_class = $inner_sql->target_class;
        }
        else {
            $this->inner_sql    = $inner_sql;
            $this->target_class = $target_class;
        }
    }

    function __toString() {
        return $this->inner_sql;
    }

    /**
     * Execute the object's content and return a
     * NGRecordSet object containing the results.
     */
    function rs() {
        return NGDb::query($this->inner_sql, $this->target_class);
    }

    /**
     * Returns a NGRecordSet object containing at most $items_per_page
     * entries.
     *
     * @param int $page_nr the page number to be returned, starting with 1
     * @param int $items_per_page the number of items to show per page. Default 30
     */
    function page($page_nr, $items_per_page = 30) {
        // we're working with 1-based page numbers, calculation is easier tho
        // with 0-based numbers.
        $page_nr -= 1;

        $offset = $page_nr * $items_per_page;
        $limit  = $items_per_page;
        $sql = "
            SELECT * FROM ({$this->inner_sql}) q1
            LIMIT  $limit
            OFFSET $offset
        ";
        return new NGDb_Query($sql, $this->target_class);
    }

    /**
     * Returns the count of all elements in the query.
     */
    function count() {
        $sql = "SELECT COUNT(*) as c FROM ({$this->inner_sql}) q1";
        $rs  = NGDb::query1($sql);
        return $rs->c;
    }

    /**
     * Returns the number of pages to display with the
     * given
     * @param int $items_per_page (default 30)
     */
    function num_pages($items_per_page = 30) {
        return ceil($this->count() / $items_per_page);
    }
}

