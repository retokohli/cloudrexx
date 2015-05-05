<?php
/**
 * Class CronController
 *
 * @copyright   CONTREXX CMS - COMVATION AG
 * @author      Project Team SS4U <info@comvation.com>
 * @package     contrexx
 * @subpackage  coremodule_multisite
 */

namespace Cx\Core_Modules\MultiSite\Controller;

/**
 * Class CronController
 *
 * The main Cron component
 *
 * @copyright   CONTREXX CMS - COMVATION AG
 * @author      Project Team SS4U <info@comvation.com>
 * @package     contrexx
 * @subpackage  coremodule_multisite
 */
class CronController extends \Cx\Core\Core\Model\Entity\Controller {
    
    /**
     * Constructor
     */
    public function __construct(\Cx\Core\Core\Model\Entity\SystemComponentController $systemComponentController, \Cx\Core\Core\Controller\Cx $cx)
    {
        parent::__construct($systemComponentController, $cx);
    }
    
    /**
     * Send the Notification email to Website owners
     */
    public function sendNotificationMails() {
        $em              = \Cx\Core\Core\Controller\Cx::instanciate()->getDb()->getEntityManager();
        $cronMailRepo    = $em->getRepository('Cx\Core_Modules\MultiSite\Model\Entity\CronMail');
        $cronMailLogRepo = $em->getRepository('Cx\Core_Modules\MultiSite\Model\Entity\CronMailLog');
        //Get all the cronMails
        $cronMails       = $cronMailRepo->findBy(array('active' => true));
        if (!$cronMails) {
            return;
        }
        
        foreach ($cronMails as $cronMail) {
            $criterias = array();
            $filterIds = array();
            foreach ($cronMail->getCronMailCriterias() as $cronMailCriteria) {
                list($tableAlias, $attribute) = explode('.', $cronMailCriteria->getAttribute());
                $criterias[$tableAlias][$attribute] = $cronMailCriteria->getCriteria();
            }
            $isWebsiteCriteriaExists = isset($criterias['Website']);
            if (isset($criterias['Subscription'])) {
                if (!$isWebsiteCriteriaExists && !isset($criterias['User'])) {
                    $isWebsiteCriteriaExists = true;
                }
                //Get all the website / owner ids based on the Subscription criteria and $isWebsiteCriteriaExists
                $filterIds = $this->getWebsiteOrUserIdsBasedOnSubscriptionsCriteria(array('Subscription' => $criterias['Subscription']), $isWebsiteCriteriaExists);
                if (empty($filterIds)) {
                    continue;
                }
                unset($criterias['Subscription']); // unset the Subscription criteria
            }
            
            //Get all the websites and owners based on the criteria
            $results = $this->getWebsitesOrOwnersByCriteria($criterias, $isWebsiteCriteriaExists, $filterIds);
            if (empty($results)) {
                continue;
            }
            foreach ($results as $result) {
                //if $isWebsiteCriteriaExists is set send mail to each website owner
                $user    = !$isWebsiteCriteriaExists ? $result : $result->getOwner();
                $website = !$isWebsiteCriteriaExists ? $em->getRepository('\Cx\Core_Modules\MultiSite\Model\Entity\Website')
                                                          ->findWebsitesByCriteria(array('user.id' => $user->getId()))
                                                     : $result;
                                                        
                \DBG::msg(__METHOD__.": matched CronMail (ID={$cronMail->getId()}): User=".\FWUser::getParsedUserTitle($user).(($website instanceof \Cx\Core_Modules\MultiSite\Model\Entity\Website) ? '; Website='.$website->getName() : ''));
                $this->sendMail($cronMail, $user, $website, $cronMailLogRepo);
            }
        }
    }
    
    /**
     * Send Cron mail to website User
     * 
     * @param \Cx\Core_Modules\MultiSite\Model\Entity\CronMail      $cronMail
     * @param \Cx\Core_Modules\MultiSite\Model\Entity\MultiSiteUser $objUser
     * @param mixed                                                 $websiteObj
     * @param object                                                $cronMailLogRepo
     * 
     * @return boolean
     */
    public function sendMail(\Cx\Core_Modules\MultiSite\Model\Entity\CronMail $cronMail, 
                             \Cx\Core\User\Model\Entity\User $objUser, 
                             $websiteObj, $cronMailLogRepo) 
    {
        //check already mail send to that owner
        $logCriteria = array('id' => $cronMail->getId(), 'userId' => $objUser->getId());
        if ($websiteObj instanceof \Cx\Core_Modules\MultiSite\Model\Entity\Website) {
            $logCriteria['websiteId'] = $websiteObj->getId();
        }
        $cronMailLogEntity = $cronMailLogRepo->getOneCronMailLogByCriteria($logCriteria);
        
        //If the owner already have a log and status success, proceed next
        if ($cronMailLogEntity && $cronMailLogEntity->getSuccess()) {
            return;
        }
     
        if ($websiteObj instanceof \Cx\Core_Modules\MultiSite\Model\Entity\Website) {
            $search  = array(
                            '[[WEBSITE_CREATION_DATE]]',
                            '[[WEBSITE_NAME]]',
                            '[[WEBSITE_MAIL]]',
                            '[[CUSTOMER_MAIL]]',
                            '[[WEBSITE_DOMAIN]]',
                            '[[CUSTOMER_NAME]]'
                        );
            $replace = array(
                            $websiteObj->getCreationDate()->format(ASCMS_DATE_FORMAT_INTERNATIONAL_DATETIME),
                            $websiteObj->getName(),
                            $objUser->getEmail(),
                            $objUser->getEmail(),
                            $websiteObj->getName() . '.' . \Cx\Core\Setting\Controller\Setting::getValue('multiSiteDomain','MultiSite'),
                            \FWUser::getParsedUserTitle($objUser)
                        );
             $substitution = array();   
        } else {
            $websiteDetails = array();            
            if (!empty($websiteObj) && is_array($websiteObj)) {
                foreach ($websiteObj as $website) {
                    $websiteDetails[] = array(
                        'WEBSITE_CREATION_DATE' => $website->getCreationDate()->format(ASCMS_DATE_FORMAT_INTERNATIONAL_DATETIME),
                        'WEBSITE_NAME'          => $website->getName(),
                        'WEBSITE_MAIL'          => $objUser->getEmail(),
                        'WEBSITE_DOMAIN'        => $website->getName() . '.' . \Cx\Core\Setting\Controller\Setting::getValue('multiSiteDomain','MultiSite')
                    );
                }
            }
            
            $search  = array(
                            '[[CUSTOMER_MAIL]]',
                            '[[CUSTOMER_NAME]]',
                        );
            $replace = array(
                            $objUser->getEmail(),
                            \FWUser::getParsedUserTitle($objUser),
                        );            
            $substitution = array('WEBSITE_LIST' => array(0 => array('WEBSITE_DETAIL' => $websiteDetails)));
        }
        
        //send mail to website owner
        $arrValues = array(
                        'section' => 'MultiSite',
                        'lang_id' => 1,
                        'key'     => $cronMail->getMailTemplateKey(),
                        'to'      => $objUser->getEmail(),
                        'search'  => $search,
                        'replace' => $replace,
                        'substitution' => $substitution);
        
        \DBG::msg(__METHOD__." ID={$cronMail->getId()}) / User=".\FWUser::getParsedUserTitle($objUser).(($website instanceof \Cx\Core_Modules\MultiSite\Model\Entity\Website) ? '; Website='.$website->getName() : ''));
        $mailStatus = \Cx\Core\MailTemplate\Controller\MailTemplate::send($arrValues);
        
        //If the owner already have a log and status failed, update the log
        if ($cronMailLogEntity && !$cronMailLogEntity->getSuccess()) {
            $cronMailLogEntity->setSuccess($mailStatus ? true : false);
        }
        
        //Otherwise create a new log
        if (!$cronMailLogEntity) {
            $cronMailLog = new \Cx\Core_Modules\MultiSite\Model\Entity\CronMailLog();
            $cronMailLog->setUserId($objUser->getId());
            if ($websiteObj instanceof \Cx\Core_Modules\MultiSite\Model\Entity\Website) {
                $cronMailLog->setWebsiteId($websiteObj->getId());
            }
            $cronMailLog->setSuccess($mailStatus ? true : false);
            $cronMail->addCronMailLog($cronMailLog);
            \Env::get('em')->persist($cronMailLog);
        }
        \Env::get('em')->flush();
        
        return true;
    }
    
    /**
     * Add the date filter to the query builder
     * 
     * @param \Doctrine\ORM\QueryBuilder $qb             Query builder object
     * @param string                     $fieldName      filter field name
     * @param string                     $filterCriteria filter criteria    
     * @param int                        $filterPos      current postion of filter query
     * @param boolean                    $useTimeStamp   use datetime or timestamp in the query
     * 
     * @return null
     */
    public function addDateFilterToQueryBuilder(\Doctrine\ORM\QueryBuilder & $qb, $fieldName, $filterCriteria, & $filterPos, $useTimeStamp = false)
    {
        if (empty($fieldName) || empty($filterCriteria)) {
            return;
        }
        
        $criteria = preg_replace('#^\+#i', '-', $filterCriteria);  // +n days = (date - n days)
        $format   = preg_replace('/\b(ON|BEFORE|AFTER) \b/i', '', $criteria);
        
        $startDate = new \DateTime($format);
        $startDate->setTime(0, 0, 1);
        
        $method = ($filterPos == 1) ? 'where' : 'andWhere';
        switch (true) {
            case preg_match('#^ON\ #i', $criteria):
            case preg_match('#^\-#i', $criteria):
                $qb
                    ->$method($fieldName . ' > ?'. $filterPos)
                    ->setParameter($filterPos, self::parseTimeForFilter($startDate, $useTimeStamp));                
                $startDate->setTime(23, 59, 59);
                $filterPos++;
                
                $qb
                    ->andWhere($fieldName . ' < ?'. $filterPos)
                    ->setParameter($filterPos,  self::parseTimeForFilter($startDate, $useTimeStamp));
                break;
            case preg_match('#^BEFORE\ #i', $criteria):
                $qb
                    ->$method($fieldName . '< ?'. $filterPos)
                    ->setParameter($filterPos, self::parseTimeForFilter($startDate, $useTimeStamp));
                break;
            case preg_match('#^AFTER\ #i', $criteria):
                $startDate->setTime(23, 59, 59);
                $qb
                    ->$method($fieldName . ' > ?'. $filterPos)
                    ->setParameter($filterPos, self::parseTimeForFilter($startDate, $useTimeStamp));
                break;
        }
        $filterPos++;        
    }
    
    /**
     * Add the filter to the query builder
     * 
     * @param \Doctrine\ORM\QueryBuilder $qb
     * @param array $cronMailCriterias
     * @param array $dateFields
     * 
     * return null
     */
    public function addFilterToQueryBuilder(\Doctrine\ORM\QueryBuilder & $qb, $cronMailCriterias, $dateFields) 
    {
        $filterPos = 1;
        $userDateField = array('User.regdate', 'User.lastAuth', 'User.lastActivity');
        foreach ($cronMailCriterias as $alias => $criterias) {
            foreach ($criterias as $attribute => $criteria) {
                $formattedCriteria = $alias . '.' . $attribute;
                //for date field
                if (in_array($formattedCriteria, $dateFields)) {
                    $timeStamp = in_array($formattedCriteria, $userDateField) ? true : false;
                    $this->addDateFilterToQueryBuilder($qb, $formattedCriteria, $criteria, $filterPos, $timeStamp);
                } else {
                    $qb->andWhere($formattedCriteria . ' = ?' . $filterPos)->setParameter($filterPos, $criteria);
                    $filterPos++;
                }                
            }
        }
    }

    /**
     * get the websites or owners by criteria
     * 
     * @param array   $cronMailCriterias
     * @param boolean $isWebsiteCriteriaExists
     * @param array   $filterIds
     * 
     * @return array
     */
    public function getWebsitesOrOwnersByCriteria($cronMailCriterias, $isWebsiteCriteriaExists, $filterIds) {
        
        try {
            $qb = \Cx\Core\Core\Controller\Cx::instanciate()->getDb()->getEntityManager()->createQueryBuilder();
            if ($isWebsiteCriteriaExists) {
                $qb->select('Website')
                   ->from('\Cx\Core_Modules\MultiSite\Model\Entity\Website', 'Website')
                   ->leftJoin('Website.owner', 'User');
            } else {
                $qb->select('User')
                   ->from('\Cx\Core\User\Model\Entity\User', 'User');
            }
                    
            $dateFields = array('User.regdate', 'User.lastAuth', 'User.lastActivity', 'Website.creationDate', );
            $this->addFilterToQueryBuilder($qb, $cronMailCriterias, $dateFields);
            
            if (!empty($filterIds)) {
                $qb->andWhere($qb->expr()->in($isWebsiteCriteriaExists ? 'Website.id' : 'User.id', $filterIds));
            }
            
            $objResult = $qb->getQuery()->getResult();
        } catch (\Doctrine\ORM\NoResultException $e) {
            \DBG::dump('CronController (getWebsiteOwnersByCriteria) : Failed to get the website owners by criteria' . $e->getMessage());
            $objResult = array();
        }
        
        return $objResult;
    }
    
    /**
     * Get the timestamp or date value based on the date time object
     * 
     * @param \DateTime $date
     * @param boolean   $timeStamp
     * 
     * @return array
     */
    public static function parseTimeForFilter(\DateTime $date, $timeStamp = false) {
        return $timeStamp ? $date->getTimestamp() : $date->format('Y-m-d H:i:s');
    }
    
    /**
     * get the Website / User ids based on the Subscription criteria
     * 
     * @param array   $cronMailCriterias
     * @param boolean $isWebsiteCriteriaExists
     * 
     * @return type
     */
    public function getWebsiteOrUserIdsBasedOnSubscriptionsCriteria($cronMailCriterias, $isWebsiteCriteriaExists) {
        $qb = \Cx\Core\Core\Controller\Cx::instanciate()->getDb()->getEntityManager()->createQueryBuilder();
        $qb
            ->select('Subscription')
            ->from('\Cx\Modules\Order\Model\Entity\Subscription', 'Subscription');
        
        $dateFields = array('Subscription.subscriptionDate', 'Subscription.expirationDate',
                            'Subscription.renewalDate', 'Subscription.terminationDate');
        $this->addFilterToQueryBuilder($qb, $cronMailCriterias, $dateFields);

        $filterIds = array();
        $subscriptions = $qb->getQuery()->getResult();
        if (!empty($subscriptions)) {
            foreach ($subscriptions as $subscription) {
                $productEntity = $subscription->getProductEntity();
                if (!$productEntity) {
                    continue;
                }
                
                if ($productEntity instanceof \Cx\Core_Modules\MultiSite\Model\Entity\Website) {
                    $filterIds[] = $isWebsiteCriteriaExists ? $productEntity->getId() : self::getUserIdFromWebsite($productEntity);
                } elseif (   
                       $productEntity instanceof \Cx\Core_Modules\MultiSite\Model\Entity\WebsiteCollection 
                    && $productEntity->getWebsites()
                ) {
                    foreach ($productEntity->getWebsites() as $website) {
                        $filterIds[] = $isWebsiteCriteriaExists ? $website->getId() : self::getUserIdFromWebsite($website);
                    }
                }
            }
        }
        
        return $filterIds;
    }
    
    /**
     * Get the userId from the website by id
     * 
     * @param object  $website
     * 
     * @return mixed integer|boolean
     */
    public static function getUserIdFromWebsite(\Cx\Core_Modules\MultiSite\Model\Entity\Website $website) {
        
        if ($website && $website->getOwner()) {
            return $website->getOwner()->getId();
        }
        return false;
    }
}
