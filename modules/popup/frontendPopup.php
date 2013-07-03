<html>
    <head>
        <title><?php print $_GET['title']; ?></title>
        <style type="text/css">
        <!--
        body {
            background-color: #ffffff;
            margin: 0px 0px 0px 0px;
        }
        -->
        </style>
    </head>
    <body>
    <?php
    global $objDatabase;
    require_once dirname(__FILE__).'/../../init.php';
    $cx = init('minimal');
    $objDatabase = $cx->getDb()->getAdoDb();
    if ($objDatabase === false) {
        die('Database error.');
    }

    $objPopup = $objDatabase->SelectLimit("SELECT `name`, `content` FROM ".DBPREFIX."module_popup WHERE id=".intval(!empty($_GET['id']) ? $_GET['id'] : 0), 1);
    if ($objPopup !== false && $objPopup->RecordCount() == 1) {
        print $objPopup->fields['content'];
    } else {
        print "no Source found...";
    }
    ?>
    </body>
</html>