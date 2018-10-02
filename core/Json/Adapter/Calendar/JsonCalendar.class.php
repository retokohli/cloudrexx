<?php

/**
 * Cloudrexx
 *
 * @link      http://www.cloudrexx.com
 * @copyright Cloudrexx AG 2007-2015
 *
 * According to our dual licensing model, this program can be used either
 * under the terms of the GNU Affero General Public License, version 3,
 * or under a proprietary license.
 *
 * The texts of the GNU Affero General Public License with an additional
 * permission and of our proprietary license can be found at and
 * in the LICENSE file you have received along with this program.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU Affero General Public License for more details.
 *
 * "Cloudrexx" is a registered trademark of Cloudrexx AG.
 * The licensing of the program under the AGPLv3 does not imply a
 * trademark license. Therefore any rights, title and interest in
 * our trademarks remain entirely with us.
 */

/**
 * JSON Adapter for Calendar module
 * @copyright   Cloudrexx AG
 * @author      ss4u <ss4ugroup@gmail.com>
 * @package     cloudrexx
 * @subpackage  core_json
 */

namespace Cx\Core\Json\Adapter\Calendar;
use \Cx\Core\Json\JsonAdapter;

/**
 * JSON Adapter for Calendar module
 * @copyright   Cloudrexx AG
 * @author      ss4u <ss4ugroup@gmail.com>
 * @package     cloudrexx
 * @subpackage  core_json
 */
class JsonCalendar implements JsonAdapter {
    /**
     * List of messages
     * @var Array
     */
    private $messages = array();

    /**
     * Returns the internal name used as identifier for this adapter
     * @return String Name of this adapter
     */
    public function getName() {
        return 'calendar';
    }

    /**
     * Returns an array of method names accessable from a JSON request
     * @return array List of method names
     */
    public function getAccessableMethods() {
        return array('getExeceptionDates', 'getRecipientCount');
    }

    /**
     * Returns all messages as string
     * @return String HTML encoded error messages
     */
    public function getMessagesAsString() {
        return implode('<br />', $this->messages);
    }

    /**
     * Returns default permission as object
     * @return Object
     */
    public function getDefaultPermissions() {
        return null;
    }

    /**
     * Returns all series dates from the given post data
     *
     * @return array Array of dates
     */
    public function getExeceptionDates() {
        global $objInit, $_CORELANG;

        if (!\FWUser::getFWUserObject()->objUser->login() || $objInit->mode != 'backend') {
            throw new \Exception($_CORELANG['TXT_ACCESS_DENIED_DESCRIPTION']);
        }

        $calendarLib = new \Cx\Modules\Calendar\Controller\CalendarLibrary();
        return $calendarLib->getExeceptionDates();
    }

    /**
     * Get the count of the selected recipients of a event invitation
     *
     * @param   array   $params     List of get and post parameters which were
     *                              sent to the json adapter.
     *
     * @return  integer            The count of the recipients
     */
    public function getRecipientCount($params = array())
    {
        $event = new \Cx\Modules\Calendar\Controller\CalendarEvent();
        if (intval($params['get']['id']) != 0) {
            $event->get(
                intval($params['get']['id']),
                null,
                intval($params['get']['lang_id'])
            );

            if (empty($event->id)) {
                return 0;
            }
        }

        // load current lists of invited people directly from the get params,
        // because they are not necessarily stored in the database yet
        // 1.) load access users
        $event->invitedGroups = $params['get']['selectedGroups'];

        // 2.) load crm users
        $invited = $params['get']['invite_crm_memberships'];
        $excluded = $params['get']['excluded_crm_memberships'];
        $event->invitedCrmGroups = is_array($invited) ? contrexx_input2int($invited) : array();
        $event->excludedCrmGroups = is_array($excluded) ? contrexx_input2int($excluded) : array();

        // 3.) load emails which were entered manually
        $event->invitedMails = $params['get']['invitedMails'];

        // get the send to filter
        $sendInvitationTo = $params['get']['sendMailTo'];
        $calendarManager = new \Cx\Modules\Calendar\Controller\CalendarMailManager();
        $recipientsCount = $calendarManager->getSendMailRecipientsCount(
            \Cx\Modules\Calendar\Controller\CalendarMailManager::MAIL_INVITATION,
            $event,
            0,
            null,
            $sendInvitationTo
        );
        return $recipientsCount;
    }
}
