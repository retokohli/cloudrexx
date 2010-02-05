<?PHP

include_once(dirname(__FILE__)."/LabelView.php");

class LabelDropdownView extends LabelView {

    private $entries;

    function __construct($path, $label_obj, $lang_id) {
        parent::__construct($path, $label_obj, $lang_id);

        $this->loadTemplateFile('_label_dropdown.html',true,true);

        $this->entries = $this->entries_to_arr();
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

