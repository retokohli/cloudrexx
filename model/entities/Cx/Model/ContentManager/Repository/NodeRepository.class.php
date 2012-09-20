<?php

namespace Cx\Model\ContentManager\Repository;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Mapping\ClassMetadata;
use Gedmo\Tree\Entity\Repository\NestedTreeRepository;

class NodeRepository extends NestedTreeRepository {
    protected $em = null;
    const DataProperty = '__data';

    public function __construct(EntityManager $em, ClassMetadata $class)
    {
        parent::__construct($em, $class);
        $this->em = $em;
    }

    /**
     * Returns the root node.
     * @todo DO NOT use NestedTreeRepository->getRootNodes(), it needs a lot of RAM, implement own query to get all root nodes
     * @return \Cx\Model\ContentManager\Node
     */
    public function getRoot() {
        return $this->findOneBy(array('id'=>1));
    }
    
    /**
     * Translates a branch of the tree recursively
     * @todo This does only work for root node by now
     * @param \Cx\Model\ContentManager\Node $rootNode Node to start with (see todo, optional for now)
     * @param int $fromLanguage Language id to copy from
     * @param int $toLanguage Language id to copy to
     * @param boolean $includingContent Wheter to copy content or set type to fallback
     * @param int $limit (optional) How many nodes should be copied, 0 means all, defaults to 0
     * @param int $offset (optional) How many nodes should be skipped, defaults to 0
     * @return array Returns an array with the following structure: array('count'=>{count of nodes}, 'offset'=>{current offset after copy})
     * @throws \Cx\Core\ContentManager\ContentManagerException 
     */
    public function translateRecursive($rootNode, $fromLanguage, $toLanguage, $includingContent, $limit = 0, $offset = 0) {
        $nodes = $this->findAll();
        $i = 0;
        foreach ($nodes as $node) {
            if ($i < $offset) {
                $i++;
                continue;
            }
            if ($limit > 0 && ($i - $offset) >= $limit) {
                break;
            }
            $page = $node->getPage($fromLanguage);
            if (!$page) {
                // no page in this lang, we don't care
                $i++;
                continue;
            }
            // if the target page exists, we just customize it
            $destPage = $node->getPage($toLanguage);
            try {
                $pageCopy = $page->copyToLang(
                    $toLanguage,
                    $includingContent,
                    $includingContent, //$includeModuleAndCmd = true,
                    true, //$includeName = true,
                    true, // true, //$includeMetaData = true,
                    true, // true, //$includeProtection = true,
                    false, // true, //$followRedirects = false,
                    false, // true //$followFallbacks = false
                    $destPage
                );
            } catch (\Exception $e) {
                throw new \Cx\Core\ContentManager\ContentManagerException('Failed to copy page #' . $page->getId() . '. Error was: ' . $e->getMessage());
            }
            if (!$pageCopy) {
                throw new \Cx\Core\ContentManager\ContentManagerException('Failed to copy page #' . $page->getId());
            }
            $this->em->persist($pageCopy);
            $this->em->flush();
            $i++;
        }
        return array('count'=>count($nodes), 'offset'=>$i);
    }

    /**
     * Tries to recover the tree
     *
     * @throws RuntimeException - if something fails in transaction
     * @return void
     */
    public function recover()
    {
        if ($this->verify() === true) {
            return;
        }
        $left = 1;
        $startNode = $this->findOneBy(array('id'=>1));
        $startNode->setLft($left);
        $this->recoverBranch($startNode, $left);
        return $this->verify();
    }
    
    /**
     * Tries to recover a branch - assuming that level and left of $rootNode are correct!
     * @param \Cx\Model\ContentManager\Node $rootNode Node to start with
     */
    private function recoverBranch($rootNode, &$left = null, $level = null)
    {
        if ($left == null) {
            $left = $rootNode->getLft();
        }
        if ($level == null) {
            $level = $rootNode->getLvl();
        }
        echo $rootNode->getId() . '<br />';
        $level++;
        foreach ($rootNode->getChildren() as $child) {
            $left++;
            $child->setLft($left);
            $child->setLvl($level);
            $this->recoverBranch($child, $left, $level);
        }
        $left++;
        $rootNode->setRgt($left);
        $this->em->persist($rootNode);
        $this->em->flush();
    }
}

