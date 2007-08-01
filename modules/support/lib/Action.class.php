<?

/**
 * Action taken on Support Tickets
 *
 * @copyright   CONTREXX CMS - COMVATION AG
 * @author      Reto Kohli <reto.kohli@comvation.com>
 * @version     0.0.1
 * @package     contrexx
 * @subpackage  module_support
 */

/*

Database Table Structure:

DROP TABLE contrexx_module_support_action;
CREATE TABLE contrexx_module_support_action (
  id            int(10)     unsigned NOT NULL auto_increment,
  `event`       tinyint(2)  unsigned NOT NULL default  0,
  foreign_id    int(10)     unsigned NOT NULL default  0,
  `table`       varchar(32)          NOT NULL default '',
  `field`       varchar(32)          NOT NULL default '',
  `value`       varchar(16)          NOT NULL default '',
  `timestamp`   timestamp            NOT NULL default current_timestamp,
  PRIMARY KEY    (id),
  KEY foreign_id (foreign_id),
  KEY `timestamp`     (`timestamp`)
) ENGINE=MyISAM;

*/

/**
 * Support Ticket Action
 *
 * Every Action consists of one database entry and
 * is associated with one of the Support Tickets.
 * The Action object is INSERTed into the database table upon
 * creation, and may not be updated (or changed) in any way.
 * It may be deleted, however, though this *SHOULD* only be done
 * when the associated Ticket is deleted.
 * @copyright   CONTREXX CMS - COMVATION AG
 * @author      Reto Kohli <reto.kohli@comvation.com>
 * @version     0.0.1
 * @package     contrexx
 * @subpackage  module_support
 */
class Action
{
    /**
     * Action ID
     *
     * From table modules_support_action
     * @var integer
     */
    var $id;

    /**
     * The code for the event taken
     *
     * From table modules_support_action
     * @var integer
     */
    var $event;

    /**
     * The associated foreign key (ID)
     *
     * From table modules_support_action
     * @var integer
     */
    var $foreignId;

    /**
     * The name of the table affected by the Action taken
     *
     * From table modules_support_action
     * @var string
     */
    var $table;

    /**
     * The field name affected by the Action
     *
     * From table modules_support_action
     * @var string
     */
    var $field;

    /**
     * The new value changed by the action taken
     *
     * From table modules_support_action
     * @var string
     */
    var $value;

    /**
     * The timestamp of the event being processed and the action taken
     *
     * Note that according to the definition of the timestamp field in the
     * database table, this value *MUST* not be updated (or changed).
     * It is set to the current date and time only once, when the Action
     * is INSERTed into the table.
     * From table modules_support_action
     * @var string
     */
    var $timestamp;


    /**
     * Constructor (PHP4)
     * @copyright   CONTREXX CMS - COMVATION AG
     * @author      Reto Kohli <reto.kohli@comvation.com>
     * @version     0.0.1
     * @see         __construct()
     */
    function Action(
        $event, $foreignId, $table, $field, $value, $timestamp='', $id=0
    ) {
        $this->__construct(
            $event, $foreignId, $table, $field, $value, $timestamp, $id
        );
    }

    /**
     * Constructor (PHP5)
     * @global      array   $_ARRAYLANG     Language array
     * @copyright   CONTREXX CMS - COMVATION AG
     * @author      Reto Kohli <reto.kohli@comvation.com>
     * @version     0.0.1
     * @todo        PHP5: Make $this->arrStatusString and
     *                         $this->arrActionString static!
     */
    function __construct(
        $event, $foreignId, $table, $field, $value, $timestamp='', $id=0
    ) {
        if (!$this->foreignId) {
echo("Action::__construct(): No foreign ID!<br />;");
            exit;
        }

        $this->event     = intval($event);
        $this->foreignId = intval($foreignId);
        $this->table     = $table;
        $this->field     = $field;
        $this->value     = $value;
        $this->timestamp = $timestamp;
        $this->id        = $id;

        // If it's not an Action read from the database but a brand new
        // one, store it immediately!
        if (!$this->id) {
            $this->insert();
        }
    }


    /**
     * Get this Actions' ID
     * @return  integer     The Action ID
     */
    function getId()
    {
        return $this->Id;
    }

    /**
     * Get the event code from this Action
     * @return  integer     The event code
     */
    function getEvent()
    {
        return $this->event;
    }
    /**
     * Set the event code for this Action
     * @param   integer     The event code
    function setEvent($event)
    {
        $this->event = intval($event);
    }
     */

    /**
     * Get the foreign key from this Action
     * @return  integer     The foreign key
     */
    function getForeignId()
    {
        return $this->foreignId;
    }
    /**
     * Set the foreign key for this Action
     * @param   integer     The foreign key
    function setForeignId($foreignId)
    {
        $this->foreignId = intval($foreignId);
    }
     */

    /**
     * Get the table affected by this Action
     * @return  string      The table name
     */
    function getTable()
    {
        return $this->table;
    }
    /**
     * Set the table affected by this Action
     * @param   string      The table name
    function setTable($table)
    {
        $this->table = $table;
    }
     */

    /**
     * Get the field name from this Action
     * @return  string      The field name
     */
    function getField()
    {
        return $this->field;
    }
    /**
     * Set the field name for this Action
     * @param   string      The field name
    function setField($field)
    {
        $this->field = $field;
    }
     */

    /**
     * Get the value changed by this Action
     * @return  string      The changed value
     */
    function getValue()
    {
        return $this->value;
    }
    /**
     * Set the value changed by this Action
     * @param   string      The changed value
    function setValue($value)
    {
        $this->value = $value;
    }
     */

    /**
     * Get this Actions' timestamp
     * @return  string      The Action timestamp
     */
    function getDate()
    {
        return $this->timestamp;
    }
    /**
     * Set this Actions' timestamp
     * @param   string      The Action timestamp
    function setDate($timestamp)
    {
        $this->timestamp = $timestamp;
    }
     */


    /**
     * Delete this Action from the database.
     * @return      boolean                     True on success, false otherwise
     * @global      mixed       $objDatabase    Database object
     * @copyright   CONTREXX CMS - COMVATION AG
     * @author      Reto Kohli <reto.kohli@comvation.com>
     */
    function delete()
    {
        global $objDatabase;
//echo("Debug: Action::delete(): entered<br />");

        if (!$this->id) {
echo("Action::delete(): ERROR: This Action is missing the Action ID!<br />");
            return false;
        }
        $objResult = $objDatabase->Execute("
            DELETE FROM ".DBPREFIX."module_support_action
             WHERE id=$this->id
        ");
        if (!$objResult) {
echo("Action::delete(): Error: Failed to delete the Action from the database<br />");
            return false;
        }
        return true;
    }


    /**
     * Stores this Action in the database.
     *
     * Either updates (id > 0) or inserts (id == 0) the object.
     * @return      boolean     True on success, false otherwise
     * @copyright   CONTREXX CMS - COMVATION AG
     * @author      Reto Kohli <reto.kohli@comvation.com>
    function store()
    {
        if ($this->id > 0) {
echo("Action::store(): WARNING: someone is trying to UPDATE an Action record! -- Bailing out<br />");
            return false;
        }
        return $this->insert();
    }
     */


    /**
     * Update this Action in the database.
     * @return      boolean                     True on success, false otherwise
     * @global      mixed       $objDatabase    Database object
     * @copyright   CONTREXX CMS - COMVATION AG
     * @author      Reto Kohli <reto.kohli@comvation.com>
    function update()
    {
        global $objDatabase;

        $query = "
            UPDATE ".DBPREFIX."module_support_action
               SET 'timestamp'='".contrexx_addslashes($this->timestamp)."',
                   email=$this->email,
                   'status'=$this->status,
                   support_category_id=$this->supportCategoryId,
                   language_id=$this->languageId,
             WHERE id=$this->id
        ";
        $objResult = $objDatabase->Execute($query);
        if (!$objResult) {
            return false;
        }
//echo("Action::update(): done<br />");
        return true;
    }
     */


    /**
     * Insert this Action into the database.
     * @return      boolean                     True on success, false otherwise
     * @global      mixed       $objDatabase    Database object
     * @copyright   CONTREXX CMS - COMVATION AG
     * @author      Reto Kohli <reto.kohli@comvation.com>
     */
    function insert()
    {
        global $objDatabase;

        $query = "
            INSERT INTO ".DBPREFIX."module_support_action (
                   foreign_id, 'event', 'table', 'field', 'value'
            ) VALUES (
                   $this->foreignId,
                   $this->event,
                   $this->table,
                   $this->field,
                   $this->value
            )
        ";
        $objResult = $objDatabase->Execute($query);
        if (!$objResult) {
            return false;
        }
        $this->id = $objDatabase->Insert_ID();
        // Update the object with the actual timestamp
//echo("Action::insert(): done<br />");
        return $this->refreshTimestamp();
    }


    /**
     * Updates the Action object with the timestamp value stored in the
     * database.
     *
     * This *MUST* be called by insert() after INSERTing any new
     * Action object!
     * @return  boolean         True on success, false otherwise.
     */
    function refreshTimestamp()
    {
        global $objDatabase;

        $query = "
            SELECT 'timestamp'
              FROM ".DBPREFIX."module_support_action
             WHERE id=$this->id
        ";
echo("Action::refreshTimestamp(): query: $query<br />");
        $objResult = $objDatabase->Execute($query);
echo("Action::refreshTimestamp(): objResult: '$objResult'<br />");
        if (!$objResult) {
echo("Action::refreshTimestamp(): query failed, objResult: '$objResult', count: ".$objResult->RecordCount()."<br />");
            return false;
        }
        if ($objResult->RecordCount() == 0) {
echo("Action::refreshTimestamp(): no result: ".$objResult->RecordCount()."<br />");
            return false;
        }
        $this->timestamp = $objResult->fields('timestamp');
        return true;
    }


    /**
     * Select an Action by ID from the database.
     * @static
     * @param       integer     $id             The Action ID
     * @return      Action                      The Action object
     *                                          on success, false otherwise
     * @global      mixed       $objDatabase    Database object
     * @copyright   CONTREXX CMS - COMVATION AG
     * @author      Reto Kohli <reto.kohli@comvation.com>
     */
    //static
    function getById($id)
    {
        global $objDatabase;

        $query = "
            SELECT *
              FROM ".DBPREFIX."module_support_action
             WHERE id=$id
        ";
echo("Action::getById($id): query: $query<br />");
        $objResult = $objDatabase->Execute($query);
echo("Action::getById($id): objResult: '$objResult'<br />");
        if (!$objResult) {
echo("Action::getById($id): query failed, objResult: '$objResult', count: ".$objResult->RecordCount()."<br />");
            return false;
        }
        if ($objResult->RecordCount() == 0) {
echo("Action::getById($id): no result: ".$objResult->RecordCount()."<br />");
            return false;
        }
        $objAction = new Action(
            $objResult->fields('event'),
            $objResult->fields('foreign_id'),
            $objResult->fields('table'),
            $objResult->fields('field'),
            $objResult->fields('value'),
            $objResult->fields('timestamp'),
            $objResult->fields('id')
        );
        return $objAction;
    }


    /**
     * Returns an array of Action objects related to a certain
     * foreign ID, table, and/or field from the database.
     *
     * Any of the mandatory arguments may contain values evaluating to
     * the boolean false value, in which case they are not considered
     * in the WHERE clause.  Any such arguments that evaluate to true,
     * however, limit the result set to records having identical values.
     * The optional parameter $order determines the sorting order
     * in SQL syntax, it defaults to ordered by date descending, or
     * latest first.
     * The optional parameter $offset determines the offset of the
     * first Action to be read from the database, and defaults to 0 (zero).
     * The optional parameter $limit limits the number of results.
     * It defaults to the value of the global $_CONFIG['corePagingLimit']
     * setting if unset or zero.
     * @static
     * @param       integer     $event          The desired event code, or zero
     * @param       integer     $foreignId      The desired foreign ID, or zero
     * @param       string      $table          The desired table name, or the
     *                                          empty string
     * @param       string      $field          The desired field name, or the
     *                                          empty string
     * @param       string      $value          The desired field value, or the
     *                                          empty string
     * @param       string      $order          The sorting order
     * @param       integer     $offset         The offset
     * @return      array                       The array of Action objects
     * @global      mixed       $objDatabase    Database object
     * @global      array       $_CONFIG        Global configuration array
     *                                          on success, false otherwise
     * @copyright   CONTREXX CMS - COMVATION AG
     * @author      Reto Kohli <reto.kohli@comvation.com>
     */
    //static
    function getActionArray(
        $event, $foreignId, $table, $field, $value,
        $order="'timestamp' DESC", $offset=0, $limit=0
    ) {
        global $objDatabase, $_CONFIG;

        $limit = ($limit ? $limit : $_CONFIG['corePagingLimit']);
        $query = "
            SELECT id
              FROM ".DBPREFIX."module_support_action
             WHERE 1
              ".($event     ? "AND event=$event"          : '')."
              ".($foreignId ? "AND foreign_id=$foreignId" : '')."
              ".($table     ? "AND table='$table'"        : '')."
              ".($field     ? "AND field='$field'"        : '')."
              ".($value     ? "AND value='$value'"        : '')."
          ORDER BY $order
        ";
        $objResult = $objDatabase->SelectLimit($query, $limit, $offset);
        if (!$objResult) {
            return false;
        }
        // return array
        $arrAction = array();
        while (!$objResult->EOF) {
            $arrAction[] = Action::getById($objResult->fields['id']);
            $objResult->MoveNext();
        }
        return $arrAction;
    }
}

?>
