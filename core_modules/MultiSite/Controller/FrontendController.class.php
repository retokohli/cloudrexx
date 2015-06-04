<?php
/**
 * FrontendController for MultiSite component
 *
 * @copyright   CONTREXX CMS - COMVATION AG
 * @author      COMVATION Development Team <info@comvation.com>
 * @package     contrexx
 * @subpackage  coremodule_multisite
 */

namespace Cx\Core_Modules\MultiSite\Controller;

/**
 * FrontendController for MultiSite component
 *
 * @copyright   CONTREXX CMS - COMVATION AG
 * @author      COMVATION Development Team <info@comvation.com>
 * @package     contrexx
 * @subpackage  coremodule_multisite
 */
class FrontendController extends \Cx\Core\Core\Model\Entity\SystemComponentFrontendController {
    /**
     * @param \Cx\Core\Html\Sigma $template Template containing content of resolved page
     */
    public function parsePage(\Cx\Core\Html\Sigma $template, $cmd) {
        global $_ARRAYLANG;
        
        switch ($cmd) {
            case 'Website':
                if (empty($_GET['id'])) {
                    break;
                }
                $websiteId = intval($_GET['id']);
                $websiteRepository = \Env::get('em')->getRepository('Cx\Core_Modules\MultiSite\Model\Entity\Website');
                $website = $websiteRepository->findOneById($websiteId);
                
                // check the website is actually owned by the signed-in user
                if (    !($website instanceof \Cx\Core_Modules\MultiSite\Model\Entity\Website) 
                     || $website->getOwner()->getId() != \FWUser::getFWUserObject()->objUser->getId()
                   ) {
                       $this->redirectToSubscriptionPage();
                }
                \Cx\Core\Core\Controller\Cx::instanciate()->getPage()->setTitle($website->getName());
                \Cx\Core\Core\Controller\Cx::instanciate()->getPage()->setContentTitle('Website - '.$website->getBaseDn()->getName() . ComponentController::getWebsiteNonOnlineStateAsLiteral($website));
                \Cx\Core\Core\Controller\Cx::instanciate()->getPage()->setMetaTitle($website->getName());
                break;
            case 'SubscriptionDetail':
                if (empty($_GET['id'])) {
                    break;
                }
                $subscriptionId = intval($_GET['id']);
                $subscriptionRepository = \Env::get('em')->getRepository('Cx\Modules\Order\Model\Entity\Subscription');
                $subscription = $subscriptionRepository->findOneById($subscriptionId);
                
                // check the subscription is actually owned by the signed-in user
                if (    !($subscription instanceof \Cx\Modules\Order\Model\Entity\Subscription) 
                     || !($subscription->getOrder() instanceof \Cx\Modules\Order\Model\Entity\Order)
                     || \FWUser::getFWUserObject()->objUser->getCrmUserId() != $subscription->getOrder()->getContactId()
                    ) {
                        $this->redirectToSubscriptionPage();
                }
                $subscriptionDescription = $subscription->getDescription();
                if (!empty($subscriptionDescription)) {
                    $subscriptionTitle = $subscriptionDescription;
                } else {
                    $subscriptionTitle = '#'.$subscription->getId();
                }
                \Cx\Core\Core\Controller\Cx::instanciate()->getPage()->setTitle($subscriptionTitle);
                \Cx\Core\Core\Controller\Cx::instanciate()->getPage()->setContentTitle($_ARRAYLANG['TXT_MULTISITE_WEBSITE_SUBSCRIPTION'].' '.$subscriptionTitle);
                \Cx\Core\Core\Controller\Cx::instanciate()->getPage()->setMetaTitle($subscriptionTitle);
                break;
            case 'Affiliate':
                if (!self::isUserLoggedIn()) {
                    return $_ARRAYLANG['TXT_MULTISITE_WEBSITE_LOGIN_NOACCESS'];            
                }
                
                $objUser = \FWUser::getFWUserObject()->objUser;
                \Cx\Core\Setting\Controller\Setting::init('MultiSite', '','FileSystem');
                $affiliateCreditRepo = \Env::get('em')->getRepository('\Cx\Core_Modules\MultiSite\Model\Entity\AffiliateCredit');
                $affiliatePayoutRepo = \Env::get('em')->getRepository('\Cx\Core_Modules\MultiSite\Model\Entity\AffiliatePayout');
                //get the affiliateIdProfileAttributeId
                $affiliateIdProfileAttributeId = \Cx\Core\Setting\Controller\Setting::getValue('affiliateIdProfileAttributeId','MultiSite');
                $affiliateId = $objUser->getProfileAttribute((int)$affiliateIdProfileAttributeId);
                //get the payPalProfileAttributeId
                $paypalEmailAddressProfileAttribute = \Cx\Core\Setting\Controller\Setting::getValue('payPalProfileAttributeId','MultiSite');
                $paypalEmailAddress = $objUser->getProfileAttribute((int)$paypalEmailAddressProfileAttribute);
                //display of affiliate-id
                $marketingWebsiteDomain = \Cx\Core\Setting\Controller\Setting::getValue('marketingWebsiteDomain','MultiSite');
                $affiliateIdQueryStringKey = \Cx\Core\Setting\Controller\Setting::getValue('affiliateIdQueryStringKey','MultiSite');
                if (!empty($affiliateId)) {
                    
                    //get the total Referrer count
                    $totalRefererCount = BackendController::getReferralCountByAffiliateId($affiliateId);
                    if (empty($totalRefererCount)) {
                        $template->touchBlock('showNoAffiliateErrorMsg');
                    }
                    
                    //show the solo, non-profit and business subscription counts
                    $subscriptionListByProduct = ComponentController::getReferralsSubscriptionIdsBasedOnProduct($affiliateId);
                    if (empty($subscriptionListByProduct)) {
                        $template->touchBlock('showNoReferralsErrorMsg');
                        $template->hideBlock('showReferralsSubscriptionCount');
                    } else {
                        foreach ($subscriptionListByProduct as $productName => $subscriptionIds) {
                            if (!empty($subscriptionIds)) {
                                $template->setVariable(array(
                                    'MULTISITE_SUBSCRIPTIONS_PRODUCT_NAME'           => $productName,
                                    'MULTISITE_SUBSCRIPTIONS_PRODUCT_PENDING_COUNT'  => $affiliateCreditRepo->getSubscriptionCountByCriteria(array('in' => array(array('s.id', $subscriptionIds)), 'ac.credited' => 0, 'payout' => 'IS NULL', 'user' => $objUser)),
                                    'MULTISITE_SUBSCRIPTIONS_PRODUCT_CREDITED_COUNT' => $affiliateCreditRepo->getSubscriptionCountByCriteria(array('in' => array(array('s.id', $subscriptionIds)), 'ac.credited' => 1, 'payout' => 'IS NULL', 'user' => $objUser)),
                                    'MULTISITE_SUBSCRIPTIONS_PRODUCT_PAYOUT_COUNT'   => $affiliateCreditRepo->getSubscriptionCountByCriteria(array('in' => array(array('s.id', $subscriptionIds)), 'payout' => 'IS NOT NULL', 'user' => $objUser)),
                                ));
                                $template->parse('showSubscriptionsCountByProduct');
                            }
                        }
                    }
                    $template->setVariable(array(
                        'MULTISITE_AFFILIATE_REFERRALS_COUNT' => $totalRefererCount
                    ));
                }
                //parse block for Affiliate Id
                !empty($affiliateId) ? $template->touchBlock('showAffiliateIdPartOne') : $template->hideBlock('showAffiliateIdPartOne');
                !empty($affiliateId) ? $template->touchBlock('showAffiliateIdPartTwo') : $template->hideBlock('showAffiliateIdPartTwo');
                !empty($affiliateId) ? $template->touchBlock('showCloudrexxBannerLinks') : $template->hideBlock('showCloudrexxBannerLinks');
                empty($affiliateId) ? $template->touchBlock('showAffiliateIdForm') : $template->hideBlock('showAffiliateIdForm');
                //parse block for paypal email address
                !empty($paypalEmailAddress) ? $template->touchBlock('showPaypalEmailAddress') : $template->hideBlock('showPaypalEmailAddress');
                empty($paypalEmailAddress) ? $template->touchBlock('showPaypalEmailAddressForm') : $template->hideBlock('showPaypalEmailAddressForm');
                //parse the form 
                !empty($affiliateId) && !empty($paypalEmailAddress) ? $template->hideBlock('showAffiliateForm') : $template->touchBlock('showAffiliateForm');
                !empty($affiliateId) && !empty($paypalEmailAddress) ? $template->touchBlock('showAffiliateCreditTotalAmt') : $template->hideBlock('showAffiliateCreditTotalAmt');
                
                //get the sum of affiliate credit amount
                $affiliateTotalCreditAmount = $affiliateCreditRepo->getTotalCreditsAmountByUser($objUser);                
                // as of for now, all affiliate commission is being paid out in CHF (=> currently default currency)
                $currencyId = \Cx\Modules\Crm\Controller\CrmLibrary::getDefaultCurrencyId();
                
                //calculate the Total
                $affiliatePayoutSum = $affiliatePayoutRepo->getTotalAmountByUser($objUser);
                $total = $affiliatePayoutSum + $affiliateTotalCreditAmount;
                
                $currencyObj  = \Env::get('em')->getRepository('\Cx\Modules\Crm\Model\Entity\Currency')->findOneById($currencyId);
                $currencyCode = $currencyObj ? $currencyObj->getName() : '';

                if (floatval($affiliateTotalCreditAmount) >= \Cx\Core\Setting\Controller\Setting::getValue('affiliatePayoutLimit','MultiSite')) {
                    $template->touchBlock('showPayoutButton');
                } else {
                    $template->hideBlock('showPayoutButton');
                }
                $template->setVariable(array(
                    'MULTISITE_MARKETING_WEBSITE'         => $marketingWebsiteDomain,
                    'MULTISITE_AFFILIATE_QUERY_STRING'    => $affiliateIdQueryStringKey,
                    'MULTISITE_AFFILIATE_PROFILE_ATTR_ID' => !empty($affiliateId) ? $affiliateId : '',
                    'MULTISITE_PAYPAL_EMAIL_ADDRESS'      => !empty($paypalEmailAddress) ? $paypalEmailAddress : $objUser->getEmail(),
                    'MULTISITE_AFFILIATE_CREDIT_AMT_TOTAL'=> number_format($affiliateTotalCreditAmount, 2) . ' ' . $currencyCode,
                    'MULTISITE_AFFILIATE_TOTAL_AMT'       => number_format($total, 2) . ' ' . $currencyCode,
                    'MULTISITE_MARKETING_WEBSITE'         => \Cx\Core\Setting\Controller\Setting::getValue('marketingWebsiteDomain','MultiSite')
                ));
                //initialize
                $objJs = \ContrexxJavascript::getInstance();
                $objJs->setVariable(array(
                    'TXT_CORE_MODULE_MULTISITE_NO_INPUT_ERROR' => $_ARRAYLANG['TXT_CORE_MODULE_MULTISITE_NO_INPUT_ERROR'],
                    'TXT_CORE_MODULE_MULTISITE_PAYPAL_EMAIL_ADDRESS_ERROR' => $_ARRAYLANG['TXT_CORE_MODULE_MULTISITE_PAYPAL_EMAIL_ADDRESS_ERROR']
                ), 'AffiliateSetup');
                //parse the paypal email address edit modal
                if (!empty($paypalEmailAddress)) {
                    $blockName = 'multisite_user';
                    $placeholderPrefix = strtoupper($blockName).'_';
                    $objAccessLib = new \Cx\Core_Modules\Access\Controller\AccessLib($template);
                    $objAccessLib->setModulePrefix($placeholderPrefix);
                    $objAccessLib->setAttributeNamePrefix($blockName.'_profile_attribute');
                    $objAccessLib->setAccountAttributeNamePrefix($blockName.'_account_');
                    
                    $objUser->objAttribute->first();
                    while (!$objUser->objAttribute->EOF) {
                        $objAttribute = $objUser->objAttribute->getById($objUser->objAttribute->getId());
                        if ((int)$paypalEmailAddressProfileAttribute === $objUser->objAttribute->getId()) {
                            $template->setVariable(array(
                                'MULTISITE_USER_PROFILE_ATTRIBUTE_ID'   =>  $objUser->objAttribute->getId(),
                                'MULTISITE_USER_PROFILE_ATTRIBUTE_DESC' =>  $objUser->objAttribute->getName(),
                                'MULTISITE_USER_PROFILE_ATTRIBUTE'      =>  $objAccessLib->parseAttribute($objUser, $objAttribute->getId(), 0, true, true)
                            ));
                            $template->parse('multisite_user_profile_attribute_list');
                        }
                        $objUser->objAttribute->next();
                    }
                    $template->setVariable(array(
                        'MULTISITE_USER_PROFILE_SUBMIT_URL'   => \Env::get('cx')->getWebsiteBackendPath() . '/index.php?cmd=JsonData&object=MultiSite&act=updateOwnUser',
                        'MULTISITE_PAYPAL_EMAIL_ATTRIBUTE_ID' => $paypalEmailAddressProfileAttribute,
                    ));
                }
                break;
            case 'NotificationUnsubscribe':
                $this->parseNotificationUnsubscribe($template);
                break;
            default:
        }
    }
        
    /**
     * Parse the section NotificationUnsubscribe
     * 
     * @param \Cx\Core\Html\Sigma $template Template object
     * 
     * @return null
     * 
     * @throws \Exception
     */
    private function parseNotificationUnsubscribe(\Cx\Core\Html\Sigma $template)
    {
        global $_ARRAYLANG;
        
        try {
            $cronMailLogId    = isset($_GET['i']) ? contrexx_input2raw($_GET['i']) : false;
            $cronMailLogToken = isset($_GET['t']) ? contrexx_input2raw($_GET['t']) : false;
            if (!$cronMailLogId || !$cronMailLogToken) {
                throw new \Exception('$cronMailLogId or $cronMailLogToken does not exist');
            }

            $cronMailLogRepo   = \Env::em()->getRepository('\Cx\Core_Modules\MultiSite\Model\Entity\CronMailLog');
            $cronMailLogResult = $cronMailLogRepo->findBy(array('id' => $cronMailLogId, 'token' => $cronMailLogToken));

            if (empty($cronMailLogResult)) {
                throw new \Exception('Cron mail log doesnot exist, id : '. $cronMailLogId .', token : '. $cronMailLogToken);
            }
            $cronMailLog = current($cronMailLogResult);
            $userId      = $cronMailLog->getUserId();
            $objUser     = \FWUser::getFWUserObject()->objUser->getUser($userId);
            if (!$objUser) {
                throw new \Exception('User doesnot exist, User id : '. $userId);
            }
            $notificationCancelledProfileAttributeId = \Cx\Core\Setting\Controller\Setting::getValue('notificationCancelledProfileAttributeId', 'MultiSite');
            $objUser->setProfile(array(
                $notificationCancelledProfileAttributeId => array(0 => true)
            ));
            if (!$objUser->store()) {
                throw new \Exception('Could not update the user, User id : '. $userId);
            }
        } catch (\Exception $e) {
            \DBG::log('NotificationUnsubscribe : '. $e->getMessage());
            $supportRecipientMailAddress = \Cx\Core\Setting\Controller\Setting::getValue('supportRecipientMailAddress', 'MultiSite');
            $template->setVariable(array(
                'MULTISITE_NOTIFICATION_UNSUBSCRIBE_MESSAGE' => sprintf($_ARRAYLANG['TXT_CORE_MODULES_MULTISITE_NOTIFICATION_UNSUBSCRIBE_ERROR'], $supportRecipientMailAddress),
                'MULTISITE_NOTIFICATION_UNSUBSCRIBE_MESSAGE_CLASS' => 'msg-error',
            ));
            return;
        }

        $template->setVariable(array(
            'MULTISITE_NOTIFICATION_UNSUBSCRIBE_MESSAGE' => $_ARRAYLANG['TXT_CORE_MODULES_MULTISITE_NOTIFICATION_UNSUBSCRIBE_SUCCESS'],
            'MULTISITE_NOTIFICATION_UNSUBSCRIBE_MESSAGE_CLASS' => 'msg-success',
        ));                
    }
    
    /**
     * Redirect to subscription overview page
     */
    public function redirectToSubscriptionPage()
    {
        $url = \Cx\Core\Routing\Url::fromModuleAndCmd('MultiSite', 'Subscription')->toString();
        \Cx\Core\Csrf\Controller\Csrf::header('Location: '. $url);
        exit;
    }
}
