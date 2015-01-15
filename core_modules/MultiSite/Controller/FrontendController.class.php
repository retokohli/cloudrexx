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
            default:
        }
    }
}
