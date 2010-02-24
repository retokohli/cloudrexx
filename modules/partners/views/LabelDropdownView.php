<?PHP

include_once(dirname(__FILE__)."/LabelView.php");

class LabelDropdownView extends LabelView {

    function __construct($path, $label_obj, $lang_id) {
        parent::__construct($path, $label_obj, $lang_id);

        $this->loadTemplateFile('_label_dropdown.html',true,true);

    }
}

