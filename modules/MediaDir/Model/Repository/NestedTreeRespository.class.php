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
namespace Cx\Modules\MediaDir\Model\Repository;

/**
 * Class NestedTreeRespository
 *
 * @copyright   CLOUDREXX CMS - CLOUDREXX AG
 * @author      Project Team SS4U <info@cloudrexx.com>
 * @package     cloudrexx
 * @subpackage  module_mediadir
 */
namespace Cx\Modules\MediaDir\Model\Repository;

/**
 * Class NestedTreeRespository
 * 
 * @copyright   CLOUDREXX CMS - CLOUDREXX AG
 * @author      Project Team SS4U <info@cloudrexx.com>
 * @package     cloudrexx
 * @subpackage  module_mediadir
 */
class NestedTreeRespository extends \Gedmo\Tree\Entity\Repository\NestedTreeRepository
{
    /**
     * Returns the root node.
     *     
     * @return object entity
     */
    public function getRoot()
    {
        return $this->findOneById(1);
    }

    /**
     * Get children of given node
     *
     * @param object    $node           Parent node
     * @param integer   $langId         Language id
     * @param boolean   $activeOnly     Get active only
     * @param string    $sortByField    Sorting field
     * @param string    $direction      Sort direction ASC|DESC
     *
     * @return array Children array
     */
    public function getChildren($node = null, $langId = null, $activeOnly = false, $sortByField = null, $direction = 'ASC')
    {
        if ($node == null) {
            return array();
        }

        $meta   = $this->getClassMetadata();
        $config = $this->listener->getConfiguration($this->_em, $meta->name);

        $qb = $this->_em->createQueryBuilder();
        $qb->select('node')
           ->from($config['useObjectClass'], 'node');

        if ($langId) {
           $qb->innerJoin('node.locale', 'lc', \Doctrine\ORM\Query\Expr\Join::WITH, 'lc.lang_id = '. $langId);
        }

        $left  = $node->getLft();
        $right = $node->getRgt();
        if ($left && $right) {
            $qb->where('node.rgt < '. $right)
               ->andWhere('node.lft > '. $left);
        }
        $qb->andWhere("node.parent = ". $node->getId());
        if ($activeOnly) {
           $qb->andWhere('node.active = 1');
        }
        if (!$sortByField) {
            $qb->orderBy('node.lft', 'ASC');
        } else {
            $qb->orderBy($sortByField, $direction);
        }
        return $qb->getQuery()->getResult();
    }

    /**
     * Get count of childern by given node
     *
     * @param object    $node
     * @param boolean   $activeOnly     Get only active children
     *
     * @return integer Count of children
     */
    public function getChildCount($node = null, $activeOnly = false)
    {
        if ($node == null) {
            return 0;
        }

        $meta   = $this->getClassMetadata();
        $config = $this->listener->getConfiguration($this->_em, $meta->name);

        $qb = $this->_em->createQueryBuilder();
        $qb->select('COUNT(node.id)')
           ->from($config['useObjectClass'], 'node');

        $left  = $node->getLft();
        $right = $node->getRgt();
        if ($left && $right) {
            $qb->where('node.rgt < '. $right)
               ->andWhere('node.lft > '. $left);
        }

        if ($activeOnly) {
            $qb->andWhere('node.active = 1');
        }

        $q = $qb->getQuery();

        return intval($q->getSingleScalarResult());
    }
}
