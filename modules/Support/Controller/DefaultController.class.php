<?php

/**
 * DefaultController
 *
 * @copyright   Comvation AG
 * @author      Project Team SS4U <info@comvation.com>
 * @package     contrexx
 * @subpackage  module_support
 */

namespace Cx\Modules\Support\Controller;

/**
 * 
 * DefaultController for support.
 *
 * @copyright   Comvation AG
 * @author      Project Team SS4U <info@comvation.com>
 * @package     contrexx
 * @subpackage  module_support
 */
class DefaultController extends \Cx\Core\Core\Model\Entity\Controller {
    
    /**
     * Em instance
     * @var \Doctrine\ORM\EntityManager em
     */
    protected $em;
    
    /**
     * Sigma template instance
     * @var Cx\Core\Html\Sigma  $template
     */
    protected $template;
    
    /**
     * module name
     * @var string $moduleName
     */
    public $moduleName = 'Support';
    
    /**
     * module name for language placeholder
     * @var string $moduleNameLang
     */
    public $moduleNameLang = 'SUPPORT';

    /**
     * Controller for the Backend Orders views
     * 
     * @param \Cx\Core\Core\Model\Entity\SystemComponentController $systemComponentController the system component controller object
     * @param \Cx\Core\Core\Controller\Cx                          $cx                        the cx object
     * @param \Cx\Core\Html\Sigma                                  $template                  the template object
     * @param string                                               $submenu                   the submenu name
     */
    public function __construct(\Cx\Core\Core\Model\Entity\SystemComponentController $systemComponentController, \Cx\Core\Core\Controller\Cx $cx, \Cx\Core\Html\Sigma $template, $submenu = null) {
        parent::__construct($systemComponentController, $cx);
        
        $this->template          = $template;
        $this->em                = $this->cx->getDb()->getEntityManager();
        
        $this->showFeedBackForm();
    }

    /**
     * FeedBack Form
     * 
     * @global array $_ARRAYLANG
     */
    public function showFeedBackForm() 
    {
        global $_ARRAYLANG;
        
        //feed back types
        $feedBackTypes = array(
            $_ARRAYLANG['TXT_SUPPORT_FEEDBACK_SELECT_FEEDBACK'],
            $_ARRAYLANG['TXT_SUPPORT_FEEDBACK_BUG_REPORT'],
            $_ARRAYLANG['TXT_SUPPORT_FEEDBACK_FEATURE_REQUEST'],
            $_ARRAYLANG['TXT_SUPPORT_FEEDBACK_HAVE_QUESTION']
        );
        \Cx\Core\Setting\Controller\Setting::init('Support', 'setup','Yaml');
        $faqUrl = \Cx\Core\Setting\Controller\Setting::getValue('faqUrl');
        $recipientMailAddress = \Cx\Core\Setting\Controller\Setting::getValue('recipientMailAddress');
        $faqLink = '<a target="_blank" title="click to FAQ page" href='.$faqUrl.'>'.$_ARRAYLANG['TXT_SUPPORT_FEEDBACK_FAQ'].'</a>';
        
        //get the input datas
        $feedBackType    = isset($_POST['feedBackType']) ? contrexx_input2raw($_POST['feedBackType']) : '';
        $feedBackSubject = isset($_POST['feedBackSubject']) ? contrexx_input2raw($_POST['feedBackSubject']) : '';
        $feedBackComment = isset($_POST['feedBackComment']) ? contrexx_input2raw($_POST['feedBackComment']) : '';
        $customerName    = isset($_POST['customerName']) ? contrexx_input2raw($_POST['customerName']) : '';
        $customerEmailId = isset($_POST['customerEmailId']) ? contrexx_input2raw($_POST['customerEmailId']) : '';
        $feedBackUrl     = isset($_POST['feedBackUrl']) ? contrexx_input2raw($_POST['feedBackUrl']) : '';
        
        if (isset($_POST['sendAndSave'])) {
            if (!empty($feedBackSubject) && !empty($feedBackComment)) {
                $arrFields = array (
                    'key'          => 'notifyAboutNewSupportFeedBack',
                    'section'      => $this->moduleName,
                    'lang_id'      => 1,
                    'substitution' => array(
                        'TXT_USER_CONTACT'      => $_ARRAYLANG['TXT_SUPPORT_CONTACT_TITLE'],
                        'TXT_USER_NAME'         => $_ARRAYLANG['TXT_SUPPORT_USER_NAME'],
                        'TXT_USER_EMAIL'        => $_ARRAYLANG['TXT_SUPPORT_USER_EMAIL'],
                        'TXT_FEEDBACK_MAIL'     => $_ARRAYLANG['TXT_SUPPORT_FEEDBACK_MAIL'],
                        'TXT_FEEDBACK_TOPIC'    => $_ARRAYLANG['TXT_SUPPORT_FEEDBACK_TOPIC'],
                        'TXT_FEEDBACK_URL'      => $_ARRAYLANG['TXT_SUPPORT_FEEDBACK_URL'],
                        'TXT_FEEDBACK_COMMENT'  => $_ARRAYLANG['TXT_SUPPORT_FEEDBACK_COMMENTS'],
                        'USER_NAME'             => contrexx_raw2xhtml($customerName),
                        'USER_EMAIL'            => contrexx_raw2xhtml($customerEmailId),
                        'FEEDBACK_TYPE'         => $feedBackType != 0 ? contrexx_raw2xhtml($feedBackTypes[$feedBackType]) : '',
                        'FEEDBACK_URL'          => contrexx_raw2xhtml($feedBackUrl),
                        'FEEDBACK_COMMENT'      => contrexx_raw2xhtml($feedBackComment),
                        'FEEDBACK_FROM_EMAIL'   => contrexx_raw2xhtml($customerEmailId),
                        'FEEDBACK_SENDER_NAME'  => contrexx_raw2xhtml($customerName),
                        'FEEDBACK_SUBJECT'      => contrexx_raw2xhtml($feedBackSubject),
                        'FEEDBACK_TO_EMAIL'     => 'info@comvation.com'
                    )
                );
                if (\Cx\Core\MailTemplate\Controller\MailTemplate::send($arrFields)) {
                    $objFeedBack = new \Cx\Modules\Support\Model\Entity\FeedBack();
                    $objFeedBack->setFeedBackType($feedBackType);
                    $objFeedBack->setSubject($feedBackSubject);
                    $objFeedBack->setComment($feedBackComment);
                    $objFeedBack->setName($customerName);
                    $objFeedBack->setEmail($customerEmailId);
                    $objFeedBack->setUrl($feedBackUrl);
                    
                    \Env::get('em')->persist($objFeedBack);
                    \Env::get('em')->flush();
                    \Message::ok($_ARRAYLANG['TXT_SUPPORT_FEEDBACK_EMAIL_SEND_SUCESSFULLY']);
                } else {
                    \Message::error($_ARRAYLANG['TXT_SUPPORT_FEEDBACK_EMAIL_SEND_FAILED']);
                }
            } else {
                \Message::error($_ARRAYLANG['TXT_SUPPORT_ERROR_MSG_FIELDS_EMPTY']);
                $this->template->setVariable(array(
                    'TXT_SUPPORT_ERROR_CLASS_SUBJECT' => !empty($feedBackSubject) ? "" : "errBoxStyle",
                    'TXT_SUPPORT_ERROR_CLASS_COMMENT' => !empty($feedBackComment) ? "" : "errBoxStyle",
                    'SUPPORT_FEEDBACK_SUBJECT' => contrexx_raw2xhtml($feedBackSubject),
                    'SUPPORT_FEEDBACK_COMMENT' => contrexx_raw2xhtml($feedBackComment),
                ));
            }
        }
        //show FeedBack Types
        foreach ($feedBackTypes AS $key => $feedbackType) {
             $this->template->setVariable(array(
                 'SUPPORT_FEEDBACK_TYPES'          => $feedbackType,
                 'SUPPORT_FEEDBACK_SELECTED_TYPE'  => (!empty($feedBackType) && $feedBackType == $key) ? 'selected' : '',
                 'SUPPORT_FEEDBACK_ID'             => $key
             ));
            $this->template->parse('showFeedBackTypes');
        }
        
        $objUser = \FWUser::getFWUserObject();
        $this->template->setVariable(array(
            'SUPPORT_FEEDBACK_FAQ'                  => $faqLink,
            'SUPPORT_FEEDBACK_CUSTOMER_NAME'        => $objUser->objUser->getUsername(),
            'SUPPORT_FEEDBACK_CUSTOMER_EMAIL'       => $objUser->objUser->getEmail(),
            'SUPPORT_FEEDBACK_URL'                  => $recipientMailAddress
        ));
        
        $this->template->setVariable(array(
            'TXT_SUPPORT_FEEDBACK'            => $_ARRAYLANG['TXT_SUPPORT_FEEDBACK'],
            'TXT_SUPPORT_FEEDBACK_SUBJECT'    => $_ARRAYLANG['TXT_SUPPORT_FEEDBACK_SUBJECT'],
            'TXT_SUPPORT_FEEDBACK_COMMENTS'   => $_ARRAYLANG['TXT_SUPPORT_FEEDBACK_COMMENTS']
        ));
    }
    
}
