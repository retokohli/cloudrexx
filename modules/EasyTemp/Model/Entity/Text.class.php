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

namespace Cx\Modules\EasyTemp\Model\Entity;

/**
 * Cx\Modules\EasyTemp\Model\Entity\Text
 */
class Text extends \Cx\Model\Base\EntityBase {
    /**
     * @var string $id
     */
    private $id;

    /**
     * @var string $code
     */
    private $code;

    /**
     * @var string $de
     */
    private $de;

    /**
     * @var string $en
     */
    private $en;

    /**
     * @var string $fr
     */
    private $fr;

    /**
     * @var string $it
     */
    private $it;

    /**
     * @var boolean $active
     */
    private $active;


    /**
     * Set id
     *
     * @param string $id
     */
    public function setId($id)
    {
        $this->id = $id;
    }

    /**
     * Get id
     *
     * @return string $id
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set code
     *
     * @param string $code
     */
    public function setCode($code)
    {
        $this->code = $code;
    }

    /**
     * Get code
     *
     * @return string $code
     */
    public function getCode()
    {
        return $this->code;
    }

    /**
     * Set de
     *
     * @param string $de
     */
    public function setDe($de)
    {
        $this->de = $de;
    }

    /**
     * Get de
     *
     * @return string $de
     */
    public function getDe()
    {
        return $this->de;
    }

    /**
     * Set en
     *
     * @param string $en
     */
    public function setEn($en)
    {
        $this->en = $en;
    }

    /**
     * Get en
     *
     * @return string $en
     */
    public function getEn()
    {
        return $this->en;
    }

    /**
     * Set fr
     *
     * @param string $fr
     */
    public function setFr($fr)
    {
        $this->fr = $fr;
    }

    /**
     * Get fr
     *
     * @return string $fr
     */
    public function getFr()
    {
        return $this->fr;
    }

    /**
     * Set it
     *
     * @param string $it
     */
    public function setIt($it)
    {
        $this->it = $it;
    }

    /**
     * Get it
     *
     * @return string $it
     */
    public function getIt()
    {
        return $this->it;
    }

    /**
     * Set active
     *
     * @param boolean $active
     */
    public function setActive($active)
    {
        $this->active = $active;
    }

    /**
     * Get active
     *
     * @return boolean $active
     */
    public function getActive()
    {
        return $this->active;
    }
}
