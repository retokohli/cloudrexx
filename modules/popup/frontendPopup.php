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
    require_once(dirname(dirname(dirname(__FILE__))).'/config/configuration.php');
    require_once(dirname(dirname(dirname(__FILE__))).'/core/ClassLoader/ClassLoader.class.php');
    new \Cx\Core\ClassLoader\ClassLoader();

    require_once ('../../core/database.php');
    require_once ('../../config/settings.php');
    require_once ('../../core/API.php');
    
    
    //-------------------------------------------------------
    // Initialize database object
    //-------------------------------------------------------
    $errorMsg = '';
    $objDatabase = getDatabaseObject($errorMsg);
    if ($objDatabase === false) {
        die('Database error.');
    }
            
    $objPopup = $objDatabase->SelectLimit("SELECT `name`, `content` FROM ".DBPREFIX."module_popup WHERE id=".intval($_GET['id']), 1);
    if ($objPopup !== false && $objPopup->RecordCount() == 1) {
        print $objPopup->fields['content'];
    } else {
        print "no Source found...";
    }
    ?>
    </body>
</html>