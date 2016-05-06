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


namespace Cx\Core_Modules\TemplateEditor\Model\Entity;

/**
 * Class Group
 *
 * @copyright   CLOUDREXX CMS - CLOUDREXX AG
 * @author      Adrian Berger <adrian.berger@cloudrexx.com>
 * @package     contrexx
 * @subpackage  core_module_templateeditor
 */
class Group {

    /**
     * The identifying name of the group
     *
     * @var string
     */
    protected $name;

    /**
     * The color of the group shown in GUI
     *
     * @var string
     */
    protected $color;

    /**
     * Array with translations for all available languages.
     * The key of the array is the language id.
     *
     * @var array
     */
    protected $translations;

    public function __construct(
        $name,
        $color = '#fff',
        $translations = array()
    ) {
        $this->name = $name;
        $this->color = $color;
        $this->translations = $translations;
    }

    /**
     * Get the name of the group
     *
     * @return string the name of the group
     */
    public function getName() {
        return $this->name;
    }

    /**
     * Set the name of the group
     *
     * @param string $name the name of the group
     */
    public function setName($name) {
        $this->name = $name;
    }

    /**
     * Get the color of the group
     *
     * @return string the color of the group
     */
    public function getColor() {
        return $this->color;
    }

    /**
     * Set the color of the group
     *
     * @param string $color the color of the group
     */
    public function setColor($color) {
        $this->color = $color;
    }

    /**
     * Get the translations for the group
     *
     * @return string the translations for the group
     */
    public function getTranslations() {
        return $this->translations;
    }

    /**
     * Set the translations for the group
     *
     * @param array $translations the translations for the group
     */
    public function setTranslations($translations) {
        $this->translations = $translations;
    }

}