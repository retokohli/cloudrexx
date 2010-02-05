<?PHP

class LabelWithText_Error              extends Exception {};
class LabelWithText_UnknownField_Error extends Exception {};

abstract class LabelWithText extends NGModel {
    private $_text_data = array();
    private $lang_fields= array();
    private $labelclass = '';

    function init_textdata($class, $fields) {
        $this->lang_fields = $fields;
        $this->labelclass  = $class;

        // FIXME: find a way around eval()
        eval("\$table = {$class}::typeinfo('table');");
        $id = $this->id;
        if (is_null($id)) {
            return;
        }
        $textobjs = NGDb::query(
            "SELECT alt.* FROM $table AS alt WHERE alt.label_id = $id",
            $class
        );
        foreach ($textobjs as $text) {
            $this->_text_data[$text->lang_id] = $text;
        }
    }

    /**
     * Internal helper to return a translation object for the given
     * language id. The translation object is itself a NGModel object.
     */
    private function _langobj($langid) {
        if (isset($this->_text_data[$langid])) {
            return $this->_text_data[$langid];
        }
        // save() does nothing if nothing is needed to be done.
        // But we need to make sure we have an id here.
        $this->save();

        $class = $this->labelclass;
        $this->_text_data[$langid] = new $class();
        $this->_text_data[$langid]->lang_id  = $langid;
        $this->_text_data[$langid]->label_id = $this->id;
        return $this->_text_data[$langid];
    }

    /**
     * Helper for accessing translated fields. Suppose we have a "name"
     * field - then you could call the "name" method in the two following
     * ways:
     *     $obj->name($lang_id) # -> returns translated value depending on lang id.
     *     $obj->name($lang_id, $new_value) # -> sets translated value
     */
    function __call($func, $args) {
        global $objInit;
        if (!in_array($func, $this->lang_fields)) {
            throw new LabelWithText_UnknownField_Error($func);
        }

        list($langid, $new_val) = $args;
        $t = $this->_langobj($langid);
        if (is_null($new_val)) {

            // if translated string is empty, look for a fallback
            // in the default language.
            if ($t->$func != '') {
                return $t->$func;
            }
            // else: falling back..
            $fallback_lang = $objInit->getBackendDefaultLangId();

            // not falling back again if we're already in fallback language
            if ($fallback_lang == $langid) {
                return '';
            }
            $t2 = $this->_langobj($fallback_lang);
            return $t2->$func;
        }

        $t->$func = $new_val;
        $t->save();
        return $new_val;
    }
}

