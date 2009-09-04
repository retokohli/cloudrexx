<?php
if (!@include_once(ASCMS_CORE_PATH.'/API.php')) {
	die(sprintf($_CORELANG['TXT_UPDATE_API_LOAD_FAILED'], ASCMS_CORE_PATH.'/API.php'));
}

function executeContrexxUpdate($updateRepository = true, $updateBackendAreas = true, $updateModules = true)
{
	global $_ARRAYLANG, $_CORELANG, $objDatabase, $objUpdate;

	$arrDirs = array('core_module', 'module');
	$updateStatus = true;

	if (!@include_once(dirname(__FILE__).'/components/core/core.php')) {
		setUpdateMsg(sprintf($_CORELANG['TXT_UPDATE_UNABLE_LOAD_UPDATE_COMPONENT'], dirname(__FILE__).'/components/core/core.php'));
		return false;
	} elseif (UPDATE_UTF8 && !@include_once(dirname(__FILE__).'/components/core/utf8.php')) {
		setUpdateMsg(sprintf($_CORELANG['TXT_UPDATE_UNABLE_LOAD_UPDATE_COMPONENT'], dirname(__FILE__).'/components/core/utf8.php'));
		return false;
	} elseif (!@include_once(dirname(__FILE__).'/components/core/backendAreas.php')) {
		setUpdateMsg(sprintf($_CORELANG['TXT_UPDATE_UNABLE_LOAD_UPDATE_COMPONENT'], dirname(__FILE__).'/components/core/backendAreas.php'));
		return false;
	} elseif (!@include_once(dirname(__FILE__).'/components/core/modules.php')) {
		setUpdateMsg(sprintf($_CORELANG['TXT_UPDATE_UNABLE_LOAD_UPDATE_COMPONENT'], dirname(__FILE__).'/components/core/modules.php'));
		return false;
	} elseif (!@include_once(dirname(__FILE__).'/components/core/settings.php')) {
		setUpdateMsg(sprintf($_CORELANG['TXT_UPDATE_UNABLE_LOAD_UPDATE_COMPONENT'], dirname(__FILE__).'/components/core/settings.php'));
		return false;
	} elseif (!@include_once(dirname(__FILE__).'/components/core/version.php')) {
		setUpdateMsg(sprintf($_CORELANG['TXT_UPDATE_UNABLE_LOAD_UPDATE_COMPONENT'], dirname(__FILE__).'/components/core/version.php'));
		return false;
	}

	if (!isset($_SESSION['contrexx_update']['update']['done'])) {
		$_SESSION['contrexx_update']['update']['done'] = array();
	}

	if (!in_array('configSettings', $_SESSION['contrexx_update']['update']['done'])) {
		$result = _writeNewConfigurationFile();
		if ($result === false) {
			return false;
		}

		if (UPDATE_UTF8) {
			$result = _utf8Update();
			if ($result === false) {
				if (empty($objUpdate->arrStatusMsg['title'])) {
					setUpdateMsg(sprintf($_CORELANG['TXT_UPDATE_COMPONENT_BUG'], $_CORELANG['TXT_UTF_CONVERSION']), 'title');
				}
				return false;
			}
		}

		$result = _writeNewConfigurationFile(true);
		if ($result === false) {
			if (empty($objUpdate->arrStatusMsg['title'])) {
				setUpdateMsg(sprintf($_CORELANG['TXT_UPDATE_COMPONENT_BUG'], $_CORELANG['TXT_UTF_CONVERSION']), 'title');
			}
			return false;
		} else {
			$_SESSION['contrexx_update']['update']['done'][] = 'configSettings';
		}
	}

	if (UPDATE_UTF8) {
		$query = 'SET CHARACTER SET utf8';
		if ($objDatabase->Execute($query) === false) {
			return _databaseError($query, $objDatabase->ErrorMsg());
		}
	}

	if (!in_array('coreUpdate', $_SESSION['contrexx_update']['update']['done'])) {
		$result = _coreUpdate();
		if ($result === false) {
			if (empty($objUpdate->arrStatusMsg['title'])) {
				setUpdateMsg(sprintf($_CORELANG['TXT_UPDATE_COMPONENT_BUG'], $_CORELANG['TXT_UPDATE_CORE_SYSTEM']), 'title');
			}
			return false;
		} else {
			$_SESSION['contrexx_update']['update']['done'][] = 'coreUpdate';
		}
	}

	foreach ($arrDirs as $dir) {
		$dh = opendir(dirname(__FILE__).'/components/'.$dir);
		if ($dh) {
			while (($file = readdir($dh)) !== false) {
				if (!in_array($file, $_SESSION['contrexx_update']['update']['done'])) {
					if (substr($file, -4) == '.php') {
                        DBG::msg("--------- updating $file ------");
						if (!@include_once(dirname(__FILE__).'/components/'.$dir.'/'.$file)) {
							setUpdateMsg('Update Fehler', 'title');
							setUpdateMsg(sprintf($_CORELANG['TXT_UPDATE_UNABLE_LOAD_UPDATE_COMPONENT'], dirname(__FILE__).'/components/'.$dir.'/'.$file));
							return false;
						}
						$function = '_'.substr($file, 0, -4).'Update';
						if (function_exists($function)) {
							$result = $function();
							if ($result === false) {
								if (empty($objUpdate->arrStatusMsg['title'])) {
									setUpdateMsg(sprintf($_CORELANG['TXT_UPDATE_COMPONENT_BUG'], $file), 'title');
								}
								return false;
							}
						} else {
							setUpdateMsg('Update Fehler', 'title');
							setUpdateMsg(sprintf($_CORELANG['TXT_UPDATE_UPDATE_COMPONENT_CORRUPT'], substr($file, 0, -4), $file));
							return false;
						}
					}

					$_SESSION['contrexx_update']['update']['done'][] = $file;
				}
			}
		} else {
			setUpdateMsg('Update Fehler', 'title');
			setUpdateMsg(sprintf($_CORELANG['TXT_UPDATE_UNABLE_LOAD_DIR_COMPONENTS'], dirname(__FILE__).'/components/'.$dir));
			return false;
		}

		closedir($dh);
	}

	if (!in_array('coreSettings', $_SESSION['contrexx_update']['update']['done'])) {
		$result = _updateSettings();
		if ($result === false) {
			if (empty($objUpdate->arrStatusMsg['title'])) {
				setUpdateMsg(sprintf($_CORELANG['TXT_UPDATE_COMPONENT_BUG'], $_CORELANG['TXT_UPDATE_BASIC_CONFIGURATION']), 'title');
			}
			return false;
		} else {
			$_SESSION['contrexx_update']['update']['done'][] = 'coreSettings';
		}
	}

	if (!in_array('coreModules', $_SESSION['contrexx_update']['update']['done'])) {
		if ($updateModules) {
			$result = _updateModules();
			if ($result === false) {
				if (empty($objUpdate->arrStatusMsg['title'])) {
					setUpdateMsg(sprintf($_CORELANG['TXT_UPDATE_COMPONENT_BUG'], $_CORELANG['TXT_UPDATE_MODULES']), 'title');
				}
				return false;
			} else {
				$_SESSION['contrexx_update']['update']['done'][] = 'coreModules';
			}
		}
	}

	if (!in_array('coreBackendAreas', $_SESSION['contrexx_update']['update']['done'])) {
		if ($updateBackendAreas) {
			$result = _updateBackendAreas();
			if ($result === false) {
				if (empty($objUpdate->arrStatusMsg['title'])) {
					setUpdateMsg(sprintf($_CORELANG['TXT_UPDATE_COMPONENT_BUG'], $_CORELANG['TXT_UPDATE_SECURITY_SYSTEM']), 'title');
				}
				return false;
			} else {
				$_SESSION['contrexx_update']['update']['done'][] = 'coreBackendAreas';
			}
		}
	}

	if (!in_array('coreModuleRepository', $_SESSION['contrexx_update']['update']['done'])) {
		if ($updateRepository) {
			$result = _updateModuleRepository();
			if ($result === false) {
				if (empty($objUpdate->arrStatusMsg['title'])) {
					setUpdateMsg(sprintf($_CORELANG['TXT_UPDATE_COMPONENT_BUG'], $_CORELANG['TXT_UPDATE_MODULE_REPOSITORY']), 'title');
				}
				return false;
			} else {
				$_SESSION['contrexx_update']['update']['done'][] = 'coreModuleRepository';
			}
		}
	}

	$result = _createVersionFile();
	if ($result === false) {
		if (empty($objUpdate->arrStatusMsg['title'])) {
			setUpdateMsg(sprintf($_CORELANG['TXT_UPDATE_COMPONENT_BUG'], $_CORELANG['TXT_UPDATE_VERSION_INFO']), 'title');
		}
		return false;
	}

	_response();

	return true;
}

function _response()
{
	global $_ARRAYLANG;
	setUpdateMsg($_ARRAYLANG['TXT_README_MSG'], 'msg');
}

function _updateModuleRepository()
{
	global $_CORELANG, $objUpdate;

	$count = 0;

	$dh = opendir(dirname(__FILE__).'/components/core');
	if ($dh) {
		while (($file = readdir($dh)) !== false) {
			if (preg_match('#^repository_([0-9]+)\.php$#', $file, $arrFunction)) {
				if (!in_array($file, $_SESSION['contrexx_update']['update']['done'])) {
					if (function_exists('memory_get_usage')) {
						if (!checkMemoryLimit()) {
							return false;
						}
					} else {
						$count++;
					}

					if (!@include_once(dirname(__FILE__).'/components/core/'.$file)) {
						setUpdateMsg(sprintf($_CORELANG['TXT_UPDATE_UNABLE_LOAD_UPDATE_COMPONENT'], dirname(__FILE__).'/components/core/'.$file));
						return false;
					}
					$function = '_updateModuleRepository_'.$arrFunction[1];
                    if (function_exists($function)) {
                        DBG::msg("---------------------- update: calling $function() ---------");
						$result = $function();
						if ($result === false) {
							if (empty($objUpdate->arrStatusMsg['title'])) {
								setUpdateMsg(sprintf($_CORELANG['TXT_UPDATE_COMPONENT_BUG'], $file), 'title');
							}
							return false;
						}
					} else {
						setUpdateMsg(sprintf($_CORELANG['TXT_UPDATE_UPDATE_COMPONENT_CORRUPT'], $_CORELANG['TXT_UPDATE_MODULE_REPOSITORY'], $arrFunction[1]));
						return false;
					}

					$_SESSION['contrexx_update']['update']['done'][] = $file;

					if ($count == 10) {
						setUpdateMsg($_CORELANG['TXT_UPDATE_PROCESS_HALTED'], 'title');
						setUpdateMsg($_CORELANG['TXT_UPDATE_PROCESS_HALTED_RAM_MSG'].'<br /><br />', 'msg');
						setUpdateMsg('<input type="submit" value="'.$_CORELANG['TXT_CONTINUE_UPDATE'].'" name="updateNext" /><input type="hidden" name="processUpdate" id="processUpdate" />', 'button');
						return false;
					}
				}
			}
		}
	} else {
		setUpdateMsg($_CORELANG['TXT_UPDATE_UNABLE_LOAD_REPOSITORY_PARTS']);
		return false;
	}

	closedir($dh);

	return true;
}
?>
