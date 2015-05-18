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
        
        $notificationCancelledProfileAttributeId = \Cx\Core\Setting\Controller\Setting::getValue('notificationCancelledProfileAttributeId','MultiSite');                
        foreach ($cronMails as $cronMail) {
            $criterias = array();
            foreach ($cronMail->getCronMailCriterias() as $cronMailCriteria) {
                list($tableAlias, $attribute) = explode('.', $cronMailCriteria->getAttribute());
                $criterias[$tableAlias][$attribute] = $cronMailCriteria->getCriteria();
            }
            $isWebsiteCriteriaExists = isset($criterias['Website']) || isset($criterias['Subscription']);
            
            //Get all the websites and owners based on the criteria
            $results = $this->getWebsitesOrOwnersByCriteria($criterias);
            if (empty($results)) {
                continue;
            }
            foreach ($results as $result) {
                //if $isWebsiteCriteriaExists is set send mail to each website owner
                $user    = !$isWebsiteCriteriaExists ? $result : $result->getOwner();
                $website = !$isWebsiteCriteriaExists ? $em->getRepository('\Cx\Core_Modules\MultiSite\Model\Entity\Website')
                                                          ->findWebsitesByCriteria(array('user.id' => $user->getId()))
                                                     : $result;
                                                        
                $objUser = \FWUser::getFWUserObject()->objUser->getUser($user->getId());
                if ($objUser && !$objUser->getProfileAttribute($notificationCancelledProfileAttributeId)) {                    
                    \DBG::msg(__METHOD__.": matched CronMail (ID={$cronMail->getId()}): User=".\FWUser::getParsedUserTitle($user).(($website instanceof \Cx\Core_Modules\MultiSite\Model\Entity\Website) ? '; Website='.$website->getName() : ''));
                    $this->sendMail($cronMail, $user, $website, $cronMailLogRepo);
                }
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
        
        //create a new log
        if (!$cronMailLogEntity) {
            $cronMailLogEntity = new \Cx\Core_Modules\MultiSite\Model\Entity\CronMailLog();
            $cronMailLogEntity->setUserId($objUser->getId());
            if ($websiteObj instanceof \Cx\Core_Modules\MultiSite\Model\Entity\Website) {
                $cronMailLogEntity->setWebsiteId($websiteObj->getId());
            }
            $cronMailLogEntity->setSuccess(false);
            $cronMailLogEntity->setToken(\Cx\Core_Modules\MultiSite\Controller\JsonMultiSite::generateSecretKey());
            $cronMail->addCronMailLog($cronMailLogEntity);
            \Env::get('em')->persist($cronMailLogEntity);
            \Env::get('em')->flush();
        }

        $unSubscribeUrl = ComponentController::getApiProtocol() .
                           \Cx\Core\Setting\Controller\Setting::getValue('customerPanelDomain','MultiSite') .
                           '/' . \Cx\Core\Routing\Url::fromModuleAndCmd('MultiSite', 'NotificationUnsubscribe', null, array('i'=> $cronMailLogEntity->getId(),'t'=> $cronMailLogEntity->getToken()))->getPath();
            
        if ($websiteObj instanceof \Cx\Core_Modules\MultiSite\Model\Entity\Website) {
            $search  = array(
                            '[[WEBSITE_CREATION_DATE]]',
                            '[[WEBSITE_NAME]]',
                            '[[WEBSITE_MAIL]]',
                            '[[CUSTOMER_MAIL]]',
                            '[[WEBSITE_DOMAIN]]',
                            '[[CUSTOMER_NAME]]',
                            '[[UNSUBSCRIBE]]'
                        );
            $replace = array(
                            $websiteObj->getCreationDate()->format(ASCMS_DATE_FORMAT_INTERNATIONAL_DATETIME),
                            $websiteObj->getName(),
                            $objUser->getEmail(),
                            $objUser->getEmail(),
                            $websiteObj->getName() . '.' . \Cx\Core\Setting\Controller\Setting::getValue('multiSiteDomain','MultiSite'),
                            \FWUser::getParsedUserTitle($objUser),
                            $unSubscribeUrl
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
                            '[[UNSUBSCRIBE]]'
                        );
            $replace = array(
                            $objUser->getEmail(),
                            \FWUser::getParsedUserTitle($objUser),
                            $unSubscribeUrl
                        );            
            $substitution = array('WEBSITE_LISTS' => array(0 => array('WEBSITE_DETAILS' => $websiteDetails)));
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
        $cronMailLogEntity->setSentDate(new \DateTime());
        $cronMailLogEntity->setSuccess($mailStatus ? true : false);
        \Env::get('em')->flush();
              
        return true;
    }
    
    /**
     * Get the date filter for the query
     * 
     * @param string  $fieldName      filter field name
     * @param string  $filterCriteria filter criteria
     * @param boolean $useTimeStamp   use datetime or timestamp in the query
     * 
     * @return string $condition
     */
    public function getDateFilter($fieldName, $filterCriteria, $useTimeStamp = false)
    {
        if (empty($fieldName) || empty($filterCriteria)) {
            return;
        }
        
        $criteria = preg_replace('#^\+#i', '-', $filterCriteria);  // +n days = (date - n days)
        $format   = preg_replace('/\b(ON|BEFORE|AFTER) \b/i', '', $criteria);
        
        $condition = '';
        $startDate = new \DateTime($format);
        $startDate->setTime(0, 0, 0);
        
        switch (true) {
            case preg_match('#^ON\ #i', $criteria):
            case preg_match('#^\-#i', $criteria):
                $condition = $fieldName . ' >= "' . self::parseTimeForFilter($startDate, $useTimeStamp) . '" ';
                $startDate->setTime(23, 59, 59);
                $condition .= ' AND ' . $fieldName . ' <= "' . self::parseTimeForFilter($startDate, $useTimeStamp) . '" ';
                break;
            case preg_match('#^BEFORE\ #i', $criteria):
                $condition = $fieldName . ' < "' . self::parseTimeForFilter($startDate, $useTimeStamp) . '" ';
                break;
            case preg_match('#^AFTER\ #i', $criteria):
                $startDate->setTime(23, 59, 59);
                $condition = $fieldName . ' > "' . self::parseTimeForFilter($startDate, $useTimeStamp) . '" ';
                break;
        }
        
        return $condition;
    }
    
    /**
     * Get the filter for the query
     * 
     * @param array $cronMailCriterias
     * 
     * return array $conditions
     */
    public function getFilter($cronMailCriterias) 
    {
        $conditions     = array();
        $userDateField  = array('User.regdate', 'User.last_auth', 'User.last_activity');
        $dateFieldTypes = array(\Doctrine\DBAL\Types\Type::DATETIME, \Doctrine\DBAL\Types\Type::DATE);
        $classes        = array('Subscription' => 'Cx\Modules\Order\Model\Entity\Subscription',
                               'Website' => 'Cx\Core_Modules\MultiSite\Model\Entity\Website', 
                               'User'    => 'Cx\Core\User\Model\Entity\User');
        $em = \Cx\Core\Core\Controller\Cx::instanciate()->getDb()->getEntityManager();
        foreach ($cronMailCriterias as $alias => $criterias) {
            $classMetaData = $em->getClassMetadata($classes[$alias]);
            foreach ($criterias as $fieldName => $criteria) {
                $attribute = $classMetaData->getColumnName($fieldName);
                $fieldType = $classMetaData->getTypeOfColumn($attribute);
                $formattedCriteria  = $alias . '.' . $attribute;
                //for date field
                $isUserDateField = in_array($formattedCriteria, $userDateField);
                if ($isUserDateField || in_array($fieldType, $dateFieldTypes)) {
                    $conditions[] = $this->getDateFilter($formattedCriteria, $criteria, $isUserDateField);
                } else {
                    $conditions[] = $formattedCriteria . ' = "' . $criteria . '" ';
                }
            }
        }
        return $conditions;
    }

    /**
     * get the websites or owners by criteria
     * 
     * @param array $cronMailCriterias
     * 
     * @return array
     */
    public function getWebsitesOrOwnersByCriteria($cronMailCriterias)
    {
        try {
            $em = \Cx\Core\Core\Controller\Cx::instanciate()->getDb()->getEntityManager();
            if (isset($cronMailCriterias['Website']) || isset($cronMailCriterias['Subscription'])) {
                //NativeQuery for Website and Subscription Criteria
                $rsm   = self::getResultSetMapping(
                            array(
                                'Cx\Core_Modules\MultiSite\Model\Entity\Website' => '',
                                'Cx\Core\User\Model\Entity\User' => 'Website.owner'
                            ),
                            array(
                                'Website' => array('id', 'name', 'creationDate'),
                                'User'    => array('id', 'email')
                            )
                        );
                //we must set the alias name for all the select fields. Alias Format like as Table Alias name + Field name in CC Format
                $query = 'SELECT `Website`.`id` as WebsiteId, `Website`.`name` as WebsiteName, 
                                 `Website`.`creationDate` as WebsiteCreationDate, 
                                 `User`.`id` as UserId, `User`.`email` as UserEmail
                                FROM 
                                    `' . DBPREFIX . 'core_module_multisite_website` As Website
                                LEFT JOIN 
                                    `' . DBPREFIX . 'access_users` As User
                                ON
                                    `User`.`id` = `Website`.`ownerId`
                                LEFT JOIN 
                                    `' . DBPREFIX . 'module_order_subscription` As Subscription
                                ON 
                                    `Subscription`.`product_entity_id` = IF(`Website`.`websiteCollectionId` IS NULL, `Website`.`id`, `Website`.`websiteCollectionId`)';
            } else {
                //NativeQuery for User Criteria
                $rsm   = self::getResultSetMapping(
                            array('Cx\Core\User\Model\Entity\User' => ''),
                            array('User' => array('id', 'email'))
                        );
                $query = 'SELECT `User`.`id` as UserId, 
                                 `User`.`email` as UserEmail
                                FROM `' . DBPREFIX . 'access_users` as User';
            }

            $conditions = $this->getFilter($cronMailCriterias);
            $query .= !empty($conditions) ? ' WHERE ' . implode(' AND ', array_filter($conditions)) : '';
            
            $queryObj  = $em->createNativeQuery($query, $rsm);
            $objResult = $queryObj->getResult();
        } catch (\Doctrine\ORM\NoResultException $e) {
            \DBG::dump('CronController (getWebsiteOwnersByCriteria) : Failed to get the website owners by criteria' . $e->getMessage());
            $objResult = array();
        }
        
        return $objResult;
    }
    
    /**
     * Get the result set mapping object
     * 
     * @param array  $entityClasses
     * @param array  $requiredFields
     * 
     * @return \Doctrine\ORM\Query\ResultSetMapping
     */
    private static function getResultSetMapping($entityClasses = array(), $requiredFields = array())
    {
        if (empty($entityClasses)) {
            return;
        }
        
        $rsm = new \Doctrine\ORM\Query\ResultSetMapping();
        $first = true;
        foreach ($entityClasses as $class => $options) {
            $alias = end(explode('\\', $class));
            if ($first) {
                $rsm->addEntityResult($class, $alias);
            } else {
                list($parentAlias, $relationField) = explode('.', $options);
                $rsm->addJoinedEntityResult($class, $alias, $parentAlias, $relationField);
            }
            foreach ($requiredFields[$alias] as $field) {
                $rsm->addFieldResult($alias, $alias.ucfirst($field), $field);
            }
            $first = false;
        }
        
        return $rsm;
    }
    
    /**
     * Get the timestamp or date value based on the date time object
     * 
     * @param \DateTime $date
     * @param boolean   $timeStamp
     * 
     * @return string
     */
    public static function parseTimeForFilter(\DateTime $date, $timeStamp = false) {
        return $timeStamp ? $date->getTimestamp() : $date->format('Y-m-d H:i:s');
    }
}
