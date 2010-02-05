<?PHP

class NGRecordSet_Error                 extends Exception {};
class NGRecordSet_NotImplemented_Error  extends NGRecordSet_Error {};

class NGRecordSet implements Iterator {
    private $result;
    private $class;
    private $rewound;

    function __construct($objResult, $targetClass = Null) {
        $this->class   = $targetClass;
        $this->result  = $objResult;
        $this->rewound = true;
    }

    function current() {
        #print "called current()\n";
        $this->rewound = false;

        if ($this->result->EOF) {
            return Null;
        }

        if (is_null($this->class)) {
            return new NGRecord($this->result);
        }
        else {
            $cls = $this->class;
            return new $cls($this->result);
        }
    }

    function next() {
        #print "called next()\n";
        if (!$this->rewound) {
            $this->result->MoveNext();
        }
        return $this->current();
    }

    function key() {
        throw new NGRecordSet_NotImplemented_Error("NGRecordSet::key()");
    }
    function valid() {
        #print "called valid()\n";
        return !$this->result->EOF;
    }
    function rewind() {
        #print "called rewind()\n";
        $this->result->MoveFirst();
        $this->rewound = true;
    }
}

class NGRecord {
    /**
     * NOTE: although this property is protected, DO NOT mess around with it!
     * It's protected instead of private for making NGModel work!
     */
    protected $data_storage = array();

    /**
     * This flag is set to true if the record was read from the database.
     * For freshly created Records (ie through NGModel) that have not been
     * stored in the database, this flag is set to false.
     */
    protected $__is_in_database = false;

    function __get($name) {
        if (isset($this->data_storage[$name]))
            return $this->data_storage[$name];
        return Null;
    }

    function __construct($objResult = Null) {

        #print "new NGRecord\n";
        if (!is_null($objResult)) {
            $this->data_storage = $objResult->fields;
            #foreach ($objResult->fields as $k => $v) {
            #    $this->data_storage[$k] = $v;
            #}
            $this->__is_in_database = true;
        }
        if (get_class($this) == 'AssignableLabel' and is_null($objResult)) {
            print "<pre>";
            print_r($objResult->fields);
            print "</pre>";
        }

    }
}

