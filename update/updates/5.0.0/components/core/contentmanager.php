<?php

/**
 * Cloudrexx
 *
 * @link      http://www.cloudrexx.com
 * @copyright Cloudrexx AG 2007-2015
 *
 * According to our dual licensing model, this program can be used either
 * under the terms of the GNU Affero General Public License, version 3,
 * or under a proprietary license.
 *
 * The texts of the GNU Affero General Public License with an additional
 * permission and of our proprietary license can be found at and
 * in the LICENSE file you have received along with this program.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU Affero General Public License for more details.
 *
 * "Cloudrexx" is a registered trademark of Cloudrexx AG.
 * The licensing of the program under the AGPLv3 does not imply a
 * trademark license. Therefore any rights, title and interest in
 * our trademarks remain entirely with us.
 */

class ContentManagerUpdate {
    public static function updateContentManagerDbStructure() {
        global $objUpdate, $_CONFIG;

        // fix customContent
        if (   !$objUpdate->_isNewerVersion($_CONFIG['coreCmsVersion'], '3.0.0')
            && $objUpdate->_isNewerVersion($_CONFIG['coreCmsVersion'], '3.0.1')
        ) {
            try {
                \Cx\Lib\UpdateUtil::sql('UPDATE `'.DBPREFIX.'content_page` SET `customContent` = \'\' WHERE `customContent` = \'(Default)\'');
            } catch (\Cx\Lib\UpdateException $e) {
                return \Cx\Lib\UpdateUtil::DefaultActionHandler($e);
            }
        }

        // verify & fix DB structrue
        if ($objUpdate->_isNewerVersion($_CONFIG['coreCmsVersion'], '5.0.0')) {
            try {
                \Cx\Lib\UpdateUtil::sql('
                    ALTER TABLE `' . DBPREFIX . 'content_node` ENGINE = INNODB
                ');

                \Cx\Lib\UpdateUtil::table(
                    DBPREFIX . 'content_page',
                    array(
                        'id'                                => array('type' => 'INT(11)', 'notnull' => true, 'auto_increment' => true, 'primary' => true),
                        'node_id'                           => array('type' => 'INT(11)', 'notnull' => false, 'after' => 'id'),
                        'nodeIdShadowed'                    => array('type' => 'INT(11)', 'notnull' => false, 'after' => 'node_id'),
                        'lang'                              => array('type' => 'INT(11)', 'after' => 'nodeIdShadowed'),
                        'type'                              => array('type' => 'VARCHAR(16)', 'after' => 'lang'),
                        'caching'                           => array('type' => 'TINYINT(1)', 'after' => 'type'),
                        'updatedAt'                         => array('type' => 'timestamp', 'after' => 'caching', 'notnull' => false),
                        'updatedBy'                         => array('type' => 'CHAR(40)', 'after' => 'updatedAt'),
                        'title'                             => array('type' => 'VARCHAR(255)', 'after' => 'updatedBy'),
                        'linkTarget'                        => array('type' => 'VARCHAR(16)', 'notnull' => false, 'after' => 'title'),
                        'contentTitle'                      => array('type' => 'VARCHAR(255)', 'after' => 'linkTarget'),
                        'slug'                              => array('type' => 'VARCHAR(255)', 'after' => 'contentTitle'),
                        'content'                           => array('type' => 'longtext', 'after' => 'slug'),
                        'sourceMode'                        => array('type' => 'TINYINT(1)', 'notnull' => true, 'default' => '0', 'after' => 'content'),
                        'customContent'                     => array('type' => 'VARCHAR(64)', 'notnull' => false, 'after' => 'sourceMode'),
                        'useCustomContentForAllChannels'    => array('type' => 'INT(2)', 'notnull' => false, 'after' => 'customContent'),
                        'applicationTemplate'               => array('type' => 'VARCHAR(100)', 'notnull' => false, 'after' => 'useCustomContentForAllChannels'),
                        'useCustomApplicationTemplateForAllChannels' => array('type' => 'TINYINT(2)', 'notnull' => false, 'after' => 'applicationTemplate'),
                        'cssName'                           => array('type' => 'VARCHAR(255)', 'notnull' => false, 'after' => 'useCustomApplicationTemplateForAllChannels'),
                        'cssNavName'                        => array('type' => 'VARCHAR(255)', 'notnull' => false, 'after' => 'cssName'),
                        'skin'                              => array('type' => 'INT(11)', 'notnull' => false, 'after' => 'cssNavName'),
                        'useSkinForAllChannels'             => array('type' => 'INT(2)', 'notnull' => false, 'after' => 'skin'),
                        'metatitle'                         => array('type' => 'VARCHAR(255)', 'notnull' => false, 'after' => 'useSkinForAllChannels'),
                        'metadesc'                          => array('type' => 'text', 'after' => 'metatitle'),
                        'metakeys'                          => array('type' => 'text', 'after' => 'metadesc'),
                        'metarobots'                        => array('type' => 'VARCHAR(7)', 'notnull' => false, 'after' => 'metakeys'),
                        'start'                             => array('type' => 'timestamp', 'notnull' => false, 'after' => 'metarobots'),
                        'end'                               => array('type' => 'timestamp', 'notnull' => false, 'after' => 'start'),
                        'editingStatus'                     => array('type' => 'VARCHAR(16)', 'after' => 'end'),
                        'protection'                        => array('type' => 'INT(11)', 'after' => 'editingStatus'),
                        'frontendAccessId'                  => array('type' => 'INT(11)', 'after' => 'protection'),
                        'backendAccessId'                   => array('type' => 'INT(11)', 'after' => 'frontendAccessId'),
                        'display'                           => array('type' => 'TINYINT(1)', 'after' => 'backendAccessId'),
                        'active'                            => array('type' => 'TINYINT(1)', 'after' => 'display'),
                        'target'                            => array('type' => 'VARCHAR(255)', 'notnull' => false, 'after' => 'active'),
                        'module'                            => array('type' => 'VARCHAR(255)', 'notnull' => false, 'after' => 'target'),
                        'cmd'                               => array('type' => 'VARCHAR(50)', 'notnull' => true, 'default' => '', 'after' => 'module'),
                    ),
                    array(
                        'node_id'              => array('fields' => array('node_id', 'lang'), 'type' => 'UNIQUE'),
                        'IDX_D8E86F54460D9FD7' => array('fields' => array('node_id'))
                    ),
                    'InnoDB',
                    '',
                    array(
                        'node_id' => array(
                            'table'    => DBPREFIX . 'content_node',
                            'column'   => 'id',
                            'onDelete' => 'SET NULL',
                            'onUpdate' => 'NO ACTION',
                        ),
                    )
                );

                \Cx\Lib\UpdateUtil::table(
                    DBPREFIX . 'content_node',
                    array(
                        'id'                                 => array('type' => 'INT(11)', 'notnull' => true, 'auto_increment' => true, 'primary' => true),
                        'parent_id'                          => array('type' => 'INT(11)', 'notnull' => false, 'after' => 'id'),
                        'lft'                                => array('type' => 'INT(11)', 'after' => 'parent_id'),
                        'rgt'                                => array('type' => 'INT(11)', 'after' => 'lft'),
                        'lvl'                                => array('type' => 'INT(11)', 'after' => 'rgt')
                    ),
                    array(
                        'IDX_E5A18FDD727ACA70'               => array('fields' => array('parent_id'))
                    ),
                    'InnoDB',
                    '',
                    array(
                        'parent_id' => array(
                            'table' => DBPREFIX.'content_node',
                            'column'    => 'id',
                            'onDelete'  => 'NO ACTION',
                            'onUpdate'  => 'NO ACTION',
                        ),
                    )
                );
            } catch (\Cx\Lib\UpdateException $e) {
                return \Cx\Lib\UpdateUtil::DefaultActionHandler($e);
            }
        }

        return true;
    }

    public static function fixPageLogs() {
        global $objUpdate, $_CONFIG;

        try {
            $userData = json_encode(array(
                'id'   => $_SESSION['contrexx_update']['user_id'],
                'name' => $_SESSION['contrexx_update']['username'],
            ));


            // migrate content logs (3.0 - 3.1.1)
            if (   !$objUpdate->_isNewerVersion($_CONFIG['coreCmsVersion'], '3.0.0')
                && $objUpdate->_isNewerVersion($_CONFIG['coreCmsVersion'], '3.2.0')
            ) {
                \Cx\Lib\UpdateUtil::sql('UPDATE `'.DBPREFIX.'log_entry`
                    SET `object_class` = \'Cx\\\\Core\\\\ContentManager\\\\Model\\\\Entity\\\\Page\'
                    WHERE object_class = \'Cx\\\\Model\\\\ContentManager\\\\Page\'');
            }


            // set user on page logs
            if (   !$objUpdate->_isNewerVersion($_CONFIG['coreCmsVersion'], '3.0.0')
                && $objUpdate->_isNewerVersion($_CONFIG['coreCmsVersion'], '3.0.3')
            ) {
                \Cx\Lib\UpdateUtil::sql('UPDATE `' . DBPREFIX . 'log_entry` SET `username` = \'' . $userData . '\' WHERE `username` = \'currently_loggedin_user\'');
            }


            // add missing "remove page" log entries
            \DBG::msg(__METHOD__.': add missing "remove page" log entries');
            $sqlQuery = '
                SELECT
                    MAX(l1.version) as `version`,
                    l1.object_id
                FROM
                    `' . DBPREFIX . 'log_entry` AS l1
                WHERE
                    l1.object_id NOT IN (
                        SELECT
                            id
                        FROM
                            `' . DBPREFIX . 'content_page`
                    )
                    AND l1.object_id NOT IN (
                        SELECT
                            l3.object_id
                        FROM
                            `' . DBPREFIX . 'log_entry` AS l3
                        WHERE
                            l3.action LIKE \'remove\'
                    )
                GROUP BY
                    l1.object_id
            ';
            $result = \Cx\Lib\UpdateUtil::sql($sqlQuery);
            while (!$result->EOF) {
                $sqlQuery = '
                    INSERT INTO
                        `' . DBPREFIX . 'log_entry`
                        (
                            `action`,
                            `logged_at`,
                            `version`,
                            `object_id`,
                            `object_class`,
                            `data`,
                            `username`
                        )
                    VALUES
                        (
                            \'remove\',
                            NOW(),
                            ' . ($result->fields['version'] + 1) . ',
                            ' . $result->fields['object_id'] . ',
                            \'Cx\\\\Core\\\\ContentManager\\\\Model\\\\Doctrine\\\\Entity\\\\Page\',
                            \'N;\',
                            \'' . $userData . '\'
                        )
                ';
                \Cx\Lib\UpdateUtil::sql($sqlQuery);
                $result->MoveNext();
            }

            return true;
        } catch (\Cx\Lib\UpdateException $e) {
            return \Cx\Lib\UpdateUtil::DefaultActionHandler($e);
        }
    }

    // fix fallback pages
    public static function fixFallbackPages() {
        // only version 3 rc1 is affected by this bug
        if (detectCx3Version() != 'rc1') {
            return true;
        }

        try {
            \DBG::msg(__METHOD__);
            $em = \Env::get('em');
            $pageRepo = $em->getRepository('Cx\Core\ContentManager\Model\Entity\Page');

            $fallbackPages = $pageRepo->findBy(array(
                'type' => \Cx\Core\ContentManager\Model\Entity\Page::TYPE_FALLBACK,
            ));

            foreach ($fallbackPages as $page) {
                $page->setModule($page->getModule());
                $page->setCmd($page->getCmd());
                $page->setUpdatedAtToNow();
                $em->persist($page);
            }
            $em->flush();
        } catch (\Exception $e) {
            return \Cx\Lib\UpdateUtil::DefaultActionHandler($e);
        }

        return true;
    }

    public static function fixTree() {
        // fix tree
        \DBG::msg(__METHOD__);
        \Env::get('em')->getRepository('Cx\Core\ContentManager\Model\Entity\Node')->recover();
    }
}
