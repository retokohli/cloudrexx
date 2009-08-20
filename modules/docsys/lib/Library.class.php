<?php
/**
 * Class Document System
 *
 * @copyright   CONTREXX CMS - COMVATION AG
 * @author Comvation Development Team <info@comvation.com>
 * @access public
 * @version 1.0.0
 * @package     contrexx
 * @subpackage  module_docsys
 * @todo        Edit PHP DocBlocks!
 */

/**
 * Class Document System
 *
 * @copyright   CONTREXX CMS - COMVATION AG
 * @author Comvation Development Team <info@comvation.com>
 * @access public
 * @version 1.0.0
 * @package     contrexx
 * @subpackage  module_docsys
 * @todo        Edit PHP DocBlocks!
 */
class docSysLibrary
{
    /**
    * Gets the categorie option menu string
    *
    * @global    ADONewConnection
    * @param     string     $lang
    * @param     string     $selectedOption
    * @return    string     $modulesMenu
    * @todo         whats this cmdName for?
    */
    function getCategoryMenu($langId, $selectedCatIds=array(), $cmdName=false)
    {
        global $objDatabase;

        $strMenu = "";
		!$cmdName ? $query_where = '' : $query_where = " AND cmd='".$cmdName."'";

        $query="SELECT catid,
                       name
                  FROM ".DBPREFIX."module_docsys".MODULE_INDEX."_categories
                 WHERE lang=".$langId.$query_where."
              ORDER BY catid";

        $objResult = $objDatabase->Execute($query);

        while (!$objResult->EOF) {
            if (array_search($objResult->fields['catid'], $selectedCatIds) !== false) {
                $selected = "selected";
            } else {
                $selected = "";
            }
            $strMenu .="<option value=\"".$objResult->fields['catid']."\" $selected>".stripslashes($objResult->fields['name'])."</option>\n";
            $objResult->MoveNext();
        }
        return $strMenu;
    }

    /**
     * Get all categories of a entry
     *
     * @param int $id
     * @author Stefan Heinemann <sh@comvation.com>
     */
    protected function getCategories($id)
    {
        global $objDatabase;

        $id = intval($id);

        $query = "  SELECT category FROM ".DBPREFIX."module_docsys".MODULE_INDEX."_entry_category
                    WHERE entry = ".$id;
        $result = $objDatabase->Execute($query);
        if ($result === false) {
            return false;
        } else {
            $retval = array();
            if ($result->RecordCount()) {
                while (!$result->EOF) {
                    $retval[] = $result->fields['category'];
                    $result->MoveNext();
                }
            }

            return $retval;
        }
    }

    /**
     * Return all entries
     *
     * Return all entries with their categorie names and user name
     * @param int $pos the position to start with (vor paging)
     * @return array
     * @author Stefan Heinemann <sh@comvation.com>
     */
    protected function getAllEntries($pos)
    {
        global $objDatabase, $_CONFIG;

        $query = "  SELECT entry.id, entry.date, entry.author, entry.title, entry.status, entry.changelog, cat.name as catname, users.username
                    FROM ".DBPREFIX."module_docsys".MODULE_INDEX." as entry
                    LEFT JOIN ".DBPREFIX."module_docsys".MODULE_INDEX."_entry_category as joined ON entry.id = joined.entry
                    LEFT JOIN ".DBPREFIX."module_docsys".MODULE_INDEX."_categories as cat ON joined.category = cat.catid
                    LEFT JOIN ".DBPREFIX."access_users as users ON entry.userid = users.id
                    WHERE entry.lang = ".$this->langId."
                    ORDER BY entry.id";

        $result = $objDatabase->SelectLimit($query, $_CONFIG['corePagingLimit'], intval($pos));
        if ($result === false) {
            return false;
        } else{
            $retval = array();
            if ($result->RecordCount()) {
                while (!$result->EOF) {
                    if (array_key_exists($result->fields['id'], $retval)) {
                        $retval[$result->fields['id']]['categories'][] = $result->fields['catname'];
                    } else {
                        $retval[$result->fields['id']] = array(
                            "id"        => $result->fields['id'],
                            "date"      => $result->fields['date'],
                            "author"    => $result->fields['author'],
                            "title"     => $result->fields['title'],
                            "status"    => $result->fields['status'],
                            "changelog" => $result->fields['changelog'],
                            "username"  => $result->fields['username'],
                            "categories" => array($result->fields['catname'])
                        );
                    }
                    $result->MoveNext();
                }
            }

            return $retval;
        }

    }

    /**
     * Count all entries (for paging)
     *
     * @return int
     * @author Stefan Heinemann <sh@comvation.com>
     */
    protected function countAllEntries()
    {
        global $objDatabase;

        $query = "  SELECT count(id) as count FROM ".DBPREFIX."module_docsys".MODULE_INDEX."
                    WHERE lang = ".$this->langId;
        $result = $objDatabase->Execute($query);
        if ($result === false) {
            return false;
        }

        return intval($result->fields['count']);
    }

    /**
     * Assign categories to an entry
     *
     * @param int $entry
     * @param array $categories Array of integers of the categories' ids
     * @return boolean Success
     * @author Stefan Heinemann <sh@comvation.com>
     */
    protected function assignCategories($entry, $categories)
    {
        global $objDatabase;

        $entry = intval($entry);

        $err = false;
        foreach ($categories as $cat) {
            $query = "  INSERT INTO ".DBPREFIX."module_docsys".MODULE_INDEX."_entry_category
                        (entry, category)
                        VALUES (".$entry.", ".intval($cat).")";
            if ($objDatabase->Execute($query) === false) {
                $err = true;
            }
        }

        return !$err;
    }

    /**
     * Remove an entry's categories
     *
     * @param int $entry
     * @return boolean
     * @author Stefan Heinemann <sh@comvation.com>
     */
    protected function removeCategories($entry)
    {
        global $objDatabase;

        $entry = intval($entry);

        $query = "  DELETE FROM ".DBPREFIX."module_docsys".MODULE_INDEX."_entry_category
                    WHERE entry = ".$entry;
        return !($objDatabase->Execute($query) === false);
    }

    /**
     * Get the entries for the frontend overview, according to the selected category
     *
     * @param int $pos Position for limiting/paging
     * @param int $category The category to be shown
     * @param string $sortType Some sorting stuff
     * @return bool/array An array with entries on success, else the boolean false
     * @author Stefan Heinemann <sh@comvation.com>
     * @see docSys::getTitles
     */
    protected function getOverviewTitles($pos=0, $category=null, $sortType=null)
    {
        global $objDatabase, $_CONFIG;

        if (isset($category) && !isset($sortType)) {
            throw new Exception("second argument needed");
        }

        $query = "  SELECT entry.date, entry.id, entry.title, entry.author, cat.name
                    FROM ".DBPREFIX."module_docsys".MODULE_INDEX." as entry
                    LEFT JOIN ".DBPREFIX."module_docsys".MODULE_INDEX."_entry_category as j
                        ON entry.id = j.entry
                    LEFT JOIN ".DBPREFIX."module_docsys".MODULE_INDEX."_categories as cat
                        ON j.category = cat.catid
                    WHERE entry.lang = ".$this->langId."
                        AND (startdate<=".time()." OR startdate=0)
                        AND (enddate>=".time()." OR enddate=0)";

        if (isset($category)) {
            $category = intval($category);
            $query .= " AND cat.catid = ".$category." ";

            switch($sortType){
                case 'alpha':
                    $query .= " ORDER BY entry.title";
                break;

                case 'date':
                    $query .= " ORDER BY entry.date DESC";
                break;

                case 'date_alpha':
                    $query .= " ORDER BY DATE_FORMAT( FROM_UNIXTIME( `date` ) , '%Y%j' ) DESC, entry.title";
                break;

                default:
                    $query .= " ORDER BY entry.date DESC";
            }
        } else{
            $query .= " ORDER BY entry.date DESC";
        }

        $result = $objDatabase->SelectLimit($query, $_CONFIG['corePagingLimit'], $pos);
        if ($result === false) {
            return false;
        } else{
            $retval = array();
            if ($result->RecordCount()) {
                while (!$result->EOF) {
                    if (array_key_exists($result->fields['id'], $retval)) {
                        $retval[$result->fields['id']]['categories'][] = $result->fields['name'];
                    } else {
                        $retval[$result->fields['id']] = array(
                            "id"        => $result->fields['id'],
                            "date"      => $result->fields['date'],
                            "title"     => $result->fields['title'],
                            "author"    => $result->fields['author'],
                            "name"      => $result->fields['name'],
                            "categories"    => array($result->fields['name'])
                        );
                    }

                    $result->MoveNext();
                }
            }

            return $retval;
        }
    }

    /**
     * Count the entries for a specific category
     *
     * Count the entries for a specific category. If no category is given (0)
     * all entries ar counted. This is used for paging.
     * @param int $category
     * @return int/boolean Amount of entries on success, else false
     * @author Stefan Heinemann <sh@comvation.com>
     * @see docSys::getTitles
     */
    protected function countOverviewEntries($category=null)
    {
        global $objDatabase;

        if (!isset($category)) {
            return $this->countAllEntries();
        }

        $category = intval($category);
        $query = "  SELECT count(id) as count FROM ".DBPREFIX."module_docsys".MODULE_INDEX." as e
                    LEFT JOIN ".DBPREFIX."module_docsys".MODULE_INDEX."_entry_category as j
                    ON e.id = j.entry
                    WHERE j.category = ".$category;
        $result = $objDatabase->Execute($query);
        if ($result === false) {
            return false;
        }

        return intval($result->fields['count']);
    }
}

?>
