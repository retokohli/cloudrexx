<?php

namespace Gedmo\Loggable\Entity\Repository;

use Doctrine\Common\Util\Debug as DoctrineDebug;
use Doctrine\ORM\EntityRepository,
    Doctrine\ORM\EntityManager,
    Doctrine\ORM\Mapping\ClassMetadata,
    Doctrine\ORM\Query\Expr;

class LogEntryRepositoryException extends \Exception {};

class CxLogEntryRepository extends \Gedmo\Loggable\Entity\Repository\LogEntryRepository {
    //doctrine entity manager
    protected $em = null;
    protected $pageRepo = null;

    public function __construct(EntityManager $em, ClassMetadata $class)
    {
        parent::__construct($em, $class);
        $this->em = $em;
        $this->pageRepo = $this->em->getRepository('Cx\Model\ContentManager\Page');
    }
    
    public function countLogEntries($cmd)
    {
        $counter = 0;
        
        switch ($cmd) {
            case 'deleted':
                $logs = $this->findByAction('remove');
                $logsByNodeId = array();
                
                foreach ($logs as $log) {
                    $page = new \Cx\Model\ContentManager\Page();
                    $page->setId($log->getObjectId());
                    $this->revert($page, $log->getVersion() - 1);
                    
                    // Only used to count
                    $logsByNodeId[$page->getNodeIdShadowed()] = 0;
                }
                
                $counter = count($logsByNodeId);
                break;
            case 'unvalidated':
                $logs = $this->findByAction('create');
                array_merge($logs, $this->findByAction('update'));
                
                foreach ($logs as $log) {
                    $page = $this->pageRepo->findOneById($log->getObjectId());
                    if (!$page) {
                        continue;
                    }
                    
                    if ($page->getEditingStatus() == 'hasDraftWaiting') {
                        $counter++;
                    }
                }
                break;
            default: // create
                $where = $cmd == 'updated' ? 'update' : 'create';
                $logs  = $this->findByAction($where);
                
                foreach ($logs as $log) {
                    $page = $this->pageRepo->findOneById($log->getObjectId());
                    if (!$page) {
                        continue;
                    }
                    
                    if ($page->getEditingStatus() == '') {
                        $counter++;
                    }
                }
        }
        
        return $counter;
    }
    
    public function getLogs($cmd = '', $from, $to)
    {
        $result = array();
        
        switch ($cmd) {
            case 'deleted':
                $where = "WHERE l.action = 'remove'";
                break;
            case 'unvalidated':
                $editingStatus = 'hasDraftWaiting';
                $where = "WHERE l.action = 'create'
                          OR    l.action = 'update'";
                break;
            case 'updated':
                $editingStatus = '';
                $where = "WHERE l.action = 'update'";
                break;
            default: // create
                $editingStatus = '';
                $where = "WHERE l.action = 'create'";
        }
        
        $query = $this->em->createQuery("
            SELECT l.action, l.objectId, MAX(l.version) AS version
            FROM Gedmo\Loggable\Entity\LogEntry l
            ".$where."
            GROUP BY l.objectId
            ORDER BY l.loggedAt DESC
        ");
        
        switch ($cmd) {
            case 'deleted':
                $query->setFirstResult($from)->setMaxResults($to);
                $logs = $query->getResult();
                $logsByNodeId = array();
                
                // Structure the logs by node id and language
                foreach ($logs as $log) {
                    $page = new \Cx\Model\ContentManager\Page();
                    $page->setId($log['objectId']);
                    $this->revert($page, $log['version'] - 1);
                    
                    $logsByNodeId[$page->getNodeIdShadowed()][$page->getLang()] = $log;
                }
                
                // Only one log per node id. It takes the first index.
                foreach ($logsByNodeId as $log) {
                    $result[] = array_shift(array_values($log));
                }
                break;
            default: // create, update and unvalidated
                // If setFirstResult() is called, setMaxResult must be also called. Otherwise there is a fatal error.
                // The parameter for setMaxResult() method is a custom value set to 999999, because we need all pages.
                $query->setFirstResult($from)->setMaxResults(999999);
                $logs = $query->getResult();
                $i = 0;
                
                foreach ($logs as $log) {
                    $page = $this->pageRepo->findOneById($log['objectId']);
                    if (!$page) {
                        continue;
                    }
                    
                    if ($page->getEditingStatus() == $editingStatus) {
                        $result[] = $log;
                        $i++;
                    }
                    
                    if ($i >= $to) {
                        break;
                    }
                }
        }
        
        return $result;
    }
    
}