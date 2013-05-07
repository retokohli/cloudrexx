<?php

namespace Cx\Core_Modules\Workbench\Model\Entity;

class ActivateCommand extends Command {
    protected $name = 'activate';
    protected $description = 'Activates a component (core, core_module, lib, module)';
    protected $synopsis = 'workbench(.bat) activate [core|core_module|lib|module] {component_name}';
    protected $help = 'Activates a component of the specified type named {component_name}.';
    
    public function execute(array $arguments) {
        $isCore = $arguments[2] == 'core' || $arguments[2] == 'core_module';
        $isRequired = $arguments[2] == 'core';
        // TODO: check filesystem
        $query = '
            SELECT
                `id`,
                `is_active`
            FROM
                `' . DBPREFIX . 'modules`
            WHERE
                `is_required` = ' . (int) $isRequired . ' AND
                `is_core` = ' . (int) $isCore . ' AND
                `name` = \'' . $arguments[3] . '\'
        ';
        $res = $this->interface->getDb()->getAdoDb()->Execute($query);
        $moduleExists = (bool) $res->RecordCount();
        $id = $res->fields['id'];
        $moduleActive = $res->fields['is_active'] == 1;
        if (!$moduleExists) {
            $res = $this->interface->getDb()->getAdoDb()->Execute('SELECT MAX(`id`) AS `max_id` FROM `' . DBPREFIX . 'modules`');
            $id = $res->fields['max_id'];
            if ($id < 900) {
                $id = 900;
            }
            $id = $this->interface->input('New module ID', $id);
            $defaultDistributor = $this->interface->getConfigVar('distributor');
            $distributor = $this->interface->input('Distributed by', $defaultDistributor);
            if ($distributor != $defaultDistributor) {
                if ($this->interface->yesNo('Do you want to save this distributor name as default?')) {
                    $this->interface->setConfigVar('distributor', $distributor);
                }
            }
            $descriptionVar = 'TXT_' . strtoupper($arguments[3]) . '_MODULE_DESCRIPTION';
            $query = '
                INSERT INTO
                    `' . DBPREFIX . 'modules`
                    (
                        `id`,
                        `name`,
                        `distributor`,
                        `description_variable`,
                        `status`,
                        `is_required`,
                        `is_core`,
                        `is_active`
                    )
                VALUES
                    (
                        ' . $id . ',
                        \'' . $arguments[3] . '\',
                        \'' . $distributor . '\',
                        \'' . $descriptionVar . '\',
                        \'y\',
                        ' . (int) $isRequired . ',
                        ' . (int) $isCore . ',
                        1
                    )
            ';
            if (!$this->interface->getDb()->getAdoDb()->Execute($query)) {
                throw new CommandException('Component activation failed!');
            }
            $moduleActive = true;
        }
        if (!$moduleActive) {
            $query = '
                UPDATE
                    `' . DBPREFIX . 'modules`
                SET
                    `is_active` = 1
                WHERE
                    `id` = ' . $id . '
            ';
            if (!$this->interface->getDb()->getAdoDb()->Execute($query)) {
                throw new CommandException('Component activation failed!');
            }
        }
        // check if backend_areas entry exists
        // if not: add backend_areas entry
        $this->interface->show('TODO: Generate backend areas entry for this module!');
        if ($isCore) {
            $this->interface->show('done');
            return;
        }
        // create page for module
        $this->interface->show('TODO: Generate content page for this module!');
        $this->interface->show('Done');
    }
}
