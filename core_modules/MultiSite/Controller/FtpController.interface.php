<?php

/**
 * Main controller for Multisite
 * 
 * @copyright   Comvation AG
 * @author      Project Team SS4U <info@comvation.com>
 * @package     contrexx
 * @subpackage  coremodule_multisite
 */

namespace Cx\Core_Modules\MultiSite\Controller;

class FtpControllerException extends \Exception {}

/**
 * manage the Ftp accounts
 * 
 * @copyright   Comvation AG
 * @author      Project Team SS4U <info@comvation.com>
 * @package     contrexx
 * @subpackage  coremodule_multisite
 */
interface FtpController {

    /**
     * Add the new Ftp Account
     * 
     * @param string  $userName       FTP user name
     * @param string  $password       FTP password
     * @param string  $homePath       FTP accessible path
     * @param integer $subscriptionId Id of plesk subscription to add the FTP
     * 
     * @return object
     * @throws ApiRequestException On error
     */
    public function addFtpAccount($userName, $password, $homePath, $subscriptionId);

    /**
     * Delete the FTP Account
     * 
     * @param string $userName FTP user name
     * 
     * @return object
     * @throws ApiRequestException On error
     */
    public function removeFtpAccount($userName);

    /**
     * Change the FTP Account password
     * 
     * @param string $userName FTP user name
     * @param string $password FTP password
     * 
     * @return object
     * @throws ApiRequestException On error
     */
    public function changeFtpAccountPassword($userName, $password);

    /**
     * Get All the Ftp Accounts
     * 
     * @param boolean $extendedData Get additional data of the FTP user
     * 
     * @return array
     * @throws ApiRequestException On error
     */
    public function getFtpAccounts($extendedData = false);
}
