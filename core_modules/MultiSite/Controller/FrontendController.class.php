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
                //get the minimum length of websiteName
                $objJs = \ContrexxJavascript::getInstance();
                $objJs->setVariable('websiteNameMinLength', \Cx\Core\Setting\Controller\Setting::getValue('websiteNameMinLength','MultiSite'), 'affiliateSetup');
                $affiliateIdProfileAttributeId = \Cx\Core\Setting\Controller\Setting::getValue('affiliateIdProfileAttributeId','MultiSite');
                $affiliateId = $objUser->getProfileAttribute($affiliateIdProfileAttributeId);
                if (!empty($affiliateId)) {
                    $template->setVariable(array(
                        'TXT_MULTISITE_AFFILIATE_PROFILE_ATTR_ID' => $_ARRAYLANG['TXT_MULTISITE_AFFILIATE_PROFILE_ATTR_ID'],
                        'MULTISITE_AFFILIATE_PROFILE_ATTR_ID'     => $affiliateId
                    ));
                    $template->touchBlock('showAffiliateId');
                } else {
                    $template->setVariable(array(
                        'TXT_MULTISITE_CHOOSE_AFFILIATE_PROFILE_ATTR_ID' => $_ARRAYLANG['TXT_MULTISITE_CHOOSE_AFFILIATE_PROFILE_ATTR_ID']
                    ));
                    $template->touchBlock('showAffiliateIdForm');
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
