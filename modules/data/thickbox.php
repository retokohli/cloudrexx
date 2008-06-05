<?php

error_reporting(E_ALL);

include_once "../../config/configuration.php";
require_once "../../core/API.php";
require_once "lib/dataLib.class.php";

$objInit = &new InitCMS();

$objDatabase = getDatabaseObject($errorMsg);
$objData = new DataLibrary();

$id = intval($_GET['id']);
$lang = intval($_GET['lang']);

$entries = $objData->createEntryArray();
$entry  = $entries[$id];
$settings = $objData->createSettingsArray();

$title = $entry['translation'][$lang]['subject'];
$content = $entry['translation'][$lang]['content'];
$picture = (!empty($entry['translation'][$lang]['image'])) ? $entry['translation'][$lang]['image'] : "none";

$objTemplate = &new HTML_Template_Sigma(ASCMS_THEMES_PATH);
$objTemplate->setCurrentBlock("thickbox");

$template = preg_replace("/\[\[([A-Z_]+)\]\]/", '{$1}', $settings['data_template_thickbox']);
$objTemplate->setTemplate($template);

if ($entry['translation'][$lang]['attachment']) {
    $objTemplate->setVariable(array(
        "HREF"          => $entry['translation'][$lang]['attachment'],
        "TXT_DOWNLOAD"  => "blabla"
    ));
    $objTemplate->parse("attachment");
}

$objTemplate->setVariable(array(
    "TITLE"         => $title,
    "CONTENT"       => $content,
    "PICTURE"       => $picture
));
if ($picture != "none") {
    $objTemplate->parse("image");
} else {
    $objTemplate->hideBlock("image");
}
$objTemplate->parse("thickbox");
$objTemplate->show();
?>