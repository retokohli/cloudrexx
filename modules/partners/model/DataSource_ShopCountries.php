<?PHP

require_once(ASCMS_MODULE_PATH.'/partners/model/DataSource.php');

class DataSource_ShopCountries extends DataSource {
    function import() {
        $rs = NGDb::query("
            SELECT * FROM ".DBPREFIX."module_shop_countries
        ");
        DBG::msg("-------------- IMPORTING COUNTRIES ---");

        foreach ($rs as $country) {
            DBG::msg("-------------- importing country {$country->countries_iso_code_3} --- ");
            $dsid = "shop_countries|" . $country->countries_iso_code_3;
            $entry = $this->label_entry_for($dsid);

            // we set the translation UNLESS there's already a value for that language.
            // This way, we can import new data, but if the user chose to translate an
            // entry, we won't overwrite it.
            foreach ($this->lang_rs as $lang) {
                DBG::msg("-------------------------- importing for langid {$lang->id}");
                if ($entry->name($lang->id) == '') {
                    $entry->name($lang->id, $country->countries_name);
                }
            }
            $entry->save();
        }
    }

}

