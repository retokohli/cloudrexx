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
 * CalendarDateTime
 *
 * @copyright   Cloudrexx AG
 * @author      Project Team SS4U <info@cloudrexx.com>
 * @package     cloudrexx
 * @subpackage  modules_calendar
 */
namespace Cx\Modules\Calendar\Controller;

/**
 * CalendarDateTime
 *
 * @copyright   Cloudrexx AG
 * @author      Project Team SS4U <info@cloudrexx.com>
 * @package     cloudrexx
 * @subpackage  modules_calendar
 */
class CalendarDateTime extends \DateTime
{
    /**
     * Instance of DateTime component
     *
     * @var \Cx\Core\DateTime\Controller\ComponentController
     */
    protected $dateTimeComponent;

    /**
     * Default date format to parse
     * 
     * @var string
     */
    private $dateFormat = 'Y-m-d';

    /**
     * Default constructor
     *
     * @param string        $time       Time format [optional]
     * @param \DateTimeZone $timeZone   TimeZone object [optional]
     */
    function __construct($time = 'now', \DateTimeZone $timeZone = null)
    {
        parent::__construct($time, $timeZone);

        // fetch DateTime component controller
        $em            = \Cx\Core\Core\Controller\Cx::instanciate()->getDb()->getEntityManager();
        $componentRepo = $em->getRepository('Cx\Core\Core\Model\Entity\SystemComponent');

        $this->dateTimeComponent = $componentRepo->findOneBy(array('name'=>'DateTime'));
    }

    /**
     * Format to user date with time 
     *
     * @return string Formatted string
     */
    public function format2userDateTime()
    {
        return $this->format2user($this->dateFormat .' H:i');
    }

    /**
     * Format to user date
     *
     * @return string Formatted string
     */
    public function format2userDate()
    {
        return $this->format2user($this->dateFormat);
    }

    /**
     * Format to user time
     *
     * @return string Formatted string
     */
    public function format2userTime()
    {
        return $this->format2user('H:i');
    }

    /**
     * Format the datetime instance to user timezone by given format 
     *
     * @param string $format Format string
     *
     * @return string Formatted string
     */
    public function format2user($format)
    {
        return $this->getDb2user()
                    ->format($format);
    }

    /**
     * Returns the formatted string in database saving format
     *
     * @return string Formatted string
     */
    public function format2db()
    {
        return $this->getUser2db()
                    ->format('Y-m-d H:i:s');
    }

    /**
     * Wrapper method to call the DateTime component user2db method
     *
     * @return \DateTime
     */
    public function getUser2db()
    {
        return $this->dateTimeComponent
                    ->user2db($this);
    }

    /**
     * Wrapper method to call the DateTime component db2user method
     *
     * @return \DateTime
     */
    public function getDb2user()
    {
        return $this->dateTimeComponent
                    ->db2user($this);
    }

    /**
     * Getter for dateFormat value
     *
     * @return string
     */
    public function getDateFormat()
    {
        return $this->dateFormat;
    }

    /**
     * Setter for dateFormat value
     *
     * @param string $dateFormat
     */
    public function setDateFormat($dateFormat)
    {
        $this->dateFormat = $dateFormat;
    }

    /**
     * Getter for datetime component
     *
     * return \Cx\Core\DateTime\Controller\ComponentController
     */
    public function getDateTimeComponent()
    {
        return $this->dateTimeComponent;
    }
}
