<?php

//error_reporting(E_ALL);
//ini_set('display_errors', 1);

class Catalog
{
    public $langId;
    public $objTpl;
    public $statusMessage;
    public $arrSettings = array();


    /**
     * Constructor
     * @param  string   $pageContent
     * @global integer
     * @access public
     */
    function __construct($pageContent)
    {
        global $_LANGID;

        $this->pageContent = $pageContent;
        $this->langId = $_LANGID;
        $this->objTpl = new HTML_Template_Sigma('.');
        $this->objTpl->setErrorHandling(PEAR_ERROR_DIE);
    }


    /**
     * Gets the page
     */
    function getPage()
    {
        $this->overview();
        return $this->objTpl->get();
    }


    /**
     * Gets the guestbook status
     *
     * @global  ADONewConnection
     * @global  array
     * @global  array
     * @access private
     */

    function overview()
    {
        global $objDatabase;

        $this->objTpl->setTemplate($this->pageContent);

        $_POST['send'] = (empty($_POST['send']) ? '' : $_POST['send']);
        if ($_POST['send']) {
            $this->objTpl->setVariable(array('CATALOG_MESSAGE' => "Ihre Bestellung wurde versandt."));
            $this->objTpl->hideBlock('catalog_form');
            $this->sendNotificationEmail($_POST['arrCatalog'], $_POST['arrContact']);
        }

        $objResult = $objDatabase->Execute("
            SELECT `id`, `title`, `picture`, `description`
              FROM `".DBPREFIX."module_catalog`
             WHERE `status`='1'
             ORDER BY `sortorder` ASC");

        while (!$objResult->EOF) {
            $picture = $objResult->fields['picture'];
//echo("picture: $picture, path ".ASCMS_PATH.$picture.'.thumb'."<br />");
            if (!file_exists(ASCMS_PATH.$picture.'.thumb')) {
                require_once('thumb.php');
//echo("Creating thumb: ".(
                createThumb($picture);
//? "OK" : "FAIL")."<br />");
            }
            $this->objTpl->setVariable(array(
                'CATALOG_ID'          => $objResult->fields['id'],
                'CATALOG_TITLE'       => $objResult->fields['title'],
                'CATALOG_PICTURE'     => $picture.'.thumb',
                'CATALOG_DESCRIPTION' => $objResult->fields['description'],
            ));
            $this->objTpl->parse('catalog_row');
            $objResult->MoveNext();
        }
    }


    /**
    * @return void
    * @desc Sends a notification email to the administrator
    */
    function sendNotificationEmail($arrCatalog, $arrContact)
    {
        global $_CONFIG;

        foreach ($arrCatalog as $catalog) {
            if (!empty($catalog['quantity'])) {
                $catalogs .= 'Name: '.$catalog['title'].'<br />Anzahl: '.$catalog['quantity'].'<br /><br />';
            }
        }


$mailContent = 'Vielen Dank für Ihr Interesse an unserem Informationsmaterial.<br />
<br />
Folgende Unterlagen werden Ihnen in Kürze zugesandt:<br />
'.$catalogs.'
<br />
Ihre übermitteleten Kontaktdaten:<br />
Sie sind '.$arrContact['iam'].'<br />
'.$arrContact['salutation'].' '.$arrContact['prename'].' '.$arrContact['lastname'].'<br />'
.$arrContact['company'].'<br />'
.$arrContact['street'].'<br />'
.$arrContact['zip']." ".$arrContact['city'].'<br />'
.$arrContact['number'].'<br />'
.$arrContact['fax'].'<br />'
.$arrContact['email'].'<br />
<br />
<br />
Ackerfläche Gesamt: '.$arrContact['ackergesamt'].'<br />
Getreide: '.$arrContact['getreide'].'<br />
Raps: '.$arrContact['raps'].'<br />
Mais: '.$arrContact['mais'].'<br />
Kartoffeln: '.$arrContact['kartoffeln'].'<br />
Sonstiges: '.$arrContact['sonstiges'].'<br />
<br />
Mit freundlichen Grüssen<br />
<br />
Ihr Stähler-Team';
        $subject = "Neue Bestellung";
        if (@include_once ASCMS_LIBRARY_PATH.'/phpmailer/class.phpmailer.php') {
            $objMail = new phpmailer();

            if ($_CONFIG['coreSmtpServer'] > 0 && @include_once ASCMS_CORE_PATH.'/SmtpSettings.class.php') {
                if (($arrSmtp = SmtpSettings::getSmtpAccount($_CONFIG['coreSmtpServer'])) !== false) {
                    $objMail->IsSMTP();
                    $objMail->Host = $arrSmtp['hostname'];
                    $objMail->Port = $arrSmtp['port'];
                    $objMail->SMTPAuth = true;
                    $objMail->Username = $arrSmtp['username'];
                    $objMail->Password = $arrSmtp['password'];
                }
            }
            $email = "mail@phillipwoelfel.de";
            $objMail->CharSet = CONTREXX_CHARSET;
            $objMail->From = $email;
            $objMail->Subject = $subject;
            $objMail->IsHTML(true);
            $objMail->Body = $mailContent;
            $objMail->AddAddress($arrContact['email']);
            $objMail->AddAddress("mail@phillipwoelfel.de");
            if ($objMail->Send()) {
                return true;
            }
        }

        return false;
    }

}

?>
