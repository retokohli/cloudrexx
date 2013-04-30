<?php

namespace Cx\Core_Modules\Workbench\Controller;

class Toolbox {
    protected $template = null;
    
    public function __construct(&$language, $mode, &$arguments) {
        $this->template = new \Cx\Core\Html\Sigma(ASCMS_CORE_MODULE_PATH . '/Workbench/View/Template');
        switch ($mode) {
            case 'yaml':
                $this->template->loadTemplateFile('Yaml.html');
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
                break;
                $this->template->setVariable(array(
                    'TXT_WORKBENCH_TOOLBOX_YAML_FROM_TABLE' => $language['TXT_WORKBENCH_TOOLBOX_YAML_FROM_TABLE'],
                    'TXT_WORKBENCH_TOOLBOX_YAML_FROM_SQL' => $language['TXT_WORKBENCH_TOOLBOX_YAML_FROM_SQL'],
                    'TXT_WORKBENCH_TOOLBOX_SUBMIT' => $language['TXT_WORKBENCH_TOOLBOX_SUBMIT'],
                    'RESULT' => $result,
                ));
            case 'components':
                $this->template->loadTemplateFile('Components.html');
                $query = '
                    SELECT
                        `id`,
                        `name`,
                        `is_required`,
                        `is_core`
                    FROM
                        `' . DBPREFIX . 'modules`
                    ORDER BY
                        `name` ASC
                ';
                $res = \Env::get('db')->Execute($query);
                $modules = array();
                while (!$res->EOF) {
                    $modules[] = array(
                        'id' => $res->fields['id'],
                        'name' => $res->fields['name'],
                        'type' => ($res->fields['is_required'] && $res->fields['is_core'] ? 'core' : ($res->fields['is_core'] ? 'core_module' : 'module')),
                        'exists_db' => 'true',
                        'exists_filesystem' => $this->componentExistsInFileSystem(
                            $res->fields['is_required'],
                            $res->fields['is_core'],
                            $res->fields['name']
                        ),
                        'style' => $this->getComponentStyle(
                            $res->fields['is_required'],
                            $res->fields['is_core'],
                            $res->fields['name']
                        ),
                    );
                    $res->MoveNext();
                }
                $table = new \BackendTable(new \Cx\Core_Modules\Listing\Model\Entity\DataSet($modules));
                $this->template->setVariable(array(
                    'RESULT' => $table->toHtml(),
                ));
                break;
        }
    }
    
    protected function componentExistsInFileSystem($required, $core, $name) {
        $path = ASCMS_MODULE_FOLDER;
        if ($required && $core) {
            $path = ASCMS_CORE_FOLDER;
        } else if ($core) {
            $path = ASCMS_CORE_MODULE_FOLDER;
        }
        $path .= '/' . $name;
        if (is_dir(ASCMS_CUSTOMIZING_PATH . $path)) {
            return 'customizing';
        } else if (is_dir(ASCMS_DOCUMENT_ROOT . $path)) {
            return 'true';
        }
        return 'false';
    }
    
    protected function getComponentStyle($required, $core, $name) {
        if ($this->componentExists($name)) {
            return '3.1';
        }
        if ($this->componentExistsInFileSystem($required, $core, $name) !== 'false') {
            if (preg_match('/[A-Z]/', $name)) {
                return '3.0';
            }
        }
        return '<= 2.2.6';
    }
    
    protected function componentExists($name) {
        $componentRepo = \Env::get('em')->getRepository('Cx\Core\Component\Model\Entity\SystemComponent');
        return (bool) $componentRepo->findOneBy(array('name' => $name));
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
