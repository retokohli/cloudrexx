<?php
/**
 * This is the crmInterface class file for handling the all functionalities under interface menu.
 *
 * PHP version 5.3 or >
 *
 * @category crmInterface
 * @package  PM_CRM_Tool
 * @author   ss4ugroup <ss4ugroup@softsolutions4u.com>
 * @license  BSD Licence
 * @version  1.0.0
 * @link     http://mycomvation.com/po/cadmin
 */

/**
 * This is the crmInterface class file for handling the all functionalities under interface menu.
 *
 * @category crmInterface
 * @package  PM_CRM_Tool
 * @author   ss4ugroup <ss4ugroup@softsolutions4u.com>
 * @license  BSD Licence
 * @version  1.0.0
 * @link     http://mycomvation.com/po/cadmin
 */

class crmInterface extends CrmLibrary
{
    private $_delimiter = array(
                            array(
                                'title'=>'Comma',
                                'value' => ',',
                                'placeholder' => 'TXT_CRM_COMMA'
                            ),
                            array(
                                'title'=>'Semicolon',
                                'value' => ';',
                                'placeholder' => 'TXT_CRM_SEMICOLON'
                            ),
                            array(
                                'title'=>'Colon',
                                'value' => ':',
                                'placeholder' => 'TXT_CRM_COLON'
                            ),
                          );
    private $_enclosure = array(
                            array(
                                'title'=>'Double quote',
                                'value' => '"',
                                'placeholder' => 'TXT_CRM_DOUBLE_QUOTE'
                            ),
                            array(
                                'title'=>'Single quote',
                                'value' => "'",
                                'placeholder' => 'TXT_CRM_DOUBLE_QUOTE'
                            ),
                          );
    private $_mediaPath = '';

    /**
     * Template object
     *     
     * @param object
     */
    public $_objTpl;

    /**
     * php 5.3 contructor
     *
     * @param object $objTpl
     */
    function  __construct($objTpl)
    {
        $this->_objTpl = $objTpl;
        $this->_mediaPath = ASCMS_MEDIA_PATH.'/crm';

    }

    /**
     * It displayes the import menu
     *
     * @return customer import screen
     */
    function showImport()
    {
        global $_ARRAYLANG, $objDatabase;

        JS::activate('jqueryui');
        JS::registerCSS('lib/javascript/crm/css/main.css');        
        JS::registerJS('lib/javascript/crm/contactexport.js');
        JS::registerJS('lib/javascript/jquery.form.js');
        JS::registerJS('lib/javascript/jquery.tmpl.min.js');
        JS::registerJS('lib/javascript/jquery.base64.js');
        JS::registerJS('lib/javascript/jquery.format.js');
        
        $objTpl = $this->_objTpl;

        $objTpl->loadTemplateFile("module_{$this->moduleName}_interface_import_options.html");
        $objTpl->setGlobalVariable(array('MODULE_NAME' => $this->moduleName));

        foreach ($this->_delimiter as $key => $value) {
            $objTpl->setVariable(array(
                'CRM_DELIMITER_VALUE' => $key,
                'CRM_DELIMITER_TITLE' => $_ARRAYLANG[$value['placeholder']]
            ));
            $objTpl->parse('crm_delimiter');
        }
        foreach ($this->_enclosure as $key => $value) {
            $objTpl->setVariable(array(
                'CRM_ENCLOSURE_VALUE' => $key,
                'CRM_ENCLOSURE_TITLE' => $_ARRAYLANG[$value['placeholder']]
            ));
            $objTpl->parse('crm_enclosure');
        }
        
        $objTpl->setVariable(array(
            'TXT_CRM_TITLE_IMPORT_CONTACTS'         => $_ARRAYLANG['TXT_CRM_TITLE_IMPORT_CONTACTS'],
            'TXT_CRM_IMPORT_HEADER'                 => $_ARRAYLANG['TXT_CRM_IMPORT_HEADER'],
            'TXT_CRM_IMPORT_NOTE'                   => $_ARRAYLANG['TXT_CRM_IMPORT_NOTE'],
            'TXT_CRM_IMPORT_NOTE_DESCRIPTION'       => $_ARRAYLANG['TXT_CRM_IMPORT_NOTE_DESCRIPTION'],
            'TXT_CRM_CSV_SETTINGS'                  => $_ARRAYLANG['TXT_CRM_CSV_SETTINGS'],
            'TXT_CRM_SKIP'                          => $_ARRAYLANG['TXT_CRM_SKIP'],
            'TXT_CRM_OVERWRITE'                     => $_ARRAYLANG['TXT_CRM_OVERWRITE'],
            'TXT_CRM_DUPLICATE'                     => $_ARRAYLANG['TXT_CRM_DUPLICATE'],
            'TXT_CRM_CHOOSE_FILE'                   => $_ARRAYLANG['TXT_CRM_CHOOSE_FILE'],
            'TXT_CRM_CSV_SEPARATOR'                 => $_ARRAYLANG['TXT_CRM_CSV_SEPARATOR'],
            'TXT_CRM_CSV_ENCLOSURE'                 => $_ARRAYLANG['TXT_CRM_CSV_ENCLOSURE'],
            'TXT_CRM_ON_DUPLICATES'                 => $_ARRAYLANG['TXT_CRM_ON_DUPLICATES'],
            'TXT_CRM_CHOOSE_CSV'                    => $_ARRAYLANG['TXT_CRM_CHOOSE_CSV'],
            'TXT_CRM_ON_DUPLICATES_INFO'            => $_ARRAYLANG['TXT_CRM_ON_DUPLICATES_INFO'],
            'TXT_CRM_ON_DUPLICATE_SKIP_INFO'        => $_ARRAYLANG['TXT_CRM_ON_DUPLICATE_SKIP_INFO'],
            'TXT_CRM_ON_DUPLICATE_OVERWRITE_INFO'   => $_ARRAYLANG['TXT_CRM_ON_DUPLICATE_OVERWRITE_INFO'],
            'TXT_CRM_ON_DUPLICATE_INFO'             => $_ARRAYLANG['TXT_CRM_ON_DUPLICATE_INFO'],
            'TXT_CRM_IGNORE_FIRST_ROW'              => $_ARRAYLANG['TXT_CRM_IGNORE_FIRST_ROW'],
            'TXT_CRM_CONTINUE'                      => $_ARRAYLANG['TXT_CRM_CONTINUE'],
            'TXT_CRM_CANCEL'                        => $_ARRAYLANG['TXT_CRM_CANCEL'],
            'TXT_CRM_VERIFY_FIELDS'                 => $_ARRAYLANG['TXT_CRM_VERIFY_FIELDS'],
            'TXT_CRM_VERIFY_INFO'                   => $_ARRAYLANG['TXT_CRM_VERIFY_INFO'],
            'TXT_CRM_FILE_COLUMN'                   => $_ARRAYLANG['TXT_CRM_FILE_COLUMN'],
            'TXT_CRM_CORRESPONDING_FIELD'           => $_ARRAYLANG['TXT_CRM_CORRESPONDING_FIELD'],
            'TXT_CRM_CSV_VALUE'                     => $_ARRAYLANG['TXT_CRM_CSV_VALUE'],

            'TXT_CRM_IMPORT_NAME'                   => $_ARRAYLANG['TXT_CRM_IMPORT_NAME'],
            'TXT_CRM_EXPORT_NAME'                   => $_ARRAYLANG['TXT_CRM_EXPORT_NAME']
        ));
        
    }

    /**
     * used to fetch the csv data
     * @return csvdata
     */
    function csvImport()
    {
        global $_ARRAYLANG, $objDatabase;

        $json = array();

        $csvSeprator    = isset ($_POST['csv_delimiter']) && in_array($_POST['csv_delimiter'], array_keys($this->_delimiter)) ? $this->_delimiter[$_POST['csv_delimiter']]['value'] : $this->_delimiter[0]['value'];
        $csvDelimiter   = isset ($_POST['csv_enclosure']) && in_array($_POST['csv_enclosure'], array_keys($this->_enclosure)) ? $this->_enclosure[$_POST['csv_enclosure']]['value'] : $this->_enclosure[0]['value'];
        $csvIgnoreFirst = isset ($_POST['ignore_first']) && (int) $_POST['ignore_first'];
        $fileName       = isset ($_POST['fileUri']) ? $_POST['fileUri'] : '';

        if (!empty ($_FILES['importfile'])) {
            if (empty ($_FILES['importfile']['error'])) {
                $filename       = $this->uploadCSV('importfile', $this->_mediaPath.'/');
                if ($filename != 'error') {
                    $fileName        = $filename;
                    $json['fileUri'] = $filename;
                } else {
                    $json['error'] = 'Could not upload File';
                }
            } else {
                $json['error'] = 'Error in file';
            }            
        }

        $rowIndex = 1;

        $objCsv        = new Csv_bv($this->_mediaPath.'/'.$fileName, $csvSeprator, $csvDelimiter);
        $importedLines = 0;
        $first         = true;
        $line          = $objCsv->NextLine();
        while ($line) {
            if ($first) {
                $json['data']['contactHeader'] = $line;
                $first = false;
            }
            if ($importedLines == $rowIndex) {
                $json['data']['contactFields'] = $line;
            }
            $json['contactData'][$importedLines] = $line;
            
            ++$importedLines;
            $line = $objCsv->NextLine();
        }
        $json['data']       = base64_encode(json_encode($json['data']));
        $json['contactData']= base64_encode(json_encode($json['contactData']));
        $json['totalRows']  = $importedLines - 1;
        
        echo json_encode($json);
        exit();
    }

    
    function uploadCSV($name, $path) {
        //check file array
        if (isset($_FILES) && !empty($_FILES)) {
            //get file info
            $status = "";
            $tmpFile = $_FILES[$name]['tmp_name'];
            $fileName = $_FILES[$name]['name'];
            $fileType = $_FILES[$name]['type'];
            $fileSize = $_FILES[$name]['size'];

            if ($fileName != "" && FWValidator::is_file_ending_harmless($fileName)) {

                //check extension
                $info = pathinfo($fileName);
                $exte = $info['extension'];
                $exte = (!empty($exte)) ? '.' . $exte : '';
                $fileName = time() . $exte;
                
                //upload file
                if (@move_uploaded_file($tmpFile, $path.$fileName)) {
                    @chmod($path.$fileName, '0777');
                    $status = $fileName;
                } else {
                    $status = "error";
                }

            } else {
                $status = "error";
            }
        }
        return $status;

    }
    /**
     * It displayes the import menu
     *
     * @return customer import screen
     */
    function showExport()
    {
        global $_ARRAYLANG, $objDatabase;

        $objTpl = $this->_objTpl;

        $objTpl->loadTemplateFile("module_{$this->moduleName}_interface_export_options.html");
        $objTpl->setGlobalVariable(array('MODULE_NAME' => $this->moduleName));

        $objTpl->setVariable(array(
            'TXT_CRM_EXPORT_INFO'         => $_ARRAYLANG['TXT_CRM_EXPORT_INFO'],
            'TXT_CRM_FUNCTIONS'           => $_ARRAYLANG['TXT_CRM_FUNCTIONS'],
            'TXT_CRM_EXPORT_CUSTOMER_CSV' => $_ARRAYLANG['TXT_CRM_EXPORT_CUSTOMER_CSV'],
            'TXT_CRM_EXPORT_COMPANY'  => $_ARRAYLANG['TXT_CRM_EXPORT_COMPANY'],
            'TXT_CRM_EXPORT_PERSON'   => $_ARRAYLANG['TXT_CRM_EXPORT_PERSON'],
            'TXT_CRM_EXPORT_ACTIVE_CUSTOMER' => $_ARRAYLANG['TXT_CRM_EXPORT_ACTIVE_CUSTOMER'],

            'TXT_CRM_IMPORT_NAME'         => $_ARRAYLANG['TXT_CRM_IMPORT_NAME'],
            'TXT_CRM_EXPORT_NAME'         => $_ARRAYLANG['TXT_CRM_EXPORT_NAME']
        ));
    }

    /**
     * Export all contacts in csv format
     *
     * @global array $_ARRAYLANG
     * @global object $objDatabase
     * @global integer $_LANGID
     *
     * @return all contacts in csv format
     */
    function csvExport() {
        global $_ARRAYLANG,$objDatabase, $_LANGID;

        $process = isset ($_GET['process']) ? trim($_GET['process']) : '';
        switch ($process) {
            case '1':
                $activeWhere = " WHERE c.contact_type = 1";
                break;
            case '2':
                $activeWhere = " WHERE c.contact_type = 2";
                break;
            case 'active':
                $activeWhere = " WHERE c.status = 1";
                break;
            default:
                $activeWhere = '';
                break;
        }
        
       $query = "SELECT
                           c.id,                           
                           c.customer_id,
                           c.customer_name,
                           c.contact_familyname,
                           c.contact_type,
                           c.added_date,
                           c.customer_website,
                           c.contact_role,
                           c.notes,
                           c.gender,
                           con.customer_name AS contactCustomer,
                           t.label AS cType,
                           Inloc.value AS industryType,
                           lang.name AS language,
                           u.username,
                           user.email,
                           cur.name AS currency
                       FROM `".DBPREFIX."module_{$this->moduleName}_contacts` AS c
                       LEFT JOIN `".DBPREFIX."module_{$this->moduleName}_contacts` AS con
                         ON c.contact_customer = con.id
                       LEFT JOIN `".DBPREFIX."module_{$this->moduleName}_customer_types` AS t
                         ON c.customer_type = t.id
                       LEFT JOIN `".DBPREFIX."module_{$this->moduleName}_industry_types` AS Intype
                         ON c.industry_type = Intype.id
                       LEFT JOIN `".DBPREFIX."module_{$this->moduleName}_industry_type_local` AS Inloc
                         ON Intype.id = Inloc.entry_id AND Inloc.lang_id = ".$_LANGID."
                       LEFT JOIN `".DBPREFIX."module_{$this->moduleName}_currency` AS cur
                         ON cur.id = c.customer_currency
                       LEFT JOIN `".DBPREFIX."languages` AS lang
                         ON lang.id = c.contact_language
                       LEFT JOIN `".DBPREFIX."access_users` AS u
                         ON u.id = c.customer_addedby
                       LEFT JOIN `".DBPREFIX."access_users` AS user
                         ON user.id = c.user_account
                         ".$activeWhere."
                         order by c.id desc"; 
        $objResult = $objDatabase->Execute($query);

        switch ($process){
            case '1':
                $headerCsv = array(
                    $_ARRAYLANG['TXT_CRM_CONTACT_TYPE'],
                    $_ARRAYLANG['TXT_CRM_TITLE_COMPANY_NAME'],
                    $_ARRAYLANG['TXT_CRM_TITLE_CUSTOMERID'],
                    $_ARRAYLANG['TXT_CRM_TITLE_CUSTOMERTYPE'],
                    $_ARRAYLANG['TXT_CRM_INDUSTRY_TYPE'],
                    $_ARRAYLANG['TXT_CRM_CUSTOMER_MEMBERSHIP'],
                    $_ARRAYLANG['TXT_CRM_TITLE_CURRENCY'],
                    $_ARRAYLANG['TXT_CRM_TITLE_CUSTOMER_ADDEDBY']
                );
                break;
            case '2':
                $headerCsv = array(
                    $_ARRAYLANG['TXT_CRM_CONTACT_TYPE'],
                    $_ARRAYLANG['TXT_CRM_CONTACT_NAME'],
                    $_ARRAYLANG['TXT_CRM_FAMILY_NAME'],
                    $_ARRAYLANG['TXT_CRM_GENDER'],
                    $_ARRAYLANG['TXT_CRM_ROLE'],
                    $_ARRAYLANG['TXT_CRM_TITLE_COMPANY_NAME'],
                    $_ARRAYLANG['TXT_CRM_TITLE_CUSTOMERID'],
                    $_ARRAYLANG['TXT_CRM_TITLE_CUSTOMERTYPE'],
                    $_ARRAYLANG['TXT_CRM_CUSTOMER_MEMBERSHIP'],
                    $_ARRAYLANG['TXT_CRM_TITLE_CURRENCY'],
                    $_ARRAYLANG['TXT_CRM_TITLE_LANGUAGE'],
                    $_ARRAYLANG['TXT_CRM_ACCOUNT_EMAIL'],
                    $_ARRAYLANG['TXT_CRM_TITLE_CUSTOMER_ADDEDBY']
                );
                break;
            default:
                $headerCsv = array(
                    $_ARRAYLANG['TXT_CRM_CONTACT_TYPE'],
                    $_ARRAYLANG['TXT_CRM_CONTACT_NAME'],
                    $_ARRAYLANG['TXT_CRM_FAMILY_NAME'],
                    $_ARRAYLANG['TXT_CRM_GENDER'],
                    $_ARRAYLANG['TXT_CRM_ROLE'],
                    $_ARRAYLANG['TXT_CRM_TITLE_COMPANY_NAME'],
                    $_ARRAYLANG['TXT_CRM_TITLE_CUSTOMERID'],
                    $_ARRAYLANG['TXT_CRM_TITLE_CUSTOMERTYPE'],
                    $_ARRAYLANG['TXT_CRM_INDUSTRY_TYPE'],
                    $_ARRAYLANG['TXT_CRM_CUSTOMER_MEMBERSHIP'],
                    $_ARRAYLANG['TXT_CRM_TITLE_CURRENCY'],
                    $_ARRAYLANG['TXT_CRM_TITLE_LANGUAGE'],
                    $_ARRAYLANG['TXT_CRM_ACCOUNT_EMAIL'],
                    $_ARRAYLANG['TXT_CRM_TITLE_CUSTOMER_ADDEDBY']
                );
                break;
        }

        foreach ($this->emailOptions as $emailValue) {
            array_push($headerCsv, "{$_ARRAYLANG['TXT_CRM_EMAIL']} ({$_ARRAYLANG[$emailValue]})");
        }
        foreach ($this->phoneOptions as $phoneValue) {
            array_push($headerCsv, "{$_ARRAYLANG['TXT_CRM_PHONE']} ({$_ARRAYLANG[$phoneValue]})");
        }
        foreach ($this->websiteProfileOptions as $webValue) {
            array_push($headerCsv, "{$_ARRAYLANG['TXT_CRM_WEBSITE']} ({$_ARRAYLANG[$webValue]})");
        }
        foreach ($this->socialProfileOptions as $socialValue) {
            if (!empty ($socialValue)) {
                array_push($headerCsv, "{$_ARRAYLANG['TXT_CRM_SOCIAL_NETWORK']} ({$_ARRAYLANG[$socialValue]})");
            }
        }
        foreach ($this->addressTypes as $addressType) {
            foreach ($this->addressValues as $addressValue) {
                if (!empty ($addressValue) && $addressValue != 'type') {
                    array_push($headerCsv, "$addressValue ({$_ARRAYLANG[$addressType]})");
                }
            }
        }        
        $headerCsv[] = $_ARRAYLANG['TXT_CRM_DESCRIPTION'];

        $currDate = date('d_m_Y');
        header("Content-Type: text/comma-separated-values; charset:".CONTREXX_CHARSET, true);
        header("Content-Disposition: attachment; filename=\"Kundenstamm_$currDate.csv\"", true);
        
        foreach ($headerCsv as $field) {
            print $this->_escapeCsvValue($field).$this->_csvSeparator;
        }        
        print ("\r\n");

        if($objResult) {
            $membership = array();
            while(!$objResult->EOF) {
                $query = "SELECT c.id,
                                 memloc.value As value
                                 FROM `".DBPREFIX."module_{$this->moduleName}_contacts` As c
                                LEFT JOIN `".DBPREFIX."module_{$this->moduleName}_customer_membership` AS mem
                                    ON c.id = mem.contact_id
                                LEFT JOIN `".DBPREFIX."module_{$this->moduleName}_membership_local` AS memloc
                                ON (memloc.entry_id = mem.membership_id AND memloc.lang_id = {$_LANGID})
                              WHERE c.id = {$objResult->fields['id']}";
                $objMember = $objDatabase->Execute($query);
                while (!$objMember->EOF) {
                    array_push($membership, $objMember->fields['value']);
                    $objMember->MoveNext();
                }
                $membership = implode(', ', $membership);
                $gender = ($objResult->fields['gender'] == 1) ? $_ARRAYLANG['TXT_CRM_GENDER_FEMALE'] : ($objResult->fields['gender'] == 2) ? $_ARRAYLANG['TXT_CRM_GENDER_MALE'] : '' ;
                switch ($process)
                {
                    case '1':
                        print ($objResult->fields['contact_type'] == 1 ? 'Company' : 'Person').$this->_csvSeparator;
                        print ($objResult->fields['contact_type'] == 2 ? $this->_escapeCsvValue($objResult->fields['contactCustomer']) : $this->_escapeCsvValue($objResult->fields['customer_name'])).$this->_csvSeparator;
                        print $this->_escapeCsvValue($objResult->fields['customer_id']).$this->_csvSeparator;
                        print $this->_escapeCsvValue($objResult->fields['cType']).$this->_csvSeparator;
                        print $this->_escapeCsvValue($objResult->fields['industryType']).$this->_csvSeparator;
                        print $this->_escapeCsvValue($membership).$this->_csvSeparator;
                        print $this->_escapeCsvValue($objResult->fields['currency']).$this->_csvSeparator;
                        print $this->_escapeCsvValue($objResult->fields['username']).$this->_csvSeparator;
                        break;
                    case '2':
                        print ($objResult->fields['contact_type'] == 1 ? 'Company' : 'Person').$this->_csvSeparator;
                        print ($objResult->fields['contact_type'] == 1 ? '' : $this->_escapeCsvValue($objResult->fields['customer_name'])).$this->_csvSeparator;
                        print ($objResult->fields['contact_type'] == 1 ? '' : $this->_escapeCsvValue($objResult->fields['contact_familyname'])).$this->_csvSeparator;
                        print $this->_escapeCsvValue($gender).$this->_csvSeparator;
                        print $this->_escapeCsvValue($objResult->fields['contact_role']).$this->_csvSeparator;
                        print ($objResult->fields['contact_type'] == 2 ? $this->_escapeCsvValue($objResult->fields['contactCustomer']) : $this->_escapeCsvValue($objResult->fields['customer_name'])).$this->_csvSeparator;
                        print $this->_escapeCsvValue($objResult->fields['customer_id']).$this->_csvSeparator;
                        print $this->_escapeCsvValue($objResult->fields['cType']).$this->_csvSeparator;
                        print $this->_escapeCsvValue($membership).$this->_csvSeparator;
                        print $this->_escapeCsvValue($objResult->fields['currency']).$this->_csvSeparator;
                        print $this->_escapeCsvValue($objResult->fields['language']).$this->_csvSeparator;
                        print $this->_escapeCsvValue($objResult->fields['email']).$this->_csvSeparator;
                        print $this->_escapeCsvValue($objResult->fields['username']).$this->_csvSeparator;
                        break;
                    default:
                        print ($objResult->fields['contact_type'] == 1 ? 'Company' : 'Person').$this->_csvSeparator;
                        print ($objResult->fields['contact_type'] == 1 ? '' : $this->_escapeCsvValue($objResult->fields['customer_name'])).$this->_csvSeparator;
                        print ($objResult->fields['contact_type'] == 1 ? '' : $this->_escapeCsvValue($objResult->fields['contact_familyname'])).$this->_csvSeparator;
                        print $this->_escapeCsvValue($gender).$this->_csvSeparator;
                        print $this->_escapeCsvValue($objResult->fields['contact_role']).$this->_csvSeparator;
                        print ($objResult->fields['contact_type'] == 2 ? $this->_escapeCsvValue($objResult->fields['contactCustomer']) : $this->_escapeCsvValue($objResult->fields['customer_name'])).$this->_csvSeparator;
                        print $this->_escapeCsvValue($objResult->fields['customer_id']).$this->_csvSeparator;
                        print $this->_escapeCsvValue($objResult->fields['cType']).$this->_csvSeparator;
                        print $this->_escapeCsvValue($objResult->fields['industryType']).$this->_csvSeparator;
                        print $this->_escapeCsvValue($membership).$this->_csvSeparator;
                        print $this->_escapeCsvValue($objResult->fields['currency']).$this->_csvSeparator;
                        print $this->_escapeCsvValue($objResult->fields['language']).$this->_csvSeparator;
                        print $this->_escapeCsvValue($objResult->fields['email']).$this->_csvSeparator;
                        print $this->_escapeCsvValue($objResult->fields['username']).$this->_csvSeparator;
                        break;
                }
//                print $this->_escapeCsvValue($objResult->fields['customer_website']).$this->_csvSeparator;

                $result = array();
                // Get emails and phones
                $objEmails = $objDatabase->Execute("SELECT * FROM `".DBPREFIX."module_{$this->moduleName}_customer_contact_emails` WHERE contact_id = {$objResult->fields['id']} ORDER BY id ASC");
                if ($objEmails) {
                    while(!$objEmails->EOF) {
                        $result['contactemail'][$objEmails->fields['email_type']] = $objEmails->fields['email'];
                        $objEmails->MoveNext();
                    }
                }
                $objPhone = $objDatabase->Execute("SELECT * FROM `".DBPREFIX."module_{$this->moduleName}_customer_contact_phone` WHERE contact_id = {$objResult->fields['id']} ORDER BY id ASC");
                if ($objPhone) {
                    while(!$objPhone->EOF) {
                        $result['contactphone'][$objPhone->fields['phone_type']] = $objPhone->fields['phone'];
                        $objPhone->MoveNext();
                    }
                }
                $objWebsite = $objDatabase->Execute("SELECT * FROM `".DBPREFIX."module_{$this->moduleName}_customer_contact_websites` WHERE contact_id = {$objResult->fields['id']} ORDER BY id ASC");
                if ($objWebsite) {
                    while(!$objWebsite->EOF) {
                        $result['contactwebsite'][$objWebsite->fields['url_profile']] = $objWebsite->fields['url'];
                        $objWebsite->MoveNext();
                    }
                }
                $objSocial = $objDatabase->Execute("SELECT * FROM `".DBPREFIX."module_{$this->moduleName}_customer_contact_social_network` WHERE contact_id = {$objResult->fields['id']} ORDER BY id ASC");
                if ($objSocial) {
                    while(!$objSocial->EOF) {
                        $result['contactsocial'][$objSocial->fields['url_profile']] = $objSocial->fields['url'];
                        $objSocial->MoveNext();
                    }
                }
                $objAddress = $objDatabase->Execute("SELECT * FROM `".DBPREFIX."module_{$this->moduleName}_customer_contact_address` WHERE contact_id = {$objResult->fields['id']} ORDER BY id ASC");
                if ($objAddress) {
                    while(!$objAddress->EOF) {
                        $result['contactAddress'][$objAddress->fields['Address_Type']] = array(
                                                                                            1 => $objAddress->fields['address'],
                                                                                            2 => $objAddress->fields['city'],
                                                                                            3 => $objAddress->fields['state'],
                                                                                            4 => $objAddress->fields['zip'],
                                                                                            5 => $objAddress->fields['country'],
                                                                                         );
                        $objAddress->MoveNext();
                    }
                }

                foreach ($this->emailOptions as $key => $emailValue) {
                    print (isset($result['contactemail'][$key]) ? $this->_escapeCsvValue($result['contactemail'][$key]) : '').$this->_csvSeparator;
                }
                foreach ($this->phoneOptions as $key => $phoneValue) {
                    print (isset($result['contactphone'][$key]) ? $this->_escapeCsvValue($result['contactphone'][$key]) : '').$this->_csvSeparator;
                }
                foreach ($this->websiteProfileOptions as $proKey => $proValue) {
                    print (isset($result['contactwebsite'][$proKey]) ? $this->_escapeCsvValue($result['contactwebsite'][$proKey]) : '').$this->_csvSeparator;
                } 
                foreach ($this->socialProfileOptions as $socialKey => $socValue) {
                    if (!empty ($socValue)) {
                        print (isset($result['contactsocial'][$socialKey]) ? $this->_escapeCsvValue($result['contactsocial'][$socialKey]) : '').$this->_csvSeparator;
                    }
                }
                foreach ($this->addressTypes as $addTypeKey => $addressType) {
                    foreach ($this->addressValues as $addValKey => $addressValue) {
                        if (!empty ($addressValue) && $addressValue != 'type') {
                            print (isset($result['contactAddress'][$addTypeKey]) ? $this->_escapeCsvValue($result['contactAddress'][$addTypeKey][$addValKey]) : '').$this->_csvSeparator;
                        }
                    }
                }
                
                $description = str_replace("&nbsp;"," ",strip_tags(html_entity_decode($objResult->fields['notes'], ENT_QUOTES, CONTREXX_CHARSET)));
                print $this->_escapeCsvValue(html_entity_decode($description, ENT_QUOTES, CONTREXX_CHARSET)).$this->_csvSeparator;

                print ("\r\n");
                $objResult->MoveNext();
            }
        }
        exit();
    }

    function getImportOptions() {
        global $_ARRAYLANG;
        
        $headerCsv = array(
            array("name"   => "", "title"  => $_ARRAYLANG['TXT_CRM_NO_MATCHES_FROM_LIST'], "Header" => false),
            array("name"   => "-1", "title"  => $_ARRAYLANG['TXT_CRM_DONT_IMPORT_FIELD'], "Header" => false),
            array("name"     => "", "title"    => $_ARRAYLANG['TXT_CRM_GENERAL_INFORMATION'], "Header" => true),
            array('name' => 'firstname', 'title' => $_ARRAYLANG['TXT_CRM_CONTACT_NAME'], 'Header' => false),
            array('name' => 'lastname', 'title' => $_ARRAYLANG['TXT_CRM_FAMILY_NAME'], 'Header' => false),
            array('name' => 'company', 'title' => $_ARRAYLANG['TXT_CRM_TITLE_COMPANY_NAME'], 'Header' => false),
            array('name' => 'website', 'title' => $_ARRAYLANG['TXT_CRM_WEBSITE'], 'Header' => false),
            array('name' => 'role', 'title' => $_ARRAYLANG['TXT_CRM_ROLE'], 'Header' => false),
            array('name' => 'customertype', 'title' => $_ARRAYLANG['TXT_CRM_TITLE_CUSTOMERTYPE'], 'Header' => false),
            array('name' => 'industrytype', 'title' => $_ARRAYLANG['TXT_CRM_INDUSTRY_TYPE'], 'Header' => false),
            array('name' => 'currency', 'title' => $_ARRAYLANG['TXT_CRM_TITLE_CURRENCY'], 'Header' => false),            
            array('name' => 'customerId', 'title' => $_ARRAYLANG['TXT_CRM_TITLE_CUSTOMERID'], 'Header' => false),
            array('name' => 'language', 'title' => $_ARRAYLANG['TXT_CRM_TITLE_LANGUAGE'], 'Header' => false),
            array('name' => 'addedby', 'title' => $_ARRAYLANG['TXT_CRM_TITLE_CUSTOMER_ADDEDBY'], 'Header' => false),
            );

        foreach ($this->emailOptions as $key => $emailValue) {
            array_push($headerCsv, array('name' => "customer_email_$key", 'title' => "{$_ARRAYLANG['TXT_CRM_EMAIL']} ({$_ARRAYLANG[$emailValue]})", 'Header' => false));
        }
        foreach ($this->phoneOptions as $key => $phoneValue) {
            array_push($headerCsv, array('name' => "customer_phone_$key", 'title' => "{$_ARRAYLANG['TXT_CRM_PHONE']} ({$_ARRAYLANG[$phoneValue]})", 'Header' => false));
        }
        foreach ($this->websiteProfileOptions as $websiteKey => $webValues) {
            if (!empty($webValues)) {
                foreach ($this->emailOptions as $key => $emailValue) {
                    array_push($headerCsv, array('name' => "customer_website_{$websiteKey}_{$key}", 'title' => "{$_ARRAYLANG[$webValues]} ({$_ARRAYLANG[$emailValue]})", 'Header' => false));
                }
            }
        }
        foreach ($this->addressTypes as $addrKey => $addressType) {
            foreach ($this->addressValues as $key => $addressValue) {
                if (!empty ($addressValue) && $addressValue != 'type') {
                    array_push($headerCsv, array('name' => "customer_address_{$addrKey}_{$key}", 'title' => "Address ({$_ARRAYLANG[$addressType]}) $addressValue", 'Header' => false));
                }
            }
        }
        
        echo json_encode($headerCsv);
        exit();
    }
}
