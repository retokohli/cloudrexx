<?php
/**
 * Alias library
 * @copyright   CONTREXX CMS - COMVATION AG
 * @author        Comvation Development Team <info@comvation.com>
 * @version        1.0.0
 * @package     contrexx
 * @subpackage  core_module_alias
 * @todo        Edit PHP DocBlocks!
 */
/**
 * @ignore
 */
require_once ASCMS_FRAMEWORK_PATH.'/File.class.php';
/**
 * @ignore
 */
require_once ASCMS_FRAMEWORK_PATH.'/FWHtAccess.class.php';

/**
 * Alias library
 * @copyright   CONTREXX CMS - COMVATION AG
 * @author        Comvation Development Team <info@comvation.com>
 * @version        1.0.0
 * @package     contrexx
 * @subpackage  core_module_alias
 * @todo        Edit PHP DocBlocks!
 */
class aliasLib
{
    public $_arrAliasTypes = array(
        'local',
        'url'
    );

    public $langId;

    public $_arrConfig = null;

    public $objFWHtAccess;

    function __construct($langId = 0)
    {
        $this->objFWHtAccess = new FWHtAccess();
        if (($result = $this->objFWHtAccess->loadHtAccessFile('/.htaccess')) !== true) {;
            $this->arrStatusMsg['error'][] = $result;
        }
        $this->langId = intval($langId) > 0 ? $langId : FRONTEND_LANG_ID;
    }

    function _getConfig()
    {
        if (!is_array($this->_arrConfig)) {
            $this->_initConfig();
        }

        return $this->_arrConfig;
    }

    function _initConfig()
    {
        global $objDatabase;

        $objConfig = $objDatabase->Execute('SELECT `setname`, `setvalue` FROM `'.DBPREFIX.'settings` WHERE `setmodule` = 41');
        if ($objConfig !== false) {
            $this->_arrConfig = array();
            while (!$objConfig->EOF) {
                $this->_arrConfig[$objConfig->fields['setname']] = $objConfig->fields['setvalue'];
                $objConfig->MoveNext();
            }
        }
    }

    function _getMappings($limit = null)
    {
        global $objDatabase, $_CONFIG;

        $arrMappings = array();
        $pos = isset($_GET['pos']) ? intval($_GET['pos']) : 0;

        $query = "
            SELECT
               `id`,
               `domain`,
               `target`
            FROM `".DBPREFIX."module_alias_domain_mapping`
            ORDER BY `domain` ASC";
        if (!empty($limit)) {
            $objRS = $objDatabase->SelectLimit($query, $_CONFIG['corePagingLimit'], $pos);
        } else {
            $objRS = $objDatabase->Execute($query);
        }

        if ($objRS !== false) {
            while (!$objRS->EOF) {
                $arrMappings[$objRS->fields['id']] = $objRS->fields;
                $objRS->MoveNext();
            }
        }
        return $arrMappings;
    }

	function _getMapping($mappingId)
    {
        global $objDatabase;
        $objMapping = $objDatabase->Execute("
             SELECT
               `id`,
               `domain`,
               `target`
            FROM `".DBPREFIX."module_alias_domain_mapping`
            WHERE `id` = ".intval($mappingId));
        if ($objMapping && $objMapping->RecordCount()) {
            return $objMapping->fields;
        }
        return false;
    }
    
    
    function _getAliases($limit = null, $allLanguages = false)
    {
        global $objDatabase, $_CONFIG;

        $arrAliases = array();
        $arrLocalAliases = array();
        $pos = isset($_GET['pos']) ? intval($_GET['pos']) : 0;

        $query = "
            SELECT
                t.`id`        AS targetId,
                t.`type`      AS targetType,
                t.`url`       AS targetUrl,
                s.`id`        AS sourceId,
                s.`isdefault` AS isdefault,
                s.`url`       AS sourceUrl,
                s.`lang_id`   AS langId
            FROM `".DBPREFIX."module_alias_target` AS t
            INNER JOIN `".DBPREFIX."module_alias_source` AS s ON s.`target_id` = t.`id`"
            .(!$allLanguages ? "WHERE s.`lang_id` = ".$this->langId : ' ')."
            ORDER BY sourceUrl ASC";

        if (!empty($limit)) {
            $objAlias = $objDatabase->SelectLimit($query, $_CONFIG['corePagingLimit'], $pos);
        } else {
            $objAlias = $objDatabase->Execute($query);
        }

        if ($objAlias !== false) {
            while (!$objAlias->EOF) {
                if (!isset($arrAliases[$objAlias->fields['langId']][$objAlias->fields['targetId']])) {
                    $arrAliases[$objAlias->fields['langId']][$objAlias->fields['targetId']] = array(
                        'type'        => $objAlias->fields['targetType'],
                        'url'        => $objAlias->fields['targetUrl'],
                        'sources'    => array()
                    );

                    if ($objAlias->fields['targetType'] == 'local') {
                        $arrLocalAliases[intval($objAlias->fields['targetUrl'])] = $objAlias->fields['targetId'];
                    }
                }
                array_push($arrAliases[$objAlias->fields['langId']][$objAlias->fields['targetId']]['sources'], array(
                    'id'        => $objAlias->fields['sourceId'],
                    'isdefault' => $objAlias->fields['isdefault'],
                    'url'        => $objAlias->fields['sourceUrl']
                ));

                $objAlias->MoveNext();
            }

            if (count($arrLocalAliases)) {
                $arrLocalAliasIds = array_keys($arrLocalAliases);
                $objAlias = $objDatabase->Execute("
                    SELECT
                        n.`catid`,
                        n.`catname`,
                        n.`cmd`,
                        n.`lang`,
                        m.`name`
                    FROM
                        `".DBPREFIX."content_navigation` AS n
                    LEFT OUTER JOIN `".DBPREFIX."modules` AS m ON m.`id` = n.`module`
                    WHERE (n.`catid` = ".implode(' OR n.`catid` = ', $arrLocalAliasIds).")".(!$allLanguages ? " AND n.`lang` = ".$this->langId : ' ')
                );
                if ($objAlias !== false) {
                    while (!$objAlias->EOF) {
                        if (isset($arrAliases[$objAlias->fields['lang']][$arrLocalAliases[$objAlias->fields['catid']]])) {
                            $arrAliases[$objAlias->fields['lang']][$arrLocalAliases[$objAlias->fields['catid']]]['title'] = $objAlias->fields['catname'];
                            $arrAliases[$objAlias->fields['lang']][$arrLocalAliases[$objAlias->fields['catid']]]['pageUrl'] = ASCMS_PATH_OFFSET
                                .($_CONFIG['useVirtualLanguagePath'] == 'on' ? '/'.FWLanguage::getLanguageParameter($objAlias->fields['lang'], 'lang') : null)
                                .'/'.CONTREXX_DIRECTORY_INDEX
                                .(!empty($objAlias->fields['name']) ? '?section='.$objAlias->fields['name'] : '?page='.$objAlias->fields['catid'])
                                .(empty($objAlias->fields['cmd']) ? '' : '&cmd='.$objAlias->fields['cmd']);
                        }

                        $objAlias->MoveNext();
                    }
                }
            }
        }

        return $arrAliases;
    }

    function _getPageURL($pageId){
    	global $objDatabase, $_CONFIG;
    	if($pageId == 0){
    		return '';
    	}
    	
    	$objRS = $objDatabase->SelectLimit("
                    SELECT
                        n.`catid`,
                        n.`catname`,
                        n.`cmd`,
                        n.`lang`,
                        m.`name`
                    FROM
                        `".DBPREFIX."content_navigation` AS n
                    LEFT OUTER JOIN `".DBPREFIX."modules` AS m ON m.`id` = n.`module`
                    WHERE (n.`catid` = ".$pageId." AND n.`lang` = ".$this->langId.')');
    	
    	$pageURL = $_CONFIG['domainUrl'].ASCMS_PATH_OFFSET
            .($_CONFIG['useVirtualLanguagePath'] == 'on' ? '/'.FWLanguage::getLanguageParameter($objRS->fields['lang'], 'lang') : null)
            .'/'.CONTREXX_DIRECTORY_INDEX
            .(!empty($objRS->fields['name']) ? '?section='.$objRS->fields['name'] : '?page='.$objRS->fields['catid'])
            .(empty($objRS->fields['cmd']) ? '' : '&cmd='.$objRS->fields['cmd']);
                   
		return $pageURL;                   
    }
    
    function is_alias_valid($alias) {
        return !file_exists(ASCMS_DOCUMENT_ROOT.'/'.$alias);
    }

    function _getMappingCount()
    {
        global $objDatabase;

        $objRS = $objDatabase->Execute("
            SELECT SUM(1) AS mappingCount
            FROM `".DBPREFIX."module_alias_domain_mapping`");
        if ($objRS !== false) {
            return $objRS->fields['mappingCount'];
        } else {
            return 0;
        };
    }
    
    function _getAliasesCount()
    {
        global $objDatabase;

        $objAlias = $objDatabase->Execute("
            SELECT SUM(1) AS aliasCount
            FROM `".DBPREFIX."module_alias_target` AS t
            INNER JOIN `".DBPREFIX."module_alias_source` AS s ON s.`target_id` = t.`id`
            WHERE s.`lang_id`=".$this->langId);

        if ($objAlias !== false) {
            return $objAlias->fields['aliasCount'];
        } else {
            return 0;
        };
    }

    function _getAlias($aliasId)
    {
        global $objDatabase;

        $objAlias = $objDatabase->Execute("
            SELECT t.`type` AS targetType,
                   t.`url` AS targetUrl,
                   s.`id` AS sourceId,
                   s.`isdefault` AS isdefault,
                   s.`url` AS sourceUrl,
                   s.`lang_id` AS langId
              FROM `".DBPREFIX."module_alias_target` AS t
              LEFT OUTER JOIN `".DBPREFIX."module_alias_source` AS s ON s.`target_id` = t.`id`
             WHERE t.`id` = ".$aliasId." AND s.`lang_id` = ".$this->langId."
             ORDER BY sourceUrl ASC");
        if ($objAlias && $objAlias->RecordCount()) {
            while (!$objAlias->EOF) {
                if (!isset($arrAlias)) {
                    $arrAlias = array(
                        'type'       => $objAlias->fields['targetType'],
                        'url'        => $objAlias->fields['targetUrl'],
                        'sources'    => array(),
                        'lang'       => $objAlias->fields['langId']
                    );
                }

                array_push($arrAlias['sources'], array(
                    'id'        => $objAlias->fields['sourceId'],
                    'isdefault' => $objAlias->fields['isdefault'],
                    'url'        => $objAlias->fields['sourceUrl']
                ));
                $objAlias->MoveNext();
            }
            $this->_setAliasTarget($arrAlias);
            return $arrAlias;
        }
        return false;
    }

    function _setAliasTarget(&$arrAlias)
    {
        global $objDatabase, $_CONFIG;

        if ($arrAlias['type'] == 'local') {
            $objAlias = $objDatabase->SelectLimit("
                SELECT
                    n.`catid`,
                    n.`catname`,
                    n.`cmd`,
                    m.`name`
                FROM
                    `".DBPREFIX."content_navigation` AS n
                LEFT OUTER JOIN `".DBPREFIX."modules` AS m ON m.`id` = n.`module`
                WHERE n.`catid` = ".intval($arrAlias['url']), 1
            );
            if ($objAlias !== false && $objAlias->RecordCount() == 1) {
                $arrAlias['title'] = $objAlias->fields['catname'];
                $arrAlias['pageUrl'] = ASCMS_PATH_OFFSET
                    .($_CONFIG['useVirtualLanguagePath'] == 'on' ? '/'.FWLanguage::getLanguageParameter($arrAlias['lang'], 'lang') : null)
                    .'/'.CONTREXX_DIRECTORY_INDEX
                    .(!empty($objAlias->fields['name']) ? '?section='.$objAlias->fields['name'] : '?page='.$objAlias->fields['catid'])
                    .(empty($objAlias->fields['cmd']) ? '' : '&cmd='.$objAlias->fields['cmd']);
            }
        }
    }

    function _addMapping($arrMapping)
    {
        global $objDatabase;

        $objRS = $objDatabase->SelectLimit("
            SELECT `id` FROM `".DBPREFIX."module_alias_domain_mapping`
            WHERE `domain` = '".addslashes($arrMapping['domain'])."'", 1);
        if ($objRS !== false && $objRS->RecordCount() == 1){
            $mappingId = $objRS->fields['id'];
        } else {
            if ($objDatabase->Execute("
                INSERT INTO `".DBPREFIX."module_alias_domain_mapping` (`domain`, `target`)
                VALUES ('".contrexx_addslashes($arrMapping['domain'])."','".contrexx_addslashes($arrMapping['target'])."')
                ON DUPLICATE KEY
                UPDATE `domain`='".contrexx_addslashes($arrMapping['domain'])."', 
                	   `target`='".contrexx_addslashes($arrMapping['target'])."'") !== false) {
                $mappingId = $objDatabase->Insert_ID();
            } else {
                return false;
            }
        }
        return $this->_writeHtAccessMappings();
    }
    
    function _updateMapping($mappingId, $arrMapping)
    {
        global $objDatabase;

        $upd_query = "
            UPDATE `".DBPREFIX."module_alias_domain_mapping`
            SET `domain`      = '".contrexx_addslashes($arrMapping['domain'])."',
                `target`       = '".contrexx_addslashes($arrMapping['target'])."'
            WHERE `id` =       ".intval($mappingId);
        if ($objDatabase->Execute($upd_query) !== false) {
            return $this->_writeHtAccessMappings();
        } else {
            return false;
        }
    }
    
    function _writeHtAccessMappings(){
    	global $_CONFIG;

        $arrRewriteRules = array();
        $limit = null;
        $arrRedirectAliases = array();
        $arrDefinedMappings = $this->_getMappings($limit);

        foreach ($arrDefinedMappings as $arrMapping) {           
        	$arrRewriteRules[] = 'RewriteCond %{HTTP_HOST} ^'.$arrMapping['domain'].'$ [NC]';
        	$arrRewriteRules[] = 'RewriteRule ^.*$ http://'.$_CONFIG['domainUrl'].'/'
        											 .CONTREXX_DIRECTORY_INDEX.'?page='.$arrMapping['target'].' [L,NC]';
        }
        $this->objFWHtAccess->setSection('core_modules__domainmapping', $arrRewriteRules);
        return $this->objFWHtAccess->write();
    }
           
    function _addAlias($arrAlias)
    {
        global $objDatabase;

        $objRS = $objDatabase->SelectLimit("
            SELECT `id` FROM `".DBPREFIX."module_alias_target`
            WHERE `url` = '".addslashes($arrAlias['url'])."'", 1);
        if ($objRS !== false && $objRS->RecordCount() == 1){
            $aliasId = $objRS->fields['id'];
        } else {
            if ($objDatabase->Execute("
                INSERT INTO `".DBPREFIX."module_alias_target` (`type`, `url`)
                VALUES ('".addslashes($arrAlias['type'])."','".addslashes($arrAlias['url'])."')
                ON DUPLICATE KEY
                UPDATE `type`='".addslashes($arrAlias['type'])."', `url`='".addslashes($arrAlias['url'])."'") !== false) {
                $aliasId = $objDatabase->Insert_ID();
            } else {
                return false;
            }
        }
        return $this->_setAliasSources($aliasId, $arrAlias);
    }

    function _updateAlias($aliasId, $arrAlias)
    {
        global $objDatabase;

        $upd_query = "
            UPDATE `".DBPREFIX."module_alias_target`
            SET `type`      = '".addslashes($arrAlias['type'])."',
                `url`       = '".addslashes($arrAlias['url'])."'
            WHERE `id` =       ".intval    ($aliasId);

        if ($objDatabase->Execute($upd_query) !== false) {
            return $this->_setAliasSources($aliasId, $arrAlias);
        } else {
            return false;
        }
    }

    function _setAliasSources($aliasId, $arrAlias)
    {
        global $objDatabase;

        $error = false;

        if (($arrOldAlias = $this->_getAlias($aliasId)) !== false) {
            foreach ($arrOldAlias['sources'] as $arrOldSource) {
                $stillPresent = false;
                foreach ($arrAlias['sources'] as $arrSource) {
                    if (!empty($arrSource['id']) && $arrSource['id'] == $arrOldSource['id']) {
                        if (($arrSource['isdefault'] != $arrOldSource['isdefault'] ) or  ($arrSource['url'] != $arrOldSource['url'])) {
                            $qry_update = "
                                UPDATE `".DBPREFIX."module_alias_source`
                                    SET `url`       = '".addslashes($arrSource['url'])      ."',
                                        `isdefault` = '".intval    ($arrSource['isdefault'])."',
                                        `lang_id`   = ".$this->langId."
                                WHERE `id` = ".intval($arrSource['id'])."
                                    AND `target_id` = ".intval($aliasId)
                                ;

                            if ($objDatabase->Execute($qry_update) === false) {
                                $error = true;
                            }
                        }
                        $stillPresent = true;
                        break;
                    }
                }

                if (!$stillPresent) {
                    if ($objDatabase->Execute("DELETE FROM `".DBPREFIX."module_alias_source` WHERE `id` = ".intval($arrOldSource['id'])." AND `target_id` = ".intval($aliasId).' AND `lang_id`='.$this->langId) === false) {
                        $error = true;
                    }
                }
            }
        }

        foreach ($arrAlias['sources'] as $arrSource) {
            if (empty($arrSource['id'])) {
                $alias_id  = intval($aliasId);
                $isdefault = intval($arrSource['isdefault']);
                $url       = addslashes($arrSource['url']);
                $qry_insert = "
                    INSERT INTO `".DBPREFIX."module_alias_source` (`target_id`, `url`, `isdefault`, `lang_id`)
                    VALUES ($alias_id, '$url', $isdefault, $this->langId)
                    ";
                if ($objDatabase->Execute($qry_insert) === false) {
                    $error = true;
                }
            }
        }

        if (!$error) {
// TODO: Never used
//            if ($arrAlias['type'] == 'local') {
//                $target = $arrAlias['pageUrl'];
//            } else {
//                $target = $arrAlias['url'];
//            }
            return $this->_activateRewriteEngine();
        }
        return false;
    }
        
    function _getRewriteInfo()
    {
        $arrRules = $this->objFWHtAccess->getSection('core_modules__alias');
        $arrRewriteInfo = array();
        $arrRewriteRule = array();
        foreach ($arrRules as $directive) {
            if (preg_match('#^\s*RewriteRule\s+\^(.+)\$\s+(.+)\s+.*$#', $directive, $arrRewriteRule)) {
                $arrRewriteInfo[$arrRewriteRule[2]][] = $arrRewriteRule[1];
            }
        }

        return $arrRewriteInfo;
    }

    function _isModRewriteInUse()
    {
        return $this->objFWHtAccess->isRewriteEngineInUse();
    }

    function _escapeStringForRegex($string) {
        $string = str_replace(array(' ', '\\\ '), '\\ ', $string);
        return str_replace(
            array('\\', '^',    '$',    '.',    '[',    ']',    '|',    '(',    ')',    '?',    '*',    '+',    '{',    '}',    ':'),
            array('\\\\', '\^',    '\$',    '\.',    '\[',    '\]',    '\|',    '\(',    '\)',    '\?',    '\*',    '\+',    '\{',    '\}',    '\:'),
            $string
        );
    }

    function _activateRewriteEngine()
    {
        global $_CONFIG;

        $arrRewriteRules = array();
        $limit = null;
        $allLanguages = true;
        $arrRedirectAliases = array();
        $arrDefinedAliasesByLang = $this->_getAliases($limit, $allLanguages);

        foreach ($arrDefinedAliasesByLang as $langId => $arrDefinedAliases) {
            foreach ($arrDefinedAliases as $arrDefinedAlias) {
                if ($arrDefinedAlias['type'] == 'local') {
                    if (!empty($arrDefinedAlias['pageUrl'])) {
                        $target = $arrDefinedAlias['pageUrl'];
                        if ($_CONFIG['useVirtualLanguagePath'] == 'off') {
                            $target .= '&setLang='.$langId;
                        }
                    } else {
                        continue;
                    }
                } else {
                    $target = $arrDefinedAlias['url'];
                }
                foreach ($arrDefinedAlias['sources'] as $arrSource) {
                    if ($_CONFIG['useVirtualLanguagePath'] == 'on') {
                        $langPrefixedSource = FWLanguage::getLanguageParameter($langId, 'lang').'/'.$arrSource['url'];

                        if ($this->_isUniqueAliasSource($arrSource['url'], '/'.$langPrefixedSource, '/'.$langPrefixedSource, $arrSource['id'], $langId, true)) {
                            // in case the language redirect alias already exists but doesn't belongs to the default language, then overwrite it
                            $langRedirectRule = 'RewriteRule ^'.$arrSource['url'].'$ /'.$langPrefixedSource.' [L,NC,QSA,R=301]';
                            if (isset($arrRedirectAliases[$arrSource['url']])) {
                                if ($langId == FWLanguage::getDefaultLangId()) {
                                    $arrRewriteRules[$arrRedirectAliases[$arrSource['url']]] =  $langRedirectRule;
                                }
                            } else {
                                $arrRewriteRules[] = $langRedirectRule;
                                $arrRedirectAliases[$arrSource['url']] = count($arrRewriteRules)-1;
                            }
                        }
                        $arrRewriteRules[] = 'RewriteRule ^'.$langPrefixedSource.'$ '.$target.' [L,NC,QSA]';
                    } else {
                        $arrRewriteRules[] = 'RewriteRule ^'.$arrSource['url'].'$    '.$target.' [L,NC,QSA]';
                    }
                }
            }
        }
        $this->objFWHtAccess->setSection('core_modules__alias', $arrRewriteRules);
        return $this->objFWHtAccess->write();
    }

    function _deactivateRewriteEngine()
    {
        $this->objFWHtAccess->removeSection('core_modules__alias');
        return $this->objFWHtAccess->write();
    }

    function _isUniqueAliasSource($url, $target, $oldTarget, $sourceId=0, $langId = null, $coreUrl = false)
    {
        global $objDatabase, $_CONFIG;

        if (empty($langId)) {
            $langId = $this->langId;
        }

        if (!$coreUrl && $_CONFIG['useVirtualLanguagePath'] == 'on') {
            // virtual language path has be taken in account
            $source = FWLanguage::getLanguageParameter($langId, 'lang').'/'.$url;
        } else {
            $source = $url;
        }

        $arrUsedAliasesInHtaccessFile = $this->_getUsedAliasesInHtaccessFile();
        if (   $arrUsedAliasesInHtaccessFile === false
            || (   isset($arrUsedAliasesInHtaccessFile[$source])
                && $arrUsedAliasesInHtaccessFile[$source] != $target
                && $arrUsedAliasesInHtaccessFile[$source] != $oldTarget)) {
            return false;
        }
        $objResult = $objDatabase->SelectLimit("
            SELECT 1
              FROM `".DBPREFIX."module_alias_source`
             WHERE `id`!=".intval($sourceId)."
               AND `url`='".addslashes($url)."'
               AND `lang_id`=".$langId, 1);
        if ($objResult && $objResult->RecordCount() == 0) {
            return true;
        }
        return false;
    }

    function _isUniqueAliasTarget($url, $targetId = 0)
    {
        global $objDatabase;

        $objResult = $objDatabase->SelectLimit("
            SELECT 1
              FROM `".DBPREFIX."module_alias_target` AS `t`
              LEFT JOIN `".DBPREFIX."module_alias_source` AS `s` ON (`s`.`target_id` = `t`.`id`)
             WHERE `t`.`id` != ".intval($targetId)."
               AND `t`.`url` = '".addslashes($url)."'
               AND `s`.`lang_id`=".$this->langId, 1);
        if ($objResult && $objResult->RecordCount() == 0) {
            return true;
        }
        return false;
    }

    function _deleteMapping($mappingId)
    {
        global $objDatabase;

        if ($objDatabase->Execute("
            DELETE FROM `".DBPREFIX."module_alias_domain_mapping`
            WHERE `id`=".intval($mappingId))){
            return true;
        }
        return false;
    }
    
    function _deleteAlias($aliasId)
    {
        global $objDatabase, $_CONFIG;

        $arrAlias = $this->_getAlias($aliasId);
        if ($arrAlias) {
            if ($objDatabase->Execute("
                DELETE s,t
                  FROM `".DBPREFIX."module_alias_source` AS s
                 INNER JOIN `".DBPREFIX."module_alias_target` AS t
                    ON t.`id` = s.`target_id`
                 WHERE s.`target_id`=".intval($aliasId).'
                   AND s.`lang_id`='.$this->langId
            ) !== false && $this->_activateRewriteEngine()) {
                if (   $_CONFIG['xmlSitemapStatus'] == 'on'
                    && ($result = XMLSitemap::write()) !== true) {
                    $this->arrStatusMsg['error'][] = $result;
                }
                return true;
            }
        }
        return false;
    }


    function _getUsedAliasesInHtaccessFile()
    {
        static $arrUsedAliases;

        if (!is_array($arrUsedAliases)) {
            $objHtAccess = new File_HtAccess(ASCMS_DOCUMENT_ROOT.'/.htaccess');
            if ($objHtAccess->load() !== true) {
                return false;
            }
            $arrAddition = $objHtAccess->getAdditional('array');
            $arrUsedAliases = array();
            $arrAlias = array();
            foreach ($arrAddition as $directive) {
// TODO: check the setLang condition -> might be required if we don't use virtual language paths
                if (preg_match('#^\s*RewriteRule\s+\^(.+)\$\s+(.+?)(?:&setLang=\d+)?\s+.*$#', $directive, $arrAlias)) {
//                if (preg_match('#^\s*RewriteRule\s+\^(.+)\$\s+(.+)\s+.*$#', $directive, $arrAlias)) {
                    $arrUsedAliases[$arrAlias[1]] = $arrAlias[2];
                }
            }
        }
        return $arrUsedAliases;
    }

    /**
     * get all alias_source entries that don't have a corresponding alias_target entry
     *
     * @param boolean $cleanUp whether to delete the aimless source entries
     */
    function _getUnusedTargets($cleanUp = false){
        global $objDatabase;

        $arrUnused = array();
        $query = "SELECT `s`.`id`
                    FROM `".DBPREFIX."module_alias_source` AS `s`
                    LEFT JOIN `".DBPREFIX."module_alias_target` AS `t` ON `t`.`id` = `s`.`target_id`
                    WHERE `t`.`id` IS NULL";
        if(($objRS = $objDatabase->Execute($query)) !== false){
            while(!$objRS->EOF){
                $arrUnused[] = $objRS->fields['id'];
                $objRS->MoveNext();
            }
        }
        if($cleanUp){
            $query = "DELETE `s`
                        FROM `".DBPREFIX."module_alias_source` AS `s`
                        LEFT JOIN `".DBPREFIX."module_alias_target` AS `t` ON `t`.`id` = `s`.`target_id`
                        WHERE `t`.`id` IS NULL";
            $objDatabase->Execute($query);
        }
        return $arrUnused;
    }

}

?>
