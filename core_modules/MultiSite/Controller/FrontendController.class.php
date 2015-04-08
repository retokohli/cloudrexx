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
                $crmContactId = $objUser->getCrmUserId();
                if (empty($crmContactId)) {
                    return ' '; // Do not show AffiliateSetup detail
                }
                \Cx\Core\Setting\Controller\Setting::init('MultiSite', '','FileSystem');
                //get the affiliateIdProfileAttributeId
                $affiliateIdProfileAttributeId = \Cx\Core\Setting\Controller\Setting::getValue('affiliateIdProfileAttributeId','MultiSite');
                $affiliateId = $objUser->getProfileAttribute((int)$affiliateIdProfileAttributeId);
                //get the payPalProfileAttributeId
                $paypalEmailAddressProfileAttribute = \Cx\Core\Setting\Controller\Setting::getValue('payPalProfileAttributeId','MultiSite');
                $paypalEmailAddress = $objUser->getProfileAttribute((int)$paypalEmailAddressProfileAttribute);
                if (!empty($affiliateId)) {
                    list($soloCnt, $nonProfitCnt, $businessCnt) = ComponentController::getSubscriptionsCountBasedOnProductForReferralsSubscribe($affiliateId);
                    $template->setVariable(array(
                        'MULTISITE_AFFILIATE_REFERRALS_COUNT'                       => BackendController::getReferralCountByAffiliateId($affiliateId),
                        'MULTISITE_SUBSCRIPTIONS_COUNT_BASED_ON_PRODUCT_SOLO'       => $soloCnt,
                        'MULTISITE_SUBSCRIPTIONS_COUNT_BASED_ON_PRODUCT_NON_PROFIT' => $nonProfitCnt,
                        'MULTISITE_SUBSCRIPTIONS_COUNT_BASED_ON_PRODUCT_BUSINESS'   => $businessCnt,
                    ));
                }
                //parse block for Affiliate Id
                !empty($affiliateId) ? $template->touchBlock('showAffiliateId') : $template->hideBlock('showAffiliateId');
                empty($affiliateId) ? $template->touchBlock('showAffiliateIdForm') : $template->hideBlock('showAffiliateIdForm');
                //parse block for paypal email address
                !empty($paypalEmailAddress) ? $template->touchBlock('showPaypalEmailAddress') : $template->hideBlock('showPaypalEmailAddress');
                empty($paypalEmailAddress) ? $template->touchBlock('showPaypalEmailAddressForm') : $template->hideBlock('showPaypalEmailAddressForm');
                //parse the form 
                !empty($affiliateId) && !empty($paypalEmailAddress) ? $template->hideBlock('showAffiliateForm') : $template->touchBlock('showAffiliateForm');
                
                $template->setVariable(array(
                    'MULTISITE_AFFILIATE_ID_NOT_SET_NOTE' => empty($affiliateId) ? $_ARRAYLANG['TXT_CORE_MODULE_MULTISITE_AFFILIATE_ID_NOT_SET_NOTE'] : '',
                    'MULTISITE_AFFILIATE_PROFILE_ATTR_ID' => !empty($affiliateId) ? $affiliateId : '',
                    'MULTISITE_PAYPAL_EMAIL_ADDRESS'      => $paypalEmailAddress
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
                                'MULTISITE_USER_PROFILE_ATTRIBUTE_DESC' =>  htmlentities($objUser->objAttribute->getName(), ENT_QUOTES, CONTREXX_CHARSET),
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
            default:
        }
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
