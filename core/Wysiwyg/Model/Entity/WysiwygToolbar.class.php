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
 * Properties for the wysiwyg template entity
 *
 * @copyright   Cloudrexx AG
 * @author      Nick Brönnimann <nick.broennimann@comvation.com>
 * @package     cloudrexx
 * @subpackage  core_wysiwyg
 * @version     1.0.0
 */

namespace Cx\Core\Wysiwyg\Model\Entity;

/**
 * Cx\Core\Wysiwyg\Model\Entity\WysiwygToolbar
 */
class WysiwygToolbar extends \Cx\Model\Base\EntityBase
{
    /**
     * @var integer $id
     */
    protected $id;

    /**
     * @var string $availableFunctions
     */
    protected $availableFunctions = '';

    /**
     * @var string $removedButtons
     */
    protected $removedButtons = '';

    /**
     * @var integer $isDefault
     */
    protected $isDefault = 0;

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
     * Set availableFunctions
     *
     * @param string $availableFunctions
     */
    public function setAvailableFunctions($availableFunctions)
    {
        $this->availableFunctions = $availableFunctions;
    }

    /**
     * Get availableFunctions
     *
     * @return string $availableFunctions
     */
    public function getAvailableFunctions()
    {
        return $this->availableFunctions;
    }

    /**
     * Set removedButtons
     *
     * @param string $removedButtons
     */
    public function setRemovedButtons($removedButtons)
    {
        $this->removedButtons = $removedButtons;
    }

    /**
     * Get removedButtons
     *
     * @return string $removedButtons
     */
    public function getRemovedButtons()
    {
        return $this->removedButtons;
    }

    /**
     * Set isDefault
     *
     * @param integer $isDefault
     */
    public function setIsDefault($isDefault)
    {
        $this->isDefault = $isDefault;
    }

    /**
     * Get isDefault
     *
     * @return string $isDefault
     */
    public function getIsDefault()
    {
        return $this->isDefault;
    }
}