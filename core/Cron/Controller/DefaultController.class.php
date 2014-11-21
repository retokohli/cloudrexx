<?php

/**
 * DefaultController
 *
 * @copyright   Comvation AG
 * @author      Project Team SS4U <info@comvation.com>
 * @package     contrexx
 * @subpackage  core_cron
 */

namespace Cx\Core\Cron\Controller;

/**
 * 
 * DefaultController for Scheduled Tasks to list entities of Job using ViewGenerator
 *
 * @copyright   Comvation AG
 * @author      Project Team SS4U <info@comvation.com>
 * @package     contrexx
 * @subpackage  core_cron
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
     * @var \Cx\Core\Cron\Model\Repository\JobRepository $jobRepository
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
     * @param string                                               $submenu                   the submenu name
     */
    public function __construct(\Cx\Core\Core\Model\Entity\SystemComponentController $systemComponentController, \Cx\Core\Core\Controller\Cx $cx, \Cx\Core\Html\Sigma $template, $submenu = null) {
        parent::__construct($systemComponentController, $cx);
        
        $this->template          = $template;
        $this->em                = $this->cx->getDb()->getEntityManager();
        $this->jobRepository     = $this->em->getRepository('Cx\Core\Cron\Model\Entity\Job');
        
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
            $cronJob = new \Cx\Core\Cron\Model\Entity\Job();
        }
        $view = new \Cx\Core\Html\Controller\ViewGenerator($cronJob, array(
            'header'    => $_ARRAYLANG['TXT_CORE_CRON_ACT_DEFAULT'],
            'functions' => array(
                'add'       => true,
                'edit'      => true,
                'delete'    => true,
                'sorting'   => true,
                'paging'    => true,
                'filtering' => false,
            ),
            
        ));
        $this->template->setVariable('CRON_CONTENT', $view->render());
    }
}
