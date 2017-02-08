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
 * JsonAdapter Controller to handle EsiWidgets
 *
 * @author Michael Ritter <michael.ritter@cloudrexx.com>
 * @package cloudrexx
 * @subpackage coremodules_widget
 */

namespace Cx\Core_Modules\Access\Controller;

/**
 * JsonAdapter Controller to handle EsiWidgets
 * Usage:
 * - Create a subclass that implements parseWidget()
 * - Register it as a Controller in your ComponentController
 * @author Michael Ritter <michael.ritter@cloudrexx.com>
 * @package cloudrexx
 * @subpackage coremodules_widget
 */
class EsiWidgetController extends \Cx\Core_Modules\Widget\Controller\EsiWidgetController {
    /**
     * Parses a widget
     * @param string $name Widget name
     * @param \Cx\Core\Html\Sigma Widget template
     * @param string $locale RFC 3066 locale identifier
     */
    public function parseWidget($name, $template, $locale)
    {
        if (preg_match('/^access_logged_(in|out)\d{0,2}/', $name)) {
            $this->getComponent('Session')->getSession();
            \FWUser::parseLoggedInOutBlocks($template);
        }

        $objAccessBlocks = new AccessBlocks($template);
        if ($template->blockExists('access_currently_online_member_list')) {
            if ($template->blockExists('access_currently_online_female_members')) {
                $objAccessBlocks->setCurrentlyOnlineUsers('female');
            }

            if ($template->blockExists('access_currently_online_male_members')) {
                $objAccessBlocks->setCurrentlyOnlineUsers('male');
            }

            if ($template->blockExists('access_currently_online_members')) {
                $objAccessBlocks->setCurrentlyOnlineUsers();
            }
        }

        if ($template->blockExists('access_last_active_member_list')) {
            if ($template->blockExists('access_last_active_female_members')) {
                $objAccessBlocks->setLastActiveUsers('female');
            }

            if ($template->blockExists('access_last_active_male_members')) {
                $objAccessBlocks->setLastActiveUsers('male');
            }

            if ($template->blockExists('access_last_active_members')) {
                $objAccessBlocks->setLastActiveUsers();
            }
        }

        if ($template->blockExists('access_latest_registered_member_list')) {
            if ($template->blockExists('access_latest_registered_female_members')) {
                $objAccessBlocks->setLatestRegisteredUsers('female');
            }

            if ($template->blockExists('access_latest_registered_male_members')) {
                $objAccessBlocks->setLatestRegisteredUsers('male');
            }

            if ($template->blockExists('access_latest_registered_members')) {
                $objAccessBlocks->setLatestRegisteredUsers();
            }
        }

        if ($template->blockExists('access_birthday_member_list')) {
            if ($template->blockExists('access_birthday_female_members')) {
                $objAccessBlocks->setBirthdayUsers('female');
            }

            if ($template->blockExists('access_birthday_male_members')) {
                $objAccessBlocks->setBirthdayUsers('male');
            }

            if ($template->blockExists('access_birthday_members')) {
                $objAccessBlocks->setBirthdayUsers();
            }
        }
    }

    /**
     * Returns the content of a widget
     *
     * @param array $params JsonAdapter parameters
     *
     * @return array Content in an associative array
     */
    public function getWidget($params)
    {
        $widgetname = isset($params['get']['name'])
            ? contrexx_input2raw($params['get']['name']) : '';
        if ($widgetname == 'access_birthday_member_list') {
            $dateTime = new \DateTime();
            $dateTime->setTime(23, 59, 59);
            $params['response']->setExpirationDate($dateTime);
        }

        return parent::getWidget($params);
    }
}
