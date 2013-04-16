<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */
namespace Cx\Core_Modules\Workbench\Controller;
/**
 * Description of Workbench
 *
 * @author ritt0r
 */
class WorkbenchException extends \Exception {}

class Workbench {
    private $config = null;
    
    /**
     * Returns a list of files (directories include all contained files and folders)
     * @return array 
     */
    public function getFileList() {
        return array(
            '/core_modules/Workbench',
            '/workbench.config',
            '/workbench.sh',
            '/workbench.bat',
            '/testing',
            '/model/doctrine.php',
            '/model/doctrine',
        );
    }
    
    /**
     *
     * @param type $objTemplate
     * @param type $post
     * @return type
     * @throws \Exception 
     * @todo YAML assistant
     * @todo Cx/Module sandbox
     * @todo Language var checker (/translation helper)
     * @todo Component analysis (/testing)
     */
    public function getPage($objTemplate, $post) {
        // this is the code for the DQL SANDBOX. This should be moved to a new SanboxController or something similiar
        $objTemplate->setVariable('CONTENT_NAVIGATION', '<a href="index.php?cmd=workbench" class="active">DQL</a>');
        $dql = 'SELECT p FROM Cx\Core\ContentManager\Model\Doctrine\Entity\Page p WHERE p.id < 10';
        $output = '';
        if (!empty($post['dql'])) {
            $dql = $post['dql'];
        }
        if (isset($post['dql']) && trim($dql) != '') {
            $strQuery = trim($post['dql']);
            $lister = new \Cx\Core_Modules\Listing\Controller\ListingController(
                function(&$offset, &$count, &$criteria, &$order) use ($strQuery) {
                    // @todo: add statements for offset, count, crit and order
                    $query = \Env::get('em')->createQuery($strQuery);
                    $result = $query->getResult();
                    if (!$result) {
                        throw new \Exception('Empty result');
                    }
                    return new \Cx\Core_Modules\Listing\Model\DataSet($result);
                }
            );
            try {
                $output = (new \BackendTable($lister->getData()))->toHtml().$lister;
            } catch (\Exception $e) {
                $output = 'Could not execute query (' . $e->getMessage() . ')!';
            }
        }
        $objTemplate->setVariable('ADMIN_CONTENT', '<form method="post"><textarea name="dql" rows="10" cols="70">' . $dql . '</textarea><br /><input type="submit" /></form><hr />'.$output);
    }
    
    public function getConfigEntry($identifier) {
        if (!$this->config) {
            $this->loadConfig();
        }
        if (!isset($this->config[$identifier])) {
            return '';
        }
        return $this->config[$identifier];
    }
    
    public function __destruct() {
        $this->writeConfig();
    }
    
    private function loadConfig() {
        $content = file_get_contents(ASCMS_DOCUMENT_ROOT.'/workbench.config');
        $content = explode("\n", $content);
        $this->config = array();
        foreach ($content as $line) {
            $line = explode('=', $line, 2);
            if (count($line) != 2) {
                continue;
            }
            $this->config[trim($line[0])] = trim($line[1]);
        }
    }
    
    private function writeConfig() {
        $content = '';
        foreach ($this->config as $key=>$value) {
            $content .= $key.'='.$value."\r\n";
        }
        file_put_contents(ASCMS_DOCUMENT_ROOT.'/workbench.config', $content);
    }
}
