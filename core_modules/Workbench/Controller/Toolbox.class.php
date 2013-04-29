<?php

namespace Cx\Core_Modules\Workbench\Controller;

class Toolbox {
    protected $template = null;
    
    public function __construct(&$language, $mode, &$arguments) {
        $this->template = new \Cx\Core\Html\Sigma(ASCMS_CORE_MODULE_PATH . '/Workbench/View/Template');
        $this->template->loadTemplateFile('Toolbox.html');
        $res = \Env::get('db')->Execute('SHOW TABLES');
        while (!$res->EOF) {
            $this->template->setVariable('TABLE', current($res->fields));
            $this->template->parse('table_option');
            $res->MoveNext();
        }
        //if (mode = table) {
        $result = $this->loadSql($arguments['table']);
        //} else {
        //$result = sql
        //}
        $result = $this->sql2Yaml($result);
        $this->template->setVariable(array(
            'TXT_WORKBENCH_TOOLBOX_YAML_FROM_TABLE' => $language['TXT_WORKBENCH_TOOLBOX_YAML_FROM_TABLE'],
            'TXT_WORKBENCH_TOOLBOX_YAML_FROM_SQL' => $language['TXT_WORKBENCH_TOOLBOX_YAML_FROM_SQL'],
            'TXT_WORKBENCH_TOOLBOX_SUBMIT' => $language['TXT_WORKBENCH_TOOLBOX_SUBMIT'],
            'RESULT' => $result,
        ));
    }
    
    protected function loadSql($table) {
        $res = \Env::get('db')->Execute('SHOW CREATE TABLE `' . $table . '`');
        return $res->fields['Create Table'];
    }
    
    protected function sql2Yaml($sql) {
        \DBG::activate(DBG_PHP);
        $em = \Env::get('em');
        $em->getConfiguration()->setMetadataDriverImpl(
    new \Doctrine\ORM\Mapping\Driver\DatabaseDriver(
        $em->getConnection()->getSchemaManager()
    )
);

$cmf = new \Doctrine\ORM\Tools\DisconnectedClassMetadataFactory();
$cmf->setEntityManager($em);
return;
$metadata = $cmf->getMetadataFor('ContrexxContentPage');
//$metadata = $cmf->getAllMetadata();
$exporter = $cme->getExporter('yml', '/path/to/export/yml');
$exporter->setMetadata($metadata);
return $exporter->export();
    }
    
    public function __toString() {
        return $this->template->get();
    }
}
