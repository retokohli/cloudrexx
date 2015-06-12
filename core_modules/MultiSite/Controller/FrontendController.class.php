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
                //get the affiliateIdProfileAttributeId
                $affiliateIdProfileAttributeId = \Cx\Core\Setting\Controller\Setting::getValue('affiliateIdProfileAttributeId','MultiSite');
                $affiliateId = $objUser->getProfileAttribute((int)$affiliateIdProfileAttributeId);
                
                //get the payPalProfileAttributeId
                $paypalEmailAddressProfileAttribute = \Cx\Core\Setting\Controller\Setting::getValue('payPalProfileAttributeId','MultiSite');
                $paypalEmailAddress = $objUser->getProfileAttribute((int)$paypalEmailAddressProfileAttribute);
                
                //parse the affiliate id details
                $this->parseSectionAffiliateDetails($template, $objUser);                                
                //parse the paypal email address edit modal                
                $this->parseSectionAffiliatePaypalDetails($template, $objUser);
                
                //parse the form 
                $showAffiliateForm = !empty($affiliateId) && !empty($paypalEmailAddress);
                ComponentController::showOrHideBlock($template, 'affiliateFormSubmitBtn', !$showAffiliateForm);
                ComponentController::showOrHideBlock($template, 'showAffiliateCreditTotalAmt', $showAffiliateForm);                 
                
                $template->setVariable(array(
                    'MULTISITE_AFFILIATE_PROFILE_ATTR_ID_LABEL' =>  !empty($affiliateId) 
                                                                  ? $_ARRAYLANG['TXT_CORE_MODULE_MULTISITE_AFFILIATE_PROFILE_ATTR_ID'] 
                                                                  : $_ARRAYLANG['TXT_CORE_MODULE_MULTISITE_CHOOSE_AFFILIATE_PROFILE_ATTR_ID'],
                    'MULTISITE_PAYPAL_EMAIL_ADDRESS'      => !empty($paypalEmailAddress) ? $paypalEmailAddress : $objUser->getEmail(),
                    'MULTISITE_MARKETING_WEBSITE'         => \Cx\Core\Setting\Controller\Setting::getValue('marketingWebsiteDomain','MultiSite'),
                    'MULTISITE_AFFILIATE_QUERY_STRING'    => \Cx\Core\Setting\Controller\Setting::getValue('affiliateIdQueryStringKey','MultiSite'),
                    'MULTISITE_MARKETING_WEBSITE'         => \Cx\Core\Setting\Controller\Setting::getValue('marketingWebsiteDomain','MultiSite'),
                    'MULTISITE_AFFILIATE_COOKIE_LIFETIME' => \Cx\Core\Setting\Controller\Setting::getValue('affiliateCookieLifetime','MultiSite'),
                ));
                break;
            case 'NotificationUnsubscribe':
                $this->parseNotificationUnsubscribe($template);
                break;
            default:
        }
    }
        
    /**
     * Parse the affiliate details for the frontend section affiliate
     * 
     * @param \Cx\Core\Html\Sigma $template Template object
     * @param \User               $objUser  User object
     */
    private function parseSectionAffiliateDetails(\Cx\Core\Html\Sigma $template, \User $objUser)
    {
        //get the affiliateIdProfileAttributeId
        $affiliateIdProfileAttributeId = \Cx\Core\Setting\Controller\Setting::getValue('affiliateIdProfileAttributeId','MultiSite');
        
        $affiliateId         = $objUser->getProfileAttribute((int)$affiliateIdProfileAttributeId);
        $isAffiliateIdExists = !empty($affiliateId);
        
        ComponentController::showOrHideBlock($template, 'affiliateIdValue', $isAffiliateIdExists);
        ComponentController::showOrHideBlock($template, 'showAffiliateIdPartTwo', $isAffiliateIdExists);
        ComponentController::showOrHideBlock($template, 'showCloudrexxBannerLinks', $isAffiliateIdExists);
        ComponentController::showOrHideBlock($template, 'affiliateIdInput', !$isAffiliateIdExists);
        
        if (!$isAffiliateIdExists) {
            return;
        }
        
        $affiliateCreditRepo = \Env::get('em')->getRepository('\Cx\Core_Modules\MultiSite\Model\Entity\AffiliateCredit');
        $affiliatePayoutRepo = \Env::get('em')->getRepository('\Cx\Core_Modules\MultiSite\Model\Entity\AffiliatePayout');

        //get the total Referrer count
        $totalRefererCount = BackendController::getReferralCountByAffiliateId($affiliateId);
        if (empty($totalRefererCount)) {
            ComponentController::showOrHideBlock($template, 'affiliateRefererCountEmpty', true);
        }

        //show the solo, non-profit and business subscription counts
        $subscriptionListByProduct = ComponentController::getReferralsSubscriptionIdsBasedOnProduct($affiliateId);
        foreach ($subscriptionListByProduct as $productName => $subscriptionIds) {
            if (empty($subscriptionIds)) {
                continue;
            }
            $template->setVariable(array(
                'MULTISITE_SUBSCRIPTIONS_PRODUCT_NAME'           => $productName,
                'MULTISITE_SUBSCRIPTIONS_PRODUCT_PENDING_COUNT'  => $affiliateCreditRepo->getSubscriptionCountByCriteria(array('in' => array(array('s.id', $subscriptionIds)), 'ac.credited' => 0, 'payout' => 'IS NULL', 'user' => $objUser)),
                'MULTISITE_SUBSCRIPTIONS_PRODUCT_CREDITED_COUNT' => $affiliateCreditRepo->getSubscriptionCountByCriteria(array('in' => array(array('s.id', $subscriptionIds)), 'ac.credited' => 1, 'payout' => 'IS NULL', 'user' => $objUser)),
                'MULTISITE_SUBSCRIPTIONS_PRODUCT_PAYOUT_COUNT'   => $affiliateCreditRepo->getSubscriptionCountByCriteria(array('in' => array(array('s.id', $subscriptionIds)), 'payout' => 'IS NOT NULL', 'user' => $objUser)),
            ));
            $template->parse('showSubscriptionsCountByProduct');            
        }
        if (empty($subscriptionListByProduct)) {
            ComponentController::showOrHideBlock($template, 'showNoReferralsMsg', true);
            ComponentController::showOrHideBlock($template, 'showReferralsSubscriptionCount', false);            
        }

        //get the sum of affiliate credit amount
        $affiliateTotalCreditAmount = $affiliateCreditRepo->getTotalCreditsAmountByUser($objUser);
        //calculate the Total
        $affiliatePayoutSum   = $affiliatePayoutRepo->getTotalAmountByUser($objUser);
        $totalAffiliatePayout = $affiliatePayoutSum + $affiliateTotalCreditAmount;

        // as of for now, all affiliate commission is being paid out in CHF (=> currently default currency)
        $currencyId   = \Cx\Modules\Crm\Controller\CrmLibrary::getDefaultCurrencyId();
        $currencyObj  = \Env::get('em')->getRepository('\Cx\Modules\Crm\Model\Entity\Currency')->findOneById($currencyId);
        $currencyCode = $currencyObj ? $currencyObj->getName() : '';

        $isPayoutVaild = (floatval($affiliateTotalCreditAmount) >= \Cx\Core\Setting\Controller\Setting::getValue('affiliatePayoutLimit','MultiSite'));
        ComponentController::showOrHideBlock($template, 'showPayoutButton', $isPayoutVaild);

        $template->setVariable(array(
            'MULTISITE_AFFILIATE_PROFILE_ATTR_ID' => $affiliateId,
            'MULTISITE_AFFILIATE_CREDIT_AMT_TOTAL'=> number_format($affiliateTotalCreditAmount, 2) . ' ' . $currencyCode,
            'MULTISITE_AFFILIATE_TOTAL_AMT'       => number_format($totalAffiliatePayout, 2) . ' ' . $currencyCode,
            'MULTISITE_AFFILIATE_REFERRALS_COUNT' => $totalRefererCount,
        ));            
    }
    
    /**
     * Parse the paypal details for the frontend section affiliate
     * 
     * @param \Cx\Core\Html\Sigma $template Template object
     * @param \User               $objUser  User object
     */
    private function parseSectionAffiliatePaypalDetails(\Cx\Core\Html\Sigma $template, \User $objUser)
    {
        //get the payPalProfileAttributeId
        $paypalEmailAddressProfileAttribute = \Cx\Core\Setting\Controller\Setting::getValue('payPalProfileAttributeId','MultiSite');
        $paypalEmailAddress  = $objUser->getProfileAttribute((int)$paypalEmailAddressProfileAttribute);
        $isPaypalEmailExists = !empty($paypalEmailAddress);        
        
        ComponentController::showOrHideBlock($template, 'paypalEmailAddressValue', $isPaypalEmailExists);
        ComponentController::showOrHideBlock($template, 'payrexxAccountEditModal', $isPaypalEmailExists);
        ComponentController::showOrHideBlock($template, 'paypalEmailAddressInput', !$isPaypalEmailExists);
        
        if (!$isPaypalEmailExists) {
            return;
        }
        
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
