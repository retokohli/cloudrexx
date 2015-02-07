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
class CronController {
    
    /**
     * Constructor
     */
    public function __construct() {}
    
    /**
     * Send the Notification email to Website owners
     */
    public function sendNotificationMails() {
        $cronMailRepo    = \Env::get('em')->getRepository('Cx\Core_Modules\MultiSite\Model\Entity\CronMail');
        $cronMailLogRepo = \Env::get('em')->getRepository('Cx\Core_Modules\MultiSite\Model\Entity\CronMailLog');
        $websiteRepo     = \Env::get('em')->getRepository('Cx\Core_Modules\MultiSite\Model\Entity\Website');
        $objFWUser       = \FWUser::getFWUserObject();
        //Get all the cronMails
        $cronMails       = $cronMailRepo->findBy(array('active' => true));
        if (!$cronMails) {
            return;
        }
        
        foreach ($cronMails as $cronMail) {
            $userCriteria = array();
            $websiteCriteria = array();
            $cronMailCriterias = $cronMail->getCronMailCriterias();
            
            foreach ($cronMailCriterias as $cronMailCriteria) {
                $cronAttribute = $cronMailCriteria->getAttribute();
                
                //get all the users criteria
                if (preg_match('/^User/', $cronAttribute)) {
                    $userCriteria[$cronAttribute] = $cronMailCriteria->getCriteria();
                }
                //get all the websites criteria
                if (preg_match('/^Website/', $cronAttribute)) {
                    $websiteCriteria[$cronAttribute] = $cronMailCriteria->getCriteria();
                }
            }

            //Get all the users based on the criteria
            $userIds = $websiteRepo->getUsersByCriteria($userCriteria);
            //Get all the websites based on the criteria
            $websites = $websiteRepo->getWebsitesByCriteria($websiteCriteria, $userIds);
            if ($websites) {
                foreach ($websites as $website) {
                    //If owner is empty, proceed next
                    if (!$website->getOwnerId()) {
                        continue;
                    }
                    //load the owner
                    $objUser = $objFWUser->objUser->getUser($website->getOwnerId());
                    //If owner is not exists, proceed next
                    if (!$objUser) {
                        continue;
                    }

                    $cronMailLogEntity = $cronMailLogRepo->getOneCronMailLogByCriteria(array('id' => $cronMail->getId(),
                        'userId' => $website->getOwnerId(),
                        'websiteId' => $website->getId()));
                    //If the owner already have a log and status success, proceed next
                    if ($cronMailLogEntity && $cronMailLogEntity->getSuccess()) {
                        continue;
                    }
                    //send mail to website owner
                    $mailStatus = \Cx\Core\MailTemplate\Controller\MailTemplate::send(array(
                                'section' => 'MultiSite',
                                'lang_id' => 1,
                                'key'     => $cronMail->getMailTemplateKey(),
                                'to'      => $objUser->getEmail(),
                                'search'  => array(
                                    '[[WEBSITE_CREATION_DATE]]',
                                    '[[WEBSITE_NAME]]',
                                    '[[WEBSITE_MAIL]]',
                                    '[[CUSTOMER_MAIL]]',
                                    '[[WEBSITE_DOMAIN]]',
                                    '[[CUSTOMER_NAME]]'),
                                'replace' => array(
                                    $website->getCreationDate()->format(ASCMS_DATE_FORMAT_INTERNATIONAL_DATETIME),
                                    $website->getName(),
                                    $objUser->getEmail(),
                                    $objUser->getEmail(),
                                    $website->getName() . '.' . \Cx\Core\Setting\Controller\Setting::getValue('multiSiteDomain'),
                                    \FWUser::getParsedUserTitle($objUser))
                    ));
                    //If the owner already have a log and status failed, update the log
                    if ($cronMailLogEntity && !$cronMailLogEntity->getSuccess()) {
                        $cronMailLogEntity->setSuccess($mailStatus ? true : false);
                    }
                    //Otherwise create a new log
                    if (!$cronMailLogEntity) {
                        $cronMailLog = new \Cx\Core_Modules\MultiSite\Model\Entity\CronMailLog();
                        $cronMailLog->setUserId($website->getOwnerId());
                        $cronMailLog->setWebsiteId($website->getId());
                        $cronMailLog->setSuccess($mailStatus ? true : false);
                        $cronMail->addCronMailLog($cronMailLog);
                        \Env::get('em')->persist($cronMailLog);
                    }
                    \Env::get('em')->flush();
                }
            }
        }
    }
}
