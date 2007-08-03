<?php

/**
 * The state-event diagram for the Ticket:
 * (The default Status of any new Tickets is, of course, NEW.)
 *
 * \EVENT  | UNKNOWN | NONE | READ | M_CAT | M_PER | M_OTH | REPLY | MESSAGE | CLOSE |
 * STATUS\ |         |      |      |       |       |       |       |         |       |
 * UNKNOWN |   -/U   | -/U  | -/O  |  -/U  | -/U   |  -/U  |  -/U  |   F/U   |  -/U  |
 * NEW     |   -/U   | -/N  | A/O* |  -/U  | -/U   |  -/N  |  -/U  |   F/N   |  -/U  |
 * OPEN    |   -/U   | -/O  | -/O  |  S/O  | B/M***|  -/O  |  R/W  |   F/N   |  C/C  |
 * WAIT    |   -/U   | -/W  | -/W  |  -/U  | -/U   |  -/U  |  -/U  |   F/N   |  -/U  |
 * MOVED   |   -/U   | -/M  | A/O**|  -/U  | B/M***|  -/M  |  -/U  |   F/N   |  -/U  |
 * CLOSED  |   -/U   | -/C  | -/C  |  -/U  | -/U   |  -/U  |  -/U  |   N/C   |  -/C  |
 *            \____________________ ACTION/NEW_STATUS _____________________________/
 *
 * Actions are:
 * -    No Action is performed.
 * A    Assign ownership of the Ticket to the reader.
 * B    Assign ownership of the Ticket to the person chosen (personId).
 * C    Close a Ticket.  Update everything related.
 * F    Add the followup message to the Ticket (in the Message table)
 *      and update all affected data.  The Ticket status will be reset
 *      to NEW, except when it was UNKNOWN before.  In that case, it
 *      won't be changed. (messageId)
 * S    Modify the Ticket Support Category (supportCategoryId)
 * N    A new Ticket will automagically be created, with a reference to the
 *      previous one.  Caused by a MESSAGE to an already closed Ticket.
 *      (messageId)
 * R    Reply to the Ticket: store and send the reply, update the KB and all
 *      affected tables. (messageId)
 *
 * Notes:
 * *  : New Tickets are not assigned to any person.  Anybody reading it first
 *      will take ownership for it.
 * ** : The ownership will be taken and the Ticket status set to OPEN only if
 *      read by the person it has been assigned to.
 * ***: The Ticket can be taken over from anyone willing to process it
 *      further.  Once they own it, the status will be set to OPEN when they
 *      view it.
 *
 * Note that everything in this file *SHOULD* of course be static.
 */

/**
 * The State-Event-Action Matrix
 */
$this->arrSea = array(
    SUPPORT_TICKET_STATUS_UNKNOWN => array(
        SUPPORT_TICKET_EVENT_UNKNOWN         => array(
            'action' => 'actionNone()',
            'status' => 'SUPPORT_TICKET_STATUS_UNKNOWN'),
        SUPPORT_TICKET_EVENT_READ            => array(
            'action' => 'actionNone()',
            'status' => 'SUPPORT_TICKET_STATUS_OPEN'),
        SUPPORT_TICKET_EVENT_CHANGE_CATEGORY => array(
            'action' => 'actionNone()',
            'status' => 'SUPPORT_TICKET_STATUS_UNKNOWN'),
        SUPPORT_TICKET_EVENT_CHANGE_PERSON   => array(
            'action' => 'actionNone()',
            'status' => 'SUPPORT_TICKET_STATUS_UNKNOWN'),
        SUPPORT_TICKET_EVENT_CHANGE_OTHER    => array(
            'action' => 'actionNone()',
            'status' => 'SUPPORT_TICKET_STATUS_UNKNOWN'),
        SUPPORT_TICKET_EVENT_REPLY           => array(
            'action' => 'actionNone()',
            'status' => 'SUPPORT_TICKET_STATUS_UNKNOWN'),
        SUPPORT_TICKET_EVENT_MESSAGE         => array(
            'action' => 'actionNone()',
            'status' => 'SUPPORT_TICKET_STATUS_UNKNOWN'),
        SUPPORT_TICKET_EVENT_CLOSE           => array(
            'action' => 'actionNone()',
            'status' => 'SUPPORT_TICKET_STATUS_UNKNOWN'),
    ),
    SUPPORT_TICKET_STATUS_NEW     => array(
        SUPPORT_TICKET_EVENT_UNKNOWN         => array(
            'action' => 'actionNone()',
            'status' => 'SUPPORT_TICKET_STATUS_UNKNOWN'),
        SUPPORT_TICKET_EVENT_READ            => array(
            'action' => 'actionAssignToReader()',
            'status' => 'SUPPORT_TICKET_STATUS_OPEN'),
        SUPPORT_TICKET_EVENT_CHANGE_CATEGORY => array(
            'action' => 'actionNone()',
            'status' => 'SUPPORT_TICKET_STATUS_UNKNOWN'),
        SUPPORT_TICKET_EVENT_CHANGE_PERSON   => array(
            'action' => 'actionNone()',
            'status' => 'SUPPORT_TICKET_STATUS_UNKNOWN'),
        SUPPORT_TICKET_EVENT_CHANGE_OTHER    => array(
            'action' => 'actionNone()',
            'status' => 'SUPPORT_TICKET_STATUS_NEW'),
        SUPPORT_TICKET_EVENT_REPLY           => array(
            'action' => 'actionNone()',
            'status' => 'SUPPORT_TICKET_STATUS_UNKNOWN'),
        SUPPORT_TICKET_EVENT_MESSAGE         => array(
            'action' => 'actionFollowup($messageId)',
            'status' => 'SUPPORT_TICKET_STATUS_NEW'),
        SUPPORT_TICKET_EVENT_CLOSE           => array(
            'action' => 'actionNone()',
            'status' => 'SUPPORT_TICKET_STATUS_UNKNOWN'),
    ),
    SUPPORT_TICKET_STATUS_OPEN    => array(
        SUPPORT_TICKET_EVENT_UNKNOWN         => array(
            'action' => 'actionNone()',
            'status' => 'SUPPORT_TICKET_STATUS_UNKNOWN'),
        SUPPORT_TICKET_EVENT_READ            => array(
            'action' => 'actionNone()',
            'status' => 'SUPPORT_TICKET_STATUS_OPEN'),
        SUPPORT_TICKET_EVENT_CHANGE_CATEGORY => array(
            'action' => 'actionModifySupportCategory($supportCategoryId)',
            'status' => 'SUPPORT_TICKET_STATUS_OPEN'),
        SUPPORT_TICKET_EVENT_CHANGE_PERSON   => array(
            'action' => 'actionAssign($personId)',
            'status' => 'SUPPORT_TICKET_STATUS_MOVED'),
        SUPPORT_TICKET_EVENT_CHANGE_OTHER    => array(
            'action' => 'actionNone()',
            'status' => 'SUPPORT_TICKET_STATUS_OPEN'),
        SUPPORT_TICKET_EVENT_REPLY           => array(
            'action' => 'actionReply($reply)',
            'status' => 'SUPPORT_TICKET_STATUS_WAIT'),
        SUPPORT_TICKET_EVENT_MESSAGE         => array(
            'action' => 'actionFollowup($messageId)',
            'status' => 'SUPPORT_TICKET_STATUS_NEW'),
        SUPPORT_TICKET_EVENT_CLOSE           => array(
            'action' => 'actionClose($id)',
            'status' => 'SUPPORT_TICKET_STATUS_CLOSED'),
    ),
    SUPPORT_TICKET_STATUS_WAIT    => array(
        SUPPORT_TICKET_EVENT_UNKNOWN         => array(
            'action' => 'actionNone()',
            'status' => 'SUPPORT_TICKET_STATUS_UNKNOWN'),
        SUPPORT_TICKET_EVENT_READ            => array(
            'action' => 'actionNone()',
            'status' => 'SUPPORT_TICKET_STATUS_WAIT'),
        SUPPORT_TICKET_EVENT_CHANGE_CATEGORY => array(
            'action' => 'actionNone()',
            'status' => 'SUPPORT_TICKET_STATUS_UNKNOWN'),
        SUPPORT_TICKET_EVENT_CHANGE_PERSON   => array(
            'action' => 'actionNone()',
            'status' => 'SUPPORT_TICKET_STATUS_UNKNOWN'),
        SUPPORT_TICKET_EVENT_CHANGE_OTHER    => array(
            'action' => 'actionNone()',
            'status' => 'SUPPORT_TICKET_STATUS_UNKNOWN'),
        SUPPORT_TICKET_EVENT_REPLY           => array(
            'action' => 'actionNone()',
            'status' => 'SUPPORT_TICKET_STATUS_UNKNOWN'),
        SUPPORT_TICKET_EVENT_MESSAGE         => array(
            'action' => 'actionFollowup($messageId)',
            'status' => 'SUPPORT_TICKET_STATUS_NEW'),
        SUPPORT_TICKET_EVENT_CLOSE           => array(
            'action' => 'actionNone()',
            'status' => 'SUPPORT_TICKET_STATUS_UNKNOWN'),
    ),
    SUPPORT_TICKET_STATUS_MOVED   => array(
        SUPPORT_TICKET_EVENT_UNKNOWN         => array(
            'action' => 'actionNone()',
            'status' => 'SUPPORT_TICKET_STATUS_UNKNOWN'),
        SUPPORT_TICKET_EVENT_READ            => array(
            'action' => 'actionAssignToReader()',
            'status' => 'SUPPORT_TICKET_STATUS_OPEN'),
        SUPPORT_TICKET_EVENT_CHANGE_CATEGORY => array(
            'action' => 'actionNone()',
            'status' => 'SUPPORT_TICKET_STATUS_UNKNOWN'),
        SUPPORT_TICKET_EVENT_CHANGE_PERSON   => array(
            'action' => 'actionAssign($personId)',
            'status' => 'SUPPORT_TICKET_STATUS_MOVED'),
        SUPPORT_TICKET_EVENT_CHANGE_OTHER    => array(
            'action' => 'actionNone()',
            'status' => 'SUPPORT_TICKET_STATUS_MOVED'),
        SUPPORT_TICKET_EVENT_REPLY           => array(
            'action' => 'actionNone()',
            'status' => 'SUPPORT_TICKET_STATUS_UNKNOWN'),
        SUPPORT_TICKET_EVENT_MESSAGE         => array(
            'action' => 'actionFollowup($messageId)',
            'status' => 'SUPPORT_TICKET_STATUS_NEW'),
        SUPPORT_TICKET_EVENT_CLOSE           => array(
            'action' => 'actionNone()',
            'status' => 'SUPPORT_TICKET_STATUS_UNKNOWN'),
    ),
    SUPPORT_TICKET_STATUS_CLOSED  => array(
        SUPPORT_TICKET_EVENT_UNKNOWN         => array(
            'action' => 'actionNone()',
            'status' => 'SUPPORT_TICKET_STATUS_UNKNOWN'),
        SUPPORT_TICKET_EVENT_READ            => array(
            'action' => 'actionNone()',
            'status' => 'SUPPORT_TICKET_STATUS_UNKNOWN'),
        SUPPORT_TICKET_EVENT_CHANGE_CATEGORY => array(
            'action' => 'actionNone()',
            'status' => 'SUPPORT_TICKET_STATUS_UNKNOWN'),
        SUPPORT_TICKET_EVENT_CHANGE_PERSON   => array(
            'action' => 'actionNone()',
            'status' => 'SUPPORT_TICKET_STATUS_UNKNOWN'),
        SUPPORT_TICKET_EVENT_CHANGE_OTHER    => array(
            'action' => 'actionNone()',
            'status' => 'SUPPORT_TICKET_STATUS_UNKNOWN'),
        SUPPORT_TICKET_EVENT_REPLY           => array(
            'action' => 'actionNone()',
            'status' => 'SUPPORT_TICKET_STATUS_UNKNOWN'),
        SUPPORT_TICKET_EVENT_MESSAGE         => array(
            // new ticket for new message, references old ticket ID
            'action' => 'actionNew($messageId, $id)',
            'status' => 'SUPPORT_TICKET_STATUS_CLOSED'),
        SUPPORT_TICKET_EVENT_CLOSE           => array(
            'action' => 'actionNone()',
            'status' => 'SUPPORT_TICKET_STATUS_CLOSED'),
    ),
);

?>
