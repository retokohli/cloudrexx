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
    require_once(dirname(__FILE__).'/../../config/settings.php');
    require_once(dirname(__FILE__).'/../../config/configuration.php');
    require_once(ASCMS_CORE_PATH.'/ClassLoader/ClassLoader.class.php');
    new \Cx\Core\ClassLoader\ClassLoader(ASCMS_DOCUMENT_ROOT);

    require_once(ASCMS_CORE_PATH.'/database.php');
    require_once(ASCMS_CORE_PATH.'/API.php');
    
    
    //-------------------------------------------------------
    // Initialize database object
    //-------------------------------------------------------
    $errorMsg = '';
    $objDatabase = getDatabaseObject($errorMsg);
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