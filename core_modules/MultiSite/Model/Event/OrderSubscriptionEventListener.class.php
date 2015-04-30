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
            \Env::get('cx')->getEvents()->triggerEvent('model/payComplete', array(new \Doctrine\ORM\Event\LifecycleEventArgs($subscription, \Env::get('em'))));
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
            $productEntityObj = $subscription->getProductEntity();
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

            // fetch old product entity of subscription
            $oldProductEntity = $oldProduct->getEntityById($subscription->getProductEntityId());

            // set new product entity
            $subscription->setProductEntity($websiteCollection);
            $websiteCollection->setTempData(array('assignedSubscriptionId' => $subscription->getId()));
            $this->entitiesToPersistOnPostFlush[] = $websiteCollection;
     
            // attach existing Website to new WebsiteCollection (in case the subscription used to have a Website)
            if ($oldProductEntity instanceof \Cx\Core_Modules\MultiSite\Model\Entity\Website) {
                $websiteCollection->addWebsite($oldProductEntity);
            }
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

    /**
     * Pay Complete Event
     * 
     * @param type $eventArgs
     */
    public function payComplete($eventArgs) {
        $subscription           = $eventArgs->getEntity();
        $productEntity          = $subscription->getProductEntity();
        $entityAttributes       = $subscription->getProduct()->getEntityAttributes();
        $websiteTemplate        = null;
        
        if ($productEntity instanceof \Cx\Core_Modules\MultiSite\Model\Entity\WebsiteCollection) {
            $websites = $productEntity->getWebsites();
        } elseif ($productEntity instanceof \Cx\Core_Modules\MultiSite\Model\Entity\Website) {
            $websites = array($productEntity);
        } else {
            return;
        }
        
        \DBG::msg(__METHOD__ . ': Subscription::$productEntity is '.get_class($productEntity));

        // load website template
        if (isset($entityAttributes['websiteTemplate'])) {
            $websiteTemplate = \Env::get('em')->getRepository('Cx\Core_Modules\MultiSite\Model\Entity\WebsiteTemplate')->findOneById($entityAttributes['websiteTemplate']);
        }

        // assign website template to WebsiteCollection
        if ($websiteTemplate && $productEntity instanceof \Cx\Core_Modules\MultiSite\Model\Entity\WebsiteCollection) {
            $productEntity->setWebsiteTemplate($websiteTemplate);
        }

        if ($subscription->getExpirationDate()) {
            $entityAttributes['subscriptionExpiration'] = $subscription->getExpirationDate()->getTimestamp();
        }
        
        foreach ($websites as $website) {
            if (!($website instanceof \Cx\Core_Modules\MultiSite\Model\Entity\Website)) {
                continue;
            }

            $entityAttributes['initialSignUp'] = false;
            switch ($website->getStatus()) {
                case \Cx\Core_Modules\MultiSite\Model\Entity\Website::STATE_INIT:
                    // perform initial sign-up in case the user has not yet been verified
                    $entityAttributes['initialSignUp'] = !\FWUser::getFWUserObject()->objUser->getUser($website->getOwner()->getId(), true)->isVerified();
// why return????
                    $website->setup($entityAttributes);
                    break;
                
                case \Cx\Core_Modules\MultiSite\Model\Entity\Website::STATE_DISABLED:
                    $website->setStatus(\Cx\Core_Modules\MultiSite\Model\Entity\Website::STATE_OFFLINE);
                    
                case \Cx\Core_Modules\MultiSite\Model\Entity\Website::STATE_ONLINE:
                case \Cx\Core_Modules\MultiSite\Model\Entity\Website::STATE_OFFLINE:
                    if ($websiteTemplate instanceof \Cx\Core_Modules\MultiSite\Model\Entity\WebsiteTemplate) {
                        $website->setupLicense($entityAttributes);
                    }
                    break;

                default:
                    break;
            }
        }
        
    }

    /**
     * Migrate expired subscriptions to new products
     * 
     * @param object $eventArgs
     */
    public function expired($eventArgs) {
        $subscription      = $eventArgs->getEntity();
        $productEntity     = $subscription->getProductEntity();
        $entityAttributes  = $subscription->getProduct()->getEntityAttributes();
        $websiteTemplate   = null;

        // abort in case the subscription is not of our concern (not based on a Website or WebsiteCollection)
        if (   !($productEntity instanceof \Cx\Core_Modules\MultiSite\Model\Entity\Website)
            && !($productEntity instanceof \Cx\Core_Modules\MultiSite\Model\Entity\WebsiteCollection)
        ) {
            return;
        }

        // load website template
        if (isset($entityAttributes['websiteTemplate'])) {
            $websiteTemplate = \Env::get('em')->getRepository('Cx\Core_Modules\MultiSite\Model\Entity\WebsiteTemplate')->findOneById($entityAttributes['websiteTemplate']);
        }

        // abort in case the associated product of the expired subscription
        // does not have a WebsiteTemplate defined
        if (!$websiteTemplate || !($websiteTemplate instanceof \Cx\Core_Modules\MultiSite\Model\Entity\WebsiteTemplate)) {
            return;
        }

        // Fetch the Product the expired websites shall be migrated to.
        // Abort in case no migration path has been defined.
        $migrationProduct = $websiteTemplate->getMigrationProductOnExpiration();
        if (!$migrationProduct || !($migrationProduct instanceof \Cx\Modules\Pim\Model\Entity\Product)) {
            return;
        }

        // migrate the subscription to the new product
        \DBG::msg("Migrate subscription {$subscription->getId()} from {$subscription->getProduct()->getName()} to {$migrationProduct->getName()}");
        $subscription->setProduct($migrationProduct);

        // set new expiration date of subscription
        if ($migrationProduct->isExpirable()) {
            $subscription->setExpirationDate($migrationProduct->getExpirationDate());
        } else {
            $subscription->setExpirationDate(null);
        }

        // Flushing the subscription at this point is basically obsolete,
        // as the cronjob Cx\Modules\Order\Controller\ComponentController::touchProductEntitiesOfExpiredSubscriptions
        // does also perform a flush afterwards.
        // However, we could end up in a situation where there are too many subscriptions
        // to be flushed which could result in a memory overflow or timeout exception.
        // Therefore we shall perform a flush operation for each individual subscription.
        // This will slow down the overall operation but shall ensure that the task can
        // get done, even if it may require for the operation (cronjob) to be run more
        // then once to get done.
        \Cx\Core\Core\Controller\Cx::instanciate()->getDb()->getEntityManager()->flush();
    }

    /**
     * Terminated Event Change the website status to offline
     * 
     * @param object $eventArgs
     */
    public function terminated($eventArgs) {
        $subscription      = $eventArgs->getEntity();
        $productEntity     = $subscription->getProductEntity();
        
        if ($productEntity instanceof \Cx\Core_Modules\MultiSite\Model\Entity\WebsiteCollection) {
            // disable all associated websites of websiteCollection
            foreach ($productEntity->getWebsites() as $website) {
                $website->setStatus(\Cx\Core_Modules\MultiSite\Model\Entity\Website::STATE_DISABLED);
            }
        } elseif ($productEntity instanceof \Cx\Core_Modules\MultiSite\Model\Entity\Website) {
            // disable associated website
            $productEntity->setStatus(\Cx\Core_Modules\MultiSite\Model\Entity\Website::STATE_DISABLED);
        }
    }

    public function onEvent($eventName, array $eventArgs) {
        \DBG::msg(__METHOD__.": $eventName");
        $this->$eventName(current($eventArgs));
    }
}
