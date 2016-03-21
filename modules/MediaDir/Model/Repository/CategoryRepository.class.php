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
 * Class CategoryRepository
 *
 * @copyright   CLOUDREXX CMS - CLOUDREXX AG
 * @author      Project Team SS4U <info@cloudrexx.com>
 * @package     cloudrexx
 * @subpackage  module_mediadir
 */
namespace Cx\Modules\MediaDir\Model\Repository;

/**
 * Class CategoryRepository
 * 
 * @copyright   CLOUDREXX CMS - CLOUDREXX AG
 * @author      Project Team SS4U <info@cloudrexx.com>
 * @package     cloudrexx
 * @subpackage  module_mediadir
 */
class CategoryRepository extends \Doctrine\ORM\EntityRepository
{
    /**
     * Returns the root node.
     * @todo DO NOT use NestedTreeRepository->getRootNodes(), it needs a lot of RAM, implement own query to get all root nodes
     * @return \Cx\Core\ContentManager\Model\Entity\Node
     */
    public function getRoot()
    {
        return $this->findOneById(1);
    }

    /**
     * Get sub categories by given criteria
     *
     * @param \Cx\Modules\MediaDir\Model\Entity\Category    $category       Parent category
     * @param integer                                       $langId         Language id
     * @param boolean                                       $activeOnly     Get active only
     * @param string                                        $sortByField    Sorting field
     * @param string                                        $direction      Sort direction ASC|DESC
     *
     * @return array Subcategory array
     */
    public function getChildren(\Cx\Modules\MediaDir\Model\Entity\Category $category, $langId = null, $activeOnly = false, $sortByField = null, $direction = 'ASC')
    {
        $qb = $this->getEntityManager()->createQueryBuilder();

        $qb->select('node')
           ->from('\Cx\Modules\MediaDir\Model\Entity\Category', 'node');
        if ($langId) {
           $qb->innerJoin('node.locale', 'lc', \Doctrine\ORM\Query\Expr\Join::WITH, 'lc.lang_id = '. $langId);
        }

        $left  = $category->getLft();
        $right = $category->getRgt();
        if ($left && $right) {
            $qb->where('node.rgt < '. $right)
               ->andWhere('node.lft > '. $left);
        }
        $qb->andWhere("node.parent = ". $category->getId());
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
     * Get count of subcategories
     *
     * @param \Cx\Modules\MediaDir\Model\Entity\Category $category
     * @param boolean $activeOnly                        Get only active subcategories
     *
     * @return integer Count of subcategories
     */
    public function getChildCount(\Cx\Modules\MediaDir\Model\Entity\Category $category, $activeOnly = false)
    {
        $qb = $this->getEntityManager()->createQueryBuilder();

        $qb->select('COUNT(node.id)')
           ->from('\Cx\Modules\MediaDir\Model\Entity\Category', 'node');

        $left  = $category->getLft();
        $right = $category->getRgt();
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
