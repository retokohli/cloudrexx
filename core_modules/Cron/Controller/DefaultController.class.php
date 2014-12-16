<?php

/**
 * DefaultController
 *
 * @copyright   Comvation AG
 * @author      Project Team SS4U <info@comvation.com>
 * @package     contrexx
 * @subpackage  coremodule_cron
 */

namespace Cx\Core_Modules\Cron\Controller;

/**
 * 
 * DefaultController for Scheduled Tasks to list entities of Job using ViewGenerator
 *
 * @copyright   Comvation AG
 * @author      Project Team SS4U <info@comvation.com>
 * @package     contrexx
 * @subpackage  coremodule_cron
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
     * JobRepository instance 
     * @var \Cx\Core_Modules\Cron\Model\Repository\JobRepository $jobRepository
     */
    protected $jobRepository;
    
    /**
     * module name
     * @var string $moduleName
     */
    public $moduleName = 'Cron';
    
    /**
     * module name for language placeholder
     * @var string $moduleNameLang
     */
    public $moduleNameLang = 'CRON';

    /**
     * Controller for the Backend Cron jobs  views
     * 
     * @param \Cx\Core\Core\Model\Entity\SystemComponentController $systemComponentController the system component controller object
     * @param \Cx\Core\Core\Controller\Cx                          $cx                        the cx object
     * @param \Cx\Core\Html\Sigma                                  $template                  the template object
     */
    public function __construct(\Cx\Core\Core\Model\Entity\SystemComponentController $systemComponentController, \Cx\Core\Core\Controller\Cx $cx) {
        parent::__construct($systemComponentController, $cx);
        
        $this->em                = $this->cx->getDb()->getEntityManager();
        $this->jobRepository     = $this->em->getRepository('Cx\Core_Modules\Cron\Model\Entity\Job');
    }
    
    /**
     * Use this to parse your backend page
     * 
     * @param \Cx\Core\Html\Sigma $template 
     */
    public function parsePage(\Cx\Core\Html\Sigma $template) {
        $this->template = $template;
        
        $this->showCronJobs();
    }
    /**
     * Displaying entities of job using ViewGenerator.
     * 
     * @global type $_ARRAYLANG
     */
    public function showCronJobs() 
    {
        global $_ARRAYLANG;
        
        $cronJob = $this->jobRepository->findAll();
        if (empty($cronJob)) {
            $cronJob = new \Cx\Core_Modules\Cron\Model\Entity\Job();
        }
        $view = new \Cx\Core\Html\Controller\ViewGenerator($cronJob, array(
            'header'    => $_ARRAYLANG['TXT_CORE_MODULE_CRON_ACT_DEFAULT'],
            'functions' => array(
                'add'       => true,
                'edit'      => true,
                'delete'    => true,
                'sorting'   => true,
                'paging'    => true,
                'filtering' => false,
            ),
            'fields' => array(
                'id' => array(
                    'showOverview' => false,
                ),
                'active' => array(
                    'header' => $_ARRAYLANG['TXT_CORE_MODULE_CRON_ACTIVE'],
                ),
                'expression' => array(
                    'header' => $_ARRAYLANG['TXT_CORE_MODULE_CRON_EXPRESSION'],
                ),
                'command' => array(
                    'header' => $_ARRAYLANG['TXT_CORE_MODULE_CRON_COMMAND'],
                    'storecallback' => function ($value) {
                        return $value['command'] . ' ' . $value['arguments'];
                    },
                    'formfield' => function ($name, $type, $length, $value, $options) {
                        $field = new \Cx\Core\Html\Model\Entity\HtmlElement('span');
                        $commandSelectOptions = array_keys($this->cx->getCommands());
                        $value = explode(' ', $value, 2);
                        $commandSelect = new \Cx\Core\Html\Model\Entity\DataElement(
                            $name . '[command]',
                            \Html::getOptions(
                                array_combine(
                                    array_values($commandSelectOptions),
                                    array_values($commandSelectOptions)
                                ),
                                isset($value[0]) ? $value[0] : ''
                            ),
                            \Cx\Core\Html\Model\Entity\DataElement::TYPE_SELECT
                        );
                        $commandArguments = new \Cx\Core\Html\Model\Entity\DataElement(
                            $name . '[arguments]',
                            isset($value[1]) ? $value[1] : ''
                        );
                        $field->addChild($commandSelect);
                        $field->addChild($commandArguments);
                        return $field;
                    },
                ),
                'lastRan' => array(
                    'header' => $_ARRAYLANG['TXT_CORE_MODULE_CRON_LAST_RUN'],
                ),
            )
        ));
        $this->template->setVariable('CRON_CONTENT', $view->render());
    }
}
