<?php

function _updateModuleRepository_%MODULE_ID%()
{
    global $objDatabase;

    $arrModuleRepositoryPages = array(/*REPOSITORY_ARRAY*/);

    $query = "DELETE FROM ".DBPREFIX."module_repository WHERE `moduleid`=%MODULE_ID%";
    if ($objDatabase->Execute($query) === false) {
        return _databaseError($query, $objDatabase->ErrorMsg());
    }

    $arrPageId = array();
    foreach ($arrModuleRepositoryPages as $arrPage) {
        $arrPage['query'] = str_replace(
            '[[PKG_MODULE_REPOSITORY_PAGE_PARID]]',
            (empty($arrPageId[$arrPage['parid']])
              ? 0 : $arrPageId[$arrPage['parid']]
            ),
            $arrPage['query']
        );

        if ($objDatabase->Execute($arrPage['query']) === false) {
            return _databaseError($arrPage['query'], $objDatabase->ErrorMsg());
        }
        $arrPageId[$arrPage['id']] = $objDatabase->Insert_ID();
    }
    return true;
}

function _updateModulePages() {
    global $objUpdate, $_CONFIG, $objDatabase;
    
    $updateTable = array(
        'newsletter'    => '3.0.1.0', // E-Mail Marketing
        'shop'          => '3.0.0.0', // Online Shop
        'voting'        => '2.1.0.0', // Umfragen
        'access'        => '2.0.0.0', // Benutzerverwaltung
        'podcast'       => '2.0.0.0', // Podcast
        'login'         => '3.0.2.0', // Login
        'gallery'       => '3.0.2.0', // Bildgalerie
    );
    
    foreach ($updateTable as $module=>$version) {
        // only update templates if the installed version is older than $version
        if (!$objUpdate->_isNewerVersion($_CONFIG['coreCmsVersion'], $version)) {
            continue;
        }
        $em = \Env::get('em');
        $pageRepo = $em->getRepository('\Cx\Core\ContentManager\Model\Doctrine\Entity\Page');
        $pages = $pageRepo->findBy(array(
            'module' => $module,
            'type'   => \Cx\Core\ContentManager\Model\Doctrine\Entity\Page::TYPE_APPLICATION,
        ));
        $objResult = $objDatabase->Execute('
            SELECT
                `id`
            FROM
                ' . DBPREFIX . 'modules
            WHERE
                `name` LIKE \'' . $module . '\'
        ');
        if ($objResult) {
            if (!$objResult->EOF) {
                $moduleId = $objResult->fields['id'];
            }
        } else {
            return false;
        }
        foreach ($pages as $page) {
            $query = '
                SELECT
                    `content`
                FROM
                    ' . DBPREFIX . 'module_repository
                WHERE
                    `moduleid` = ' . $moduleId . ' AND
                    `cmd` LIKE \'' . $page->getCmd() . '\'
            ';
            $objResult = $objDatabase->Execute($query);
            if (!$objResult || $objResult->EOF) {
                return false;
            }
            $page->setContent($objResult->fields['content']);
            $em->persist($page);
            $em->flush();
        }
    }
    return true;
}
?>