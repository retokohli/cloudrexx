<?PHP

class DataSource {
    protected $label;
    protected $langid;
    protected $lang_rs;

    function __construct($label, $langid, $lang_rs) {
        $this->label   = $label;
        $this->langid  = $langid;
        $this->lang_rs = $lang_rs;
    }

    /**
     * Imports all the entries from the data source into the label
     * entries of the given AssignableLabel.
     *
     * NOTE that subclasses need to overload this method to actually
     * do something - this is a dummy and also used to import when
     * there is no datasource specified.
     */
    function import() {
        return;
    }


    /**
     * Use this method to look for an entry for the given
     * datasource identifier.
     */
    function label_entry_for($identifier) {
        $query = $this->label->labels($this->langid);
        $sql = NGDb::parse("
                SELECT e.*
                FROM ($query) AS e
                WHERE e.datasource_id = %0
            ",
            $identifier
        );
        DBG::msg("----------------- label_entry_for($identifier): SQL.....  $sql");
        $entry = NGDb::query1($sql, 'LabelEntry');
        if (!$entry) {
            // new entry
            $entry = new LabelEntry();
            $entry->label_id = $this->label->id;
            $entry->datasource_id = $identifier;
            $entry->save();
            DBG::msg("----------------- label_entry_for($identifier): CREATED");
        }
        else {
            DBG::msg("----------------- label_entry_for($identifier): FOUND");
        }
        return $entry;
    }

}

