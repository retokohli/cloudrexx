<?php

namespace Gedmo\Tree\Entity\Repository;

use Doctrine\ORM\Query,
    Gedmo\Tree\Strategy\ORM\Nested,
    Gedmo\Exception\InvalidArgumentException;

class CxNestedTreeRepository extends NestedTreeRepository
{
    /**
     * Get the query for next siblings of the given $node
     *
     * @param object $node
     * @param bool $includeSelf - include the node itself
     * @throws \Gedmo\Exception\InvalidArgumentException - if input is invalid
     * @return Query
     */
    public function getNextSiblingsQuery($node, $includeSelf = false, $skipAliasNodes = false)
    {
        $meta = $this->getClassMetadata();
        if (!$node instanceof $meta->name) {
            throw new InvalidArgumentException("Node is not related to this repository");
        }
        if (!$this->_em->getUnitOfWork()->isInIdentityMap($node)) {
            throw new InvalidArgumentException("Node is not managed by UnitOfWork");
        }

        $config = $this->listener->getConfiguration($this->_em, $meta->name);
        $parent = $meta->getReflectionProperty($config['parent'])->getValue($node);
        if (!$parent) {
            throw new InvalidArgumentException("Cannot get siblings from tree root node");
        }
        $parentId = current($this->_em->getUnitOfWork()->getEntityIdentifier($parent));

        $left  = $meta->getReflectionProperty($config['left'])->getValue($node);
        $sign  = $includeSelf ? '>=' : '>';
        $page  = $skipAliasNodes ? ', Cx\Model\ContentManager\Page page' : '';
        $where = $skipAliasNodes ? ' AND node.id = page.node' : '';
        $type  = $skipAliasNodes ? ' AND page.type <> \'alias\'' : '';
        $group = $skipAliasNodes ? ' GROUP BY node.id' : '';

        $dql = "SELECT node FROM {$config['useObjectClass']} node {$page}";
        $dql .= " WHERE node.{$config['parent']} = {$parentId}";
        $dql .= " AND node.{$config['left']} {$sign} {$left}";
        $dql .= $where;
        $dql .= $type;
        $dql .= $group;
        $dql .= " ORDER BY node.{$config['left']} ASC";
        return $this->_em->createQuery($dql);
    }

    /**
     * Find the next siblings of the given $node
     *
     * @param object $node
     * @param bool $includeSelf - include the node itself
     * @return array
     */
    public function getNextSiblings($node, $includeSelf = false, $skipAliasNodes = false)
    {
        return $this->getNextSiblingsQuery($node, $includeSelf, $skipAliasNodes)->getResult();
    }

    /**
     * Get query for previous siblings of the given $node
     *
     * @param object $node
     * @param bool $includeSelf - include the node itself
     * @throws \Gedmo\Exception\InvalidArgumentException - if input is invalid
     * @return Query
     */
    public function getPrevSiblingsQuery($node, $includeSelf = false, $skipAliasNodes = false)
    {
        $meta = $this->getClassMetadata();
        if (!$node instanceof $meta->name) {
            throw new InvalidArgumentException("Node is not related to this repository");
        }
        if (!$this->_em->getUnitOfWork()->isInIdentityMap($node)) {
            throw new InvalidArgumentException("Node is not managed by UnitOfWork");
        }

        $config = $this->listener->getConfiguration($this->_em, $meta->name);
        $parent = $meta->getReflectionProperty($config['parent'])->getValue($node);
        if (!$parent) {
            throw new InvalidArgumentException("Cannot get siblings from tree root node");
        }
        $parentId = current($this->_em->getUnitOfWork()->getEntityIdentifier($parent));

        $left  = $meta->getReflectionProperty($config['left'])->getValue($node);
        $sign  = $includeSelf ? '<=' : '<';
        $page  = $skipAliasNodes ? ', Cx\Model\ContentManager\Page page' : '';
        $where = $skipAliasNodes ? ' AND node.id = page.node' : '';
        $type  = $skipAliasNodes ? ' AND page.type <> \'alias\'' : '';
        $group = $skipAliasNodes ? ' GROUP BY node.id' : '';

        $dql = "SELECT node FROM {$config['useObjectClass']} node {$page}";
        $dql .= " WHERE node.{$config['parent']} = {$parentId}";
        $dql .= " AND node.{$config['left']} {$sign} {$left}";
        $dql .= $where;
        $dql .= $type;
        $dql .= $group;
        $dql .= " ORDER BY node.{$config['left']} ASC";
        return $this->_em->createQuery($dql);
    }

    /**
     * Find the previous siblings of the given $node
     *
     * @param object $node
     * @param bool $includeSelf - include the node itself
     * @return array
     */
    public function getPrevSiblings($node, $includeSelf = false, $skipAliasNodes = false)
    {
        return $this->getPrevSiblingsQuery($node, $includeSelf, $skipAliasNodes)->getResult();
    }

    /**
     * Move the node down in the same level
     *
     * @param object $node
     * @param mixed $number
     *         integer - number of positions to shift
     *         boolean - if "true" - shift till last position
     * @throws RuntimeException - if something fails in transaction
     * @return boolean - true if shifted
     */
    public function moveDown($node, $number = 1, $skipAliasNodes = false)
    {
        $result = false;
        $meta = $this->getClassMetadata();
        if ($node instanceof $meta->name) {
            $config = $this->listener->getConfiguration($this->_em, $meta->name);
            $nextSiblings = $this->getNextSiblings($node, false, $skipAliasNodes);
            if ($numSiblings = count($nextSiblings)) {
                $result = true;
                if ($number === true) {
                    $number = $numSiblings;
                } elseif ($number > $numSiblings) {
                    $number = $numSiblings;
                }
                $this->listener
                    ->getStrategy($this->_em, $meta->name)
                    ->updateNode($this->_em, $node, $nextSiblings[$number - 1], Nested::NEXT_SIBLING);
            }
        } else {
            throw new InvalidArgumentException("Node is not related to this repository");
        }
        return $result;
    }

    /**
     * Move the node up in the same level
     *
     * @param object $node
     * @param mixed $number
     *         integer - number of positions to shift
     *         boolean - true shift till first position
     * @throws RuntimeException - if something fails in transaction
     * @return boolean - true if shifted
     */
    public function moveUp($node, $number = 1, $skipAliasNodes = false)
    {
        $result = false;
        $meta = $this->getClassMetadata();
        if ($node instanceof $meta->name) {
            $config = $this->listener->getConfiguration($this->_em, $meta->name);
            $prevSiblings = array_reverse($this->getPrevSiblings($node, false, $skipAliasNodes));
            if ($numSiblings = count($prevSiblings)) {
                $result = true;
                if ($number === true) {
                    $number = $numSiblings;
                } elseif ($number > $numSiblings) {
                    $number = $numSiblings;
                }
                $this->listener
                    ->getStrategy($this->_em, $meta->name)
                    ->updateNode($this->_em, $node, $prevSiblings[$number - 1], Nested::PREV_SIBLING);
            }
        } else {
            throw new InvalidArgumentException("Node is not related to this repository");
        }
        return $result;
    }
}
