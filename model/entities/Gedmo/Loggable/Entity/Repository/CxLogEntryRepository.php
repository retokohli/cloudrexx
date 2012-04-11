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
    protected $pageRepository = null;

    public function __construct(EntityManager $em, ClassMetadata $class)
    {
        parent::__construct($em, $class);
        $this->em = $em;
        $this->pageRepository = $this->em->getRepository('Cx\Model\ContentManager\Page');
    }
    
    public function countLogEntries($cmd)
    {
        $counter = 0;
        
        switch ($cmd) {
            case 'deleted':
                $counter = count($this->findByAction('remove'));
                break;
            case 'unvalidated':
                // ToDo
                break;
            default:
                $where = $cmd == 'updated' ? 'update' : 'create';
                $logs  = $this->findByAction($where);
                
                foreach ($logs as $log) {
                    $page = $this->pageRepository->findOneById($log->getObjectId());
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
        switch ($cmd) {
            case 'deleted':
                
                break;
            case 'unvalidated':
                
                break;
            default: // create and update
                $result = array();
                $where  = $cmd == 'updated' ? 'update' : 'create';
                
                $query = $this->em->createQuery("
                    SELECT l.action, l.objectId, MAX(l.version) AS version
                    FROM Gedmo\Loggable\Entity\LogEntry l
                    WHERE l.action = '".$where."'
                    GROUP BY l.objectId
                    ORDER BY l.loggedAt DESC
                ");
                // If setFirstResult() is called, setMaxResult must be also called. Otherwise there is a fatal error.
                // The parameter for setMaxResult() method is a custom value set to 999999, because we need all pages.
                $query->setFirstResult($from)->setMaxResults(999999);
                $logs = $query->getResult();
                $i = 0;
                
                foreach ($logs as $log) {
                    $page = $this->pageRepository->findOneById($log['objectId']);
                    if (!$page) {
                        continue;
                    }
                    
                    if ($page->getEditingStatus() == '') {
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