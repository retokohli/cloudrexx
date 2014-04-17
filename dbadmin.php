<?php
require_once './core/Core/init.php';

$cx = init('minimal');

die();

$db = $cx->getDb()->getAdoDb();

$mode = 'DATABASES';
if (isset($_GET['mode']) && $_GET['mode'] == 'USERS') {
    $mode = 'USERS';
}

if (isset($_POST['remove'])) {
    if ($mode == 'USERS') {
        $result = $db->query('DROP USER \'' . contrexx_input2db($_POST['remove']) . '\'@\'localhost\'');
    } else {
        $result = $db->query('DROP DATABASE `' . contrexx_input2db($_POST['remove']) . '`');
    }
}

$field = 'Database';
if ($mode == 'USERS') {
    $field = 'User';
    $result = $db->query('SELECT User FROM mysql.user');
} else {
    $result = $db->query('SHOW DATABASES');
}
while (!$result->EOF) {
    if (in_array($result->fields[$field], array('information_schema', 'apsc', 'horde', 'mysql', 'performance_schema', 'psa', 'roundcubemail', 'sitebuilder5', 'admin', 'roundcube', 'pp_sb_db'))) {
        $result->moveNext();
        continue;
    }
    echo '<form method="post" action="' . $_SERVER['PHP_SELF'] . '?mode=' . $mode . '"><input name="remove" type="hidden" value="' . $result->fields[$field] . '" />' . $result->fields[$field] . '<input type="submit" value="remove" /></form>';
    $result->moveNext();
}

