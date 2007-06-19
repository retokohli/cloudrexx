<?php
/**
 * Alias library
 * @copyright   CONTREXX CMS - ASTALAVISTA IT AG
 * @author		Astalavista Development Team <thun@astalvista.ch>
 * @version		1.0.0
 * @package     contrexx
 * @subpackage  core_module_alias
 * @todo        Edit PHP DocBlocks!
 */
class aliasLib
{
	var $_arrAliasTypes = array(
		'local',
		'url'
	);

	var $_arrConfig = null;

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

	function _getAliases($limit = null)
	{
		global $objDatabase, $_CONFIG;

		$arrAliases = array();
		$arrLocalAliases = array();
		$pos = isset($_GET['pos']) ? intval($_GET['pos']) : 0;

		$query = "
			SELECT
				t.`id` AS targetId,
				t.`type` AS targetType,
				t.`url` AS targetUrl,
				s.`id` AS sourceId,
				s.`url` AS sourceUrl
			FROM `".DBPREFIX."module_alias_target` AS t
			INNER JOIN `".DBPREFIX."module_alias_source` AS s ON s.`target_id` = t.`id`
			ORDER BY sourceUrl ASC";

		if (!empty($limit)) {
			$objAlias = $objDatabase->SelectLimit($query, $_CONFIG['corePagingLimit'], $pos);
		} else {
			$objAlias = $objDatabase->Execute($query);
		}

		if ($objAlias !== false) {
			while (!$objAlias->EOF) {
				if (!isset($arrAliases[$objAlias->fields['targetId']])) {
					$arrAliases[$objAlias->fields['targetId']] = array(
						'type'		=> $objAlias->fields['targetType'],
						'url'		=> $objAlias->fields['targetUrl'],
						'sources'	=> array()
					);

					if ($objAlias->fields['targetType'] == 'local') {
						$arrLocalAliases[intval($objAlias->fields['targetUrl'])] = $objAlias->fields['targetId'];
					}
				}
				array_push($arrAliases[$objAlias->fields['targetId']]['sources'], array(
					'id'	=> $objAlias->fields['sourceId'],
					'url'	=> $objAlias->fields['sourceUrl']
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
						m.`name`
					FROM
						`".DBPREFIX."content_navigation` AS n
					LEFT OUTER JOIN `".DBPREFIX."modules` AS m ON m.`id` = n.`module`
					WHERE n.`catid` = ".implode(' OR n.`catid` = ', $arrLocalAliasIds)
				);
				if ($objAlias !== false) {
					while (!$objAlias->EOF) {
						$arrAliases[$arrLocalAliases[$objAlias->fields['catid']]]['title'] = $objAlias->fields['catname'];
						$arrAliases[$arrLocalAliases[$objAlias->fields['catid']]]['pageUrl'] = ASCMS_PATH_OFFSET.'/index.php'
							.(!empty($objAlias->fields['name']) ? '?section='.$objAlias->fields['name'] : '?page='.$objAlias->fields['catid'])
							.(empty($objAlias->fields['cmd']) ? '' : '&amp;cmd='.$objAlias->fields['cmd']);

						$objAlias->MoveNext();
					}
				}
			}
		}

		return $arrAliases;
	}

	function _getAliasesCount()
	{
		global $objDatabase;

		$objAlias = $objDatabase->Execute("
			SELECT SUM(1) AS aliasCount
			FROM `".DBPREFIX."module_alias_target` AS t
			INNER JOIN `".DBPREFIX."module_alias_source` AS s ON s.`target_id` = t.`id`
		");

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
			SELECT
				t.`type` AS targetType,
				t.`url` AS targetUrl,
				s.`id` AS sourceId,
				s.`url` AS sourceUrl
			FROM `".DBPREFIX."module_alias_target` AS t
			LEFT OUTER JOIN `".DBPREFIX."module_alias_source` AS s ON s.`target_id` = t.`id`
			WHERE t.`id` = ".$aliasId."
			ORDER BY sourceUrl ASC"
		);

		if ($objAlias !== false && $objAlias->RecordCount() > 0) {
			while (!$objAlias->EOF) {
				if (!isset($arrAlias)) {
					$arrAlias = array(
						'type'		=> $objAlias->fields['targetType'],
						'url'		=> $objAlias->fields['targetUrl'],
						'sources'	=> array()
					);
				}

				array_push($arrAlias['sources'], array(
					'id'	=> $objAlias->fields['sourceId'],
					'url'	=> $objAlias->fields['sourceUrl']
				));

				$objAlias->MoveNext();
			}

			$this->_setAliasTarget($arrAlias);

			return $arrAlias;
		} else {
			return false;
		}
	}

	function _setAliasTarget(&$arrAlias)
	{
		global $objDatabase;

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
				$arrAlias['pageUrl'] = ASCMS_PATH_OFFSET.'/index.php'
					.(!empty($objAlias->fields['name']) ? '?section='.$objAlias->fields['name'] : '?page='.$objAlias->fields['catid'])
					.(empty($objAlias->fields['cmd']) ? '' : '&cmd='.$objAlias->fields['cmd']);
			}
		}
	}

	function _addAlias($arrAlias)
	{
		global $objDatabase;

		if ($objDatabase->Execute("INSERT INTO `".DBPREFIX."module_alias_target` (`type`, `url`) VALUES ('".addslashes($arrAlias['type'])."','".addslashes($arrAlias['url'])."')") !== false) {
			return $this->_setAliasSources($objDatabase->Insert_ID(), $arrAlias, '');
		} else {
			return false;
		}
	}

	function _updateAlias($aliasId, $arrAlias)
	{
		global $objDatabase;

		if (($arrOldAlias = $this->_getAlias($aliasId)) !== false && $objDatabase->Execute("UPDATE `".DBPREFIX."module_alias_target` SET `type` = '".addslashes($arrAlias['type'])."', `url` = '".addslashes($arrAlias['url'])."' WHERE `id` = ".intval($aliasId)) !== false) {
			return $this->_setAliasSources($aliasId, $arrAlias, ($arrOldAlias['type'] == 'local' ? $arrOldAlias['pageUrl'] : $arrOldAlias['url']));
		} else {
			return false;
		}
	}

	function _setAliasSources($aliasId, $arrAlias, $oldTarget)
	{
		global $objDatabase;

		$arrRemovedAliases = array();
		$error = false;

		if (($arrOldAlias = $this->_getAlias($aliasId)) !== false) {
			foreach ($arrOldAlias['sources'] as $arrOldSource) {
				$stillPresent = false;
				foreach ($arrAlias['sources'] as $arrSource) {
					if (!empty($arrSource['id']) && $arrSource['id'] == $arrOldSource['id']) {
						if ($arrSource['url'] != $arrOldSource['url']) {
							if ($objDatabase->Execute("UPDATE `".DBPREFIX."module_alias_source` SET `url` = '".addslashes($arrSource['url'])."' WHERE `id` = ".intval($arrSource['id'])." AND `target_id` = ".intval($aliasId)) !== false) {
								$arrRemovedAliases[] = $arrOldSource['url'];
							} else {
								$error = true;
							}
						}
						$stillPresent = true;
						break;
					}
				}

				if (!$stillPresent) {
					if ($objDatabase->Execute("DELETE FROM `".DBPREFIX."module_alias_source` WHERE `id` = ".intval($arrOldSource['id'])." AND `target_id` = ".intval($aliasId)) === false) {
						$error = true;
					} else {
						$arrRemovedAliases[] = $arrOldSource['url'];
					}
				}
			}
		}

		foreach ($arrAlias['sources'] as $arrSource) {
			if (empty($arrSource['id'])) {
				if ($objDatabase->Execute("INSERT INTO `".DBPREFIX."module_alias_source` (`target_id`, `url`) VALUES (".intval($aliasId).", '".addslashes($arrSource['url'])."')") === false) {
					$error = true;
				}
			}
		}

		if (!$error) {
			if ($arrAlias['type'] == 'local') {
				$target = $arrAlias['pageUrl'];
			} else {
				$target = $arrAlias['url'];
			}

			return $this->_activateRewriteEngine($arrRemovedAliases);
		} else {
			return false;
		}
	}

	function _getRewriteInfo()
	{
		require_once ASCMS_LIBRARY_PATH.'/PEAR/File/HtAccess.php';

		$objHtAccess = new File_HtAccess(ASCMS_DOCUMENT_ROOT.'/.htaccess');
		if ($objHtAccess->load() !== true) {
			return false;
		} else {
			$arrRewriteInfo = array('engine' => false, 'rules' => array());
			$arrAddition = $objHtAccess->getAdditional('array');

			foreach ($arrAddition as $directive) {
				if (preg_match('#^\s*RewriteRule\s+\^(.+)\$\s+(.+)\s+.*$#', $directive, $arrRewriteRule)) {
					$arrRewriteInfo['rules'][$arrRewriteRule[2]][] = $arrRewriteRule[1];
				} elseif (preg_match('#^\s*RewriteEngine\s+(Off|On)?.*$#i', $directive, $arrEngineStatus)) {
					if (strtolower($arrEngineStatus[1]) == 'on') {
						$arrRewriteInfo['engine'] = true;
					}
				}
			}

			return $arrRewriteInfo;
		}
	}

	function _isModRewriteInUse()
	{
		require_once ASCMS_LIBRARY_PATH.'/PEAR/File/HtAccess.php';

		$objHtAccess = new File_HtAccess(ASCMS_DOCUMENT_ROOT.'/.htaccess');
		if ($objHtAccess->load() !== true) {
			return false;
		} else {
			$arrAddition = $objHtAccess->getAdditional('array');

			if (is_array($arrAddition) && count(preg_grep('#^\s*RewriteEngine\s+On.*$#i', $arrAddition)) > 0) {
				return true;
			} else {
				return false;
			}
		}
	}

	function _escapeStringForRegex($string) {
		return str_replace(
			array('\\', '^',	'$',	'.',	'[',	']',	'|',	'(',	')',	'?',	'*',	'+',	'{',	'}',	':'),
			array('\\\\', '\^',	'\$',	'\.',	'\[',	'\]',	'\|',	'\(',	'\)',	'\?',	'\*',	'\+',	'\{',	'\}',	'\:'),
			$string
		);
	}

	function _activateRewriteEngine($arrRemoveAliases = array())
	{
		require_once ASCMS_LIBRARY_PATH.'/PEAR/File/HtAccess.php';

		if (!file_exists(ASCMS_DOCUMENT_ROOT.'/.htaccess')) {
			touch(ASCMS_DOCUMENT_ROOT.'/.htaccess');
		}

		$objHtAccess = new File_HtAccess(ASCMS_DOCUMENT_ROOT.'/.htaccess');
		if ($objHtAccess->load() !== true) {
			return false;
		} else {
			$arrAddition = $objHtAccess->getAdditional('array');
			$arrAdditionNew = array();
			$rewriteEngine = false;
			$arrAdditionAlias = array();
			$arrDefinedAliases = $this->_getAliases();

			foreach ($arrAddition as $directive) {
				if (preg_match('#^\s*RewriteRule.*$#i', $directive)) {
					if (count($arrRemoveAliases) == 0 || !preg_match('#^\s*RewriteRule\s+\^(.+)\$\s+.+\s+.*$#', $directive, $arrSources) || !in_array($arrSources[1], $arrRemoveAliases)) {
						$arrAdditionAlias[] = $directive;
					} else {
						continue;
					}
				} elseif (preg_match('#^\s*RewriteEngine\s+(Off|On)?.*$#i', $directive)) {
					$directive = 'RewriteEngine On';
					$rewriteEngine = true;
				}
				$arrAdditionNew[] = $directive;
			}

			foreach ($arrDefinedAliases as $arrDefinedAlias) {
				$arrAvailableRules = array();

				if ($arrDefinedAlias['type'] == 'local') {
					if (!empty($arrDefinedAlias['pageUrl'])) {
						$target = $arrDefinedAlias['pageUrl'];
					} else {
						continue;
					}
				} else {
					$target = $arrDefinedAlias['url'];
				}

				$targetEscaped = $this->_escapeStringForRegex($target);
				$arrAliasesSet = preg_grep('#^\s*RewriteRule\s+\^.+\$\s+'.$targetEscaped.'.*$#', $arrAdditionAlias);
				if (is_array($arrAliasesSet) && count($arrAliasesSet) > 0) {
					foreach ($arrAliasesSet as $settedAlias) {
						if (preg_match('#^\s*RewriteRule\s+\^(.+)\$\s+'.$targetEscaped.'.*$#', $settedAlias, $arrSource)) {
							$arrAvailableRules[] = $arrSource[1];
						}
					}
				}

				// add missing rewriterules
				foreach ($arrDefinedAlias['sources'] as $arrSource) {
					if (!in_array($arrSource['url'], $arrAvailableRules)) {
						$arrAdditionNew[] = 'RewriteRule ^'.$arrSource['url'].'$	'.$target.' [L,NC]';
					}
				}
			}

			if (!$rewriteEngine) {
				$arrAdditionNew = array_merge(array('RewriteEngine On'), $arrAdditionNew);
			}

			if ($arrAddition !== $arrAdditionNew) {
				$objHtAccess->setAdditional($arrAdditionNew);
				if ($objHtAccess->save() !== true) {
					return false;
				} else {
					return true;
				}
			} else {
				return true;
			}
		}
	}

	function _deactivateRewriteEngine()
	{
		require_once ASCMS_LIBRARY_PATH.'/PEAR/File/HtAccess.php';

		if (file_exists(ASCMS_DOCUMENT_ROOT.'/.htaccess')) {
			$objHtAccess = new File_HtAccess(ASCMS_DOCUMENT_ROOT.'/.htaccess');
			if ($objHtAccess->load() !== true) {
				return false;
			} else {
				$arrAddition = $objHtAccess->getAdditional('array');
				$arrAdditionNew = array();
				$rewriteEngineAddition = '';
				$arrDefinedAliases = $this->_getAliases();

				foreach ($arrAddition as $directive) {
					if (preg_match('#^\s*RewriteRule.*$#i', $directive)) {
						$arrAdditionAlias[] = $directive;
					} elseif (preg_match('#^\s*RewriteEngine\s+(Off|On)?.*$#i', $directive)) {
						$rewriteEngineAddition = $directive;
					}
				}

				foreach ($arrDefinedAliases as $arrDefinedAlias) {
					// remove aliases that have beed defined by the alias administration
					foreach ($arrDefinedAlias['sources'] as $arrSource) {
						$source = $this->_escapeStringForRegex($arrSource['url']);
						$arrAdditionAlias = preg_grep('#^\s*RewriteRule\s+\^'.$source.'\$\s+.*$#', $arrAdditionAlias, PREG_GREP_INVERT);
					}
				}

				if (count($arrAdditionAlias) > 0) {
					$arrAdditionNew = array_merge(array($rewriteEngineAddition), $arrAdditionAlias);
				}

				if ($arrAddition !== $arrAdditionNew) {
					$objHtAccess->setAdditional($arrAdditionNew);
					if ($objHtAccess->save() !== true) {
						return false;
					} else {
						return true;
					}
				} else {
					return true;
				}
			}
		}
	}

	function _setRewriteRules($aliasId, $oldTarget)
	{
		if (($arrAlias = $this->_getAlias($aliasId)) !== false) {
			$target = $arrAlias['type'] == 'local' ? $arrAlias['pageUrl'] : $arrAlias['url'];

			require_once ASCMS_LIBRARY_PATH.'/PEAR/File/HtAccess.php';

			$objHtAccess = new File_HtAccess(ASCMS_DOCUMENT_ROOT.'/.htaccess');
			$objHtAccess->load();

			$arrAddition = $objHtAccess->getAdditional('array');
			$arrAdditionNew = array();
			$rewriteEngine = false;

			foreach ($arrAddition as $directive) {
				if (!preg_match('#^\s*RewriteRule\s+\^.+\$\s+'.$oldTarget.'.*$#', $directive)) {
					if (preg_match('#^\s*RewriteEngine\s+(Off|On)?.*$#i', $directive)) {
						array_push($arrAdditionNew, 'RewriteEngine On');
						$rewriteEngine = true;
					} else {
						array_push($arrAdditionNew, $directive);
					}
				}
			}

			if (!$rewriteEngine) {
				$arrAdditionNew = array_merge(array('RewriteEngine On'), $arrAdditionNew);
			}

			foreach ($arrAlias['sources'] as $arrSource) {
				if (!empty($arrSource['url'])) {
					array_push($arrAdditionNew, 'RewriteRule ^'.$arrSource['url'].'$	'.$target.' [L,NC]');
				}
			}

			$objHtAccess->setAdditional($arrAdditionNew);
			if ($objHtAccess->save() !== true) {
				return false;
			} else {
				return true;
			}
		} else {
			return false;
		}
	}

	function _isUniqueAliasSource($url, $target, $sourceId = 0)
	{
		global $objDatabase;

		if (($arrUsedAliasesInHtaccessFile = $this->_getUsedAlisesInHtaccessFile()) === false || (isset($arrUsedAliasesInHtaccessFile[$url]) && $arrUsedAliasesInHtaccessFile[$url] != $target)) {
			return false;
		}

		$objResult = $objDatabase->SelectLimit("SELECT 1 FROM `".DBPREFIX."module_alias_source` WHERE `id` != ".intval($sourceId)." AND `url` = '".addslashes($url)."'");
		if ($objResult !== false && $objResult->RecordCount() == 0) {
			return true;
		} else{
			return false;
		}
	}

	function _isUniqueAliasTarget($url, $targetId = 0)
	{
		global $objDatabase;

		$objResult = $objDatabase->SelectLimit("SELECT 1 FROM `".DBPREFIX."module_alias_target` WHERE `id` != ".intval($targetId)." AND `url` = '".addslashes($url)."'");
		if ($objResult !== false && $objResult->RecordCount() == 0) {
			return true;
		} else{
			return false;
		}
	}

	function _deleteAlias($aliasId)
	{
		global $objDatabase;

		$arrRemovedAliases = array();
		if (($arrAlias = $this->_getAlias($aliasId)) !== false) {
			foreach ($arrAlias['sources'] as $arrSource) {
				$arrRemovedAliases[] = $arrSource['url'];
			}

			if ($objDatabase->Execute("DELETE FROM `".DBPREFIX."module_alias_source` WHERE `target_id` = ".intval($aliasId)) !== false && $this->_activateRewriteEngine($arrRemovedAliases) && $objDatabase->Execute("DELETE FROM `".DBPREFIX."module_alias_target` WHERE `id` = ".intval($aliasId)) !== false) {
			}
			return true;
		} else {
			return false;
		}
	}

	function _getUsedAlisesInHtaccessFile()
	{
		static $arrUsedAliases;

		if (!is_array($arrUsedAliases)) {
			require_once ASCMS_LIBRARY_PATH.'/PEAR/File/HtAccess.php';

			$objHtAccess = new File_HtAccess(ASCMS_DOCUMENT_ROOT.'/.htaccess');
			if ($objHtAccess->load() !== true) {
				return false;
			} else {
				$arrAddition = $objHtAccess->getAdditional('array');
				$arrUsedAliases = array();

				foreach ($arrAddition as $directive) {
					if (preg_match('#^\s*RewriteRule\s+\^(.+)\$\s+(.+)\s+.*$#', $directive, $arrAlias)) {
						$arrUsedAliases[$arrAlias[1]] = $arrAlias[2];
					}
				}
			}
		}

		return $arrUsedAliases;
	}
}
?>
