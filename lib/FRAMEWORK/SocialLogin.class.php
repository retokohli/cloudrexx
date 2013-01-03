<?php

namespace Cx\Lib;

/**
 * Social Login
 *
 * This class is used to provide a support for social media login.
 * @copyright   CONTREXX CMS - COMVATION AG
 * @author      Ueli Kramer <ueli.kramer@comvation.com>
 * @version     1.0.0
 * @package     contrexx
 * @subpackage  lib_framework
 */
class SocialLogin
{
    const SETTINGS_PROVIDER_PREFIX = 'provider_';
    const SETTINGS_PROVIDER_DELIMITER = ',';

    private static $providers = array();

    public function __construct()
    {
        self::$providers = self::getProviders();
    }

    /**
     * Login with a provider.
     * Loads the correct provider class from Lib\OAuth\.. and uses his methods to
     * login to the social platform
     *
     * @param string $provider the chosen oauth provider
     * @return null if the chosen oauth provider does not exist
     */
    public function loginWithProvider($provider)
    {
        if (empty(self::$providers[$provider])) {
            return null;
        } else {
            $class = self::getClassByProvider($provider);
            if ($class != null) {
                $OAuth = new $class;
                $OAuth->setApplicationData(self::$providers[$provider]);
                $OAuth->login();
            } else {
                return null;
            }
        }
    }

    /**
     * Get the oauth class of the provider
     *
     * @static
     * @param string $provider the provider name
     * @return null|OAuth
     */
    public static function getClassByProvider($provider)
    {
        $class = '\Cx\Lib\OAuth\\' . ucfirst($provider);
        include_once ASCMS_FRAMEWORK_PATH . '/OAuth/' . ucfirst($provider) . '.class.php';
        if (class_exists($class)) {
            return $class;
        }
        return null;
    }

    /**
     * Gets all the providers from the setting db.
     *
     * @static
     * @return array the providers and their data
     */
    public static function getProviders()
    {
        \SettingDb::init('access', 'sociallogin');

        $providers = array();

        $settingProviders = explode(self::SETTINGS_PROVIDER_DELIMITER, \SettingDb::getValue('providers'));
        foreach ($settingProviders as $settingProvider) {
            if (empty($settingProvider)) {
                continue;
            }

            $providerSettings = \SettingDb::getValue(self::SETTINGS_PROVIDER_PREFIX . $settingProvider);
            $providers[$settingProvider] = explode(self::SETTINGS_PROVIDER_DELIMITER, $providerSettings);
        }

        return $providers;
    }

    /**
     * Updates the providers and write changes to the setting db.
     * The provider array has to be two dimensional.
     *
     * array(
     *     ProviderName1 => array(provider_app_id, provider_app_secret),
     *     ProviderName1 => array(provider_app_id, provider_app_secret),
     * )
     *
     * @static
     * @param array $providers the new provider data
     */
    public static function updateProviders($providers)
    {
        \SettingDb::init('access', 'sociallogin');

        foreach ($providers as $providerName => $providerValue) {

            $newProviderData = implode(self::SETTINGS_PROVIDER_DELIMITER, $providerValue);

            $key = contrexx_input2raw(self::SETTINGS_PROVIDER_PREFIX . $providerName);
            $value = contrexx_input2raw($newProviderData);

            // if the provider has not yet saved data in the setting db, the provider has to be added first
            $providerData = \SettingDb::getValue($key);
            if (is_null($providerData)) {
                \SettingDb::add($key, $value);
            } else {
                \SettingDb::set($key, $value);
            }
        }
        \SettingDb::updateAll();
    }

    /**
     * Generates the contrexx login link to log in with the given provider.
     * This can be used to generate the redirect url.
     *
     * @static
     * @param string $provider the provider name
     * @param string|null $redirect the redirect url
     * @return string
     */
    public static function getLoginUrl($provider, $redirect = null)
    {
        global $_CONFIG, $objInit;
        return ASCMS_PROTOCOL . '://' . $_CONFIG['domainUrl'] . ASCMS_PATH_OFFSET . '/' .
            \FWLanguage::getLanguageCodeById($objInit->getDefaultFrontendLangId()) .
            '/index.php?section=login&provider=' . $provider . (!empty($redirect) ? '&redirect=' . $redirect : '');
    }
}
