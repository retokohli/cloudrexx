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
 * DoctrineRepository
 *
 * @copyright   Cloudrexx AG
 * @author      Project Team SS4U <info@cloudrexx.com>
 * @package     cloudrexx
 * @subpackage  core_datasource
 */

namespace Cx\Core\DataSource\Model\Entity;

/**
 * DoctrineRepository
 *
 * @copyright   Cloudrexx AG
 * @author      Project Team SS4U <info@cloudrexx.com>
 * @package     cloudrexx
 * @subpackage  core_datasource
 */

class DoctrineRepository extends DataSource {
    
    public function get($elementId, $filter, $order, $limit, $offset, $fieldList) {
        $em = $this->cx->getDb()->getEntityManager();
        $repo = $em->getRepository($this->getIdentifier());
        
        if (!$repo) {
            throw new \Exception('Repository not found!');
        }
        
        $criteria = array();
        
        // $filter
        if (count($fieldList)) {
            foreach ($filter as $field=>$value) {
                if (!in_array($field, $fieldList)) {
                    continue;
                }
                $criteria[$field] = $value;
            }
        }
        
        // $elementId
        if (isset($elementId)) {
            $meta = $em->getClassMetadata($this->getIdentifier());
            $identifierField = $meta->getSingleIdentifierFieldName();
            $criteria[$identifierField] = $elementId;
        }
        
        // $order
        foreach ($order as $field=>$ascdesc) {
            if (
                !in_array($field, $fieldList) ||
                !in_array($ascdesc, array('ASC', 'DESC'))
            ) {
                unset($order[$field]);
            }
        }
        
        // order, limit and offset are not supported by our doctrine version
        // yet! This would be the nice way to solve this:
        /*$result = $repo->findBy(
            $criteria,
            $order,
            (int) $limit,
            (int) $offset
        );//*/
        
        // but for now we'll have to:
        $qb = $em->createQueryBuilder();
        $qb->select('x')
            ->from($this->getIdentifier(), 'x');
        // $filter
        $i = 1;
        foreach ($criteria as $field=>$value) {
            $qb->andWhere($qb->expr()->eq('x.' . $field, '?' . $i));
            $qb->setParameter($i, $value);
            $i++;
        }
        // $order, $limit, $offset
        foreach ($order as $field=>$ascdesc) {
            $qb->orderBy('x.' . $field, $ascdesc);
        }
        // $limit, $offset
        if ($limit) {
            $qb->setMaxResults($limit);
            if ($offset) {
                $qb->setFirstResult($offset);
            }
        }
        $result = $qb->getQuery()->getResult(\Doctrine\ORM\AbstractQuery::HYDRATE_ARRAY);
        
        // $fieldList
        $dataSet = new \Cx\Core_Modules\Listing\Model\Entity\DataSet($result);
        if (count($fieldList)) {
            $dataFlipped = $dataSet->flip()->toArray();
            foreach ($dataFlipped as $key=>$value) {
                if (!in_array($key, $fieldList)) {
                    unset($dataFlipped[$key]);
                }
            }
            $dataSetFlipped = new \Cx\Core_Modules\Listing\Model\Entity\DataSet(
                $dataFlipped
            );
            $dataSet = $dataSetFlipped->flip();
        }
        
        return $dataSet->toArray();
    }
}

