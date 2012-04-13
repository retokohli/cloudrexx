<?php
/**
 * Module Checker
 * @copyright   CONTREXX CMS - COMVATION AG
 * @author		Comvation Development Team <info@comvation.com>
 * @version		2.0.0
 * @package     contrexx
 * @subpackage  core
 * @todo        Edit PHP DocBlocks!
 */
namespace Cx\Core {
    /**
     * Module Checker Class
     *
     * Checks for activated modules and plugins
     *
     * @copyright   CONTREXX CMS - COMVATION AG
     * @author		Comvation Development Team <info@comvation.com>
     * @access		public
     * @version		2.0.0
     * @package     contrexx
     * @subpackage  core
     */
    class ModuleChecker
    {
        /**
         * A list of all module names
         *
         * @access private
         * @var array
         * @see ModuleChecker::init()
         */
        private $arrModules = array();
        private $arrActiveModulesByName = array();
        private $arrUsedModules = array();
        private $em = null;
        private $db = null;


        public function __construct($em, $db){
            $this->em = $em;
            $this->db = $db;

            $this->init();
        }


        private function init()
        {
            // check the content for installed and used modules
            $arrUsedModules = array();
            $qb = $this->em->createQueryBuilder();
            $qb->add('select', 'p')
                ->add('from', 'Cx\Model\ContentManager\Page p')
                ->add('where',
                    $qb->expr()->andx(
                        $qb->expr()->eq('p.lang', FRONTEND_LANG_ID),
// TODO: what is the proper syntax for non-empty values?
// TODO: add additional check for module != NULL
                        $qb->expr()->neq('p.module', $qb->expr()->literal(''))
                    ));
            $pages = $qb->getQuery()->getResult();
            foreach ($pages as $page) {
                if (!$page->isActive()) {
                    continue;
                }

                $arrUsedModules[] = $page->getModule();
            }

            // add static modules
            array_push($this->arrUsedModules, 'block');
            array_push($this->arrUsedModules, 'upload');

            $this->arrUsedModules = array_unique($arrUsedModules);

            // check the module database tables for required modules
            $objResult = $this->db->Execute('SELECT name,is_core,is_required FROM `'.DBPREFIX.'modules`');
            if ($objResult !== false) {
                while(!$objResult->EOF) {
                    $moduleName = $objResult->fields['name'];

                    if (empty($moduleName)) {
                        $objResult->MoveNext();
                        continue;
                    }

                    $this->arrModules[] = $moduleName;

                    if (   $objResult->fields['is_core']=='1'
                        || $objResult->fields['is_required']=='1'
                        || (   in_array($moduleName, $this->arrUsedModules)
                            && is_dir(ASCMS_MODULE_PATH.'/'.$moduleName))
                    ) {
                        $this->arrActiveModulesByName[] = $moduleName;
                    }

                    $objResult->MoveNext();
                }
            }
        }


        public function isModuleActive($moduleName)
        {
            return in_array($moduleName, $this->arrActiveModulesByName);
        }
    }
}

namespace {
    /**
     * Checks if a certain module, specified by param $moduleName, is active/installed.
     * 
     * @param   string Module name
     * @return boolean  Either TRUE or FALSE, depending if the module in question is
     *                  active/installed or not.
     */
    function contrexx_isModuleActive($moduleName)
    {
        static $objModuleChecker;

        if (!isset($objModuleChecker)) {
            $objModuleChecker = new \Cx\Core\ModuleChecker(\Env::get('em'), \Env::get('db'));
        }

        return $objModuleChecker->isModuleActive($moduleName);
    }
}

