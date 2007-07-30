<?

/**
 * Ticket
 *
 * @copyright   CONTREXX CMS - COMVATION AG
 * @author      Reto Kohli <reto.kohli@comvation.com>
 * @version     0.0.1
 * @package     contrexx
 * @subpackage  module_support
 */

/*

Database Tables Structure:

CREATE TABLE contrexx_module_support_ticket (
  id int(10) unsigned NOT NULL auto_increment,
  `date` datetime NOT NULL,
  email varchar(255) NOT NULL,
  `status` int(11) NOT NULL,
  support_category_id int(10) unsigned NOT NULL,
  PRIMARY KEY  (id),
  KEY email (email),
  KEY `status` (`status`),
  KEY support_category_id (support_category_id)
) ENGINE=MyISAM;

*/


// Ticket status constant values
// UNKNOWN: The Ticket was found in a state other than those listed
// here, and has been reset to unknown state.  Someone needs to
// adjust its state manually now.
define('SHOP_SUPPORT_TICKET_STATUS_UNKNOWN', 0);
// NEW: The Ticket has been opened, but noone has seen it yet.
define('SHOP_SUPPORT_TICKET_STATUS_NEW',     1);
// OPEN: The Ticket is open, someone has already taken a look at it.
define('SHOP_SUPPORT_TICKET_STATUS_OPEN',    2);
// WAIT: The Ticket has been handled other than just looking at it,
// i.e. replied, moved.  It's now waiting for the next step to take place,
// like another reply or the other person accepting it.
define('SHOP_SUPPORT_TICKET_STATUS_WAIT',    3);
// CLOSED: The Ticket has been replied and is considered to be
// satisfactorily answered.
define('SHOP_SUPPORT_TICKET_STATUS_CLOSED',  4);
// More to come...
//define('SHOP_SUPPORT_TICKET_STATUS_', 0);
// Total number.  Keep this up to date!
define('SHOP_SUPPORT_TICKET_STATUS_COUNT',   5);

// Ticket action constant values
// UNKNOWN: Some Ticket action was found other than those listed
// here, and has been reset to unknown state.  The Ticket status
// should be UNKNOWN now, too.  Someone needs to take more action now.
define('SHOP_SUPPORT_TICKET_ACTION_UNKNOWN', 0);
// NONE: No action has been taken on this Ticket yet.
// If you see this, you probably should, as this Tickets' state
// must still be NEW!
define('SHOP_SUPPORT_TICKET_ACTION_NONE',    1);
// READ: Someone took a look at this Ticket.  The Ticket status
// must be OPEN now.
// Tickets with status WAIT or CLOSED should not be moved!
define('SHOP_SUPPORT_TICKET_ACTION_READ',    2);
// MOVE: Someone moved this Ticket to another Support Category,
// or to another person.
// If the Ticket status is WAIT, the other person hasn't seen it since.
// If the Ticket status is OPEN, the Support Category has been changed only.
define('SHOP_SUPPORT_TICKET_ACTION_MOVE',    3);
// CHANGE: Someone made changes other than the Support Category
// or the person in charge.
// After changing a Ticket, its status must be set to OPEN.
// Tickets with status WAIT or CLOSED should not be changed!
define('SHOP_SUPPORT_TICKET_ACTION_CHANGE',  4);
// REPLY: Someone has sent a reply to the Ticket.
// Tickets that have been replied must be set to status WAIT!
define('SHOP_SUPPORT_TICKET_ACTION_REPLY',   5);
// CLOSE: Someone has declared this Ticket closed.
// The Ticket state must now be CLOSED, and the Ticket should
// not be MOVEd or CHANGEd anymore!
define('SHOP_SUPPORT_TICKET_ACTION_CLOSE',   6);
// Total number.  Keep this up to date!
define('SHOP_SUPPORT_TICKET_ACTION_COUNT',   7);


/**
 * Ticket
 *
 * Every Support Ticket consists of one or more messages
 * The Ticket class is associated with one of the Support Categories.
 * Every Ticket has the following fields:
 *  id
 *  email       Either from the e-mail message, or the web form.
 *  date
 *  status      0 new, 1 open, 2 waiting, 127 closed.
 *  support_category_id
 * One or more Messages should be linked to the Ticket.
 * Attachments may be linked to Messages.
 * @copyright   CONTREXX CMS - COMVATION AG
 * @author      Reto Kohli <reto.kohli@comvation.com>
 * @version     0.0.1
 * @package     contrexx
 * @subpackage  module_support
 */

class Ticket
{
    /**
     * Ticket ID
     *
     * From table modules_support_ticket
     * @var integer
     */
    var $id;

    /**
     * Date
     *
     * From table modules_support_ticket
     * @var string
     */
    var $date;

    /**
     * E-Mail
     *
     * From table modules_support_ticket
     * @var string
     */
    var $email;

    /**
     * Status
     *
     * From table modules_support_ticket
     * @var integer
     */
    var $status;

    /**
     * Support Category associated with this Ticket
     *
     * From table modules_support_ticket
     * @var integer
     */
    var $supportCategoryId;

    /**
     * Language associated with this Ticket
     *
     * From table modules_support_ticket
     * @var integer
     */
    var $languageId;

    /**
     * Status strings
     * @var array
     */
    var $status;


    /**
     * Constructor (PHP4)
     * @copyright   CONTREXX CMS - COMVATION AG
     * @author      Reto Kohli <reto.kohli@comvation.com>
     * @version     0.0.1
     * @see         __construct()
     */
    function Ticket(
        $date, $email, $status, $supportCategoryId, $languageId, $id=0
    ) {
        $this->__construct(
            $date, $email, $status, $supportCategoryId, $languageId, $id
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
        $date, $email, $status, $supportCategoryId, $languageId, $id=0)
    {
        global $_ARRAYLANG;

        $this->date              = $date;
        $this->email             = $email;
        $this->status            = $status;
        $this->supportCategoryId = $supportCategoryId;
        $this->languageId        = $languageId;
        $this->id                = $id;

        // *SHOULD* be static
        $this->arrStatusString = array(
            SUPPORT_TICKET_STATUS_UNKNOWN => $_ARRAYLANG['TXT_SUPPORT_TICKET_STATUS_UNKNOWN'],
            SUPPORT_TICKET_STATUS_NEW     => $_ARRAYLANG['TXT_SUPPORT_TICKET_STATUS_NEW'],
            SUPPORT_TICKET_STATUS_OPEN    => $_ARRAYLANG['TXT_SUPPORT_TICKET_STATUS_OPEN'],
            SUPPORT_TICKET_STATUS_WAIT    => $_ARRAYLANG['TXT_SUPPORT_TICKET_STATUS_WAIT'],
            SUPPORT_TICKET_STATUS_CLOSED  => $_ARRAYLANG['TXT_SUPPORT_TICKET_STATUS_CLOSED'],
        );

        // *SHOULD* be static
        $this->arrActionString = array(
            SUPPORT_TICKET_ACTION_UNKNOWN => $_ARRAYLANG['TXT_SUPPORT_TICKET_ACTION_UNKNOWN'],
            SUPPORT_TICKET_ACTION_NONE    => $_ARRAYLANG['TXT_SUPPORT_TICKET_ACTION_NONE'],
            SUPPORT_TICKET_ACTION_READ    => $_ARRAYLANG['TXT_SUPPORT_TICKET_ACTION_READ'],
            SUPPORT_TICKET_ACTION_MOVE    => $_ARRAYLANG['TXT_SUPPORT_TICKET_ACTION_MOVE'],
            SUPPORT_TICKET_ACTION_CHANGE  => $_ARRAYLANG['TXT_SUPPORT_TICKET_ACTION_CHANGE'],
            SUPPORT_TICKET_ACTION_REPLY   => $_ARRAYLANG['TXT_SUPPORT_TICKET_ACTION_REPLY'],
            SUPPORT_TICKET_ACTION_CLOSE   => $_ARRAYLANG['TXT_SUPPORT_TICKET_ACTION_CLOSE'],
        );

    }


    /**
     * Get this Tickets' ID
     * @return  integer     The Ticket ID
     */
    function getId()
    {
        return $this->Id;
    }

    /**
     * Get this Tickets' date
     * @return  string      The Ticket date
     */
    function getDate()
    {
        return $this->date;
    }
    /**
     * Set this Tickets' date
     * @param   string      The Ticket date
     */
    function setDate($date)
    {
        $this->date = $date;
    }

    /**
     * Get this Tickets' e-mail address
     * @return  string      The Ticket e-mail address
     */
    function getEmail()
    {
        return $this->email;
    }
    /**
     * Set this Tickets' e-mail address
     * @param   string      The Ticket e-mail address
     */
    function setEmail($email)
    {
        $this->email = strip_tags($email);
    }

    /**
     * Get this Tickets' status
     * @return  integer     The Ticket status
     */
    function getStatus()
    {
        return $this->status;
    }
    /**
     * Set this Tickets' status
     * @param   string      The Ticket status
     */
    function setStatus($status)
    {
        $this->status = intval($status);
    }

    /**
     * Get this Tickets' SupportCategory ID
     * @return  integer     The Ticket SupportCategory ID
     */
    function getSupportCategoryId()
    {
        return $this->supportCategoryId;
    }
    /**
     * Set this Tickets' SupportCategory ID
     * @param   integer     The Ticket SupportCategory ID
     */
    function setSupportCategoryId($supportCategoryId)
    {
        $this->supportCategoryId = intval($supportCategoryId);
    }

    /**
     * Get this Tickets' language ID
     * @return  integer     The Ticket language ID
     */
    function getLanguageId()
    {
        return $this->languageId;
    }
    /**
     * Set this Tickets' language ID
     * @param   integer     The Ticket language ID
     */
    function setLanguageId($languageId)
    {
        $this->languageId = intval($languageId);
    }


    /**
     * Get this Tickets' status as a string
     * @return  string      The Ticket status string
     */
    function getStatusString()
    {
        return $this->statusStrings[$this->status];
    }


    /**
     * Delete this Ticket from the database.
     * @return      boolean                     True on success, false otherwise
     * @global      mixed       $objDatabase    Database object
     * @copyright   CONTREXX CMS - COMVATION AG
     * @author      Reto Kohli <reto.kohli@comvation.com>
     */
    function delete()
    {
        global $objDatabase;
//echo("Debug: Ticket::delete(): entered<br />");

        if (!$this->id) {
echo("Ticket::delete(): Error: This Ticket is missing the Ticket ID<br />");
            return false;
        }
        $objResult = $objDatabase->Execute("
            DELETE FROM ".DBPREFIX."module_support_ticket
             WHERE id=$this->id
        ");
        if (!$objResult) {
echo("Ticket::delete(): Error: Failed to delete the Ticket from the database<br />");
            return false;
        }
        return true;
    }


    /**
     * Stores this Ticket in the database.
     *
     * Either updates (id > 0) or inserts (id == 0) the object.
     * @return      boolean     True on success, false otherwise
     * @copyright   CONTREXX CMS - COMVATION AG
     * @author      Reto Kohli <reto.kohli@comvation.com>
     */
    function store()
    {
        if ($this->id > 0) {
            return $this->update();
        }
        return $this->insert();
    }


    /**
     * Update this Ticket in the database.
     * @return      boolean                     True on success, false otherwise
     * @global      mixed       $objDatabase    Database object
     * @copyright   CONTREXX CMS - COMVATION AG
     * @author      Reto Kohli <reto.kohli@comvation.com>
     */
    function update()
    {
        global $objDatabase;

        $query = "
            UPDATE ".DBPREFIX."module_support_ticket
               SET 'date'='".contrexx_addslashes($this->date)."',
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
//echo("Ticket::update(): done<br />");
        return true;
    }


    /**
     * Insert this Ticket into the database.
     * @return      boolean                     True on success, false otherwise
     * @global      mixed       $objDatabase    Database object
     * @copyright   CONTREXX CMS - COMVATION AG
     * @author      Reto Kohli <reto.kohli@comvation.com>
     */
    function insert()
    {
        global $objDatabase;

        $query = "
            INSERT INTO ".DBPREFIX."module_support_ticket (
                   'date',
                   email,
                   'status',
                   support_category_id,
                   language_id
            ) VALUES (
                   $this->date,
                   $this->email,
                   $this->status,
                   $this->supportCategoryId,
                   $this->languageId
            )
        ";
        $objResult = $objDatabase->Execute($query);
        if (!$objResult) {
            return false;
        }
        $this->id = $objDatabase->Insert_ID();
//echo("Ticket::insert(): done<br />");
        return true;
    }


    /**
     * Select a Ticket by ID from the database.
     * @static
     * @param       integer     $id             The Ticket ID
     * @return      Ticket                      The Ticket object
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
              FROM ".DBPREFIX."module_support_ticket
             WHERE id=$id
        ";
echo("Ticket::getById($id): query: $query<br />");
        $objResult = $objDatabase->Execute($query);
echo("Ticket::getById($id): objResult: '$objResult'<br />");
        if (!$objResult) {
echo("Ticket::getById($id): query failed, objResult: '$objResult', count: ".$objResult->RecordCount()."<br />");
            return false;
        }
        if ($objResult->RecordCount() == 0) {
echo("Ticket::getById($id): no result: ".$objResult->RecordCount()."<br />");
            return false;
        }
        $objTicket = new Ticket(
            $objResult->fields('date'),
            $objResult->fields('email'),
            $objResult->fields('status'),
            $objResult->fields('support_category_id'),
            $objResult->fields('language_id'),
            $objResult->fields('id')
        );
        return $objTicket;
    }


    /**
     * Returns an array of Ticket objects from the database.
     *
     * The array size is limited by the global paging size limit setting.
     * The optional parameter $order determines the sorting order
     * in SQL syntax, it defaults to ordered by date, latest first.
     * The optional parameter $offset determines the offset of the
     * first Ticket to be read from the database.
     * @static
     * @param       string      $order          The sorting order
     * @param       integer     $offset         The offset
     * @return      array                       The array of Ticket objects
     * @global      mixed       $objDatabase    Database object
     * @global      array       $_CONFIG        Global configuration array
     *                                          on success, false otherwise
     * @copyright   CONTREXX CMS - COMVATION AG
     * @author      Reto Kohli <reto.kohli@comvation.com>
     */
    //static
    function getTicketArray($order="'date' DESC", $offset=0)
    {
        global $objDatabase, $_CONFIG;

        $query = "
            SELECT id
              FROM ".DBPREFIX."module_support_ticket
          ORDER BY $order
        ";
        $objResult = $objDatabase->SelectLimit(
            $query, $_CONFIG['corePagingLimit'], $offset
        );
        if (!$objResult) {
            return false;
        }
        // return array
        $arrTicket = array();
        while (!$objResult->EOF) {
            $arrTicket[] = Ticket::getById($objResult->fields['id']);
            $objResult->MoveNext();
        }
        return $arrTicket;
    }
}

?>
