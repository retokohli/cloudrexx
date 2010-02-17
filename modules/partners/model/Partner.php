<?PHP

require_once dirname(__FILE__)."/../lib/NGDb.php";
require_once dirname(__FILE__)."/../lib/NGModel.php";
require_once dirname(__FILE__)."/../lib/NGRecordSet.php";

require_once ASCMS_FRAMEWORK_PATH.'/Validator.class.php';

require_once ASCMS_MODULE_PATH.'/partners/model/Partner2Label.php';

class Partner extends NGModel {
    static function typeinfo($type) {
        switch ($type){
            case 'table'  : return DBPREFIX . 'module_partners';
            case 'fields' :
                return array(
                    'id',
                    'name',
                    'creation_date',
                    'first_contact_name',
                    'first_contact_email',
                    'web_url',
                    'address',
                    'city',
                    'zip_code',
                    'phone_nr',
                    'fax_nr',
                    'customer_quote',
                    'logo_url',
                    'sort_order',
                    'description',
                    'active',
                    'user_id',
                );
            case 'primary': return 'id';
        };
    }

    private static function _escape_fulltext($text) {
        $text = str_replace('%', '\%', $text);
        $text = str_replace('_', '\_', $text);
        $text = str_replace('*', '%',  $text);
        $text = str_replace('?', '_',  $text);
        $text = addslashes($text);

        return $text;
    }

    static function search($fulltext, $label_queries=array(), $mode='backend') {

        $fulltext = self::_escape_fulltext($fulltext);

        list($label_join, $label_where) = self::label_search('partner', $label_queries);

        $frontend_join  = '';
        $frontend_where = '';
        if ($mode=='frontend' && !sizeof($label_queries) && $fulltext=='') {
            // no search and we're in frontend. this means we're only to
            // display the default partners.
            $partner2lbl_tbl= Partner2Label::typeinfo('table');
            $label_tbl      = LabelEntry::typeinfo('table');
            $assignable_tbl = AssignableLabel::typeinfo('table');
            $default_labels = NGDb::query("
                SELECT    lbl.id
                FROM      $label_tbl      AS lbl
                LEFT JOIN $assignable_tbl AS atl ON atl.id = lbl.label_id
                WHERE lbl.default_partner = 1
                AND   atl.active          = 1
            ");

            $idlist = array();
            $frontend_join = "
                LEFT JOIN $partner2lbl_tbl ptl ON ptl.partner_id = partner.id
            ";
            foreach ($default_labels as $entry) {
                $idlist[] = $entry->id;
            }
            $frontend_where = "AND ptl.label_id IN (".join(',', $idlist).")";
        }
        if ($mode == 'frontend') {
            $active_only = 'AND active = 1';
        }

        $partner_tbl = Partner::typeinfo('table');
        $sql = "
            SELECT partner.*
            FROM $partner_tbl AS partner
            $label_join
            $frontend_join
            WHERE (partner.name               LIKE '$fulltext%'
                OR partner.first_contact_name LIKE '$fulltext%'
                OR partner.web_url            LIKE '$fulltext%'
                OR partner.address            LIKE '$fulltext%'
                OR partner.city               LIKE '$fulltext%')
                AND $label_where
                $frontend_where
                $active_only
            ORDER BY `sort_order`, `name`
        ";
        #print("<pre>$sql</pre>");
        return new NGDb_Query($sql, 'Partner');
    }

    /**
     * Returns all LabelEntry objects that are assigned
     * to this Partner as a NGDb_Query, ordered by
     * the language given by $lang_id
     * If you pass the optional $label_id, only
     * LabelEntry objects associated with that AssignableLabel
     * will be returned.
     * @param int $lang_id Language by which to sort the entries
     * @param int $label_id Optional AssignableLabel id to restrict results.
     */
    function assigned_entries($lang_id, $label_id = Null) {
        $l_table   = LabelEntry::typeinfo('table');
        $l_table_t = LabelEntry_text::typeinfo('table');
        $p2l_table = Partner2Label::typeinfo('table');

        $label_where = '';
        $lang_id = intval($lang_id);

        if (!is_null($label_id)) {
            $label_id = intval($label_id);
            $label_where = "AND le.label_id = $label_id";
        }

        $sql = "
            SELECT le.*
                FROM            `$l_table`   AS le
                LEFT OUTER JOIN `$l_table_t` AS at ON at.label_id = le.id
                LEFT OUTER JOIN `$p2l_table` AS pt ON pt.label_id = le.id
            WHERE at.lang_id    = {$lang_id}
              AND pt.partner_id = {$this->id}
                  $label_where
            ORDER BY at.name
        ";
        return new NGDb_Query($sql, 'LabelEntry');
    }

    /**
     * Removes the current entry from the partner.
     */
    function drop_entry($label_entry) {
        $e_id  = intval($label_entry->id);
        $p_id  = intval($this->id);
        $tbl   = Partner2Label::typeinfo('table');

        NGDb::execute("DELETE FROM $tbl WHERE partner_id = $p_id AND label_id = $e_id");
    }

    /**
     * Assigns a the given LabelEntry object to this partner object.
     * Note that depending on the LabelEntry's configuration of multiple_assignable,
     * the previous value(s) will be removed.
     */
    function assign_entry($label_entry) {
        $label = $label_entry->label();

        // see if we need to drop all the links
        if (!$label->multiple_assignable) {
            $link_t  = Partner2Label::typeinfo('table');
            $label_t = LabelEntry::typeinfo('table');
            // FIXME: this probably needs some optimisation for larger datasets..
            NGDb::execute("
                DELETE FROM $link_t
                WHERE label_id IN (
                        SELECT id FROM $label_t
                        WHERE label_id = {$label->id}
                    )
                AND partner_id = {$this->id};
            ");
        }

        try {
            $link = new Partner2Label();
            $link->partner_id = $this->id;
            $link->label_id   = $label_entry->id;
            $link->save();
        }
        catch (NGDb_Error_SQL $e){
            if (!preg_match('#Duplicate entry#', $e->getMessage())) {
                // we only ignore duplicates, as they COULD arise
                throw $e;
            }
        }
    }

    /**
     * Returns an array consisting of a join statement
     * and a where clause.
     * @param string $left_tbl - The left table; where to join from.
     * @param array  $labels - array of label ids to search
     */
    private static function label_search($left_tbl, $labels) {
        if (empty($labels)) {
            return array('', "(1=1)");
        }

        $partner2lbl_tbl = Partner2Label::typeinfo('table');

        $labels = array_map('intval', $labels);
        $join   = '';
        $wheres = array();
        foreach ($labels as $entry) {
            $join .= "
                LEFT JOIN $partner2lbl_tbl AS ptl_$entry ON ptl_$entry.partner_id = $left_tbl.id
            ";
            $wheres[] = "ptl_$entry.label_id = $entry";
        }

        $cond = join(" AND ", $wheres);

        return array($join, $cond);
    }

    static function all($label_queries = array()) {
        $tbl = Partner::typeinfo('table');
        list($label_join, $label_where) = self::label_search($tbl, $label_queries);
        $sql = "
            SELECT *
            FROM `{$tbl}`
                $label_join
            WHERE
                $label_where
            ORDER BY `name`
        ";
        return new NGDb_Query($sql, 'Partner');
    }

    static function get($pkval) {
        $table = self::typeinfo('table');
        $pk    = self::typeinfo('primary');
        $fields= join(', ', self::typeinfo('fields'));
        $sql   = NGDb::parse("SELECT $fields FROM `$table` WHERE $pk = %0", $pkval);

        return NGDb::query1($sql, 'Partner');
    }

    /**
     * validates the fields of the Partner and returns false
     * if there is an invalid field. If a callback is provided,
     * it will be called for each failed field with an appropriate
     * message.
     */
    function validate($callback = Null) {
        global $_ARRAYLANG;
        $valid = true;
        if(!FWValidator::isEmail($this->first_contact_email)) {
            $this->_valid_msg($callback, $_ARRAYLANG['TXT_PARTNERS_INV_EMAIL']);
            $valid = false;
        }
        if(strlen($this->first_contact_name) < 3) {
            $this->_valid_msg($callback, $_ARRAYLANG['TXT_PARTNERS_INV_CONTACT']);
            $valid = false;
        }
        if(strlen($this->name) < 3) {
            $this->_valid_msg($callback, $_ARRAYLANG['TXT_PARTNERS_INV_NAME']);
            $valid = false;
        }
        if(strlen($this->city) < 3) {
            $this->_valid_msg($callback, $_ARRAYLANG['TXT_PARTNERS_INV_CITY']);
            $valid = false;
        }

        return $valid;
    }
    private function _valid_msg($callback, $arg) {
        if (!is_null($callback)) {
            call_user_func($callback, $arg);
        }
        // else: just don't do anything
    }

    function all_labels() {
        $ptl = Partner2Label::typeinfo('table');
        $lbl = LabelEntry::typeinfo('table');
        $id  = $this->id;
        return new NGDb_Query(
            "
                SELECT l.*
                FROM      $ptl AS p
                LEFT JOIN $lbl AS l ON l.id = p.label_id
                WHERE p.partner_id = $id
            ",
            'LabelEntry'
        );
    }
}

