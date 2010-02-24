<?PHP
class LabelView extends NGView {
    protected $label;
    protected $lang_id;
    protected $entries;

    function __construct($path, $label_obj, $lang_id) {
        parent::__construct($path);
        $this->label   = $label_obj;
        $this->lang_id = $lang_id;

        $this->entries = $this->entries_to_arr();
    }
    function entries_to_arr() {
        // this partners => 1 is a hack, but it's not used in the output, so it's no problem
        $tmp = array(array('id'=>0, 'name' => tr('TXT_PARTNERS_PLEASE_CHOOSE'), 'parentid' => Null, 'children' => array(), 'partners' => 1));
        foreach ($this->label->labels($this->lang_id)->rs() as $entry) {
            $tmp[$entry->id] = $this->_entry2arr($entry);
        }
        foreach ($tmp as $id => $e) {
            $par = $e['parentid'];
            if ($par == '') {
                continue;
            }
            $tmp[$par]['children'][] = &$tmp[$id];
            $tmp[$id]['moved']   = true;
        }
        // cleanup
        foreach (array_keys($tmp) as $id) {
            if ($tmp[$id]['moved']){
                unset($tmp[$id]['moved']);
                unset($tmp[$id]);
            }
        }
        // cleanup stage 2
        $out = array();
        foreach ($tmp as $entry) {
            $out[] = $entry;
        }
        return $out;
    }

    function _entry2arr($e) {
        return array(
            'id'         => $e->id,
            'name'       => $e->name($this->lang_id),
            'parentid'   => $e->parent_entry_id,
            'children'   => array(),
            'partners'   => $e->partner_count(),
            #'entry_obj'  => $e
        );
    }

    function dropdown_name() {
        return "dd_" . strtolower($this->label->label_placeholder);
    }


    // walks the entry tree and removes all the label entries
    // that have no partners assigned. Note that empty parents
    // of entries with partners will of course NOT be removed.
    function hide_empty() {
        $this->_hide(&$this->entries);
        $this->entries = $this->_reindex($this->entries);
    }
    private function _reindex($list) {
        $out = array();
        foreach($list as $v) {
            $v['children'] = $this->_reindex($v['children']);
            $out[] = $v;
        }
        return $out;
    }

    private function _hide(&$list) {

        $count = 0;
        foreach ($list as $k => $entry) {

            $child_count = $this->_hide($entry['children']) + $entry['partners'];
            if ($child_count == 0) {
                unset($list[$k]);
            }
            else {
                unset($list[$k]['partners']);
            }
            $count += $child_count;
        }
        return $count;
    }

    function __toString() {
        $this->SELECT_NAME = $this->dropdown_name();
        $this->CURRENT     = Request::ANY($this->dropdown_name());
        $this->setVariable('ENTRIES_JSON', json_encode($this->entries));
        return $this->get();
    }

}

