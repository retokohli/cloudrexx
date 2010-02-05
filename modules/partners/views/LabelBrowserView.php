<?PHP

include_once(dirname(__FILE__)."/LabelView.php");

class LabelBrowserView extends LabelView {
    
    function __construct($path, $label_obj, $lang_id) {
        parent::__construct($path, $label_obj, $lang_id);
        $this->loadTemplateFile('_label_browser.html',true,true);
    }

    function __toString() {
        $this->SELECT_NAME = $this->dropdown_name();
        $this->CURRENT     = Request::ANY($this->dropdown_name());
        $this->setVariable('ENTRIES_JSON', json_encode($this->entries_to_arr()));
        return $this->get();
    }
}

