<?php

/**
 * Load custom fonts for PDF generation
 *
 * @param mPdf $mPdf mPdf
 *
 * @return void
 */
function getCustomFonts($mPdf) {
    $cx = \Cx\Core\Core\Controller\Cx::instanciate();
    $dir = ltrim($cx->getSystemFolders()[10], '/') . '/Pdf/ttfonts/';
    $fileNames = array_diff(scandir($dir), array('..', '.'));
    $fontNames = array();

    foreach ($fileNames as $fileName) {
        $font = explode('-', $fileName);
        $fontName = $font[0];
        $extension = pathinfo($font, PATHINFO_EXTENSION);

        if (array_key_exists($fontName, $mPdf->fontdata)) {
            continue;
        }
        $fontNames[$fontName] = $extension;
    }

    foreach ($fontNames as $fontName => $extension) {
        $newFonts = array(
            'R' => $fontName . '-Regular.' . $extension,
            'B' => $fontName . '-Bold.' . $extension,
            'I' => $fontName . '-Italic.' . $extension,
            'BI' => $fontName . '-BoldItalic.' . $extension,
        );

        $mPdf->fontdata[strtolower($fontName)] = $newFonts;
    }
}
getCustomFonts($this);
