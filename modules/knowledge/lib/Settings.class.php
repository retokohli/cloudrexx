<?php

/**
 * This file holds the settings object for the knowledge module
 *
 * @author Stefan Heinemann <sh@comvation.com>
 * @copyright Comvation AG <info@comvation.com>
 * @package contrexx
 * @subpackage knowledge
 */

/**
 * The settings of the knowledge module
 *
 * @author Stefan Heinemann <sh@comvation.com>
 * @copyright Comvation AG <info@comvation.com>
 */
class KnowledgeSettings
{
    /**
     * The settings
     *
     * @var array
     */
    private $settings = array();

    /**
     * The name of the settings table
     *
     * @var string
     */
    private $table = "";

    /**
     * Read the settings
     */
    public function __construct()
    {
        $this->table = "module_knowledge_".MODULE_INDEX."settings";

        $this->readSettings();
    }

    /**
     * Get all settings
     *
     * @global $objDatabase
     * @throws DatabaseError
     */
    public function readSettings()
    {
        global $objDatabase;

        $query = "  SELECT name, value
                    FROM ".DBPREFIX.$this->table;

        $rs = $objDatabase->Execute($query);
        if ($rs === false) {
            throw new DatabaseError("failed to get settings");
        }

        foreach ($rs as $setting) {
            $this->settings[$setting['name']] = $setting['value'];
        }
    }

    /**
     * Return a value
     *
     * @param string $what
     * @return string
     */
    public function get($what)
    {
        return $this->settings[$what];
    }

    /**
     * Return all settings
     * @return array
     */
    public function getAll()
    {
        return $this->settings;
    }

    /**
     * Set a value
     *
     * If the value doesn't exist yet, create it
     * @param string $what
     * @param string $value
     * @global $objDatabase
     * @throws DatabaseError
     */
    public function set($what, $value)
    {
        global $objDatabase;

        $what = contrexx_addslashes($what);
        $value = contrexx_addslashes($value);

        if (!isset($this->settings[$what])) {
            $query = "  INSERT INTO ".DBPREFIX.$this->table."
                        (name, value)
                        VALUES
                        ('".$what."', '".$value."')";
        } else {
            $query = "  UPDATE ".DBPREFIX.$this->table."
                        SET value = '".$value."'
                        WHERE name = '".$what."'";
        }
        if ($objDatabase->Execute($query) === false) {
            throw new DatabaseError("");
        }
    }

    /**
     * Format the templates
     *
     * Replace the [[ ]] placeholder with {}
     * @param string $template
     * @return string
     */
    public function formatTemplate($template)
    {
        return preg_replace("/\[\[([A-Z_]+)\]\]/", '{$1}', $template);
    }

}

?>
