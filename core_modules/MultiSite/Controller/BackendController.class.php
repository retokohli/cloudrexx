<?php
/**
 * Specific BackendController for this Component. Use this to easily create a backend view
 *
 * @copyright   Comvation AG
 * @author      Michael Ritter <michael.ritter@comvation.com>
 * @package     contrexx
 * @subpackage  modules_skeleton
 */

namespace Cx\Core_Modules\MultiSite\Controller;

/**
 * Specific BackendController for this Component. Use this to easily create a backend view
 *
 * @copyright   Comvation AG
 * @author      Michael Ritter <michael.ritter@comvation.com>
 * @package     contrexx
 * @subpackage  modules_skeleton
 */
class BackendController extends \Cx\Core\Core\Model\Entity\SystemComponentBackendController {
     
    /**
     * Returns a list of available commands (?act=XY)
     * @return array List of acts
     */
    public function getCommands() {
        return array('statistics','settings'=> array('email','website_service_servers'));
    }
    
    /**
     * Use this to parse your backend page
     * 
     * You will get the template located in /View/Template/{CMD}.html
     * You can access Cx class using $this->cx
     * To show messages, use \Message class
     * @param \Cx\Core\Html\Sigma $template Template for current CMD
     * @param array $cmd CMD separated by slashes
     */
    public function parsePage(\Cx\Core\Html\Sigma $template, array $cmd) {
        switch (current($cmd)) {
            case 'settings':
                if(!empty($cmd[1]) && $cmd[1]=='email')
                {   
                    $config = \Env::get('config');
                    $template->setVariable(array(
                        'TABLE' => \Cx\Core\MailTemplate\Controller\MailTemplate::adminView('MultiSite', 'nonempty', $config['corePagingLimit'], 'settings/email')->get(),
                    ));
                }elseif(!empty($cmd[1]) && $cmd[1]=='website_service_servers'){
                    $websiteServiceServers = \Env::get('em')->getRepository('Cx\Core_Modules\MultiSite\Model\Entity\WebsiteServiceServer')->findAll();
                    if(empty($websiteServiceServers)){
                        $websiteServiceServers = new \Cx\Core_Modules\MultiSite\Model\Entity\WebsiteServiceServer();
                    }
                    $view = new \Cx\Core\Html\Controller\ViewGenerator($websiteServiceServers,
                        array(
                            'functions' => array(
                                'edit' => true,
                                'add' => true,
                                'delete' => true,
                                'sorting' => true,
                                'paging' => true,       // cannot be turned off yet
                                'filtering' => false,   // this does not exist yet
                            ),
                            'fields' => array(
                                'secretKey' => array(
                                    'showOverview' => false,
                                ),
                                'installationId' => array(
                                    'showOverview' => false,
                                ),
                                'httpAuthMethod' => array(
                                    'showOverview' => false,
                                ),
                                'httpAuthUsername' => array(
                                    'showOverview' => false,
                                ),
                                'httpAuthPassword' => array(
                                    'showOverview' => false,
                                ),
                            )
                        )
                    );
                    $isSingle = false;
                    $view->render($isSingle);
                    $renderedView = $view->render($isSingle);
                    $template->setVariable(array(
                        'TABLE' => $renderedView
                    ));
                }else{
                    $this->settings($template);
                }
                break;
            case 'statistics':
                //dynamic use websites path
                //self::errorHandler();
                //\Cx\Core\Setting\Controller\Setting::init('MultiSite', 'config');
                $websitesPath=\Cx\Core\Setting\Controller\Setting::getValue('websitePath');
                // this a very ugly BETA with no much comment and wrong english in it
                $instRepo = \Env::get('em')->getRepository('\Cx\Core_Modules\MultiSite\Model\Entity\Website');
                $websites = new \Cx\Core_Modules\Listing\Model\Entity\DataSet($instRepo->findAll());
                $html = '
                    <p>
                        Heute: ' . 
                        $instRepo->findWebsitesBetween(
                            $websitesPath,
                            new \DateTime(date('Y-m-d 00:00:00')),
                            new \DateTime(date('Y-m-d 23:59:59'))
                        ) . ' Accounts<br />
                        Total: ' . $websites->size() . ' Accounts
                    </p>
                    <p><form>';
                /*$filterTable = new \BackendTable(array('width'=>'100%'));
                $filterTable->setCellContents(0, 0, 'Filter', 'th');
                $filterTable->setCellContents(1, 0, '
                    <input type="datetime" class="datetime" name="startdate" />
                    <input type="datetime" class="datetime" name="enddate" />
                    <input type="submit" class="button" name="submit" />
                ', 'td', 0, false);
                $html .= $filterTable;*/
                $html .= '
                    </form></p>
                    <table border="1" style="border-collapse: collapse;" cellpadding="2">
                        <tr>
                            <th>Jahr</th>
                            <th>Total</th>
                            <th>Jan</th>
                            <th>Feb</th>
                            <th>Mar</th>
                            <th>Apr</th>
                            <th>Mai</th>
                            <th>Jun</th>
                            <th>Jul</th>
                            <th>Aug</th>
                            <th>Sep</th>
                            <th>Oct</th>
                            <th>Nov</th>
                            <th>Dec</th>
                        </tr>';
                for ($year = '2013'; $year <= date('Y'); $year++) {
                    $html .= '
                        <tr>
                            <td>' . $year . '</td>
                            <td>' . 
                    $instRepo->findWebsitesBetween(
                        $websitesPath,
                        new \DateTime(date($year . '-01-01 00:00:00')),
                        new \DateTime(date($year . '-12-31 23:59:59'))
                    ) . '
                            </td>';
                    for ($month = 1; $month <= 12; $month++) {
                        if ($month < 10) {
                            $month = '0'.$month;
                        }
                        $html .= '
                            <td>' . 
                        $instRepo->findWebsitesBetween(
                            $websitesPath,
                            new \DateTime(date($year . '-' . $month . '-01 00:00:00')),
                            new \DateTime(date($year . '-' . $month . '-t 23:59:59'))
                        ) . '
                            </td>';
                    }
                    $html .= '
                        </tr>';
                }
                $html .= '
                    </table>';
                $template->setVariable(array(
                    'TABLE' => $html,
                ));
                break;
            default:
                //dynamic use websites path
               // \Cx\Core\Setting\Controller\Setting::init('MultiSite', 'config','FileSystem');
                //self::errorHandler();
                $websitesPath=\Cx\Core\Setting\Controller\Setting::getValue('websitePath');
                $instRepo = \Env::get('em')->getRepository('\Cx\Core_Modules\MultiSite\Model\Entity\Website');
                $websites = new \Cx\Core_Modules\Listing\Model\Entity\DataSet($instRepo->findAll());
                if (isset($_GET['adminLogin'])) {
                    $website = $instRepo->findByName($_GET['adminLogin']);
                    if ($website) {
                        // perform login via JSON
                        $websiteDomain = contrexx_input2raw($_GET['adminLogin']) . '.' . substr(\Cx\Core\Setting\Controller\Setting::getValue('multiSiteDomain'), 0);
                        $websiteUrl = 'https://' . $websiteDomain;
                        $jd = new \Cx\Core\Json\JsonData();
                        //$jd->setSessionId(session_id());
                        $response = $jd->getJson(
                            $websiteUrl.'/cadmin/index.php?cmd=jsondata&object=user&act=loginUser',
                            array(
                                'USERNAME' => 'O5vie5gOnIMY3Xbi@'.\Cx\Core\Setting\Controller\Setting::getValue('multiSiteDomain'),
                                'PASSWORD' => 'NwIoOKjujfMlLcMnZCoSxFyZ6hmKnRxa',
                            )
                        );
                        
                        if ($response) {
                            // redirect user to website
                            \CSRF::redirect($websiteUrl.'/cadmin/index.php?autoLogin=' . $response->data->key);
                        }
                    }
                }

                if (!isset($_GET['order'])) {
                    $_GET['order'] = 'createdAt/DESC';
                }
                //require_once ASCMS_DOCUMENT_ROOT.'config/settings.php';
                       
                $view = new \Cx\Core\Html\Controller\ViewGenerator($websites, array(
                    'functions' => array(
                        'edit' => true,
                        'delete' => true,
                        'sorting' => true,
                        'paging' => true,       // cannot be turned off yet
                        'filtering' => false,   // this does not exist yet
                    ),
                    'fields' => array(
                        'name' => array(
                            'readonly' => true,
                            'table' => array(
                                'parse' => function($value) {
                                    //$websiteUrl = '<a href="https://' . $value . '.cloudrexx.com/" target="_blank">' . $value . '</a>';
                                    $websiteUrl = '<a href="https://' . $value .\Cx\Core\Setting\Controller\Setting::getValue('multiSiteDomain').'" target="_blank">' . $value . '</a>';
                                    $backendLogin = ' (<a href="?cmd=MultiSite&adminLogin=' . $value . '" target="_blank">BE</a>)';
                                    return $websiteUrl . $backendLogin;
                                },
                            ),
                        ),
                        /*'licenseState' => array(
                            'readonly' => true,
                        ),
                        'licenseEdition' => array(
                            'readonly' => true,
                        ),
                        'createdAt' => array(
                            'readonly' => true,
                        ),
                        'licenseLastUpdate' => array(
                            'readonly' => true,
                        ),*/
                        'codeBase' => array(
                            'showOverview' => true,
                        ),
                    ),
                ));
                $isSingle = false;
                $renderedView = $view->render($isSingle);
                if ($isSingle) {
                    // find last input field (last child of first fieldset)
                    $fieldsetChildren = current($renderedView->getForm()->getChildren())->getChildren();
                    end($fieldsetChildren);
                    $lastInputField = current($fieldsetChildren);
                    //echo $submitButton;
                    
                    // create password edit field
                    $additionalFields = array();
                    
                    $passwordField1 = new \Cx\Core\Html\Model\Entity\DataElement('password1');
                    $passwordField1->setAttribute('id', 'password1');
                    $passwordField1->setAttribute('type', 'password');
                    $additionalFields[] = \Cx\Core\Html\Controller\FormGenerator::getDataElementGroup('password1', $passwordField1);
                    
                    $passwordField2 = new \Cx\Core\Html\Model\Entity\DataElement('password2');
                    $passwordField2->setAttribute('id', 'password2');
                    $passwordField2->setAttribute('type', 'password');
                    $additionalFields[] = \Cx\Core\Html\Controller\FormGenerator::getDataElementGroup('password2', $passwordField2);
                    
                    $websiteMessage = new \Cx\Core\Html\Model\Entity\DataElement('websiteMessage');
                    $websiteMessage->setAttribute('id', 'websiteMessage');
                    $websiteMessage->setAttribute('type', 'text');
                    
                    $websiteConfigPath = $websitesPath . '/' . basename(trim($_GET['editid'])) . '/config/settings.php';
                    if (file_exists($websiteConfigPath)) {
                        require_once $websitesPath . '/' . basename(trim($_GET['editid'])) . '/config/settings.php';
                        if (!empty($_CONFIG['dashboardMessages'])) {
                            $arrDashboardMessages = unserialize(base64_decode($_CONFIG['dashboardMessages']));
                            $langCode = \FWLanguage::getLanguageCodeById(LANG_ID);
                            if (!empty($arrDashboardMessages[$langCode])) {
                                $objDashboardMessage = $arrDashboardMessages[$langCode];
                                $websiteMessage->setAttribute('value', $objDashboardMessage->getText());
                            }
                        }
                    }
                    
                    $additionalFields[] = \Cx\Core\Html\Controller\FormGenerator::getDataElementGroup('websiteMessage', $websiteMessage);
                    
                    $activeStatus = new \Cx\Core\Html\Model\Entity\DataElement('activeStatus');
                    $activeStatus->setAttribute('id', 'activeStatus');
                    $activeStatus->setAttribute('type', 'checkbox');
                    if (isset($_CONFIG['systemStatus']) && $_CONFIG['systemStatus'] == 'on') {
                        $activeStatus->setAttribute('checked', 'checked');
                    }
                    $additionalFields[] = \Cx\Core\Html\Controller\FormGenerator::getDataElementGroup('activeStatus', $activeStatus);
                    
                    // append password edit field to form (submit button is added in render() method of form)
                    $renderedView->getForm()->addChildren($additionalFields, $lastInputField, false);
                }
                $template->setVariable(array(
                    'TABLE' => $renderedView,
                ));
                break;
        }
    }
    
    /**
     * Set up the page with a list of all Settings  
     * Stores the settings if requested to.
     * @return  boolean             True on success, false otherwise
     */
    static function settings($objTemplate)
    { 
        global $_ARRAYLANG;
        
        try {
            \Cx\Core\Setting\Controller\Setting::init('MultiSite', null, 'FileSystem');  
            //check form post
            if (isset($_POST)   && !empty($_POST['bsubmit'])) {
                if (isset($_POST['websitePath']))  {
                    $_POST['websitePath']=rtrim($_POST['websitePath'],"/");
                }
                \Cx\Core\Setting\Controller\Setting::storeFromPost();
            }

            // fetch MultiSite operation mode and set websiteController
            $mode = \Cx\Core\Setting\Controller\Setting::getValue('mode');
            $websiteController = \Cx\Core\Setting\Controller\Setting::getValue('websiteController');

            if ($mode != 'website') {
                \Cx\Core\Setting\Controller\Setting::init('MultiSite', 'config', 'FileSystem');    
                \Cx\Core\Setting\Controller\Setting::show(
                    $objTemplate,
                    'index.php?cmd=MultiSite&act=settings',
                    $_ARRAYLANG['TXT_CORE_MODULE_MULTISITE'],
                    'General',
                    'TXT_CORE_MODULE_MULTISITE_'
                );
            }
            
            if (in_array($mode, array('manager', 'service', 'hybrid'))) {
                \Cx\Core\Setting\Controller\Setting::init('MultiSite', 'setup', 'FileSystem');    
                \Cx\Core\Setting\Controller\Setting::show(
                    $objTemplate,
                    'index.php?cmd=MultiSite&act=settings',
// TODO: The configuration options multiSiteDomain, unavailablePrefixes, websiteNameMaxLength and  websiteNameMinLength must be set remotely by the Website Manager
//       Once implemented, those options must be read-only or not getting listed at all
                    $_ARRAYLANG['TXT_CORE_MODULE_MULTISITE'] .($mode == 'service' ? ' - TODO: The configuration options below must be set remotely by the Website Manager! (except for option "Subscription controller")' : ''),
                    'Setup',
                    'TXT_CORE_MODULE_MULTISITE_'
                );
            }
            
            if ($mode == 'service') {
                \Cx\Core\Setting\Controller\Setting::init('MultiSite', 'websiteManager', 'FileSystem');
                \Cx\Core\Setting\Controller\Setting::show(
                    $objTemplate,
                    'index.php?cmd=MultiSite&act=settings',
                    $_ARRAYLANG['TXT_CORE_MODULE_MULTISITE'],
                    'Website Manager',
                    'TXT_CORE_MODULE_MULTISITE_'
                );
            }

            if (in_array($mode, array('service', 'hybrid'))) {
                \Cx\Core\Setting\Controller\Setting::init('MultiSite', 'websiteSetup', 'FileSystem');    
                \Cx\Core\Setting\Controller\Setting::show(
                    $objTemplate,
                    'index.php?cmd=MultiSite&act=settings',
                    $_ARRAYLANG['TXT_CORE_MODULE_MULTISITE'],
                    'Website Setup',
                    'TXT_CORE_MODULE_MULTISITE_'
                );
            }
            
            if (   in_array($mode, array('manager', 'service', 'hybrid'))
                && $websiteController == 'plesk'
            ) {
                \Cx\Core\Setting\Controller\Setting::init('MultiSite', 'plesk', 'FileSystem');
                \Cx\Core\Setting\Controller\Setting::show(
                    $objTemplate,
                    'index.php?cmd=MultiSite&act=settings',
                    $_ARRAYLANG['TXT_CORE_MODULE_MULTISITE'],
                    'Plesk',
                    'TXT_CORE_MODULE_MULTISITE_'
                );
            }

            if ($mode == 'website') {
                // config section if the MultiSite is run as Website
                \Cx\Core\Setting\Controller\Setting::init('MultiSite', 'website', 'FileSystem');
                \Cx\Core\Setting\Controller\Setting::show(
                    $objTemplate,
                    'index.php?cmd=MultiSite&act=settings',
                    $_ARRAYLANG['TXT_CORE_MODULE_MULTISITE'],
                    'Website Service',
                    'TXT_CORE_MODULE_MULTISITE_'
                );
            }
        } catch (\Exception $e) {
            \DBG::msg($e->getMessage());
            \Message::error($e->getMessage());
        }
                    
    }
}
