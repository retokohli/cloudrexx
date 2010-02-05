<?PHP

include_once dirname(__FILE__) .'/LabelWithText.php';

class LabelEntry extends LabelWithText {
    static function typeinfo($mode) {
        switch ($mode){
            case 'table'  : return DBPREFIX . 'module_partners_label_entry';
            case 'fields' : return array("id", 'label_id', 'parent_entry_id', 'default_partner', 'parse_custom_block', 'datasource_id');
            case 'primary': return 'id';
        };
    }

    function __construct($objResult = Null) {
        parent::__construct($objResult);
        $this->init_textdata('LabelEntry_text', array('name'));
    }

    /**
     * Returns the associated AssignableLabel object.
     */
    function label() {
        $id  = intval($this->label_id);
        $tbl = AssignableLabel::typeinfo('table');
        return NGDb::query1("SELECT t.* FROM `$tbl` AS t WHERE t.id = $id", 'AssignableLabel');
    }

    /**
     * Returns either all or a subset of LabelEntry objects
     * associated with a given AssignableLabel.
     *
     * @param int $langid   Language id by which to sort the entries.
     * @param int $label_id [optional] associated AssignableLabel id (if not given, returns all entries)
     * @param int $parent_id [optional] if given, only returns the
     *                       entries with this parent_entry_id
     */
    static function all($langid, $label_id = null, $parent_id = Null) {
        $table   = self::typeinfo('table');
        $table_t = LabelEntry_text::typeinfo('table');

        if (!is_null($parent_id)) {
            if ($parent_id == 0) {
                $parent_where = "AND parent_entry_id IS NULL";
            }
            else {
                $parent_where = "AND parent_entry_id = %2";
            }
        }

        if ($label_id) {
            $le_where = "AND le.label_id = %1";
        }
        $sql = NGDb::parse("
            SELECT le.*
                FROM            `$table`   AS le
                LEFT OUTER JOIN `$table_t` AS at ON at.label_id = le.id
            WHERE   at.lang_id = %0
                $le_where
                $parent_where
            ORDER BY at.name
            ",
            $langid, $label_id, $parent_id
        );
        return new NGDb_Query($sql, 'LabelEntry');
    }

    /**
     * Returns all the labels that are
     * a subordinate of this current label as
     * a NGDb_Query object.
     * @param int $langid Language id to sort items by.
     */
    function sub_entries($langid) {
        return self::all($langid, $this->label_id, $this->id);
    }

    /**
     * Returns the LabelEntry object
     * identified by the given $id.
     */
    static function get($id) {
        $tbl = self::typeinfo('table');
        $id  = addslashes($id);
        return NGDb::query1("SELECT * FROM `$tbl` WHERE id = '$id'", 'LabelEntry');
    }


    /**
     * Returns the name of all parents, separated by the given
     * separator string.
     */
    function hierarchic_name($langid, $separator = " » ") {
        if ($this->parent_entry_id) {
            $parent = self::get($this->parent_entry_id);
            return $parent->hierarchic_name($langid, $separator)
                . $separator
                . $this->name($langid);
        }
        else {
            return $this->name($langid);
        }
    }

    function partner_count() {
        $id  = $this->id;
        $tbl = Partner2Label::typeinfo('table');
        $sql = "SELECT COUNT(*) AS cnt FROM `$tbl` WHERE `label_id` = '$id'";
        $res = NGDb::query1($sql);
        return $res->cnt;
    }


}


class LabelEntry_text extends NGModel {
    static function typeinfo($mode) {
        switch ($mode){
            case 'table'  : return DBPREFIX . 'module_partners_label_entry_text';
            case 'fields' : return array("label_id", 'lang_id', 'name');
            case 'primary': return array('label_id', 'lang_id');
        };
    }

}
