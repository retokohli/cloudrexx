<?php
/**
 * Class ComponentController
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
        global $objInit, $_ARRAYLANG;

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
                        
                    case 'Payrexx':
                        $this->executeCommandPayrexx();
                        break;
                    
                    case 'Backup':
                        $this->executeCommandBackup($arguments);
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
        ));
        $productId = !empty($arguments['product-id']) ? $arguments['product-id'] : \Cx\Core\Setting\Controller\Setting::getValue('defaultPimProduct');
        if (!empty($productId)) {
            $productRepository = \Env::get('em')->getRepository('Cx\Modules\Pim\Model\Entity\Product');
            $product = $productRepository->findOneBy(array('id' => $productId));
            $productPrice = $product->getPrice();
            if (!empty($productPrice)) {
                $additionalParameters = array(
                    'invoice_amount'    => $productPrice,
                    'invoice_currency'  => 'CHF',
                    'invoice_number'    =>  $product->getName(),
                    'contact_email'     => '',
                    'referenceId'       => ''
                );
                $i = 1;
                $params = '';
                foreach ($additionalParameters as $key => $val) {
                    $params .= $key . '=' . $val . ($i != count($additionalParameters) ? '&' : '');
                    $i++;
                }
                $objTemplate->setVariable(array(
                    'MULTISITE_OPTION_PAYREXXFORMURL' => contrexx_raw2xhtml('https://'.\Cx\Core\Setting\Controller\Setting::getValue('payrexxAccount').'.payrexx.com/pay?tid=' . \Cx\Core\Setting\Controller\Setting::getValue('payrexxFormId') . '&appview=1&'.$params),
                ));
            }
            $objTemplate->setVariable(array(
                'TXT_MULTISITE_PAYMENT_MODE' => !empty($productPrice) ? true : false,
                'PRODUCT_NOTE_ENTITY'     => $product->getNoteEntity(),
                'PRODUCT_NOTE_RENEWAL'    => $product->getNoteRenewal(),
                'PRODUCT_NOTE_UPGRADE'    => $product->getNoteUpgrade(),
                'PRODUCT_NOTE_EXPIRATION' => $product->getNoteExpiration(),
                'PRODUCT_NOTE_PRICE'      => $product->getNotePrice(),
                'PRODUCT_NAME'            => $product->getName(),
                'PRODUCT_ID'              => $product->getId()
            ));
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
        global $objInit, $_ARRAYLANG;
        
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
        global $objInit, $_CORELANG;
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
            $objAccessLib->parseAttribute($objUser, $objAttribute->getId(), 0, $arguments[2] == 'Edit' ? true : false, false, false, false, false);
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
        $excludeProduct = isset($arguments['exclude_product']) ? array_map('contrexx_input2raw', $arguments['exclude_product']) : '';
        $includeProduct = isset($arguments['include_product']) ? array_map('contrexx_input2raw', $arguments['include_product']) : '';
        //Get the orders based on CRM contact id and get params
        $orderRepo = \Env::get('em')->getRepository('Cx\Modules\Order\Model\Entity\Order');
        $orders    = $orderRepo->getOrdersByCriteria($crmContactId, $status, $excludeProduct, $includeProduct);

        //parse the Site Details
        if (!empty($orders)) {            
            foreach ($orders as $order) {
                foreach ($order->getSubscriptions() as $subscription) {                                    
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
                                    self::parseWebsiteDetails($objTemplate, $website);
                                    $objTemplate->parse('showWebsites');
                                }
                                self::showOrHideBlock($objTemplate, 'showAddWebsiteButton', ($websiteCollection->getQuota() < count($websiteCollection->getWebsites())));
                            } elseif ($websiteCollection instanceof \Cx\Core_Modules\MultiSite\Model\Entity\Website) {
                                self::parseWebsiteDetails($objTemplate, $websiteCollection);
                                $objTemplate->parse('showWebsites');
                            }
                        }
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
        
        $websiteId = isset($arguments['id']) ? $arguments['id'] : 0;
        $subscriptionId = isset($arguments['subscriptionId']) ? $arguments['subscriptionId'] : 0;
        $domainRepository = new \Cx\Core\Net\Model\Repository\DomainRepository();
        $mainDomain = $domainRepository->getMainDomain()->getName();
        $subscriptionUrl = \Cx\Core\Routing\Url::fromMagic(ASCMS_PROTOCOL . '://' . $mainDomain . \Env::get('cx')->getBackendFolderName() . '/index.php?cmd=JsonData&object=MultiSite&act=upgradeSubscription');
        
        $objTemplate->setGlobalVariable($_ARRAYLANG);
        
        if (!\FWValidator::isEmpty($subscriptionId)) {
            $subscription = \Env::get('em')->getRepository('Cx\Modules\Order\Model\Entity\Subscription')->findOneBy(array('id' => $subscriptionId));
            
            if (!$subscription) {
                return $_ARRAYLANG['TXT_MULTISITE_WEBSITE_SUBSCRIPTION_NOT_EXISTS'];                
            }
            if ($subscription->getProductEntity() instanceof \Cx\Core_Modules\MultiSite\Model\Entity\Website) {
                // if the user clicked on button "Abo wählen" from trial subscription means list all products
                $products = \Env::get('em')->getRepository('Cx\Modules\Pim\Model\Entity\Product')->findBy(array('entityClass' => 'Cx\Core_Modules\MultiSite\Model\Entity\WebsiteCollection'));
            } else {
                $product = $subscription->getProduct();
                if (!$product) {
                    return $_ARRAYLANG['TXT_MULTISITE_WEBSITE_PRODUCT_NOT_EXISTS'];
                }
                $products = $product->getUpgrades();
            }
            if (\FWValidator::isEmpty($products)) {
                return $_ARRAYLANG['TXT_MULTISITE_WEBSITE_PRODUCT_NOT_EXISTS'];
            }

            if (!\FWValidator::isEmpty($websiteId)) {
                $websiteServiceRepo = \Env::get('em')->getRepository('Cx\Core_Modules\MultiSite\Model\Entity\Website');
                $website = $websiteServiceRepo->findOneById($websiteId);
                $websiteName = $website ? $website->getName() : '';
            }

            $conatctEmail = (self::isUserLoggedIn()) ? \FWUser::getFWUserObject()->objUser->getEmail() : '';
            foreach ($products as $product) {
                $productName = contrexx_raw2xhtml($product->getName());
                $productPrice = $product->getPrice();
                if ($productPrice > 0) {
                    $additionalParameters = array(
                        'invoice_number' => $productName . ' - ' . $websiteName . '.' . \Cx\Core\Setting\Controller\Setting::getValue('multiSiteDomain'),
                        'contact_email' => $conatctEmail,
                        'invoice_amount' => $productPrice,
                        'invoice_currency' => 'CHF',
                        'referenceId' => $product->getId() . '-' . $websiteName
                    );
                    $i = 1;
                    $params = '';
                    foreach ($additionalParameters as $key => $val) {
                        $params .= $key . '=' . $val . ($i != count($additionalParameters) ? '&' : '');
                        $i++;
                    }
                    $objTemplate->setVariable('MULTISITE_OPTION_PAYREXXFORMURL' , contrexx_raw2xhtml('https://'.\Cx\Core\Setting\Controller\Setting::getValue('payrexxAccount').'.payrexx.com/pay?tid=' . \Cx\Core\Setting\Controller\Setting::getValue('payrexxFormId') . '&appview=1&'.$params));                   
                }
                $objTemplate->setVariable(array(
                    'MULTISITE_WEBSITE_PRODUCT_NAME' => $productName,
                    'MULTISITE_WEBSITE_PRODUCT_ATTRIBUTE_ID' => lcfirst($productName),
                    'MULTISITE_WEBSITE_PRODUCT_PRICE_MONTHLY' => $productPrice,
                    'MULTISITE_WEBSITE_PRODUCT_PRICE_ANNUALLY' => $productPrice * 12,
                    'MULTISITE_WEBSITE_PRODUCT_PRICE_BIANNUALLY' => $productPrice * 24 * 0.9,
                    'MULTISITE_WEBSITE_PRODUCT_NOTE_PRICE' => $product->getNotePrice(),
                    'MULTISITE_WEBSITE_PRODUCT_ID' => $product->getId()
                ));
                $objTemplate->parse('showProduct');
            }
            $objTemplate->setVariable( array(
                'MULTISITE_SUBSCRIPTION_SELECTION_URL' => $subscriptionUrl,
                'MULTISITE_SUBSCRIPTION_ID'            => $subscriptionId
            ));
            return $objTemplate->get();
        }
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

        $product = $subscriptionObj->getProduct();

        if (!$product) {
            return $_ARRAYLANG['TXT_MULTISITE_WEBSITE_PRODUCT_NOT_EXISTS'];
        }

        $objTemplate->setVariable(array(
            'MULTISITE_SUBSCRIPTION_ID'      => contrexx_raw2xhtml($subscriptionObj->getId()),
            'MULTISITE_WEBSITE_PRODUCT_NAME' => contrexx_raw2xhtml($product->getName()),
            'MULTISITE_WEBSITE_SUBSCRIPTION_DATE' => $subscriptionObj->getSubscriptionDate() ? contrexx_raw2xhtml($subscriptionObj->getSubscriptionDate()->format('d.m.Y')) : '',
            'MULTISITE_WEBSITE_SUBSCRIPTION_EXPIRATIONDATE' => $subscriptionObj->getExpirationDate() ? contrexx_raw2xhtml($subscriptionObj->getExpirationDate()->format('d.m.Y')) : '',
        ));

        self::showOrHideBlock($objTemplate, 'showUpgradeButton', $product->isUpgradable());

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
                self::showOrHideBlock($objTemplate, 'showAddWebsiteButton', ($websiteCollection->getQuota() < count($websiteCollection->getWebsites())));
            } elseif ($websiteCollection instanceof \Cx\Core_Modules\MultiSite\Model\Entity\Website) {
                self::parseWebsiteDetails($objTemplate, $websiteCollection);
                $objTemplate->parse('showWebsites');
            }
        }
        
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

        if (!self::isUserLoggedIn()) {
            return $_ARRAYLANG['TXT_MULTISITE_WEBSITE_LOGIN_NOACCESS'];
        }

        if (empty($subscriptionId)) {
            return $_ARRAYLANG['TXT_MULTISITE_WEBSITE_SUBSCRIPTIONID_EMPTY'];
        }

        if (isset($arguments['addWebsite'])) {
            $websiteName = isset($_POST['multisite_address']) ? contrexx_input2raw($_POST['multisite_address']) : '';
            $json = new \Cx\Core\Json\JsonData();

            $subscriptionRepo = \Env::get('em')->getRepository('Cx\Modules\Order\Model\Entity\Subscription');
            $subscriptionObj = $subscriptionRepo->findOneBy(array('id' => $subscriptionId));

            //check the subscription is exist
            if (!$subscriptionObj) {
                return $json->json(array(
                        'status' => 'error',
                        'message' => $_ARRAYLANG['TXT_MULTISITE_WEBSITE_SUBSCRIPTION_NOT_EXISTS']
                ));
            }

            //get website collections
            $websiteCollection = $subscriptionObj->getProductEntity();
            if ($websiteCollection instanceof \Cx\Core_Modules\MultiSite\Model\Entity\WebsiteCollection) {
                if ($websiteCollection->getQuota() <= count($websiteCollection->getWebsites())) {
                    return $json->json(array(
                            'status' => 'error',
                            'message' => sprintf($_ARRAYLANG['TXT_CORE_MODULE_MULTISITE_MAXIMUM_QUOTA_REACHED'], $websiteCollection->getQuota())
                    ));
                }
                //create new website object and add to website
                $website = \Env::get('em')->getRepository('Cx\Core_Modules\MultiSite\Model\Entity\Website')->initWebsite($websiteName, \FWUser::getFWUserObject()->objUser);
                $websiteCollection->addWebsite($website);
                //website setup process
                $websiteStatus = $website->setup();
                return $json->json(array(
                        'status' => 'success',
                        'message' => $websiteStatus
                ));
            }
            die();
        } else {
            $domainRepository = new \Cx\Core\Net\Model\Repository\DomainRepository();
            $mainDomain = $domainRepository->getMainDomain()->getName();
            $addressUrl = \Cx\Core\Routing\Url::fromMagic(ASCMS_PROTOCOL . '://' . $mainDomain . \Env::get('cx')->getBackendFolderName() . '/index.php?cmd=JsonData&object=MultiSite&act=address');

            $objTemplate->setVariable(array(                
                'MULTISITE_DOMAIN'             => \Cx\Core\Setting\Controller\Setting::getValue('multiSiteDomain'),
                'MULTISITE_ADDRESS_URL'        => $addressUrl->toString(),
                'MULTISITE_ADD_WEBSITE_URL'    => '/api/MultiSite/SubscriptionAddWebsite?addWebsite=1&id=' . $subscriptionId,
                'TXT_MULTISITE_CREATE_WEBSITE' => $_ARRAYLANG['TXT_MULTISITE_SUBMIT_BUTTON'],
            ));

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
    public function executeCommandWebsite($objTemplate, $arguments) {
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
        $objTemplate->setVariable('MULTISITE_WEBSITE_FRONTEND_LINK', $this->getApiProtocol() . $website->getBaseDn()->getName());
        self::showOrHideBlock($objTemplate, 'showWebsiteViewButton', $status);
        self::showOrHideBlock($objTemplate, 'showAdminButton', $status);

        //Show the Website Admin and Backend group users
        if ($objTemplate->blockExists('showWebsiteAdminUsers')) {
            $websiteAdminUsers = $website->getAdminUsers();
            foreach ($websiteAdminUsers as $websiteAdminUser) {
                $objTemplate->setVariable(array(
                    'MULTISITE_WEBSITE_USER_NAME' => \FWUser::getParsedUserTitle($websiteAdminUser->id),
                    'MULTISITE_WEBSITE_USER_EMAIL' => $websiteAdminUser->email,
                ));
                $objTemplate->parse('showWebsiteAdminUsers');
            }
        }

        //Show the Website Domain Alias name
        if ($objTemplate->blockExists('showWebsiteDomainAliases')) {
            $websiteDomainAliases = $website->getDomainAliases();
            foreach ($websiteDomainAliases as $domainAlias) {
                $objTemplate->setVariable(array(
                    'MULTISITE_WEBSITE_DOMAIN_ALIAS' => contrexx_raw2xhtml($domainAlias->getName()),
                ));
                $objTemplate->parse('showWebsiteDomainAliases');
            }
            self::showOrHideBlock($objTemplate, 'showWebsiteDomainAliasFound', !empty($websiteDomainAliases));
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

        return $objTemplate->get();
    }
    
    /**
     * Api Payrexx command
     */
    public function executeCommandPayrexx() 
    {
        $transaction = !empty($_POST['transaction']) ? $_POST['transaction'] : array();
        $subscription = !empty($_POST['subscription']) ? $_POST['subscription'] : array();
        if (!empty($transaction) && isset($transaction['status']) && $transaction['status'] === 'confirmed') {
            $invoice = $transaction['invoice'];
            $referenceId = !empty($subscription['id']) ? $invoice['referenceId'] . '-' . $subscription['id'] : $invoice['referenceId'];
            $payment = new \Cx\Modules\Order\Model\Entity\Payment();
            $payment->setAmount($invoice['amount']);
            $payment->setHandler(\Cx\Modules\Order\Model\Entity\Payment::HANDLER_PAYREXX);
            $payment->setTransactionReference($referenceId);
            \Env::get('em')->persist($payment);
            \Env::get('em')->flush();
        }
//        $transaction   = !empty($_POST['transaction']) ? $_POST['transaction'] : array();
//        $instanceName  = \Cx\Core\Setting\Controller\Setting::getValue('payrexxAccount');
//        $apiSecret     = \Cx\Core\Setting\Controller\Setting::getValue('payrexxApiSecret');
//        $paymentRequestId = $_POST['transaction']['invoice']['paymentRequestId'];
//
//        $payrexx = new \Payrexx\Payrexx($instanceName, $apiSecret);
//
//        $paymentRequest = new \Payrexx\Models\Request\PaymentRequest();
//        $paymentRequest->setId($paymentRequestId);
//
//        try {
//            $response = $payrexx->getOne($paymentRequest);
//        } catch (\Payrexx\PayrexxException $e) {
//            throw new MultiSiteException("Failed to get payment response:". $e->getMessage());
//        }
//
//        if (!empty($transaction) && isset($transaction['status']) && ($transaction['status'] === 'confirmed')
//                && !empty($response) && isset($response['status']) && ($response['status'] === 'success')
//                && $transaction['invoice']['amount'] === $response['invoice']['amount']
//                && $transaction['invoice']['referenceId'] === $response['invoice']['referenceId']) {
//            $invoice = $transaction['invoice'];
//            $payment = new \Cx\Modules\Order\Model\Entity\Payment();
//            $payment->setAmount($invoice['amount']);
//            $payment->setHandler(\Cx\Modules\Order\Model\Entity\Payment::HANDLER_PAYREXX);
//            $payment->setTransactionReference($invoice['referenceId']);
//            \Env::get('em')->persist($payment);
//            \Env::get('em')->flush();
//        }
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

                if (!$website) {
                    return false;
                }

                $websiteServiceServer = $website->getWebsiteServiceServer();
                if ($websiteServiceServer) {
                    $params = array(
                        'websiteId' => $websiteId,
                        'websiteName' => $website->getName(),
                        'backupLocation' => $backupLocation
                    );
                    $resp = \Cx\Core_Modules\MultiSite\Controller\JsonMultiSite::executeCommandOnServiceServer('websiteBackup', $params, $websiteServiceServer);
                    if ($resp->status == 'success' && $resp->data->status = 'success') {
                        return array('status' => 'success', 'message' => $resp->data->message);
                    }
                    return array('status' => 'error', 'message' => $resp->data->message);
                }
                $this->cx->getEvents()->triggerEvent(
                        'SysLog/Add', array(
                            'severity' => 'WARNING',
                            'message' => 'This website doesnot exists in the service server',
                            'data' => ' ',
                ));
            }
        } catch (\Exception $e) {
            throw new MultiSiteException("Failed to backup the website:" . $e->getMessage());
        }
    }
    
    /**
     * Parse the website details to the view page
     * 
     * @param \Cx\Core\Html\Sigma $objTemplate                         Template object
     * @param \Cx\Core_Modules\MultiSite\Model\Entity\Website $website website object
     */
    public function parseWebsiteDetails(\Cx\Core\Html\Sigma $objTemplate, \Cx\Core_Modules\MultiSite\Model\Entity\Website $website)
    {
        $userId = \FWUser::getFWUserObject()->objUser->getId();
        
        $status = ($website->getStatus() == \Cx\Core_Modules\MultiSite\Model\Entity\Website::STATE_ONLINE);
        $objTemplate->setVariable(array(
            'MULTISITE_WEBSITE_NAME'          => contrexx_raw2xhtml($website->getName()),
            'MULTISITE_WEBSITE_ID'            => contrexx_raw2xhtml($website->getId()),
            'MULTISITE_WEBSITE_LINK'          => contrexx_raw2xhtml(self::getApiProtocol() . $website->getBaseDn()->getName()),
            'MULTISITE_WEBSITE_BACKEND_LINK'  => contrexx_raw2xhtml(self::getApiProtocol() . $website->getBaseDn()->getName()) . '/cadmin',
            'MULTISITE_WEBSITE_FRONTEND_LINK' => self::getApiProtocol() . $website->getBaseDn()->getName(),
            'MULTISITE_WEBSITE_STATE_CLASS'   => $status ? 'active' : 'inactive',
        ));
        
        self::showOrHideBlock($objTemplate, 'websiteLinkActive', $status);
        self::showOrHideBlock($objTemplate, 'websiteLinkInactive', !$status);
        self::showOrHideBlock($objTemplate, 'showAdminButton', ($status && $website->getOwnerId() == $userId));
        self::showOrHideBlock($objTemplate, 'showWebsiteLink', $status);
        self::showOrHideBlock($objTemplate, 'showWebsiteName', !$status);
        self::showOrHideBlock($objTemplate, 'showWebsiteViewButton', $status);                
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
    
    /**
     * @param array $params the parameters
     */
    public function sendMails($params) {
// TODO: refactor whole method
//       -> cronjob might be running on Website Manager Server
//       -> there we have all information about the websites in the repository
//       no need for strange methods like $website->getDefaultLanguageId()
throw new MultiSiteException('Refactor this method!');

        if ($_SERVER['SERVER_ADDR'] != $_SERVER['REMOTE_ADDR']) {
            exit;
        }
        $get = $params['get'];
        $daysInPast = intval($get['days']);
        if (!in_array($daysInPast, $this->reminders)) {
            throw new MultiSiteException("The day " . $daysInPast . " is not possible");
        }
        $instRepo = new \Cx\Core_Modules\MultiSite\Model\Repository\WebsiteRepository();

        $mktime = strtotime('-' . $daysInPast . 'days');
        $start = strtotime(date('Y-m-d 00:00:00', $mktime));
        $end = strtotime(date('Y-m-d 23:59:59', $mktime));

        $websites = $instRepo->findByCreatedDateRange($this->websitePath, $start, $end);

        \MailTemplate::init('MultiSite');
        foreach ($websites as $website) {
            if (!\MailTemplate::send(array(
                'lang_id' => $website->getOwner()->getBackendLanguage(),
                'section' => 'MultiSite',
                'key' => 'reminder' . $daysInPast . 'days',
                'to' => $website->getMail(),
                'search' => array(),
                'replace' => array(),
            ))) {
                throw new MultiSiteException('Could not send reminder to ' . $website->getMail() . ' (Mail send failed)');
            }
        }
        return true;
    }

    /**
     * The user lost the password
     *
     * @param array $params the parameters of post and get array
     * @return bool
     * @throws MultiSiteRoutingException
     * @throws MultiSiteException
     * @throws \Exception
     */
    public function lostPassword($params) {
// TODO: refactor whole method
throw new MultiSiteException('Refactor this method!');
        global $_ARRAYLANG;

        if (empty($params['post'])) {
            $rawPostData = file_get_contents("php://input");
            if (!empty($rawPostData) && ($arrRawPostData = explode('&', $rawPostData)) && !empty($arrRawPostData)) {
                $arrPostData = array();
                foreach ($arrRawPostData as $postData) {
                    if (!empty($postData)) {
                        list($postKey, $postValue) = explode('=', $postData);
                        $arrPostData[$postKey] = $postValue;
                    }
                }
                $params['post'] = $arrPostData;
            }
        }
        
        if (empty($params['get']['name']) && empty($params['post']['name'])) {
            if (preg_match('/'.$this->getApiProtocol().':\/\/(.+)\.'.\Cx\Core\Setting\Controller\Setting::getValue('multiSiteDomain').'/', $_SERVER['HTTP_REFERER'], $matches)) {
                $params['post']['name'] = $matches[1];
            } else {
                throw new \Exception("not enough arguments!");
            }
        }

        $lang = 'de';
        if (isset($params['get']) && isset($params['get']['language'])) {
            $lang = $params['get']['language'];
        }
        if (isset($params['post']) && isset($params['post']['lang'])) {
            $lang = $params['post']['lang'];
            $params['post']['language'] = $lang;
        }
        $langId = \FWLanguage::getLanguageIdByCode($lang);
        \Env::get('ClassLoader')->loadFile(ASCMS_CORE_MODULE_PATH.'/MultiSite/lang/' . $lang . '/backend.php');

        $instRepo = \Env::get('em')->getRepository('\Cx\Core_Modules\MultiSite\Model\Entity\Website');
        $websiteName = isset($params['get']['name']) ? $params['get']['name'] : $params['post']['name'];
        /**
         * @var \Cx\Core_Modules\MultiSite\Model\Entity\Websites $website
         */
        $website = $instRepo->findByName($websiteName);
        if (!$website) {
            throw new MultiSiteRoutingException($_ARRAYLANG['TXT_CORE_MODULE_MULTISITE_NO_SUCH_WEBSITE_WITH_NAME']);
        }

        $jd = new \Cx\Core\Json\JsonData();
        // used by jsonUser
        $params['post']['email'] = $website->getMail();
        $params['post']['sendMail'] = false;

        // used by routing of a.
        // index.php?cmd=jsondata&object=RoutingAdapter&act=route&mail=" + $("#email").val() + "&adapter=user&method=lostPassword
        $get = array(
            'adapter' => 'user',
            'method' => 'lostPassword',
            'mail' => $website->getMail(),
        );
        $get = array_merge($params['get'], $get);
        $response = $jd->jsondata('RoutingAdapter', 'route', array('get' => $get, 'post' => $params['post']));
        $response = json_decode($response);
        if ($response->status !== 'success') {
            throw new MultiSiteException('Unable to restore password for website!');
        }
        $restoreLink = isset($response->data->restoreLink) ? $response->data->restoreLink : null;
        if (!$restoreLink) {
            throw new MultiSiteException('Something went wrong. Could not restore the user.');
        }

        \MailTemplate::init('MultiSite');
        if (!\MailTemplate::send(array(
            'section' => 'MultiSite',
            'lang_id' => $langId,
            'key' => 'lostPassword',
            'to' => $website->getMail(),
            'search' => array('[[WEBSITE_NAME]]', '[[WEBSITE_MAIL]]', '[[WEBSITE_RESTORE_LINK]]'),
            'replace' => array($website->getName(), $website->getMail(), $restoreLink),
        ))) {
            throw new MultiSiteException('Could not restore password (Mail send failed)');
        }

        $this->messages = $response->message;
        return true;
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
                $hostingController = new PleskController($mailServiceServer->getHostname(), $mailServiceServer->getAuthUsername() , $mailServiceServer->getAuthPassword());
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
            if (\Cx\Core\Setting\Controller\Setting::getValue('payrexxFormId') === NULL
                && !\Cx\Core\Setting\Controller\Setting::add('payrexxFormId', '', 20,
                \Cx\Core\Setting\Controller\Setting::TYPE_TEXT, null, 'setup')){
                    throw new MultiSiteException("Failed to add Setting entry for Payrexx Form Id");
            }
            if (\Cx\Core\Setting\Controller\Setting::getValue('payrexxApiSecret') === NULL
                && !\Cx\Core\Setting\Controller\Setting::add('payrexxApiSecret', '', 21,
                \Cx\Core\Setting\Controller\Setting::TYPE_TEXT, null, 'setup')){
                    throw new MultiSiteException("Failed to add Setting entry for Payrexx API Secret");
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
            if (\Cx\Core\Setting\Controller\Setting::getValue('defaultWebsiteServiceServer') === NULL
                && !\Cx\Core\Setting\Controller\Setting::add('defaultWebsiteServiceServer', 0, 1,
                \Cx\Core\Setting\Controller\Setting::TYPE_DROPDOWN, '{src:\\'.__CLASS__.'::getWebsiteServiceServerList()}', 'manager') ) {
                   throw new MultiSiteException("Failed to add Setting entry for Default Website Service Server");
            }
            if (\Cx\Core\Setting\Controller\Setting::getValue('defaultMailServiceServer') === NULL
                && !\Cx\Core\Setting\Controller\Setting::add('defaultMailServiceServer', 0, 2,
                \Cx\Core\Setting\Controller\Setting::TYPE_DROPDOWN, '{src:\\'.__CLASS__.'::getMailServiceServerList()}', 'manager') ) {
                   throw new MultiSiteException("Failed to add Setting entry for Default mail Service Server");
            }
            if (\Cx\Core\Setting\Controller\Setting::getValue('defaultWebsiteTemplate') === NULL
                && !\Cx\Core\Setting\Controller\Setting::add('defaultWebsiteTemplate', '0', 3,
                \Cx\Core\Setting\Controller\Setting::TYPE_DROPDOWN, '{src:\\'.__CLASS__.'::getWebsiteTemplateList()}', 'manager')) {
                    throw new MultiSiteException("Failed to add Setting entry for default Website Template");
            }
            if (\Cx\Core\Setting\Controller\Setting::getValue('defaultPimProduct') === NULL 
                && !\Cx\Core\Setting\Controller\Setting::add('defaultPimProduct', '0', 4,
                \Cx\Core\Setting\Controller\Setting::TYPE_DROPDOWN, '{src:\Cx\Modules\Pim\Controller\BackendController::getProductList()}', 'manager') ) {
                   throw new MultiSiteException("Failed to add Setting entry for Product List");
            }
        } catch (\Exception $e) {
            \DBG::msg($e->getMessage());
        }
        // Always
        return false;
    }

    public function postResolve(\Cx\Core\ContentManager\Model\Entity\Page $page) {
        // Event Listener must be registered before preContentLoad event

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
        $evm->addModelListener(\Doctrine\ORM\Events::postRemove, 'Cx\\Core\\Net\\Model\\Entity\\Domain', $domainEventListener);
        $evm->addModelListener(\Doctrine\ORM\Events::preUpdate, 'Cx\\Core\\Net\\Model\\Entity\\Domain', $domainEventListener);
        
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
                // handle MultiSite-API requests
                if (   $cx->getMode() == $cx::MODE_BACKEND
                    && $_REQUEST['cmd'] == 'JsonData'
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
     * Used to get all the admin users and backend group users
     * 
     * @return array returns admin users
     */
    public static function getAllAdminUsers() {
        // check the mode
        switch (\Cx\Core\Setting\Controller\Setting::getValue('mode')) {
            case ComponentController::MODE_WEBSITE:

                $objFWUser = \FWUser::getFWUserObject();
                $users = array();

                //get the backend group ids
                $backendGroupIds = self::getBackendGroupIds();
                
                //get backend group users
                $objBackendGroupUser = $objFWUser->objUser->getUsers(array('group_id' => $backendGroupIds));
                if ($objBackendGroupUser) {
                    while (!$objBackendGroupUser->EOF) {
                        $users[$objBackendGroupUser->getId()] = array(
                            'id' => contrexx_raw2xhtml($objBackendGroupUser->getId()),
                            'email' => contrexx_raw2xhtml($objBackendGroupUser->getEmail()),
                            'username' => contrexx_raw2xhtml($objBackendGroupUser->getUsername()),
                            'firstname' => contrexx_raw2xhtml($objBackendGroupUser->getProfileAttribute('firstname')),
                            'lastname' => contrexx_raw2xhtml($objBackendGroupUser->getProfileAttribute('lastname'))
                        );
                        $objBackendGroupUser->next();
                    }
                }

                //get Admin users
                $objAdminUser = $objFWUser->objUser->getUsers(array('is_admin' => 1));
                if ($objAdminUser) {
                    while (!$objAdminUser->EOF) {
                        if (!array_key_exists($objAdminUser->getId(), $users)) {
                            $users[$objAdminUser->getId()] = array(
                                'id' => contrexx_raw2xhtml($objAdminUser->getId()),
                                'email' => contrexx_raw2xhtml($objAdminUser->getEmail()),
                                'username' => contrexx_raw2xhtml($objAdminUser->getUsername()),
                                'firstname' => contrexx_raw2xhtml($objAdminUser->getProfileAttribute('firstname')),
                                'lastname' => contrexx_raw2xhtml($objAdminUser->getProfileAttribute('lastname'))
                            );
                        }
                        $objAdminUser->next();
                    }
                }
                return $users;
                break;
        }
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
     * Post content load hook.
     *
     * @param \Cx\Core\ContentManager\Model\Entity\Page $page Resolved page
     */
    public function postContentLoad(\Cx\Core\ContentManager\Model\Entity\Page $page) {
        self::loadAccountActivationBar();
        self::loadPoweredByFooter();
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
        $warning = new \Cx\Core\Html\Sigma($this->cx->getCodeBaseCoreModuleWebPath() . '/MultiSite/View/Template/Backend');
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
        
        if (!$this->cx->getMode() == \Cx\Core\Core\Controller\Cx::MODE_FRONTEND) {
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
            $footer = new \Cx\Core\Html\Sigma($this->cx->getCodeBaseCoreModuleWebPath() . '/MultiSite/View/Template/Backend');
            $footer->loadTemplateFile('Footer.html');
            $footer->setVariable(array(
                'MULTISITE_POWERED_BY_FOOTER_LINK' => $marketingWebsiteDomainName,
                'MULTISITE_POWERED_BY_CONTENT'     => $_ARRAYLANG['TXT_MULTISITE_POWERED_BY_FOOTER'] . ' &nbsp;&nbsp;<img src="/core/Core/View/Media/login_contrexx_logo.png" />',
            ));

            \JS::registerCSS('core_modules/MultiSite/View/Style/PoweredByFooterFrontend.css');                
            $objTemplate->_blocks['__global__'] = preg_replace('/<\/body>/', '\\0' . $footer->get(), $objTemplate->_blocks['__global__']);
        }
        
    }
}
