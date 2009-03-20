<?php


class CalendarSettings
{
    /**
     * If the Settings are already read
     *
     * @var boolean
     */
    private $settingsAreRead = false;

    private $settings = array();

    /**
     * The Name of the module. The database table name will therefore be
     * contrexx_module_modulename_settings
     *
     * @var string
     */
    private $modulename = "calendar";

    public function __construct() {}

    /**
     * Get a setting
     *
     * Return a specifig setting. If the settings are not read yet, read
     * them out first.
     * @param string $what
     * @return string
     */
    public function get($what)
    {
        if (!$this->settingsAreRead) {
            $this->readSettings();
        }
        return $this->settings[$what];
    }

    /**
     * Read the settings out of the db
     *
     */
    public function readSettings()
    {
        global $objDatabase;

        $query = "  SELECT setname, setvalue
                    FROM ".DBPREFIX."module_".$this->modulename."_settings";
        $result = $objDatabase->Execute($query);
        if ($result === false) {
            throw new DatabaseError("can't read settings");
        } else {
            if ($result->RecordCount()) {
                while (!$result->EOF) {
                    $this->settings[$result->fields['setname']] = $result->fields['setvalue'];

                    $result->MoveNext();
                }
            }
        }
        $this->settingsAreRead = true;
    }

    /**
     * Set a setting
     *
     * @param string $what
     * @param string $value
     */
    public function set($what, $value)
    {
        global $objDatabase;

        if (!$this->settingsAreRead) {
            $this->readSettings();
        }

        $what = contrexx_addslashes($what);
        $value = contrexx_addslashes($value);

        if (!isset($this->settings[$what])) {
            $query = "  INSERT INTO ".DBPREFIX."module_".$this->modulename."_settings
                        (setname, setvalue)
                        VALUES
                        ('".$what."', '".$value."')";
        } else {
            $query = "  UPDATE ".DBPREFIX."module_".$this->modulename."_settings
                        SET setvalue = '".$value."'
                        WHERE setname = '".$what."'";
        }
        if ($objDatabase->Execute($query) === false) {
            throw new DatabaseError("error setting a setting");
        }
    }

}