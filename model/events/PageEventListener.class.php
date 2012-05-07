<?php
/**
 * This listener ensures slug consistency on Page objects.
 * On Flushing, all entities are scanned and changed where needed.
 */
namespace Cx\Model\Events;
use \Cx\Model\ContentManager\Page as Page;
use Doctrine\Common\Util\Debug as DoctrineDebug;

class PageEventListenerException extends \Exception {}

class PageEventListener {
    
    public function onFlush($eventArgs) {
        $em = $eventArgs->getEntityManager();
        
        $uow = $em->getUnitOfWork();
        
        foreach ($uow->getScheduledEntityUpdates() AS $entity) {
            $this->checkValidPersistingOperation($entity);
        }
    }

    /**
     * Sanity test for Pages. Prevents user from persisting bogus Pages.
     * This is the case if
     *  - the Page has fallback content. In this case, the Page's content was overwritten with
     *    other data that is not meant to be persisted.
     * @throws PageEventListenerException
     */
    protected function checkValidPersistingOperation($page) {
        if ($page instanceof Page) {
            if ($page->hasFallbackContent()) {
                throw new PageEventListenerException('Tried to persist Page "'.$page->getTitle().'" with id "'.$page->getId().'". This Page was retrieved by the routing and is filled with (bogus) fallback content. Please re-fetch the Page via it\'s id, then edit and persist it.');
            }
        }
    }
}
