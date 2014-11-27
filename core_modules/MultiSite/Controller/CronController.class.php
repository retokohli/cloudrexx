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
        if ($cronMails) {
            //write mail
            \Cx\Core\MailTemplate\Controller\MailTemplate::init('MultiSite');
            foreach ($cronMails as $cronMail) {
                $cronMailCriterias = $cronMail->getCronMailCriterias();
                //where conditions
                $criteria = array();
                $isUserVerified = false;
                $creationDateCriteria = '';
                if ($cronMailCriterias) {
                    foreach ($cronMailCriterias as $cronMailCriteria) {
                        switch ($cronMailCriteria->getAttribute()) {
                            case 'creationDate':
                                $creationDateCriteria = $cronMailCriteria->getCriteria();
                                if (preg_match('#^[ON\ | BEFORE\ | AFTER\ ]#i', $creationDateCriteria)) {
                                    $criteria['creationDate'] = $cronMailCriteria->getCriteria();
                                    $creationDateCriteria     = '';
                                }
                                break;
                            case 'websiteServiceServer':
                                $criteria['websiteServiceServerId'] = $cronMailCriteria->getCriteria();
                                break;
                            case 'verified':
                                if ($cronMailCriteria->getCriteria()) {
                                    $isUserVerified = true;
                                }
                                break;
                            default :
                                $criteria[$cronMailCriteria->getAttribute()] = $cronMailCriteria->getCriteria();
                                break;
                        }
                    }
                }
                //Get all the websites based on the criteria
                $websites = $websiteRepo->getWebsitesByCriteria($criteria);
                if ($websites) {
                    foreach ($websites as $website) {
                        //checking creationDate criteria(like Date, Time format)
                        if (!empty($creationDateCriteria)) {
                            $currentDate  = new \DateTime('now');
                            $creationDate = $website->getCreationDate()->modify($creationDateCriteria);
                            if ($creationDate->format('Y-m-d') != $currentDate->format('Y-m-d')) {
                                continue;
                            }
                        }
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
                        //check If user is verified or not
                        if ($isUserVerified) {
                            if (!$objUser->isVerified()) {
                                continue;
                            }
                        }
                        $cronMailLogEntity = $cronMailLogRepo->getOneCronMailLogByCriteria(array('id'        => $cronMail->getId(),
                                                                                                 'userId'    => $website->getOwnerId(), 
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
                                            $objUser->getUsername()),
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
}
