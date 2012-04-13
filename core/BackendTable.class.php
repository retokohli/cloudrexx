<?php
class BackendTable extends HTML_Table {
    public function __construct($attrs = array()) {
        parent::__construct(array_merge($attrs, array('class' => 'adminlist')));
    }

    public function toHtml() {
        $this->altRowAttributes(1, array('class' => 'row1'), array('class' => 'row2'), true);
        return parent::toHtml();
    }
}