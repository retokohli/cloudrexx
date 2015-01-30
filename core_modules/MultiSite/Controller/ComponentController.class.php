<?php

/**
 * Class ComponentController
 *
 * 
 * @copyright   CONTREXX CMS - Comvation AG Thun
 * @author      Ueli Kramer <ueli.kramer@comvation.com>
 * @author      Sudhir Parmar <sudhirparmar@cdnsol.com>
 * @package     contrexx
 * @subpackage  coremodule_multisite
 * @version     1.0.0
 */

namespace Cx\Core_Modules\MultiSite\Controller;

/**
 * Class MultisiteException
 */
class MultiSiteException extends \Exception {}

/**
 * Class ComponentController
 *
 * The main Multisite component
 *
 * @copyright   CONTREXX CMS - Comvation AG Thun
 * @author      Ueli Kramer <ueli.kramer@comvation.com>
 * @author      Sudhir Parmar <sudhirparmar@cdnsol.com>
 * @package     contrexx
 * @subpackage  coremodule_multisite
 * @version     1.0.0
 */
class ComponentController extends \Cx\Core\Core\Model\Entity\SystemComponentController {
   // const MAX_WEBSITE_NAME_LENGTH = 18; 
    const MODE_NONE = 'none';
    const MODE_MANAGER = 'manager';
    const MODE_SERVICE = 'service';
    const MODE_HYBRID = 'hybrid';
    const MODE_WEBSITE = 'website';
    
    protected $messages = '';
    protected $reminders = array(3, 14);
    protected $db;
    /*
     * Constructor
     */
    public function __construct(\Cx\Core\Core\Model\Entity\SystemComponent $systemComponent, \Cx\Core\Core\Controller\Cx $cx) {
        parent::__construct($systemComponent, $cx);
        //multisite configuration setting
        self::errorHandler();

        // add marketing website as valid redirection after logout
        \FWUser::$allowedHosts[] = 'http://'.\Cx\Core\Setting\Controller\Setting::getValue('marketingWebsiteDomain');
        \FWUser::$allowedHosts[] = 'https://'.\Cx\Core\Setting\Controller\Setting::getValue('marketingWebsiteDomain');
    }
    
    public function getControllersAccessableByJson() { 
        return array('JsonMultiSite');
    }

    public function getCommandsForCommandMode() {
        return array('MultiSite');
    }

    public function getCommandDescription($command, $short = false) {
        switch ($command) {
            case 'MultiSite':
                return 'Load MultiSite GUI forms (sign-up / Customer Panel / etc.)';
        }
    }

    public function executeCommand($command, $arguments) {
        
        // Event Listener must be registered before preContentLoad event
        $this->registerEventListener();

        $subcommand = null;
        if (!empty($arguments[0])) {
            $subcommand = $arguments[0];
        }
        $pageCmd = $subcommand;
        if (!empty($arguments[1])) {
            $pageCmd .= '_'.$arguments[1];
        }
        if (!empty($arguments[2])) {
            $pageCmd .= '_'.$arguments[2];
        }
        
        \Cx\Core\Setting\Controller\Setting::init('MultiSite', '','FileSystem');
        // allow access only if mode is MODE_MANAGER or MODE_HYBRID
        if (!in_array(\Cx\Core\Setting\Controller\Setting::getValue('mode'), array(self::MODE_MANAGER, self::MODE_HYBRID))) {
            return;
        }

        // define frontend language
// TODO: implement multilanguage support for API command
        if (!defined('FRONTEND_LANG_ID')) {
            define('FRONTEND_LANG_ID', 1);
        }

        // load language data of MultiSite component
        JsonMultiSite::loadLanguageData();
        
        // load application template
        $page = new \Cx\Core\ContentManager\Model\Entity\Page();
        $page->setVirtual(true);
        $page->setType(\Cx\Core\ContentManager\Model\Entity\Page::TYPE_APPLICATION);
        $page->setCmd($pageCmd);
        $page->setModule('MultiSite');
        $pageContent = \Cx\Core\Core\Controller\Cx::getContentTemplateOfPage($page);
        \LinkGenerator::parseTemplate($pageContent, true, new \Cx\Core\Net\Model\Entity\Domain(\Cx\Core\Setting\Controller\Setting::getValue('customerPanelDomain')));
        $objTemplate = new \Cx\Core\Html\Sigma();                
        $objTemplate->setTemplate($pageContent);
        $objTemplate->setErrorHandling(PEAR_ERROR_DIE);

        switch ($command) {
            case 'MultiSite':
                switch ($subcommand) {
                    case 'Signup':
                        echo $this->executeCommandSignup($objTemplate, $arguments);
                        break;

                    case 'Login':
                        echo $this->executeCommandLogin($objTemplate);                        
                        break;

                    case 'User':
                        echo $this->executeCommandUser($objTemplate, $arguments);
                        break;

                    case 'Subscription':
                        echo $this->executeCommandSubscription($objTemplate, $arguments);                        
                        break;
                        
                    case 'SubscriptionSelection':
                        echo $this->executeCommandSubscriptionSelection($objTemplate, $arguments);
                        break;
                        
                    case 'SubscriptionDetail':
                        echo $this->executeCommandSubscriptionDetail($objTemplate, $arguments);
                        break;

                    case 'SubscriptionAddWebsite':
                        echo $this->executeCommandSubscriptionAddWebsite($objTemplate, $arguments);                        
                        break;

                    case 'Website':
                        echo $this->executeCommandWebsite($objTemplate, $arguments);
                        break;
                    
                    case 'Domain':
                        echo $this->executeCommandDomain($objTemplate, $arguments);
                        break;
                    
                    case 'Admin':
                        echo $this->executeCommandAdmin($objTemplate, $arguments);
                        break;
                    
                    case 'Payrexx':
                        $this->executeCommandPayrexx();
                        break;
                    
                    case 'Backup':
                        echo $this->executeCommandBackup($arguments);
                        break;
                    
                    case 'Cron':
                        $this->executeCommandCron();
                        break;
                    default:
                        break;
                }
                break;
            default:
                break;
        }
    }
    
    /**
     * Api Signup command 
     * 
     * @param object $objTemplate Template object \Cx\Core\Html\Sigma
     * @param array  $arguments   Array parameters
     * 
     * @return string 
     */
    public function executeCommandSignup($objTemplate, $arguments)
    {
        global $_ARRAYLANG;
        
        $websiteName = isset($arguments['multisite_address']) ? contrexx_input2xhtml($arguments['multisite_address']) : '';
        $domainRepository = new \Cx\Core\Net\Model\Repository\DomainRepository();
        $mainDomain = $domainRepository->getMainDomain()->getName();
        $signUpUrl = \Cx\Core\Routing\Url::fromMagic(ASCMS_PROTOCOL . '://' . $mainDomain . \Env::get('cx')->getBackendFolderName() . '/index.php?cmd=JsonData&object=MultiSite&act=signup');
        $emailUrl = \Cx\Core\Routing\Url::fromMagic(ASCMS_PROTOCOL . '://' . $mainDomain . \Env::get('cx')->getBackendFolderName() . '/index.php?cmd=JsonData&object=MultiSite&act=email');
        $addressUrl = \Cx\Core\Routing\Url::fromMagic(ASCMS_PROTOCOL . '://' . $mainDomain . \Env::get('cx')->getBackendFolderName() . '/index.php?cmd=JsonData&object=MultiSite&act=address');
        $paymentUrl = \Cx\Core\Routing\Url::fromMagic(ASCMS_PROTOCOL . '://' . $mainDomain . \Env::get('cx')->getBackendFolderName() . '/index.php?cmd=JsonData&object=MultiSite&act=getPayrexxUrl');
        $termsUrlValue = preg_replace('/\[\[([A-Z0-9_]*?)\]\]/', '{\\1}' ,\Cx\Core\Setting\Controller\Setting::getValue('termsUrl'));
        \LinkGenerator::parseTemplate($termsUrlValue);
        $termsUrl = '<a href="'.$termsUrlValue.'" target="_blank">'.$_ARRAYLANG['TXT_MULTISITE_ACCEPT_TERMS_URL_NAME'].'</a>';
        $websiteNameMinLength=\Cx\Core\Setting\Controller\Setting::getValue('websiteNameMinLength');
        $websiteNameMaxLength=\Cx\Core\Setting\Controller\Setting::getValue('websiteNameMaxLength');
        if (\Cx\Core\Setting\Controller\Setting::getValue('autoLogin')) {
            $buildWebsiteMsg = $_ARRAYLANG['TXT_MULTISITE_BUILD_WEBSITE_MSG_AUTO_LOGIN'];
        } else {
            $buildWebsiteMsg = $_ARRAYLANG['TXT_MULTISITE_BUILD_WEBSITE_MSG'];
        }
        $objTemplate->setVariable(array(
            'TITLE'                         => $_ARRAYLANG['TXT_MULTISITE_TITLE'],
            'TXT_MULTISITE_CLOSE'           => $_ARRAYLANG['TXT_MULTISITE_CLOSE'],
            'TXT_MULTISITE_EMAIL_ADDRESS'   => $_ARRAYLANG['TXT_MULTISITE_EMAIL_ADDRESS'],
            'TXT_MULTISITE_SITE_ADDRESS'         => $_ARRAYLANG['TXT_MULTISITE_SITE_ADDRESS'],
            'TXT_MULTISITE_SITE_ADDRESS_SCHEME'  => sprintf($_ARRAYLANG['TXT_MULTISITE_SITE_ADDRESS_SCHEME'], $websiteNameMinLength, $websiteNameMaxLength),
            'TXT_MULTISITE_CREATE_WEBSITE'  => $_ARRAYLANG['TXT_MULTISITE_SUBMIT_BUTTON'],
            'TXT_MULTISITE_ORDER_NOW'       => $_ARRAYLANG['TXT_MULTISITE_ORDER_BUTTON'],
            'MULTISITE_PATH'                => ASCMS_PROTOCOL . '://' . $mainDomain . \Env::get('cx')->getWebsiteOffsetPath(),
            'MULTISITE_DOMAIN'              => \Cx\Core\Setting\Controller\Setting::getValue('multiSiteDomain'),
            'POST_URL'                      => '',
            'MULTISITE_ADDRESS_MIN_LENGTH'  => $websiteNameMinLength,
            'MULTISITE_ADDRESS_MAX_LENGTH'  => $websiteNameMaxLength,
            'MULTISITE_ADDRESS'             => $websiteName,
            'MULTISITE_SIGNUP_URL'          => $signUpUrl->toString(),
            'MULTISITE_EMAIL_URL'           => $emailUrl->toString(),
            'MULTISITE_ADDRESS_URL'         => $addressUrl->toString(),
            'MULTISITE_PAYMENT_URL'         => $paymentUrl->toString(),
            'TXT_MULTISITE_ACCEPT_TERMS'    => sprintf($_ARRAYLANG['TXT_MULTISITE_ACCEPT_TERMS'], $termsUrl),
            'TXT_MULTISITE_BUILD_WEBSITE_TITLE' => $_ARRAYLANG['TXT_MULTISITE_BUILD_WEBSITE_TITLE'],
            'TXT_MULTISITE_BUILD_WEBSITE_MSG' => $buildWebsiteMsg,
            'TXT_MULTISITE_REDIRECT_MSG'    => $_ARRAYLANG['TXT_MULTISITE_REDIRECT_MSG'],
            'TXT_MULTISITE_BUILD_SUCCESSFUL_TITLE' => $_ARRAYLANG['TXT_MULTISITE_BUILD_SUCCESSFUL_TITLE'],
            'TXT_MULTISITE_BUILD_ERROR_TITLE' => $_ARRAYLANG['TXT_MULTISITE_BUILD_ERROR_TITLE'],
            'TXT_MULTISITE_BUILD_ERROR_MSG' => $_ARRAYLANG['TXT_MULTISITE_BUILD_ERROR_MSG'],
            'TXT_CORE_MODULE_MULTISITE_INVALID_EMAIL' => $_ARRAYLANG['TXT_CORE_MODULE_MULTISITE_INVALID_EMAIL'],
            'TXT_MULTISITE_ACCEPT_TERMS_ERROR' => $_ARRAYLANG['TXT_MULTISITE_ACCEPT_TERMS_ERROR'],
    // TODO: add configuration option for contact details and replace the hard-coded e-mail address on the next line
            'TXT_MULTISITE_EMAIL_INFO'      => sprintf($_ARRAYLANG['TXT_MULTISITE_EMAIL_INFO'], 'info@cloudrexx.com'),
            'TXT_CORE_MODULE_MULTISITE_LOADING_TEXT' => $_ARRAYLANG['TXT_CORE_MODULE_MULTISITE_LOADING_TEXT'],
        ));
        $productId = !empty($arguments['product-id']) ? $arguments['product-id'] : \Cx\Core\Setting\Controller\Setting::getValue('defaultPimProduct');
        if (!empty($productId)) {
            $productRepository = \Env::get('em')->getRepository('Cx\Modules\Pim\Model\Entity\Product');
            $product = $productRepository->findOneBy(array('id' => $productId));
            if ($product) {
                self::parseProductForAddWebsite($objTemplate, $product);
            }
        }
        return $objTemplate->get();
    }
    
    /**
     * Api Login command 
     * 
     * @param object $objTemplate Template object \Cx\Core\Html\Sigma
     * @param array  $arguments   Array parameters
     * 
     * @return string 
     */
    public function executeCommandLogin($objTemplate)
    {
        global $objInit, $_ARRAYLANG, $_CORELANG;
        
        $langData = $objInit->loadLanguageData('Login');
        $_ARRAYLANG = array_merge($_ARRAYLANG, $langData);
        $langData = $objInit->loadLanguageData('core');
        $_CORELANG = $_ARRAYLANG = array_merge($_ARRAYLANG, $langData);
        $objTemplate->setVariable(array(
            'TITLE'                 => $_ARRAYLANG['TXT_LOGIN_LOGIN'],
            'TXT_LOGIN_PASSWORD'    => $_ARRAYLANG['TXT_LOGIN_PASSWORD'],
            'TXT_LOGIN_USERNAME'    => $_ARRAYLANG['TXT_LOGIN_USERNAME'],
            'TXT_LOGIN_REMEMBER_ME' => $_ARRAYLANG['TXT_CORE_REMEMBER_ME'],
            'TXT_LOGIN_LOGIN'       => $_ARRAYLANG['TXT_LOGIN_LOGIN'],
            'TXT_LOGIN_PASSWORD_LOST'=> $_ARRAYLANG['TXT_LOGIN_PASSWORD_LOST'],
        ));
        
        return $objTemplate->get();
    }
    
    /**
     * Api User command 
     * 
     * @param object $objTemplate Template object \Cx\Core\Html\Sigma
     * @param array  $arguments   Array parameters
     * 
     * @return string 
     */
    public function executeCommandUser($objTemplate, $arguments) 
    {
        // profile attribute labels are stored in core-lang
        global $objInit, $_CORELANG, $_ARRAYLANG;
        $langData = $objInit->loadLanguageData('core');
        $_CORELANG = $_ARRAYLANG = array_merge($_ARRAYLANG, $langData);

        if (!self::isUserLoggedIn()) {
            return $_ARRAYLANG['TXT_MULTISITE_WEBSITE_LOGIN_NOACCESS'];            
        }
        $objUser = \FWUser::getFWUserObject()->objUser;
        
        $blockName = 'multisite_user';
        $placeholderPrefix = strtoupper($blockName).'_';
        $objAccessLib = new \Cx\Core_Modules\Access\Controller\AccessLib($objTemplate);
        $objAccessLib->setModulePrefix($placeholderPrefix);
        $objAccessLib->setAttributeNamePrefix($blockName.'_profile_attribute');
        $objAccessLib->setAccountAttributeNamePrefix($blockName.'_account_');

        $objUser->objAttribute->first();
        while (!$objUser->objAttribute->EOF) {
            $objAttribute = $objUser->objAttribute->getById($objUser->objAttribute->getId());
            $objAccessLib->parseAttribute($objUser, $objAttribute->getId(), 0, (isset($arguments[2]) && $arguments[2] == 'Edit' ? true : false), false, false, false, false);
            $objUser->objAttribute->next();
        }
        $objAccessLib->parseAccountAttributes($objUser);
        $objTemplate->setVariable(array(
            'MULTISITE_USER_PROFILE_SUBMIT_URL' => \Env::get('cx')->getWebsiteBackendPath() . '/index.php?cmd=JsonData&object=MultiSite&act=updateOwnUser',
        ));
        
        return $objTemplate->get();
    }
    
    /**
     * Api Subscription command 
     * 
     * @param object $objTemplate Template object \Cx\Core\Html\Sigma
     * @param array  $arguments   Array parameters
     * 
     * @return string 
     */
    public function executeCommandSubscription($objTemplate, $arguments) {
        global $_ARRAYLANG;

        $objTemplate->setGlobalVariable($_ARRAYLANG);
        
        if (!self::isUserLoggedIn()) {
            return $_ARRAYLANG['TXT_MULTISITE_WEBSITE_LOGIN_NOACCESS'];            
        }

        $crmContactId = \FWUser::getFWUserObject()->objUser->getCrmUserId();
        if (empty($crmContactId)) {
            return ' '; // Do not show sbuscriptions
        }

        //Get the input values
        $status         = isset($arguments['status']) ? contrexx_input2raw($arguments['status']) : '';
        $website_status = isset($arguments['website_status']) ? contrexx_input2raw($arguments['website_status']) : '';
        $excludeProduct = isset($arguments['exclude_product']) ? array_map('contrexx_input2raw', $arguments['exclude_product']) : '';
        $includeProduct = isset($arguments['include_product']) ? array_map('contrexx_input2raw', $arguments['include_product']) : '';
        //Get the orders based on CRM contact id and get params
        $orderRepo = \Env::get('em')->getRepository('Cx\Modules\Order\Model\Entity\Order');
        $orders    = $orderRepo->getOrdersByCriteria($crmContactId, $status, $excludeProduct, $includeProduct);

        //parse the Site Details
        if (!empty($orders)) {            
            foreach ($orders as $order) {
                foreach ($order->getSubscriptions() as $subscription) {
                    if ($subscription->getState() == \Cx\Modules\Order\Model\Entity\Subscription::STATE_TERMINATED) {
                        continue;
                    }
                    
                    $product = $subscription->getProduct();
                    if (!$product) {
                        continue;
                    }
                    $objTemplate->setGlobalVariable(array(
                        'MULTISITE_SUBSCRIPTION_ID'          => contrexx_raw2xhtml($subscription->getId()),
                        'MULTISITE_SUBSCRIPTION_DESCRIPTION' => contrexx_raw2xhtml($subscription->getDescription()),
                        'MULTISITE_WEBSITE_PLAN'             => contrexx_raw2xhtml($product->getName()),
                        'MULTISITE_WEBSITE_INVOICE_DATE'     => $subscription->getRenewalDate() ? $subscription->getRenewalDate()->format('d.m.Y') : '',
                        'MULTISITE_WEBSITE_EXPIRE_DATE'      => $subscription->getExpirationDate() ? $subscription->getExpirationDate()->format('d.m.Y') : '',    
                    ));

                    if ($status == 'valid' && $objTemplate->blockExists('showUpgradeButton')) {
                        $product->isUpgradable() ? $objTemplate->touchBlock('showUpgradeButton') : $objTemplate->hideBlock('showUpgradeButton');
                    }

                    if ($status != 'expired') {
                        $websiteCollection = $subscription->getProductEntity();
                        if ($websiteCollection) {
                            if ($websiteCollection instanceof \Cx\Core_Modules\MultiSite\Model\Entity\WebsiteCollection) {
                                foreach ($websiteCollection->getWebsites() as $website) {
                                    if (!($website instanceof \Cx\Core_Modules\MultiSite\Model\Entity\Website)) {
                                        continue;
                                    }
                                    self::parseWebsiteDetails($objTemplate, $website, $website_status);
                                    $objTemplate->parse('showWebsites');
                                }
                                self::showOrHideBlock($objTemplate, 'showAddWebsiteButton', ($websiteCollection->getQuota() > count($websiteCollection->getWebsites())));
                            } elseif ($websiteCollection instanceof \Cx\Core_Modules\MultiSite\Model\Entity\Website) {
                                self::parseWebsiteDetails($objTemplate, $websiteCollection, $website_status);
                                $objTemplate->parse('showWebsites');
                            }
                        }
                    } else {
                        $objTemplate->touchBlock('showWebsites');
                    }

                    $objTemplate->parse('showSiteDetails');
                }
            }
        } else {
            $objTemplate->hideBlock('showSiteTable');
        }
        return $objTemplate->get();
    }
    
    /**
     * Api SubscriptionSelection command 
     * 
     * @param object $objTemplate Template object \Cx\Core\Html\Sigma
     * @param array  $arguments   Array parameters
     * 
     * @return string 
     */
    public function executeCommandSubscriptionSelection($objTemplate, $arguments) 
    {
        global $_ARRAYLANG;

        if (!self::isUserLoggedIn()) {
            return $_ARRAYLANG['TXT_MULTISITE_WEBSITE_LOGIN_NOACCESS'];            
        }
        
        $websiteId = isset($arguments['id']) ? $arguments['id'] : 0;
        $subscriptionId = isset($arguments['subscriptionId']) ? $arguments['subscriptionId'] : 0;
        
        $objTemplate->setGlobalVariable($_ARRAYLANG);
        $objUser = \FWUser::getFWUserObject()->objUser;
        $crmContactId = $objUser->getCrmUserId();
        $userId = $objUser->getId();
        if (\FWValidator::isEmpty($crmContactId)) {
            // create a new CRM Contact and link it to the User account
            \Cx\Modules\Crm\Controller\CrmLibrary::addCrmContactFromAccessUser($objUser);
        }
        
        $subscription = null;
        $website      = null;
        $termsUrlValue = preg_replace('/\[\[([A-Z0-9_]*?)\]\]/', '{\\1}' ,\Cx\Core\Setting\Controller\Setting::getValue('termsUrl'));
        \LinkGenerator::parseTemplate($termsUrlValue);
        $termsUrl = '<a href="'.$termsUrlValue.'" target="_blank">'.$_ARRAYLANG['TXT_MULTISITE_ACCEPT_TERMS_URL_NAME'].'</a>';
        
        if (!\FWValidator::isEmpty($subscriptionId)) {
            $subscription = \Env::get('em')->getRepository('Cx\Modules\Order\Model\Entity\Subscription')->findOneBy(array('id' => $subscriptionId));
            
            if ($subscription) {
                $order = $subscription->getOrder();
                if (!$order) {
                    return $_ARRAYLANG['TXT_MULTISITE_WEBSITE_ORDER_NOT_EXISTS'];
                }
                
                //Verify the owner of the associated Order of the Subscription is actually owned by the currently sign-in user
                if ($crmContactId != $order->getContactId()) {
                    return $_ARRAYLANG['TXT_MULTISITE_WEBSITE_NOT_MULTISITE_USER'];
                }
                
                if (!\FWValidator::isEmpty($websiteId)) {
                    $websiteServiceRepo = \Env::get('em')->getRepository('Cx\Core_Modules\MultiSite\Model\Entity\Website');
                    $website = $websiteServiceRepo->findOneById($websiteId);
                    if (!$website) {
                        return $_ARRAYLANG['TXT_MULTISITE_UNKOWN_WEBSITE'];
                    }

                    if ($website->getOwnerId() != $userId) {
                        return $_ARRAYLANG['TXT_MULTISITE_WEBSITE_NOT_MULTISITE_USER'];
                    }
                }
            } else {
                return $_ARRAYLANG['TXT_MULTISITE_WEBSITE_SUBSCRIPTION_NOT_EXISTS'];
            }
        }
        $currency = self::getUserCurrency($crmContactId);
        $websiteName = $website instanceof \Cx\Core_Modules\MultiSite\Model\Entity\Website ? $website->getName() : '';
        $products = array();
        $renewalPlan = 'monthly';
        if ($subscription) {
            if ($subscription->getProductEntity() instanceof \Cx\Core_Modules\MultiSite\Model\Entity\WebsiteCollection) {
                $renewalPlan = self::getSubscriptionRenewalPlan($subscription->getRenewalUnit(), $subscription->getRenewalQuantifier());
            }
            $product = $subscription->getProduct();
            if (!$product) {
                return $_ARRAYLANG['TXT_MULTISITE_WEBSITE_PRODUCT_NOT_EXISTS'];
            }

            $productCollection = $product->getUpgrades();
            // cast $productCollection into an array -> this is required as uasort() only works with arrays
            foreach ($productCollection as $product) {
                $products[] = $product; 
            }
        } else {
            $products = \Env::get('em')->getRepository('Cx\Modules\Pim\Model\Entity\Product')->findAll();
        }

        uasort($products, function($a, $b) use ($currency) {
// customizing: list subscription Non-Profit always at the end
// TODO: implement some sort of sorting ability to the model collection
            # list Non-Profit at last position
            if ($a->getName() == 'Non-Profit') return 1;
            if ($b->getName() == 'Non-Profit') return -1;

            # list Trial at first position
            if ($a->getName() == 'Trial') return -1;
            if ($b->getName() == 'Trial') return 1;
            
            if ($a->getPaymentAmount(\Cx\Modules\Pim\Model\Entity\Product::UNIT_MONTH, 1, $currency) == $b->getPaymentAmount(\Cx\Modules\Pim\Model\Entity\Product::UNIT_MONTH, 1, $currency)) {
                return 0;
            }
            return ($a->getPaymentAmount(\Cx\Modules\Pim\Model\Entity\Product::UNIT_MONTH, 1, $currency) < $b->getPaymentAmount(\Cx\Modules\Pim\Model\Entity\Product::UNIT_MONTH, 1, $currency)) ? -1 : 1;
        });

        if (\FWValidator::isEmpty($products)) {
            return $_ARRAYLANG['TXT_MULTISITE_WEBSITE_PRODUCTS_NOT_FOUND'];
        }
        
        foreach ($products as $product) {
// customizing: do not list Enterprise product 
// TODO: implement some sort of selective product selection in the multisite configuration
            if ($product->getName() == 'Enterprise') {
                continue;
            }
            $productName = contrexx_raw2xhtml($product->getName());
            $priceMonthly = $product->getPaymentAmount(\Cx\Modules\Pim\Model\Entity\Product::UNIT_MONTH, 1, $currency);
            $priceAnnually = number_format($product->getPaymentAmount(\Cx\Modules\Pim\Model\Entity\Product::UNIT_YEAR, 1, $currency), 2, '.', "'");
            $priceBiannually = number_format($product->getPaymentAmount(\Cx\Modules\Pim\Model\Entity\Product::UNIT_YEAR, 2, $currency), 2, '.', "'");
            $objTemplate->setVariable(array(
                'MULTISITE_WEBSITE_PRODUCT_NAME' => $productName,
                'MULTISITE_WEBSITE_PRODUCT_ATTRIBUTE_ID' => lcfirst($productName),
                'MULTISITE_WEBSITE_PRODUCT_PRICE_MONTHLY' => $priceMonthly,
                'MULTISITE_WEBSITE_PRODUCT_PRICE_ANNUALLY' => (substr($priceAnnually, -3) == '.00' ? substr($priceAnnually, 0 , -3) : $priceAnnually),
                'MULTISITE_WEBSITE_PRODUCT_PRICE_BIANNUALLY' => (substr($priceBiannually, -3) == '.00' ? substr($priceBiannually, 0 , -3) : $priceBiannually),
                'MULTISITE_WEBSITE_PRODUCT_NOTE_PRICE' => $product->getNotePrice(),
                'MULTISITE_WEBSITE_PRODUCT_ID' => $product->getId(),
                'MULTISITE_PRODUCT_TYPE' => $product->getEntityClass() == 'Cx\Core_Modules\MultiSite\Model\Entity\Website' ? 'website' : 'websiteCollection'
            ));
            $objTemplate->parse('showProduct');
        }
        $objTemplate->setVariable(array(
            'MULTISITE_SUBSCRIPTION_ID'             => $subscriptionId,
            'MULTISITE_WEBSITE_NAME'                => $websiteName,    
            'MULTISITE_SUBSCRIPTION_RENEWAL_PLAN'   => $renewalPlan,    
            'MULTISITE_ACCEPT_TERMS_URL'            => sprintf($_ARRAYLANG['TXT_MULTISITE_ACCEPT_TERMS'], $termsUrl),
            'MULTISITE_IS_USER_HAS_PAYREXX_ACCOUNT' => !\FWValidator::isEmpty($objUser->getProfileAttribute(\Cx\Core\Setting\Controller\Setting::getValue('externalPaymentCustomerIdProfileAttributeId'))) ? 'true' : 'false',            
        ));
        return $objTemplate->get();
    }
    
    /**
     * Get the subscription renewal plan
     * 
     * @param string  $unit
     * @param integer $quantifier
     * 
     * @return string
     */
    public static function getSubscriptionRenewalPlan($unit, $quantifier) {
        
        $renewalPlan = 'monthly';
        
        switch ($unit) {
            case \Cx\Modules\Pim\Model\Entity\Product::UNIT_YEAR:
                $renewalPlan = ($quantifier == 1) ? 'annually' : 'biannually';
                break;
        }

        return $renewalPlan;
    }
    
    /**
     * Api SubscriptionDetail command 
     * 
     * @param object $objTemplate Template object \Cx\Core\Html\Sigma
     * @param array  $arguments   Array parameters
     * 
     * @return string 
     */
    public function executeCommandSubscriptionDetail($objTemplate, $arguments) 
    {
        global $_ARRAYLANG;

        $objTemplate->setGlobalVariable($_ARRAYLANG);
        
        $subscriptionId = isset($arguments['id']) ? contrexx_input2raw($arguments['id']) : 0;
        $action         = isset($arguments['action']) ? contrexx_input2raw($arguments['action']) : '';

        if (!self::isUserLoggedIn()) {
            return $_ARRAYLANG['TXT_MULTISITE_WEBSITE_LOGIN_NOACCESS'];            
        }

        $crmContactId = \FWUser::getFWUserObject()->objUser->getCrmUserId();
        if (empty($crmContactId)) {
            return ' '; // Do not show subscription detail
        }
        
        if (empty($subscriptionId)) {
            return $_ARRAYLANG['TXT_MULTISITE_WEBSITE_SUBSCRIPTIONID_EMPTY'];
        }

        $subscriptionRepo = \Env::get('em')->getRepository('Cx\Modules\Order\Model\Entity\Subscription');
        $subscriptionObj = $subscriptionRepo->findOneBy(array('id' => $subscriptionId));

        if (!$subscriptionObj) {
            return $_ARRAYLANG['TXT_MULTISITE_WEBSITE_SUBSCRIPTION_NOT_EXISTS'];
        }

        $order = $subscriptionObj->getOrder();

        if (!$order) {
            return $_ARRAYLANG['TXT_MULTISITE_WEBSITE_ORDER_NOT_EXISTS'];
        }

        //Verify the owner of the associated Order of the Subscription is actually owned by the currently sign-in user
        if ($crmContactId != $order->getContactId()) {
            return $_ARRAYLANG['TXT_MULTISITE_WEBSITE_NOT_MULTISITE_USER'];
        }
        
        if (!\FWValidator::isEmpty($action)) {
            switch ($action) {
                case 'subscriptionCancel':
                   $subscriptionObj->setState(\Cx\Modules\Order\Model\Entity\Subscription::STATE_CANCELLED);
                    \Env::get('em')->flush();
                    return $this->parseJsonMessage($_ARRAYLANG['TXT_CORE_MODULE_MULTISITE_SUBSCRIPTION_CANCELLED_SUCCESS_MSG'], true);
                    break;
                
                case 'updateDescription':
                    $description = isset($_POST['description']) 
                                        ? contrexx_input2raw($_POST['description']) 
                                        : '';
                    $subscriptionObj->setDescription($description);
                    \Env::get('em')->flush();
                    return $this->parseJsonMessage($_ARRAYLANG['TXT_CORE_MODULE_MULTISITE_SUBSCRIPTION_DESCRIPTION_SUCCESS_MSG'], true);
                    break;
                default :
                    break;
            }
        }
        
        $product = $subscriptionObj->getProduct();

        if (!$product) {
            return $_ARRAYLANG['TXT_MULTISITE_WEBSITE_PRODUCT_NOT_EXISTS'];
        }

        $subscriptionExpirationDate = $subscriptionObj->getExpirationDate() ? $subscriptionObj->getExpirationDate()->format(ASCMS_DATE_FORMAT_DATE) : '';
        $objTemplate->setVariable(array(
            'MULTISITE_SUBSCRIPTION_ID'      => contrexx_raw2xhtml($subscriptionObj->getId()),
            'MULTISITE_WEBSITE_PRODUCT_NAME' => contrexx_raw2xhtml($product->getName()),
            'MULTISITE_WEBSITE_SUBSCRIPTION_DATE' => $subscriptionObj->getSubscriptionDate() ? contrexx_raw2xhtml($subscriptionObj->getSubscriptionDate()->format('d.m.Y')) : '',
            'MULTISITE_WEBSITE_SUBSCRIPTION_EXPIRATIONDATE' => contrexx_raw2xhtml($subscriptionExpirationDate),
            'MULTISITE_SUBSCRIPTION_DESCRIPTION' => contrexx_raw2xhtml($subscriptionObj->getDescription()),
            'MULTISITE_SUBSCRIPTION_CANCEL_CONTENT' => sprintf($_ARRAYLANG['TXT_CORE_MODULE_MULTISITE_SUBSCRIPTION_CANCEL_CONTENT'], $subscriptionExpirationDate),
            'MULTISITE_SUBSCRIPTION_CANCEL_SUBMIT_URL' => '/api/MultiSite/SubscriptionDetail?action=subscriptionCancel&id=' . $subscriptionId,
            'MULTISITE_SUBSCRIPTION_DESCRIPTION_SUBMIT_URL' => '/api/MultiSite/SubscriptionDetail?action=updateDescription&id=' . $subscriptionId,            
        ));

        $cancelButtonStatus = ($subscriptionObj->getState() !== \Cx\Modules\Order\Model\Entity\Subscription::STATE_CANCELLED);
        self::showOrHideBlock($objTemplate, 'showUpgradeButton', $product->isUpgradable());
        self::showOrHideBlock($objTemplate, 'showSubscriptionCancelButton', $cancelButtonStatus);

        if ($objTemplate->blockExists('showWebsites')) {
            $websiteCollection = $subscriptionObj->getProductEntity();
                if ($websiteCollection instanceof \Cx\Core_Modules\MultiSite\Model\Entity\WebsiteCollection) {
                    foreach ($websiteCollection->getWebsites() as $website) {
                        if (!($website instanceof \Cx\Core_Modules\MultiSite\Model\Entity\Website)) {
                            continue;
                        }
                        self::parseWebsiteDetails($objTemplate, $website);

                        $objTemplate->parse('showWebsites');
                    }
                    self::showOrHideBlock($objTemplate, 'showAddWebsiteButton', ($websiteCollection->getQuota() > count($websiteCollection->getWebsites())));
                } elseif ($websiteCollection instanceof \Cx\Core_Modules\MultiSite\Model\Entity\Website) {
                    self::parseWebsiteDetails($objTemplate, $websiteCollection);
                    self::showOrHideBlock($objTemplate, 'showAddWebsiteButton', false);
                    $objTemplate->parse('showWebsites');
                }
                if(array_key_exists('showWebsites', $objTemplate->_parsedBlocks) && $objTemplate->blockExists('showWebsitesHeader')){
                    $objTemplate->touchBlock('showWebsitesHeader');
                }else{
                    $objTemplate->hideBlock('showWebsitesHeader');
                }
        }

        //payments
        self::showOrHideBlock($objTemplate, 'showPayments', !\FWValidator::isEmpty($subscriptionObj->getExternalSubscriptionId()));

        return $objTemplate->get();
    }
    
    /**
     * Api SubscriptionAddWebsite command 
     * 
     * @param object $objTemplate Template object \Cx\Core\Html\Sigma
     * @param array  $arguments   Array parameters
     * 
     * @return string 
     */
    public function executeCommandSubscriptionAddWebsite($objTemplate, $arguments)
    {
        global $_ARRAYLANG;
        
        $objTemplate->setGlobalVariable($_ARRAYLANG);
        
        $subscriptionId = isset($arguments['id']) ? contrexx_input2raw($arguments['id']) : 0;
        $productId      = isset($arguments['productId']) ? contrexx_input2raw($arguments['productId']) : 0;

        if (!self::isUserLoggedIn()) {
            return $_ARRAYLANG['TXT_MULTISITE_WEBSITE_LOGIN_NOACCESS'];
        }

        $crmContactId = \FWUser::getFWUserObject()->objUser->getCrmUserId();
        if (\FWValidator::isEmpty($subscriptionId) && \FWValidator::isEmpty($productId)) {
            return $_ARRAYLANG['TXT_MULTISITE_WEBSITE_SUBSCRIPTIONID_EMPTY'];
        }

        if (isset($arguments['addWebsite'])) {
            $websiteName = isset($_POST['multisite_address']) ? contrexx_input2raw($_POST['multisite_address']) : '';

            $resp = array();
            if (!\FWValidator::isEmpty($subscriptionId)) {
                $resp = $this->createNewWebsiteInSubscription($subscriptionId, $websiteName);
            } elseif (!\FWValidator::isEmpty($productId)) {
                $resp = $this->createNewWebsiteByProduct($productId, $websiteName);
            }

            $responseStatus  = isset($resp['status']) && $resp['status'] == 'success';
            $responseMessage = isset($resp['message']) ? $resp['message'] : '';
            $reload          = isset($resp['reload']) ? $resp['reload'] : '';
            return $this->parseJsonMessage($responseMessage, $responseStatus, $reload);
        } else {
            $domainRepository = new \Cx\Core\Net\Model\Repository\DomainRepository();
            $mainDomain = $domainRepository->getMainDomain()->getName();
            $addressUrl = \Cx\Core\Routing\Url::fromMagic(ASCMS_PROTOCOL . '://' . $mainDomain . \Env::get('cx')->getBackendFolderName() . '/index.php?cmd=JsonData&object=MultiSite&act=address');
            
            $websiteNameMinLength = \Cx\Core\Setting\Controller\Setting::getValue('websiteNameMinLength');
            $websiteNameMaxLength = \Cx\Core\Setting\Controller\Setting::getValue('websiteNameMaxLength');
            
            $queryArguments = array(
                'addWebsite' => 1,
                'id'         => $subscriptionId,
                'productId'  => $productId,
                'page_reload'  => (isset($_GET['multisite_page_reload']) && $_GET['multisite_page_reload'] == 'reload_page' ? 'reload_page' : ''),
            );
            $objTemplate->setVariable(array(
                'MULTISITE_DOMAIN'             => \Cx\Core\Setting\Controller\Setting::getValue('multiSiteDomain'),
                'MULTISITE_RELOAD_PAGE'        => (isset($_GET['multisite_page_reload']) && $_GET['multisite_page_reload'] == 'reload_page' ? 'reload_page' : ''),
                'MULTISITE_ADDRESS_URL'        => $addressUrl->toString(),
                'MULTISITE_ADD_WEBSITE_URL'    => '/api/MultiSite/SubscriptionAddWebsite?' . self::buildHttpQueryString($queryArguments),
                'TXT_MULTISITE_CREATE_WEBSITE' => $_ARRAYLANG['TXT_MULTISITE_SUBMIT_BUTTON'],
                'TXT_MULTISITE_SITE_ADDRESS_INFO'  => sprintf($_ARRAYLANG['TXT_MULTISITE_SITE_ADDRESS_SCHEME'], $websiteNameMinLength, $websiteNameMaxLength)
            ));
            
            if (!empty($productId)) {
                $productRepository = \Env::get('em')->getRepository('Cx\Modules\Pim\Model\Entity\Product');
                $product = $productRepository->findOneBy(array('id' => $productId));
                if ($product) {
                    self::parseProductForAddWebsite($objTemplate, $product, $crmContactId);
                } else {
                    return $_ARRAYLANG['TXT_MULTISITE_WEBSITE_PRODUCT_NOT_EXISTS'];
                }
            }
            return $objTemplate->get();
        }
    }
    
    /**
     * Api Website command 
     * 
     * @param object $objTemplate Template object \Cx\Core\Html\Sigma
     * @param array  $arguments   Array parameters
     * 
     * @return string 
     */
    public function executeCommandWebsite($objTemplate, $arguments)
    {
        global $_ARRAYLANG;
        $objTemplate->setGlobalVariable($_ARRAYLANG);

        if (!self::isUserLoggedIn()) {
            return $_ARRAYLANG['TXT_MULTISITE_WEBSITE_LOGIN_NOACCESS'];
        }

        $websiteId = isset($arguments['id']) ? contrexx_input2raw($arguments['id']) : '';
        if (empty($websiteId)) {
            return $_ARRAYLANG['TXT_MULTISITE_UNKOWN_WEBSITE'];
        }

        $websiteServiceRepo = \Env::get('em')->getRepository('Cx\Core_Modules\MultiSite\Model\Entity\Website');
        $website = $websiteServiceRepo->findOneById($websiteId);
        if (!$website) {
            return $_ARRAYLANG['TXT_MULTISITE_WEBSITE_NOT_EXISTS'];
        }
        if($website->getOwnerId() != \FWUser::getFWUserObject()->objUser->getId()){
            return $_ARRAYLANG['TXT_MULTISITE_WEBSITE_NOT_MULTISITE_USER'];
        }

        //show the frontend
        $status = ($website->getStatus() == \Cx\Core_Modules\MultiSite\Model\Entity\Website::STATE_ONLINE);
        $statusDisabled = ($website->getStatus() == \Cx\Core_Modules\MultiSite\Model\Entity\Website::STATE_DISABLED);
        $isTrialWebsite = false;
        
        // check the website is trial or not
        $subscription = \Env::get('em')->getRepository('Cx\Modules\Order\Model\Entity\Subscription')->findOneBy(array('productEntityId' => $websiteId));
        if ($subscription && $objTemplate->blockExists('showUpgradeButton')) {
            $productEntity = $subscription->getProductEntity();
            $product = $subscription->getProduct();
            if ($productEntity instanceof \Cx\Core_Modules\MultiSite\Model\Entity\Website && $product->getName() === 'Trial') {
                $isTrialWebsite = true;
            }
        }

        $objTemplate->setVariable(array(
            'MULTISITE_WEBSITE_FRONTEND_LINK'       => $this->getApiProtocol() . $website->getBaseDn()->getName(),
            'MULTISITE_WEBSITE_DELETE_REDIRECT_URL' => \Cx\Core\Routing\Url::fromModuleAndCmd('MultiSite', 'Subscription')->toString(),
            'MULTISITE_SUBSCRIPTION_ID'             => !\FWValidator::isEmpty($subscription) ? $subscription->getId() : ''
        ));
        self::showOrHideBlock($objTemplate, 'showWebsiteViewButton', $status);
        self::showOrHideBlock($objTemplate, 'showAdminButton', $status);
        self::showOrHideBlock($objTemplate, 'showUpgradeButton', $isTrialWebsite);
        //Show the Website Admin and Backend group users
        if ($objTemplate->blockExists('showWebsiteAdminUsers')) {
            $websiteAdminUsers = $website->getAdminUsers();
            $showOverview = array(
                    'id' => array(
                            'showOverview' => false,
                        ),
                    'username'=> array(
                              'header' => $_ARRAYLANG['TXT_MULTISITE_WEBSITE_TITLE_NAME']
                        ),
                    'email'=> array(
                              'header' => $_ARRAYLANG['TXT_MULTISITE_WEBSITE_TITLE_EMAIL']
                        ),
                    'isAdmin' => array(
                            'showOverview' => false,
                        ),
                    'password' => array(
                            'showOverview' => false,
                        ),
                    'authToken' => array(
                            'showOverview' => false,
                        ),
                    'authTokenTimeout' => array(
                            'showOverview' => false,
                        ),
                    'regdate' => array(
                            'showOverview' => false,
                        ),
                    'expiration' => array(
                            'showOverview' => false,
                        ),
                    'validity' => array(
                            'showOverview' => false,
                        ),
                    'lastAuth' => array(
                            'showOverview' => false,
                        ),
                    'lastAuthStatus' => array(
                            'showOverview' => false,
                        ),
                    'lastActivity' => array(
                            'showOverview' => false,
                        ),
                    'emailAccess' => array(
                            'showOverview' => false,
                        ),
                    'frontendLangId' => array(
                            'showOverview' => false,
                        ),
                    'backendLangId' => array(
                            'showOverview' => false,
                        ),
                    'active' => array(
                            'showOverview' => false,
                        ),
                    'verified' => array(
                            'showOverview' => false,
                        ),
                    'primaryGroup' => array(
                            'showOverview' => false,
                        ),
                    'profileAccess' => array(
                            'showOverview' => false,
                        ),
                    'restoreKey' => array(
                            'showOverview' => false,
                        ),
                    'restoreKeyTime' => array(
                            'showOverview' => false,
                        ),
                    'u2uActive' => array(
                            'showOverview' => false,
                        ),
                    'userProfile' => array(
                            'showOverview' => false,
                        ),
                    'group' => array(
                            'showOverview' => false,
                        ),
                );
            //display the admin users using viewgenerator
            if($websiteAdminUsers){
                $view = new \Cx\Core\Html\Controller\ViewGenerator($websiteAdminUsers, array(
                    'functions' => array(
                        'add' => false,
                        'edit' => false,
                        'delete' => false,
                        'sorting' => false,
                        'baseUrl' => '',
                        'actions' => function($rowData) {
                            if ($rowData['email'] != \FWUser::getFWUserObject()->objUser->getEmail()) {
                                return '<a class="entypo-tools" data-user_id= "' . $rowData['id'] . '" data-page= "Edit"></a>  '
                                        . '<a class="entypo-trash" data-user_id= "' . $rowData['id'] . '" data-page= "Delete"></a>';
                            }
                        },
                    ),
                    'fields' => $showOverview
                ));
                $objTemplate->setVariable('ADMIN_USERS', $view->render());
            }
        }
        
        //show section Domains if component NetManager is licensed on Website.
        $response = JsonMultiSite::executeCommandOnWebsite('isComponentLicensed', array('component' => 'NetManager'), $website);
        $showDomainSectionStatus = isset($response->status) && $response->status == 'success' && isset($response->data) && $response->data->status == 'success';
        self::showOrHideBlock($objTemplate, 'showDomainsSection', $showDomainSectionStatus);
        
        //show website base domain and domain aliases
        if ($showDomainSectionStatus && $objTemplate->blockExists('showWebsiteDomains')) {
            $resp = JsonMultiSite::executeCommandOnWebsite('getMainDomain', array(), $website);
            $mainDomainName = '';
            if ($resp->status == 'success' && $resp->data->status == 'success') {
                $mainDomainName = $resp->data->mainDomain;
            }             
            $domains = array_merge(array($website->getBaseDn()), $website->getDomainAliases());
            foreach ($domains as $domain) {
                $isBaseDomain = $domain->getType() == \Cx\Core_Modules\MultiSite\Model\Entity\Domain::TYPE_BASE_DOMAIN;        
                $domainId     = $isBaseDomain ? $domain->getId() : $domain->getCoreNetDomainId(); 
                $objTemplate->setVariable(array(
                        'MULTISITE_WEBSITE_DOMAIN'                    => contrexx_raw2xhtml($domain->getName()),
                        'MULTISITE_WEBSITE_DOMAIN_ID'                 => contrexx_raw2xhtml($domainId),
                        'MULTISITE_WEBSITE_MAIN_DOMAIN_RADIO_CHECKED' => ($domain->getName() === $mainDomainName) ? 'checked' : '',
                        'MULTISITE_WEBSITE_DOMAIN_SUBMIT_URL'         => '/api/MultiSite/Domain?action=Select&website_id=' . $website->getId() . '&domain_id=' . contrexx_raw2xhtml($domainId) . '&domain_name=' . contrexx_raw2xhtml($domain->getName())
                ));
                // hide the edit/delete icons if the domain is selected as main domain or  base domain.
                $domainActionStatus = !$statusDisabled ? ($domain->getName() !== $mainDomainName && !$isBaseDomain) : false;
                self::showOrHideBlock($objTemplate, 'showWebsiteDomainActions', $domainActionStatus);
                //hide the selection websiteMainDomain if the website is disabled
                self::showOrHideBlock($objTemplate, 'showWebsiteMainDomain', !$statusDisabled);
                $objTemplate->parse('showWebsiteDomains');                
            }
        }        
        
        //show the website's domain name
        if ($objTemplate->blockExists('showWebsiteDomainName')) {
            $domain = $website->getBaseDn();
            if ($domain) {
                $objTemplate->setVariable(array(
                    'MULTISITE_WEBSITE_DOMAIN_NAME' => contrexx_raw2xhtml($domain->getName()),
                ));
            }
        }
        
        //show the website's mail service enable|disable button
        $showMailService = false;
        if ($objTemplate->blockExists('activateMailService') && $objTemplate->blockExists('deactivateMailService')) {
            $mailServiceServerStatus = false;
            $additionalDataResp = JsonMultiSite::executeCommandOnWebsite('getModuleAdditionalData', array('moduleName' => 'MultiSite', 'additionalType' => 'Mail'), $website);
            $additionalData     = null;
            if ($additionalDataResp->status == 'success' && $additionalDataResp->data->status == 'success') {
                $additionalData = $additionalDataResp->data->additionalData;
            }

            $showMailService = (   !\FWValidator::isEmpty($additionalData)
                                && isset($additionalData->service) 
                                && !\FWValidator::isEmpty($additionalData->service));
            if ($website->getMailServiceServer() && !\FWValidator::isEmpty($website->getMailAccountId())) {
                $response = \Cx\Core_Modules\MultiSite\Controller\JsonMultiSite::executeCommandOnManager('getMailServiceStatus', array('websiteId' => $websiteId));
                if (!\FWValidator::isEmpty($response)
                        && $response->status == 'success' 
                        && $response->data->status == 'success'
                ) {
                    $mailServiceServerStatus = $response->data->mailServiceStatus;
                }
            }
            self::showOrHideBlock($objTemplate, 'deactivateMailService', $mailServiceServerStatus);
            self::showOrHideBlock($objTemplate, 'openAdministration', $mailServiceServerStatus);
            self::showOrHideBlock($objTemplate, 'activateMailService', !$mailServiceServerStatus);

            $objTemplate->setVariable('MULTISITE_WEBSITE_MAIL_SERVICE_STATUS', $statusDisabled ? ($mailServiceServerStatus 
                                                                                                  ? $_ARRAYLANG['TXT_CORE_MODULE_MULTISITE_WEBSITE_MAIL_SERVICE_ENABLED']
                                                                                                  : $_ARRAYLANG['TXT_CORE_MODULE_MULTISITE_WEBSITE_MAIL_SERVICE_DISABLED']
                                                                                                 )
                                                                                               : '');

        }
        
        //show the website's resources
        if ($objTemplate->blockExists('showWebsiteResources')) {
            $resourceUsageStats = $website->getResourceUsageStats();
            $objTemplate->setVariable(array(
                'MULTISITE_WEBSITE_ADMIN_USERS_USAGE'   => $resourceUsageStats->accessAdminUser->usage,
                'MULTISITE_WEBSITE_ADMIN_USERS_QUOTA'   => $resourceUsageStats->accessAdminUser->quota,
                'MULTISITE_WEBSITE_CONTACT_FORMS_USAGE' => $resourceUsageStats->contactForm->usage,
                'MULTISITE_WEBSITE_CONTACT_FORMS_QUOTA' => $resourceUsageStats->contactForm->quota,
                'MULTISITE_WEBSITE_SHOP_PRODUCTS_USAGE' => $resourceUsageStats->shopProduct->usage,
                'MULTISITE_WEBSITE_SHOP_PRODUCTS_QUOTA' => $resourceUsageStats->shopProduct->quota,
                'MULTISITE_WEBSITE_CRM_CUSTOMERS_USAGE' => $resourceUsageStats->crmCustomer->usage,
                'MULTISITE_WEBSITE_CRM_CUSTOMERS_QUOTA' => $resourceUsageStats->crmCustomer->quota,
            ));
            $objTemplate->parse('showWebsiteResources');
        }
        $objTemplate->setGlobalVariable(array(
            'MULTISITE_WEBSITE_ID' => contrexx_raw2xhtml($websiteId)
        ));
        
        //hide the website info if the website is disabled
        self::showOrHideBlock($objTemplate, 'showWebsiteInfo', !$statusDisabled);
         //hide the website add user button if the website is disabled
        self::showOrHideBlock($objTemplate, 'showWebsiteAdminAddUser', !$statusDisabled);
        //hide the  add domain button if the website is disabled
        self::showOrHideBlock($objTemplate, 'showWebsiteAddDomain', !$statusDisabled);
        //hide the  website mail service if the website is disabled
        self::showOrHideBlock($objTemplate, 'showWebsiteMailService', !$statusDisabled);
         // show/hide the  website mail service
        self::showOrHideBlock($objTemplate, 'showMailServiceSection', $showMailService);
        
        return $objTemplate->get();
    }
  
    /**
     * Api Domain command 
     * 
     * @param object $objTemplate Template object \Cx\Core\Html\Sigma
     * @param array  $arguments   Array parameters
     * 
     * @return string 
     */
    public function executeCommandDomain($objTemplate, $arguments) {

        global $_ARRAYLANG;
        $objTemplate->setGlobalVariable($_ARRAYLANG);

        if (!self::isUserLoggedIn()) {
            return $_ARRAYLANG['TXT_MULTISITE_WEBSITE_LOGIN_NOACCESS'];
        }

        $websiteId = isset($arguments['website_id']) ? contrexx_input2raw($arguments['website_id']) : '';
        if (empty($websiteId)) {
            return $_ARRAYLANG['TXT_MULTISITE_UNKOWN_WEBSITE'];
        }

        $websiteServiceRepo = \Env::get('em')->getRepository('Cx\Core_Modules\MultiSite\Model\Entity\Website');
        $website = $websiteServiceRepo->findOneById($websiteId);
        if (!$website) {
            return $_ARRAYLANG['TXT_MULTISITE_WEBSITE_NOT_EXISTS'];
        }

        if ($website->getOwnerId() != \FWUser::getFWUserObject()->objUser->getId()) {
            return $_ARRAYLANG['TXT_MULTISITE_WEBSITE_NOT_MULTISITE_USER'];
        }
        
        $loadPageAction   = isset($arguments[1]) ? contrexx_input2raw($arguments[1]) : '';
        $submitFormAction = isset($arguments['action']) ? contrexx_input2raw($arguments['action']) : '';
        $domainId         = isset($arguments['domain_id']) ? contrexx_input2raw($arguments['domain_id']) : '';
        $domainName       = isset($arguments['domain_name']) ? contrexx_input2raw($arguments['domain_name']):'';
                
        //processing form values after submit
        if (!\FWValidator::isEmpty($submitFormAction)) {
            try {
                if (\FWValidator::isEmpty($domainId) && $submitFormAction != 'Add') {
                    return $this->parseJsonMessage($_ARRAYLANG['TXT_CORE_MODULE_MULTISITE_WEBSITE_DOMAIN_UNKNOWN'], false);
                }
                switch ($submitFormAction) {
                    case 'Add':
                        if (\FWValidator::isEmpty($_POST['add_domain'])) {
                            return $this->parseJsonMessage($_ARRAYLANG['TXT_CORE_MODULE_MULTISITE_WEBSITE_DOMAIN_UNKNOWN'], false);
                        }
                        $command = 'mapNetDomain';
                        $params = array(
                            'domainName' => $_POST['add_domain']
                        );
                        break;

                    case 'Edit':
                        if (\FWValidator::isEmpty($_POST['edit_domain'])) {
                            return $this->parseJsonMessage($_ARRAYLANG['TXT_CORE_MODULE_MULTISITE_WEBSITE_DOMAIN_UNKNOWN'], false);
                        }
                        $command = 'updateNetDomain';
                        $params = array(
                            'domainName' => $_POST['edit_domain'],
                            'domainId' => $domainId
                        );
                        break;

                    case 'Delete':
                        $command = 'unMapNetDomain';
                        $params = array(
                            'domainId' => $domainId
                        );
                        break;
                        
                    case 'Select':
                        $command = 'setMainDomain';
                        $params = array(
                          'mainDomainId'   => ($website->getBaseDn()->getName() === $domainName) ? 0 : $domainId
                        );
                        break;
                    
                    default :
                        return $this->parseJsonMessage($_ARRAYLANG['TXT_CORE_MODULE_MULTISITE_WEBSITE_DOMAIN_UNKNOWN'], false);
                        break;
                }
                if (isset($command) && isset($params)) {
                    $response = \Cx\Core_Modules\MultiSite\Controller\JsonMultiSite::executeCommandOnWebsite($command, $params, $website);
                    if ($response && $response->status == 'success' && $response->data->status == 'success') {
                        $message = ($submitFormAction == 'Select') 
                                    ? sprintf($_ARRAYLANG['TXT_CORE_MODULE_MULTISITE_WEBSITE_DOMAIN_'.strtoupper($submitFormAction).'_SUCCESS_MSG'], contrexx_raw2xhtml($domainName))
                                    : $_ARRAYLANG['TXT_CORE_MODULE_MULTISITE_WEBSITE_DOMAIN_'.strtoupper($submitFormAction).'_SUCCESS_MSG'];
                                
                        return $this->parseJsonMessage($message, true);
                    } else {
                        return $this->parseJsonMessage($_ARRAYLANG['TXT_CORE_MODULE_MULTISITE_WEBSITE_DOMAIN_'.strtoupper($submitFormAction).'_FAILED'], false);
                    }
                }
            } catch (\Exception $e) {
                \DBG::log('Failed to '.$submitFormAction. 'Domain'. $e->message());
                return $this->parseJsonMessage($_ARRAYLANG['TXT_CORE_MODULE_MULTISITE_WEBSITE_DOMAIN_'.strtoupper($submitFormAction).'_FAILED'], false);
            }
        } else {
            if(!empty($domainName) && !empty($domainId)){
                if (($loadPageAction == 'Delete') && $objTemplate->blockExists('showDeleteDomainInfo')) {
                    $objTemplate->setVariable(array(
                        'TXT_MULTISITE_DELETE_DOMAIN_INFO' => sprintf($_ARRAYLANG['TXT_CORE_MODULE_MULTISITE_WEBSITE_DOMAIN_REMOVE_INFO'], contrexx_raw2xhtml($domainName)),
                        'MULTISITE_DOMAIN_NAME' => contrexx_raw2xhtml($domainName),
                        'MULTISITE_WEBSITE_DOMAIN_ALIAS_ID' => contrexx_raw2xhtml($domainId)
                    ));
                }

                if (($loadPageAction == 'Edit') && $objTemplate->blockExists('showEditDomainName')) {
                    $objTemplate->setVariable(array(
                        'MULTISITE_DOMAIN_NAME' => contrexx_raw2xhtml($domainName),
                        'MULTISITE_WEBSITE_DOMAIN_ALIAS_ID' => contrexx_raw2xhtml($domainId)
                    ));
                }
            }

            $objTemplate->setVariable(array(
                'MULTISITE_WEBSITE_DOMAIN_SUBMIT_URL' => '/api/MultiSite/Domain?action=' . $loadPageAction . '&website_id=' . $websiteId . '&domain_id=' . $domainId,
            ));

            return $objTemplate->get();
        }
    }
    
    /**
     * Api command Admin 
     * 
     * @param object $objTemplate Template object \Cx\Core\Html\Sigma
     * @param array  $arguments   Array parameters
     * 
     * @return string 
     */
    public function executeCommandAdmin($objTemplate, $arguments) 
    {
        global $objInit, $_CORELANG, $_ARRAYLANG;

        $coreLangData   = $objInit->loadLanguageData('core');
        $accessLangData = $objInit->loadLanguageData('Access');
        $_CORELANG = $_ARRAYLANG = array_merge($_ARRAYLANG, $coreLangData, $accessLangData );
        $objTemplate->setGlobalVariable($_ARRAYLANG);
        if (!self::isUserLoggedIn()) {
            return $_ARRAYLANG['TXT_MULTISITE_WEBSITE_LOGIN_NOACCESS'];
        }

        $objUser   = \FWUser::getFWUserObject()->objUser;
        $websiteId = isset($arguments['website_id']) ? contrexx_input2raw($arguments['website_id']) : 0;
        $userId    = isset($arguments['user_id']) ? contrexx_input2raw($arguments['user_id']) : 0;

        if (\FWValidator::isEmpty($websiteId)) {
            return $_ARRAYLANG['TXT_MULTISITE_WEBSITE_NOT_EXISTS'];
        }

        $website = \Env::get('em')->getRepository('Cx\Core_Modules\MultiSite\Model\Entity\Website')->findOneById($websiteId);
        if (!$website) {
            return $_ARRAYLANG['TXT_MULTISITE_UNKOWN_WEBSITE'];
        }

        if ($website->getOwnerId() != $objUser->getId()) {
            return $_ARRAYLANG['TXT_MULTISITE_WEBSITE_NOT_MULTISITE_USER'];
        }

        $loadPageAction   = isset($arguments[1]) ? contrexx_input2raw($arguments[1]) : '';
        $submitFormAction = isset($arguments['action']) ? contrexx_input2raw($arguments['action']) : '';

        if (!\FWValidator::isEmpty($submitFormAction)) {
            try {
                if (\FWValidator::isEmpty($userId) && $submitFormAction != 'Add') {
                    return $this->parseJsonMessage($_ARRAYLANG['TXT_CORE_MODULE_MULTISITE_UNKOWN_USER_REQUEST'], false);
                }

                $successMsg = $_ARRAYLANG['TXT_CORE_MODULE_MULTISITE_ADMIN_USER_' . strtoupper($submitFormAction) . '_SUCCESS'];
                $errorMsg = $_ARRAYLANG['TXT_CORE_MODULE_MULTISITE_ADMIN_USER_' . strtoupper($submitFormAction) . '_FAILED'];
                switch ($submitFormAction) {

                    case 'Edit':
                        $command                                                    = 'updateUser';
                        $params['websiteUserId']                                    = $_POST['adminUser']['id'];
                    case 'Add' :
                        $email = isset($_POST['adminUser']['email']) ? contrexx_input2raw($_POST['adminUser']['email']) : '';
                        if (\FWValidator::isEmpty($email) || !\FWValidator::isEmail($email)) {
                            return $this->parseJsonMessage($_ARRAYLANG['TXT_ACCESS_INVALID_ENTERED_EMAIL_ADDRESS'], false);
                        }
                        if(!isset($command)) {
                            $command                                                = 'createAdminUser';
                        }
                        
                        $params['multisite_user_account_email']                     = $_POST['adminUser']['email'];
                        $params['multisite_user_account_password']                  = contrexx_input2raw($_POST['adminUser']['password']);
                        $params['multisite_user_account_password_confirmed']        = contrexx_input2raw($_POST['adminUser']['confirm_password']);
                        $params['multisite_user_profile_attribute']['lastname']     = array(contrexx_input2raw($_POST['adminUser']['userProfile']['lastname']));
                        $params['multisite_user_profile_attribute']['firstname']    = array(contrexx_input2raw($_POST['adminUser']['userProfile']['firstname']));
                        break;
                    case 'Delete':
                        $command = 'removeUser';
                        $params  = array('userId' => $userId);
                        break;
                    default:
                        return $this->parseJsonMessage($_ARRAYLANG['TXT_CORE_MODULE_MULTISITE_UNKOWN_USER_REQUEST'], false);
                        break;
                }
                if (isset($command) && isset($params)) {
                    $response = JsonMultiSite::executeCommandOnWebsite($command, $params, $website);
                    if ($response && $response->status == 'success' && $response->data->status == 'success') {
                        return $this->parseJsonMessage($successMsg, true);
                    } else {
                        if (is_object($response->message)) {
                            return $this->parseJsonMessage($response->message->message, false);
                        } else {
                            return $this->parseJsonMessage($response->message, false);
                        }
                    }
                }
            } catch (\Exception $e) {
                \DBG::log('Failed to ' . $submitFormAction . 'administrator account' . $e->message());
                return $this->parseJsonMessage($errorMsg, false);
            }
        } else {

            if (!\FWValidator::isEmpty($userId) && $loadPageAction != 'Add') {
                //get admin user from website by id
                $adminUser = current($website->getUser($userId));
                if (\FWValidator::isEmpty($adminUser)) {
                    return $_ARRAYLANG['TXT_MULTISITE_WEBSITE_NOT_MULTISITE_USER'];
                }
            }

            if ($objTemplate->blockExists('showEditAdminUser')) {
                $objTemplate->setVariable(array(
                    'MULTISITE_ADMIN_USER_FIRSTNAME'=> $adminUser->getUserProfile()->getFirstname(),
                    'MULTISITE_ADMIN_USER_LASTNAME' => $adminUser->getUserProfile()->getLastname(),
                    'MULTISITE_ADMIN_USER_EMAIL'    => $adminUser->getEmail(),
                    'MULTISITE_ADMIN_USER_ID'       => $adminUser->getId()
                ));
            }

            if ($objTemplate->blockExists('showDeleteAdminUser')) {
                $objTemplate->setVariable(array(
                    'MULTISITE_ADMIN_USER_DELETE_CONFIRM' => sprintf($_ARRAYLANG['TXT_CORE_MODULE_MULTISITE_ADMIN_USER_DELETE_CONFIRM'], $adminUser->getEmail()),
                    'MULTISITE_ADMIN_USER_ID' => $adminUser->getId()
                ));
            }
            $objTemplate->setVariable(array(
                'MULTISITE_ADMIN_USER_SUBMIT_URL' => '/api/MultiSite/Admin?action=' . $loadPageAction . '&website_id=' . $websiteId . '&user_id=' . $userId,
            ));
        }
        return $objTemplate->get();
    }

    /**
     * Api Payrexx command
     */
    public function executeCommandPayrexx()
    {                
        $transaction = isset($_POST['transaction'])
                       ? $_POST['transaction']
                       : (isset($_POST['subscription'])
                           ? $_POST['subscription']
                           : array());
        $invoice = isset($transaction['invoice']) ? $transaction['invoice'] : array();
        $contact = isset($transaction['contact']) ? $transaction['contact'] : array();
        $hasTransaction = isset($_POST['transaction']);
        
        if (
               \FWValidator::isEmpty($transaction)
            || \FWValidator::isEmpty($invoice)
            || \FWValidator::isEmpty($contact)
        ) {
            return;
        }
        
        //For cancelling the subscription
        $subscriptionId     = $hasTransaction ? (isset($transaction['subscription']) ? $transaction['subscription']['id'] : '')  : $transaction['id'];
        $subscriptionStatus = $hasTransaction ? (isset($transaction['subscription']) ? $transaction['subscription']['status'] : '')  : $transaction['status'];
        $subscriptionEnd    = $hasTransaction
                                ? (isset($transaction['subscription']) && isset($transaction['subscription']['end'])
                                     ? $transaction['subscription']['end']
                                     : ''
                                  )
                                : (isset($transaction['end']) ? $transaction['end'] : '');
        
        if (   !\FWValidator::isEmpty($subscriptionId)
            && !\FWValidator::isEmpty($subscriptionEnd)
            && $subscriptionStatus === \Cx\Modules\Order\Model\Entity\Subscription::STATE_CANCELLED
        ) {
            $subscriptionRepo = \Env::get('em')->getRepository('Cx\Modules\Order\Model\Entity\Subscription');
            $subscription = $subscriptionRepo->findOneBy(array('externalSubscriptionId' => $subscriptionId));
            if (!\FWValidator::isEmpty($subscription)) {
                // TO-DO:check the payrexx account to confirm whether the subscription is cancelled
                
                $subscription->setExpirationDate(new \DateTime($subscriptionEnd));
                $subscription->setState(\Cx\Modules\Order\Model\Entity\Subscription::STATE_CANCELLED);
                \Env::get('em')->flush();
                return;
            }
        }
        
        // register placed payment
        $invoiceReferId = isset($invoice['referenceId']) ? $invoice['referenceId'] : '';
        $invoiceId      = isset($invoice['paymentRequestId']) ? $invoice['paymentRequestId'] : 0;
        if (\FWValidator::isEmpty($invoiceReferId) || \FWValidator::isEmpty($invoiceId)) {
            return;
        }
        
        $instanceName  = \Cx\Core\Setting\Controller\Setting::getValue('payrexxAccount');
        $apiSecret     = \Cx\Core\Setting\Controller\Setting::getValue('payrexxApiSecret');

        $payrexx = new \Payrexx\Payrexx($instanceName, $apiSecret);

        $invoiceRequest = new \Payrexx\Models\Request\Invoice();
        $invoiceRequest->setId($invoiceId);

        try {
            $response = $payrexx->getOne($invoiceRequest);
        } catch (\Payrexx\PayrexxException $e) {
            throw new MultiSiteException("Failed to get payment response:". $e->getMessage());
        }
        
        if (   isset($transaction['status']) && ($transaction['status'] === 'confirmed')
            && !\FWValidator::isEmpty($response)
            && $response instanceof \Payrexx\Models\Response\Invoice
            && $response->getStatus() == 'confirmed'
            && $invoice['amount'] == ($response->getAmount() / 100)
            && $invoice['referenceId'] == $response->getReferenceId()
        ) {
            $transactionReference = $invoiceReferId . (!\FWValidator::isEmpty($subscriptionId) ? "$subscriptionId|" : '');
            self::createPayrexxPayment($transactionReference, $invoice['amount'], $transaction);
        }
    }
    
    /**
     * Api Backup command
     */
    public function executeCommandBackup($arguments) 
    {
        try {
            $websiteId = isset($arguments['websiteId']) ? contrexx_input2raw($arguments['websiteId']) : 0;
            $backupLocation = isset($arguments['backupLocation']) ? contrexx_input2raw($arguments['backupLocation']) : '';

            if (!empty($websiteId)) {
                $websiteServiceRepo = \Env::get('em')->getRepository('Cx\Core_Modules\MultiSite\Model\Entity\Website');
                $website = $websiteServiceRepo->findOneById($websiteId);

                if (!$website instanceof \Cx\Core_Modules\MultiSite\Model\Entity\Website) {
                    return 'Website Not Exists.';
                }
                
                $websiteServiceServer = $website->getWebsiteServiceServer();
                $params = array(
                        'websiteId' => $websiteId,
                        'backupLocation' => $backupLocation
                );
            } else {
                $defaultServiceServerId = \Cx\Core\Setting\Controller\Setting::getValue('defaultWebsiteServiceServer');
                if (\FWValidator::isEmpty($defaultServiceServerId)) {
                    return 'Invalid Service server Id.';
                }
                
                $websiteServiceServerRepo = \Env::get('em')->getRepository('Cx\Core_Modules\MultiSite\Model\Entity\WebsiteServiceServer');
                $websiteServiceServer  = $websiteServiceServerRepo->findOneById($defaultServiceServerId);
                $params = array(
                    'backupLocation' => $backupLocation
                );
            }
                
            if ($websiteServiceServer instanceof \Cx\Core_Modules\MultiSite\Model\Entity\WebsiteServiceServer) {
                $resp = \Cx\Core_Modules\MultiSite\Controller\JsonMultiSite::executeCommandOnServiceServer('websiteBackup', $params, $websiteServiceServer);
                if ($resp->status == 'success' && $resp->data->status = 'success') {
                     //TODO display success message when ajax success
                   return $resp->data->message;
                }
                //TODO display error message when ajax fails
                return $resp->data->message;
            }
            $this->cx->getEvents()->triggerEvent(
                    'SysLog/Add', array(
                        'severity' => 'WARNING',
                        'message' => 'This website doesnot exists in the service server',
                        'data' => ' ',
            ));
            return 'Unknown website service server';
        } catch (\Exception $e) {
            throw new MultiSiteException("Failed to backup the website:" . $e->getMessage());
        }
    }
    
    /**
     * Api Cron command
     */
    public function executeCommandCron()
    {
        $cron = new CronController();
        $cron->sendNotificationMails();
        
        //  Terminate the cancelled subscription.
        $this->disableCancelledWebsites();
    }

    /**
     * Create new payment (handler Payrexx)
     * 
     * @param string $transactionReference
     * @param string $amount
     * @param array  $transactionData
     * 
     * @return null
     */
    public static function createPayrexxPayment($transactionReference, $amount, $transactionData)
    {
        if (\FWValidator::isEmpty($transactionReference) || \FWValidator::isEmpty($amount) || \FWValidator::isEmpty($transactionData)) {
            return;
        }
        
        $payment = new \Cx\Modules\Order\Model\Entity\Payment();
        $payment->setHandler(\Cx\Modules\Order\Model\Entity\Payment::HANDLER_PAYREXX);
        $payment->setAmount($amount);
        $payment->setTransactionReference($transactionReference);
        $payment->setTransactionData($transactionData);
        \Env::get('em')->persist($payment);
        \Env::get('em')->flush();
    }
    
    /**
     * Terminate the cancelled subscription.
     * 
     * @return null
     */
    public function disableCancelledWebsites()
    {
        $subscriptionRepo = \Env::get('em')->getRepository('Cx\Modules\Order\Model\Entity\Subscription');
        $subscriptions    = $subscriptionRepo->getExpiredSubscriptionsByCriteria(\Cx\Modules\Order\Model\Entity\Subscription::STATE_CANCELLED);
        
        if (\FWValidator::isEmpty($subscriptions)) {
            return;
        }
        
        foreach ($subscriptions as $subscription) {
            $subscription->terminate();
        }
        \Env::get('em')->flush();
    }

    /**
     * Create new website into the existing subscription
     * 
     * @param integer $subscriptionId Subscription id
     * @param string  $websiteName    Name of the website
     * 
     * return array return's array that contains array('status' => success | error, 'message' => 'Status message')
     */
    public function createNewWebsiteInSubscription($subscriptionId, $websiteName)
    {
        global $_ARRAYLANG;
        
        try {
            $subscriptionRepo = \Env::get('em')->getRepository('Cx\Modules\Order\Model\Entity\Subscription');
            $subscriptionObj = $subscriptionRepo->findOneBy(array('id' => $subscriptionId));

            //check the subscription is exist
            if (!$subscriptionObj) {
                return array('status' => 'error', 'message' => $_ARRAYLANG['TXT_MULTISITE_WEBSITE_SUBSCRIPTION_NOT_EXISTS']);
            }

            //get sign-in user crm id!
            $crmContactId = \FWUser::getFWUserObject()->objUser->getCrmUserId();

            if (empty($crmContactId)) {
               return array('status' => 'error', 'message' => $_ARRAYLANG['TXT_MULTISITE_WEBSITE_NOT_MULTISITE_USER']);
            }

            $order = $subscriptionObj->getOrder();
            if (!$order) {
                return array('status' => 'error', 'message' => $_ARRAYLANG['TXT_MULTISITE_WEBSITE_ORDER_NOT_EXISTS']);
            }

            //Verify the owner of the associated Order of the Subscription is actually owned by the currently sign-in user
            if ($crmContactId != $order->getContactId()) {
                return array('status' => 'error', 'message' => $_ARRAYLANG['TXT_MULTISITE_WEBSITE_NOT_MULTISITE_USER']);
            }

            //get website collections
            $websiteCollection = $subscriptionObj->getProductEntity();
            if ($websiteCollection instanceof \Cx\Core_Modules\MultiSite\Model\Entity\WebsiteCollection) {
                if ($websiteCollection->getQuota() <= count($websiteCollection->getWebsites())) {
                    return array('status' => 'error', 'message' => sprintf($_ARRAYLANG['TXT_CORE_MODULE_MULTISITE_MAXIMUM_QUOTA_REACHED'], $websiteCollection->getQuota()));
                }
                //create new website object and add to website
                $website = \Env::get('em')->getRepository('Cx\Core_Modules\MultiSite\Model\Entity\Website')->initWebsite($websiteName, \FWUser::getFWUserObject()->objUser);
                $websiteCollection->addWebsite($website);
                \Env::get('em')->persist($website);
                // flush $website to database -> subscription will need the ID of $website
                // to properly work
                \Env::get('em')->flush();

                $product = $subscriptionObj->getProduct();
                //check the product
                if (!$product) {
                    return array('status' => 'error', 'message' => $_ARRAYLANG['TXT_MULTISITE_WEBSITE_PRODUCT_NOT_EXISTS']);
                }
                $productEntityAttributes = $product->getEntityAttributes();
                //pass the website template value
                $options = array(
                    'websiteTemplate'   => $productEntityAttributes['websiteTemplate'],
                    'initialSignUp'     => false,
                );
                //website setup process
                $websiteStatus = $website->setup($options);
                if ($websiteStatus['status'] == 'success') {
                    return array('status' => 'success', 'message' => array('message' => $_ARRAYLANG['TXT_CORE_MODULE_MULTISITE_ADD_WEBSITE_SUCCESS'], 'websiteId' => $website->getId()), 'reload' => (isset($_GET['page_reload']) && $_GET['page_reload'] == 'reload_page' ? true : false));
                }
                
                return array('status' => 'error', 'message' => $_ARRAYLANG['TXT_CORE_MODULE_MULTISITE_ADD_WEBSITE_FAILED']);
            }
        } catch (\Exception $e) {
            \DBG::log("Failed to add website:" . $e->getMessage());
            return array('status' => 'error', 'message' => $_ARRAYLANG['TXT_CORE_MODULE_MULTISITE_ADD_WEBSITE_FAILED']);
        }
    }
    
    /**
     * Create new website based on the given product id and website name
     * 
     * @param integer $productId   Product id
     * @param string  $websiteName Website name
     * 
     * return array return's array that contains array('status' => success | error, 'message' => 'Status message')
     */
    public function createNewWebsiteByProduct($productId, $websiteName)
    {
        global $_ARRAYLANG;
        
        try {
            $productRepository = \Env::get('em')->getRepository('Cx\Modules\Pim\Model\Entity\Product');
            $product = $productRepository->findOneBy(array('id' => $productId));
            
            if (!$product) {
                return array('status' => 'error', 'message' => $_ARRAYLANG['TXT_MULTISITE_WEBSITE_PRODUCT_NOT_EXISTS']);
            }
            
            //get sign-in user crm id!
            $crmContactId = \FWUser::getFWUserObject()->objUser->getCrmUserId();

            if (empty($crmContactId)) {
               return array('status' => 'error', 'message' => $_ARRAYLANG['TXT_MULTISITE_WEBSITE_NOT_MULTISITE_USER']);
            }
            
            // create new subscription of selected product
            $subscriptionOptions = array(
                'renewalUnit'       => \Cx\Modules\Pim\Model\Entity\Product::UNIT_MONTH,
                'renewalQuantifier' => 1,
                'websiteName'       => $websiteName,
                'customer'          => \FWUser::getFWUserObject()->objUser,
            );
            
            $transactionReference = "|$productId|name|$websiteName|";
            $currency = self::getUserCurrency($crmContactId);
            $order = \Env::get('em')->getRepository('Cx\Modules\Order\Model\Entity\Order')->createOrder($productId, $currency, \FWUser::getFWUserObject()->objUser, $transactionReference, $subscriptionOptions);
            if (!$order) {
                return array('status' => 'error', 'message' => $_ARRAYLANG['TXT_CORE_MODULE_MULTISITE_ORDER_FAILED']);
            }
            
            // create the website process in the payComplete event
            $order->complete();
            return array('status' => 'success', 'message' => $_ARRAYLANG['TXT_CORE_MODULE_MULTISITE_ADD_WEBSITE_SUCCESS'], 'reload' => (isset($_GET['page_reload']) && $_GET['page_reload'] == 'reload_page' ? true : false));
        } catch (Exception $e) {
            \DBG::log("Failed to add website:" . $e->getMessage());
            return array('status' => 'error', 'message' => $_ARRAYLANG['TXT_CORE_MODULE_MULTISITE_ADD_WEBSITE_FAILED']);
        }
    }
    
    /**
     * Parse the website details to the view page
     * 
     * @param \Cx\Core\Html\Sigma $objTemplate                         Template object
     * @param \Cx\Core_Modules\MultiSite\Model\Entity\Website $website website object
     */
    public function parseWebsiteDetails(\Cx\Core\Html\Sigma $objTemplate, \Cx\Core_Modules\MultiSite\Model\Entity\Website $website, $demandedStatus='')
    {
        $userId = \FWUser::getFWUserObject()->objUser->getId();

        $websiteInitialStatus = array(
            \Cx\Core_Modules\MultiSite\Model\Entity\Website::STATE_INIT,
            \Cx\Core_Modules\MultiSite\Model\Entity\Website::STATE_SETUP, 
        );
        
        $status = ($website->getStatus() == \Cx\Core_Modules\MultiSite\Model\Entity\Website::STATE_ONLINE);

        if($demandedStatus == '' || $demandedStatus == $website->getStatus()){
            $objTemplate->setVariable(array(
                'MULTISITE_WEBSITE_NAME'          => contrexx_raw2xhtml($website->getName()).self::getWebsiteNonOnlineStateAsLiteral($website),
                'MULTISITE_WEBSITE_ID'            => contrexx_raw2xhtml($website->getId()),
                'MULTISITE_WEBSITE_LINK'          => contrexx_raw2xhtml(self::getApiProtocol() . $website->getBaseDn()->getName()),
                'MULTISITE_WEBSITE_BACKEND_LINK'  => contrexx_raw2xhtml(self::getApiProtocol() . $website->getBaseDn()->getName()) . '/cadmin',
                'MULTISITE_WEBSITE_FRONTEND_LINK' => self::getApiProtocol() . $website->getBaseDn()->getName(),
                'MULTISITE_WEBSITE_STATE_CLASS'   => $status ? 'active' : (in_array($website->getStatus(), $websiteInitialStatus) ? 'init' : 'inactive'),
            ));

            self::showOrHideBlock($objTemplate, 'websiteLinkActive', $status);
            self::showOrHideBlock($objTemplate, 'websiteLinkInactive', !$status);
            self::showOrHideBlock($objTemplate, 'showAdminButton', ($status && $website->getOwnerId() == $userId));
            self::showOrHideBlock($objTemplate, 'showWebsiteLink', $status);
            self::showOrHideBlock($objTemplate, 'showWebsiteName', !$status);
            self::showOrHideBlock($objTemplate, 'showWebsiteViewButton', $status);

            if (in_array($website->getStatus(), $websiteInitialStatus)) {
                self::showOrHideBlock($objTemplate, 'actionButtonsActive', false);
                self::showOrHideBlock($objTemplate, 'websiteInitializing', true);
            }
        }
    }

    /**
     * Return the status of a website as literal
     * I.e. if website::$status is STATE_OFFLINE, it will return ' (offline)'
     *
     * @param   \Cx\Core_Modules\MultiSite\Model\Entity\Website $website
     * @global  array   $_ARRAYLANG
     * @return  string
     */
    public static function getWebsiteNonOnlineStateAsLiteral(\Cx\Core_Modules\MultiSite\Model\Entity\Website $website) {
        global $_ARRAYLANG;

        $status = '';
        switch ($website->getStatus()) {
            case \Cx\Core_Modules\MultiSite\Model\Entity\Website::STATE_OFFLINE:
                $status = ' ('.$_ARRAYLANG['TXT_CORE_MODULE_MULTISITE_OFFLINE'].')';
                break;

            case \Cx\Core_Modules\MultiSite\Model\Entity\Website::STATE_DISABLED:
                $status = ' ('.$_ARRAYLANG['TXT_CORE_MODULE_MULTISITE_DISABLED'].')';
                break;

            default:
                break;
        }

        return $status;
    }

    /**
     * Parse the product details to the view page
     * 
     * @param \Cx\Core\Html\Sigma $objTemplate              Template object
     * @param \Cx\Modules\Pim\Model\Entity\Product $product Product object
     * @param integer $crmContactId crmContactId
     */
    public static function parseProductForAddWebsite(\Cx\Core\Html\Sigma $objTemplate, \Cx\Modules\Pim\Model\Entity\Product $product, $crmContactId = 0)
    {
        $currency = self::getUserCurrency($crmContactId);
        $productPrice = $product->getPaymentAmount(\Cx\Modules\Pim\Model\Entity\Product::UNIT_MONTH, 1, $currency);
        if (\FWValidator::isEmpty($productPrice)) {
            self::showOrHideBlock($objTemplate, 'multisite_pay_button', false);
        }
        $objTemplate->setVariable(array(
            'TXT_MULTISITE_PAYMENT_MODE' => !empty($productPrice) ? true : false,
            'PRODUCT_NOTE_ENTITY'     => $product->getNoteEntity(),
            'PRODUCT_NOTE_RENEWAL'    => $product->getNoteRenewal(),
            'PRODUCT_NOTE_UPGRADE'    => $product->getNoteUpgrade(),
            'PRODUCT_NOTE_EXPIRATION' => $product->getNoteExpiration(),
            'PRODUCT_NOTE_PRICE'      => $product->getNotePrice(),
            'PRODUCT_NAME'            => $product->getName(),
            'PRODUCT_ID'              => $product->getId(),
            'RENEWAL_UNIT'            => isset($_GET['renewalUnit']) ? contrexx_raw2xhtml($_GET['renewalUnit']) : 'monthly',
        ));
    }
    
    /**
     * returns the formatted query string
     * 
     * @param array $params parameters array 
     * 
     * @return string query string
     */
    public static function buildHttpQueryString($params = array())
    {
        $separator   = '';
        $queryString = ''; 
        foreach($params as $key => $value) {
            $queryString .= $separator . $key . '=' . $value; 
            $separator    = '&'; 
        }
        
        return $queryString;
    }

    /**
     * Show or hide the block based on criteria
     * 
     * @param \Cx\Core\Html\Sigma $objTemplate
     * @param string              $blockName
     * @param boolean             $status
     */
    public static function showOrHideBlock(\Cx\Core\Html\Sigma $objTemplate, $blockName, $status = true) {
        if ($objTemplate->blockExists($blockName)) {
            if ($status) {
                $objTemplate->touchBlock($blockName);
            } else {
                $objTemplate->hideBlock($blockName);
            }
        } 
    }
    
    /**
     * Check currently sign-in user
     * 
     * @return boolean
     */
    public static function isUserLoggedIn() {
        global $sessionObj;
        
        if (empty($sessionObj)) {
            $sessionObj = \cmsSession::getInstance();
        }
        
        $objUser = \FWUser::getFWUserObject()->objUser;
        
        return $objUser->login(); 
    }
    

    public static function getHostingController() {
        global $_DBCONFIG;

        \Cx\Core\Setting\Controller\Setting::init('MultiSite', '','FileSystem');
        switch (\Cx\Core\Setting\Controller\Setting::getValue('websiteController')) {
            case 'plesk':
                $hostingController = \Cx\Core_Modules\MultiSite\Controller\PleskController::fromConfig();
                $hostingController->setWebspaceId(\Cx\Core\Setting\Controller\Setting::getValue('pleskWebsitesSubscriptionId'));
                break;

            case 'xampp':
                // initialize XAMPP controller with database of Website Manager/Service Server
                $dbObj = new \Cx\Core\Model\Model\Entity\Db($_DBCONFIG);
                $dbUserObj = new \Cx\Core\Model\Model\Entity\DbUser($_DBCONFIG);
                $hostingController = new \Cx\Core_Modules\MultiSite\Controller\XamppController($dbObj, $dbUserObj); 
                break;

            default:
                throw new WebsiteException('Unknown websiteController set!');    
                break;
        }

        return $hostingController;
    }

    /**
     * Get mail service server hosting controller
     * 
     * @param object \Cx\Core_Modules\MultiSite\Model\Entity\MailServiceServer $mailServiceServer
     * 
     * @return $hostingController
     */
    public static function getMailServerHostingController(\Cx\Core_Modules\MultiSite\Model\Entity\MailServiceServer $mailServiceServer) {
        switch ($mailServiceServer->getType()) {
            case 'plesk':
                $hostingController = new PleskController($mailServiceServer->getHostname(), $mailServiceServer->getAuthUsername() , $mailServiceServer->getAuthPassword(), $mailServiceServer->getApiVersion());
                break;

            case 'xampp':
            default:
                throw new WebsiteException('Unknown MailController set!');    
                break;
        }
        return $hostingController;
    }
    
    /**
     * Fixes database errors.   
     *
     * @return  boolean                 False.  Always.
     * @throws  MultiSiteException
     */
    static function errorHandler()
    {
        global $_CONFIG;
        
        try {
            \Cx\Core\Setting\Controller\Setting::init('MultiSite', '','FileSystem');

            // abort in case the Contrexx installation is in MultiSite website operation mode
            if (\Cx\Core\Setting\Controller\Setting::getValue('mode') == self::MODE_WEBSITE) {
                return false;
            }

            // config group
            \Cx\Core\Setting\Controller\Setting::init('MultiSite', 'config','FileSystem');
            if (\Cx\Core\Setting\Controller\Setting::getValue('mode') === NULL
                && !\Cx\Core\Setting\Controller\Setting::add('mode',self::MODE_NONE, 1,
                \Cx\Core\Setting\Controller\Setting::TYPE_DROPDOWN, self::MODE_NONE.':'.self::MODE_NONE.','.self::MODE_MANAGER.':'.self::MODE_MANAGER.','.self::MODE_SERVICE.':'.self::MODE_SERVICE.','.self::MODE_HYBRID.':'.self::MODE_HYBRID, 'config')){
                    throw new MultiSiteException("Failed to add Setting entry for Database Mode");
            }
            
            // server group
            \Cx\Core\Setting\Controller\Setting::init('MultiSite', 'server','FileSystem');
            if (\Cx\Core\Setting\Controller\Setting::getValue('websiteController') === NULL
                && !\Cx\Core\Setting\Controller\Setting::add('websiteController','xampp', 1,
                \Cx\Core\Setting\Controller\Setting::TYPE_DROPDOWN, 'xampp:XAMPP,plesk:Plesk', 'server')){
                    throw new MultiSiteException("Failed to add Setting entry for Database user website Controller");
            }
            
            // setup group
            \Cx\Core\Setting\Controller\Setting::init('MultiSite', 'setup','FileSystem');
            if (\Cx\Core\Setting\Controller\Setting::getValue('multiSiteProtocol') === NULL
                && !\Cx\Core\Setting\Controller\Setting::add('multiSiteProtocol','mixed', 2,
                \Cx\Core\Setting\Controller\Setting::TYPE_DROPDOWN, 'mixed:Allow insecure (HTTP) and secure (HTTPS) connections,http:Allow only insecure (HTTP) connections,https:Allow only secure (HTTPS) connections', 'setup')){
                    throw new MultiSiteException("Failed to add Setting entry for Multisite Protocol");
            }
            if (\Cx\Core\Setting\Controller\Setting::getValue('multiSiteDomain') === NULL
                && !\Cx\Core\Setting\Controller\Setting::add('multiSiteDomain',$_CONFIG['domainUrl'], 3,
                \Cx\Core\Setting\Controller\Setting::TYPE_TEXT, null, 'setup')){
                    throw new MultiSiteException("Failed to add Setting entry for Database multiSite Domain");
            }
            if (\Cx\Core\Setting\Controller\Setting::getValue('marketingWebsiteDomain') === NULL
                && !\Cx\Core\Setting\Controller\Setting::add('marketingWebsiteDomain',$_CONFIG['domainUrl'], 4,
                \Cx\Core\Setting\Controller\Setting::TYPE_TEXT, null, 'setup')){
                    throw new MultiSiteException("Failed to add Setting entry for Marketing Website Domain");
            }
            if (\Cx\Core\Setting\Controller\Setting::getValue('dashboardNewsSrc') === NULL
                && !\Cx\Core\Setting\Controller\Setting::add('dashboardNewsSrc', 'http://'.$_CONFIG['domainUrl'].'/feed/news_headlines_de.xml', 5,
                \Cx\Core\Setting\Controller\Setting::TYPE_TEXT, null, 'setup')){
                    throw new MultiSiteException("Failed to add Setting entry for dashboardNewsSrc");
            }
// TODO: this should be an existing domain from Cx\Core\Net
            if (\Cx\Core\Setting\Controller\Setting::getValue('customerPanelDomain') === NULL
                && !\Cx\Core\Setting\Controller\Setting::add('customerPanelDomain',$_CONFIG['domainUrl'], 5,
                \Cx\Core\Setting\Controller\Setting::TYPE_TEXT, null, 'setup')){
                    throw new MultiSiteException("Failed to add Setting entry for Customer Panel Domain");
            }
            if (\Cx\Core\Setting\Controller\Setting::getValue('unavailablePrefixes') === NULL
                && !\Cx\Core\Setting\Controller\Setting::add('unavailablePrefixes', 'account,admin,demo,dev,mail,media,my,staging,test,www', 6,
                \Cx\Core\Setting\Controller\Setting::TYPE_TEXTAREA, null, 'setup')){
                    throw new MultiSiteException("Failed to add Setting entry for Unavailable website names");
            }
            if (\Cx\Core\Setting\Controller\Setting::getValue('websiteNameMaxLength') === NULL
                && !\Cx\Core\Setting\Controller\Setting::add('websiteNameMaxLength',80, 7,
                \Cx\Core\Setting\Controller\Setting::TYPE_TEXT, null, 'setup')){
                    throw new MultiSiteException("Failed to add Setting entry for Maximal length of website names");
            }
            if (\Cx\Core\Setting\Controller\Setting::getValue('websiteNameMinLength') === NULL
                && !\Cx\Core\Setting\Controller\Setting::add('websiteNameMinLength',4, 8,
                \Cx\Core\Setting\Controller\Setting::TYPE_TEXT, null, 'setup')){
                    throw new MultiSiteException("Failed to add Setting entry for Minimal length of website names");
            }
            if (\Cx\Core\Setting\Controller\Setting::getValue('sendSetupError') === NULL
                && !\Cx\Core\Setting\Controller\Setting::add('sendSetupError','0', 9,
                \Cx\Core\Setting\Controller\Setting::TYPE_RADIO, '1:Activated,0:Deactivated', 'setup')){
                    throw new MultiSiteException("Failed to add Setting entry for sendSetupError");
            }
            if (\Cx\Core\Setting\Controller\Setting::getValue('termsUrl') === NULL
                && !\Cx\Core\Setting\Controller\Setting::add('termsUrl','[[NODE_AGB]]', 10,
                \Cx\Core\Setting\Controller\Setting::TYPE_TEXT, null, 'setup')){
                    throw new MultiSiteException("Failed to add Setting entry for URL to T&Cs");
            }
            if (\Cx\Core\Setting\Controller\Setting::getValue('createFtpAccountOnSetup') === NULL
                && !\Cx\Core\Setting\Controller\Setting::add('createFtpAccountOnSetup', 0, 11,
                \Cx\Core\Setting\Controller\Setting::TYPE_RADIO, '1:Activated, 0:Deactivated', 'setup')){
                    throw new MultiSiteException("Failed to add Setting entry for Create FTP account during website setup");
            }
            if (\Cx\Core\Setting\Controller\Setting::getValue('passwordSetupMethod') === NULL
                && !\Cx\Core\Setting\Controller\Setting::add('passwordSetupMethod', 'auto', 12,
                \Cx\Core\Setting\Controller\Setting::TYPE_DROPDOWN, 'auto:Automatically,auto-with-verification:Automatically (with email verification),interactive:Interactive', 'setup')){
                    throw new MultiSiteException("Failed to add Setting entry for Password set method during website setup");
            }
            if (\Cx\Core\Setting\Controller\Setting::getValue('autoLogin') === NULL
                && !\Cx\Core\Setting\Controller\Setting::add('autoLogin', '0', 13,
                \Cx\Core\Setting\Controller\Setting::TYPE_RADIO, '1:Activated, 0:Deactivated', 'setup')){
                    throw new MultiSiteException("Failed to add Setting entry for Auto Login during website setup");
            }
            if (\Cx\Core\Setting\Controller\Setting::getValue('ftpAccountFixPrefix') === NULL
                && !\Cx\Core\Setting\Controller\Setting::add('ftpAccountFixPrefix', 'cx', 14,
                \Cx\Core\Setting\Controller\Setting::TYPE_TEXT, null, 'setup')){
                    throw new MultiSiteException("Failed to add Setting entry for ftp account fix prefix during website setup");
            }
            if (\Cx\Core\Setting\Controller\Setting::getValue('forceFtpAccountFixPrefix') === NULL
                && !\Cx\Core\Setting\Controller\Setting::add('forceFtpAccountFixPrefix', 0, 15,
                \Cx\Core\Setting\Controller\Setting::TYPE_RADIO, '1:Activated, 0:Deactivated', 'setup')){
                    throw new MultiSiteException("Failed to add Setting entry for force ftp account fix prefix during website setup");
            }
            if (\Cx\Core\Setting\Controller\Setting::getValue('supportFaqUrl') === NULL
                && !\Cx\Core\Setting\Controller\Setting::add('supportFaqUrl', 'https://www.cloudrexx.com/FAQ', 16,
                \Cx\Core\Setting\Controller\Setting::TYPE_TEXT, null, 'setup')){
                    throw new MultiSiteException("Failed to add Setting entry for support faq url during website setup");
            }
            if (\Cx\Core\Setting\Controller\Setting::getValue('supportRecipientMailAddress') === NULL
                && !\Cx\Core\Setting\Controller\Setting::add('supportRecipientMailAddress', $_CONFIG['coreAdminEmail'], 17,
                \Cx\Core\Setting\Controller\Setting::TYPE_TEXT, null, 'setup')){
                    throw new MultiSiteException("Failed to add Setting entry for support recipient mail address during website setup");
            }
            if (\Cx\Core\Setting\Controller\Setting::getValue('maxLengthFtpAccountName') === NULL
                && !\Cx\Core\Setting\Controller\Setting::add('maxLengthFtpAccountName', 16, 18,
                \Cx\Core\Setting\Controller\Setting::TYPE_TEXT, null, 'setup')){
                    throw new MultiSiteException("Failed to add Setting entry for maximum length for the FTP account name");
            }
            if (\Cx\Core\Setting\Controller\Setting::getValue('payrexxAccount') === NULL
                && !\Cx\Core\Setting\Controller\Setting::add('payrexxAccount', '', 19,
                \Cx\Core\Setting\Controller\Setting::TYPE_TEXT, null, 'setup')){
                    throw new MultiSiteException("Failed to add Setting entry for URL to Payrexx form");
            }
            if (\Cx\Core\Setting\Controller\Setting::getValue('payrexxApiSecret') === NULL
                && !\Cx\Core\Setting\Controller\Setting::add('payrexxApiSecret', '', 21,
                \Cx\Core\Setting\Controller\Setting::TYPE_TEXT, null, 'setup')){
                    throw new MultiSiteException("Failed to add Setting entry for Payrexx API Secret");
            }
            if (\Cx\Core\Setting\Controller\Setting::getValue('domainBlackList') === NULL
                && !\Cx\Core\Setting\Controller\Setting::add('domainBlackList', self::getAllDomainsName(), 22,
                \Cx\Core\Setting\Controller\Setting::TYPE_TEXTAREA, null, 'setup')){
                    throw new MultiSiteException("Failed to add Setting entry for Domain Black list");
            }

            // websiteSetup group
            \Cx\Core\Setting\Controller\Setting::init('MultiSite', 'websiteSetup','FileSystem');
            if (\Cx\Core\Setting\Controller\Setting::getValue('websitePath') === NULL
                && !\Cx\Core\Setting\Controller\Setting::add('websitePath',\Env::get('cx')->getCodeBaseDocumentRootPath().'/websites', 1,
                \Cx\Core\Setting\Controller\Setting::TYPE_TEXT, null, 'websiteSetup')){
                    throw new MultiSiteException("Failed to add Setting entry for websites path");
            }
            if (\Cx\Core\Setting\Controller\Setting::getValue('defaultCodeBase') === NULL
                && !\Cx\Core\Setting\Controller\Setting::add('defaultCodeBase','', 2,
                \Cx\Core\Setting\Controller\Setting::TYPE_TEXT, null, 'websiteSetup')){
                    throw new MultiSiteException("Failed to add SettingDb entry for Database Default code base");
            }
            if (\Cx\Core\Setting\Controller\Setting::getValue('websiteDatabaseHost') === NULL
                && !\Cx\Core\Setting\Controller\Setting::add('websiteDatabaseHost','localhost', 3,
                \Cx\Core\Setting\Controller\Setting::TYPE_TEXT, null, 'websiteSetup')){
                    throw new MultiSiteException("Failed to add Setting entry for website database host");
            }
            if (\Cx\Core\Setting\Controller\Setting::getValue('websiteDatabasePrefix') === NULL
                && !\Cx\Core\Setting\Controller\Setting::add('websiteDatabasePrefix','cloudrexx_', 4,
                \Cx\Core\Setting\Controller\Setting::TYPE_TEXT, null, 'websiteSetup')){
                    throw new MultiSiteException("Failed to add Setting entry for Database prefix for websites");
            }
            if (\Cx\Core\Setting\Controller\Setting::getValue('websiteDatabaseUserPrefix') === NULL
                && !\Cx\Core\Setting\Controller\Setting::add('websiteDatabaseUserPrefix','clx_', 5,
                \Cx\Core\Setting\Controller\Setting::TYPE_TEXT, null, 'websiteSetup')){
                    throw new MultiSiteException("Failed to add Setting entry for Database user prefix for websites");
            }
            if (\Cx\Core\Setting\Controller\Setting::getValue('defaultWebsiteIp') === NULL
                && !\Cx\Core\Setting\Controller\Setting::add('defaultWebsiteIp', $_SERVER['SERVER_ADDR'], 6,
                \Cx\Core\Setting\Controller\Setting::TYPE_TEXT, null, 'websiteSetup')){
                    throw new MultiSiteException("Failed to add Setting entry for Database user plesk IP");
            }
            if (\Cx\Core\Setting\Controller\Setting::getValue('websiteHttpAuthMethod') === NULL
                && !\Cx\Core\Setting\Controller\Setting::add('websiteHttpAuthMethod', '', 8,
                \Cx\Core\Setting\Controller\Setting::TYPE_DROPDOWN, 'none:none, basic:basic, digest:digest', 'websiteSetup')){
                    throw new MultiSiteException("Failed to add Setting entry for HTTP Authentication Method of Website");
            }
            if (\Cx\Core\Setting\Controller\Setting::getValue('websiteHttpAuthUsername') === NULL
                && !\Cx\Core\Setting\Controller\Setting::add('websiteHttpAuthUsername', '', 9,
                \Cx\Core\Setting\Controller\Setting::TYPE_TEXT, null, 'websiteSetup')){
                    throw new MultiSiteException("Failed to add Setting entry for HTTP Authentication Username of Website");
            }
            if (\Cx\Core\Setting\Controller\Setting::getValue('websiteHttpAuthPassword') === NULL
                && !\Cx\Core\Setting\Controller\Setting::add('websiteHttpAuthPassword', '', 10,
                \Cx\Core\Setting\Controller\Setting::TYPE_TEXT, null, 'websiteSetup')){
                    throw new MultiSiteException("Failed to add Setting entry for HTTP Authentication Password of Website");
            }
            if (\Cx\Core\Setting\Controller\Setting::getValue('codeBaseRepository') === NULL
                && !\Cx\Core\Setting\Controller\Setting::add('codeBaseRepository', \Env::get('cx')->getCodeBaseDocumentRootPath() . '/codeBases', 7,
                \Cx\Core\Setting\Controller\Setting::TYPE_TEXT, null, 'websiteSetup')){
                    throw new MultiSiteException("Failed to add Setting Repository for Contrexx Code Bases");
            }
            if (\Cx\Core\Setting\Controller\Setting::getValue('websiteFtpPath') === NULL
                && !\Cx\Core\Setting\Controller\Setting::add('websiteFtpPath', '', 11,
                \Cx\Core\Setting\Controller\Setting::TYPE_TEXT, null, 'websiteSetup')){
                    throw new MultiSiteException("Failed to add Setting Repository for website FTP path");
            }
            if (\Cx\Core\Setting\Controller\Setting::getValue('websiteBackupLocation') === NULL
                && !\Cx\Core\Setting\Controller\Setting::add('websiteBackupLocation', \Env::get('cx')->getCodeBaseDocumentRootPath().'/', 12,
                \Cx\Core\Setting\Controller\Setting::TYPE_TEXT, null, 'websiteSetup')){
                    throw new MultiSiteException("Failed to add Setting Repository for website Backup Location");
            }

            // websiteManager group
            \Cx\Core\Setting\Controller\Setting::init('MultiSite', 'websiteManager','FileSystem');
            if (\Cx\Core\Setting\Controller\Setting::getValue('managerHostname') === NULL
                && !\Cx\Core\Setting\Controller\Setting::add('managerHostname',$_CONFIG['domainUrl'], 1,
                \Cx\Core\Setting\Controller\Setting::TYPE_TEXT, null, 'websiteManager')){
                    throw new MultiSiteException("Failed to add Setting entry for Database Manager Hostname");
            }
            if (\Cx\Core\Setting\Controller\Setting::getValue('managerSecretKey') === NULL
                && !\Cx\Core\Setting\Controller\Setting::add('managerSecretKey','', 2,
                \Cx\Core\Setting\Controller\Setting::TYPE_TEXT, null, 'websiteManager')){
                    throw new MultiSiteException("Failed to add Setting entry for Database Manager Secret Key");
            }
            if (\Cx\Core\Setting\Controller\Setting::getValue('managerInstallationId') === NULL
                && !\Cx\Core\Setting\Controller\Setting::add('managerInstallationId','', 3,
                \Cx\Core\Setting\Controller\Setting::TYPE_TEXT, null, 'websiteManager')){
                    throw new MultiSiteException("Failed to add Setting entry for Database Manager Installation Id");
            }
            if (\Cx\Core\Setting\Controller\Setting::getValue('managerHttpAuthMethod') === NULL
                && !\Cx\Core\Setting\Controller\Setting::add('managerHttpAuthMethod','', 4,
                \Cx\Core\Setting\Controller\Setting::TYPE_DROPDOWN, 'none:none, basic:basic, digest:digest', 'websiteManager')){
                    throw new MultiSiteException("Failed to add Setting entry for Database Manager HTTP Authentication Method");
            }
            if (\Cx\Core\Setting\Controller\Setting::getValue('managerHttpAuthUsername') === NULL
                && !\Cx\Core\Setting\Controller\Setting::add('managerHttpAuthUsername','', 5,
                \Cx\Core\Setting\Controller\Setting::TYPE_TEXT, null, 'websiteManager')){
                    throw new MultiSiteException("Failed to add Setting entry for Database Manager HTTP Authentication Username");
            }
            if (\Cx\Core\Setting\Controller\Setting::getValue('managerHttpAuthPassword') === NULL
                && !\Cx\Core\Setting\Controller\Setting::add('managerHttpAuthPassword','', 6,
                \Cx\Core\Setting\Controller\Setting::TYPE_TEXT, null, 'websiteManager')){
                    throw new MultiSiteException("Failed to add Setting entry for Database Manager HTTP Authentication Password");
            }
            
            // plesk group
            \Cx\Core\Setting\Controller\Setting::init('MultiSite', 'plesk','FileSystem');
            if (\Cx\Core\Setting\Controller\Setting::getValue('pleskHost') === NULL
                && !\Cx\Core\Setting\Controller\Setting::add('pleskHost','localhost', 1,
                \Cx\Core\Setting\Controller\Setting::TYPE_TEXT, null, 'plesk')){
                    throw new MultiSiteException("Failed to add Setting entry for Database user plesk Host");
            }
            if (\Cx\Core\Setting\Controller\Setting::getValue('pleskLogin') === NULL
                && !\Cx\Core\Setting\Controller\Setting::add('pleskLogin','', 2,
                \Cx\Core\Setting\Controller\Setting::TYPE_TEXT, null, 'plesk')){
                    throw new MultiSiteException("Failed to add Setting entry for Database user plesk Login");
            }
            if (\Cx\Core\Setting\Controller\Setting::getValue('pleskPassword') === NULL
                && !\Cx\Core\Setting\Controller\Setting::add('pleskPassword','', 3,
                \Cx\Core\Setting\Controller\Setting::TYPE_PASSWORD,'plesk')){
                    throw new MultiSiteException("Failed to add Setting entry for Database user plesk Password");
            }
            if (\Cx\Core\Setting\Controller\Setting::getValue('pleskApiVersion') === NULL
                && !\Cx\Core\Setting\Controller\Setting::add('pleskApiVersion','', 4,
                \Cx\Core\Setting\Controller\Setting::TYPE_TEXT,'plesk')){
                    throw new MultiSiteException("Failed to add Setting entry for Plesk Api Version");
            }
            if (\Cx\Core\Setting\Controller\Setting::getValue('pleskWebsitesSubscriptionId') === NULL
                && !\Cx\Core\Setting\Controller\Setting::add('pleskWebsitesSubscriptionId',0, 5,
                \Cx\Core\Setting\Controller\Setting::TYPE_TEXT, null, 'plesk')){
                    throw new MultiSiteException("Failed to add Setting entry for Database user plesk Subscription Id");
            }
            if (\Cx\Core\Setting\Controller\Setting::getValue('pleskMasterSubscriptionId') === NULL
                && !\Cx\Core\Setting\Controller\Setting::add('pleskMasterSubscriptionId',0, 6,
                \Cx\Core\Setting\Controller\Setting::TYPE_TEXT, null, 'plesk')){
                    throw new MultiSiteException("Failed to add Setting entry for Database ID of master subscription");
            }
            //manager group
            \Cx\Core\Setting\Controller\Setting::init('MultiSite', 'manager','FileSystem');
            if (!\FWValidator::isEmpty(\Env::get('db'))
                && \Cx\Core\Setting\Controller\Setting::getValue('defaultWebsiteServiceServer') === NULL
                && !\Cx\Core\Setting\Controller\Setting::add('defaultWebsiteServiceServer', self::getDefaultEntityId('Cx\Core_Modules\MultiSite\Model\Entity\WebsiteServiceServer'), 1,
                \Cx\Core\Setting\Controller\Setting::TYPE_DROPDOWN, '{src:\\'.__CLASS__.'::getWebsiteServiceServerList()}', 'manager') ) {
                   throw new MultiSiteException("Failed to add Setting entry for Default Website Service Server");
            }
            if (!\FWValidator::isEmpty(\Env::get('db'))
                && \Cx\Core\Setting\Controller\Setting::getValue('defaultMailServiceServer') === NULL
                && !\Cx\Core\Setting\Controller\Setting::add('defaultMailServiceServer', self::getDefaultEntityId('Cx\Core_Modules\MultiSite\Model\Entity\MailServiceServer'), 2,
                \Cx\Core\Setting\Controller\Setting::TYPE_DROPDOWN, '{src:\\'.__CLASS__.'::getMailServiceServerList()}', 'manager') ) {
                   throw new MultiSiteException("Failed to add Setting entry for Default mail Service Server");
            }
            if (!\FWValidator::isEmpty(\Env::get('db'))
                && \Cx\Core\Setting\Controller\Setting::getValue('defaultWebsiteTemplate') === NULL
                && !\Cx\Core\Setting\Controller\Setting::add('defaultWebsiteTemplate', self::getDefaultEntityId('Cx\Core_Modules\MultiSite\Model\Entity\WebsiteTemplate'), 3,
                \Cx\Core\Setting\Controller\Setting::TYPE_DROPDOWN, '{src:\\'.__CLASS__.'::getWebsiteTemplateList()}', 'manager')) {
                    throw new MultiSiteException("Failed to add Setting entry for default Website Template");
            }
            if (!\FWValidator::isEmpty(\Env::get('db'))
                && \Cx\Core\Setting\Controller\Setting::getValue('defaultPimProduct') === NULL 
                && !\Cx\Core\Setting\Controller\Setting::add('defaultPimProduct', self::getDefaultPimProductId(), 4,
                \Cx\Core\Setting\Controller\Setting::TYPE_DROPDOWN, '{src:\\'.__CLASS__.'::getProductList()}', 'manager') ) {
                   throw new MultiSiteException("Failed to add Setting entry for Product List");
            }
            
            if (!\FWValidator::isEmpty(\Env::get('db'))) {
                $settingExternalPaymentCustomerIdProfileAttributeId = \Cx\Core\Setting\Controller\Setting::getValue('externalPaymentCustomerIdProfileAttributeId');
                $dbExternalPaymentCustomerIdProfileAttributeId      = self::getExternalPaymentCustomerIdProfileAttributeId();
              
                if ($settingExternalPaymentCustomerIdProfileAttributeId != $dbExternalPaymentCustomerIdProfileAttributeId) {
                    \Cx\Core\Setting\Controller\Setting::init('MultiSite', 'manager','FileSystem');
                    if ($settingExternalPaymentCustomerIdProfileAttributeId === null) {
                        if (!\Cx\Core\Setting\Controller\Setting::add('externalPaymentCustomerIdProfileAttributeId', $dbExternalPaymentCustomerIdProfileAttributeId, 5,
                            \Cx\Core\Setting\Controller\Setting::TYPE_TEXT, null, 'manager')) {
                                throw new MultiSiteException("Failed to add Setting entry for External Payment Customer Id Profile Attribute Id");
                        }
                    } else {
                        if (!(\Cx\Core\Setting\Controller\Setting::set('externalPaymentCustomerIdProfileAttributeId', $dbExternalPaymentCustomerIdProfileAttributeId)
                            && \Cx\Core\Setting\Controller\Setting::update('externalPaymentCustomerIdProfileAttributeId'))) {
                            throw new MultiSiteException("Failed to update Setting for External Payment Customer Id Profile Attribute Id");
                        }
                    }
                } 
            }
        } catch (\Exception $e) {
            \DBG::msg($e->getMessage());
        }
        // Always
        return false;
    }

    public function postResolve(\Cx\Core\ContentManager\Model\Entity\Page $page) {
        // Event Listener must be registered before preContentLoad event
        $this->registerEventListener();
    }
    
    /**
     * Register the Event listeners
     */
    public function registerEventListener(){
        // do not register any Event Listeners in case MultiSite mode is not set
        if (!\Cx\Core\Setting\Controller\Setting::getValue('mode')) {
            return;
        }

        global $objInit, $_ARRAYLANG;
        
        $langData = $objInit->loadLanguageData('MultiSite');
        $_ARRAYLANG = array_merge($_ARRAYLANG, $langData);
        
        $evm = \Env::get('cx')->getEvents();
        $evm->addEvent('model/payComplete');
        $evm->addEvent('model/terminated');
        $domainEventListener = new \Cx\Core_Modules\MultiSite\Model\Event\DomainEventListener();
        $evm->addModelListener(\Doctrine\ORM\Events::prePersist, 'Cx\\Core_Modules\\MultiSite\\Model\\Entity\\Domain', $domainEventListener);
        $evm->addModelListener(\Doctrine\ORM\Events::postPersist, 'Cx\\Core_Modules\\MultiSite\\Model\\Entity\\Domain', $domainEventListener);
        $evm->addModelListener(\Doctrine\ORM\Events::postRemove, 'Cx\\Core_Modules\\MultiSite\\Model\\Entity\\Domain', $domainEventListener);
        $evm->addModelListener(\Doctrine\ORM\Events::preUpdate, 'Cx\\Core_Modules\\MultiSite\\Model\\Entity\\Domain', $domainEventListener);

        $evm->addModelListener(\Doctrine\ORM\Events::prePersist, 'Cx\\Core\\Net\\Model\\Entity\\Domain', $domainEventListener);
        $evm->addModelListener(\Doctrine\ORM\Events::postPersist, 'Cx\\Core\\Net\\Model\\Entity\\Domain', $domainEventListener);
        $evm->addModelListener(\Doctrine\ORM\Events::postRemove, 'Cx\\Core\\Net\\Model\\Entity\\Domain', $domainEventListener);
        $evm->addModelListener(\Doctrine\ORM\Events::preUpdate, 'Cx\\Core\\Net\\Model\\Entity\\Domain', $domainEventListener);
        $evm->addModelListener(\Doctrine\ORM\Events::postUpdate, 'Cx\\Core\\Net\\Model\\Entity\\Domain', $domainEventListener);
        
        $websiteEventListener = new \Cx\Core_Modules\MultiSite\Model\Event\WebsiteEventListener();
        $evm->addModelListener(\Doctrine\ORM\Events::preUpdate, 'Cx\\Core_Modules\\MultiSite\\Model\\Entity\\Website', $websiteEventListener);
        $evm->addModelListener(\Doctrine\ORM\Events::postUpdate, 'Cx\\Core_Modules\\MultiSite\\Model\\Entity\\Website', $websiteEventListener);
        $evm->addModelListener(\Doctrine\ORM\Events::preRemove, 'Cx\\Core_Modules\\MultiSite\\Model\\Entity\\Website', $websiteEventListener);
        $evm->addModelListener('payComplete', 'Cx\\Modules\\Order\\Model\\Entity\\Subscription', $websiteEventListener);
        
        //accessUser Event Listenter
        $accessUserEventListener    = new \Cx\Core_Modules\MultiSite\Model\Event\AccessUserEventListener();
        $evm->addModelListener(\Doctrine\ORM\Events::prePersist, 'User', $accessUserEventListener);
        $evm->addModelListener(\Doctrine\ORM\Events::postPersist, 'User', $accessUserEventListener);
        $evm->addModelListener(\Doctrine\ORM\Events::preUpdate, 'User', $accessUserEventListener);
        $evm->addModelListener(\Doctrine\ORM\Events::preRemove, 'User', $accessUserEventListener);
        $evm->addModelListener(\Doctrine\ORM\Events::postUpdate, 'User', $accessUserEventListener);
        $evm->addModelListener(\Doctrine\ORM\Events::prePersist, 'Cx\\Core_Modules\\MultiSite\\Model\\Entity\\User', $accessUserEventListener);
        $evm->addModelListener(\Doctrine\ORM\Events::postPersist, 'Cx\\Core_Modules\\MultiSite\\Model\\Entity\\User', $accessUserEventListener);
        $evm->addModelListener(\Doctrine\ORM\Events::preUpdate, 'Cx\\Core_Modules\\MultiSite\\Model\\Entity\\User', $accessUserEventListener);
        $evm->addModelListener(\Doctrine\ORM\Events::preRemove, 'Cx\\Core_Modules\\MultiSite\\Model\\Entity\\User', $accessUserEventListener);
        $evm->addModelListener(\Doctrine\ORM\Events::postUpdate, 'Cx\\Core_Modules\\MultiSite\\Model\\Entity\\User', $accessUserEventListener);
        
        $cronMailEventListener = new \Cx\Core_Modules\MultiSite\Model\Event\CronMailEventListener();
        $evm->addModelListener(\Doctrine\ORM\Events::prePersist, 'Cx\\Core_Modules\\MultiSite\\Model\\Entity\\CronMail', $cronMailEventListener);
        $evm->addModelListener(\Doctrine\ORM\Events::preUpdate, 'Cx\\Core_Modules\\MultiSite\\Model\\Entity\\CronMail', $cronMailEventListener);
        
        //website Template Event Listener
        $websiteTemplateEventListener = new \Cx\Core_Modules\MultiSite\Model\Event\WebsiteTemplateEventListener();
        $evm->addModelListener(\Doctrine\ORM\Events::postPersist, 'Cx\\Core_Modules\\MultiSite\\Model\\Entity\\WebsiteTemplate', $websiteTemplateEventListener);
        $evm->addModelListener(\Doctrine\ORM\Events::postUpdate, 'Cx\\Core_Modules\\MultiSite\\Model\\Entity\\WebsiteTemplate', $websiteTemplateEventListener);
        
        //ContactForm event Listener
        $contactFormEventListener = new \Cx\Core_Modules\MultiSite\Model\Event\ContactFormEventListener();
        $evm->addModelListener(\Doctrine\ORM\Events::prePersist, 'Cx\\Core_Modules\\Contact\\Model\\Entity\\Form', $contactFormEventListener);
        
        //ShopProduct Event Listener
        $shopProductEventListener = new \Cx\Core_Modules\MultiSite\Model\Event\ShopProductEventListener();
        $evm->addModelListener(\Doctrine\ORM\Events::prePersist, 'Cx\\Modules\\Shop\\Controller\\Product', $shopProductEventListener);
        
        //CrmCustomer event Listener
        $crmCustomerEventListener = new \Cx\Core_Modules\MultiSite\Model\Event\CrmCustomerEventListener();
        $evm->addModelListener(\Doctrine\ORM\Events::prePersist, 'Cx\\Modules\\Crm\\Model\\Entity\\CrmContact', $crmCustomerEventListener);
        
        $websiteCollectionEventListener = new \Cx\Core_Modules\MultiSite\Model\Event\WebsiteCollectionEventListener();
        $evm->addModelListener('terminated', 'Cx\\Modules\\Order\\Model\\Entity\\Subscription', $websiteCollectionEventListener);
        $evm->addModelListener('payComplete', 'Cx\\Modules\\Order\\Model\\Entity\\Subscription', $websiteCollectionEventListener);

        $evm->addModelListener(\Doctrine\ORM\Events::preRemove, 'Cx\\Core_Modules\\MultiSite\\Model\\Entity\\WebsiteCollection', $websiteCollectionEventListener);

        //OrderPayment event Listener
        $orderPaymentEventListener = new \Cx\Core_Modules\MultiSite\Model\Event\OrderPaymentEventListener();
        $evm->addModelListener(\Doctrine\ORM\Events::postPersist, 'Cx\\Modules\\Order\\Model\\Entity\\Payment', $orderPaymentEventListener);
        
        //CoreYamlSetting event Listener
        $coreYamlSettingEventListener = new \Cx\Core_Modules\MultiSite\Model\Event\CoreYamlSettingEventListener();
        $evm->addModelListener(\Doctrine\ORM\Events::postUpdate, 'Cx\\Core\\Setting\\Model\\Entity\\YamlSetting', $coreYamlSettingEventListener);
    }
    
    
    public function preInit(\Cx\Core\Core\Controller\Cx $cx) {
        global $_CONFIG;

        // Abort in case the request has been made to a unsupported cx-mode
        if (!in_array($cx->getMode(), array($cx::MODE_FRONTEND, $cx::MODE_BACKEND, $cx::MODE_COMMAND, $cx::MODE_MINIMAL))) {
            return;
        }

        // Abort in case this Contrexx installation has not been set up as a Website Service.
        // If the MultiSite module has not been configured, then 'mode' will be set to null.
        switch (\Cx\Core\Setting\Controller\Setting::getValue('mode')) {
            case self::MODE_MANAGER:
                $this->verifyRequest($cx);
                break;

            case self::MODE_HYBRID:
            case self::MODE_SERVICE:
                // In case the deployment was successful,
                // we need to exit this method and proceed
                // with the regular bootstrap process.
                // This case is required by the cx-mode MODE_MINIMAL.
                if ($this->deployWebsite($cx)) {
                    return;
                }
                $this->verifyRequest($cx);
                break;

            case self::MODE_WEBSITE:
                $requestCmd = isset($_REQUEST['cmd']) ? $_REQUEST['cmd'] : null;
                // handle MultiSite-API requests
                if (   $cx->getMode() == $cx::MODE_BACKEND
                    && $requestCmd == 'JsonData'
                ) {
                    // Set domainUrl to requeted website's domain alias.
                    // This is required in case optino 'forceDomainUrl' is set.
                    $_CONFIG['domainUrl'] = $_SERVER['HTTP_HOST'];

                    // MultiSite-API requests shall always be by-passed
                    break;
                }

                // deploy website when in online-state and request is a regular http request
                if (\Cx\Core\Setting\Controller\Setting::getValue('websiteState') == \Cx\Core_Modules\MultiSite\Model\Entity\Website::STATE_ONLINE) {
                    break;
                }

// TODO: this offline mode has been caused by the MultiSite Manager -> Therefore, we should not return the Website's custom offline page.
//       Instead we shall show the Cloudrexx offline page
                throw new \Exception('Website is currently not online');
                break;

            default:
                break;
        }
    }

    protected function verifyRequest($cx) {
        $domainRepository = new \Cx\Core\Net\Model\Repository\DomainRepository();
        $managerDomain = $domainRepository->getMainDomain();
        $customerPanelDomainName = \Cx\Core\Setting\Controller\Setting::getValue('customerPanelDomain');
        $marketingWebsiteDomainName = \Cx\Core\Setting\Controller\Setting::getValue('marketingWebsiteDomain');
        $requestedDomainName = $_SERVER['HTTP_HOST'];

        // Allow access to backend only through Manager domain (-> Main Domain).
        // Other requests will be forwarded to the Marketing Website of MultiSite.
        if (   $cx->getMode() == $cx::MODE_BACKEND
            && $requestedDomainName != $managerDomain->getName()
// TODO: This is a workaround as all JsonData-requests sent from the
//       Customer Panel are also being sent to the Manager Domain.
            && $requestedDomainName != $customerPanelDomainName
        ) {
            header('Location: '.$this->getApiProtocol().$marketingWebsiteDomainName, true, 301);
            exit;
        }
        // Allow access to command-mode only through Manager domain (-> Main Domain) and Customer Panel domain
        // Other requests will be forwarded to the Marketing Website of MultiSite.
        if (   $cx->getMode() == $cx::MODE_COMMAND
            && $requestedDomainName != $managerDomain->getName()
            && $requestedDomainName != $customerPanelDomainName
        ) {
            header('Location: '.$this->getApiProtocol().$marketingWebsiteDomainName, true, 301);
            exit;
        }

        // Allow access to frontend only on domain of Marketing Website and Customer Panel.
        // Other requests will be forwarded to the Marketing Website of MultiSite.
        if (   $cx->getMode() == $cx::MODE_FRONTEND
            && !empty($marketingWebsiteDomainName)
            && !empty($customerPanelDomainName)
            && $requestedDomainName != $marketingWebsiteDomainName
            && $requestedDomainName != $customerPanelDomainName
        ) {
            header('Location: '.$this->getApiProtocol().$marketingWebsiteDomainName, true, 301);
            exit;
        }

        // In case the Manager domain has been requested,
        // the user will automatically be redirected to the backend.
        if (   $cx->getMode() == $cx::MODE_FRONTEND
            && $customerPanelDomainName != $managerDomain->getName()
            && $requestedDomainName == $managerDomain->getName()
        ) {
            $backendUrl = \Env::get('cx')->getWebsiteBackendPath();
            header('Location: '.$backendUrl);
            exit;
        }
    }

    protected function deployWebsite(\Cx\Core\Core\Controller\Cx $cx) {
        $multiSiteRepo = new \Cx\Core_Modules\MultiSite\Model\Repository\FileSystemWebsiteRepository();
        $website = $multiSiteRepo->findByDomain(\Cx\Core\Setting\Controller\Setting::getValue('websitePath').'/', $_SERVER['HTTP_HOST']);
        if ($website) {
            // Recheck the system state of the Website Service Server (1st check
            // has already been performed before executing the preInit-Hooks),
            // but this time also lock the backend in case the system has been
            // put into maintenance mode, as a Website must also not be
            // accessable throuth the backend in case its Website Service Server
            // has activated the maintenance-mode.
            $cx->checkSystemState(true);

            $configFile = \Cx\Core\Setting\Controller\Setting::getValue('websitePath').'/'.$website->getName().'/config/configuration.php';
            $requestInfo =    isset($_REQUEST['cmd']) && $_REQUEST['cmd'] == 'JsonData'
                           && isset($_REQUEST['object']) && $_REQUEST['object'] == 'MultiSite'
                           && isset($_REQUEST['act'])
                                ? '(API-call: '.$_REQUEST['act'].')'
                                : '';
            \DBG::msg("MultiSite: Loading customer Website {$website->getName()}...".$requestInfo);
            // set SERVER_NAME to BaseDN of Website
            $_SERVER['SERVER_NAME'] = $website->getName() . '.' . \Cx\Core\Setting\Controller\Setting::getValue('multiSiteDomain');
            \Cx\Core\Core\Controller\Cx::instanciate($cx->getMode(), true, $configFile, true);

            // In cx-mode MODE_MINIMAL we must not abort
            // script execution as the script that initialized
            // the Cx object is most likely going to perform some
            // additional operations after the Cx initialization
            // has finished.
            // To prevent that the bootstrap process of the service
            // server is being proceeded, we must throw an
            // InstanceException here.
            if ($cx->getMode() == $cx::MODE_MINIMAL) {
                throw new \Cx\Core\Core\Controller\InstanceException();
            }
            exit;
        }

        // no website found. Abort website-deployment and let Contrexx process with the regular system initialization (i.e. most likely with the Website Service Website)
        $requestInfo =    isset($_REQUEST['cmd']) && $_REQUEST['cmd'] == 'JsonData'
                       && isset($_REQUEST['object']) && $_REQUEST['object'] == 'MultiSite'
                       && isset($_REQUEST['act'])
                            ? '(API-call: '.$_REQUEST['act'].')'
                            : '';
        \DBG::msg("MultiSite: Loading Website Service...".$requestInfo);
        return false;
    }
    
    /**
     * Get the api protocol url
     * 
     * @return string $protocolUrl
     */
    public static function getApiProtocol() {
        switch (\Cx\Core\Setting\Controller\Setting::getValue('multiSiteProtocol')) {
            case 'http':
                $protocolUrl = 'http://';
                break;
            case 'https':
                $protocolUrl = 'https://';
                break;
            case 'mixed':
// TODO: this is a workaround for Websites, as they are not aware of the related configuration option
            default:
                return empty($_SERVER['HTTPS']) || $_SERVER['HTTPS'] == 'off' ? 'http://' : 'https://';
                break;
        }
        return $protocolUrl;
    }
    
    /**
     * Get the website service servers
     * 
     * @return string serviceServers list
     */
    public static function getWebsiteServiceServerList() {
        $websiteServiceServers = \Env::get('em')->getRepository('Cx\Core_Modules\MultiSite\Model\Entity\WebsiteServiceServer')->findAll();
        $dropdownOptions = array();
        foreach ($websiteServiceServers As $serviceServer) {
            $dropdownOptions[] = $serviceServer->getId() . ':' . $serviceServer->getHostname();
        }
        return implode(',', $dropdownOptions);
    }
    
    /**
     * Get the default entity id
     * 
     * @param string $entityClass entityClass
     * 
     * @return integer id
     */
    public static function getDefaultEntityId($entityClass) 
    {
        if (empty($entityClass)) {
            return;
        }

        $repository = \Env::get('em')->getRepository($entityClass);
        if ($repository) {
            $defaultEntity = $repository->getFirstEntity();
            if ($defaultEntity) {
                return $defaultEntity->getId();
            }
        }
        return 0;
    }

    /**
     * Get the mail service servers
     * 
     * @return string  mail service servers list
     */
    public static function getMailServiceServerList() {
        $mailServiceServers = \Env::get('em')->getRepository('Cx\Core_Modules\MultiSite\Model\Entity\MailServiceServer')->findAll();
        $dropdownOptions = array();
        foreach ($mailServiceServers as $mailServiceServer) {
            $dropdownOptions[] = $mailServiceServer->getId() . ':' .$mailServiceServer->getLabel(). ' ('.$mailServiceServer->getHostname().')';
        }
        return implode(',', $dropdownOptions);
    }
    
    /**
     * Get the module additional data by its type
     * 
     * @param string $moduleName      name of the module
     * @param string $additionalType  additional type of the module additional data
     * @return mixed array | boolean
     */
    public static function getModuleAdditionalDataByType($moduleName = '', $additionalType = 'quota') {
        global $objDatabase;
        
        if (empty($moduleName) || empty($additionalType)) {
            return;
        }
        
        $objResult = $objDatabase->Execute('SELECT `additional_data` FROM ' . DBPREFIX . 'modules WHERE name= "'. contrexx_raw2db($moduleName) .'"');
        if ($objResult !== false) {
            $options = json_decode($objResult->fields['additional_data'], true);
            if (!empty($options)) {
               return $options[$additionalType]; 
            }
        }
        
        return false;
    }
    
    /**
     * Shows the all website templates
     * 
     * @access  private
     * @return  string
     */
    public static function getWebsiteTemplateList() {
        $websiteTemplatesObj = \Env::get('em')->getRepository('Cx\Core_Modules\MultiSite\Model\Entity\WebsiteTemplate');
        $websiteTemplates = $websiteTemplatesObj->findAll();
        $display = array();
        foreach ($websiteTemplates as $websiteTemplate) {
            $display[] = $websiteTemplate->getId() .':'. $websiteTemplate->getName();
        }
        return implode(',', $display);
    }
    
    /**
     * Get the product list
     * 
     * @param string $returntype Type of return value (array | dropDownOption)
     * 
     * @return array products
     */
    public static function getProductList($returntype = 'dropDownOption') 
    {
        $qb = \Env::get('em')->createQueryBuilder();
        $qb->select('p')
                ->from('\Cx\Modules\Pim\Model\Entity\Product', 'p')
                ->where("p.entityClass = 'Cx\Core_Modules\MultiSite\Model\Entity\Website'")
                ->orWhere("p.entityClass = 'Cx\Core_Modules\MultiSite\Model\Entity\WebsiteCollection'")
                ->orderBy('p.id');
        $products =  $qb->getQuery()->getResult();
        
        $response = null;
        switch ($returntype) {
            case 'array':
                $response = $products;
                break;
            case 'dropDownOption':
            default:
                // Get all products to display in the dropdown.
                $productsList = array();
                foreach ($products as $product) {
                    $productsList[] = $product->getId() . ':' . $product->getName();
                }
                $response = implode(',', $productsList);
        }
        
        return $response;        
    }
    
    /**
     * Get default product id
     * 
     * @return int productId
     */
    public static function getDefaultPimProductId()
    {
        $products = self::getProductList('array');
        if (\FWValidator::isEmpty($products)) {
            return 0;
        }
        
        $defaultProduct = current($products);
        if ($defaultProduct) {
            return $defaultProduct->getId();
        }
        return 0;
    }

    /**
     * Get the External Payment Customer Id Profile Attribute Id
     * 
     * @return integer attribute id
     * @throws MultiSiteException
     */
    public static function getExternalPaymentCustomerIdProfileAttributeId() {
        $objUser = \FWUser::getFWUserObject()->objUser;
        
        $externalPaymentCustomerIdProfileAttributeId = \Cx\Core\Setting\Controller\Setting::getValue('externalPaymentCustomerIdProfileAttributeId');
        if ($externalPaymentCustomerIdProfileAttributeId) {
            $objProfileAttribute = $objUser->objAttribute->getById($externalPaymentCustomerIdProfileAttributeId);
            if ($objProfileAttribute->getId() != $externalPaymentCustomerIdProfileAttributeId) {
                $externalPaymentCustomerIdProfileAttributeId = false;
            }
        }
        if (!$externalPaymentCustomerIdProfileAttributeId) {
            $objProfileAttribute = $objUser->objAttribute->getById(0);
            $objProfileAttribute->setNames(array(
                1 => 'MultiSite External Payment Customer ID',
                2 => 'MultiSite External Payment Customer ID'
            ));
            $objProfileAttribute->setType('text');
            $objProfileAttribute->setParent(0);
            if (!$objProfileAttribute->store()) {
                throw new MultiSiteException(
                'Failed to create MultiSite External Payment Customer Id Profile Attribute Id');
            }
            
        }
        return $objProfileAttribute->getId();
    }
    
    /**
     * Used to get all the admin users and backend group users
     * 
     * @return array returns admin users
     */
    public static function getAllAdminUsers()
    {
        // check the mode
        $adminUsers = array();
        switch (\Cx\Core\Setting\Controller\Setting::getValue('mode')) {
            case ComponentController::MODE_WEBSITE:

                $userRepo = \Env::get('em')->getRepository('Cx\Core\User\Model\Entity\User');
                $users = $userRepo->findBy(array('isAdmin' => '1'));

                
                foreach ($users as $user) {
                    $adminUsers[$user->getId()] = $user;
                }

                $groupRepo = \Env::get('em')->getRepository('Cx\Core\User\Model\Entity\Group');
                $groups = $groupRepo->findBy(array('type' => 'backend'));

                foreach ($groups as $group) {
                    foreach ($group->getUser() as $user) {
                        if (!array_key_exists($user->getId(), $adminUsers)) {
                            $adminUsers[$user->getId()] = $user;
                        }
                    }
                }                
                break;
        }
        return $adminUsers;
    }
    
    /**
     * Get the backend group ids
     * 
     * @return array $backendGroupIds
     */
    public static function getBackendGroupIds() {
        $objFWUser       = \FWUser::getFWUserObject();
        $backendGroupIds = array();
        $objGroup = $objFWUser->objGroup->getGroups(array('type' => \Cx\Core\Core\Controller\Cx::MODE_BACKEND));
        if ($objGroup) {
            while (!$objGroup->EOF) {
                $backendGroupIds[] = $objGroup->getId();
                $objGroup->next();
            }
        }
        return $backendGroupIds;
    }
    
    /**
     * Get all the domains name
     * 
     * @return string $domainNames
     */
    public static function getAllDomainsName() {
        $domainRepo = new \Cx\Core\Net\Model\Repository\DomainRepository();
        $domains = $domainRepo->findAll();
        $domainNames = array();
        foreach ($domains as $domain) {
            $domainNames[] = $domain->getName();
        }
        return implode(',', $domainNames);
    }

    /**
     * Parse the message to the json output.
     * 
     * @param  string  $message message 
     * @param  boolean $status  true | false (if status is true returns success json data)
     *                          if status is false returns error message json.
     * @param  boolean $reload  true | false
     * 
     * @return string
     */
    public function parseJsonMessage($message, $status, $reload=false) {
        $json = new \Cx\Core\Json\JsonData();
        if (is_array($message)) {
            $data = $message;
        } else {
            $data['message'] = $message;
        }
        $data['reload'] = $reload;
        
        if ($status) {
            return $json->json(array(
                        'status' => 'success',
                        'data'   => $data
            ));
        }

        if (!$status) {
            return $json->json(array(
                        'status'  => 'error',
                        'message' => $message
            ));
        }
    }
    
    /**
     * Post content load hook.
     *
     * @param \Cx\Core\ContentManager\Model\Entity\Page $page Resolved page
     */
    public function postContentLoad(\Cx\Core\ContentManager\Model\Entity\Page $page)
    {
        self::loadAccountActivationBar();
        self::loadPoweredByFooter();
        self::loadContactInformationForm();
    }
    
    /**
     * Get the account activation bar if user is not verified
     */
    public function loadAccountActivationBar()
    {
        global $_ARRAYLANG;
        
        // only show account-activation-bar if user is signed-in
        if (!\FWUser::getFWUserObject()->objUser->login()) {
            return;
        }

        \Cx\Core\Setting\Controller\Setting::init('MultiSite', '','FileSystem');
        $websiteUserId = \Cx\Core\Setting\Controller\Setting::getValue('websiteUserId');
        if (!$websiteUserId) {
            return;
        }

        $websiteUser = \FWUser::getFWUserObject()->objUser->getUser(\Cx\Core\Setting\Controller\Setting::getValue('websiteUserId'));
        if (!$websiteUser) {
            return;
        }
        
        if ($websiteUser->isVerified()) {
            return;
        }

        JsonMultiSite::loadLanguageData();
        $objTemplate = $this->cx->getTemplate();
        $warning = new \Cx\Core\Html\Sigma($this->cx->getCodeBaseCoreModulePath() . '/MultiSite/View/Template/Backend');
        $warning->loadTemplateFile('AccountActivation.html');

        $dueDate = '<span class="highlight">'.date(ASCMS_DATE_FORMAT_DATE, $websiteUser->getRestoreKeyTime()).'</span>';
        $email = '<span class="highlight">'.contrexx_raw2xhtml($websiteUser->getEmail()).'</span>';
        $reminderMsg = sprintf($_ARRAYLANG['TXT_MULTISITE_ACCOUNT_ACTIVATION_REMINDER'], $email, $dueDate);

        $warning->setVariable(array(
            'MULTISITE_ACCOUNT_ACTIVATION_REMINDER_MSG' => $reminderMsg,
            'TXT_MULTISITE_RESEND_ACTIVATION_CODE'      => $_ARRAYLANG['TXT_MULTISITE_RESEND_ACTIVATION_CODE'],
        ));

        \JS::registerJS('core_modules/MultiSite/View/Script/AccountActivation.js');

        if ($this->cx->getMode() == \Cx\Core\Core\Controller\Cx::MODE_BACKEND) {
            \JS::registerCSS('core_modules/MultiSite/View/Style/AccountActivationBackend.css');
            $objTemplate->_blocks['__global__'] = preg_replace('/<div id="container"[^>]*>/', '\\0' . $warning->get(), $objTemplate->_blocks['__global__']);
        } else {
            \JS::registerCSS('core_modules/MultiSite/View/Style/AccountActivationFrontend.css');
            $objTemplate->_blocks['__global__'] = preg_replace('/<body[^>]*>/', '\\0' . $warning->get(), $objTemplate->_blocks['__global__']);
        }
    }
    
    /**
     * Get the powered by footer content.
     */
    public function loadPoweredByFooter()
    {
        global $_ARRAYLANG;
        
        if (!($this->cx->getMode() == \Cx\Core\Core\Controller\Cx::MODE_FRONTEND)) {
            return;
        }
        
        $loadPoweredFooter = self::getModuleAdditionalDataByType('MultiSite', 'poweredbyfooter');
        
        if (empty($loadPoweredFooter)) {
            return;
        }
        
        if (isset($loadPoweredFooter['show']) && $loadPoweredFooter['show']) {
            $marketingWebsiteDomainName = isset($loadPoweredFooter['marketingWebsiteDomain']) ? $loadPoweredFooter['marketingWebsiteDomain'] : '';
            if (empty($marketingWebsiteDomainName)) {
                return;
            }
            
            $objTemplate = $this->cx->getTemplate();
            $footer = new \Cx\Core\Html\Sigma($this->cx->getCodeBaseCoreModulePath() . '/MultiSite/View/Template/Backend');
            $footer->loadTemplateFile('Footer.html');
            $footer->setVariable(array(
                'MULTISITE_POWERED_BY_FOOTER_LINK' => $marketingWebsiteDomainName,
                'MULTISITE_POWERED_BY_IMG_SRC'     => $this->cx->getCodeBaseCoreWebPath() .'/Core/View/Media/login_contrexx_logo.png',
                'TXT_MULTISITE_POWERED_BY_FOOTER'  => $_ARRAYLANG['TXT_MULTISITE_POWERED_BY_FOOTER'],
            ));

            \JS::registerCSS('core_modules/MultiSite/View/Style/PoweredByFooterFrontend.css');                
            $objTemplate->_blocks['__global__'] = preg_replace(array('/<body>/', '/<\/body>/'), array('\\0' . '<div id="preview-content">', $footer->get() .'</div>' . '\\0' ), $objTemplate->_blocks['__global__']);
        }
        
    }
    
    /**
     * load the contact information form 
     * 
     * @global array $_ARRAYLANG
     * @return null
     */
    
    public function loadContactInformationForm()
    {
       global $_ARRAYLANG;

        //check the mode
        if ($this->cx->getMode() !== \Cx\Core\Core\Controller\Cx::MODE_FRONTEND) {
            return;
        }

        switch (\Cx\Core\Setting\Controller\Setting::getValue('mode')) {
            case self::MODE_HYBRID:
            case self::MODE_MANAGER:
                // only show modal contact information modal if user is signed-in
                $objUser = \FWUser::getFWUserObject()->objUser;
                if (!$objUser->login()) {
                    return;
                }

                $profileAttributes = array(
                                        'firstname',
                                        'lastname',
                                        'address',
                                        'zip',
                                        'city',
                                        'country'
                                    );
                $userData = array();

                foreach ($profileAttributes as $profileAttribute) {
                    $userData[$profileAttribute] = !\FWValidator::isEmpty($objUser->getProfileAttribute($profileAttribute)) ? $objUser->getProfileAttribute($profileAttribute) : '';
                }

                if (count($userData) === count(array_filter($userData))) {
                    return;
                }

                $objTemplate = $this->cx->getTemplate();
                $objContactTpl = new \Cx\Core\Html\Sigma($this->cx->getCodeBaseCoreModulePath() . '/MultiSite/View/Template/Backend');
                $objContactTpl->loadTemplateFile('ContactInformation.html');

                $blockName = 'multisite_user';
                $placeholderPrefix = strtoupper($blockName) . '_';
                $objAccessLib = new \Cx\Core_Modules\Access\Controller\AccessLib($objContactTpl);
                $objAccessLib->setModulePrefix($placeholderPrefix);
                $objAccessLib->setAttributeNamePrefix($blockName . '_profile_attribute');
                $objAccessLib->setAccountAttributeNamePrefix($blockName . '_account_');

                $objUser->objAttribute->first();
                while (!$objUser->objAttribute->EOF) {
                    $objAttribute = $objUser->objAttribute->getById($objUser->objAttribute->getId());
                    $objAccessLib->parseAttribute($objUser, $objAttribute->getId(), 0, true, false, false, false, false);
                    $objUser->objAttribute->next();
                }
                $objAccessLib->parseAccountAttributes($objUser);
                $objContactTpl->setVariable(array(
                    'TXT_CORE_MODULE_MULTISITE_CONTACT_INFO_TITTLE' => $_ARRAYLANG['TXT_CORE_MODULE_MULTISITE_CONTACT_INFO_TITTLE'],
                    'TXT_CORE_MODULE_MULTISITE_CONTACT_INFO_CONTENT' => $_ARRAYLANG['TXT_CORE_MODULE_MULTISITE_CONTACT_INFO_CONTENT'],
                    'TXT_CORE_MODULE_MULTISITE_SAVE' => $_ARRAYLANG['TXT_CORE_MODULE_MULTISITE_SAVE'],
                    'MULTISITE_CONTACT_INFO_SUBMIT_URL' => \Env::get('cx')->getWebsiteBackendPath() . '/index.php?cmd=JsonData&object=MultiSite&act=updateOwnUser',
                ));
                $objTemplate->_blocks['__global__'] = preg_replace('/<\/body>/', $objContactTpl->get() . '\\0', $objTemplate->_blocks['__global__']);
                break;

            default:
                break;
        }
    }

    
    /**
     * Get User Currency Object
     * 
     * @param type $crmContactId crmContactId
     * 
     * @return mixed  \Cx\Modules\Crm\Model\Entity\Currency or null
     */
    public static function getUserCurrency($crmContactId = 0)
    {
        $crmCurrencyId = 0;
        
        if (!\FWValidator::isEmpty($crmContactId)) {
            $crmCurrencyId = \Cx\Modules\Crm\Controller\CrmLibrary::getCurrencyIdByCrmId($crmContactId);
        }
        
        $currencyId = !\FWValidator::isEmpty($crmCurrencyId)
                       ? $crmCurrencyId
                       : \Cx\Modules\Crm\Controller\CrmLibrary::getDefaultCurrencyId();
        
        if (\FWValidator::isEmpty($currencyId)) {
            return null;
        }
        
        $currency = \Env::get('em')->getRepository('Cx\Modules\Crm\Model\Entity\Currency')->findOneById($currencyId);
        return $currency;
    }

    public function preFinalize(\Cx\Core\Html\Sigma $template) {
        global $_ARRAYLANG;

        \Env::get('init')->loadLanguageData('MultiSite');

        $this->cx->getTemplate()->setVariable(
            array(
                'MULTISITE_AGB_URL' => \Cx\Core\Setting\Controller\Setting::getValue('termsUrl'),
                'TXT_MULTISITE_ACCEPT_TERMS_URL_NAME' => $_ARRAYLANG['TXT_MULTISITE_ACCEPT_TERMS_URL_NAME'],
            )
        );
    }
}
