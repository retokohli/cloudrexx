<?PHP

include_once dirname(__FILE__) .'/LabelWithText.php';

class Partner2Label extends NGModel {
    static function typeinfo($mode) {
        switch ($mode){
            case 'table'  : return DBPREFIX . 'module_partners_to_labels';
            case 'primary':
            case 'fields' : return array('label_id', 'partner_id');
        };
    }
}

