<?php


/**
 * JSON Adapter for Multisite
 * @copyright   CONTREXX CMS - COMVATION AG
 * @author      Sudhir Parmar <sudhirparmar@cdnsol.com>
 * @version     4.0.0
 * @package     contrexx
 * @subpackage  Multisite
*/

namespace Cx\Core_Modules\MultiSite\Controller;

class MultiSiteJsonException extends \Exception {
    protected $object;
    protected $type = 'danger';

    /**
     * Overwriting the default Exception constructor
     * The default Exception constructor only accepts $message to be a string.
     * We do overwrite this here to also allow $message to be an array
     * that can then be sent back in the JsonData-response.
     */
    public function __construct($message = null, $code = 0, Exception $previous = null) {
        if (is_array($message)) {
            $msg = $message['message'];
            if (isset($message['object'])) {
                $this->object = $message['object'];
            }
            if (isset($message['type'])) {
                $this->type = $message['type'];
            }
        } else {
            $msg = $message;
        }
        parent::__construct($msg, $code, $previous);
        // overwrite $message to pass exception data to JsonData
        $this->message=$message;
    }
    
    public function getObject() {
        return $this->object;
    }

    public function getType() {
        return $this->type;
    }
}
/**
 * JSON Adapter for Multisite
 * @copyright   CONTREXX CMS - COMVATION AG
 * @author      Sudhir Parmar <sudhirparmar@cdnsol.com>
 * @version     4.0.0
 * @package     contrexx
 * @subpackage  Multisite
*/
class JsonMultiSite implements \Cx\Core\Json\JsonAdapter {

    /**
     * @var boolean 
     */
    static $isIscRequest = false;
    
    /**
     * Returns the internal name used as identifier for this adapter
     * @return String Name of this adapter
     */
    public function getName() {
        return 'MultiSite';
    }

    /**
     * Returns an array of method names accessable from a JSON request
     * @return array List of method names
     */
    public function getAccessableMethods() {
        $multiSiteProtocol = (\Cx\Core\Setting\Controller\Setting::getValue('multiSiteProtocol','MultiSite') == 'mixed')? \Env::get('cx')->getRequest()->getUrl()->getProtocol(): \Cx\Core\Setting\Controller\Setting::getValue('multiSiteProtocol','MultiSite');
        return array(
            'signup'                => new \Cx\Core_Modules\Access\Model\Entity\Permission(array($multiSiteProtocol), array('post'), false),
            'email'                 => new \Cx\Core_Modules\Access\Model\Entity\Permission(array($multiSiteProtocol), array('post'), false),
            'address'               => new \Cx\Core_Modules\Access\Model\Entity\Permission(array($multiSiteProtocol), array('post'), false),
            'createWebsite'         => new \Cx\Core_Modules\Access\Model\Entity\Permission(array($multiSiteProtocol), array('post'), false, null, null, array($this, 'auth')),
            // protocol workaround as option multiSiteProtocol is not set on WEBSITE
            'createUser'            => new \Cx\Core_Modules\Access\Model\Entity\Permission(array('http', 'https'), array('post'), false, null, null, array($this, 'auth')),
            'updateUser'            => new \Cx\Core_Modules\Access\Model\Entity\Permission(array('http', 'https'), array('post'), false, null, null, array($this, 'auth')),
            'updateOwnUser'         => new \Cx\Core_Modules\Access\Model\Entity\Permission(array($multiSiteProtocol), array('post'), true),
            'mapDomain'             => new \Cx\Core_Modules\Access\Model\Entity\Permission(array($multiSiteProtocol), array('post'), false, null, null, array($this, 'auth')),
            'unMapDomain'           => new \Cx\Core_Modules\Access\Model\Entity\Permission(array($multiSiteProtocol), array('post'), false, null, null, array($this, 'auth')),
            'updateDomain'          => new \Cx\Core_Modules\Access\Model\Entity\Permission(array($multiSiteProtocol), array('post'), false, null, null, array($this, 'auth')),
            'updateDefaultCodeBase' => new \Cx\Core_Modules\Access\Model\Entity\Permission(array($multiSiteProtocol), array('post'), true, null, array(183), null),
            'setWebsiteState'       => new \Cx\Core_Modules\Access\Model\Entity\Permission(array($multiSiteProtocol), array('post'), false, null, null, array($this, 'auth')),
            'updateWebsiteState'    => new \Cx\Core_Modules\Access\Model\Entity\Permission(array($multiSiteProtocol), array('post'), true, null, array(183), null),
            'ping'                  => new \Cx\Core_Modules\Access\Model\Entity\Permission(array($multiSiteProtocol), array('post'), false, null, null, array($this, 'auth')),
            'pong'                  => new \Cx\Core_Modules\Access\Model\Entity\Permission(array($multiSiteProtocol), array('post'), false, null, null, array($this, 'auth')),
            'setLicense'            => new \Cx\Core_Modules\Access\Model\Entity\Permission(array('http', 'https'), array('post'), false, null, null, array($this, 'auth')),
            'setupConfig'           => new \Cx\Core_Modules\Access\Model\Entity\Permission(array('http', 'https'), array('post'), false, null, null, array($this, 'auth')),
            'getDefaultWebsiteIp'   => new \Cx\Core_Modules\Access\Model\Entity\Permission(array($multiSiteProtocol), array('post'), false, null, null, array($this, 'auth')),
            'setDefaultLanguage'    => new \Cx\Core_Modules\Access\Model\Entity\Permission(array('http', 'https'), array('post'), false, null, null, array($this, 'auth')),
            'resetFtpPassword'      => new \Cx\Core_Modules\Access\Model\Entity\Permission(array('http', 'https'), array('post'), false, null, null, array($this, 'checkResetFtpPasswordAccess')),
            'updateServiceServerSetup' => new \Cx\Core_Modules\Access\Model\Entity\Permission(array($multiSiteProtocol), array('post'), false, null, null, array($this, 'auth')),
            'destroyWebsite'        => new \Cx\Core_Modules\Access\Model\Entity\Permission(array($multiSiteProtocol), array('post'), false, null, null, array($this, 'verifyWebsiteOwnerOrIscRequest')),
            'executeOnWebsite'      => new \Cx\Core_Modules\Access\Model\Entity\Permission(array($multiSiteProtocol), array('post'), false, null, null, array($this, 'auth')),
            'executeOnManager'      => new \Cx\Core_Modules\Access\Model\Entity\Permission(array($multiSiteProtocol), array('post'), false, null, null, array($this, 'auth')),
            'generateAuthToken'     => new \Cx\Core_Modules\Access\Model\Entity\Permission(array('http', 'https'), array('post'), false, null, null, array($this, 'auth')),
            'executeSql'            => new \Cx\Core_Modules\Access\Model\Entity\Permission(array('http', 'https'), array('post'), false, null, null, array($this, 'checkExecuteSqlAccess')),
            'removeUser'            => new \Cx\Core_Modules\Access\Model\Entity\Permission(array('http', 'https'), array('post'), false, null, null, array($this, 'auth')),
            'setWebsiteTheme'       => new \Cx\Core_Modules\Access\Model\Entity\Permission(array('http', 'https'), array('post'), false, null, null, array($this, 'auth')),
            'getFtpUser'            => new \Cx\Core_Modules\Access\Model\Entity\Permission(array('http', 'https'), array('post'), false, null, null, array($this, 'auth')),
            'getFtpAccounts'        => new \Cx\Core_Modules\Access\Model\Entity\Permission(array('http', 'https'), array('post'), false, null, null, array($this, 'auth')),
            'getLicense'            => new \Cx\Core_Modules\Access\Model\Entity\Permission(array('http', 'https'), array('post'), false, null, null, array($this, 'checkGetLicenseAccess')),
            'remoteLogin'           => new \Cx\Core_Modules\Access\Model\Entity\Permission(array($multiSiteProtocol), array('post'), true, null, array(183), null),
            'editLicense'           => new \Cx\Core_Modules\Access\Model\Entity\Permission(array('http', 'https'), array('post'), false, null, null, array($this, 'checkGetLicenseAccess')),
            'executeQueryBySession' => new \Cx\Core_Modules\Access\Model\Entity\Permission(array($multiSiteProtocol), array('post'), true, null, array(183), null),
            'stopQueryExecution'    => new \Cx\Core_Modules\Access\Model\Entity\Permission(array($multiSiteProtocol), array('post'), true, null, array(183), null),
            'modifyMultisiteConfig' => new \Cx\Core_Modules\Access\Model\Entity\Permission(array('http', 'https'), array('post'), false, null, null, array($this, 'checkGetLicenseAccess')),
            'sendAccountActivation' => new \Cx\Core_Modules\Access\Model\Entity\Permission(array('http', 'https'), array('post'), false, null, null, array($this, 'checkSendAccountActivation')),
            'getPayrexxUrl'         => new \Cx\Core_Modules\Access\Model\Entity\Permission(array($multiSiteProtocol), array('post'), false),
            'push'                  => new \Cx\Core_Modules\Access\Model\Entity\Permission(array('http', 'https'), array('post'), false, null, null, array($this, 'auth')),
            'websiteBackup'         => new \Cx\Core_Modules\Access\Model\Entity\Permission(array($multiSiteProtocol), array('post'), false, null, null, array($this, 'auth')),
            'getWebsiteInfo'        => new \Cx\Core_Modules\Access\Model\Entity\Permission(array('http', 'https'), array('post'), false, null, null, array($this, 'auth')),
            'updateWebsiteConfig'   => new \Cx\Core_Modules\Access\Model\Entity\Permission(array('http', 'https'), array('post'), false, null, null, array($this, 'auth')),
            'updateWebsiteSubscriptionInfo'     => new \Cx\Core_Modules\Access\Model\Entity\Permission(array('http', 'https'), array('post'), false, null, null, array($this, 'auth')),
            'websiteRestore'        => new \Cx\Core_Modules\Access\Model\Entity\Permission(array($multiSiteProtocol), array('post'), false, null, null, array($this, 'auth')),
            'createNewWebsiteBySubscription' => new \Cx\Core_Modules\Access\Model\Entity\Permission(array($multiSiteProtocol), array('post'), false),
            'websiteLogin'          => new \Cx\Core_Modules\Access\Model\Entity\Permission(array($multiSiteProtocol), array('post'), true),
            'getAdminUsers'         => new \Cx\Core_Modules\Access\Model\Entity\Permission(array('http', 'https'), array('post'), false, null, null, array($this, 'auth')),
            'getUser'               => new \Cx\Core_Modules\Access\Model\Entity\Permission(array('http', 'https'), array('post'), false, null, null, array($this, 'auth')),
            'getResourceUsageStats' => new \Cx\Core_Modules\Access\Model\Entity\Permission(array('http', 'https'), array('post'), false, null, null, array($this, 'auth')),
            'enableMailService'     => new \Cx\Core_Modules\Access\Model\Entity\Permission(array($multiSiteProtocol), array('post'), false, null, null, array($this, 'verifyWebsiteOwnerOrIscRequest')),            
            'disableMailService'    => new \Cx\Core_Modules\Access\Model\Entity\Permission(array($multiSiteProtocol), array('post'), false, null, null, array($this, 'verifyWebsiteOwnerOrIscRequest')),
            'resetEmailPassword'    => new \Cx\Core_Modules\Access\Model\Entity\Permission(array($multiSiteProtocol), array('post'), false, null, null, array($this, 'verifyWebsiteOwnerOrIscRequest')),            
            'getMailServiceStatus'  => new \Cx\Core_Modules\Access\Model\Entity\Permission(array($multiSiteProtocol), array('post'), false, null, null, array($this, 'verifyWebsiteOwnerOrIscRequest')),
            'createMailServiceAccount' => new \Cx\Core_Modules\Access\Model\Entity\Permission(array($multiSiteProtocol), array('post'), false, null, null, array($this, 'auth')),
            'deleteMailServiceAccount' => new \Cx\Core_Modules\Access\Model\Entity\Permission(array($multiSiteProtocol), array('post'), false, null, null, array($this, 'auth')),
            'manageSubscription'   => new \Cx\Core_Modules\Access\Model\Entity\Permission(array($multiSiteProtocol), array('post'), true),
            'getPanelAutoLoginUrl' => new \Cx\Core_Modules\Access\Model\Entity\Permission(array($multiSiteProtocol), array('post'), true),
            'mapNetDomain'          => new \Cx\Core_Modules\Access\Model\Entity\Permission(array('http', 'https'), array('post'), false, null, null, array($this, 'auth')), 
            'unMapNetDomain'        => new \Cx\Core_Modules\Access\Model\Entity\Permission(array('http', 'https'), array('post'), false, null, null, array($this, 'auth')), 
            'updateNetDomain'       => new \Cx\Core_Modules\Access\Model\Entity\Permission(array('http', 'https'), array('post'), false, null, null, array($this, 'auth')), 
            'createAdminUser'       => new \Cx\Core_Modules\Access\Model\Entity\Permission(array('http', 'https'), array('post'), false, null, null, array($this, 'auth')),
            'payrexxAutoLoginUrl'     => new \Cx\Core_Modules\Access\Model\Entity\Permission(array($multiSiteProtocol), array('post'), true),
            'updateOwnWebsiteState'  => new \Cx\Core_Modules\Access\Model\Entity\Permission(array($multiSiteProtocol), array('post'), false, null, null, array($this, 'verifyWebsiteOwnerOrIscRequest')),
            'getMainDomain'         => new \Cx\Core_Modules\Access\Model\Entity\Permission(array('http', 'https'), array('post'), false, null, null, array($this, 'auth')),
            'setMainDomain'         => new \Cx\Core_Modules\Access\Model\Entity\Permission(array('http', 'https'), array('post'), false, null, null, array($this, 'auth')),
            'deleteAccount'         => new \Cx\Core_Modules\Access\Model\Entity\Permission(array('http', 'https'), array('post'), true),   
            'isComponentLicensed'  => new \Cx\Core_Modules\Access\Model\Entity\Permission(array('http', 'https'), array('post'), false, null, null, array($this, 'auth')),
            'getModuleAdditionalData' => new \Cx\Core_Modules\Access\Model\Entity\Permission(array('http', 'https'), array('post'), false, null, null, array($this, 'auth')),            
            'changePlanOfMailSubscription' => new \Cx\Core_Modules\Access\Model\Entity\Permission(array('http', 'https'), array('post'), false, null, null, array($this, 'auth')),
            'domainManipulation'    => new \Cx\Core_Modules\Access\Model\Entity\Permission(array('http', 'https'), array('post'), false, null, null, array($this, 'auth')),
            'isUniqueEmail'         => new \Cx\Core_Modules\Access\Model\Entity\Permission(array($multiSiteProtocol), array('post'), false, null, null, array($this, 'auth')),
            'getMailServicePlans'   => new \Cx\Core_Modules\Access\Model\Entity\Permission(array($multiSiteProtocol), array('post'), false, null, array(183), null),
            'trackAffiliateId'      => new \Cx\Core_Modules\Access\Model\Entity\Permission(null, null, false),
            'checkAvailabilityOfAffiliateId' => new \Cx\Core_Modules\Access\Model\Entity\Permission(array($multiSiteProtocol), array('post'), true),
            'setAffiliate'        => new \Cx\Core_Modules\Access\Model\Entity\Permission(array($multiSiteProtocol), array('post'), true),
            'sendNotificationForPayoutRequest' => new \Cx\Core_Modules\Access\Model\Entity\Permission(array($multiSiteProtocol), array('post'), true),
        );  
    }

    /**
     * Returns all messages as string
     * @return String HTML encoded error messages
     */
    public function getMessagesAsString() {
        return '';
    }
    
    /**
     * Returns default permission as object
     * @return Object
     */
    public function getDefaultPermissions() {
        return null;
    }

    /**
     * Check if there is already an account present
     * by the supplied email address.
     * @param array $params supplied arguments from JsonData-request
     * @return void Returns nothing in case the email is not yet registered
     * @throws MultiSiteJsonException An array with further information about the already used email address
     */
    public function email($params) {
        global $_ARRAYLANG;

        if (!isset($params['post']['multisite_email_address'])) {
            return;
        }

        //check email validity
        if (!\FWValidator::isEmail($params['post']['multisite_email_address'])) {
            self::loadLanguageData();
            throw new MultiSiteJsonException(array(
                'object'    => 'email',
                'type'      => 'danger',
                'message'   => $_ARRAYLANG['TXT_CORE_MODULE_MULTISITE_INVALID_EMAIL'],
            ));
        }

        if (!\User::isUniqueEmail($params['post']['multisite_email_address'])) {
            self::loadLanguageData();

            $loginUrl = \Cx\Core\Routing\Url::fromMagic(ASCMS_PROTOCOL . '://' . \Cx\Core\Setting\Controller\Setting::getValue('customerPanelDomain','MultiSite') . '/')->toString();
            $loginLink = '<a class="alert-link" href="'.$loginUrl.'" target="_blank">'.$_ARRAYLANG['TXT_CORE_MODULE_MULTISITE_LOGIN'].'</a>';
            throw new MultiSiteJsonException(array(
                'object'    => 'email',
                'type'      => 'info',
                'message'   => sprintf($_ARRAYLANG['TXT_CORE_MODULE_MULTISITE_EMAIL_IN_USE'], $loginLink),
            ));
        }
    }

    public static function loadLanguageData() {
        global $_ARRAYLANG, $_CORELANG, $objInit;
        $langData = $objInit->loadLanguageData('MultiSite');
        $_ARRAYLANG = array_merge($_ARRAYLANG, $langData);
        
        $coreLangData = $objInit->loadLanguageData('core');
        $_CORELANG = array_merge($_ARRAYLANG, $coreLangData);
    }

    public function address($params) {
        if (!isset($params['post']['multisite_address'])) {
            return;
        }
        try {
            \Cx\Core_Modules\MultiSite\Model\Entity\Website::validateName(contrexx_input2raw($params['post']['multisite_address']));
        } catch (\Cx\Core_Modules\MultiSite\Model\Entity\WebsiteException $e) {
            throw new MultiSiteJsonException(array(
                'object'    => 'address',
                'type'      => 'warning',
                'message'   => $e->getMessage()
            ));
        }
    }

    /**
     * @param post parameters
     */
    public function signup($params) {
        global $_ARRAYLANG;

        // abort in case command has been requested without any data
        if (empty($params['post'])) {
            return;
        }

        // activate memory-logging
        $this->activateDebuggingToMemory();

        // Validate address and email before starting with the actual sign up process.
        // Those methods throw an individual exception that can be parsed by the sign up form.
        // Therefore, those shall not be called in the below try/catch block
        $this->address($params);
        $this->email($params);

        try {
            // load text-variables of module MultiSite
            self::loadLanguageData();

            // set website name and website theme
            $websiteName = contrexx_input2raw($params['post']['multisite_address']);
            $websiteThemeId = !empty($params['post']['themeId']) ? contrexx_input2raw($params['post']['themeId']) : null;

            // create new user account
            $arrSettings = \User_Setting::getSettings();
            $user = $this->createUser(array('post' => array(
                'active'=> true,
                'email' => $params['post']['multisite_email_address'],
                'groups'=> explode(',', $arrSettings['assigne_to_groups']['value']),
            )));

            $objFWUser = \FWUser::getFWUserObject();
            $objUser = $objFWUser->objUser->getUser(intval($user['userId']));
            if (!$objUser) {
                throw new MultiSiteJsonException("Unable to load user account {$user['userId']}.");
            }

            // create a new CRM Contact and link it to the User account
            \Cx\Modules\Crm\Controller\CrmLibrary::addCrmContactFromAccessUser($objUser);
        
            // TODO: Product ID should be supplied by POST-data.
            //       If not set, then the ID should be taken from a MultiSite configuration option 'defaultProductId'
            $id = isset($params['post']['product_id']) ? contrexx_input2raw($params['post']['product_id']) : \Cx\Core\Setting\Controller\Setting::getValue('defaultPimProduct','MultiSite');

            // create new subscription of selected product
            $renewalOption = isset($params['post']['renewalOption']) ? $params['post']['renewalOption'] : 'monthly';
			list($renewalUnit, $renewalQuantifier) = self::getProductRenewalUnitAndQuantifier($renewalOption);
            $subscriptionOptions = array(
                'renewalUnit'       => $renewalUnit,
                'renewalQuantifier' => $renewalQuantifier,
                'websiteName'       => $websiteName,
                'customer'          => $objUser,
            );
            
            //pass the website's theme id to subscription option, if $themeId set
            if (!empty($websiteThemeId)) {
                $subscriptionOptions['themeId'] = $websiteThemeId;
            }
            
            $transactionReference = "|$id|name|$websiteName|";
            $currency = ComponentController::getUserCurrency($objUser->getCrmUserId());
            $order = \Env::get('em')->getRepository('Cx\Modules\Order\Model\Entity\Order')->createOrder($id, $currency, $objUser, $transactionReference, $subscriptionOptions);
            if (!$order) {
                throw new MultiSiteJsonException($_ARRAYLANG['TXT_CORE_MODULE_MULTISITE_ORDER_FAILED']);
            }

            // Set user's verification state to pending in case the related
            // password-setup-method has been configured.
            if (\Cx\Core\Setting\Controller\Setting::getValue('passwordSetupMethod','MultiSite') == 'auto-with-verification') {
                \DBG::msg('HOTFIX: set verification state to pending on Cloudrexx user..');
                // set state of user account to unverified
                $objUser->setVerification(false);
                $objUser->store();
            }

            // create the website process in the payComplete event
            $order->complete(); 
            
            // fetch the newly build website from the repository
            $websiteRepository = \Env::get('em')->getRepository('Cx\Core_Modules\MultiSite\Model\Entity\Website');
            $website   = $websiteRepository->findOneBy(array('name' => $websiteName));

// TODO: remove once setup process works flawlessly
            // send setup protocol anyway
            if (\Cx\Core\Setting\Controller\Setting::getValue('sendSetupError','MultiSite')) {
                $config = \Env::get('config');
                \Cx\Core\MailTemplate\Controller\MailTemplate::init('MultiSite');
                \Cx\Core\MailTemplate\Controller\MailTemplate::send(array(
                    'section' => 'MultiSite',
                    'key' => 'setupError',
                    'to' => $config['coreAdminEmail'],
                    'search' => array(
                        '[[ERROR]]',
                        '[[WEBSITE_NAME]]',
                        '[[CUSTOMER_EMAIL]]',
                        '[[DBG_LOG]]',
                    ),
                    'replace' => array(
                        'SETUP SUCCESSFUL',
                        $websiteName,
                        $params['post']['multisite_email_address'],
                        join("\n", \DBG::getMemoryLogs()),
                    ),
                ));
            }

            $authToken = null;
            $autoLoginUrl = null;
            if (\Cx\Core\Setting\Controller\Setting::getValue('autoLogin','MultiSite')) {
                \DBG::msg('Website: generate auth-token for Cloudrexx user..');
                try {
                    list($ownerUserId, $authToken) = $website->generateAuthToken();
                    $autoLoginUrl = \Cx\Core\Routing\Url::fromMagic(ComponentController::getApiProtocol() . $website->getBaseDn()->getName() . \Env::get('cx')->getWebsiteBackendPath() . '/?user-id='.$ownerUserId.'&auth-token='.$authToken);
                } catch (\Cx\Core_Modules\MultiSite\Model\Entity\WebsiteException $e) {
                    \DBG::msg($e->getMessage());
                }
            }

            if ($autoLoginUrl) {
                return array(
                    'status'    => 'success',
                    'message'   => 'auto-login',
                    'loginUrl'  => $autoLoginUrl->toString(),
                );
            } else {
                $websiteLink = '<a href="'.ComponentController::getApiProtocol().$website->getBaseDn()->getName().'" target="_blank">'.$website->getBaseDn()->getName().'</a>';
                return array(
                    'status'    => 'success',
                    'message'   => sprintf($_ARRAYLANG['TXT_CORE_MODULE_MULTISITE_WEBSITE_CREATED'], $websiteLink),
                );
            }
        } catch (\Exception $e) {
            $config = \Env::get('config');
            if (\Cx\Core\Setting\Controller\Setting::getValue('sendSetupError','MultiSite')) {
                \Cx\Core\MailTemplate\Controller\MailTemplate::init('MultiSite');
                \Cx\Core\MailTemplate\Controller\MailTemplate::send(array(
                    'section' => 'MultiSite',
                    'key' => 'setupError',
                    'to' => $config['coreAdminEmail'],
                    'search' => array(
                        '[[ERROR]]',
                        '[[WEBSITE_NAME]]',
                        '[[CUSTOMER_EMAIL]]',
                        '[[DBG_LOG]]',
                    ),
                    'replace' => array(
                        $e->getMessage(),
                        $websiteName,
                        $params['post']['multisite_email_address'],
                        join("\n", \DBG::getMemoryLogs()),
                    ),
                ));
            }
            \DBG::msg($e->getMessage());
            throw new MultiSiteJsonException(array(
                'object'    => 'form',
                'type'      => 'danger',
                'message'   => sprintf($_ARRAYLANG['TXT_CORE_MODULE_MULTISITE_WEBSITE_CREATION_ERROR'], contrexx_raw2xhtml($objUser->getEmail())),
            ));
        }
    }

    /**
     * Manage Subscription 
     * 
     * @param array $params parameters
     * 
     * @return array
     * @throws MultiSiteJsonException
     */
    public function manageSubscription($params) {
        global $_ARRAYLANG;
        
        // abort in case command has been requested without any data
        if (empty($params['post'])) {
            return;
        }
        
        // activate file logging to debug payment handling
        \DBG::deactivate();
        \DBG::activate(DBG_LOG_FILE | DBG_PHP | DBG_LOG);
        \DBG::dump($_REQUEST);
        
        $subscriptionId = isset($params['post']['subscription_id']) ? contrexx_input2raw($params['post']['subscription_id']) : 0;
        $subscriptionType = !\FWValidator::isEmpty($subscriptionId) ? 'UPGRADE' : 'ADD';
        $objUser = \FWUser::getFWUserObject()->objUser;
        $crmContactId = $objUser->getCrmUserId();

        //validate Crm user
        if (\FWValidator::isEmpty($crmContactId)) {
            \DBG::log('Not a valid user');
            return array ('status' => 'error','message' => $_ARRAYLANG['TXT_CORE_MODULE_MULTISITE_SUBSCRIPTION_'.$subscriptionType.'_FAILED']);
        }
        
        try {
            // Load language variables of MultisiteexternalPayment
            self::loadLanguageData();
            
            $productId = isset($params['post']['product_id']) ? contrexx_input2raw($params['post']['product_id']) : 0;
            $websiteName = isset($params['post']['websiteName']) ? contrexx_input2raw($params['post']['websiteName']) : '';
            $renewalOption = isset($params['post']['renewalOption']) ? contrexx_input2raw($params['post']['renewalOption']) : '';
            
            if (   \FWValidator::isEmpty($productId) 
                || \FWValidator::isEmpty($renewalOption)
            ) {
                \DBG::log('Invalid argument supplied.');
                return array ('status' => 'error','message' => $_ARRAYLANG['TXT_CORE_MODULE_MULTISITE_SUBSCRIPTION_'.$subscriptionType.'_FAILED']);
            }
            
            $productRepository = \Env::get('em')->getRepository('Cx\Modules\Pim\Model\Entity\Product');
            $product = $productRepository->findOneBy(array('id' => $productId));
            
            //validate selected product
            if (\FWValidator::isEmpty($product)) {
                \DBG::log('Invalid Product Requested.');
                return array('status' => 'error', 'message' => $_ARRAYLANG['TXT_CORE_MODULE_MULTISITE_SUBSCRIPTION_'.$subscriptionType.'_FAILED']);
            }
            
            list($renewalUnit, $renewalQuantifier) = self::getProductRenewalUnitAndQuantifier($renewalOption);
            $currency = ComponentController::getUserCurrency($crmContactId);
            
            $subscriptionOptions = array(
                'renewalUnit'       => $renewalUnit,
                'renewalQuantifier' => $renewalQuantifier
            );
            
            // upgrade subscription 
            $subscriptionObj = null;
            if (!\FWValidator::isEmpty($subscriptionId)) {
                $subscriptionRepo = \Env::get('em')->getRepository('Cx\Modules\Order\Model\Entity\Subscription');
                $subscriptionObj = $subscriptionRepo->findOneBy(array('id' => $subscriptionId));

                if (!$subscriptionObj) {
                    \DBG::log('Invalid Subscription ID.');
                    return array('status' => 'error', 'message' => $_ARRAYLANG['TXT_CORE_MODULE_MULTISITE_SUBSCRIPTION_'.$subscriptionType.'_FAILED']);
                }
                
                $externalSubscriptionId = !\FWValidator::isEmpty($subscriptionObj->getExternalSubscriptionId()) ? $subscriptionObj->getExternalSubscriptionId() : '';
                $orderObj = $subscriptionObj->getOrder();
                if (!$orderObj) {
                    \DBG::log('Invalid Order for subscription.');
                    return array('status' => 'error', 'message' => $_ARRAYLANG['TXT_CORE_MODULE_MULTISITE_SUBSCRIPTION_'.$subscriptionType.'_FAILED']);
                }
                
                //Verify the owner of the associated Order of the Subscription is actually owned by the currently sign-in user
                if ($crmContactId != $orderObj->getContactId()) {
                     \DBG::log('Access denied Invalid Crm User');
                    return array('status' => 'error', 'message' => $_ARRAYLANG['TXT_CORE_MODULE_MULTISITE_SUBSCRIPTION_'.$subscriptionType.'_FAILED']);
                }
                
                $subscriptionOptions['baseSubscription'] = $subscriptionObj;
                $subscriptionOptions['description']      = $subscriptionObj->getDescription();
            }

            if (!\FWValidator::isEmpty($websiteName)) {
                $transactionReference = "|{$product->getId()}|name|$websiteName|";
                $purpose              = $product->getName() . ' - ' . $websiteName;
            } else {
                $transactionReference = "|{$product->getId()}|owner|{$objUser->getId()}|";
                $purpose              = $product->getName();
            }
           
            $productPrice = $product->getPaymentAmount($renewalUnit, $renewalQuantifier, $currency);
            $credit = 0;
            $externalPaymentCustomerId = $objUser->getProfileAttribute(\Cx\Core\Setting\Controller\Setting::getValue('externalPaymentCustomerIdProfileAttributeId','MultiSite'));
            if (   !\FWValidator::isEmpty($productPrice)
                && !\FWValidator::isEmpty($externalPaymentCustomerId)
            ) {
                
                $instanceName = \Cx\Core\Setting\Controller\Setting::getValue('payrexxAccount','MultiSite');
                $apiSecret    = \Cx\Core\Setting\Controller\Setting::getValue('payrexxApiSecret','MultiSite');
                $payrexx      = new \Payrexx\Payrexx($instanceName, $apiSecret);

                $subscription = new \Payrexx\Models\Request\Subscription();
                $subscription->setUserId($externalPaymentCustomerId);
                $subscription->setPurpose($purpose);
                $subscription->setReferenceId($transactionReference);
                $subscription->setPsp(5);
                
                if (   !\FWValidator::isEmpty($subscriptionId)
                    && $subscriptionObj
                    && !\FWValidator::isEmpty($subscriptionObj->getPaymentAmount())
                    && !\FWValidator::isEmpty($subscriptionObj->getRenewalDate())
                ) {
                    $today = new \DateTime();
                    $today->setTime(0,0); // modify the time to start time
                    
                    $renewalDate      = $subscriptionObj->getRenewalDate();
                    $renewalStartDate = clone $renewalDate;
                    
                    $oldSubscriptionInterval = \DateInterval::createFromDateString($subscriptionObj->getRenewalQuantifier() .' '. $subscriptionObj->getRenewalUnit());
                    $renewalStartDate->sub($oldSubscriptionInterval);
                    
                    $renewalPeriodDays        = $renewalDate->diff($renewalStartDate)->days;
                    $subscriptionPricePerDay  = $subscriptionObj->getPaymentAmount() / $renewalPeriodDays;
                    $daysLeftInCurrentRenewal = $renewalDate->diff($today)->days;
                    
                    $credit                   = number_format($subscriptionPricePerDay * $daysLeftInCurrentRenewal, 2, '.', '');

                    // set discount price of first payment period of subscription
                    $subscriptionOptions['oneTimeSalePrice'] = $productPrice - $credit;
                }
                //update amount deatails in subscription
                $subscription->setAmount(($productPrice - $credit) * 100);
                
                $subscription->setCurrency(\Payrexx\Models\Request\Subscription::CURRENCY_CHF);

                list($subscriptionPeriod, $subscriptionInterval, $cancellationInterval)
                        = self::getSubscriptionPeriodAndIntervalsByProduct($product, $renewalUnit, $renewalQuantifier);
                // set payment interval
                $subscription->setPaymentInterval(\DateTimeTools::getDateIntervalAsString($subscriptionInterval));

                // set subscription period
                $subscription->setPeriod(\DateTimeTools::getDateIntervalAsString($subscriptionPeriod));

                // set cancellation period
                $subscription->setCancellationInterval(\DateTimeTools::getDateIntervalAsString($cancellationInterval));

                try {
                    $response = $payrexx->create($subscription);

                    if (   $response
                        && $response instanceof \Payrexx\Models\Response\Subscription
                        && $response->getStatus() == 'active'
                    ) {
                        $newExternalSubscriptionId = $response->getId();
                        // if credit applied, update the subscription price for the next renewal
                        if (!\FWValidator::isEmpty($credit)) {
                            $updateSubscription = new \Payrexx\Models\Request\Subscription();
                            $updateSubscription->setId($newExternalSubscriptionId);
                            $updateSubscription->setAmount($productPrice * 100);
                            $updateSubscription->setCurrency(\Payrexx\Models\Request\Subscription::CURRENCY_CHF);
                            try {
                                $payrexx->update($updateSubscription);
                            } catch (\Payrexx\PayrexxException $e) {
                                \DBG::log('JsonMultiSite::manageSubscription() - Could not update the a subscription ID => '. $newExternalSubscriptionId. ' / '. $e->getMessage());
                            }
                        }
                        
                        $transactionReference .= "$newExternalSubscriptionId|";

                        // transaction data needed for OrderPaymentEventListener::postPersist()
                        $subscriptionValidDate = new \DateTime();
                        $subscriptionValidDate->add($subscriptionPeriod);
                        $transactionData = array(
                            'subscription' => array(
                              'id'          => $newExternalSubscriptionId,
                              //'valid_until' => $response->getValidUntil(),//;$subscriptionValidDate->format('Y-m-d')
                              'valid_until' => $subscriptionValidDate->format('Y-m-d')
                          )
                        );
                        
                        // create payment for order
                        ComponentController::createPayrexxPayment($transactionReference, $productPrice - $credit, $transactionData);
                    } else {
                        \DBG::log('JsonMultiSite::manageSubscription() - Could not create a subscription.');
                        return array('status' => 'error', 'message' => $_ARRAYLANG['TXT_CORE_MODULE_MULTISITE_SUBSCRIPTION_'.$subscriptionType.'_FAILED']);
                    }
                } catch (\Payrexx\PayrexxException $e) {
                    \DBG::log($_ARRAYLANG['TXT_CORE_MODULE_MULTISITE_SUBSCRIPTION_'.$subscriptionType.'_FAILED'].': '.$e->getMessage());
                    throw new MultiSiteJsonException($_ARRAYLANG['TXT_CORE_MODULE_MULTISITE_SUBSCRIPTION_'.$subscriptionType.'_FAILED']);
                }
            }
            
            $order = \Env::get('em')->getRepository('Cx\Modules\Order\Model\Entity\Order')->createOrder($productId, $currency, $objUser, $transactionReference, $subscriptionOptions);
            if (!$order) {
                \DBG::log('Unable to create the order.');
                return array('status' => 'error', 'message' => $_ARRAYLANG['TXT_CORE_MODULE_MULTISITE_SUBSCRIPTION_'.$subscriptionType.'_FAILED']);
            }
            // create the website process in the payComplete event
            $order->complete();
            
            // update the changes to database
            \Env::get('em')->flush();
            
            $newSubscriptionId = 0;
            foreach ($order->getSubscriptions() as $newSubscription) {
                $newSubscriptionId = $newSubscription->getId();
                break; // break the loop after getting the subscription id from the first entry
            }

            // set regular product price for next payment period of subscription
            if (!empty($credit)) {
                $newSubscription->setPaymentAmount($productPrice);
                \Env::get('em')->flush();
            }
    
            return array ('status' => 'success', 'id' => $newSubscriptionId, 'message' => $_ARRAYLANG['TXT_CORE_MODULE_MULTISITE_SUBSCRIPTION_'.$subscriptionType.'_SUCCESS']);
        } catch (\Exception $e) {
            \DBG::msg($e->getMessage());
            throw new MultiSiteJsonException(array(
                'object'    => 'form',
                'type'      => 'danger',
                'message'   => $_ARRAYLANG['TXT_CORE_MODULE_MULTISITE_SUBSCRIPTION_'.$subscriptionType.'_FAILED'],
            ));
        }
    }
    
    /**
     * Get the subscription period and intervals by given product
     * 
     * @param \Cx\Modules\Pim\Model\Entity\Product $product
     * @param string                               $renewalUnit
     * @param integer                              $renewalQuantifier
     * 
     * @return array returns array 
     *               \DateInterval $subscriptionPeriod => subscription period
     *               \DateInterval $subscriptionInterval => subscription interval
     *               \DateInterval $cancellationInterval => subscriptio cancellation interval
     */
    public static function getSubscriptionPeriodAndIntervalsByProduct(\Cx\Modules\Pim\Model\Entity\Product $product, $renewalUnit, $renewalQuantifier)
    {
        $subscriptionInterval = \DateInterval::createFromDateString($renewalQuantifier .' '. $renewalUnit);
        
        // set subscription period
        $expirationInterval = \DateInterval::createFromDateString("{$product->getExpirationQuantifier()} {$product->getExpirationUnit()}");
        $subscriptionIntervalDateTime = new \DateTime();
        $expirationIntervalDateTime = clone $subscriptionIntervalDateTime;
        $subscriptionIntervalDateTime->add($subscriptionInterval);
        $expirationIntervalDateTime->add($expirationInterval);
        // in case the subscription interval is greater than the subscription period (expiration interval), then we shall extend the subscription period accordingly
        if ($subscriptionIntervalDateTime > $expirationIntervalDateTime) {
            $subscriptionPeriod = $subscriptionInterval;
        } else {
            $subscriptionPeriod = $expirationInterval;
        }

        // set cancellation period
        $cancellationInterval = \DateInterval::createFromDateString("{$product->getCancellationQuantifier()} {$product->getCancellationUnit()}");        
        
        return array($subscriptionPeriod, $subscriptionInterval, $cancellationInterval);
    }
    
    /**
     * Get Product renewalUnit and quantifier by using renewalOption
     * 
     * @param string $renewalOption renewalOption
     * @return array renewalUnit and quantifier
     */
    public static function getProductRenewalUnitAndQuantifier($renewalOption = 'monthly')
    {
        switch ($renewalOption) {
            case 'annually':
                $renewalUnit = \Cx\Modules\Pim\Model\Entity\Product::UNIT_YEAR;
                $renewalQuantifier = 1;
                break;
            case 'biannually':
                $renewalUnit = \Cx\Modules\Pim\Model\Entity\Product::UNIT_YEAR;
                $renewalQuantifier = 2;
                break;
            default:
                $renewalUnit = \Cx\Modules\Pim\Model\Entity\Product::UNIT_MONTH;
                $renewalQuantifier = 1;
                break;
        }
        return array($renewalUnit, $renewalQuantifier);
    }
    /**
     * Creates a new website
     * @param type $params  
     */
    public function createWebsite($params) {
// TODO: what do we actually need the language data for? We should load the language data at the certain place where it is actually being used
        self::loadLanguageData();
        
        $objFWUser   = \FWUser::getFWUserObject();
        $objUser     = $objFWUser->objUser->getUser(contrexx_input2raw($params['post']['userId']));
        $websiteId   = isset($params['post']['websiteId']) ? contrexx_input2raw($params['post']['websiteId']) : '';
        $websiteName = isset($params['post']['websiteName']) ? contrexx_input2raw($params['post']['websiteName']) : '';
        $themeId     = isset($params['post']['themeId']) ? contrexx_input2raw($params['post']['themeId']) : '';
        
        $basepath = \Cx\Core\Setting\Controller\Setting::getValue('websitePath','MultiSite');
        $websiteServiceServer = null;
        if (\Cx\Core\Setting\Controller\Setting::getValue('mode','MultiSite') == ComponentController::MODE_MANAGER) {
            //get default service server
            $defaultWebsiteServiceServer = \Env::get('em')->getRepository('Cx\Core_Modules\MultiSite\Model\Entity\WebsiteServiceServer')
            ->findBy(array('id' => \Cx\Core\Setting\Controller\Setting::getValue('defaultWebsiteServiceServer','MultiSite')));
            $websiteServiceServer = $defaultWebsiteServiceServer[0];
        }

        try {
            $objWebsite = new \Cx\Core_Modules\MultiSite\Model\Entity\Website($basepath, $websiteName, $websiteServiceServer, $objUser, false, $themeId);
            \Env::get('em')->persist($objWebsite);
            if ($websiteId) {
                $objWebsite->setId($websiteId);
                $metadata = \Env::get('em')->getClassMetaData(get_class($objWebsite));
                $metadata->setIdGeneratorType(\Doctrine\ORM\Mapping\ClassMetadata::GENERATOR_TYPE_NONE);
            }
            \Env::get('em')->flush();
            return $objWebsite->setup($params['post']['options']);
        } catch (\Cx\Core_Modules\MultiSite\Model\Entity\WebsiteException $e) {
            throw new MultiSiteJsonException(array(
                'log'       => \DBG::getMemoryLogs(),
                'message'   => $e->getMessage(),
            ));
        }
    }

    public function createUser($params) {
        try {
            if (empty($params['post'])) {
                throw new MultiSiteJsonException('Invalid arguments specified for command JsonMultiSite::createUser.');
            }

            $objUser = new \Cx\Core_Modules\MultiSite\Model\Entity\User();
            $userId = isset($params['post']['userId']) ? contrexx_input2raw($params['post']['userId']) : '';
            if (!empty($userId)) {
                $objFWUser = \FWUser::getFWUserObject();
                $objUserExist = $objFWUser->objUser->getUser($userId);
                if ($objUserExist) {
                    return array(
                        'userId' => $objUserExist->getId(),
                        'log' => \DBG::getMemoryLogs(),
                    );
                }
                $objUser->setMultiSiteId($userId);
            }
            $objUser->setEmail(!empty($params['post']['email']) ? contrexx_input2raw($params['post']['email']) : '');
            $objUser->setActiveStatus(!empty($params['post']['active']) ? (bool)$params['post']['active'] : false);
            $objUser->setAdminStatus(!empty($params['post']['admin']) ? (bool)$params['post']['admin'] : false);
            $objUser->setPassword(\User::make_password(8,true));
            
            if (!empty($params['post']['groups'])) {
                $objUser->setGroups($params['post']['groups']);
            }

            if (!$objUser->store()) {
                \DBG::msg('Adding user failed: '.$objUser->getErrorMsg());
                throw new MultiSiteJsonException($objUser->getErrorMsg());
            } else {
                \DBG::msg('User successfully created');
                return array(
                    'userId'=> $objUser->getId(),
                    'log'   => \DBG::getMemoryLogs(),
                );
            }
        } catch (\Exception $e) {
            throw new MultiSiteJsonException(array(
                'log'       => \DBG::getMemoryLogs(),
                'message'   => $e->getMessage(),
            ));
        }
    }

    /**
     * Create admin user
     * 
     * @global type $_ARRAYLANG
     * @param array $params
     * @return array
     * @throws MultiSiteJsonException
     */
    public function createAdminUser($params) {
        global $_ARRAYLANG;
        
        self::loadLanguageData();
        try {
            if (empty($params['post'])) {
                throw new MultiSiteJsonException($_ARRAYLANG['TXT_CORE_MODULE_MULTISITE_UNKOWN_USER_REQUEST']);
            }

            $data = $params['post'];

            $objUser = new \User();
            //Check email validity
            if (!\FWValidator::isEmail($data['multisite_user_account_email'])) {
                \DBG::log($_ARRAYLANG['TXT_CORE_MODULE_MULTISITE_INVALID_EMAIL']);
                throw new MultiSiteJsonException($_ARRAYLANG['TXT_CORE_MODULE_MULTISITE_INVALID_EMAIL']);
            }

            if (!\User::isUniqueEmail($data['multisite_user_account_email'])) {
                \DBG::log($_ARRAYLANG['TXT_CORE_MODULE_MULTISITE_EMAIL_IN_USE']);
                throw new MultiSiteJsonException($_ARRAYLANG['TXT_CORE_MODULE_MULTISITE_EMAIL_IN_USE']);
            }
            
            // set user account email
            if (isset($data['multisite_user_account_email'])) {
                $objUser->setEmail(trim(contrexx_input2raw($data['multisite_user_account_email'])));
            }
            
            // set profile data
            if (isset($data['multisite_user_profile_attribute'])) {
                $objUser->setProfile(contrexx_input2raw($data['multisite_user_profile_attribute']));
            }
            
            // set new plain text password
            if (!empty($data['multisite_user_account_password'])) {
                $password = contrexx_input2raw($data['multisite_user_account_password']);
                $confirmedPassword = !empty($data['multisite_user_account_password_confirmed']) ? contrexx_input2raw($data['multisite_user_account_password_confirmed']) : '';
                if (!$objUser->setPassword($password, $confirmedPassword)) {
                    \DBG::msg("JsonMultiSite (updateUser): Failed to update {$objUser->getId()}: ".join("\n", $objUser->getErrorMsg()));
                    throw new MultiSiteJsonException(array(
                        'object'    => 'password',
                        'type'      => 'danger',
                        'message'   => join("\n", $objUser->getErrorMsg()),
                        'log'       => \DBG::getMemoryLogs(),
                    ));
                }
            }
            
            //Set admin flag of User to true
            $objUser->setAdminStatus(1);
            
            $objUser->setActiveStatus(1);

            if (!$objUser->store()) {
                \DBG::log('Adding Admin user failed: ' . $objUser->getErrorMsg());
                throw new MultiSiteJsonException($objUser->getErrorMsg());
            }
            \DBG::log("JsonMultiSite (createAdminUser): User has been successfully added.");
            return array(
                'status' => 'success',
                'log' => \DBG::getMemoryLogs(),
            );
        } catch (\Exception $e) {
            throw new MultiSiteJsonException($e->getMessage());
        }
    }

    /**
     * Update an existing user account
     * @param array $params POST-data based on with the account shall be updated to
     * @return  boolean     TRUE on success, FALSE on failure.
     */
    public function updateUser($params) {
        global $_ARRAYLANG;
        
        if (empty($params['post'])) {
            throw new MultiSiteJsonException(array(
                'object'    => 'form',
                'type'      => 'danger',
                'message'   => 'No data supplied',
            ));
        }

        $objFWUser = \FWUser::getFWUserObject();
        $data = $params['post'];
        
        switch(\Cx\Core\Setting\Controller\Setting::getValue('mode','MultiSite')) {
            case \Cx\Core_Modules\MultiSite\Controller\ComponentController::MODE_MANAGER:
            case \Cx\Core_Modules\MultiSite\Controller\ComponentController::MODE_HYBRID:
            case \Cx\Core_Modules\MultiSite\Controller\ComponentController::MODE_SERVICE:
                $objUser = $objFWUser->objUser->getUser(intval($params['post']['userId']), true);
                break;

            case \Cx\Core_Modules\MultiSite\Controller\ComponentController::MODE_WEBSITE:
                if(isset($params['post']['websiteUserId'])){
                    $websiteUserId = $params['post']['websiteUserId'];
                } else {
                    $websiteUserId = \Cx\Core\Setting\Controller\Setting::getValue('websiteUserId','MultiSite');
                }
                $objUser = $objFWUser->objUser->getUser(intval($websiteUserId), true);
                break;

            default:
                break;
        }

        if (!$objUser) {
            throw new MultiSiteJsonException(array(
                'object'    => 'form',
                'type'      => 'danger',
                'message'   => 'Unknown user account',
                'log'       => \DBG::getMemoryLogs(),
            ));
        }
        
        // set account data
        if (isset($data['multisite_user_account_username'])) {
            $objUser->setUsername(trim(contrexx_input2raw($data['multisite_user_account_username'])));
        }
        if (isset($data['multisite_user_account_email'])) {
            $objUser->setEmail(trim(contrexx_input2raw($data['multisite_user_account_email'])));
        }
        if (isset($data['multisite_user_account_frontend_language'])) {
            $objUser->setFrontendLanguage(intval($data['multisite_user_account_frontend_language']));
        }
        if (isset($data['multisite_user_account_backend_language'])) {
            $objUser->setBackendLanguage(intval($data['multisite_user_account_backend_language']));
        }
        if (isset($data['multisite_user_account_email_access']) && $objUser->isAllowedToChangeEmailAccess()) {
            $objUser->setEmailAccess(contrexx_input2raw($data['multisite_user_account_email_access']));
        }
        if (isset($data['multisite_user_account_profile_access']) && $objUser->isAllowedToChangeProfileAccess()) {
            $objUser->setProfileAccess(contrexx_input2raw($data['multisite_user_account_profile_access']));
        }
        if (isset($data['multisite_user_account_verified'])) {
            $objUser->setVerification((boolean)intval($data['multisite_user_account_verified']));
        }
        if (isset($data['multisite_user_account_restore_key'])) {
            $objUser->setRestoreKey(contrexx_input2raw($data['multisite_user_account_restore_key']));
        }
        if (isset($data['multisite_user_account_restore_key_time'])) {
            $objUser->setRestoreKeyTime(intval($data['multisite_user_account_restore_key_time']), true);
        }

        if(isset($customerType)) {
            // if id is null, there is no crm user, so we create one
            if ($this->contact->id === null) {
                \Cx\Modules\Crm\Controller\CrmLibrary::addCrmContactFromAccessUser($objUser);
            }
        }

        // set profile data
        if (isset($data['multisite_user_profile_attribute'])) {
            $objUser->setProfile(contrexx_input2raw($data['multisite_user_profile_attribute']));
        }
        // set md5 hashed password
        if (isset($data['multisite_user_md5_password'])) {
            $objUser->setHashedPassword(contrexx_input2raw($data['multisite_user_md5_password']));
        }

        // set new plain text password
        if (!empty($data['multisite_user_account_password'])) {
            $password = contrexx_input2raw($data['multisite_user_account_password']);
            $confirmedPassword = !empty($data['multisite_user_account_password_confirmed']) ? contrexx_input2raw($data['multisite_user_account_password_confirmed']) : '';
            if (!$objUser->setPassword($password, $confirmedPassword)) {
                \DBG::msg("JsonMultiSite (updateUser): Failed to update {$objUser->getId()}: ".join("\n", $objUser->getErrorMsg()));
                throw new MultiSiteJsonException(array(
                    'object'    => 'password',
                    'type'      => 'danger',
                    'message'   => join("\n", $objUser->getErrorMsg()),
                    'log'       => \DBG::getMemoryLogs(),
                ));
            }
        }

        if (!$objUser->store()) {
            \DBG::msg("JsonMultiSite (updateUser): Failed to update {$objUser->getId()}: ".join("\n", $objUser->getErrorMsg()));
            throw new MultiSiteJsonException(array(
                'object'    => 'form',
                'type'      => 'danger',
                'message'   => join("\n", $objUser->getErrorMsg()),
                'log'       => \DBG::getMemoryLogs(),
            ));
        }
                
        \DBG::msg("JsonMultiSite (updateUser): User {$objUser->getId()} successfully updated.");
        return array(
            'status'    => 'success',
            'log'       => \DBG::getMemoryLogs(),
            'message'   => $_ARRAYLANG['TXT_CORE_MODULE_MULTISITE_USER_PROFILE_UPDATED_SUCCESS'],
            'reload'    => ($params['post']['reload'] ? true : false ),
        );
    }
    
    /**
     * Verify the owner's email address is isUniqueEmail
     * 
     * @param array $params POST-data based on with the account to verify the email
     * 
     * @return array
     * @throws MultiSiteJsonException
     */
    public function isUniqueEmail($params)
    {
        $data = $params['post'];
        
        if (empty($data)) {
            throw new MultiSiteJsonException(array(
                'object'    => 'form',
                'type'      => 'danger',
                'message'   => 'No data supplied',
            ));
        }
        
        if (!isset($data['currentEmail']) || !isset($data['newEmail'])) {
            throw new MultiSiteJsonException(array(
                'object'    => 'form',
                'type'      => 'danger',
                'message'   => 'Unknown user email id',
            ));
        }
        
        switch(\Cx\Core\Setting\Controller\Setting::getValue('mode','MultiSite')) {
            case \Cx\Core_Modules\MultiSite\Controller\ComponentController::MODE_MANAGER:
            case \Cx\Core_Modules\MultiSite\Controller\ComponentController::MODE_HYBRID:
                $objUser = \FWUser::getFWUserObject()->objUser->getUsers(array('email' => $data['currentEmail']));
                break;
            default:
                break;
        }
        
        if (!$objUser) {
            throw new MultiSiteJsonException(array(
                'object'    => 'form',
                'type'      => 'danger',
                'message'   => 'Unknown user account',
            ));
        }
        
        if (!\User::isUniqueEmail($data['newEmail'], $objUser->getId())) {
            return array('status' => 'error', 'customerPanelDomain' => \Cx\Core\Setting\Controller\Setting::getValue('customerPanelDomain','MultiSite'));
        }
    
        return array('status' => 'success');
    }
    
    /**
     * Update the user account of the signed-in user
     * @param array $params POST-data based on with the account shall be updated to
     * @return  boolean     TRUE on success, FALSE on failure.
     */
    public function updateOwnUser($params) {
        $objFWUser = \FWUser::getFWUserObject();
        $objUser = $objFWUser->objUser;
        if (!$objUser->login()) {
            throw new MultiSiteJsonException(array(
                'object'    => 'form',
                'type'      => 'danger',
                'message'   => 'Operation denied',
            ));
        }

        // NOTE: This command is used in frontend,
        //       Therefore, all users that are logged-in
        //       are allowed to update their profile.
        //       The following permission check has been
        //       deactivated therefore.
        //
        // Only administrators or users with sufficient permissions
        // may update their own account.
        /*if (!\Permission::hasAllAccess() && !\Permission::checkAccess(31, 'static', true)) {
            throw new MultiSiteJsonException(array(
                'object'    => 'form',
                'type'      => 'danger',
                'message'   => 'Operation denied',
            ));
        }*/

        $params['post']['userId'] = $objUser->getId();
        return $this->updateUser($params);
    } 

    /**
     *  callback authentication for verifing secret key and installation id based on mode
     * 
     * @return boolean
     */
    public function auth(array $params = array()) {
        $authenticationValue = isset($params['post']['auth']) ? json_decode($params['post']['auth'], true) : '';

        if (   empty($authenticationValue)
            || !is_array($authenticationValue)
            || !isset($authenticationValue['sender'])
            || !isset($authenticationValue['key'])
        ) {
            return false;
        }
        $config = \Env::get('config');
        $installationId = $config['installationId'];
        switch (\Cx\Core\Setting\Controller\Setting::getValue('mode','MultiSite')) {
            case ComponentController::MODE_MANAGER:
                try {
                    $WebsiteServiceServerRepository = \Env::get('em')->getRepository('\Cx\Core_Modules\MultiSite\Model\Entity\WebsiteServiceServer');
                    $objWebsiteService = $WebsiteServiceServerRepository->findOneBy(array('hostname' => $authenticationValue['sender']));
                    if (!$objWebsiteService) {
                        return false;
                    }
                    $secretKey = $objWebsiteService->getSecretKey();
                } catch(\Exception $e) {
                    \DBG::msg($e->getMessage());
                    return false;
                }
                break;

            case ComponentController::MODE_SERVICE:
            case ComponentController::MODE_HYBRID:
                //Check if the sender is manager or not
                if ($authenticationValue['sender'] == \Cx\Core\Setting\Controller\Setting::getValue('managerHostname','MultiSite')) {
                    $secretKey = \Cx\Core\Setting\Controller\Setting::getValue('managerSecretKey','MultiSite');
                } else {
                    try {
                        $domainRepo = \Env::get('em')->getRepository('Cx\Core_Modules\MultiSite\Model\Entity\Domain');
                        $domain     = $domainRepo->findOneBy(array('name' => $authenticationValue['sender']));
                        if (!$domain || !$domain->getWebsite()) {
                            return false;
                        }
                        $secretKey  = $domain->getWebsite()->getSecretKey();
                    } catch (\Exception $e) {
                        \DBG::msg($e->getMessage());
                        return false;
                    }
                }
                break;
                
            case ComponentController::MODE_WEBSITE:
                $secretKey = \Cx\Core\Setting\Controller\Setting::getValue('serviceSecretKey','MultiSite');
                break;
        }
        
        if (md5($secretKey.$installationId) !== $authenticationValue['key']) {
            return false;
        }

        // register request as intersystem communication request (ISC)
        self::$isIscRequest = true;

        // activate memory-logging
        $this->activateDebuggingToMemory();

        return true;
    }

    /**
     * callback authentication for verifing website owner
     * 
     * @return boolean
     */
    public function  verifyWebsiteOwnerOrIscRequest($params)
    {
        //check the authentication for verifing secret key and installation id based on mode
        if ($this->auth($params)) {
            return true;
        }
        
        if (!isset($params['post']['websiteId'])) {
            return false;
        }
        
        //check the website exists or not based on $websiteId
        $website = \Env::get('em')->getRepository('Cx\Core_Modules\MultiSite\Model\Entity\Website')->findOneBy(array('id' => $params['post']['websiteId']));
        if (!$website) {
            \DBG::log('JsonMultiSite::verifyWebsiteOwnerOrIscRequest() failed: Unkown Website-ID: '.$params['post']['websiteId']);
            return false;
        }
        
        //check the user logged in or not
        if (!ComponentController::isUserLoggedIn()) {
            return false;
        }
        
        //check the logged in user is owner of website($websiteId)
        if ($website->getOwner()->getId() == \FWUser::getFWUserObject()->objUser->getId()) {
            return true;
        }
        return false;
    }
    
    /**
     * Callback authentication for checking the user's access permission
     * 
     * @return boolean
     */
    public function checkPermission() 
    {
        if (\Permission::checkAccess(183, 'static', true)) {
            return true;
        }
        
        return false;
    }
    
    /**
     *  Get the Authentication Object
     * 
     * @param String $secretKey
     * @param String $remoteInstallationId
     * 
     * @return json
     */
    public static function getAuthenticationObject($secretKey, $remoteInstallationId) 
    {
        $key = md5($secretKey . $remoteInstallationId);
        
        return json_encode(array(
            'key'     => $key,
            'sender' => ComponentController::$cxMainDomain,
        ));
    }
    /**
     *  Get the auto-generated SecretKey
     * 
     * @return string 
     */
    public static function generateSecretKey(){
        return bin2hex(openssl_random_pseudo_bytes(16));    
    }
    
    /**
     * Map the website domain
     * 
     * @param type $params
     * @return type
     */
    public function mapDomain($params) {
        global $_ARRAYLANG;
        if (   empty($params['post'])
            || empty($params['post']['domainName'])
            || empty($params['post']['auth'])
            || empty($params['post']['componentType'])
            || !isset($params['post']['componentId'])
            || !isset($params['post']['coreNetDomainId'])
        ) {
            throw new MultiSiteJsonException('JsonMultiSite::mapDomain() failed: Insufficient mapping information supplied.');
        }

        $authenticationValue = json_decode($params['post']['auth'], true);
        if (empty($authenticationValue) || !is_array($authenticationValue)) {
            throw new MultiSiteJsonException('JsonMultiSite::mapDomain() failed: Insufficient mapping information supplied.');
        }
        //check the black listed domains
        if (!self::checkBlackListDomains($params['post']['domainName'])) {
            \DBG::log('JsonMultiSite::mapDomain() failed: ' . $params['post']['domainName'] . '(Domain name is black listed).');
            throw new MultiSiteJsonException(sprintf($_ARRAYLANG['TXT_CORE_MODULE_MULTISITE_MAPPING_DOMAIN_FAILED'], $params['post']['domainName']));
        }

        try {
            //Check the domain name is subdomain of the domain or not.
            if ($this->isDomainASubDomainOfMultisiteDomain($params['post']['domainName'])) {
                \DBG::log('JsonMultiSite::mapDomain() failed: The domain name is a subdomain of multiSite Domain');
                throw new MultiSiteJsonException(sprintf($_ARRAYLANG['TXT_CORE_MODULE_MULTISITE_MAPPING_DOMAIN_FAILED'], $params['post']['domainName']));
            }
            
            $domainRepo = \Env::get('em')->getRepository('Cx\Core_Modules\MultiSite\Model\Entity\Domain');
            
            if ($domainRepo->findOneBy(array('name' => $params['post']['domainName']))) {
                return array(
                    'status' => 'error',
                    'log'    => \DBG::getMemoryLogs(),
                );
            }
            // create a new domain entity that shall be used for the mapping
            $objDomain = new \Cx\Core_Modules\MultiSite\Model\Entity\Domain($params['post']['domainName']);
            
            switch ($params['post']['componentType']) {
                case ComponentController::MODE_SERVICE:
                    // If componentType is MODE_SERVICE, then we are about to
                    // map the domain to a Service Server. Therefore, we have
                    // to fetch the ID of the Service Server the domain shall
                    // be mapped to.
                    $websiteServiceServer = \Env::get('em')->getRepository('Cx\Core_Modules\MultiSite\Model\Entity\WebsiteServiceServer')
                        ->findOneBy(array('hostname' => $authenticationValue['sender']));
                    if (!$websiteServiceServer) {
                        throw new MultiSiteJsonException('JsonMultiSite::mapDomain() failed: Unkown Service Server: '.$authenticationValue['sender']);
                    }
                    $componentId = $websiteServiceServer->getId();
                    break;

                case ComponentController::MODE_WEBSITE:
                    switch (\Cx\Core\Setting\Controller\Setting::getValue('mode','MultiSite')) {
                        case ComponentController::MODE_MANAGER:
                            // componentId is the ID of a Website that the domain shall be mapped to
                            $componentId = $params['post']['componentId'];
                            $website = \Env::get('em')->getRepository('Cx\Core_Modules\MultiSite\Model\Entity\Website')->findOneById($componentId);
                            if (!$website) {
                                throw new MultiSiteJsonException('JsonMultiSite::mapDomain() failed: Unkown Website-ID: '.$componentId);
                            }
                            break;

                        case ComponentController::MODE_HYBRID:
                        case ComponentController::MODE_SERVICE:
                            // componentId is the ID of the Website the request was made from
                            $objWebsiteDomain = $domainRepo->findOneBy(array('name' => $authenticationValue['sender']));
                            if (!$objWebsiteDomain) {
                                throw new MultiSiteJsonException('JsonMultiSite::mapDomain() failed: Unkown Website: '.$authenticationValue['sender']);
                            }
                            $website = $objWebsiteDomain->getWebsite();
                            if (!$website) {
                                throw new MultiSiteJsonException('JsonMultiSite::mapDomain() failed: Unkown Website: '.$authenticationValue['sender']);
                            }
                            $componentId = $website->getId();
                            break;

                        default:
                            throw new MultiSiteJsonException('JsonMultiSite::mapDomain() failed: Command not available for mode: '.\Cx\Core\Setting\Controller\Setting::getValue('mode','MultiSite'));
                            break;
                    }

                    $website->mapDomain($objDomain);
// TODO: will this trigger a request to the service server, then update the domains there and then again trigger a request back to the manager and will then end in an infinite loop????
                    \Env::get('em')->persist($website);
                    break;

                case ComponentController::MODE_MANAGER:
                case ComponentController::MODE_HYBRID:
                default:
                    // If componentType is MANAGER or HYBRID, then we are about to map
                    // a Net-domain to the own system. Therefore setting componentId to null 
                    // will reference the domain to be mapped to ourself.
                    // Using NULL instead of 0 is important. As to the database scheme
                    // the domain map/unmap process would not work properly, if we use 0 at this point.
                    $componentId = null;
                    break;
            }

            $objDomain->setComponentType($params['post']['componentType']);
            $objDomain->setComponentId($componentId);
            $objDomain->setCoreNetDomainId($params['post']['coreNetDomainId']);
            \Env::get('em')->persist($objDomain);
            \Env::get('em')->flush();
            return array(
                'status' => 'success',
                'log'    => \DBG::getMemoryLogs(),
            );
        } catch (\Exception $e) {
            throw new MultiSiteJsonException($e->getMessage());
        }
    }        
    
    /**
     * Map Net Domain
     * 
     * @param  array $params supplied arguments from JsonData-request
     * 
     * @return array mapNetDomain stats 
     * @throws MultiSiteJsonException
     */
    public function mapNetDomain($params) {
        
        global $_ARRAYLANG;
        
        self::loadLanguageData();
        
        if (empty($params['post']['domainName'])) {
            \DBG::log('JsonMultiSite::mapNetDomain() failed: domainName is empty.');
            throw new MultiSiteJsonException($_ARRAYLANG['TXT_CORE_MODULE_MULTISITE_WEBSITE_DOMAIN_UNKNOWN']);
        }
        try {
            switch (\Cx\Core\Setting\Controller\Setting::getValue('mode','MultiSite')) {
                
                case ComponentController::MODE_WEBSITE:
                    $domain = new \Cx\Core\Net\Model\Entity\Domain();
                    $domain->setName(contrexx_input2raw($params['post']['domainName']));
                
                    $domainRepository = \Env::get('em')->getRepository('Cx\Core\Net\Model\Entity\Domain');
                    $domainRepository->add($domain);
                    $domainRepository->flush();
                    return array(
                        'status' => 'success'
                    );
                    break;
            }
        } catch (\Exception $e) {
            \DBG::log('JsonMultiSite::mapNetDomain() failed:'. $e->getMessage());
            throw new MultiSiteJsonException('JsonMultiSite::mapNetDomain() failed:'. $e->getMessage());
        }
    }

    /**
     * UnMap Net Domain
     * 
     * @param  array $params supplied arguments from JsonData-request
     * 
     * @return array unMapNetDomain stats
     * @throws MultiSiteJsonException
     */
    public function unMapNetDomain($params) {
       
        global $_ARRAYLANG;
        
        self::loadLanguageData();
        
        if (empty($params['post']['domainId'])) {
            \DBG::log('JsonMultiSite::unMapNetDomain() failed: domainId is empty.');
            throw new MultiSiteJsonException($_ARRAYLANG['TXT_CORE_MODULE_MULTISITE_WEBSITE_DOMAIN_UNKNOWN']);
        }
        try {
            switch (\Cx\Core\Setting\Controller\Setting::getValue('mode','MultiSite')) {
                
                case ComponentController::MODE_WEBSITE:
                    $domainRepo = new \Cx\Core\Net\Model\Repository\DomainRepository();
                    $domain = $domainRepo->findOneBy(array('id' => contrexx_input2raw($params['post']['domainId'])));
                    
                    if (!$domain) {
                       \DBG::log('JsonMultiSite::unMapNetDomain() failed: Unknown DomainId:'. $params['post']['domainId']);
                       throw new MultiSiteJsonException($_ARRAYLANG['TXT_CORE_MODULE_MULTISITE_WEBSITE_DOMAIN_UNKNOWN']);
                    }
                    
                    $domainRepository = \Env::get('em')->getRepository('Cx\Core\Net\Model\Entity\Domain');
                    $domainRepository->remove($domain);
                    $domainRepository->flush();
                    return array(
                        'status' => 'success'
                    );
                    break;
            }
        } catch (\Exception $e) {
            \DBG::dump('JsonMultiSite::unMapNetDomain() failed:'. $e->getMessage());
            throw new MultiSiteJsonException('JsonMultiSite::unMapNetDomain() failed:'. $e->getMessage());
        }
    }

    
    /**
     * Update Net Domain
     * 
     * @param  array $params supplied arguments from JsonData-request
     * 
     * @return array updateNetDomain stats
     * @throws MultiSiteJsonException
     */
    public function updateNetDomain($params)
    {
        global $_ARRAYLANG;
        
        self::loadLanguageData();
        
        if (empty($params['post']['domainName']) || empty($params['post']['domainId'])) {
            \DBG::log('JsonMultiSite::updateNetDomain() failed: domainName or domainId is empty.');
            throw new MultiSiteJsonException($_ARRAYLANG['TXT_CORE_MODULE_MULTISITE_WEBSITE_DOMAIN_UNKNOWN']);
        }
        try {
            switch (\Cx\Core\Setting\Controller\Setting::getValue('mode','MultiSite')) {
                case ComponentController::MODE_WEBSITE:
                    $domainRepo  = \Env::get('em')->getRepository('Cx\Core\Net\Model\Entity\Domain');
                    $objDomain   = $domainRepo->findOneBy(array('id' => $params['post']['domainId']));
                    if (!$objDomain) {
                       \DBG::log('JsonMultiSite::updateNetDomain() failed: Unknown DomainId:'. $params['post']['domainId']);
                       throw new MultiSiteJsonException($_ARRAYLANG['TXT_CORE_MODULE_MULTISITE_WEBSITE_DOMAIN_UNKNOWN']);
                    }
                    $objDomain->setName(contrexx_input2raw($params['post']['domainName']));
                    $domainRepo->flush();
                    return array(
                        'status' => 'success'
                    );
                    break;
            }
        } catch (\Exception $e) {
            \DBG::dump('JsonMultiSite::updateNetDomain() failed:'. $e->getMessage());
            throw new MultiSiteJsonException('JsonMultiSite::updateNetDomain() failed:'. $e->getMessage());
        }
    }

    /**
     * Unmap the website domain
     * 
     * @param type $params
     * @return type
     */
    public function unMapDomain($params)
    {
        if (   empty($params['post'])
            || empty($params['post']['domainName'])
            || empty($params['post']['auth'])
            || empty($params['post']['componentType'])
            || !isset($params['post']['componentId'])
            //|| !isset($params['post']['coreNetDomainId'])
        ) {
            throw new MultiSiteJsonException('JsonMultiSite::unMapDomain() failed: Insufficient mapping information supplied.');
        }

        $authenticationValue = json_decode($params['post']['auth'], true);
        if (empty($authenticationValue) || !is_array($authenticationValue)) {
            throw new MultiSiteJsonException('JsonMultiSite::unMapDomain() failed: Insufficient mapping information supplied.');
        }

        try {
            $website = null;

            $domainRepo = \Env::get('em')->getRepository('Cx\Core_Modules\MultiSite\Model\Entity\Domain');
            switch ($params['post']['componentType']) {
                case ComponentController::MODE_SERVICE:
                    // If componentType is MODE_SERVICE, then we are about to
                    // unmap the domain from a Service Server. Therefore, we have
                    // to fetch the ID of the Service Server the domain shall
                    // be unmapped from.
                    $websiteServiceServer = \Env::get('em')->getRepository('Cx\Core_Modules\MultiSite\Model\Entity\WebsiteServiceServer')
                        ->findOneBy(array('hostname' => $authenticationValue['sender']));
                    if (!$websiteServiceServer) {
                        throw new MultiSiteJsonException('JsonMultiSite::unMapDomain() failed: Unkown Service Server: '.$authenticationValue['sender']);
                    }
                    $componentId = $websiteServiceServer->getId();
                    break;

                case ComponentController::MODE_WEBSITE:
                    switch (\Cx\Core\Setting\Controller\Setting::getValue('mode','MultiSite')) {
                        case ComponentController::MODE_MANAGER:
                            // componentId is the ID of a Website that the domain shall be unmapped from
                            $componentId = $params['post']['componentId'];
                            $website = \Env::get('em')->getRepository('Cx\Core_Modules\MultiSite\Model\Entity\Website')->findOneById($componentId);
                            if (!$website) {
                                throw new MultiSiteJsonException('JsonMultiSite::unMapDomain() failed: Unkown Website-ID: '.$componentId);
                            }
                            break;

                        case ComponentController::MODE_HYBRID:
                        case ComponentController::MODE_SERVICE:
                            // componentId is the ID of the Website the request was made from
                            $objWebsiteDomain = $domainRepo->findOneBy(array('name' => $authenticationValue['sender']));
                            if (!$objWebsiteDomain) {
                                throw new MultiSiteJsonException('JsonMultiSite::unMapDomain() failed: Unkown Website: '.$authenticationValue['sender']);
                            }
                            $website = $objWebsiteDomain->getWebsite();
                            if (!$website) {
                                throw new MultiSiteJsonException('JsonMultiSite::unMapDomain() failed: Unkown Website: '.$authenticationValue['sender']);
                            }
                            $componentId = $website->getId();
                            break;

                        default:
                            throw new MultiSiteJsonException('JsonMultiSite::unMapDomain() failed: Command not available for mode: '.\Cx\Core\Setting\Controller\Setting::getValue('mode','MultiSite'));
                            break;
                    }
                    break;

                case ComponentController::MODE_MANAGER:
                case ComponentController::MODE_HYBRID:
                default:
                    // If componentType is MANAGER or HYBRID, then we are about to unmap
                    // a Net-domain from the own system. Therefore setting componentId to null 
                    // will reference the domain to be mapped to ourself.
                    $componentId = null;
                    break;
            }
            $critieria = array(
                'name'              => $params['post']['domainName'],
                'componentType'     => $params['post']['componentType'],
                'componentId'       => $componentId,
                'type'              => $params['post']['type'],
            );
            if (isset($params['post']['coreNetDomainId'])) {
                $critieria['coreNetDomainId'] = $params['post']['coreNetDomainId'];
            }
            $objDomain = $domainRepo->findOneBy($critieria);

            if (!$objDomain) {
                throw new MultiSiteJsonException('JsonMultiSite::unMapDomain() failed: Domain to remove not found.');
            }

            if ($website && $objDomain->getWebsite() == $website) {
                // Website::unMapDomain() does also call remove() on the entity manager
                $website->unMapDomain($objDomain);
            } else {
                \Env::get('em')->remove($objDomain);
            }
            \Env::get('em')->flush();
            return array(
                'status' => 'success',
                'log'    => \DBG::getMemoryLogs(),
            );
        } catch (\Exception $e) {
            throw new MultiSiteJsonException($e->getMessage());
        }
    }

    /**
     * update the default codeBase
     * 
     * @param array $params
     * 
     * @return string
     */
    public function updateDefaultCodeBase($params) 
    {
        global $_ARRAYLANG;
        self::loadLanguageData();
        if (!empty($params['post'])) {
            \Cx\Core\Setting\Controller\Setting::init('MultiSite', 'websiteSetup','FileSystem');
            \Cx\Core\Setting\Controller\Setting::set('defaultCodeBase',$params['post']['defaultCodeBase']);
            if (\Cx\Core\Setting\Controller\Setting::update('defaultCodeBase')) {
                return $_ARRAYLANG['TXT_CORE_MODULE_MULTISITE_DEFAULTCODEBASE_SUCCESSFUL_CREATION'];
            }
        }
    }
    /**
     * update the domain alias
     * 
     * @param array $params
     * 
     * @return string
     */
    public function updateDomain($params) {
        global $_ARRAYLANG;
        if (   empty($params['post'])
            || empty($params['post']['domainName'])
            || empty($params['post']['auth'])
            || empty($params['post']['componentType'])
            || !isset($params['post']['componentId'])
            //|| !isset($params['post']['coreNetDomainId'])
        ) {
            \DBG::dump($params);
            throw new MultiSiteJsonException('JsonMultiSite::updateDomain() on '.\Cx\Core\Setting\Controller\Setting::getValue('mode','MultiSite').' failed: Insufficient mapping information supplied: '.var_export($params, true));
        }

        $authenticationValue = json_decode($params['post']['auth'], true);
        if (empty($authenticationValue) || !is_array($authenticationValue)) {
            \DBG::dump($params);
            throw new MultiSiteJsonException('JsonMultiSite::updateDomain() on '.\Cx\Core\Setting\Controller\Setting::getValue('mode','MultiSite').' failed: Insufficient mapping information supplied.'.var_export($params, true));
        }
        //check the black listed domains
        if (!self::checkBlackListDomains($params['post']['domainName'])) {
            \DBG::log('JsonMultiSite::updateDomain() failed: ' . $params['post']['domainName'] . '(Domain name is black listed).');
            throw new MultiSiteJsonException(sprintf($_ARRAYLANG['TXT_CORE_MODULE_MULTISITE_MAPPING_DOMAIN_FAILED'], $params['post']['domainName']));
        }
        
        try {
            //Check the domain name is subdomain of the domain or not.
            if ($this->isDomainASubDomainOfMultisiteDomain($params['post']['domainName'])) {
                \DBG::log('JsonMultiSite::updateDomain() failed: The domain name is a subdomain of multiSite Domain');
                throw new MultiSiteJsonException(sprintf($_ARRAYLANG['TXT_CORE_MODULE_MULTISITE_MAPPING_DOMAIN_FAILED'], $params['post']['domainName']));
            }
            
            $website = null;
            
            $domainRepo = \Env::get('em')->getRepository('Cx\Core_Modules\MultiSite\Model\Entity\Domain');
            if ($domainRepo->findOneBy(array('name' => $params['post']['domainName']))) {
                return array(
                    'status' => 'error',
                    'log'    => \DBG::getMemoryLogs(),
                );
            }
            switch ($params['post']['componentType']) {
                case ComponentController::MODE_SERVICE:
                    // If componentType is MODE_SERVICE, then we are about to
                    // update the domain of a Service Server. Therefore, we have
                    // to fetch the ID of the Service Server the domain shall
                    // be updated of.
                    $websiteServiceServer = \Env::get('em')->getRepository('Cx\Core_Modules\MultiSite\Model\Entity\WebsiteServiceServer')
                        ->findOneBy(array('hostname' => $authenticationValue['sender']));
                    if (!$websiteServiceServer) {
                        throw new MultiSiteJsonException('JsonMultiSite::updateDomain() failed: Unkown Service Server: '.$authenticationValue['sender']);
                    }
                    $componentId = $websiteServiceServer->getId();
                    break;

                case ComponentController::MODE_WEBSITE:
                    switch (\Cx\Core\Setting\Controller\Setting::getValue('mode','MultiSite')) {
                        case ComponentController::MODE_MANAGER:
                            // componentId is the ID of a Website that the domain shall be updated of
                            $componentId = $params['post']['componentId'];
                            $website = \Env::get('em')->getRepository('Cx\Core_Modules\MultiSite\Model\Entity\Website')->findOneById($componentId);
                            if (!$website) {
                                throw new MultiSiteJsonException('JsonMultiSite::updateDomain() failed: Unkown Website-ID: '.$componentId);
                            }
                            break;

                        case ComponentController::MODE_HYBRID:
                        case ComponentController::MODE_SERVICE:
                            // componentId is the ID of the Website the request was made from
                            $objWebsiteDomain = $domainRepo->findOneBy(array('name' => $authenticationValue['sender']));
                            if (!$objWebsiteDomain) {
                                throw new MultiSiteJsonException('JsonMultiSite::updateDomain() failed: Unkown Website: '.$authenticationValue['sender']);
                            }
                            $website = $objWebsiteDomain->getWebsite();
                            if (!$website) {
                                throw new MultiSiteJsonException('JsonMultiSite::updateDomain() failed: Unkown Website: '.$authenticationValue['sender']);
                            }
                            $componentId = $website->getId();
                            break;

                        default:
                            throw new MultiSiteJsonException('JsonMultiSite::updateDomain() failed: Command not available for mode: '.\Cx\Core\Setting\Controller\Setting::getValue('mode','MultiSite'));
                            break;
                    }
                    break;

                case ComponentController::MODE_MANAGER:
                case ComponentController::MODE_HYBRID:
                default:
                    // If componentType is MANAGER or HYBRID, then we are about to update
                    // a Net-domain from the own system. Therefore setting componentId to null 
                    // will reference the domain to be mapped to ourself.
                    $componentId = null;
                    break;
            }
            $critieria = array(
                'componentType'     => $params['post']['componentType'],
                'componentId'       => $componentId,
                'type'              => $params['post']['type'],
            );
            if (isset($params['post']['coreNetDomainId'])) {
                $critieria['coreNetDomainId'] = $params['post']['coreNetDomainId'];
            }
            $objDomain = $domainRepo->findOneBy($critieria);

            if (!$objDomain) {
                throw new MultiSiteJsonException('JsonMultiSite::updateDomain() failed: Domain to update not found.');
            }

            $objDomain->setName($params['post']['domainName']);
            \Env::get('em')->flush();
            return array(
                'status' => 'success',
                'log'    => \DBG::getMemoryLogs(),
            );
        } catch (\Exception $e) {
            throw new MultiSiteJsonException($e->getMessage());
        }
    }
    
    /**
     * check the black listed domains
     * 
     * @param string $domainName
     * @return boolean
     */
    public static function checkBlackListDomains($domainName) {
        if (empty($domainName)) {
            return false;
        }
        if (in_array($domainName, array_map('trim', explode(',', \Cx\Core\Setting\Controller\Setting::getValue('domainBlackList','MultiSite'))))) {
            return false;
        }
        return true;
    }

    /**
     * set Website State
     * 
     * @param array $params
     * 
     */
    public function setWebsiteState($params) {
         if (!empty($params['post'])) {
            $webRepo = \Env::get('em')->getRepository('Cx\Core_Modules\MultiSite\Model\Entity\Website');
            $website = $webRepo->findOneById($params['post']['websiteId']);
            if (!$website) {
                throw new MultiSiteJsonException('JsonMultiSite::setWebsiteState() failed: Website by ID '.$params['post']['websiteId'].' not found.');
            }
            $website->setStatus($params['post']['status']);
            \Env::get('em')->persist($website);
            \Env::get('em')->flush();
            return true;
        }
    }
    
    /**
     * update Website State
     * 
     * @param array $params
     * 
     */
    public function updateWebsiteState($params) {
        global $_ARRAYLANG;
        self::loadLanguageData();

        if (!empty($params['post'])) {
            switch (\Cx\Core\Setting\Controller\Setting::getValue('mode','MultiSite')) {
                case ComponentController::MODE_MANAGER:
                case ComponentController::MODE_HYBRID:
                    if ($this->setWebsiteState($params)){
                        return $_ARRAYLANG['TXT_CORE_MODULE_MULTISITE_WEBSITE_STATUS_CHANGED_SUCCESSFUL'];
                    }
                    break;

                default:
                    break;
            }
        }
    }
    
    /**
     * updateOwnWebsiteState
     * 
     * @param  array $params supplied arguments from JsonData-request
     * 
     * @return array
     */
    public function updateOwnWebsiteState($params) 
    {
        global $_ARRAYLANG;
        self::loadLanguageData();
        
        if (\FWValidator::isEmpty($params) || \FWValidator::isEmpty($params['post']['websiteId'])) {
            return array('status' => 'error', 'message' => $_ARRAYLANG['TXT_CORE_MODULE_MULTISITE_WEBSITE_STATUS_FAILED']);
        }

        try {
            switch (\Cx\Core\Setting\Controller\Setting::getValue('mode','MultiSite')) {
                case ComponentController::MODE_MANAGER:
                    $website = \Env::get('em')->getRepository('Cx\Core_Modules\MultiSite\Model\Entity\Website')->findOneById($params['post']['websiteId']);

                    if (!$website) {
                        return array('status' => 'error', 'message' => $_ARRAYLANG['TXT_CORE_MODULE_MULTISITE_WEBSITE_STATUS_FAILED']);
                    }

                    $status = $website->getStatus() == \Cx\Core_Modules\MultiSite\Model\Entity\Website::STATE_ONLINE ? 'DEACTIVATED' : 'ACTIVATED';
                    switch ($website->getStatus()) {
                        case \Cx\Core_Modules\MultiSite\Model\Entity\Website::STATE_ONLINE:
                            $website->setStatus(\Cx\Core_Modules\MultiSite\Model\Entity\Website::STATE_OFFLINE);
                            break;
                        case \Cx\Core_Modules\MultiSite\Model\Entity\Website::STATE_OFFLINE:
                            $website->setStatus(\Cx\Core_Modules\MultiSite\Model\Entity\Website::STATE_ONLINE);
                            break;
                        default:
                            return array('status' => 'error', 'message' => $_ARRAYLANG['TXT_CORE_MODULE_MULTISITE_WEBSITE_STATUS_' . $status . '_FAILED']);
                            break;
                    }

                    \Env::get('em')->flush();
                    return array('status' => 'success', 'message' => $_ARRAYLANG['TXT_CORE_MODULE_MULTISITE_WEBSITE_STATUS_' . $status . '_SUCCESSFUL']);
                    break;
            }
        } catch (\Exception $e) {
            \DBG::log($e->getMessage());
            throw new MultiSiteJsonException($_ARRAYLANG['TXT_CORE_MODULE_MULTISITE_WEBSITE_STATUS_FAILED']);
        }
    }
    
    /**
     * Set the license
     * 
     * @global ADOConnection $objDatabase
     * @param array $params
     * 
     * @return boolean
     * @throws MultiSiteJsonException
     */
    public function setLicense($params) {
        global $objDatabase;

        try {
            switch (\Cx\Core\Setting\Controller\Setting::getValue('mode','MultiSite')) {
                case ComponentController::MODE_SERVICE:
                    $webRepo = \Env::get('em')->getRepository('Cx\Core_Modules\MultiSite\Model\Entity\Website');
                    $website = $webRepo->findOneById($params['post']['websiteId']);
                    $resp = self::executeCommandOnWebsite('setLicense', array('legalComponents' => $params['post']['legalComponents']), $website);                   
                    if ($resp && $resp->data->status == 'success') {
                        return array(
                            'status' => 'success',
                            'log'    => \DBG::getMemoryLogs(),
                        );
                    }
                    break;
                case ComponentController::MODE_WEBSITE:
                    $license = \Env::get('cx')->getLicense();
                    $licenseState = isset($params['post']['state']) ? $params['post']['state'] : (isset($params['post']['licenseState']) ? $params['post']['licenseState'] : '');
                    $licenseValidTo = isset($params['post']['validTo']) ? $params['post']['validTo'] : (isset($params['post']['licenseValidTo']) ? $params['post']['licenseValidTo'] : '');
                    $licenseUpdateInterval = isset($params['post']['updateInterval']) ? $params['post']['updateInterval'] : (isset($params['post']['licenseUpdateInterval']) ? $params['post']['licenseUpdateInterval'] : '');
                    $availableComponents = (isset($params['post']['availableComponents']) ? unserialize($params['post']['availableComponents']) : '');
                    $licenseLegalComponents = isset($params['post']['legalComponents']) ? $params['post']['legalComponents'] : $availableComponents;
                    
                    //If available components is set and not in a proper serialized format, throw error.
                    if (isset($params['post']['availableComponents'])) {
                        if (empty($availableComponents)) {
                            return array('status' => 'error');
                        }
                    }
                    
                    if (!empty($licenseState)) {
                        $license->setState($licenseState);
                    }
                    if (!empty($licenseValidTo)) {
                        $license->setValidToDate($licenseValidTo);
                    }
                    if (!empty($licenseUpdateInterval)) {
                        $license->setUpdateInterval($licenseUpdateInterval);
                    }
                    if (isset($params['post']['dashboardMessages'])) {
                        $dashboardMessages = array();
                        foreach ($params['post']['dashboardMessages'] as $key => $value) {
                            if (!empty($value) && $value != 'undefined') {
                                $lang = !is_string($key) ? \FWLanguage::getLanguageCodeById($key) : $key;
                                $licenseText = isset($value['text']) ? $value['text'] : '';
                                $licenseType = isset($value['type']) ? $value['type'] : '';
                                $licenseLink = isset($value['link']) ? $value['link'] : '';
                                $licenseLinkTarget = isset($value['linkTarget']) ? $value['linkTarget'] : '';
                                $dashboardMessages[$lang] = new \Cx\Core_Modules\License\Message($lang, $licenseText, $licenseType, $licenseLink, $licenseLinkTarget, true);
                            }
                        }
                        $license->setDashboardMessages($dashboardMessages);
                    }
                    if (!empty($licenseLegalComponents)) {
                        $license->setAvailableComponents($licenseLegalComponents);
                        $license->setLegalComponents($licenseLegalComponents);
                    }
                    if (isset($params['post']['isUpgradable'])) {
                        $license->setIsUpgradable($params['post']['isUpgradable']);
                    }
                    if (isset($params['post']['licenseMessage'])) {
                        $licenseMessage = array();
                        foreach ($params['post']['licenseMessage'] as $key => $value) {
                            if (!empty($value) && $value != 'undefined') {
                                $lang = !is_string($key) ? \FWLanguage::getLanguageCodeById($key) : $key;
                                $licenseMessage[$lang] = new \Cx\Core_Modules\License\Message($lang, $value['text']);
                            }
                        }
                        $license->setMessages($licenseMessage);
                    }
                    if (isset($params['post']['licenseGrayzoneMessages'])) {
                        $licenseGrayzoneMessages = array();
                        foreach ($params['post']['licenseGrayzoneMessages'] as $key => $value) {
                            if (!empty($value) && $value != 'undefined') {
                                $lang = !is_string($key) ? \FWLanguage::getLanguageCodeById($key) : $key;
                                $licenseGrayzoneMessages[$lang] = new \Cx\Core_Modules\License\Message($lang, $value['text']);
                            }
                        }
                        $license->setGrayZoneMessages($licenseGrayzoneMessages);
                    }
                    if (isset($params['post']['licenseFailedUpdate'])) {
                        $license->setFirstFailedUpdateTime($params['post']['licenseFailedUpdate']);
                    }
                    if (isset($params['post']['licenseSuccessfulUpdate'])) {
                        $license->setLastSuccessfulUpdateTime($params['post']['licenseSuccessfulUpdate']);
                    }
                    if (isset($params['post']['licenseKey'])) {
                        $license->setLicenseKey($params['post']['licenseKey']);
                    }
                    if (isset($params['post']['installationId'])) {
                        $license->setInstallationId($params['post']['installationId']);
                    }
                    if (isset($params['post']['licensePartnerTitle'])) {
                        $license->getPartner()->setTitle($params['post']['licensePartnerTitle']);
                    }
                    if (isset($params['post']['licensePartnerLastname'])) {
                        $license->getPartner()->setLastname($params['post']['licensePartnerLastname']);
                    }
                    if (isset($params['post']['licensePartnerFirstname'])) {
                        $license->getPartner()->setFirstname($params['post']['licensePartnerFirstname']);
                    }
                    if (isset($params['post']['licensePartnerCompanyname'])) {
                        $license->getPartner()->setCompanyName($params['post']['licensePartnerCompanyname']);
                    }
                    if (isset($params['post']['licensePartnerAddress'])) {
                        $license->getPartner()->setAddress($params['post']['licensePartnerAddress']);
                    }
                    if (isset($params['post']['licensePartnerZip'])) {
                        $license->getPartner()->setZip($params['post']['licensePartnerZip']);
                    }
                    if (isset($params['post']['licensePartnerCity'])) {
                        $license->getPartner()->setCity($params['post']['licensePartnerCity']);
                    }
                    if (isset($params['post']['licensePartnerCountry'])) {
                        $license->getPartner()->setCountry($params['post']['licensePartnerCountry']);
                    }
                    if (isset($params['post']['licensePartnerPhone'])) {
                        $license->getPartner()->setPhone($params['post']['licensePartnerPhone']);
                    }
                    if (isset($params['post']['licensePartnerUrl'])) {
                        $license->getPartner()->setUrl($params['post']['licensePartnerUrl']);
                    }
                    if (isset($params['post']['licensePartnerMail'])) {
                        $license->getPartner()->setMail($params['post']['licensePartnerMail']);
                    }
                    if (isset($params['post']['licenseCustomerTitle'])) {
                        $license->getCustomer()->setTitle($params['post']['licenseCustomerTitle']);
                    }
                    if (isset($params['post']['licenseCustomerLastname'])) {
                        $license->getCustomer()->setLastname($params['post']['licenseCustomerLastname']);
                    }
                    if (isset($params['post']['licenseCustomerFirstname'])) {
                        $license->getCustomer()->setFirstname($params['post']['licenseCustomerFirstname']);
                    }
                    if (isset($params['post']['licenseCustomerCompanyname'])) {
                        $license->getCustomer()->setCompanyName($params['post']['licenseCustomerCompanyname']);
                    }
                    if (isset($params['post']['licenseCustomerAddress'])) {
                        $license->getCustomer()->setAddress($params['post']['licenseCustomerAddress']);
                    }
                    if (isset($params['post']['licenseCustomerZip'])) {
                        $license->getCustomer()->setZip($params['post']['licenseCustomerZip']);
                    }
                    if (isset($params['post']['licenseCustomerCity'])) {
                        $license->getCustomer()->setCity($params['post']['licenseCustomerCity']);
                    }
                    if (isset($params['post']['licenseCustomerCountry'])) {
                        $license->getCustomer()->setCountry($params['post']['licenseCustomerCountry']);
                    }
                    if (isset($params['post']['licenseCustomerPhone'])) {
                        $license->getCustomer()->setPhone($params['post']['licenseCustomerPhone']);
                    }
                    if (isset($params['post']['licenseCustomerUrl'])) {
                        $license->getCustomer()->setUrl($params['post']['licenseCustomerUrl']);
                    }
                    if (isset($params['post']['licenseCustomerMail'])) {
                        $license->getCustomer()->setMail($params['post']['licenseCustomerMail']);
                    }
                    if (isset($params['post']['upgradeUrl'])) {
                        $license->setUpgradeUrl($params['post']['upgradeUrl']);
                    }
                    if (isset($params['post']['licenseCreatedAt'])) {
                        $license->setCreatedAtDate($params['post']['licenseCreatedAt']);
                    }
                    if (isset($params['post']['licenseDomains'])) {
                        $license->setRegisteredDomains(explode(', ',$params['post']['licenseDomains']));
                    }
                    if (isset($params['post']['isUpgradable'])) {
                        $license->setIsUpgradable($params['post']['isUpgradable']);
                    }
                    if (isset($params['post']['licenseGrayzoneTime'])) {
                        $license->setGrayzoneTime($params['post']['licenseGrayzoneTime']);
                    }
                    if (isset($params['post']['licenseLockTime'])) {
                        $license->setFrontendLockTime($params['post']['licenseLockTime']);
                    }
                    if (isset($params['post']['licenseUpdateInterval'])) {
                        $license->setRequestInterval($params['post']['licenseUpdateInterval']);
                    }
                    if (isset($params['post']['coreCmsEdition'])) {
                        $license->setEditionName($params['post']['coreCmsEdition']);
                    }
                    if (isset($params['post']['coreCmsVersion'])) {
                        $license->getVersion()->setNumber($params['post']['coreCmsVersion']);
                    }
                    if (isset($params['post']['coreCmsCodeName'])) {
                        $license->getVersion()->setCodeName($params['post']['coreCmsCodeName']);
                    }
                    if (isset($params['post']['coreCmsStatus'])) {
                        $license->getVersion()->setState($params['post']['coreCmsStatus']);
                    }
                    if (isset($params['post']['coreCmsReleaseDate'])) {
                        $license->getVersion()->setReleaseDate($params['post']['coreCmsReleaseDate']);
                    }
                    if (isset($params['post']['coreCmsName'])) {
                        $license->getVersion()->setName($params['post']['coreCmsName']);
                    }
                    
                    try {
                        $license->save($objDatabase);
                        return array(
                            'status' => 'success',
                            'log'    => \DBG::getMemoryLogs(),
                        );
                    } catch (\Exception $e) {
                        throw new MultiSiteJsonException('Unable to save the setup license'.$e->getMessage());
                    }
                    break;
            }
            return array(
                'status' => 'error',
                'log'    => \DBG::getMemoryLogs(),
            );
        } catch (\Exception $e) {
            throw new MultiSiteJsonException(array(
                'log'       => \DBG::getMemoryLogs(),
                'message'   => 'Unable to setup license: '.$e->getMessage(),
            ));
        }
    }
                
    /**
     * Returns the $isIscRequest value
     * 
     * @return boolean
     */
    public static function isIscRequest() {
        return self::$isIscRequest;
    }
    
    /**
     * Return the status message
     * 
     * @return array
     */
    public function ping() {
        global $_CONFIG;
        
        //Check the system is in maintenance mode or not
        if ($_CONFIG['systemStatus'] !='on') {
            return array('status' => 'error', 'message' => 'Service Server is currently in maintenance mode');
        }
        
        $resp = self::executeCommandOnManager('pong');
        if ($resp && $resp->status == 'success' && $resp->data->status == 'success') {
            return array('status' => 'success');
        }
        
        return array('status' => 'error', 'message' => 'Reverse connection failed');
    }
    
    /**
     * Return the status message
     * 
     * @return array
     */
    public function pong() {
        return array('status' => 'success');
    }
    
    public static function executeCommand($host, $command, $params, $secretKey, $installationId, $httpAuth) {
        $params['auth'] = self::getAuthenticationObject($secretKey, $installationId);
        $objJsonData = new \Cx\Core\Json\JsonData();
        return $objJsonData->getJson(\Cx\Core_Modules\MultiSite\Controller\ComponentController::getApiProtocol() . $host . '/cadmin/index.php?cmd=JsonData&object=MultiSite&act=' . $command, $params, false, '', $httpAuth);
    }

    /**
     * This method will be used by the Website Service to execute commands on the Website Manager
     * Fetch connection data to Manager and pass it to the method executeCommand()
     */
    public static function executeCommandOnManager($command, $params = array()) {
        if (!in_array(\Cx\Core\Setting\Controller\Setting::getValue('mode','MultiSite'), array(ComponentController::MODE_MANAGER, ComponentController::MODE_SERVICE, ComponentController::MODE_HYBRID))) {
            throw new MultiSiteJsonException('Command'.__METHOD__.' is only available in MultiSite-mode MANAGER, SERVICE or HYBRID.');
        }
        if (in_array(\Cx\Core\Setting\Controller\Setting::getValue('mode','MultiSite'), array(ComponentController::MODE_MANAGER, ComponentController::MODE_HYBRID))) {
            \DBG::msg(__METHOD__. " ($command): executing locally on manager (not going to execute through JsonData adapter)");
            $config = \Env::get('config');
            $params['auth'] = json_encode(array('sender' => $config['domainUrl']));
            try {
                $objJsonMultiSite = new self();
                $result = $objJsonMultiSite->$command(array('post' => $params));
                // Convert $result (which is an array) into an object
                // as JsonData->getJson (called by self::executeCommand())
                // would do/return that.
                return json_decode(json_encode(array('status' => 'success', 'data' => $result)));
            } catch (\Exception $e) {
                throw new MultiSiteJsonException($e->getMessage());
            }
        }
        $host = \Cx\Core\Setting\Controller\Setting::getValue('managerHostname','MultiSite');
        $installationId = \Cx\Core\Setting\Controller\Setting::getValue('managerInstallationId','MultiSite');
        $secretKey = \Cx\Core\Setting\Controller\Setting::getValue('managerSecretKey','MultiSite');
        $httpAuth = array(
            'httpAuthMethod' => \Cx\Core\Setting\Controller\Setting::getValue('managerHttpAuthMethod','MultiSite'),
            'httpAuthUsername' => \Cx\Core\Setting\Controller\Setting::getValue('managerHttpAuthUsername','MultiSite'),
            'httpAuthPassword' => \Cx\Core\Setting\Controller\Setting::getValue('managerHttpAuthPassword','MultiSite'),
        );
        return self::executeCommand($host, $command, $params, $secretKey, $installationId, $httpAuth);
    }

    /**
     * This method will be used by a Websites to execute commands on its Website Service
     * Fetch connection data to Service and pass it to the method executeCommand()
     */
    public static function executeCommandOnMyServiceServer($command, $params) {
        if (!in_array(\Cx\Core\Setting\Controller\Setting::getValue('mode','MultiSite'), array(ComponentController::MODE_WEBSITE))) {
            throw new MultiSiteJsonException('Command'.__METHOD__.' is only available in MultiSite-mode WEBSITE.');
        }
        $host = \Cx\Core\Setting\Controller\Setting::getValue('serviceHostname','MultiSite');
        $installationId = \Cx\Core\Setting\Controller\Setting::getValue('serviceInstallationId','MultiSite');
        $secretKey = \Cx\Core\Setting\Controller\Setting::getValue('serviceSecretKey','MultiSite');
        $httpAuth = array(
            'httpAuthMethod' => \Cx\Core\Setting\Controller\Setting::getValue('serviceHttpAuthMethod','MultiSite'),
            'httpAuthUsername' => \Cx\Core\Setting\Controller\Setting::getValue('serviceHttpAuthUsername','MultiSite'),
            'httpAuthPassword' => \Cx\Core\Setting\Controller\Setting::getValue('serviceHttpAuthPassword','MultiSite'),
        );
        return self::executeCommand($host, $command, $params, $secretKey, $installationId, $httpAuth);
    }
    
    /**
     * This method will be used by the Website Manager to execute commands on the Website Service
     * Fetch connection data to Service and pass it to the method executeCommand()
     */
    public static function executeCommandOnServiceServerOfWebsite($command, $params, $website) {
        if (!in_array(\Cx\Core\Setting\Controller\Setting::getValue('mode','MultiSite'), array(ComponentController::MODE_MANAGER))) {
            throw new MultiSiteJsonException('Command'.__METHOD__.' is only available in MultiSite-mode MANAGER.');
        }
        $websiteServiceServer = $website->getWebsiteServiceServer();
        $host = $websiteServiceServer->getHostname();
        $installationId = $websiteServiceServer->getInstallationId();
        $secretKey = $websiteServiceServer->getSecretKey();
        $httpAuth = array(
            'httpAuthMethod' => $websiteServiceServer->getHttpAuthMethod(),
            'httpAuthUsername' => $websiteServiceServer->getHttpAuthUsername(),
            'httpAuthPassword' => $websiteServiceServer->getHttpAuthPassword(),
        );
        return self::executeCommand($host, $command, $params, $secretKey, $installationId, $httpAuth);
    }

    /**
     * This method will be used by the Website Manager to execute commands on a Website Service
     * Fetch connection data to Service and pass it to the method executeCommand():
     */
    public static function executeCommandOnServiceServer($command, $params, $websiteServiceServer) {
        if (!in_array(\Cx\Core\Setting\Controller\Setting::getValue('mode','MultiSite'), array(ComponentController::MODE_MANAGER))) {
            throw new MultiSiteJsonException('Command'.__METHOD__.' is only available in MultiSite-mode MANAGER.');
        }
        $host = $websiteServiceServer->getHostname();
        $installationId = $websiteServiceServer->getInstallationId();
        $secretKey = $websiteServiceServer->getSecretKey();
        $httpAuth = array(
            'httpAuthMethod' => $websiteServiceServer->getHttpAuthMethod(),
            'httpAuthUsername' => $websiteServiceServer->getHttpAuthUsername(),
            'httpAuthPassword' => $websiteServiceServer->getHttpAuthPassword(),
        );
        return self::executeCommand($host, $command, $params, $secretKey, $installationId, $httpAuth);
    }

    /**
     * This method will be used by the Website Service to execute commands on a Website
     * Fetch connection data to Website and pass it to the method executeCommand():
     */
    public static function executeCommandOnWebsite($command, $params, $website) {
        if (!in_array(\Cx\Core\Setting\Controller\Setting::getValue('mode','MultiSite'), array(ComponentController::MODE_MANAGER, ComponentController::MODE_HYBRID, ComponentController::MODE_SERVICE))) {
            throw new MultiSiteJsonException('Command '.__METHOD__.' is only available in MultiSite-mode MODE_MANAGER, HYBRID or SERVICE (running on '.\Cx\Core\Setting\Controller\Setting::getValue('mode','MultiSite').' '.$website->getName().')');
        }

        // In case mode is Manager, the request shall be routed through the associated Service Server
        if (\Cx\Core\Setting\Controller\Setting::getValue('mode','MultiSite') == ComponentController::MODE_MANAGER) {
            return self::executeCommandOnServiceServerOfWebsite('executeOnWebsite', array('command' => $command, 'params' => $params, 'websiteId' => $website->getId()), $website);
        }

        // JsonData requests shall be made to the FQDN of the Website,
        // as the BaseDn might not yet work as it depends on the DNS synchronization.
        $host = $website->getFqdn()->getName();
        $installationId = $website->getInstallationId();
        $secretKey = $website->getSecretKey();
        $httpAuth = array(
            'httpAuthMethod' => \Cx\Core\Setting\Controller\Setting::getValue('websiteHttpAuthMethod','MultiSite'),
            'httpAuthUsername' => \Cx\Core\Setting\Controller\Setting::getValue('websiteHttpAuthUsername','MultiSite'),
            'httpAuthPassword' => \Cx\Core\Setting\Controller\Setting::getValue('websiteHttpAuthPassword','MultiSite'),
        );
        return self::executeCommand($host, $command, $params, $secretKey, $installationId, $httpAuth);
    }

    public function executeOnManager($params) {
        if (empty($params['post']['command'])) {
            \DBG::dump($params);
            throw new MultiSiteJsonException('JsonMultiSite::executeOnManager() on '.\Cx\Core\Setting\Controller\Setting::getValue('mode','MultiSite').' failed: Insufficient arguments supplied: '.var_export($params, true));
        }

        $passedParams = !empty($params['post']['params']) ? $params['post']['params'] : null;
        
        try {
            // special case for updateUser
            if ($params['post']['command'] == 'updateUser') {
                $authenticationValue = json_decode($params['post']['auth'], true);
                if (empty($authenticationValue) || !is_array($authenticationValue)) {
                    throw new MultiSiteJsonException(__METHOD__.': Insufficient mapping information supplied.');
                }
                $domainRepo = \Env::get('em')->getRepository('Cx\Core_Modules\MultiSite\Model\Entity\Domain');
                $domain     = $domainRepo->findOneBy(array('name' => $authenticationValue['sender']));
                if (!$domain || !$domain->getWebsite()) {
                    throw new MultiSiteJsonException(__METHOD__.': Unknown website sender');
                }
                $ownerId = $domain->getWebsite()->getOwner()->getId();
                $passedParams['userId'] = $ownerId;
            }

            $resp = self::executeCommandOnManager($params['post']['command'], $passedParams);
            if ($resp && $resp->status == 'success') {
                return $resp->data;
            } else {
                throw new MultiSiteJsonException(var_export($resp, true));
            }
        } catch (\Exception $e) {
            throw new MultiSiteJsonException('JsonMultiSite::executeOnManager() failed: ' . $e->getMessage());
        }
    }

    public function executeOnWebsite($params) {
        if (empty($params['post']['command']) || empty($params['post']['websiteId'])) {
            \DBG::dump($params);
            throw new MultiSiteJsonException('JsonMultiSite::executeOnWebsite() on '.\Cx\Core\Setting\Controller\Setting::getValue('mode','MultiSite').' failed: Insufficient arguments supplied: '.var_export($params, true));
        }
        
        $webRepo  = \Env::get('em')->getRepository('Cx\Core_Modules\MultiSite\Model\Entity\Website');
        $website  = $webRepo->findOneById($params['post']['websiteId']);
        if (!$website) {
            throw new MultiSiteJsonException('JsonMultiSite::executeOnWebsite() failed: Website by ID '.$params['post']['websiteId'].' not found.');
        }

        $passedParams = !empty($params['post']['params']) ? $params['post']['params'] : null;
        
        try {
            $resp = self::executeCommandOnWebsite($params['post']['command'], $passedParams, $website);
            if ($resp && $resp->status == 'success' && $resp->data->status == 'success') {
                return $resp->data;
            } else {
                \DBG::dump($resp);
                throw new MultiSiteJsonException($resp->message);
            }
        } catch (\Exception $e) {
            \DBG::msg(__METHOD__.': ' . $e->getMessage());
            throw new MultiSiteJsonException($e->getMessage());
        }
    }

    public function generateAuthToken($params) {
        try {
            switch (\Cx\Core\Setting\Controller\Setting::getValue('mode','MultiSite')) {
                case ComponentController::MODE_MANAGER:
                case ComponentController::MODE_HYBRID:
                    if (empty($params['post']['ownerId'])) {
                        return array(
                            'status' => 'error',
                            'log' => \DBG::getMemoryLogs(),
                        );
                    }
                    $websiteUserId = $params['post']['ownerId'];
                    break;
                case ComponentController::MODE_WEBSITE:
                    $websiteUserId = \Cx\Core\Setting\Controller\Setting::getValue('websiteUserId','MultiSite');
                    break;
                default:
                    break;
            }
            $objUser = \FWUser::getFWUserObject()->objUser->getUser(intval($websiteUserId));
            if (!$objUser) {
                throw new MultiSiteJsonException('Unable to load website Owner user account');
            }
            $authToken = $objUser->generateAuthToken();
            if (!$objUser->store()) {
                \DBG::msg('Updating user failed: '.$objUser->getErrorMsg());
                throw new MultiSiteJsonException($objUser->getErrorMsg());
            }
            return array(
                'status'    => 'success',
                'userId'    => $websiteUserId,
                'authToken' => $authToken,
                'log'       => \DBG::getMemoryLogs(),
            );
        } catch (\Exception $e) {
            throw new MultiSiteJsonException(array(
                'log'       => \DBG::getMemoryLogs(),
                'message'   => 'JsonMultiSite::generateAuthToken() failed: ' . $e->getMessage(),
            ));
        }
    }
    
    /**
     * setup the config options
     */
    public function setupConfig($params) {
        if (empty($params['post']['coreAdminEmail']) || empty($params['post']['contactFormEmail']) || empty($params['post']['dashboardNewsSrc'])) {
            throw new MultiSiteJsonException('JsonMultiSite::setupConfig() on '.\Cx\Core\Setting\Controller\Setting::getValue('mode','MultiSite').' failed: Insufficient arguments supplied: '.var_export($params, true));
        }
        
        \Cx\Core\Setting\Controller\Setting::init('Config', '','Yaml');
        if (!\Cx\Core\Setting\Controller\Setting::isDefined('dashboardNewsSrc') 
                && !\Cx\Core\Setting\Controller\Setting::add('dashboardNewsSrc', $params['post']['dashboardNewsSrc'], 2, \Cx\Core\Setting\Controller\Setting::TYPE_TEXT, null, 'component')) {
            throw new MultiSiteJsonException(array(
                'log'       => \DBG::getMemoryLogs(),
                'message'   => "Failed to add Setting entry for dashboardNewsSrc",
            ));
        }
        if (!\Cx\Core\Setting\Controller\Setting::isDefined('coreAdminEmail') 
                && !\Cx\Core\Setting\Controller\Setting::add('coreAdminEmail', $params['post']['coreAdminEmail'], 3, \Cx\Core\Setting\Controller\Setting::TYPE_TEXT, null, 'contactInformation')) {
            throw new MultiSiteJsonException(array(
                'log'       => \DBG::getMemoryLogs(),
                'message'   => "Failed to add Setting entry for coreAdminEmail",
            ));
        }
        if (!\Cx\Core\Setting\Controller\Setting::isDefined('contactFormEmail') 
                && !\Cx\Core\Setting\Controller\Setting::add('contactFormEmail', $params['post']['contactFormEmail'], 4, \Cx\Core\Setting\Controller\Setting::TYPE_TEXT, null, 'contactInformation')) {
            throw new MultiSiteJsonException(array(
                'log'       => \DBG::getMemoryLogs(),
                'message'   => "Failed to add Setting entry for contactFormEmail",
            ));
        }
        \Cx\Core\Config\Controller\Config::init();
       
        // we must re-initialize the original MultiSite settings of the main installation
        \Cx\Core\Setting\Controller\Setting::init('MultiSite', '', 'FileSystem');
        return array(
            'status' => 'success',
            'log'    => \DBG::getMemoryLogs(),
        );
    }

    public function getDefaultWebsiteIp() {
        if (\Cx\Core\Setting\Controller\Setting::getValue('mode','MultiSite') != ComponentController::MODE_SERVICE) {
            throw new MultiSiteJsonException('JsonMultiSite::getDefaultWebsiteIp() failed: Command is only on Website Service Server available.');
        }

        return array(
            'status'            => 'success',
            'defaultWebsiteIp'  => \Cx\Core\Setting\Controller\Setting::getValue('defaultWebsiteIp','MultiSite'),
            'log'               => \DBG::getMemoryLogs(),
        );
    }
    
    /**
     * Set the default language
     * 
     * @global \Cx\Core_Modules\MultiSite\Controller\ADOConnection $objDatabase
     * @param array $params
     * 
     * @return boolean
     * @throws MultiSiteJsonException
     */
    public function setDefaultLanguage($params) {
        global $objDatabase;

        if (empty($params['post']['langId'])) {
            throw new MultiSiteJsonException('JsonMultiSite::setDefaultLanguage() failed: No language specified.');
        }
        
        try {
            $deactivateIds = array();
            $arrLang = \FWLanguage::getLanguageArray();
            foreach ($arrLang As $key => $value) {
                if ($key != $params['post']['langId']) {
                    $deactivateIds[] = $key;
                }
            }
            
            //deactivate all the languages except the lang $params['post']['langId']
            $deactivateQuery = \SQL::update('languages', array('backend' => 0, 'frontend' => 0, 'is_default' => 'false'), array('escape' => true)) . ' WHERE `id` In (' . implode(', ', $deactivateIds) . ')';
            
            //set the lang($params['post']['langId']) as default
            $activateQuery = \SQL::update('languages', array('backend' => 1, 'frontend' => 1, 'is_default' => 'true'), array('escape' => true)) . ' WHERE `id` = ' . $params['post']['langId'];
            
            if ($objDatabase->Execute($deactivateQuery) !== false && $objDatabase->Execute($activateQuery) !== false) {
                return array(
                    'status' => 'success',
                    'log'    => \DBG::getMemoryLogs(),
                );
            }
            
        } catch (\Exception $e) {
            throw new MultiSiteJsonException(array(
                'log'       => \DBG::getMemoryLogs(),
                'message'   => 'JsonMultiSite::setDefaultLanguage() failed: Updating Language status.' . $e->getMessage(),
            ));
        }
    }
    
    /**
     * Check current user is website owner or not
     * 
     * @return boolean
     */
    public static function isWebsiteOwner() {
        //check user logged in or not
        if (!\FWUser::getFWUserObject()->objUser->login()) {
            return false;
        }
        
        //Check the user is website owner or not
        if (\FWUser::getFWUserObject()->objUser->getId() == \Cx\Core\Setting\Controller\Setting::getValue('websiteUserId','MultiSite')) {
            return true;
        }
        return false;
    }
    
    /**
     * Get the Ftp user
     * 
     * @param array $params
     * 
     * @return array
     * @throws MultiSiteJsonException
     */
    public function getFtpUser($params) {
        try {
            switch (\Cx\Core\Setting\Controller\Setting::getValue('mode','MultiSite')) {
                case ComponentController::MODE_SERVICE:
                case ComponentController::MODE_HYBRID:    
                    $authenticationValue = json_decode($params['post']['auth'], true);
                    if (empty($authenticationValue) || !is_array($authenticationValue)) {
                        \DBG::dump($params);
                        throw new MultiSiteJsonException('JsonMultiSite::getFtpUser() on ' . \Cx\Core\Setting\Controller\Setting::getValue('mode','MultiSite') . ' failed: Insufficient reset information supplied.' . var_export($params, true));
                    }

                    $domainRepo = \Env::get('em')->getRepository('Cx\Core_Modules\MultiSite\Model\Entity\Domain');
                    $domain = $domainRepo->findOneBy(array('name' => $authenticationValue['sender']));
                    if (!$domain) {
                        throw new MultiSiteJsonException('JsonMultiSite::getFtpUser() failed: Unkown Website: ' . $authenticationValue['sender']);
                    }

                    $website = $domain->getWebsite();
                    if (!$website) {
                        throw new MultiSiteJsonException('JsonMultiSite::getFtpUser() failed: Unkown Website: ' . $authenticationValue['sender']);
                    }

                    if ($website->getFtpUser()) {
                        return array(
                            'status'    => 'success',
                            'ftpUser'   => $website->getFtpUser(),
                            'log'       => \DBG::getMemoryLogs(),
                        );
                    }
                    break;
            }
            throw new MultiSiteJsonException('JsonMultiSite::getFtpUser() failed: Website Ftp user field is empty.');
        } catch (\Exception $e) {
            throw new MultiSiteJsonException('JsonMultiSite::getFtpUser() failed: to get website FTP user: ' . $e->getMessage());
        }
    }
    
    /**
     * Get all Ftp user accounts
     * 
     * @param array $params
     * 
     * @return array
     * @throws MultiSiteJsonException
     */
    public function getFtpAccounts($params) {
        try {
            switch (\Cx\Core\Setting\Controller\Setting::getValue('mode','MultiSite')) {
                case ComponentController::MODE_SERVICE:
                case ComponentController::MODE_HYBRID:
                    $hostingController = \Cx\Core_Modules\MultiSite\Controller\ComponentController::getHostingController();
                    $ftpUserAccounts   = $hostingController->getFtpAccounts(true);
                    if ($ftpUserAccounts) {
                        return array(
                            'status' => 'success',
                            'data'   => $ftpUserAccounts,
                            'log'    => \DBG::getMemoryLogs(),
                        );
                    }
                    break;
            }
            throw new MultiSiteJsonException('JsonMultiSite::getFtpAccounts() failed');
        } catch (\Exception $e) {
            throw new MultiSiteJsonException('JsonMultiSite::getFtpAccounts() failed: ' . $e->getMessage());
        }
    }
    
    /**
     * Reset the FTP Password
     * 
     * @param array $params
     * 
     * @return array
     * @throws MultiSiteJsonException
     */
    public function resetFtpPassword($params) {
        global $_ARRAYLANG;
        self::loadLanguageData();
        
        try {
            switch (\Cx\Core\Setting\Controller\Setting::getValue('mode','MultiSite')) {
                case ComponentController::MODE_WEBSITE:
                    $response = self::executeCommandOnMyServiceServer('resetFtpPassword', array());
                    if ($response && $response->status == 'success' && $response->data->status == 'success') {
                        return array(
                            'status'    => 'success',
                            'message'   => $_ARRAYLANG['TXT_MULTISITE_RESET_FTP_PASS_MSG'],
                            'password'  => $response->data->password,
                            'log'       => \DBG::getMemoryLogs(),
                        );
                    }
                    if (isset($response->log)) {
                        \DBG::appendLogs(array_map(function($logEntry) {return '(Service) '.$logEntry;}, $response->log));
                    }
                    if (isset($response->message)) {
                        \DBG::msg($response->message);
                    }
                    break;
                case ComponentController::MODE_SERVICE:
                case ComponentController::MODE_HYBRID:
                    $authenticationValue = json_decode($params['post']['auth'], true);
                    if (empty($authenticationValue) || !is_array($authenticationValue)) {
                        \DBG::dump($params);
                        throw new MultiSiteJsonException('JsonMultiSite::resetFtpPassword() on ' . \Cx\Core\Setting\Controller\Setting::getValue('mode','MultiSite') . ' failed: Insufficient reset information supplied.' . var_export($params, true));
                    }

                    $domainRepo = \Env::get('em')->getRepository('Cx\Core_Modules\MultiSite\Model\Entity\Domain');
                    $domain = $domainRepo->findOneBy(array('name' => $authenticationValue['sender']));
                    if (!$domain) {
                        throw new MultiSiteJsonException('JsonMultiSite::resetFtpPassword() failed: Unkown Website: ' . $authenticationValue['sender']);
                    }

                    $website = $domain->getWebsite();
                    if (!$website) {
                        throw new MultiSiteJsonException('JsonMultiSite::resetFtpPassword() failed: Unkown Website: ' . $authenticationValue['sender']);
                    }
                    
                    $hostingController = ComponentController::getHostingController();
                    
                    $password = \User::make_password(8, true);
                    if ($hostingController->changeFtpAccountPassword($website->getFtpUser(), $password)) {
                        return array(
                            'status'    => 'success',
                            'password'  => $password,
                            'log'       => \DBG::getMemoryLogs(),
                        );
                    }
                    break;
            }

            return array('status' => 'error', 'message' => $_ARRAYLANG['TXT_MULTISITE_RESET_FTP_PASS_ERROR_MSG']);
        } catch (\Exception $e) {
            throw new MultiSiteJsonException('JsonMultiSite::resetFtpPassword() failed: Updating FTP password.' . $e->getMessage());
        }
    }
    
    /**
     * Check the Authentication access for resetting the FTP password
     * 
     * @return boolean
     */
    public function checkResetFtpPasswordAccess($params) {
        switch (\Cx\Core\Setting\Controller\Setting::getValue('mode','MultiSite')) {
            case ComponentController::MODE_WEBSITE:
                if (self::isWebsiteOwner()) {
                    return true;
                }
                break;
            case ComponentController::MODE_SERVICE:
            case ComponentController::MODE_HYBRID:
                if ($this->auth($params)) {
                    return true;
                }
                break;
        }
        return false;
    }
        
    /**
     * Check the Authentication access for execute sql query in website
     * 
     * @return boolean
     */
    public function checkExecuteSqlAccess($params) {
        switch (\Cx\Core\Setting\Controller\Setting::getValue('mode','MultiSite')) {
            case ComponentController::MODE_MANAGER:
            case ComponentController::MODE_HYBRID:
                if ($this->checkPermission()) {
                    return true;
                }
                break;
            case ComponentController::MODE_SERVICE:
            case ComponentController::MODE_WEBSITE:
                if ($this->auth($params)) {
                    return true;
                }
                break;
        }
        return false;
    }
    
    /**
     * Check the Authentication access for get license
     * 
     * @return boolean
     */
    public function checkGetLicenseAccess($params) {
        switch (\Cx\Core\Setting\Controller\Setting::getValue('mode','MultiSite')) {
            case ComponentController::MODE_MANAGER:
            case ComponentController::MODE_HYBRID:
            case ComponentController::MODE_SERVICE:
                if ($this->checkPermission()) {
                    return true;
                }
                break;
            case ComponentController::MODE_WEBSITE:
                if ($this->auth($params)) {
                    return true;
                }
                break;
        }
        return false;
    }
    /**
     * check authentication access for send account activation email
     * 
     * @return boolean
     */
    public function checkSendAccountActivation($params) {
        switch (\Cx\Core\Setting\Controller\Setting::getValue('mode','MultiSite')) {
            case ComponentController::MODE_MANAGER:
            case ComponentController::MODE_SERVICE:
                if ($this->auth($params)) {
                    return true;
                }
                break;
            case ComponentController::MODE_WEBSITE:
                if ($this->checkPermission()) {
                    return true;
                }
                break;
        }
        return false;
    }
    
    /*
     * Updating setup data in servers
     * 
     * @return boolean
     */
    public function updateServiceServerSetup($params) {

        if (empty($params['post']['setupArray'])) {
            throw new MultiSiteJsonException('JsonMultiSite::updateServiceServerSetup(): Updating setup data in server failed due to empty params in post method.');
        }
        
        try {
            \Cx\Core\Setting\Controller\Setting::init('MultiSite', 'setup', 'FileSystem');
            $setupValues = $params['post']['setupArray'];
            foreach($setupValues as $valuesName => $value) {
                \Cx\Core\Setting\Controller\Setting::set($valuesName, $value['value']);
                \Cx\Core\Setting\Controller\Setting::update($valuesName);
            }
        } catch (\Exception $e) {
            throw new MultiSiteJsonException('JsonMultiSite::updateServiceServerSetup() failed: Updating setup data in server .' . $e->getMessage());
        }
    }
    
    /**
     * Completely removes an website
     * 
     */
    public function destroyWebsite($params) {
        
        if (empty($params['post']['websiteId'])) {
            throw new MultiSiteJsonException('JsonMultiSite (destroyWebsite): failed to destroy the website due to the empty param $websiteId');
        }

        try {
            $webRepo  = \Env::get('em')->getRepository('Cx\Core_Modules\MultiSite\Model\Entity\Website');
            $website  = $webRepo->findOneById($params['post']['websiteId']);
            if (!$website) {
                return array(
                    'status'    => 'success',
                    'log'       => \DBG::getMemoryLogs(),
                );            
            }
            \Env::get('em')->remove($website);
            \Env::get('em')->flush();
            return array(
                'status'    => 'success',
                'log'       => \DBG::getMemoryLogs(),
            );
        } catch (\Exception $e) {
            throw new MultiSiteJsonException(array(
                'log'       => \DBG::getMemoryLogs(),
                'message'   => 'JsonMultiSite (destroyWebsite): failed to destroy the website.' . $e->getMessage(),
            ));
        }
    }

    /**
     * Set the Website Theme
     * 
     * @global \Cx\Core_Modules\MultiSite\Controller\ADOConnection $objDatabase
     * @param array $params
     * 
     * @return array
     * @throws MultiSiteJsonException
     */
    public function setWebsiteTheme($params) {
        global $objDatabase;

        if (empty($params['post']['themeId'])) {
            throw new MultiSiteJsonException('JsonMultiSite (setWebsiteTheme): failed to set the website theme due to the empty param $themeId');
        }
        
        try {
            $themeRepo = new \Cx\Core\View\Model\Repository\ThemeRepository();
            if (!$themeRepo->findById($params['post']['themeId'])) {
                throw new MultiSiteJsonException('JsonMultiSite (setWebsiteTheme): failed to set the website theme due to no one theme exists with param $themeId');
            }

            $langId = \FWLanguage::getDefaultLangId();
            //set the theme $themeId as standard and mobile theme
            $objResult = $objDatabase->Execute('UPDATE ' . DBPREFIX . 'languages '
                                                . 'SET `themesid` = ' . intval($params['post']['themeId']) . ', `mobile_themes_id` = ' . intval($params['post']['themeId'])
                                                . ' WHERE id = ' . intval($langId));
            if ($objResult !== false) {
                return array(
                    'status'    => 'success',
                    'log'       => \DBG::getMemoryLogs(),
                );
            }
        } catch (\Exception $e) {
            throw new MultiSiteJsonException(array(
                'log'       => \DBG::getMemoryLogs(),
                'message'   => 'JsonMultiSite (setWebsiteTheme): failed to set the website theme.' . $e->getMessage(),
            ));
        }
    }
    
    /**
     * Executing Sql query
     * 
     * @global $objDatabase
     * @param type $params
     * @return type
     * @throws MultiSiteJsonException
     */
    public function executeSql($params)
    {
        global $objDatabase, $_ARRAYLANG;
        
        //load the multisite language
        self::loadLanguageData();
        try {
            switch (\Cx\Core\Setting\Controller\Setting::getValue('mode','MultiSite')) {
                case ComponentController::MODE_MANAGER:
                case ComponentController::MODE_HYBRID:
                    if (empty($params['post']['query'])) {
                        throw new MultiSiteJsonException('JsonMultiSite (executeSql): failed to execute query, the sql query is empty');
                    }
                    $websiteServiceRepo = \Env::get('em')->getRepository('Cx\Core_Modules\MultiSite\Model\Entity\Website');
                    //execute sql query on website
                    if (isset($params['post']['mode']) && $params['post']['mode'] == 'website') {
                        $website = $websiteServiceRepo->findOneBy(array('id' => $params['post']['id']));
                        if (empty($website)) {
                            throw new MultiSiteJsonException('JsonMultiSite (executeSql): failed to find the website.');
                        }
                        $params['post']['websiteName'] = $website->getFqdn()->getName();
                        $resp = self::executeCommandOnWebsite('executeSql', $params['post'], $website);
                        if ($resp->status == 'success') {
                            if ($resp->data->status) {
                                return array('status' => 'success', 'queryResult' => $resp->data, 'mode' => $params['post']['mode']);
                            }
                            return array('status' => 'error', 'message' => $resp->data->error);
                        }
                        return array('status' => 'error', 'message' => $_ARRAYLANG['TXT_MULTISITE_EXECUTED_QUERY_FAILED']);
                    }
                    //execute sql query on all websites running on service server 
                    if (isset($params['post']['mode']) && $params['post']['mode'] == 'service') {
                        $websites = $websiteServiceRepo->findBy(array('websiteServiceServerId' => $params['post']['id']));
                        if (empty($websites)) {
                            throw new MultiSiteJsonException('JsonMultiSite (executeSql): failed to find the service server.');
                        }
                        
                        if (!isset($_SESSION['MultiSite'])) {
                            $_SESSION['MultiSite'] = array();
                        }
                        
                        $randomKey = rand();
                        
                        if (!isset($_SESSION['MultiSite']['executeSql'])) {
                            $_SESSION['MultiSite']['executeSql'] = array();
                        }
                        
                        if (!isset($_SESSION['MultiSite']['executeSql'][$randomKey])) {
                            $_SESSION['MultiSite']['executeSql'][$randomKey] = array();
                        }
                        
                        $totalWebsiteCount = 0;
                        
                        foreach ($websites as $website) {
                            if ($website) {
                                $_SESSION['MultiSite']['executeSql'][$randomKey][$website->getId()] = $params['post']['query'];
                                $totalWebsiteCount++;
                            }
                        }
                        $_SESSION['MultiSite']['totalWebsites'] = $totalWebsiteCount;
                        return array('status' => 'success','randomKey' => $randomKey, 'mode' => $params['post']['mode']);
                    }
                    break;

                case ComponentController::MODE_WEBSITE:
                    if (isset($params['post']['query']) && !empty($params['post']['query'])) {
                        $resultSet = array();
                        $querys   = $this->extractSqlQueries($params['post']['query']);
                        foreach ($querys as $key => $query) {
                            switch(true) {
                                case preg_match('/^(SELECT|DESC|SHOW|EXPLAIN|CHECK|OPTIMIZE|REPAIR|ANALYZE|CACHE INDEX)/', (strtoupper($query))):
                                    $objResult = $objDatabase->GetAll($query);
                                    $resultSet[$key]['query'] = $query;
                                    $resultSet[$key]['resultValue'] = (count($objResult) > 0) ? $objResult : $_ARRAYLANG['TXT_MULTISITE_NO_RECORD_FOUND'];
                                    break;
                                case preg_match('/^(UPDATE|DELETE|REPLACE|INSERT)/', (strtoupper($query))):
                                    $objResult = $objDatabase->Execute($query);
                                    $resultSet[$key]['query'] = $query;
                                    $resultSet[$key]['resultValue'] = '';
                                    if ($objResult) {
                                        switch (true) {
                                            case preg_match('/^INSERT/', (strtoupper($query))):
                                                $resultSet[$key]['resultValue'] = $objDatabase->Affected_Rows().sprintf($_ARRAYLANG['TXT_MULTISITE_NO_ROWS_AFFECTED'], 'inserted');
                                                break;
                                            case preg_match('/^DELETE/', (strtoupper($query))):
                                                $resultSet[$key]['resultValue'] = $objDatabase->Affected_Rows().sprintf($_ARRAYLANG['TXT_MULTISITE_NO_ROWS_AFFECTED'], 'deleted');
                                                break;
                                            default:
                                                $resultSet[$key]['resultValue'] = $objDatabase->Affected_Rows().sprintf($_ARRAYLANG['TXT_MULTISITE_NO_ROWS_AFFECTED'], 'affected');
                                                break;
                                        }
                                    }
                                    break;
                                default :
                                    $objResult = $objDatabase->Execute($query);
                                    $resultSet[$key]['query'] = $query;
                                    $resultSet[$key]['resultValue'] =  '';
                                    break;
                            }
                            if ($objResult !== false) {
                                $resultSet[$key]['queryStatus'] = "okbox";
                            } else {
                                $resultSet[$key]['queryStatus'] = "alertbox";
                            }
                        }
                        return array('status' => true, 'resultSet'=>  contrexx_raw2xhtml($resultSet), 'websiteName' => contrexx_raw2xhtml($params['post']['websiteName']));
                    } else {
                        return array('status' => false, 'error' => $_ARRAYLANG['TXT_MULTISITE_QUERY_IS_EMPTY']);
                    } 
                    break;

                default:
                    break;
            }
        } catch (\Exception $e) {
            throw new MultiSiteJsonException('JsonMultiSite (executeSql): failed to execute query' . $e->getMessage());
        }
        return false;
    }

    /**
     * split sql string
     *
     * split the sql string in sql queries
     *
     * @access private
     * @param string $input
     */
    protected function extractSqlQueries($input)
    {
        $input = trim($input);
        $queryStartPos = 0;
        $stringDelimiter = '';
        $isString = false;
        $isComment = false;
        $query = '';
        $arrSqlQueries = array();
        for ($charNr = 0; $charNr < strlen($input); $charNr++) {
            switch (true) {
                case ($isComment): // check if the loop is in a comment
                    if ($input[$charNr] == "\r" || $input[$charNr] == "\n") {
                        $isComment = false;
                        $queryStartPos = $charNr + 1;
                    }
                    break;
                case $isString: // check if the loop is in a string
                    if ($input[$charNr] == $stringDelimiter && ($input[$charNr - 1] != "\\" || $input[$charNr - 2] == "\\")) {
                        $isString = false;
                    }
                    break;
                case ($input[$charNr] == "#" || (!empty($input[$charNr + 1]) && $input[$charNr] . $input[$charNr + 1] == "--")):
                    $isComment = true;
                    break;
                case ($input[$charNr] == '"' || $input[$charNr] == "'" || $input[$charNr] == "`"): // check if this is a string delimiter
                    $isString = true;
                    $stringDelimiter = $input[$charNr];
                    break;
                case ($input[$charNr] == ";" || ($input[$charNr] != ";" && $charNr == strlen($input) - 1)): // end of query reached
                    $charNr++;
                    $query = ltrim(substr($input, $queryStartPos, $charNr - $queryStartPos));
                    array_push($arrSqlQueries, $query);
                    $queryStartPos = $charNr;
                    break;
            }
        }
        return $arrSqlQueries;
    }
    
    /**
     * Execute the Queued Sql Query
     * 
     * @return array
     * @throws MultiSiteJsonException
     */
    public function executeQueryBySession($params) {
        try {
            switch (\Cx\Core\Setting\Controller\Setting::getValue('mode','MultiSite')) {
                case ComponentController::MODE_MANAGER:
                    $sqlQuery = array();
                    $randomKey = isset($params['post']['randomKey']) ? $params['post']['randomKey'] : '';
                    if (!empty($randomKey)) {
                        $websiteRepo = \Env::get('em')->getRepository('Cx\Core_Modules\MultiSite\Model\Entity\Website');
                        if (is_object($_SESSION['MultiSite']['executeSql'][$randomKey])) {
                            $sqlQuery = $_SESSION['MultiSite']['executeSql'][$randomKey]->toArray();
                        }
                        if (!isset($_SESSION['MultiSite']) || empty($_SESSION['MultiSite']['executeSql'][$randomKey]) || empty($sqlQuery)) {
                            unset($_SESSION['MultiSite']['executeSql'][$randomKey]);
                            return array('status' => 'error', 'message' => 'There are no more websites in the queue.');
                        }
                        foreach ($_SESSION['MultiSite']['executeSql'][$randomKey] as $websiteId => $query) {
                            $website = $websiteRepo->findOneBy(array('id' => $websiteId));
                            $websiteName = $website->getFqdn()->getName();
                            $resp = self::executeCommandOnWebsite('executeSql', array('query' => $query, 'websiteName' => $websiteName), $website);
                            unset($_SESSION['MultiSite']['executeSql'][$randomKey][$websiteId]);
                            $websitesDone = $_SESSION['MultiSite']['totalWebsites'] - count($_SESSION['MultiSite']['executeSql'][$randomKey]);
                            return array('status' => 'success', 'queryResult' => $resp->data, 'totalWebsites' => $_SESSION['MultiSite']['totalWebsites'], 'websitesDone' => $websitesDone, 'websiteName' => $websiteName, 'randomKey' => $randomKey);
                        }
                    }
                    return array('status' => 'error', 'message' => 'Failed to execute the Query.');
                    break;    
            }
        } catch (\Exception $e) {
            throw new MultiSiteJsonException('JsonMultiSite (executeQueryBySession): failed to execute query' . $e->getMessage());
        }
    }
    
    /**
     * Stoping the query execution
     * 
     * @return result array
     */
    public function stopQueryExecution($params) {
        if (!empty($params['post']['sessionRandomKey'])) {
            if (isset($_SESSION['MultiSite']['executeSql'][$params['post']['sessionRandomKey']])) {
                unset($_SESSION['MultiSite']['executeSql'][$params['post']['sessionRandomKey']]);
            }
        }
        return array('status' => 'success', 'message' => 'The Query Execution was Stopped');
    }
    
    /**
     * Fetch the service plans of selected mail service server
     * 
     * @global array $_ARRAYLANG
     * @param array $params
     * 
     * @return array
     * @throws MultiSiteJsonException
     */
    public function getMailServicePlans($params) {
        global $_ARRAYLANG;

        self::loadLanguageData();
        try {
            switch (\Cx\Core\Setting\Controller\Setting::getValue('mode','MultiSite')) {
                case ComponentController::MODE_MANAGER:
                case ComponentController::MODE_HYBRID:
                    if (!isset($params['post']['mailServiceServerId']) && empty($params['post']['mailServiceServerId'])) {
                        \DBG::log('JsonMultiSite::getMailServicePlans() on ' . \Cx\Core\Setting\Controller\Setting::getValue('mode','MultiSite') . ' failed: Insufficient mapping information supplied: ' . var_export($params, true));
                        throw new MultiSiteJsonException($_ARRAYLANG['TXT_MULTISITE_NO_MAIL_SERVER_FOUND']);
                    }
                    $mailServiceServerId   = contrexx_input2raw($params['post']['mailServiceServerId']);
                    $mailServiceServerRepo = \Env::get('em')->getRepository('Cx\Core_Modules\MultiSite\Model\Entity\MailServiceServer');
                    $mailServiceServer     = $mailServiceServerRepo->findOneById($mailServiceServerId);
                    if (!$mailServiceServer) {
                        \DBG::log('JsonMultiSite::getMailServicePlans() mail service server is not found for supplied mail service id ' . $mailServiceServerId);
                        throw new MultiSiteJsonException($_ARRAYLANG['TXT_MULTISITE_NO_MAIL_SERVER_FOUND']);
                    }
                    
                    $hostingController = ComponentController::getMailServerHostingController($mailServiceServer);
                    
                    $plans = $hostingController->getAvailableServicePlansOfMailServer();
                    return array(
                            'status'  => 'success',
                            'result'  => empty($plans) ? $_ARRAYLANG['TXT_MULTISITE_MAIL_SERVICE_PLAN_EMPTY'] : $plans,
                        );                        
                default:
                    break;
            }
            return array(
                        'status' =>'error',
                        'message' => $_ARRAYLANG['TXT_MULTISITE_FAILED_TO_FETCH_MAIL_SERVICE_PLAN']
                    );  
        } catch (\Exception $e) {
            \DBG::log('JsonMultiSite::getMailServicePlans() failed: to get service plans from mail service server: ' . $e->getMessage());
            throw new MultiSiteJsonException($_ARRAYLANG['TXT_MULTISITE_FAILED_TO_FETCH_MAIL_SERVICE_PLAN']);
        }
    }
    
    /**
     * Track Affiliate Id
     */
    public function trackAffiliateId($params)
    {
        $post = isset($params['post']) ? $params['post'] : array();
        $url  = isset($post['url']) ? $post['url'] : '';
        if (empty($url)) {
            return;
        }
        
        switch (\Cx\Core\Setting\Controller\Setting::getValue('mode','MultiSite')) {
                case ComponentController::MODE_MANAGER:
                case ComponentController::MODE_HYBRID:
                    $affiliateIdQueryStringKey = \Cx\Core\Setting\Controller\Setting::getValue('affiliateIdQueryStringKey','MultiSite');
                    $urlParams = \Cx\Core\Routing\Url::params2array($url);
                    if (!array_key_exists($affiliateIdQueryStringKey, $urlParams)) {
                        return;
                    }
                    $affiliateId = $urlParams[$affiliateIdQueryStringKey];
                    if (ComponentController::isValidAffiliateId($affiliateId)) {
                        setcookie('MultiSiteAffiliateId', $affiliateId, time() + (86400 * 30), "/");
                    }
                default:
                    break;
        }
    }

    /**
     * Fetching License information
     * 
     * @param type $params
     * @return type
     * @throws MultiSiteJsonException
     */
    public function getLicense($params) {
        global $_CORELANG, $_ARRAYLANG;
        
        self::loadLanguageData();
        try {
            switch (\Cx\Core\Setting\Controller\Setting::getValue('mode','MultiSite')) {
                case ComponentController::MODE_MANAGER:
                case ComponentController::MODE_HYBRID:
                    if (!isset($params['post']['websiteId'])) {
                        throw new MultiSiteJsonException('JsonMultiSite::getLicense() on '.\Cx\Core\Setting\Controller\Setting::getValue('mode','MultiSite').' failed: Insufficient mapping information supplied: '.var_export($params, true));
                    }
                    //find User's Website
                    $webRepo   = \Env::get('em')->getRepository('Cx\Core_Modules\MultiSite\Model\Entity\Website');
                    $website   = $webRepo->findOneById($params['post']['websiteId']);
                    $params    = array(
                        'websiteId'   => $params['post']['websiteId'],
                        'activeLanguages'   => \FWLanguage::getActiveFrontendLanguages()
                    );
                    $resp = \Cx\Core_Modules\MultiSite\Controller\JsonMultiSite::executeCommandOnWebsite('getLicense', $params, $website);
                    if ($resp->status == 'success' && $resp->data->status == 'success') {
                        return $resp->data;
                    }
                    return array("status" => "error", "message" => sprintf($_ARRAYLANG['TXT_CORE_MODULE_MULTISITE_FETCH_LICENSE_FAILED'], $website->getFqdn()->getName()));
                    break;

                case ComponentController::MODE_WEBSITE:
                    $license = \Env::get('cx')->getLicense();
                    if (!$license) {
                        throw new MultiSiteJsonException('JsonMultiSite::getLicense(): on '.\Cx\Core\Setting\Controller\Setting::getValue('mode','MultiSite').' $license was not set properly');
                    }
                    $dashboardMessages = array();
                    $licenseMessage = array();
                    $licenseGrayzoneMessages = array();
                    $result = array();
                    foreach ($params['post']['activeLanguages'] as $languages) {
                        $lang_id = $languages['id'];
                        $lang_name = $languages['name'];
                        $licensemessageObj = $license->getMessage(false, \FWLanguage::getLanguageCodeById($lang_id), $_CORELANG);
                        $dashboardMessagesObj = $license->getMessage(true, \FWLanguage::getLanguageCodeById($lang_id), $_CORELANG);
                        $licenseGrayzoneMessagesObj = $license->getGrayzoneMessage(\FWLanguage::getLanguageCodeById($lang_id), $_CORELANG);
                        $licenseMessage[] = ($licensemessageObj->getLangCode() == \FWLanguage::getLanguageCodeById($lang_id)) ? array('lang_id' => $lang_id, 'lang_name' => $lang_name,'message'=> $licensemessageObj->getText()) : array('lang_id' => $lang_id, 'lang_name' => $lang_name, 'message' =>'');
                        $dashboardMessages[] = ($dashboardMessagesObj->getLangCode() == \FWLanguage::getLanguageCodeById($lang_id)) ? array('lang_id' => $lang_id, 'lang_name' => $lang_name, 'message' => $dashboardMessagesObj->getText()) : array('lang_id' => $lang_id, 'lang_name' => $lang_name, 'message' =>'');
                        $licenseGrayzoneMessages[] = ($licenseGrayzoneMessagesObj->getLangCode() == \FWLanguage::getLanguageCodeById($lang_id)) ? array('lang_id' => $lang_id, 'lang_name' => $lang_name, 'message' => $licenseGrayzoneMessagesObj->getText()) : array('lang_id' => $lang_id, 'lang_name' => $lang_name, 'message' =>'');
                    }
                    \Cx\Core\Setting\Controller\Setting::init('Config', '', 'Yaml');
                    $configCoreSetting = \Cx\Core\Setting\Controller\Setting::getArray('Config', 'core');
                    if ($configCoreSetting) {
                        $result['installationId'] = array("type" => $configCoreSetting['installationId']['type'], "values" => $configCoreSetting['installationId']['values'], "content" => $configCoreSetting['installationId']['value']);
                    }
                    
                    $licenseConfig = array('license', 'release');
                    foreach ($licenseConfig as $value) {
                        foreach (\Cx\Core\Setting\Controller\Setting::getArray('Config', $value) as $key => $value) {
                            $value['type'] = ($value['type'] == 'text') ? 'textarea' : $value['type'];
                            if (in_array($key, array('licensePartner', 'licenseCustomer'))) {
                                switch ($key) {
                                    case 'licensePartner':
                                        $result['licensePartnerTitle'] = array("type" => $value['type'], "values" => $value['values'], "content" => $license->getPartner()->getTitle());
                                        $result['licensePartnerLastname'] = array("type" => $value['type'], "values" => $value['values'], "content" => $license->getPartner()->getLastname());
                                        $result['licensePartnerFirstname'] = array("type" => $value['type'], "values" => $value['values'], "content" => $license->getPartner()->getFirstname());
                                        $result['licensePartnerCompanyname'] = array("type" => $value['type'], "values" => $value['values'], "content" => $license->getPartner()->getCompanyName());
                                        $result['licensePartnerAddress'] = array("type" => $value['type'], "values" => $value['values'], "content" => $license->getPartner()->getAddress());
                                        $result['licensePartnerZip'] = array("type" => $value['type'], "values" => $value['values'], "content" => $license->getPartner()->getZip());
                                        $result['licensePartnerCity'] = array("type" => $value['type'], "values" => $value['values'], "content" => $license->getPartner()->getCity());
                                        $result['licensePartnerCountry'] = array("type" => $value['type'], "values" => $value['values'], "content" => $license->getPartner()->getCountry());
                                        $result['licensePartnerPhone'] = array("type" => $value['type'], "values" => $value['values'], "content" => $license->getPartner()->getPhone());
                                        $result['licensePartnerUrl'] = array("type" => $value['type'], "values" => $value['values'], "content" => $license->getPartner()->getUrl());
                                        $result['licensePartnerMail'] = array("type" => $value['type'], "values" => $value['values'], "content" => $license->getPartner()->getMail());
                                        break;
                                    case 'licenseCustomer':
                                        $result['licenseCustomerTitle'] = array("type" => $value['type'], "values" => $value['values'], "content" => $license->getCustomer()->getTitle());
                                        $result['licenseCustomerLastname'] = array("type" => $value['type'], "values" => $value['values'], "content" => $license->getCustomer()->getLastname());
                                        $result['licenseCustomerFirstname'] = array("type" => $value['type'], "values" => $value['values'], "content" => $license->getCustomer()->getFirstname());
                                        $result['licenseCustomerCompanyname'] = array("type" => $value['type'], "values" => $value['values'], "content" => $license->getCustomer()->getCompanyName());
                                        $result['licenseCustomerAddress'] = array("type" => $value['type'], "values" => $value['values'], "content" => $license->getCustomer()->getAddress());
                                        $result['licenseCustomerZip'] = array("type" => $value['type'], "values" => $value['values'], "content" => $license->getCustomer()->getZip());
                                        $result['licenseCustomerCity'] = array("type" => $value['type'], "values" => $value['values'], "content" => $license->getCustomer()->getCity());
                                        $result['licenseCustomerCountry'] = array("type" => $value['type'], "values" => $value['values'], "content" => $license->getCustomer()->getCountry());
                                        $result['licenseCustomerPhone'] = array("type" => $value['type'], "values" => $value['values'], "content" => $license->getCustomer()->getPhone());
                                        $result['licenseCustomerUrl'] = array("type" => $value['type'], "values" => $value['values'], "content" => $license->getCustomer()->getUrl());
                                        $result['licenseCustomerMail'] = array("type" => $value['type'], "values" => $value['values'], "content" => $license->getCustomer()->getMail());
                                        break;
                                    default: 
                                        break;
                                }
                            } else {
                                $result[$key] = array("type" => $value['type'], "values" => explode(',', $value['values']));
                            }
                            switch ($key) {
                                case 'licenseKey':
                                case 'licenseState':
                                case 'upgradeUrl':
                                case 'isUpgradable':
                                case 'licenseGrayzoneTime':
                                case 'licenseLockTime':
                                case 'licenseUpdateInterval':
                                case 'licenseFailedUpdate':
                                case 'coreCmsEdition':
                                case 'coreCmsVersion':
                                case 'coreCmsCodeName':
                                case 'coreCmsStatus':
                                case 'coreCmsName':
                                case 'licenseSuccessfulUpdate':
                                    $result[$key]['content'] = $value['value'];
                                    break;
                                case 'licenseCreatedAt':
                                case 'coreCmsReleaseDate':
                                    $result[$key]['content'] = date('d.m.Y', $value['value']);
                                    break;
                                case 'licenseValidTo':
                                    $result[$key]['content'] = date('d.m.Y h:i:s', $value['value']);;
                                    break;
                                case 'licenseMessage':
                                    $result[$key]['content'] = $licenseMessage;
                                    break;
                                case 'licenseDomains':
                                    $result[$key]['content'] = implode(', ', $license->getRegisteredDomains());
                                    break;
                                case 'availableComponents':
                                    $result[$key]['content'] = serialize($license->getLicensedComponentsWithAdditionalData());
                                    break;
                                case 'dashboardMessages':
                                    $result[$key]['content'] = $dashboardMessages;
                                    break;
                                case 'licenseGrayzoneMessages':
                                    $result[$key]['content'] = $licenseGrayzoneMessages;
                                    break;
                                default:
                                    break;
                            }
                        }
                    }
                    if ($result) {
                        return array(
                            'status' => "success",
                            'result' => contrexx_raw2xhtml($result),
                        );
                    }
                    break;

                default:
                    break;
            }
        } catch (\Exception $e) {
            throw new MultiSiteJsonException('JsonMultiSite::getLicense() failed: to get License Information: ' . $e->getMessage());
        }
    }

    /**
     * Remove the user
     * 
     * @param  array $params
     * @return boolean true or false
     * @throws MultiSiteJsonException
     */
    public function removeUser($params) {
        global $_ARRAYLANG;
        
        self::loadLanguageData();
        switch (\Cx\Core\Setting\Controller\Setting::getValue('mode','MultiSite')) {
            case \Cx\Core_Modules\MultiSite\Controller\ComponentController::MODE_HYBRID:
            case \Cx\Core_Modules\MultiSite\Controller\ComponentController::MODE_SERVICE:
            case \Cx\Core_Modules\MultiSite\Controller\ComponentController::MODE_WEBSITE:
                if (empty($params['post'])) {
                    \DBG::log($_ARRAYLANG['TXT_CORE_MODULE_MULTISITE_UNKOWN_USER_REQUEST']);
                    throw new MultiSiteJsonException($_ARRAYLANG['TXT_CORE_MODULE_MULTISITE_UNKOWN_USER_REQUEST']);
                }
                $websiteRepository = \Env::get('em')->getRepository('Cx\Core_Modules\MultiSite\Model\Entity\Website');
                $website = $websiteRepository->findWebsitesByCriteria(array('user.id' => $params['post']['userId']));
                if (!$website) {
                    $objUser = \FWUser::getFWUserObject()->objUser->getUser($params['post']['userId']);
                    $deleteUser = $objUser ? $objUser->delete() : true;
                    if ($deleteUser) {
                        return array(
                            'status'    => 'success',
                            'log'       => \DBG::getMemoryLogs(),
                        );
                    }
                }
                return array(
                    'status'    => 'error',
                    'log'       => \DBG::getMemoryLogs(),
                );
                break;
            default:
                break;
        }
    }
    
    /**
     * Remote Login to website
     * 
     * @param type $params websiteId
     * 
     * @return array 
     */
    public function remoteLogin($params)
    {
        global $_ARRAYLANG;
        self::loadLanguageData();
        
        $websiteId = $params['post']['websiteId'];
        if (empty($websiteId)) {
            return false;
        }
        $loginType = ($params['post']['loginType'] == 'customerpanel') ? 'customerpanel' : 'website';
        try {
            switch (\Cx\Core\Setting\Controller\Setting::getValue('mode','MultiSite')) {
                case \Cx\Core_Modules\MultiSite\Controller\ComponentController::MODE_MANAGER:
                case \Cx\Core_Modules\MultiSite\Controller\ComponentController::MODE_HYBRID:
                    $websiteRepository = \Env::get('em')->getRepository('Cx\Core_Modules\MultiSite\Model\Entity\Website');
                    $website   = $websiteRepository->findOneBy(array('id' => $websiteId));
                    if (!$website) {
                        return array('status' => 'error', 'message' => sprintf($_ARRAYLANG['TXT_CORE_MODULE_MULTISITE_REMOTE_LOGIN_FAILED'], $loginType));
                    }
                                    
                    if ($loginType == 'customerpanel') {
                        $response = self::executeCommandOnManager('generateAuthToken', array('ownerId' => $website->getOwner()->getId()));
                        if ($response->status == 'error' || $response->data->status == 'error') {
                            return array('status' => 'error', 'message' => sprintf($_ARRAYLANG['TXT_CORE_MODULE_MULTISITE_REMOTE_LOGIN_FAILED'], $loginType));
                        }
                        $successMessage = $_ARRAYLANG['TXT_CORE_MODULE_MULTISITE_REMOTE_LOGIN_CUSTOMERPANELDOMAIN_SUCCESS'];
                        $websiteLoginUrl = \Cx\Core\Routing\Url::fromMagic(ComponentController::getApiProtocol() . \Cx\Core\Setting\Controller\Setting::getValue('customerPanelDomain','MultiSite'). '/index.php?section=Login&user-id='.$response->data->userId.'&auth-token='.$response->data->authToken);
                    } else {
                        list($websiteOwnerUserId, $authToken) = $website->generateAuthToken();
                        
                        $websiteName     = $website->getBaseDn()->getName();
                        $websiteLoginUrl = \Cx\Core\Routing\Url::fromMagic(ComponentController::getApiProtocol() . $websiteName . \Env::get('cx')->getWebsiteBackendPath() . '/?user-id='.$websiteOwnerUserId.'&auth-token='.$authToken);
                        $successMessage  = sprintf($_ARRAYLANG['TXT_CORE_MODULE_MULTISITE_REMOTE_LOGIN_SUCCESS'], $websiteName);
                    }
                    return array('status' => 'success', 'message' => $successMessage,'webSiteLoginUrl' => $websiteLoginUrl->toString());
                    break;
                default:
                    break;
            }
        } catch (\Exception $e) {
            throw new MultiSiteJsonException('JsonMultiSite::remoteLogin() failed: to get remote website Login Link: ' . $e->getMessage());
        }
    }
    
    /**
     * Edit/Update License data
     * 
     * @param array $params Post values of License
     * @return array
     * @throws MultiSiteJsonException
     */
    public function editLicense($params)
    {
        global $_ARRAYLANG;
        
        self::loadLanguageData();
        $websiteId = $params['post']['websiteId'];
        $licenseOption = $params['post']['licenseLabel'];
        $licenseValue = $params['post']['licenseValue'];
        if (!$websiteId) {
            throw new MultiSiteJsonException('Invalid websiteId for the command JsonMultiSite::editLicense.');
        }
        try{
            switch (\Cx\Core\Setting\Controller\Setting::getValue('mode','MultiSite')) {
                case \Cx\Core_Modules\MultiSite\Controller\ComponentController::MODE_MANAGER:
                case \Cx\Core_Modules\MultiSite\Controller\ComponentController::MODE_HYBRID:
                    $dateFormatArr = array("coreCmsReleaseDate", "licenseValidTo", "licenseCreatedAt");
                    $paramsArray = array(
                        $licenseOption => in_array($licenseOption, $dateFormatArr) ? strtotime($licenseValue) : $licenseValue,
                        'websiteId' => $websiteId
                    );
                    $webRepo     = \Env::get('em')->getRepository('Cx\Core_Modules\MultiSite\Model\Entity\Website');
                    $website     = $webRepo->findOneById($websiteId);
                    $resp        = \Cx\Core_Modules\MultiSite\Controller\JsonMultiSite::executeCommandOnWebsite('setLicense', $paramsArray, $website);
                    if (($resp->status == 'success') && ($resp->data->status == 'success')) {
                        return array('status' => 'success', 'data' => $licenseValue, 'message' => sprintf($_ARRAYLANG['TXT_CORE_MODULE_MULTISITE_LICENSE_UPDATE_SUCCESS'], $licenseOption));
                    }
                    return array('status' => 'error','message' => sprintf($_ARRAYLANG['TXT_CORE_MODULE_MULTISITE_LICENSE_UPDATE_FAILED'], $licenseOption));
                    break;
                default:
                    break;
            }
        } catch (\Exception $e) {
            throw new MultiSiteJsonException('JsonMultiSite::editLicense() failed: to Update License Information of the This Website: ' . $e->getMessage());
        }
    }
    
    /**
     * sending account activation email to the user.
     * 
     * @return type
     * @throws MultiSiteJsonException
     */
    public function sendAccountActivation($params) {
        global $_ARRAYLANG;
        try {
            switch (\Cx\Core\Setting\Controller\Setting::getValue('mode','MultiSite')) {
                case \Cx\Core_Modules\MultiSite\Controller\ComponentController::MODE_MANAGER:
                    if (empty($params['post']['ownerEmail']) || empty($params['post']['websiteName'])) {
                        throw new MultiSiteJsonException('JsonMultiSite::sendAccountActivation() failed: Insufficient arguments supplied: ' . var_export($params, true));
                    }
                    $objOwner = \FWUser::getFWUserObject()->objUser->getUser(array('email' => $params['post']['ownerEmail'])); 
                    $websiteRepository = \Env::get('em')->getRepository('Cx\Core_Modules\MultiSite\Model\Entity\Website');
                    $websiteObj = $websiteRepository->findWebsitesByCriteria(array('user.id' => $objOwner->getId(), 'website.name' => $params['post']['websiteName']));
                    $website    = current($websiteObj);
                    if (!$website) {
                        throw new MultiSiteJsonException('JsonMultiSite::sendAccountActivation() failed: Unknown Website-User-Id: ' . $objOwner->getId());
                    }
                    
                    $websiteVerificationUrl = \FWUser::getVerificationLink(true, $objOwner, $website->getBaseDn()->getName());
                    // write mail
                    \Cx\Core\MailTemplate\Controller\MailTemplate::init('MultiSite');
                    \DBG::msg('Website: send Account Activation Email > ADMIN');
                    $info = array(
                        'section'      => 'Multisite',
                        'key'          => 'accountActivation',
                        'to'           => $website->getOwner()->getEmail(),
                        'substitution' => array(
                            'WEBSITE_ACCOUNT_VERIFICATION_URL' => $websiteVerificationUrl,
                            'WEBSITE_ACCOUNT_VERIFICATION_DUE_DATE' => date(ASCMS_DATE_FORMAT_DATE, $website->getOwner()->getRestoreKeyTime()),
                        )
                    );
                    if (!\Cx\Core\MailTemplate\Controller\MailTemplate::send($info)) {
                        throw new MultiSiteJsonException(__METHOD__ . ': Unable to send account activation e-mail to user');
                    }
                    \DBG::msg('Website: Sent Account Activation Email > SUCCESS');
                    return array('status' => 'success', 'date' => date(ASCMS_DATE_FORMAT_DATE, $website->getOwner()->getRestoreKeyTime()));
                    break;
                case \Cx\Core_Modules\MultiSite\Controller\ComponentController::MODE_SERVICE:
                    if (empty($params['post']['ownerEmail']) || empty($params['post']['websiteName'])) {
                        throw new MultiSiteJsonException('JsonMultiSite::sendAccountActivation() failed: Insufficient arguments supplied: ' . var_export($params, true));
                    }
                    
                    $response = self::executeCommandOnManager('sendAccountActivation', array('ownerEmail' => $params['post']['ownerEmail'], 'websiteName' => $params['post']['websiteName']));
                    if ($response->status == 'success' && $response->data->status == 'success') {
                        return array('status' => 'success', 'date' => $response->data->date);
                    }
                    break;
                case \Cx\Core_Modules\MultiSite\Controller\ComponentController::MODE_WEBSITE:
                    $websiteName = \Cx\Core\Setting\Controller\Setting::getValue('websiteName','MultiSite');
                    $ownerEmail = \FWUser::getFWUserObject()->objUser->getUser(\Cx\Core\Setting\Controller\Setting::getValue('websiteUserId','MultiSite'))->getEmail();
                    if (!empty($ownerEmail) && !empty($websiteName)) {
                        $resp = self::executeCommandOnMyServiceServer('sendAccountActivation', array('ownerEmail' => $ownerEmail, 'websiteName' => $websiteName));
                        self::loadLanguageData();
                        if ($resp->status == 'success' && $resp->data->status == 'success') {
                           return array('status' => $resp->data->status, 'message' => sprintf($_ARRAYLANG['TXT_MULTISITE_ACCOUNT_ACTIVATION_MAIL_RESENT'], '<span class="highlight">'.$ownerEmail.'</span>', '<span class="highlight">'.$resp->data->date.'</span>'));
                        }
                    }
                    break;
                default :
                    break;
            }
            return array('status' => 'error', 'message' => 'JsonMultiSite::sendAccountActivation() failed: to Send Account Activation Mail of this Website.');
        } catch (\Exception $e) {
            throw new MultiSiteJsonException('JsonMultiSite::sendAccountActivation() failed: to Send Account Activation Mail of this Website: ' . $e->getMessage());
        }
    }
    
    /**
     * Get the Payrexx Url
     * 
     * @param array $params
     * 
     * @return array
     * @throws MultiSiteJsonException
     */
    public function getPayrexxUrl($params) {
        if (!isset($params['post']['product_id'])) {
            throw new MultiSiteJsonException('JsonMultiSite::getPayrexxUrl() failed: Insufficient mapping information supplied: ' . var_export($params, true));
        }

        try {
            $productRepository = \Env::get('em')->getRepository('Cx\Modules\Pim\Model\Entity\Product');
            $product = $productRepository->findOneBy(array('id' => $params['post']['product_id']));
            
            if (\FWValidator::isEmpty($product)) {
                return;
            }
            
            $objUser = null;
            if (ComponentController::isUserLoggedIn()) {
                $objUser = \FWUser::getFWUserObject()->objUser;
            }
            
            $productName = $product->getName();
			$renewalOption = isset($params['post']['renewalOption']) ? $params['post']['renewalOption'] : 'monthly';
            list($renewalUnit, $renewalQuantifier) = self::getProductRenewalUnitAndQuantifier($renewalOption);
            $currency = ComponentController::getUserCurrency($objUser ? $objUser->getCrmUserId() : 0);
            $productPrice = $product->getPaymentAmount($renewalUnit, $renewalQuantifier, $currency);
            $referenceId = '';
            $purpose     = '';
            if (isset($params['post']['multisite_address']) && !\FWValidator::isEmpty($params['post']['multisite_address'])) {
                $referenceId = "|{$product->getId()}|name|{$params['post']['multisite_address']}|";
                $purpose     = $productName . ' - ' . $params['post']['multisite_address'];
            } elseif ($objUser) {
                $userId      = $objUser->getId();
                $referenceId = "|{$product->getId()}|owner|$userId|";
                $purpose     = $productName;
            } else {
                throw new MultiSiteJsonException('JsonMultiSite::getPayrexxUrl() failed: Insufficient mapping information supplied: ' . var_export($params, true));
            }
            
            $instanceName  = \Cx\Core\Setting\Controller\Setting::getValue('payrexxAccount','MultiSite');
            $apiSecret     = \Cx\Core\Setting\Controller\Setting::getValue('payrexxApiSecret','MultiSite');

            $payrexx = new \Payrexx\Payrexx($instanceName, $apiSecret);
            
            $invoice = new \Payrexx\Models\Request\Invoice();
            $invoice->setReferenceId($referenceId);
            $invoice->setTitle($purpose);
            $invoice->setDescription('');
            $invoice->setPurpose($purpose);
            $invoice->setPsp(5);
            $invoice->setAmount($productPrice * 100);
            $invoice->setCurrency(\Payrexx\Models\Request\Invoice::CURRENCY_CHF);
            
            $invoice->addField('email', true, (isset($params['post']['multisite_email_address']) ? $params['post']['multisite_email_address'] : ($objUser ? $objUser->getEmail() : '')));
            
            $invoice->setSubscriptionState(true);

            list($subscriptionPeriod, $subscriptionInterval, $cancellationInterval)
                        = self::getSubscriptionPeriodAndIntervalsByProduct($product, $renewalUnit, $renewalQuantifier);
            
            // set payment interval
            $invoice->setSubscriptionInterval(\DateTimeTools::getDateIntervalAsString($subscriptionInterval));

            // set subscription period
            $invoice->setSubscriptionPeriod(\DateTimeTools::getDateIntervalAsString($subscriptionPeriod));

            // set cancellation period
            $invoice->setSubscriptionCancellationInterval(\DateTimeTools::getDateIntervalAsString($cancellationInterval));

            $response = $payrexx->create($invoice);            
            if ($response instanceof \Payrexx\Models\Response\Invoice && !\FWValidator::isEmpty($response->getLink())) {
                return array('status' => 'success', 'link' => $response->getLink());
            }
        } catch (\Payrexx\PayrexxException $e) {
            throw new MultiSiteJsonException('JsonMultiSite::getPayrexxUrl() failed: to get the payrexx url: ' . $e->getMessage());
        }
    }

    /**
     * Fetch delete and Update Multisite Configuration of the selected Website
     * 
     * @param array $params websiteConfigArray
     * 
     * @return array
     * @throws MultiSiteJsonException
     */
    public function modifyMultisiteConfig($params) 
    {
        global $_ARRAYLANG;
        self::loadLanguageData();
        
        $websiteId = isset($params['post']['websiteId']) ? $params['post']['websiteId'] : '';
        $configGroup = isset($params['post']['configGroup']) ? $params['post']['configGroup'] : '';
        $configValue = isset($params['post']['configValue']) ? $params['post']['configValue'] : '';
        $configValues = isset($params['post']['configValues']) ? $params['post']['configValues'] : '';
        $configName = isset($params['post']['configOption']) ? $params['post']['configOption'] : '';
        $configType = isset($params['post']['configType']) ? $params['post']['configType'] : '';
        $operation  = isset($params['post']['operation']) ? $params['post']['operation'] : 'fetch';
        if (!$websiteId) {
            throw new MultiSiteJsonException('Invalid websiteId for the command JsonMultiSite::modifyMultisiteConfig.');
        }
        try {
            switch (\Cx\Core\Setting\Controller\Setting::getValue('mode','MultiSite')) {
                case \Cx\Core_Modules\MultiSite\Controller\ComponentController::MODE_MANAGER:
                case \Cx\Core_Modules\MultiSite\Controller\ComponentController::MODE_HYBRID:
                    $webRepo = \Env::get('em')->getRepository('Cx\Core_Modules\MultiSite\Model\Entity\Website');
                    $website = $webRepo->findOneById($websiteId);
                    $inputTypes = array(
                                    \Cx\Core\Setting\Controller\Setting::TYPE_TEXT,
                                    \Cx\Core\Setting\Controller\Setting::TYPE_TEXTAREA,
                                    \Cx\Core\Setting\Controller\Setting::TYPE_PASSWORD,
                                    \Cx\Core\Setting\Controller\Setting::TYPE_DROPDOWN,
                                    \Cx\Core\Setting\Controller\Setting::TYPE_RADIO
                                );
                    $params = ($operation !="fetch") ? array(
                                                            'websiteId' => $websiteId,
                                                            'configGroup' => $configGroup,
                                                            'configOption' => $configName,
                                                            'configValue' => $configValue,
                                                            'configType' => $configType,
                                                            'configValues' => $configValues,
                                                            'operation' => $operation
                                                       ) : array('websiteId' => $websiteId);
                    $resp = \Cx\Core_Modules\MultiSite\Controller\JsonMultiSite::executeCommandOnWebsite('modifyMultisiteConfig', $params, $website);
                    if ($resp->status == 'success' && $resp->data->status) {
                        switch ($resp->data->multisiteConfig) {
                            case 'add':
                            case 'edit':
                            case 'delete':
                                return array('status' => 'success', 'message' => $resp->data->message);
                                break;
                            case 'fetch':
                            default:
                                return array('status' => 'success', 'message' => sprintf($_ARRAYLANG['TXT_CORE_MODULE_MULTISITE_WEBSITE_CONFIG_FETCH_SUCCESSFUL'], $website->getFqdn()->getName()), 'result' => $resp->data->result, 'inputTypes' => $inputTypes);
                                break;
                        }
                    } else {
                        switch ($resp->data->multisiteConfig) {
                            case 'add':
                            case 'edit':
                            case 'delete':
                                return array('status' => 'error', 'message' => $resp->data->message);
                                break;
                            case 'fetch':
                            default:
                                return array('status' => 'error', 'message' => sprintf($_ARRAYLANG['TXT_CORE_MODULE_MULTISITE_WEBSITE_CONFIG_FETCH_FAILED'], $operation, $website->getFqdn()->getName()));
                                break;
                        }
                    }
                    break;
                case \Cx\Core_Modules\MultiSite\Controller\ComponentController::MODE_WEBSITE:
                    switch ($operation) {
                        case 'add':
                            if (!empty($configName) && !empty($configType)) {
                                if (($configType == \Cx\Core\Setting\Controller\Setting::TYPE_DROPDOWN || $configType == \Cx\Core\Setting\Controller\Setting::TYPE_RADIO)
                                        && empty($configValues)) {
                                    return array('status' => "success", "success"=> false, 'multisiteConfig' => $operation, 'message' => $_ARRAYLANG['TXT_CORE_MODULE_MULTISITE_ADD_CONFIG_FAILED']);
                                }
                                
                                if (!\Cx\Core\Setting\Controller\Setting::isDefined($configName) 
                                        && \Cx\Core\Setting\Controller\Setting::add($configName, $configValue, 1, $configType, $configValues, $configGroup)) {
                                    return array('status' => "success", "success"=> true, 'multisiteConfig' => $operation, 'message' => $_ARRAYLANG['TXT_CORE_MODULE_MULTISITE_ADD_CONFIG_SUCCESSFUL'].$configName);
                                }
                                
                                return array('status' => "success", "success"=> false, 'multisiteConfig' => $operation, 'message' => sprintf($_ARRAYLANG['TXT_CORE_MODULE_MULTISITE_ADD_CONFIG_EXISTS'], $configName));
                            }
                            return array('status' => "success", "success"=> false, 'multisiteConfig' => $operation, 'message' => $_ARRAYLANG['TXT_CORE_MODULE_MULTISITE_ADD_CONFIG_FAILED']);
                            break;
                        case 'edit':
                            if (!empty($configName)) {
                                \Cx\Core\Setting\Controller\Setting::set($configName, $configValue);
                                if (\Cx\Core\Setting\Controller\Setting::update($configName)) {
                                    return array('status' => 'success', "success"=> true, 'multisiteConfig' => $operation, 'message' => $_ARRAYLANG['TXT_CORE_MODULE_MULTISITE_WEBSITE_CONFIG_UPDATE_SUCCESSFUL'] . $configName);
                                }
                            }
                            return array('status' => 'success', "success"=> false, 'multisiteConfig' => $operation, 'message' => $_ARRAYLANG['TXT_CORE_MODULE_MULTISITE_WEBSITE_CONFIG_UPDATE_FAILED'] . $configName);
                            break;
                        case 'delete':
                            if (!empty($configName)) {
                                if (\Cx\Core\Setting\Controller\Setting::delete($configName, $configGroup)) {
                                    return array('status' => 'success', "success"=> true, 'multisiteConfig' => $operation, 'message' => $_ARRAYLANG['TXT_CORE_MODULE_MULTISITE_WEBSITE_CONFIG_DELETE_SUCCESSFUL'] . $configName);
                                }  
                            }
                            return array('status' => 'success', "success"=> false, 'multisiteConfig' => $operation, 'message' => $_ARRAYLANG['TXT_CORE_MODULE_MULTISITE_WEBSITE_CONFIG_DELETE_FAILED'] . $configName);
                            break;
                        case 'fetch':
                        default:
                            \Cx\Core\Setting\Controller\Setting::init('MultiSite', '', 'FileSystem');
                            $multisiteConfigArray = \Cx\Core\Setting\Controller\Setting::getArray('MultiSite');
                            if ($multisiteConfigArray) {
                                return array('status' => 'success', "success"=> true,'result' => $multisiteConfigArray, 'multisiteConfig' => $operation);
                            }
                            break;
                    }
                    break;
                default:
                    break;
            }
        } catch (\Exception $ex) {
            throw new MultiSiteJsonException('JsonMultiSite::modifyMultisiteConfig() failed: to Fetch the Multisite Configuration of the This Website: ' . $e->getMessage());
        }
    }

    /**
     * To set the values to entity.
     * 
     * @param array $param websiteTemplate array
     * 
     * @throws MultiSiteJsonException
     */
    public function push($param) {
	try {
            if (empty($param['post']['dataType']) && empty($param['post']['data'])) {
                    return;
            }
            $objDataSet = new \Cx\Core_Modules\Listing\Model\Entity\DataSet($param['post']['data']);
            $objEntityInterface = new \Cx\Core_Modules\Listing\Model\Entity\EntityInterface();
            $objEntityInterface->setEntityClass($param['post']['dataType']);

            $entity = current($objDataSet->export($objEntityInterface));

            \Env::get('em')->persist($entity);
            $entityObject = \Env::get('em')->getClassMetadata(get_class($entity));  
            $entityObject->setIdGeneratorType(\Doctrine\ORM\Mapping\ClassMetadata::GENERATOR_TYPE_NONE);
            \Env::get('em')->flush();
	} catch (\Exception $e) {
            throw new MultiSiteJsonException('JsonMultiSite::push() failed: To add / update the repository'. $e->getMessage());
	}
}

    /**
     * To take websiteBackup to specified location in service server
     * 
     * @param array $param website data
     * 
     * @return array
     */
    public function websiteBackup($param) {
        $websiteId         = isset($param['post']['websiteId']) ? contrexx_input2raw($param['post']['websiteId']) : '';
        $backupLocation    = !empty($param['post']['backupLocation']) ? $param['post']['backupLocation'] : \Cx\Core\Setting\Controller\Setting::getValue('websiteBackupLocation','MultiSite');
        $websiteRepository = \Env::get('em')->getRepository('Cx\Core_Modules\MultiSite\Model\Entity\Website');
        $websites          = (!\FWValidator::isEmpty($websiteId)) ? $websiteRepository->findBy(array('id' => $websiteId)) : $websiteRepository->findAll();

        //change the backup location path to absolute location path
        \Cx\Lib\FileSystem\FileSystem::path_absolute_to_os_root($backupLocation);

        if (!\FWValidator::isEmpty($websites)) {
            foreach ($websites as $website) {
                if (!$website instanceof \Cx\Core_Modules\MultiSite\Model\Entity\Website) {
                    \DBG::log('Unknown website requested');
                    continue;
                }
                
                if (!($this->websiteDataBackup($website, $backupLocation))) {
                    \DBG::log('Failed to backup the ' . $website->getName() . ' !.');
                    continue;
                }
            }
            return array('status' => 'success', 'message' => 'Successfully Backup the website Repository');
        }
        return array('status' => 'error', 'message' => 'Failed to Backup the website Repository!');
    }

    /**
     * Website Data Repository backup
     * 
     * @param \Cx\Core_Modules\MultiSite\Model\Entity\Website $website        websiteObject
     * @param string                                          $backupLocation websiteBackupLocation
     * @return boolean
     */
    public function websiteDataBackup(\Cx\Core_Modules\MultiSite\Model\Entity\Website $website, $backupLocation) {
        $websiteName       = $website->getName();
        $websitePath       = \Cx\Core\Setting\Controller\Setting::getValue('websitePath','MultiSite') . '/' . $websiteName;
        $websiteBackupPath = $backupLocation . '/' . $website->getName();
        
        if (!\Cx\Lib\FileSystem\FileSystem::exists($websitePath)) {
            \DBG::log('Failed to backup the ' . $websiteName . ' due to the website may not exists in the repository!.');
            return false;
        }
        
        if (!\Cx\Lib\FileSystem\FileSystem::exists($backupLocation) && !\Cx\Lib\FileSystem\FileSystem::make_folder($backupLocation)) {
            \DBG::log('Failed to create the backup location');
            return false;
        }
        
        if (!$this->websiteInfoBackup($website->getId(), $websiteBackupPath)) {
            \DBG::log('Failed to backup the website info  for  ' . $websiteName);
            return false;
        }
        
        if (!$this->websiteDatabaseBackup($websiteBackupPath, $websitePath)) {
            \DBG::log('Failed to backup the database for  ' . $websiteName);
            return false;
        }
        
        if (!$this->websiteRepositoryBackup($websiteBackupPath, $websitePath)) {
            \DBG::log('Failed to backup the database for  ' . $websiteName);
            return false;
        }
        
        if (!$this->createWebsiteArchive($websiteBackupPath)) {
            \DBG::log('Failed to create backup Archiev for  ' . $websiteName . ' in the location ' . $backupLocation);
            return false;
        }
        return true;
    }

    /**
     * Create Website Zip Archive for the given Location
     * 
     * @param string $websiteBackupPath websiteBackupLocation
     * 
     * @return boolean
     */
    public function createWebsiteArchive($websiteBackupPath) {
        $websiteZipFileName      = $this->websiteZipArchiveName($websiteBackupPath);
        $websiteZipArchive       = new \PclZip($websiteZipFileName . '.zip');
        $websiteArchiveFileCount = $websiteZipArchive->add($websiteBackupPath, PCLZIP_OPT_REMOVE_PATH, $websiteBackupPath);

        if ($websiteArchiveFileCount == 0) {
            \DBG::log('Failed to create Zip Archiev' . $websiteZipArchive->errorInfo(true));
            return false;
        }

        $explodeFileCount = $websiteZipArchive->delete(PCLZIP_OPT_BY_PREG, '/.ftpaccess$/');
        if ($explodeFileCount == 0) {
            \DBG::log('Failed to explode .ftpaccess in the  Archiev' . $websiteZipArchive->errorInfo(true));
            return false;
        }

        //cleanup website Backup Folder
        \Cx\Lib\FileSystem\FileSystem::delete_folder($websiteBackupPath, true);
        return true;
    }

    /**
     * Set websiteZipArchiveName
     * 
     * @param string $websiteBackupPath website backupLocation
     * 
     * @return string
     */
    public function websiteZipArchiveName($websiteBackupPath) {
        $i = 1;
        $websiteZipFileName = $websiteBackupPath;

        while (\Cx\Lib\FileSystem\FileSystem::exists($websiteZipFileName . '.zip')) {
            $websiteZipFileName = $websiteBackupPath . '_' . $i;
            $i++;
        }
        return $websiteZipFileName;
    }

    /**
     * Website Repository Backup
     * 
     * @param string $websiteBackupPath websiteBackup Location
     * @param string $websitePath       websitePath
     * 
     * @return boolean
     */
    public function websiteRepositoryBackup($websiteBackupPath, $websitePath) {
        if (!(\Cx\Lib\FileSystem\FileSystem::exists($websitePath) && \Cx\Lib\FileSystem\FileSystem::copy_folder($websitePath, $websiteBackupPath . '/dataRepository', true))) {
            \DBG::log('Failed to copy the website from ' . $websitePath . 'to ' . $websiteBackupPath);
            return false;
        }
        return true;
    }
    
    /**
     * get website Info data
     * 
     * @param array $params
     * @return boolean
     */
    public function getWebsiteInfo($params)
    {
        if (!$params['post']['websiteId']) {
            \DBG::log('Unknown website requested!.');
            return array('status' => 'error');
        }
        
        try {
            switch (\Cx\Core\Setting\Controller\Setting::getValue('mode','MultiSite')) {
                case ComponentController::MODE_MANAGER:
                case ComponentController::MODE_HYBRID:
                    $websiteServiceRepo = \Env::get('em')->getRepository('Cx\Core_Modules\MultiSite\Model\Entity\Website');
                    $website = $websiteServiceRepo->findOneById($params['post']['websiteId']);
                    if (!$website) {
                        return array('status' => 'error');
                    }
                    
                    $websiteName = $website->getName();
                    $metaInfo    = array();

                    $metaInfo['website'] = array(
                        'websiteName'  => $websiteName,
                        'websiteEmail' => $website->getOwner()->getEmail()
                    );
                    
                    $subscription = ComponentController::getSubscriptionByWebsiteId($website->getId());
                    if ($subscription) {
                        $metaInfo['subscription'] = array(
                            'subscriptionCreatedDate'       => $subscription->getSubscriptionDate() ? $subscription->getSubscriptionDate()->format('Y-m-d H:i:s') : '',
                            'subscriptionExpiredDate'       => $subscription->getExpirationDate() ? $subscription->getExpirationDate()->format('Y-m-d H:i:s') : '',
                            'subscriptionRenewalDate'       => $subscription->getRenewalDate() ? $subscription->getRenewalDate()->format('Y-m-d H:i:s') : '',
                            'subscriptionRenewalUnit'       => $subscription->getRenewalUnit(),
                            'subscriptionRenewalQuantifier' => $subscription->getRenewalQuantifier(),
                            'subscriptionProductId'         => $subscription->getProduct()->getId(),
                            'subscriptionId'                => $subscription->getId()
                        );
                    }
                    return array('status' => 'success', 'websiteInfo' => $metaInfo);
                    break;
                default :
                    break;
            }
        } catch (\Exception $e) {
            throw new MultiSiteJsonException('JsonMultiSite::getWebsiteInfo() failed: To get the website info'. $e->getMessage());
        }
        
    }
    
    /**
     * Website info backup
     * 
     * @param integer $websiteId         websiteId
     * @param string  $websiteBackupPath websiteBackup Location
     * 
     * @return boolean
     */
    public function websiteInfoBackup($websiteId, $websiteBackupPath)
    {
        if (!$websiteId) {
            \DBG::log('Unknown website requested!.');
            return false;
        }
        
        try {
            $resp = self::executeCommandOnManager('getWebsiteInfo', array('websiteId' => $websiteId));
            if (!$resp || $resp->status == 'error' || $resp->data->status == 'error') {
               \DBG::log('Failed to create the backup of websiteInfo.');
                return false;
            }

            if (!\Cx\Lib\FileSystem\FileSystem::exists($websiteBackupPath) && !\Cx\Lib\FileSystem\FileSystem::make_folder($websiteBackupPath)) {
                \DBG::log('Failed to create the website backup location Folder');
                return false;
            }

            if (!\Cx\Lib\FileSystem\FileSystem::exists($websiteBackupPath.'/info') && !\Cx\Lib\FileSystem\FileSystem::make_folder($websiteBackupPath.'/info')) {
                \DBG::log('Failed to create the website backup info location Folder');
                return false;
            }
            
            
            $file = new \Cx\Lib\FileSystem\File($websiteBackupPath.'/info/meta.yml');
            $file->touch();
            
            $dataSet = new \Cx\Core_Modules\Listing\Model\Entity\DataSet($resp->data->websiteInfo);
            $file->write($dataSet->toYaml());
            return true;
                
        } catch (\Exception $e) {
            \DBG::msg($e->getMessage());
            return false;
        }
    }
    
    /**
     * Website Database Backup 
     * 
     * @param string $websiteBackupPath websiteBackup Location
     * @param string $websitePath       websitePath
     * @return boolean
     */
    public function websiteDatabaseBackup($websiteBackupPath, $websitePath) {
        $configFilePath = $websitePath . '/config/configuration.php';

        if (!\Cx\Lib\FileSystem\FileSystem::exists($configFilePath)) {
            \DBG::log('Website configuration file is not exists in the website.');
            return false;
        }
        
        list($dbHost, $dbName, $dbUserName, $dbPassword) = $this->getWebsiteDatabaseInfo($configFilePath);
        if (!empty($dbHost) && !empty($dbUserName) && !empty($dbName)) {
            if (!\Cx\Lib\FileSystem\FileSystem::exists($websiteBackupPath. '/database') && !\Cx\Lib\FileSystem\FileSystem::make_folder($websiteBackupPath . '/database')) {
                \DBG::log('Failed to create the website databse backup location Folder');
                return false;
            }
            exec("mysqldump -h '". $dbHost . "' -u '" . $dbUserName . "' -p'" . $dbPassword . "' '".$dbName."' > '".$websiteBackupPath."/database/sql_dump.sql'");
        }
        return true;
    }
    
    /**
     * Get website database details
     * 
     * @param string $configFilePath website config file path
     * @return boolean
     */
    public function getWebsiteDatabaseInfo($configFilePath)
    {
        
        $config = new \Cx\Lib\FileSystem\File($configFilePath);
        $configData = $config->getData();
        $dbHost = $dbUserName = $dbPassword = $dbName = $matches = '';

        if (preg_match('/\\$_DBCONFIG\\[\'host\'\\] = \'(.*?)\'/', $configData, $matches)) {
            $dbHost = $matches[1];
        }
        if (preg_match('/\\$_DBCONFIG\\[\'user\'\\] = \'(.*?)\'/', $configData, $matches)) {
            $dbUserName = $matches[1];
        }
        if (preg_match('/\\$_DBCONFIG\\[\'password\'\\] = \'(.*?)\'/', $configData, $matches)) {
            $dbPassword = $matches[1];
        }
        if (preg_match('/\\$_DBCONFIG\\[\'database\'\\] = \'(.*?)\'/', $configData, $matches)) {
            $dbName = $matches[1];
        }
        
        return array($dbHost, $dbName, $dbUserName, $dbPassword);
    }
    
    /**
     * websiteRestore
     * 
     * @param array $params website data
     * @return array
     */
    public function websiteRestore($params)
    {
        $websiteName = isset($params['post']['websiteName']) ? contrexx_input2raw($params['post']['websiteName']) : '';
        $websiteBackupFilePath = !empty($params['post']['websiteBackupFilePath']) ? $params['post']['websiteBackupFilePath'] : '';
        if (   !\Cx\Lib\FileSystem\FileSystem::exists($websiteBackupFilePath) 
            || \Cx\Lib\FileSystem\FileSystem::exists(\Cx\Core\Setting\Controller\Setting::getValue('websitePath','MultiSite') . '/' . $websiteName)
           ) {
            \DBG::log('The website datafolder/backup repository doesnot exists!.');
            return array('status' => 'error', 'message' => 'Failed to Restore the website.');
        }
        
        if (!$this->createNewWebsiteOnRestore($websiteName, $websiteBackupFilePath)) {
            return array('status' => 'error', 'message' => 'Failed to Restore the website.');
        }
        
        if (!$this->websiteDataRestore($websiteName, $websiteBackupFilePath)) {
            return array('status' => 'error', 'message' => 'Failed to Restore the website.');
        }
        
        if (!$this->websiteInfoRestore($websiteName, $websiteBackupFilePath)) {
            return array('status' => 'error', 'message' => 'Failed to Restore website.');
        }
        
        return array('status' => 'success', 'message' => 'Successfully Restored the website!');
    }
    
    /**
     * getWebsiteInfoFromZip
     * 
     * @param string $websiteBackupFilePath websiteBackup path
     * @param string $file                  yml file path
     * @return array
     */
    public function getWebsiteInfoFromZip($websiteBackupFilePath, $file)
    {
        $websiteBackupFile = new \PclZip($websiteBackupFilePath);
        $list = $websiteBackupFile->extract(PCLZIP_OPT_BY_NAME, $file, PCLZIP_OPT_EXTRACT_AS_STRING);
        $yaml         = new \Symfony\Component\Yaml\Yaml();
        $websiteInfo  = $yaml->load($list[0]['content']);
        return $websiteInfo;
    }
    
    /**
     * createNewWebsite by using Restore Option
     * 
     * @param string $websiteName           websiteName
     * @param string $websiteBackupFilePath websiteBackup path
     * @return boolean
     */
    public function createNewWebsiteOnRestore($websiteName, $websiteBackupFilePath)
    {
        \DBG::log('Create new website on restore: '.$websiteName);
        $websiteInfoArray = $this->getWebsiteInfoFromZip($websiteBackupFilePath, 'info/meta.yml');
        if (empty($websiteInfoArray)) {
            return false;
        }
        
        $params = array('multisite_address' => $websiteName);
        if ($websiteInfoArray['website']) {
            $params['multisite_email_address'] = $websiteInfoArray['website']['websiteEmail'];
        }
        
        if ($websiteInfoArray['subscription']) {
            $params['product_id']    = $websiteInfoArray['subscription']['subscriptionProductId'];
            $params['renewalOption'] = $websiteInfoArray['subscription']['subscriptionRenewalUnit'];
            $subscriptionId          = $websiteInfoArray['subscription']['subscriptionId'];
        }
        
        if (empty($subscriptionId) || empty($params['product_id'])) {
            return false;
        }
        
        $product = \Env::get('em')->getRepository('Cx\Modules\Pim\Model\Entity\Product')->findOneBy(array('id' => $params['product_id']));
        
        if (!$product) {
            return false;
        }
        
        if ($product->getEntityClass() == 'Cx\Core_Modules\MultiSite\Model\Entity\WebsiteCollection') {
            $response = JsonMultiSite::executeCommandOnManager('createNewWebsiteBySubscription', array('subscriptionId' => $subscriptionId, 'websiteName' => $websiteName, 'websiteEmail' => $params['multisite_email_address']));
        } else {
            $response = JsonMultiSite::executeCommandOnManager('signup', $params);
        }
        
        if ($response->status == 'error' || $response->data->status == 'error') {
            return false;
        }
        
        return true;
    }
    
    /**
     * Restore website database and data repository
     * 
     * @param string $websiteName           website Name
     * @param string $websiteBackupFilePath website backup path
     * @return boolean
     */
    public function websiteDataRestore($websiteName, $websiteBackupFilePath)
    {
        \DBG::log('Restore website DataBase and Repository: '.$websiteName);
        $websitePath = \Cx\Core\Setting\Controller\Setting::getValue('websitePath','MultiSite') . '/' . $websiteName;
        
        if (!$this->extractWebsiteDatabase($websitePath, $websiteBackupFilePath)) {
            return false;
        }
        
        if (!$this->websiteDatabaseRestore($websitePath)) {
            return false;
        }
        
        if (!$this->websiteRepositoryRestore($websitePath, $websiteBackupFilePath)) {
            return false;
        }
        
        return true;
    }
    
    /**
     * Restore the website repository
     * 
     * @param string $websitePath           website path
     * @param string $websiteBackupFilePath website backup path
     * @return boolean
     */
    public function websiteRepositoryRestore($websitePath, $websiteBackupFilePath)
    {
        if (!\Cx\Lib\FileSystem\FileSystem::exists($websiteBackupFilePath)) {
            return false;
        }
        
        $restoreWebsiteFile = new \PclZip($websiteBackupFilePath);
        $restoreWebsiteFile->extract(PCLZIP_OPT_PATH, $websitePath, PCLZIP_OPT_BY_PREG, '/dataRepository(.(?!config))*$/', PCLZIP_OPT_REMOVE_PATH, 'dataRepository', PCLZIP_OPT_REPLACE_NEWER);
        return true;
    }
    
    
    /**
     * Extract the database folder in website_temp folder
     * 
     * @param string $websitePath           website path
     * @param string $websiteBackupFilePath website backup path
     * @return boolean
     */
    public function extractWebsiteDatabase($websitePath, $websiteBackupFilePath)
    {
        $websiteTempDir = $websitePath . '/website_temp';
        if (!\Cx\Lib\FileSystem\FileSystem::exists($websiteTempDir) && !\Cx\Lib\FileSystem\FileSystem::make_folder($websiteTempDir)) {
            return false;
        }

        $restoreWebsiteFile = new \PclZip($websiteBackupFilePath);
        $restoreWebsiteFile->extract(PCLZIP_OPT_PATH, $websiteTempDir, PCLZIP_OPT_BY_PREG, '/database/');
        return true;
    }
    
    /**
     * Restore the website database
     * 
     * @param string $websitePath           website path
     * @return boolean
     */
    public function websiteDatabaseRestore($websitePath)
    {
        $configFilePath = $websitePath . '/config/configuration.php';
        $websiteTempPath = $websitePath.'/website_temp';
        if (!\Cx\Lib\FileSystem\FileSystem::exists($configFilePath)) {
            \DBG::log('Website configuration file is not exists in the website.');
            return false;
        }

        list($dbHost, $dbName, $dbUserName, $dbPassword) = $this->getWebsiteDatabaseInfo($configFilePath);
        if (empty($dbHost) || empty($dbName) || empty($dbUserName) || empty($dbPassword)) {
            \DBG::log('Database details are not valid');
            return false;
        }

        $sqlDumpFile = $websiteTempPath . '/database/sql_dump.sql';
        if (!\Cx\Lib\FileSystem\FileSystem::exists($sqlDumpFile)) {
            return false;
        }

        exec("mysql -h '". $dbHost . "' -u '" . $dbUserName . "' -p'" . $dbPassword . "' '".$dbName."' < '".$sqlDumpFile."'");

        //cleanup website tempory folder
        if (!\Cx\Lib\FileSystem\FileSystem::delete_folder($websiteTempPath, true)) {
            return false;
        }

        return true;
    }
    
    /**
     * Restore the website Info 
     * 
     * @param string $websiteName           website name
     * @param string $websiteBackupFilePath website backup path
     * @return boolean
     */
    public function websiteInfoRestore($websiteName, $websiteBackupFilePath)
    {
        \DBG::log('Restore website Info: '.$websiteName);
        $websiteServiceRepo = \Env::get('em')->getRepository('Cx\Core_Modules\MultiSite\Model\Entity\Website');
        $website = $websiteServiceRepo->findOneBy(array('name' => $websiteName));
        if (!$website) {
            \DBG::log('This website is not valid');
            return false;
        }
        
        $websiteConfigArray = $this->getWebsiteInfoFromZip($websiteBackupFilePath, 'dataRepository/config/Config.yml');
        $websiteInfoArray   = $this->getWebsiteInfoFromZip($websiteBackupFilePath, 'dataRepository/config/MultiSite.yml');
        $configSettingArray = array();
        foreach ($websiteConfigArray['data'] as $value) {
            $configSettingArray[$value->getName()] = array(
                'name'  => $value->getName(),
                'group' => $value->getGroup(),
                'value' => $value->getValue()
            );
        }
        
        $websiteConfigInfo = array(
            'websiteMultisite'   => array('websiteState' => $websiteInfoArray['websiteState']['value']),
            'websiteConfig'      => $configSettingArray
            );
        
        
        if (!$this->updateWebsiteNetDomainsOnRestore($website, $websiteBackupFilePath)) {
            return false;
        }
        
        $resp = JsonMultiSite::executeCommandOnWebsite('updateWebsiteConfig', $websiteConfigInfo, $website);
        if ($resp->status == 'error' || $resp->data->status == 'error') {
            return false;
        }
        
        if (!$this->updateWebsiteSubscriptionDetails($websiteBackupFilePath, $website->getId())) {
            return false;
        }
        
        return true;
    }
    
    /**
     * Update website Config details
     * 
     * @param type $params
     * @return array
     */
    public function updateWebsiteConfig($params)
    {
        if (empty($params) || empty($params['post'])) {
            return array('status' => 'error');
        }
        
        if (!$this->updateWebsiteMultisiteConfigOnRestore($params['post']['websiteMultisite'])) {
            return array('status' => 'error');
        }
        
        if (!$this->updateWebsiteConfigOnRestore($params['post']['websiteConfig'])) {
            return array('status' => 'error');
        }
        return array('status' => 'success');
        
    }
    
    /**
     * updateWebsiteNetDomainsOnRestore
     * 
     * @param array  $website               websiteObject
     * @param string $websiteBackupFilePath website backup path
     * 
     * @return boolean
     */
    public function updateWebsiteNetDomainsOnRestore(\Cx\Core_Modules\MultiSite\Model\Entity\Website $website, $websiteBackupFilePath)
    {
        
        $websiteDomainObjArray = $this->getWebsiteInfoFromZip($websiteBackupFilePath, 'dataRepository/config/DomainRepository.yml');
        if (empty($websiteDomainObjArray)) {
            return true;
        }
        
        foreach ($websiteDomainObjArray['data'] as $domain) {
            $resp = JsonMultiSite::executeCommandOnWebsite('mapNetDomain', array('domainName' => $domain->getName()), $website);
            if (!$resp || $resp->status == 'error' || $resp->data->status == 'error') {
                return false;
            }
        }
        return true;
    }
    
    /**
     * Update website configuration settings
     * 
     * @param array $websiteInfo websiteConfig details
     * @return boolean
     */
    public function updateWebsiteConfigOnRestore($websiteInfo)
    {
        if (empty($websiteInfo)) {
            return false;
        }
        $updateConfigArray = array('systemStatus', 'languageDetection', 'coreGlobalPageTitle', 'mainDomainId', 'forceDomainUrl', 'coreListProtectedPages',
            'searchVisibleContentOnly', 'advancedUploadFrontend', 'forceProtocolFrontend', 'coreAdminEmail', 'contactFormEmail', 'contactCompany',
            'contactAddress', 'contactZip', 'contactPlace', 'contactCountry', 'contactPhone', 'contactFax', 'dashboardNews', 'dashboardStatistics',
            'advancedUploadBackend', 'sessionLifeTime', 'sessionLifeTimeRememberMe', 'forceProtocolBackend', 'coreIdsStatus', 'passwordComplexity',
            'coreAdminName', 'xmlSitemapStatus', 'frontendEditingStatus', 'corePagingLimit','searchDescriptionLength', 'googleMapsAPIKey',
            'googleAnalyticsTrackingId'
            );
        
        \Cx\Core\Setting\Controller\Setting::init('Config', null,'Yaml');
        foreach ($updateConfigArray as $config) {
            \Cx\Core\Setting\Controller\Setting::set($config, $websiteInfo[$config]['value']);
        }
        
        \Cx\Core\Setting\Controller\Setting::updateAll();
        
        return true;
    }
    
    /**
     * Update website MultiSite settings
     * 
     * @param array $websiteInfo websiteInfo
     * 
     * @return boolean
     */
    public function updateWebsiteMultisiteConfigOnRestore($websiteInfo)
    {
        if (empty($websiteInfo)) {
            return false;
        }
        
        \Cx\Core\Setting\Controller\Setting::init('MultiSite', 'website','FileSystem');
        \Cx\Core\Setting\Controller\Setting::set('websiteState', $websiteInfo['websiteState']);
        
        \Cx\Core\Setting\Controller\Setting::update('websiteState');
        return true;
    }
    
    /**
     * Update website subscription details
     * 
     * @param string  $websiteBackupFilePath website backup path
     * @param integer $websiteId             websiteId
     * 
     * @return boolean
     */
    public function updateWebsiteSubscriptionDetails($websiteBackupFilePath, $websiteId)
    {
        $websiteInfoArray = $this->getWebsiteInfoFromZip($websiteBackupFilePath, 'info/meta.yml');
        if (empty($websiteInfoArray) || !isset($websiteInfoArray['subscription'])) {
            \DBG::log('This website info is empty');
            return false;
        }
        
        $params = array('websiteId' => $websiteId, 'subscription' => $websiteInfoArray['subscription']);
        
        //update subscription details
        $response = JsonMultiSite::executeCommandOnManager('updateWebsiteSubscriptionInfo', $params);
        if ($response->status == 'error' || $response->data->status == 'error') {
            \DBG::log('Failed to update subscription details');
            return false;
        }
        
        return true;
    }
    
    /**
     * update subscription details of website
     * 
     * @param type $params
     * @return array
     */
    public function updateWebsiteSubscriptionInfo($params)
    {
        if (empty($params) || empty($params['post']['websiteId']) || empty($params['post']['subscription'])) {
            return array('status' => 'error');
        }
        try {
            $websiteId         = isset($params['post']['websiteId']) ? $params['post']['websiteId'] : '';
            $subscriptionInfo  = isset($params['post']['subscription']) ? $params['post']['subscription'] : '';
                 
            $subscription = ComponentController::getSubscriptionByWebsiteId($websiteId);
            if (!$subscription) {
                return array('status' => 'error');
            }
            
            $subscriptionCreatedDate = isset($subscriptionInfo['subscriptionCreatedDate']) ? new \DateTime($subscriptionInfo['subscriptionCreatedDate']) : null;
            $subscriptionExpiredDate = isset($subscriptionInfo['subscriptionExpiredDate']) ? new \DateTime($subscriptionInfo['subscriptionExpiredDate']) : null;
            $subscriptionRenewalDate = isset($subscriptionInfo['subscriptionRenewalDate']) ? new \DateTime($subscriptionInfo['subscriptionRenewalDate']) : null;
            $subscription->setSubscriptionDate($subscriptionCreatedDate);
            $subscription->setExpirationDate($subscriptionExpiredDate);
            $subscription->setRenewalDate($subscriptionRenewalDate);
            
            \Env::get('em')->flush();
            
            return array('status' => 'success');
        } catch (\Exception $e) {
            \DBG::log('JsonMultisite::updateWebsiteInfo() failed!. '.$e->getMessage());
            return array('status' => 'error');
        }
    }
    
    /**
     * createNewWebsiteBySubscription
     * 
     * @param array $params
     * @return array
     */
    public function createNewWebsiteBySubscription($params)
    {
        
        $subscriptionId = isset($params['post']['subscriptionId']) ? (int)$params['post']['subscriptionId'] : '';
        $websiteName    = isset($params['post']['websiteName']) ? contrexx_input2raw($params['post']['websiteName']) : '';
        $email          = isset($params['post']['websiteEmail']) ? contrexx_input2raw($params['post']['websiteEmail']) : '';
        
        if (   empty($subscriptionId) 
            || empty($websiteName) 
            || empty($email) 
            ) {
            return array('status' => 'error');
        }
        
        $objUser = \FWUser::getFWUserObject()->objUser->getUsers(array('email' => $email));
        if (!$objUser) {
            return array('status' => 'error');
        }
        
        $response = \Cx\Core_Modules\MultiSite\Controller\ComponentController::createNewWebsiteInSubscription($subscriptionId, $websiteName, $objUser);
        if (!$response || $response->status == 'error' || $response->data->status == 'error') {
            return array('status' => 'error');
        }
        return array('status' => 'success');
    }
    
    protected function activateDebuggingToMemory() {
        // check if memory logging shall be activated
        switch (\Cx\Core\Setting\Controller\Setting::getValue('mode','MultiSite')) {
            case ComponentController::MODE_MANAGER:
            case ComponentController::MODE_SERVICE:
            case ComponentController::MODE_HYBRID:
                // don't activate memory-logging if debug log shall not be sent to admin
                if (!\Cx\Core\Setting\Controller\Setting::getValue('sendSetupError','MultiSite')) {
                    return;
                }
                break;

            case ComponentController::MODE_WEBSITE:
                // always activate memory-logging for website mode
            default:
                break;
        }

        // activate memory-logging
        if (\DBG::getMode() & DBG_LOG_FILE || \DBG::getMode() & DBG_LOG_FIREPHP) {
            \DBG::deactivate(DBG_LOG_FILE | DBG_LOG_FIREPHP);
        }
        \DBG::deactivate(DBG_LOG);
        if (\DBG::getMode() ^ DBG_PHP || \DBG::getMode() ^ DBG_LOG_MEMORY) {
            \DBG::activate(DBG_PHP | DBG_LOG_MEMORY | DBG_LOG);
        }
    }
    
    /**
     * Create the mail service account
     * 
     * @param array $params
     * 
     * @return boolean
     * 
     * @throws MultiSiteJsonException
     */    
    public function createMailServiceAccount($params)
    {
        global $_ARRAYLANG;
        self::loadLanguageData();

        if (empty($params['post']['websiteId'])) {
            \DBG::log('JsonMultiSite::createMailServiceAccount() failed: Insufficient arguments supplied: ' . var_export($params, true));
            throw new MultiSiteJsonException($_ARRAYLANG['TXT_MULTISITE_WEBSITE_NOT_EXISTS']);
        }

        try {
            // check the mode
            switch (\Cx\Core\Setting\Controller\Setting::getValue('mode','MultiSite')) {
                case ComponentController::MODE_MANAGER:
                case ComponentController::MODE_HYBRID:
                    $website = \Env::get('em')->getRepository('Cx\Core_Modules\MultiSite\Model\Entity\Website')
                                              ->findOneBy(array('id' => $params['post']['websiteId']));
                    if (!$website) {
                        \DBG::log('JsonException::createMailServiceAccount() failed: Unkown Website-ID: '.$params['post']['websiteId']);
                        throw new MultiSiteJsonException($_ARRAYLANG['TXT_MULTISITE_WEBSITE_NOT_EXISTS']);
                    }
                    $defaultMailServiceServer = \Env::get('em')->getRepository('Cx\Core_Modules\MultiSite\Model\Entity\MailServiceServer')
                                                               ->findOneBy(array('id' => \Cx\Core\Setting\Controller\Setting::getValue('defaultMailServiceServer','MultiSite')));
                    $res = $defaultMailServiceServer->createAccount($website);
                    $accountId = $res['subscriptionId'];
                    if ($accountId) {
                        $website->setMailAccountId($accountId);
                        \Env::get('em')->flush();
                        return array('status' => 'success', 'message' => $_ARRAYLANG['TXT_MULTISITE_WEBSITE_CREATED_MAIL_ACCOUNT_SUCCESSFULLY'], 'pwd' => $res['pwd']);
                    } 
                    break;
                case ComponentController::MODE_WEBSITE:
                case ComponentController::MODE_SERVICE:
                    // forward call to manager server. 
                    $response = self::executeCommandOnManager('createMailServiceAccount', array('websiteId' => $params['post']['websiteId']));
                    if ($response && $response->status == 'success' && $response->data->status == 'success') {
                        return array('status' => 'success', 'message' => $_ARRAYLANG['TXT_MULTISITE_WEBSITE_CREATED_MAIL_ACCOUNT_SUCCESSFULLY'], 'pwd' => $response->data->pwd);
                    }
                    break;
                default:
                    break;
            }
            return array('status' => 'error', 'message' => $_ARRAYLANG['TXT_MULTISITE_WEBSITE_CREATE_MAIL_ACCOUNT_FAILED']);
        } catch (\Exception $ex) {
            \DBG::log('JsonMultiSite::createMailServiceAccount() failed: To create mail service server'. $ex->getMessage());
            return array('status' => 'error', 'message' => $_ARRAYLANG['TXT_MULTISITE_WEBSITE_CREATE_MAIL_ACCOUNT_FAILED']);
        }
    }
    
    /**
     * Delete the mail service account
     * 
     * @param array $params
     * 
     * @return boolean
     * 
     * @throws MultiSiteJsonException
     */
    public function deleteMailServiceAccount($params)
    {
        global $_ARRAYLANG;
        self::loadLanguageData();
        
        if (empty($params['post']['websiteId'])) {
            \DBG::log('JsonMultiSite::deleteMailServiceAccount() failed: Insufficient arguments supplied: ' . var_export($params, true));
            throw new MultiSiteJsonException($_ARRAYLANG['TXT_MULTISITE_WEBSITE_NOT_EXISTS']);
        }
        try {
            // check the mode
            switch (\Cx\Core\Setting\Controller\Setting::getValue('mode','MultiSite')) {
                case ComponentController::MODE_MANAGER:
                case ComponentController::MODE_HYBRID:
                    $website = \Env::get('em')->getRepository('Cx\Core_Modules\MultiSite\Model\Entity\Website')->findOneBy(array('id' => $params['post']['websiteId']));
                    if (!$website) {
                        \DBG::log('JsonException::deleteMailServiceAccount() failed: Unkown Website-ID: '.$params['post']['websiteId']);
                        throw new MultiSiteJsonException($_ARRAYLANG['TXT_MULTISITE_WEBSITE_NOT_EXISTS']);
                    }
                    
                    if (
                           self::validateWebsiteForMailService($website) 
                        && $website->getMailServiceServer()->deleteAccount($website->getMailAccountId())
                    ) {
                        //unmap domains which are related to mail service
                        foreach ($website->getDomains() as $domain) {
                            if (   $domain->getType() == \Cx\Core_Modules\MultiSite\Model\Entity\Domain::TYPE_MAIL_DOMAIN
                                || $domain->getType() == \Cx\Core_Modules\MultiSite\Model\Entity\Domain::TYPE_WEBMAIL_DOMAIN
                               ) {
                                $website->removeDomain($domain);
                                \Env::get('em')->remove($domain);
                            }
                        }
                        $website->getMailServiceServer()->removeWebsite($website);
                        $website->setMailAccountId(null);
                        $website->setMailServiceServer(null);
                        \Env::get('em')->flush();
                        return array('status' => 'success', 'message' => $_ARRAYLANG['TXT_MULTISITE_WEBSITE_DELETED_MAIL_ACCOUNT_SUCCESSFULLY']);
                    }                    
                case ComponentController::MODE_WEBSITE:
                case ComponentController::MODE_SERVICE:
                    // forward call to manager server. 
                    $response = self::executeCommandOnManager('deleteMailServiceAccount', array('websiteId' => $params['post']['websiteId']));
                    if ($response && $response->status == 'success' && $response->data->status == 'success') {
                        return array('status' => 'success', 'message' => $_ARRAYLANG['TXT_MULTISITE_WEBSITE_DELETED_MAIL_ACCOUNT_SUCCESSFULLY']);
                    }
                    break;
                default:
                    break;
            }
            return array(
                'status' => 'error', 
                'message' => $_ARRAYLANG['TXT_MULTISITE_WEBSITE_DELETE_MAIL_ACCOUNT_FAILED'],
                'log'    => \DBG::getMemoryLogs(),
            );
        } catch (\Exception $e) {
            throw new MultiSiteJsonException(
                array(
                    'log'       => \DBG::getMemoryLogs(),
                    'message'   => $_ARRAYLANG['TXT_MULTISITE_WEBSITE_DELETE_MAIL_ACCOUNT_FAILED']. $e->getMessage()
                )
            );
        }
    }
    
    /**
     * Enable the mail service
     * 
     * @param array $params
     * 
     * @return boolean
     * @throws MultiSiteJsonException
     */    
    public function enableMailService($params)
    {
        global $_ARRAYLANG;
        self::loadLanguageData();
        
        if (empty($params['post']['websiteId'])) {
            \DBG::log('JsonMultiSite::enableMailService() failed: Insufficient arguments supplied: ' . var_export($params, true));
            throw new MultiSiteJsonException($_ARRAYLANG['TXT_MULTISITE_WEBSITE_NOT_EXISTS']);
        }
               
        try {
            // check the mode
            switch (\Cx\Core\Setting\Controller\Setting::getValue('mode','MultiSite')) {
                case ComponentController::MODE_MANAGER:
                case ComponentController::MODE_HYBRID:
                    $website = \Env::get('em')->getRepository('Cx\Core_Modules\MultiSite\Model\Entity\Website')->findOneBy(array('id' => $params['post']['websiteId']));
                    if (!$website) {
                        \DBG::log('JsonException::enableMailService() failed: Unkown Website-ID: '.$params['post']['websiteId']);
                        throw new MultiSiteJsonException($_ARRAYLANG['TXT_MULTISITE_WEBSITE_NOT_EXISTS']);
                    }
                    $mailServiceServer = $website->getMailServiceServer();
                    if (!$mailServiceServer) {
                        $response = self::executeCommandOnManager('createMailServiceAccount', array('websiteId' => $params['post']['websiteId']));
                        if (!$response || $response->status == 'error' || $response->data->status == 'error') {
                            \DBG::log('JsonException::enableMailService() failed: Unable to create mail service account.');
                            throw new MultiSiteJsonException($_ARRAYLANG['TXT_MULTISITE_WEBSITE_MAIL_ENABLED_FAILED']);
                        }
                        $pwd = $response->data->pwd;
                        $mailServiceServer = $website->getMailServiceServer();
                    }
                    if (
                           $mailServiceServer && $website->getMailAccountId()
                        && $mailServiceServer->enableService($website)
                    ) {
                        return array('status' => 'success', 'message' => $_ARRAYLANG['TXT_MULTISITE_WEBSITE_MAIL_ENABLED_SUCCESSFULLY'], 'pwd' => isset($pwd)?$pwd:'');
                    }
                    break;

                case ComponentController::MODE_WEBSITE:
                case ComponentController::MODE_SERVICE:
                    // forward call to manager server. 
                    $response = self::executeCommandOnManager('enableMailService', array('websiteId' => $params['post']['websiteId']));
                    if ($response && $response->status == 'success' && $response->data->status == 'success') {
                        return array('status' => 'success', 'message' => $_ARRAYLANG['TXT_MULTISITE_WEBSITE_MAIL_ENABLED_SUCCESSFULLY']);
                    }
                    break;
                default:
                    break;
            }
            return array('status' => 'error', 'message' => $_ARRAYLANG['TXT_MULTISITE_WEBSITE_MAIL_ENABLED_FAILED']);
        } catch (\Exception $ex) {
            \DBG::log('JsonMultiSite::enableMailService() failed: To enable mail service account.'. $ex->getMessage());
            throw new MultiSiteJsonException($_ARRAYLANG['TXT_MULTISITE_WEBSITE_MAIL_ENABLED_FAILED']);
        }
    }
    
    /**
     * Disable the mail service
     * 
     * @param array $params
     * 
     * @return boolean
     * @throws MultiSiteJsonException
     */    
    public function disableMailService($params)
    {
        global $_ARRAYLANG;
        self::loadLanguageData();
        
        if (empty($params['post']['websiteId'])) {
            \DBG::log('JsonMultiSite::disableMailService() failed: Insufficient arguments supplied: ' . var_export($params, true));
            throw new MultiSiteJsonException($_ARRAYLANG['TXT_MULTISITE_WEBSITE_NOT_EXISTS']);
        }
               
        try {
            // check the mode
            switch (\Cx\Core\Setting\Controller\Setting::getValue('mode','MultiSite')) {
                case ComponentController::MODE_MANAGER:
                case ComponentController::MODE_HYBRID:
                    $website = \Env::get('em')->getRepository('Cx\Core_Modules\MultiSite\Model\Entity\Website')->findOneBy(array('id' => $params['post']['websiteId']));
                    if (!$website) {
                        \DBG::log('JsonException::disableMailService() failed: Unkown Website-ID: '.$params['post']['websiteId']);
                        return array('status' => 'error', 'message' => $_ARRAYLANG['TXT_MULTISITE_WEBSITE_NOT_EXISTS']);
                    }
                    
                    if (
                           self::validateWebsiteForMailService($website) 
                        && $website->getMailServiceServer()->disableService($website)
                    ) {
                        return array('status' => 'success', 'message' => $_ARRAYLANG['TXT_MULTISITE_WEBSITE_MAIL_DISABLED_SUCCESSFULLY']);
                    }
                    break;

                case ComponentController::MODE_WEBSITE:
                case ComponentController::MODE_SERVICE:
                    // forward call to manager server. 
                    $response = self::executeCommandOnManager('disableMailService', array('websiteId' => $params['post']['websiteId']));
                    if ($response && $response->status == 'success' && $response->data->status == 'success') {
                        return array('status' => 'success', 'message' => $_ARRAYLANG['TXT_MULTISITE_WEBSITE_MAIL_DISABLED_SUCCESSFULLY']);
                    }
                    break;
                default:
                    break;
            }
            return array('status' => 'error', 'message' => $_ARRAYLANG['TXT_MULTISITE_WEBSITE_MAIL_DISABLED_FAILED']);
        } catch (\Exception $ex) {
            \DBG::log('JsonMultiSite::disableMailService() failed: To disable mail service'. $ex->getMessage());
            throw new MultiSiteJsonException($_ARRAYLANG['TXT_MULTISITE_WEBSITE_MAIL_DISABLED_FAILED']);
        }
    }
    
    /**
     * Reset the E-Mail Password
     * 
     * @param array $params
     * 
     * @return boolean
     * @throws MultiSiteJsonException
     */    
    public function resetEmailPassword($params)
    {
        global $_ARRAYLANG;
        self::loadLanguageData();
        
        if (empty($params['post']['websiteId'])) {
            \DBG::log('JsonMultiSite::resetEmailPassword() failed: Insufficient arguments supplied: ' . var_export($params, true));
            throw new MultiSiteJsonException($_ARRAYLANG['TXT_MULTISITE_WEBSITE_NOT_EXISTS']);
        }
               
        try {
            // check the mode
            switch (\Cx\Core\Setting\Controller\Setting::getValue('mode','MultiSite')) {
                case ComponentController::MODE_MANAGER:
                case ComponentController::MODE_HYBRID:
                    $website = \Env::get('em')->getRepository('Cx\Core_Modules\MultiSite\Model\Entity\Website')->findOneBy(array('id' => $params['post']['websiteId']));
                    if (!$website) {
                        \DBG::log('JsonException::disableMailService() failed: Unkown Website-ID: '.$params['post']['websiteId']);
                        return array('status' => 'error', 'message' => $_ARRAYLANG['TXT_MULTISITE_WEBSITE_NOT_EXISTS']);
                    }
                    $mailServiceServer = $website->getMailServiceServer();
                    if (!$mailServiceServer || \FWValidator::isEmpty($website->getMailAccountId())) {
                        throw new MultiSiteJsonException('JsonMultiSite::getPanelAutoLoginUrl() failed: Unkown mail service server.');
                    }

                    $hostingController = ComponentController::getMailServerHostingController($mailServiceServer);
                    
                    $password = \User::make_password(8, true);
                    if ($hostingController->changeUserAccountPassword($website->getMailAccountId(), $password)) {
                        return array(
                            'status'    => 'success',
                            'message'   => $_ARRAYLANG['TXT_MULTISITE_RESET_EMAIL_PASS_MSG'],
                            'password'  => $password,
                            'log'       => \DBG::getMemoryLogs(),
                        );
                    }
                    break;

                case ComponentController::MODE_WEBSITE:
                case ComponentController::MODE_SERVICE:
                    // forward call to manager server. 
                    $response = self::executeCommandOnManager('resetEmailPassword', array('websiteId' => $params['post']['websiteId']));
                    if ($response && $response->status == 'success' && $response->data->status == 'success') {
                        return array('status' => 'success', 'message' => $_ARRAYLANG['TXT_MULTISITE_RESET_EMAIL_PASS_MSG']);
                    }
                    break;
                default:
                    break;
            }
            return array('status' => 'error', 'message' => $_ARRAYLANG['TXT_MULTISITE_RESET_EMAIL_PASS_ERROR_MSG']);
        } catch (Exception $ex) {
            \DBG::log('JsonMultiSite::resetEmailPassword() failed: Updating E-Mail password.'. $ex->getMessage());
            throw new MultiSiteJsonException($_ARRAYLANG['TXT_MULTISITE_RESET_EMAIL_PASS_ERROR_MSG']);
        }
    }
    
    /**
     * Get the mail service status
     * 
     * @param array $params
     * 
     * @return string mail service status
     * @throws MultiSiteJsonException
     */    
    public function getMailServiceStatus($params)
    {
        global $_ARRAYLANG;
        self::loadLanguageData();
        
        if (empty($params['post']['websiteId'])) {
            \DBG::log('JsonMultiSite::getMailServiceStatus() failed: Insufficient arguments supplied: ' . var_export($params, true));
            throw new MultiSiteJsonException($_ARRAYLANG['TXT_MULTISITE_WEBSITE_NOT_EXISTS']);
        }
               
        try {
            // check the mode
            switch (\Cx\Core\Setting\Controller\Setting::getValue('mode','MultiSite')) {
                case ComponentController::MODE_MANAGER:
                case ComponentController::MODE_HYBRID:
                    $website = \Env::get('em')->getRepository('Cx\Core_Modules\MultiSite\Model\Entity\Website')->findOneBy(array('id' => $params['post']['websiteId']));
                    if (!$website) {
                        \DBG::log('JsonException::getMailServiceStatus() failed: Unkown Website-ID: '.$params['post']['websiteId']);
                        throw new MultiSiteJsonException($_ARRAYLANG['TXT_MULTISITE_WEBSITE_NOT_EXISTS']);
                    }
                    $mailServiceServer = $website->getMailServiceServer();
                    if ($mailServiceServer && !\FWValidator::isEmpty($website->getMailAccountId())) {
                        $hostingController = ComponentController::getMailServerHostingController($mailServiceServer);

                        $status = $hostingController->getMailServiceStatus($website->getMailAccountId());
                        return array('status' => 'success', 'mailServiceStatus' => ($status == 'true') ? true : false);
                    }
                    break;

                case ComponentController::MODE_WEBSITE:
                case ComponentController::MODE_SERVICE:
                    // forward call to manager server. 
                    $response = self::executeCommandOnManager('getMailServiceStatus', array('websiteId' => $params['post']['websiteId']));
                    if ($response && $response->status == 'success' && $response->data->status == 'success') {
                        return array('status' => 'success', 'mailServiceStatus' => $response->data->mailServiceStatus);
                    }
                    break;
                default:
                    break;
            }
            return array('status' => 'error');
        } catch (\Exception $ex) {
            \DBG::log('JsonMultiSite::getMailServiceStatus() failed: To get mail service status.'. $ex->getMessage());
            throw new MultiSiteJsonException($_ARRAYLANG['TXT_MULTISITE_WEBSITE_NOT_EXISTS']);
        }
    }
    
    /**
     * Validate the website to handle it by mail service server
     * 
     * @param \Cx\Core_Modules\MultiSite\Model\Entity\Website $website
     * 
     * @return boolean                True when website is has enough values to handled by mail service
     * @throws MultiSiteJsonException when webiste does not have proper values
     */
    public static function validateWebsiteForMailService(\Cx\Core_Modules\MultiSite\Model\Entity\Website $website) {
        
        $mailServiceServer = $website->getMailServiceServer();
        if (!$mailServiceServer) {
            throw new MultiSiteJsonException('JsonException failed: mail service server is not set.');
        }

        if (!$website->getMailAccountId()) {
            throw new MultiSiteJsonException('JsonException failed: mail account id is not set.');
        }
        
        return true;
    }
    
    /**
     * Get website remote login url
     * 
     * @param array $params websiteId
     * 
     * @return mixed Get website login url or false on website owner id is different from logged user
     */
    public function websiteLogin($params) {
        if (empty($params['post']['websiteId'])) {
            throw new MultiSiteJsonException('JsonMultiSite::websiteLogin() failed: Insufficient arguments supplied: ' . var_export($params, true));
        }
        
        $website = \Env::get('em')->getRepository('Cx\Core_Modules\MultiSite\Model\Entity\Website')->findOneBy(array('id' => $params['post']['websiteId']));
        if (!$website) {
            throw new MultiSiteJsonException('JsonMultiSite::websiteLogin() failed: Unkown Website-ID: '.$params['post']['websiteId']);
        }
        
        $userId = \FWUser::getFWUserObject()->objUser->getId();
        if ($website->getOwner()->getId() == $userId) {
            return $this->remoteLogin(array('post' => array('websiteId' => $params['post']['websiteId'])));
        }
        
        return false;
    }
    
    /**
     * get the website admin users and backend group users
     * 
     * @return array users
     */
    public function getAdminUsers() 
    {
        try {
            $adminUsers         = ComponentController::getAllAdminUsers();            
            $objEntityInterface = new \Cx\Core_Modules\Listing\Model\Entity\EntityInterface();
            
            $objDataSet = \Cx\Core_Modules\Listing\Model\Entity\DataSet::import($objEntityInterface, $adminUsers);
            
            if (!\FWValidator::isEmpty($objDataSet)) {                
                return array('status' => 'success', 'users' => $objDataSet->toArray());
            }
            return array('status' => 'error');
        } catch (\Exception $e) {
            throw new MultiSiteJsonException('JsonMultiSite::getAdminUsers() failed: To get the Admin users' . $e->getMessage());
        }
    }
    
    /**
     * get user by id
     * 
     * @param  array $params supplied arguments from JsonData-request
     * 
     * @return array
     */
    public function getUser($params){

        $userId = isset($params['post']['id']) ? contrexx_input2raw($params['post']['id']) : '';
        if (\FWValidator::isEmpty($userId)) {
            return;
        }
        try {
            switch (\Cx\Core\Setting\Controller\Setting::getValue('mode','MultiSite')) {
                case ComponentController::MODE_WEBSITE:
                    $userRepo = \Env::get('em')->getRepository('Cx\Core\User\Model\Entity\User');
                    $user = $userRepo->findBy(array('id' => $userId));

                    $objEntityInterface = new \Cx\Core_Modules\Listing\Model\Entity\EntityInterface();

                    $objDataSet = \Cx\Core_Modules\Listing\Model\Entity\DataSet::import($objEntityInterface, $user);

                    if (!\FWValidator::isEmpty($objDataSet)) {
                        return array('status' => 'success', 'user' => $objDataSet->toArray());
                    }
                    return array('status' => 'error');
                    break;
            }
        } catch (\Exception $e) {
            throw new MultiSiteJsonException('JsonMultiSite::getUser() failed: To get the user' . $e->getMessage());
        }
    }

    /**
     * This function used to get the resource usage stats
     * 
     * @return array resource usage stats 
     * @throws MultiSiteJsonException
     */
    
    public function getResourceUsageStats() {
        global $_ARRAYLANG;
        self::loadLanguageData();
        
        try {
            switch (\Cx\Core\Setting\Controller\Setting::getValue('mode','MultiSite')) {
                case ComponentController::MODE_WEBSITE:
                    $resourcesUsage = array();
                    $modulesArray = array(
                        'Access' => 'AdminUser',
                        'Contact' => 'Form',
                        'Shop' => 'Product',
                        'Crm' => 'Customer'
                    );

                    foreach ($modulesArray as $module => $type) {
                        switch ($module) {
                            case 'Access':
                                $usage = count(ComponentController::getAllAdminUsers());
                                break;
                            
                            case 'Contact':
                                $forms = \Env::get('em')->getRepository('Cx\Core_Modules\Contact\Model\Entity\Form')->findAll();
                                $usage = count($forms);
                                break;

                            case 'Shop':
                                $count = 0;
                                $products = \Cx\Modules\Shop\Controller\Products::getByShopParams($count, 0, null, null, null, null, false, false, null, null, true);
                                $usage = count($products);
                                break;

                            case 'Crm':
                                $objCrm = new \Cx\Modules\Crm\Controller\CrmManager('crm');
                                $query = $objCrm->getContactsQuery(array('contactSearch' => 2));
                                $usage = $objCrm->countRecordEntries($query);
                                break;
                        }
                        
                        $quotaResult = ComponentController::getModuleAdditionalDataByType($module);
                        
                        $resourceUsageStats[lcfirst($module) . $type] = array(
                            'usage' => $usage ? $usage : 0,
                            'quota' => !empty($quotaResult[$type]) ? $quotaResult[$type] : $_ARRAYLANG['TXT_MULTISITE_UNLIMITED']
                        );
                    }

                    return array('status' => 'success', 'resourceUsageStats' => $resourceUsageStats);
                    break;
            }
            return array('status' => 'error');
        } catch (\Exception $e) {
            throw new MultiSiteJsonException('JsonMultiSite::getResourceUsageStats() failed: To get the website Resource Usage Stats' . $e->getMessage());
        }
    }

    /**
     * Create a new auto-login url for Plesk.
     * 
     * @param array $params
     * 
     * @return array login stats
     * @throws MultiSiteJsonException
     */
    public function getPanelAutoLoginUrl($params)
    {
        global $_ARRAYLANG;
        if (\FWValidator::isEmpty($params['post']['websiteId'])) {
            throw new MultiSiteJsonException('JsonMultiSite::getPanelAutoLoginUrl() failed: Insufficient arguments supplied: ' . var_export($params, true));
        }
        try {
            switch (\Cx\Core\Setting\Controller\Setting::getValue('mode','MultiSite')) {
                case ComponentController::MODE_MANAGER:
                    $website = \Env::get('em')->getRepository('Cx\Core_Modules\MultiSite\Model\Entity\Website')->findOneBy(array('id' => $params['post']['websiteId']));
                    if (!$website) {
                        throw new MultiSiteJsonException('JsonMultiSite::getPanelAutoLoginUrl() failed: Unkown Website-ID: ' . $params['post']['websiteId']);
                    }
                    if ($website->getOwner()->getId() != \FWUser::getFWUserObject()->objUser->getId()) {
                        return array('status' => 'error', 'message' => $_ARRAYLANG['TXT_MULTISITE_WEBSITE_NOT_MULTISITE_USER']);
                    }
                    $mailServiceServer = $website->getMailServiceServer();
                    if (!$mailServiceServer || \FWValidator::isEmpty($website->getMailAccountId())) {
                        throw new MultiSiteJsonException('JsonMultiSite::getPanelAutoLoginUrl() failed: Unkown mail service server.');
                    }

                    $clientIp = !\FWValidator::isEmpty($_SERVER['HTTP_X_FORWARDED_FOR']) ? trim(end(explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']))) : $_SERVER['REMOTE_ADDR'];
                    $hostingController = ComponentController::getMailServerHostingController($mailServiceServer);

                    $pleskLoginUrl = $hostingController->getPanelAutoLoginUrl($website->getMailAccountId(), $clientIp, ComponentController::getApiProtocol() . \Cx\Core\Setting\Controller\Setting::getValue('customerPanelDomain','MultiSite'));
                    
                    if ($pleskLoginUrl) {
                        return array('status' => 'success', 'autoLoginUrl' => $pleskLoginUrl);
                    }
                    return array('status' => 'error', 'message' => $_ARRAYLANG['TXT_MULTISITE_WEBSITE_LOGIN_PLESK_FAILED']);
                    break;

                default:
                    break;
            }
        } catch (\Exception $e) {
            throw new MultiSiteJsonException('JsonMultiSite::getPanelAutoLoginUrl() failed:' . $_ARRAYLANG['TXT_MULTISITE_WEBSITE_LOGIN_PLESK_FAILED'] . $e->getMessage());
        }
    }

    /**
     * Get auto-login url for Payrexx.
     * 
     * @return array Payrexx auto-login url stats
     * 
     */
    public function payrexxAutoLoginUrl()
    {
        global $_ARRAYLANG;
        
        if (!ComponentController::isUserLoggedIn()) {
            return array('status' => 'error', 'message' => $_ARRAYLANG['TXT_MULTISITE_WEBSITE_LOGIN_NOACCESS']);
        }

        $crmContactId = \FWUser::getFWUserObject()->objUser->getCrmUserId();
        if (\FWValidator::isEmpty($crmContactId)) {
            return array('status' => 'error', 'message' => $_ARRAYLANG['TXT_MULTISITE_WEBSITE_NOT_MULTISITE_USER']);
        }

        $profileAttributeId = \Cx\Core\Setting\Controller\Setting::getValue('externalPaymentCustomerIdProfileAttributeId','MultiSite');
        if (\FWValidator::isEmpty($profileAttributeId)) {
            return array('status' => 'error', 'message' => $_ARRAYLANG['TXT_MULTISITE_WEBSITE_PAYREXX_LOGIN_FAILED']);
        }

        $userId = \FWUser::getFWUserObject()->objUser->getProfileAttribute($profileAttributeId);
        if (\FWValidator::isEmpty($userId)) {
            return array('status' => 'error', 'message' => $_ARRAYLANG['TXT_MULTISITE_WEBSITE_PAYREXX_LOGIN_FAILED']);
        }

        $instanceName = \Cx\Core\Setting\Controller\Setting::getValue('payrexxAccount','MultiSite');
        $apiSecret = \Cx\Core\Setting\Controller\Setting::getValue('payrexxApiSecret','MultiSite');
        
        try {   
            $payrexx = new \Payrexx\Payrexx($instanceName, $apiSecret);
            $authToken = new \Payrexx\Models\Request\AuthToken();
            $authToken->setUserId($userId);
        
            $response = $payrexx->create($authToken);

            if (   !\FWValidator::isEmpty($response)
                && $response instanceof \Payrexx\Models\Response\AuthToken
                && !\FWValidator::isEmpty($response->getLink())
            ) {
                return array('status' => 'success', 'autoLoginUrl' => $response->getLink());
            }
            return array('status' => 'error', 'message' => $_ARRAYLANG['TXT_MULTISITE_WEBSITE_PAYREXX_LOGIN_FAILED']);
        } catch (\Payrexx\PayrexxException $e) {
            \DBG::log('JsonMultiSite::payrexxAutoLoginUrl() failed:' . $e->getMessage());
            throw new MultiSiteJsonException($_ARRAYLANG['TXT_MULTISITE_WEBSITE_PAYREXX_LOGIN_FAILED']);
        }
    }
    
    /**
     * Get the main domain of the Website
     * 
     * @return string main domain of the website
     * @throws MultiSiteJsonException
     */
    public function getMainDomain() {
        try {
            switch (\Cx\Core\Setting\Controller\Setting::getValue('mode','MultiSite')) {
                case ComponentController::MODE_WEBSITE:
                    $domainRepository = new \Cx\Core\Net\Model\Repository\DomainRepository();
                    $mainDomain       = $domainRepository->getMainDomain()->getName();
                    if (!\FWValidator::isEmpty($mainDomain)) {
                        return array('status' => 'success', 'mainDomain' => $mainDomain);
                    }
                break;
            }
            return array('status' => 'error');       
        } catch (\Exception $e) {
            \DBG::log('JsonMultiSite::getMainDomain() failed:' . $e->getMessage());
            throw new MultiSiteJsonException('JsonMultiSite::getMainDomain() failed: to get the main domain');
        }
        
    }
    
    /**
     * Delete the website Account
     * 
     * @return array
     */
    public function deleteAccount() 
    {
        global $_ARRAYLANG;
        
        try {
            if (!ComponentController::isUserLoggedIn()) {
            \DBG::log($_ARRAYLANG['TXT_MULTISITE_WEBSITE_LOGIN_NOACCESS']);
            return array('status' => 'error', 'message' => $_ARRAYLANG['TXT_MULTISITE_WEBSITE_USER_ACCOUNT_DELETE_FAILED']);
            }

            $crmContactId = \FWUser::getFWUserObject()->objUser->getCrmUserId();
            if (\FWValidator::isEmpty($crmContactId)) {
                \DBG::log($_ARRAYLANG['TXT_MULTISITE_WEBSITE_NOT_MULTISITE_USER']);
                return array('status' => 'error', 'message' => $_ARRAYLANG['TXT_MULTISITE_WEBSITE_USER_ACCOUNT_DELETE_FAILED']);
            }

            $orders = \Env::get('em')->getRepository('Cx\Modules\Order\Model\Entity\Order')->findBy(array('contactId' => $crmContactId));
            if (\FWValidator::isEmpty($orders)) {
                \DBG::log('Their is no order for this user.');
                return array('status' => 'error', 'message' => $_ARRAYLANG['TXT_MULTISITE_WEBSITE_USER_ACCOUNT_DELETE_FAILED']);
            }
            
            foreach ($orders as $order) {
                $subscriptions = $order->getSubscriptions();
                if (\FWValidator::isEmpty($subscriptions)) {
                    continue;
                }
                foreach ($subscriptions as $subscription) {

                    if ($subscription->getState() != \Cx\Modules\Order\Model\Entity\Subscription::STATE_TERMINATED) {
                        $subscription->terminate();
                    }
                    
                    $productEntity = $subscription->getProductEntity();
                    if (!\FWValidator::isEmpty($productEntity)) {
                        \Env::get('em')->remove($productEntity);
                    }
                }
            }
            
            \Env::get('em')->flush();
            
            //delete currently sign-in user
            if (!\FWUser::getFWUserObject()->objUser->delete(true)) {
                \DBG::log('Failed to delete the user account');
                return array('status' => 'error', 'message' => $_ARRAYLANG['TXT_MULTISITE_WEBSITE_USER_ACCOUNT_DELETE_FAILED']);
            }
            
            $marketingWebsiteUrl = ComponentController::getApiProtocol().\Cx\Core\Setting\Controller\Setting::getValue('marketingWebsiteDomain','MultiSite');
            return array('status' => 'success', 'marketingWebsiteUrl' => $marketingWebsiteUrl);
        } catch (\Exception $e) {
             \DBG::log('JsonMultiSite::deleteAccount() failed:' . $e->getMessage());
            throw new MultiSiteJsonException($_ARRAYLANG['TXT_MULTISITE_WEBSITE_USER_ACCOUNT_DELETE_FAILED']);
        }
        
    }
    
    /**
     * Set the Main Domain
     * 
     * @param  array  $params
     * @return string status
     * @throws MultiSiteJsonException
     */
    public function setMainDomain($params) {
        global $_ARRAYLANG;
        self::loadLanguageData();
        
        if (empty($params['post']) || !isset($params['post']['mainDomainId'])) {
            \DBG::log('JsonMultiSite::setMainDomain() failed: mainDomainId is empty');
            throw new MultiSiteJsonException($_ARRAYLANG['TXT_CORE_MODULE_MULTISITE_WEBSITE_DOMAIN_UNKNOWN']);
        }
        try {
            switch (\Cx\Core\Setting\Controller\Setting::getValue('mode','MultiSite')) {
                case ComponentController::MODE_WEBSITE:
                    \Cx\Core\Setting\Controller\Setting::init('Config', 'site', 'Yaml');
                    \Cx\Core\Setting\Controller\Setting::set('mainDomainId', $params['post']['mainDomainId']);
                    \Cx\Core\Setting\Controller\Setting::update('mainDomainId');
                    return array(
                        'status' => 'success'
                    );
                    break;
            }
        } catch (\Exception $e) {
            \DBG::dump('JsonMultiSite::setMainDomain() failed:'. $e->getMessage());
            throw new MultiSiteJsonException('JsonMultiSite::setMainDomain() failed:'. $e->getMessage());
        }
    }
    
    /**
     * Create|Delete|Rename domain alias or rename subscription in plesk.
     * 
     * @param array $params
     * 
     * @return array
     * @throws MultiSiteJsonException
     */
    public function domainManipulation($params)
    {
        global $_ARRAYLANG;
        self::loadLanguageData();
        
        if (   \FWValidator::isEmpty($params['post']) 
            || \FWValidator::isEmpty($params['post']['websiteName'])
            || \FWValidator::isEmpty($params['post']['command'])
            || \FWValidator::isEmpty($params['post']['domainName'])
        ) {
            \DBG::log('JsonMultiSite::domainManipulation() failed: Insufficient arguments supplied: ' . var_export($params, true));
            throw new MultiSiteJsonException($_ARRAYLANG['TXT_MULTISITE_WEBSITE_NOT_EXISTS']);
        }
        
        try {
            switch (\Cx\Core\Setting\Controller\Setting::getValue('mode','MultiSite')) {
                case ComponentController::MODE_MANAGER:
                case ComponentController::MODE_HYBRID:
                    $websiteRepo = \Env::get('em')->getRepository('Cx\Core_Modules\MultiSite\Model\Entity\Website');
                    $website = $websiteRepo->findOneBy(array('name' => $params['post']['websiteName']));
                    
                    if (!$website) {
                        \DBG::log('JsonMultiSite::domainManipulation() failed: Unknown website.');
                        return array('status' => 'error', 'message' => $_ARRAYLANG['TXT_MULTISITE_WEBSITE_NOT_EXISTS']);
                    }
                    $mailServiceServer = $website->getMailServiceServer();
                    if ( !\FWValidator::isEmpty($mailServiceServer) && !\FWValidator::isEmpty($website->getMailAccountId())) {
                        $hostingController = ComponentController::getMailServerHostingController($mailServiceServer);
                    
                        if (!$hostingController) {
                            \DBG::msg('Failed to get the hosting controller.');
                            throw new MultiSiteJsonException('JsonMultiSite::domainManipulation(): Failed to process the method '.$params['post']['command'] . '().');
                        }
                        $hostingController->setWebspaceId($website->getMailAccountId());
                        $methodName = $params['post']['command'];
                        switch ($methodName) {
                            case 'renameDomainAlias':
                                if (!$hostingController->$methodName($params['post']['oldDomainName'], $params['post']['domainName'])) {
                                    \DBG::msg('Failed to process the method renameDomainAlias().');
                                    throw new MultiSiteJsonException('JsonMultiSite::domainManipulation(): Failed to process the method '.$params['post']['command'] . '().');
                                }
                                break;
                            case 'deleteDomainAlias':
                            case 'createDomainAlias':
                            case 'renameSubscriptionName':
                                if (!$hostingController->$methodName($params['post']['domainName'])) {
                                    \DBG::msg('Failed to process the method '.$params['post']['command'] . '().');
                                    throw new MultiSiteJsonException('JsonMultiSite::domainManipulation(): Failed to process the method '.$params['post']['command'] . '().');
                                }
                                break;
                            default :
                                break;
                        }
                    }
                    return array('status' => 'success');
                    break;
                    
                case ComponentController::MODE_SERVICE:
                    // forward call to manager server.                     
                    $response = self::executeCommandOnManager('domainManipulation', $params['post']);
                    if ($response && $response->status == 'success' && $response->data->status == 'success') {
                        return array('status' => 'success');
                    }
                    break;
                    
                case ComponentController::MODE_WEBSITE:
                    // forward call to manager server.                     
                    $response = self::executeCommandOnMyServiceServer('domainManipulation', $params['post']);
                    if ($response && $response->status == 'success' && $response->data->status == 'success') {
                        return array('status' => 'success');
                    }
                    break;
                    
                default :
                    break;
            }
            return array('status' => 'error', 'message' => 'JsonMultiSite::domainManipulation(): Failed to process the method '.$params['post']['command'] . '().');
        } catch (\Exception $e) {
            \DBG::dump('JsonMultiSite::domainManipulation() failed:'. $e->getMessage());
            throw new MultiSiteJsonException('JsonMultiSite::domainManipulation() failed:');
        }
    }
    
    /**
     * Get Module AdditionalData
     * 
     * @param array $params
     * 
     * @return array
     * @throws MultiSiteJsonException
     */
    public function getModuleAdditionalData($params) {
        if (\FWValidator::isEmpty($params['post']) || \FWValidator::isEmpty($params['post']['moduleName'])) {
            throw new MultiSiteJsonException('JsonMultiSite::getModuleAdditionalData() failed: Insufficient arguments supplied: ' . var_export($params, true));
        }
        
        $moduleName     = isset($params['post']['moduleName']) ? $params['post']['moduleName'] : '';
        $additionalType = isset($params['post']['additionalType']) ? $params['post']['additionalType'] : '';
        try {
            switch (\Cx\Core\Setting\Controller\Setting::getValue('mode','MultiSite')) {
                case ComponentController::MODE_WEBSITE:
                    $additionalData = ComponentController::getModuleAdditionalDataByType($moduleName, $additionalType);
                    if (\FWValidator::isEmpty($additionalData)) {
                        return array('status' => 'error'); 
                    }
                    return array('status' => 'success', 'additionalData' => $additionalData);
                    break;
                default :
                    break;
            }
            
        } catch (\Exception $e) {
            \DBG::dump('JsonMultiSite::getModuleAdditionalData() failed:'. $e->getMessage());
            throw new MultiSiteJsonException('JsonMultiSite::getModuleAdditionalData() failed: Failed to get the Module additional data.');
        }
    }
    
    /**
     * Change Plan Of MailSubscription
     * 
     * @param array $params
     * 
     * @return array
     * @throws MultiSiteJsonException
     */
    public function changePlanOfMailSubscription($params)
    {
        if (   \FWValidator::isEmpty($params['post']) 
            || \FWValidator::isEmpty($params['post']['planId'])
            || \FWValidator::isEmpty($params['post']['websiteId'])
        ) {
            throw new MultiSiteJsonException('JsonMultiSite::changePlanOfMailSubscription() failed: Insufficient arguments supplied: ' . var_export($params, true));
        }
        
        $planId = isset($params['post']['planId']) ? $params['post']['planId'] : '';
        
        try {
            switch (\Cx\Core\Setting\Controller\Setting::getValue('mode','MultiSite')) {
                case ComponentController::MODE_MANAGER:
                case ComponentController::MODE_HYBRID:
                    $website = \Env::get('em')->getRepository('Cx\Core_Modules\MultiSite\Model\Entity\Website')->findOneBy(array('id' => $params['post']['websiteId']));
                    if (\FWValidator::isEmpty($website)) {
                        \DBG::log('Unkown Website-ID: '.$params['post']['websiteId']);
                        return array('status' => 'error');
                    }
                    
                    $mailServiceServer = $website->getMailServiceServer();
                    $mailAccountId     = $website->getMailAccountId();
                    if (\FWValidator::isEmpty($mailServiceServer) || \FWValidator::isEmpty($mailAccountId)) {
                        \DBG::log('Mailserver/Mail account not valid.');
                        return array('status' => 'error');
                    }
                    
                    $hostingController = ComponentController::getMailServerHostingController($mailServiceServer);
                    
                    if (!$hostingController) {
                        \DBG::log('Failed to Fetch the hosting controller.');
                        return array('status' => 'error');
                    }
                    
                    if (!$hostingController->changePlanOfSubscription($mailAccountId, $planId)) {
                        \DBG::log('Failed to change plan of the subscription');
                        return array('status' => 'error');
                    }
                    return array('status' => 'success');
                    break;
                    
                case ComponentController::MODE_SERVICE:
                    $response = self::executeCommandOnManager('changePlanOfMailSubscription', $params['post']);                
                    break;
                    
                case ComponentController::MODE_WEBSITE:
                    $response = self::executeCommandOnMyServiceServer('changePlanOfMailSubscription', $params['post']);              
                    break;
                default :
                    break;
            }
            if ($response && $response->status == 'success' && $response->data->status == 'success') {
                return array('status' => 'success');
            }
            return array('status' => 'error');
        } catch (\Exception $e) {
            \DBG::log('JsonMultiSite::changePlanOfMailSubscription() failed:'. $e->getMessage());
            throw new MultiSiteJsonException('JsonMultiSite::changePlanOfMailSubscription() failed: Failed to change the plan of mail subscription.');
        }
    }
    
    /**
     * Check whether the website component is licensed or not.
     * 
     * @param  array  $params
     * @return string $status
     * @throws MultiSiteJsonException
     */
    public function isComponentLicensed($params) 
    {
        if (empty($params['post']) || !isset($params['post']['component'])) {
            \DBG::log('JsonMultiSite::isComponentLicensed() failed: component is empty');
            throw new MultiSiteJsonException('Unknown component requested.');
        }
        try {
            switch (\Cx\Core\Setting\Controller\Setting::getValue('mode','MultiSite')) {
                case ComponentController::MODE_WEBSITE:
                    if((in_array($params['post']['component'], \Env::get('cx')->getLicense()->getLegalComponentsList()))) {
                        return array('status' => 'success');
                    }
                    break;
            }
            return array('status' => 'error');
        } catch (\Exception $e) {
            \DBG::dump('JsonMultiSite::isComponentLicensed() failed:'. $e->getMessage());
            throw new MultiSiteJsonException('JsonMultiSite::isComponentLicensed() failed:'. $e->getMessage());
        }
    }
    
    /**
     * Check whether the domain name is subdomain of multisite domain or not.
     * 
     * @param string $domainName 
     * @return boolean
     */
    public function isDomainASubDomainOfMultisiteDomain($domainName) {
        if (empty($domainName)) {
            return false;
        }
        
        $multiSiteDomain = \Cx\Core\Setting\Controller\Setting::getValue('multiSiteDomain','MultiSite');
        if (preg_match('/'.preg_quote('.'.$multiSiteDomain, '/').'$/i', $domainName)) {
            return true;
        }
        
        return false;
    }
    
    /**
     * Set Affiliate to the customer.
     * 
     * @param array $params
     * 
     * @return array
     * @throws MultiSiteJsonException
     */
    public function setAffiliate($params)
    {
        global $_ARRAYLANG;
        
        if (   empty($params['post']) 
            || (   empty($params['post']['affiliateProfileAttributeId'])
                && empty($params['post']['paypalEmailAddress']))
            ) {
            \DBG::msg('JsonMultiSite::setAffiliate() failed: Insufficient arguments supplied: ' . var_export($params, true));
            throw new MultiSiteJsonException($_ARRAYLANG['TXT_MULTISITE_AFFILIATE_ID_ERROR']);
        }
        
        try {
            switch (\Cx\Core\Setting\Controller\Setting::getValue('mode','MultiSite')) {
                case ComponentController::MODE_MANAGER:
                case ComponentController::MODE_HYBRID:
                    $objUser = \FWUser::getFWUserObject()->objUser;
                    if (isset($params['post']['affiliateProfileAttributeId']) && !empty($params['post']['affiliateProfileAttributeId'])) {
                        $affiliateIdProfileAttributeId = \Cx\Core\Setting\Controller\Setting::getValue('affiliateIdProfileAttributeId','MultiSite');
                        $affiliateId = $objUser->getProfileAttribute($affiliateIdProfileAttributeId);
                        if (!empty($affiliateId)) {
                            \DBG::msg('JsonMultiSite::setAffiliate() failed: Already the AffiliateId value present.');
                            return array('status' => 'error', 'type' => 'affiliateId', 'message' => $_ARRAYLANG['TXT_MULTISITE_AFFILIATE_ID_SET_ALREADY']);
                        }

                        $affiliateIdValue = $params['post']['affiliateProfileAttributeId'];
                        if (!self::validateAffiliateId($affiliateIdValue)) {
                            return array('status' => 'error', 'type' => 'affiliateId', 'message' => $_ARRAYLANG['TXT_MULTISITE_AFFILIATE_ID_ERROR']);
                        }
                        $objUser->setProfile(
                            array(
                                $affiliateIdProfileAttributeId => array(0 => $affiliateIdValue)
                            ),
                            true
                        );
                    }
                    if (isset($params['post']['paypalEmailAddress']) && !empty($params['post']['paypalEmailAddress'])) {
                        $paypalEmailAddress = $params['post']['paypalEmailAddress'];
                        if (!\FWValidator::isEmail($paypalEmailAddress)) {
                            return array('status' => 'error', 'type' => 'mail',  'message' => $_ARRAYLANG['TXT_CORE_MODULE_MULTISITE_INVALID_EMAIL']);                        
                        }
                        $paypalEmailAddressProfileAttribute = \Cx\Core\Setting\Controller\Setting::getValue('payPalProfileAttributeId','MultiSite');
                        $objUser->setProfile(
                            array(
                                $paypalEmailAddressProfileAttribute => array(0 => $paypalEmailAddress)
                            ),
                            true
                        );
                    }
                    if (!$objUser->store()) {
                        \DBG::msg('JsonMultiSite::setAffiliate() failed: can not store the AffiliateId.');
                        return array('status' => 'error', 'type' => 'affiliateId', 'message' => $_ARRAYLANG['TXT_MULTISITE_AFFILIATE_ID_ERROR']);
                    }
                    return array('status' => 'success', 'message' => $_ARRAYLANG['TXT_CORE_MODULE_MULTISITE_USER_PROFILE_UPDATED_SUCCESS']);
                    break;
                default :
                    break;
            }
        } catch (\Exception $e) {
            \DBG::msg('JsonMultiSite::setAffiliate() failed: ' . $e->getMessage());
            throw new MultiSiteJsonException($e->getMessage());
        }        
    }
    
    /**
     * Check availability of AffiliateId
     * 
     * @param array $params
     * 
     * @return array
     * @throws MultiSiteJsonException
     */    
    public function checkAvailabilityOfAffiliateId($params)
    {
        global $_ARRAYLANG;
        
        if (empty($params['post']) 
            || !isset($params['post']['affiliateProfileAttributeId'])
        ) {
            \DBG::msg('JsonMultiSite::checkAvailabilityOfAffiliateId() failed: Insufficient arguments supplied: ' . var_export($params, true));
            throw new MultiSiteJsonException($_ARRAYLANG['TXT_MULTISITE_AFFILIATE_ID_ERROR']);
        }
        try {
            switch (\Cx\Core\Setting\Controller\Setting::getValue('mode','MultiSite')) {
                case ComponentController::MODE_MANAGER:
                case ComponentController::MODE_HYBRID:
                    $affiliateId = $params['post']['affiliateProfileAttributeId'];
                    if (self::validateAffiliateId($affiliateId)) {
                        return array('status' => 'success');
                    }
                    break;
                default:
                    break;
            }
            return array('status' => 'error', 'message' => $_ARRAYLANG['TXT_MULTISITE_AFFILIATE_ID_ERROR']);
        } catch (\Exception $e) {
            \DBG::msg('JsonMultiSite::checkAvailabilityOfAffiliateId() failed:' . $e->getMessage());
            throw new MultiSiteJsonException($e->getMessage());
        }
    }
    
    /**
     * Validate the affiliateId
     * 
     * @param string $affiliateId
     * 
     * @return boolean
     * @throws MultiSiteJsonException
     */
    public static function validateAffiliateId($affiliateId)
    {
        global $_ARRAYLANG;
        if (empty($affiliateId)) {
            \DBG::msg('JsonMultiSite::validateAffiliateId() failed: Insufficient arguments supplied.');
            return false;
        } 
        if (ComponentController::isValidAffiliateId($affiliateId)) {
            \DBG::msg('JsonMultiSite::validateAffiliateId() failed: AffiliateId not available.');
            throw new MultiSiteException($_ARRAYLANG['TXT_MULTISITE_AFFILIATE_ID_NOT_AVAILABLE']);
        }
        // verify that affiliateId complies with naming scheme
        if (preg_match('/[^a-z0-9]/', $affiliateId)) {
            \DBG::msg('JsonMultiSite::validateAffiliateId() failed: AffiliateId must contain only lowercase letters (a-z) and numbers.');
            throw new MultiSiteException($_ARRAYLANG['TXT_CORE_MODULE_MULTISITE_AFFILIATE_ID_WRONG_CHARS']);
        }
        if (strlen($affiliateId) < \Cx\Core\Setting\Controller\Setting::getValue('websiteNameMinLength','MultiSite')) {
            \DBG::msg('JsonMultiSite::validateAffiliateId() failed: Affiliate-ID too shorter.');
            throw new MultiSiteException(sprintf($_ARRAYLANG['TXT_CORE_MODULE_MULTISITE_AFFILIATE_ID_TOO_SHORT'], \Cx\Core\Setting\Controller\Setting::getValue('websiteNameMinLength','MultiSite')));
        }
        if (strlen($affiliateId) > \Cx\Core\Setting\Controller\Setting::getValue('websiteNameMaxLength','MultiSite')) {
            \DBG::msg('JsonMultiSite::validateAffiliateId() failed: Affiliate-ID too longer.');
            throw new MultiSiteException(sprintf($_ARRAYLANG['TXT_CORE_MODULE_MULTISITE_AFFILIATE_ID_TOO_LONG'], \Cx\Core\Setting\Controller\Setting::getValue('websiteNameMaxLength','MultiSite')));
        }
        // verify that AffiliateId is not a blocked word
        $unavailablePrefixesValue = explode(',',\Cx\Core\Setting\Controller\Setting::getValue('unavailablePrefixes','MultiSite'));
        if (in_array($affiliateId, $unavailablePrefixesValue)) {
            \DBG::msg('JsonMultiSite::validateAffiliateId() failed: AffiliateId is a blocked word.');
            throw new MultiSiteException($_ARRAYLANG['TXT_MULTISITE_AFFILIATE_ID_BLOCKED']);
        }
        return true;
    }
    
    /**
     * Send the notification mail for payout request
     * 
     * @global array $_ARRAYLANG
     * 
     * @return array
     * @throws MultiSiteJsonException
     */
    public function sendNotificationForPayoutRequest() {
        global $_ARRAYLANG;
        
        try {
            $config = \Env::get('config');
            $objUser = \FWUser::getFWUserObject()->objUser;
            
            //Get the balance amount
            $affiliateCreditRepo = \Env::get('em')->getRepository('\Cx\Core_Modules\MultiSite\Model\Entity\AffiliateCredit');        
            $affiliateTotalCreditAmount = $affiliateCreditRepo->getTotalCreditsAmount();
            //get the currency code
            $currencyId = \Cx\Modules\Crm\Controller\CrmLibrary::getCurrencyIdByCrmId($objUser->getCrmUserId());
            if (empty($currencyId)) {
                $currencyId = \Cx\Modules\Crm\Controller\CrmLibrary::getDefaultCurrencyId();
            }
            $currencyObj  = \Env::get('em')->getRepository('\Cx\Modules\Crm\Model\Entity\Currency')->findOneById($currencyId);
            $currencyCode = $currencyObj ? $currencyObj->getName() : '';
            
            \Cx\Core\MailTemplate\Controller\MailTemplate::init('MultiSite');
            \Cx\Core\MailTemplate\Controller\MailTemplate::send(array(
                'section' => 'MultiSite',
                'key'     => 'payout_request',
                'to'      => $config['coreAdminEmail'],
                'search'  => array(
                    '[[USER_NAME]]',
                    '[[CREDIT_BALANCE]]',
                    '[[USER_EMAIL]]'
                ),
                'replace' => array(
                    \FWUser::getParsedUserTitle($objUser->getId()),
                    $affiliateTotalCreditAmount . ' ' . $currencyCode,
                    $objUser->getEmail()
                ),
            ));
        } catch (\Exception $e) {
            \DBG::msg($e->getMessage());
            return array('status' => 'error', 'message' => $_ARRAYLANG['TXT_CORE_MODULE_MULTISITE_PAYOUT_REQUEST_ERROR']);
        }
        return array('status' => 'success', 'message' => $_ARRAYLANG['TXT_CORE_MODULE_MULTISITE_PAYOUT_REQUEST_SUCCESS']);
    }
} 