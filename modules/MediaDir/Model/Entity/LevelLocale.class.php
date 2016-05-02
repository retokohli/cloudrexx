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
 * LevelLocale entity
 *
 * @copyright   CLOUDREXX CMS - CLOUDREXX AG
 * @author      CLOUDREXX Development Team <info@cloudrexx.com>
 * @package     cloudrexx
 * @subpackage  module_mediadir
 */
namespace Cx\Modules\MediaDir\Model\Entity;

/**
 * LevelLocale entity
 *
 * @copyright   CLOUDREXX CMS - CLOUDREXX AG
 * @author      CLOUDREXX Development Team <info@cloudrexx.com>
 * @package     cloudrexx
 * @subpackage  module_mediadir
 */
class LevelLocale extends \Cx\Model\Base\EntityBase
{

    /**
     * @var integer $lang_id
     */
    protected $lang_id;

    /**
     * @var integer $level_id
     */
    protected $level_id;

    /**
     * @var string $level_name
     */
    protected $level_name;

    /**
     * @var text $level_description
     */
    protected $level_description;

    /**
     * @var Cx\Modules\MediaDir\Model\Entity\Level
     */
    protected $level;

    /**
     * Set lang_id
     *
     * @param integer $langId
     */
    public function setLangId($langId)
    {
        $this->lang_id = $langId;
    }

    /**
     * Get lang_id
     *
     * @return integer $langId
     */
    public function getLangId()
    {
        return $this->lang_id;
    }

    /**
     * Set level_id
     *
     * @param integer $levelId
     */
    public function setLevelId($levelId)
    {
        $this->level_id = $levelId;
    }

    /**
     * Get level_id
     *
     * @return integer $levelId
     */
    public function getLevelId()
    {
        return $this->level_id;
    }

    /**
     * Set level_name
     *
     * @param string $levelName
     */
    public function setLevelName($levelName)
    {
        $this->level_name = $levelName;
    }

    /**
     * Get level_name
     *
     * @return string $levelName
     */
    public function getLevelName()
    {
        return $this->level_name;
    }

    /**
     * Set level_description
     *
     * @param text $levelDescription
     */
    public function setLevelDescription($levelDescription)
    {
        $this->level_description = $levelDescription;
    }

    /**
     * Get level_description
     *
     * @return text $levelDescription
     */
    public function getLevelDescription()
    {
        return $this->level_description;
    }

    /**
     * Set level
     *
     * @param Cx\Modules\MediaDir\Model\Entity\Level $level
     */
    public function setLevel(\Cx\Modules\MediaDir\Model\Entity\Level $level)
    {
        $this->level = $level;
    }

    /**
     * Get level
     *
     * @return Cx\Modules\MediaDir\Model\Entity\Level $level
     */
    public function getLevel()
    {
        return $this->level;
    }
}
