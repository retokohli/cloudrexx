<!doctype html>
<html lang="en">
  <head>
    <title>Migrate Users and Newsletter Subscriptions</title>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
  </head>
  <body>
    <?php
//    Run URL:
//        http://chdirect.org.lvh.me/customizing/ch-direct.org-user-import/start.php
    require_once dirname(dirname(dirname(__FILE__))) . '/core/Core/init.php';
    //\DBG::activate(DBG_PHP | DBG_LOG_FILE);
    \DBG::activate(DBG_PHP|DBG_DB_ERROR);
    init('minimal');
    require_once 'config.php';
    require_once 'lib/DatabaseToSql.php';
    chdir(dirname(__FILE__));
    DatabaseToSql::convert(\Converter::SQL_TARGET_PATH);
    ?>
  </body>
</html>
