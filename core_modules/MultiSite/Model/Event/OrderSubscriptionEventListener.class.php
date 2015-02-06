<?php

/**
 * OrderSubscriptionEventListener
 *
 * @copyright   CONTREXX CMS - COMVATION AG
 * @author      Project Team SS4U <info@comvation.com>
 * @package     contrexx
 * @subpackage  coremodule_multisite
 */

namespace Cx\Core_Modules\MultiSite\Model\Event;

/**
 * Class OrderSubscriptionEventListenerException
 *
 * @copyright   CONTREXX CMS - COMVATION AG
 * @author      Project Team SS4U <info@comvation.com>
 * @package     contrexx
 * @subpackage  coremodule_multisite
 */
class OrderSubscriptionEventListenerException extends \Exception {}

/**
 * Class OrderSubscriptionEventListener
 *
 * @copyright   CONTREXX CMS - COMVATION AG
 * @author      Project Team SS4U <info@comvation.com>
 * @package     contrexx
 * @subpackage  coremodule_multisite
 */
class OrderSubscriptionEventListener implements \Cx\Core\Event\Model\Entity\EventListener {
    /**
     * @var array
     */
    protected $entitiesToPersistOnPostFlush = array();

    public function preUpdate($eventArgs) {
        $subscription = $eventArgs->getEntity();
        
        // in case product has been changed, we have to migrate the associated entity
        if ($eventArgs->hasChangedField('product')) {
            $oldProduct = $eventArgs->getOldValue('product');
            $this->updateProductEntity($subscription, $subscription->getProduct(), $oldProduct);
            return;
        }
    }
    
    public function postUpdate($eventArgs) {
        $subscription = $eventArgs->getEntity();
        $changeSet    = $eventArgs->getEntityManager()->getUnitOfWork()->getEntityChangeSet($subscription);

        // in case product has been changed, we shall triger the model event payComplete
        // this will update the license information on the associated websites
        if (!empty($changeSet['product'])) {
            \Env::get('cx')->getEvents()->triggerEvent('model/payComplete', array(new \Doctrine\ORM\Event\LifecycleEventArgs($this, \Env::get('em'))));
        }
    }
    
    /**
     * Switch the product
     * 
     * @global array $_ARRAYLANG
     * 
     * @param \Cx\Modules\Pim\Model\Entity\Product $newProduct
     * @param \Cx\Modules\Pim\Model\Entity\Product $oldProduct
     * 
     * @throws \Cx\Core\Error\Model\Entity\ShinyException
     */
    public function updateProductEntity(\Cx\Modules\Order\Model\Entity\Subscription $subscription, 
                                  \Cx\Modules\Pim\Model\Entity\Product $newProduct, 
                                  \Cx\Modules\Pim\Model\Entity\Product $oldProduct)
    {
        global $_ARRAYLANG;
        
        $newProductEntityClass = $newProduct->getEntityClass();
        $oldProductEntityClass = $oldProduct->getEntityClass();
        
        if (empty($newProductEntityClass) || empty($oldProductEntityClass)) {
            return;
        }
        
        $productOptions        = $newProduct->getEntityAttributes();
        $productEntityObj      = $subscription->getProductEntity();
        $websiteCollectionRepo = \Env::get('em')->getRepository('Cx\Core_Modules\MultiSite\Model\Entity\WebsiteCollection');

        if (   $oldProductEntityClass == 'Cx\Core_Modules\MultiSite\Model\Entity\WebsiteCollection' 
            && $newProductEntityClass == 'Cx\Core_Modules\MultiSite\Model\Entity\Website'
        ) {
            \DBG::msg(__METHOD__.': Downgrade from WebsiteCollection to Website');
            throw new \Cx\Core\Error\Model\Entity\ShinyException(sprintf($_ARRAYLANG['TXT_CORE_MODULE_MULTISITE_UPDATE_SUBSCRIPTION_DOWNGRADE_ERROR'], $oldProduct->getName(), $newProduct->getName()));
        } elseif (   $oldProductEntityClass == 'Cx\Core_Modules\MultiSite\Model\Entity\WebsiteCollection'
                  && $newProductEntityClass == 'Cx\Core_Modules\MultiSite\Model\Entity\WebsiteCollection'
        ) {
            \DBG::msg(__METHOD__.': Update WebsiteCollection');
            if ($productEntityObj instanceof \Cx\Core_Modules\MultiSite\Model\Entity\WebsiteCollection) {
                $websiteCollectionRepo->setWebsiteCollectionMetaInformation($productEntityObj, $productOptions);
                $em = \Cx\Core\Core\Controller\Cx::instanciate()->getDb()->getEntityManager();
                $em->getUnitOfWork()->computeChangeSet(
                    $em->getClassMetadata('Cx\Core_Modules\MultiSite\Model\Entity\WebsiteCollection'), $productEntityObj
                );
            }
        } elseif (   $oldProductEntityClass == 'Cx\Core_Modules\MultiSite\Model\Entity\Website'
                  && $newProductEntityClass == 'Cx\Core_Modules\MultiSite\Model\Entity\WebsiteCollection'
        ) {
            \DBG::msg('Upgrade from Website to WebsiteCollection');
            $websiteCollection = new \Cx\Core_Modules\MultiSite\Model\Entity\WebsiteCollection();
            $websiteCollectionRepo->setWebsiteCollectionMetaInformation($websiteCollection, $productOptions);

            // attach existing Website to new WebsiteCollection (in case the subscription used to have a Website)
            if ($productEntityObj instanceof \Cx\Core_Modules\MultiSite\Model\Entity\Website) {
                $websiteCollection->setWebsite($productEntityObj);
            }

            $websiteCollection->setTempData(array('assignedSubscriptionId' => $subscription->getId()));
            $this->entitiesToPersistOnPostFlush[] = $websiteCollection;
        }
    }

    public function postFlush($eventArgs) {
        // check if there are any new entities present we need to persist
        if (!count($this->entitiesToPersistOnPostFlush)) {
            return;
        }

        // persist the new entities
        $em = \Cx\Core\Core\Controller\Cx::instanciate()->getDb()->getEntityManager();
        while ($entity = array_shift($this->entitiesToPersistOnPostFlush)) {
            $em->persist($entity);
        }
        $em->flush();
    }

    public function onEvent($eventName, array $eventArgs) {
        \DBG::msg(__METHOD__.": $eventName");
        $this->$eventName(current($eventArgs));
    }
}
