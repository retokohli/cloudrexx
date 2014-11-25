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
                if ($cronMailCriterias) {
                    foreach ($cronMailCriterias as $cronMailCriteria) {
                        switch ($cronMailCriteria->getAttribute()) {
                            case 'creationDate':
                                $criteria['creationDate'] = $cronMailCriteria->getCriteria();
                                break;
                            case 'codeBase':
                                $criteria['codeBase']     = $cronMailCriteria->getCriteria();
                                break;
                            case 'status':
                                $criteria['status']       = $cronMailCriteria->getCriteria();
                                break;
                            case 'websiteServiceServer':
                                $criteria['websiteServiceServerId'] = $cronMailCriteria->getCriteria();
                                break;
                            case 'verified':
                                if ($cronMailCriteria->getCriteria()) {
                                    $isUserVerified = true;
                                }
                                break;
                        }
                    }
                }
                //Get all the website owners based on the criteria
                $websites = $websiteRepo->getWebsiteOwnersByCriteria($criteria);
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
                        //If criteria is verified
                        if ($isUserVerified) {
                            if (!$objUser->isVerified()) {
                                continue;
                            }
                        }
                        $cronMailLogEntity = $cronMailLogRepo->findOneBy(array('userId' => $website->getOwnerId(), 'websiteId' => $website->getId(), 'success' => true));
                        //If the owner already have a log, proceed next
                        if ($cronMailLogEntity) {
                            continue;
                        }
                        //Otherwise create a new log
                        $cronMailLog = new \Cx\Core_Modules\MultiSite\Model\Entity\CronMailLog();
                        $cronMailLog->setUserId($website->getOwnerId());
                        $cronMailLog->setWebsiteId($website->getId());
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
                        $cronMailLog->setSuccess($mailStatus ? true : false);
                        $cronMail->addCronMailLog($cronMailLog);
                        \Env::get('em')->persist($cronMail);
                        \Env::get('em')->persist($cronMailLog);
                        \Env::get('em')->flush();
                    }
                }
            }
        }
    }
}
