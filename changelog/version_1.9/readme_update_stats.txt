/*********************************
* Installation des neuen counters
**********************************

1. Datenbank Tabellen erstellen

2. /index.php anpassen:
	Zeile 70 und 71:
		$counter    = &new counter();
		$users      = $counter->counter_getOnlineUsers();
	ersetzen durch:
		$objCounter = &new statsLibrary();
		$users      = $objCounter->getOnlineUsers();

	Beim Block "set global template variables" folgende variable hinzufügen:
		'COUNTER'	=> $objCounter->getCounterTag(),

3. /core/API.php anpassen:
	Zeile 59:
		require_once ASCMS_CORE_PATH.'/counter.class.php';
	ersetzen durch:
		require_once ASCMS_CORE_MODULE_PATH.'/stats/statsLib.class.php';

4. /config/set_constants.php anpassen:
	Folgende Konstante hinzufügen:
		define('ASCMS_CORE_MODULE_WEB_PATH',        ASCMS_PATH_OFFSET.'/core_modules');

5. In der Designverwaltung:
	Den Platzhalter [[COUNTER]] in die index.html Datei am Ende einfügen

6. adodb Verzeichnis nach /lib/ kopieren

7. ykcee Verzeichnis nach /lib/ kopieren

8. /config/configuration.php anpassen:
	Folgenden wert hinzufügen:
		$_DBCONFIG['dbType'] = "mysql";  //database type (e.g. mysql,postgres ..)

9. /core_modules/stats kopieren