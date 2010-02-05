<?PHP

include_once dirname(__FILE__) .'/LabelEntry.php';
include_once dirname(__FILE__) .'/LabelWithText.php';

class AssignableLabel extends LabelWithText {
    static function typeinfo($mode) {
        switch ($mode){
            case 'table'  : return DBPREFIX . 'module_partners_assignable_label';
            case 'fields' : return array("id", 'label_placeholder', 'multiple_assignable', 'active', 'datasource');
            case 'primary': return 'id';
        };
    }

    function __construct($objResult = Null) {
        parent::__construct($objResult);
        $this->init_textdata('AssignableLabel_text', array('name'));
    }

    /**
     * Returns the number of assignable labels.
     */
    function num_labels() {
        $tbl = LabelEntry::typeinfo('table');
        $r = NGDB::query1(
            "SELECT count(*) as c FROM $tbl WHERE label_id = {$this->id}"
        );
        return $r->c;
    }

    static function all($langid) {
        $table   = self::typeinfo('table');
        $table_t = AssignableLabel_text::typeinfo('table');

        $sql = NGDb::parse("
            SELECT al.*
                FROM `$table` al
                LEFT JOIN `$table_t` at ON at.label_id = al.id
            WHERE at.lang_id = %0
            ORDER BY at.name
            ",
            $langid
        );
        return new NGDb_Query($sql, 'AssignableLabel');
    }

    /**
     * Returns an array of LabelEntry objects, wrapped in
     * an array to show the indentation level so they can
     * be displayed nicely:
     * array(
     *    array('indent' => 1, 'entry' => $entry),
     *    array('indent' => 2, 'entry' => $entry),
     *    array('indent' => 2, 'entry' => $entry),
     * );
     */
    function labels_with_indent($langid) {
        $ret = array();
        $entries = LabelEntry::all($langid, $this->id, 0);
        foreach ($entries->rs() as $entry) {
            $got = $this->_labels_with_ind($langid, 0, $entry);
            $ret = array_merge($ret, $got);
        }
        return $ret;
    }
    /**
     * Actual implementation of labels_with_indent().
     */
    private function _labels_with_ind($langid, $ind, $entry) {
        $out   = array();
        $out[] = array('indent' => $ind, 'entry' => $entry);

        foreach ($entry->sub_entries($langid)->rs() as $sub) {
            $out = array_merge($out, $this->_labels_with_ind($langid, $ind+1, $sub));
        }
        return $out;
    }

    /**
     * Returns all the LabelEntry objects associated with this AssignableLabel
     * as a NGDb_Query. Note that any nesting information is lost here. For
     * nested data, use labels_with_indent() instead.
     *
     * @param int $langid Language id by which to sort
     */
    function labels($langid) {
        return LabelEntry::all($langid, $this->id);
    }

    static function get($id) {
        $id = intval($id);
        $tbl= AssignableLabel::typeinfo('table');
        return NGDb::query1("SELECT * FROM $tbl WHERE id = $id", 'AssignableLabel');
    }
}

class AssignableLabel_text extends NGModel {
    static function typeinfo($mode) {
        switch ($mode){
            case 'table'  : return DBPREFIX . 'module_partners_assignable_label_text';
            case 'fields' : return array("label_id", 'lang_id', 'name', 'name_m');
            case 'primary': return array('label_id', 'lang_id');
        };
    }

}
