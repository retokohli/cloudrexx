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
 * Category
 *
 * @copyright   Cloudrexx AG
 * @author      Project Team SS4U <info@cloudrexx.com>
 * @package     cloudrexx
 * @subpackage  module_calendar
*/
namespace Cx\Modules\Calendar\Model\Entity;

/**
 * Category
 *
 * @copyright   Cloudrexx AG
 * @author      Project Team SS4U <info@cloudrexx.com>
 * @package     cloudrexx
 * @subpackage  module_calendar
*/
class Category extends \Cx\Model\Base\EntityBase {
    /**
     * @var integer $id
     */
    protected $id;

    /**
     * @var integer $pos
     */
    protected $pos;

    /**
     * @var integer $status
     */
    protected $status;

    /**
     * @var Cx\Modules\Calendar\Model\Entity\CategoryName
     */
    protected $categoryNames;

    /**
     * @var Cx\Modules\Calendar\Model\Entity\Event
     */
    protected $events;

    public function __construct()
    {
        $this->pos = 0;
        $this->status = 0;
        $this->categoryNames = new \Doctrine\Common\Collections\ArrayCollection();
        $this->events = new \Doctrine\Common\Collections\ArrayCollection();
    }
    
    /**
     * Get id
     *
     * @return integer $id
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set pos
     *
     * @param integer $pos
     */
    public function setPos($pos)
    {
        $this->pos = $pos;
    }

    /**
     * Get pos
     *
     * @return integer $pos
     */
    public function getPos()
    {
        return $this->pos;
    }

    /**
     * Set status
     *
     * @param integer $status
     */
    public function setStatus($status)
    {
        $this->status = $status;
    }

    /**
     * Get status
     *
     * @return integer $status
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * Add categoryNames
     *
     * @param Cx\Modules\Calendar\Model\Entity\CategoryName $categoryNames
     */
    public function addCategoryName(\Cx\Modules\Calendar\Model\Entity\CategoryName $categoryNames)
    {
        $this->categoryNames[] = $categoryNames;
    }

    /**
     * Get categoryNames
     *
     * @return Doctrine\Common\Collections\Collection $categoryNames
     */
    public function getCategoryNames()
    {
        return $this->categoryNames;
    }

    /**
     * Add events
     *
     * @param Cx\Modules\Calendar\Model\Entity\Event $events
     */
    public function addEvents(\Cx\Modules\Calendar\Model\Entity\Event $events)
    {
        $this->events[] = $events;
    }

    /**
     * Get events
     *
     * @return Doctrine\Common\Collections\Collection $events
     */
    public function getEvents()
    {
        return $this->events;
    }
}