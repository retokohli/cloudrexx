<?PHP
class LabelView extends NGView {
    protected $label;
    protected $lang_id;

    function __construct($path, $label_obj, $lang_id) {
        parent::__construct($path);
        $this->label   = $label_obj;
        $this->lang_id = $lang_id;
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

}

